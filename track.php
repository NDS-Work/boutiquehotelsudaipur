<?php
/**
 * track.php — Visitor tracker
 * IP stored in admin DB only (authenticated access). GDPR: admin panel is
 * restricted to site owner — storing IP for analytics/security is legitimate interest.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(array('error' => 'Invalid JSON'));
    exit;
}

// ── Collect signals ──────────────────────────────────────────────────────────
$rawIp    = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0');
$ip       = trim(explode(',', $rawIp)[0]); // real IP (first in chain)
$ua       = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 300) : '';
$pageUrl  = isset($input['page_url'])   ? filter_var($input['page_url'],  FILTER_SANITIZE_URL) : '';
$referrer = isset($input['referrer'])   ? filter_var($input['referrer'],  FILTER_SANITIZE_URL) : '';
$title    = isset($input['page_title']) ? substr($input['page_title'], 0, 200) : '';
$today    = date('Y-m-d');

// ── Session key: daily hash of IP+UA (no raw IP in key) ─────────────────────
$dailySalt  = hash('sha256', $today . 'bhi_salt_2025');
$ipHash     = hash('sha256', $ip . $dailySalt);
$sessionKey = hash('sha256', $ipHash . $ua . $today);

// ── Bot filter ───────────────────────────────────────────────────────────────
$bots = array('bot', 'crawl', 'spider', 'slurp', 'baidu', 'yandex', 'wget', 'curl', 'python', 'http');
$uaLow = strtolower($ua);
foreach ($bots as $b) {
    if (strpos($uaLow, $b) !== false) {
        echo json_encode(array('status' => 'ignored', 'reason' => 'bot'));
        exit;
    }
}

// ── Device ───────────────────────────────────────────────────────────────────
if (preg_match('/(tablet|ipad|playbook|silk)/i', $ua)) {
    $device = 'tablet';
} elseif (preg_match('/(mobile|android|iphone|ipod|blackberry|windows phone)/i', $ua)) {
    $device = 'mobile';
} else {
    $device = 'desktop';
}

// ── Browser detection from UA ────────────────────────────────────────────────
function detectBrowser($ua) {
    if (strpos($ua, 'Edg/') !== false)     return 'Edge';
    if (strpos($ua, 'OPR/') !== false)     return 'Opera';
    if (strpos($ua, 'Chrome/') !== false)  return 'Chrome';
    if (strpos($ua, 'Firefox/') !== false) return 'Firefox';
    if (strpos($ua, 'Safari/') !== false)  return 'Safari';
    return 'Other';
}

// ── OS detection from UA ─────────────────────────────────────────────────────
function detectOS($ua) {
    if (strpos($ua, 'Windows NT') !== false) return 'Windows';
    if (strpos($ua, 'Mac OS X') !== false)   return 'macOS';
    if (strpos($ua, 'Android') !== false)    return 'Android';
    if (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false) return 'iOS';
    if (strpos($ua, 'Linux') !== false)      return 'Linux';
    return 'Other';
}

$browser = detectBrowser($ua);
$os      = detectOS($ua);

// ── DB ───────────────────────────────────────────────────────────────────────
$dbPath = __DIR__ . '/data/new.sqlite.db';
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'DB unavailable'));
    exit;
}

// ── Create / migrate tables ───────────────────────────────────────────────────
$db->exec("
CREATE TABLE IF NOT EXISTS visit_sessions (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    session_key    TEXT NOT NULL UNIQUE,
    ip_address     TEXT NOT NULL,
    ip_hash        TEXT NOT NULL,
    device         TEXT DEFAULT 'desktop',
    browser        TEXT DEFAULT '',
    os             TEXT DEFAULT '',
    user_agent     TEXT DEFAULT '',
    first_seen     DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_seen      DATETIME DEFAULT CURRENT_TIMESTAMP,
    page_count     INTEGER DEFAULT 0,
    visit_date     TEXT NOT NULL,
    is_new_visitor INTEGER DEFAULT 1
)
");

$db->exec("
CREATE TABLE IF NOT EXISTS visit_pageviews (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    session_key TEXT NOT NULL,
    page_url    TEXT NOT NULL,
    page_title  TEXT,
    referrer    TEXT,
    visited_at  DATETIME DEFAULT CURRENT_TIMESTAMP
)
");

// Add new columns if table already existed without them (safe migration)
$cols = array();
$colRes = $db->query("PRAGMA table_info(visit_sessions)");
if ($colRes) {
    while ($c = $colRes->fetch(PDO::FETCH_ASSOC)) {
        $cols[] = $c['name'];
    }
}
if (!in_array('ip_address', $cols)) $db->exec("ALTER TABLE visit_sessions ADD COLUMN ip_address TEXT DEFAULT ''");
if (!in_array('browser',    $cols)) $db->exec("ALTER TABLE visit_sessions ADD COLUMN browser    TEXT DEFAULT ''");
if (!in_array('os',         $cols)) $db->exec("ALTER TABLE visit_sessions ADD COLUMN os         TEXT DEFAULT ''");
if (!in_array('user_agent', $cols)) $db->exec("ALTER TABLE visit_sessions ADD COLUMN user_agent TEXT DEFAULT ''");

// ── Is new visitor? ──────────────────────────────────────────────────────────
$stmt = $db->prepare("SELECT COUNT(*) FROM visit_sessions WHERE ip_hash = ? AND visit_date < ?");
$stmt->execute(array($ipHash, $today));
$isNewVisitor = ((int)$stmt->fetchColumn() === 0) ? 1 : 0;

// ── Upsert session ────────────────────────────────────────────────────────────
$exists = $db->prepare("SELECT id FROM visit_sessions WHERE session_key = ?");
$exists->execute(array($sessionKey));
$existingId = $exists->fetchColumn();

if ($existingId) {
    $db->prepare("UPDATE visit_sessions SET last_seen = CURRENT_TIMESTAMP, page_count = page_count + 1, ip_address = ? WHERE session_key = ?")
       ->execute(array($ip, $sessionKey));
} else {
    $db->prepare("INSERT INTO visit_sessions (session_key, ip_address, ip_hash, device, browser, os, user_agent, visit_date, is_new_visitor, page_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)")
       ->execute(array($sessionKey, $ip, $ipHash, $device, $browser, $os, $ua, $today, $isNewVisitor));
}

// ── Record pageview ───────────────────────────────────────────────────────────
$db->prepare("INSERT INTO visit_pageviews (session_key, page_url, page_title, referrer) VALUES (?, ?, ?, ?)")
   ->execute(array($sessionKey, $pageUrl, $title, $referrer));

echo json_encode(array('status' => 'ok'));