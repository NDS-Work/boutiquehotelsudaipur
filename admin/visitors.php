<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$currentPage = 'visitors';
$pageTitle   = 'Visitor Analytics';

$db = new SQLite3(__DIR__ . '/../data/new.sqlite.db');
$db->busyTimeout(5000);

$today     = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last7     = date('Y-m-d', strtotime('-6 days'));
$last30    = date('Y-m-d', strtotime('-29 days'));

// ── Summary stats ─────────────────────────────────────────────────────────────
$totalToday     = (int)$db->querySingle("SELECT COUNT(*) FROM visit_sessions WHERE visit_date = '$today'");
$newToday       = (int)$db->querySingle("SELECT COUNT(*) FROM visit_sessions WHERE visit_date = '$today' AND is_new_visitor = 1");
$returningToday = $totalToday - $newToday;
$pagesTotal     = (int)$db->querySingle("SELECT COALESCE(SUM(page_count),0) FROM visit_sessions WHERE visit_date = '$today'");
$avgPages       = $totalToday > 0 ? round($pagesTotal / $totalToday, 1) : 0;
$totalYest      = (int)$db->querySingle("SELECT COUNT(*) FROM visit_sessions WHERE visit_date = '$yesterday'");
$total7         = (int)$db->querySingle("SELECT COUNT(*) FROM visit_sessions WHERE visit_date >= '$last7'");
$total30        = (int)$db->querySingle("SELECT COUNT(*) FROM visit_sessions WHERE visit_date >= '$last30'");

// ── Daily chart (last 14 days) ────────────────────────────────────────────────
$dailyDates = array(); $dailySessions = array(); $dailyNew = array();
$daily = $db->query("
    SELECT visit_date, COUNT(*) as sessions,
           SUM(CASE WHEN is_new_visitor=1 THEN 1 ELSE 0 END) as new_v
    FROM visit_sessions
    WHERE visit_date >= date('now','-13 days')
    GROUP BY visit_date ORDER BY visit_date ASC
");
if ($daily) {
    while ($r = $daily->fetchArray(SQLITE3_ASSOC)) {
        $dailyDates[]    = $r['visit_date'];
        $dailySessions[] = (int)$r['sessions'];
        $dailyNew[]      = (int)$r['new_v'];
    }
}

// ── Top pages (last 7 days) ───────────────────────────────────────────────────
$topPagesData = array();
$topPages = $db->query("
    SELECT page_url, COUNT(*) as views FROM visit_pageviews
    WHERE visited_at >= datetime('now','-7 days')
    GROUP BY page_url ORDER BY views DESC LIMIT 10
");
if ($topPages) while ($r = $topPages->fetchArray(SQLITE3_ASSOC)) $topPagesData[] = $r;

// ── Device breakdown ──────────────────────────────────────────────────────────
$deviceLabels = array(); $deviceCounts = array();
$devices = $db->query("SELECT device, COUNT(*) as cnt FROM visit_sessions WHERE visit_date >= '$last7' GROUP BY device");
if ($devices) {
    while ($r = $devices->fetchArray(SQLITE3_ASSOC)) {
        $deviceLabels[] = ucfirst($r['device']);
        $deviceCounts[] = (int)$r['cnt'];
    }
}

// ── Browser breakdown ─────────────────────────────────────────────────────────
$browserLabels = array(); $browserCounts = array();
$browsers = $db->query("SELECT browser, COUNT(*) as cnt FROM visit_sessions WHERE visit_date >= '$last7' AND browser != '' GROUP BY browser ORDER BY cnt DESC");
if ($browsers) {
    while ($r = $browsers->fetchArray(SQLITE3_ASSOC)) {
        $browserLabels[] = $r['browser'];
        $browserCounts[] = (int)$r['cnt'];
    }
}

// ── Session list (today, latest 20) ──────────────────────────────────────────
$sessionRows = array();
$sessions = $db->query("
    SELECT session_key, ip_address, device, browser, os, user_agent,
           first_seen, last_seen, page_count, is_new_visitor,
           (strftime('%s', last_seen) - strftime('%s', first_seen)) as duration_sec
    FROM visit_sessions
    WHERE visit_date = '$today'
    ORDER BY last_seen DESC LIMIT 20
");
if ($sessions) while ($r = $sessions->fetchArray(SQLITE3_ASSOC)) $sessionRows[] = $r;

// ── Pageviews per session ─────────────────────────────────────────────────────
$pvsMap = array();
if (!empty($sessionRows)) {
    $keys = array();
    foreach ($sessionRows as $s) $keys[] = "'" . SQLite3::escapeString($s['session_key']) . "'";
    $pvs = $db->query("
        SELECT session_key, page_url, page_title, referrer, visited_at
        FROM visit_pageviews WHERE session_key IN (" . implode(',', $keys) . ")
        ORDER BY visited_at ASC
    ");
    if ($pvs) while ($r = $pvs->fetchArray(SQLITE3_ASSOC)) $pvsMap[$r['session_key']][] = $r;
}

function deviceIcon($d) {
    if ($d === 'mobile') return '📱';
    if ($d === 'tablet') return '💊';
    return '🖥️';
}
function formatDur($sec) {
    $sec = (int)$sec;
    if ($sec <= 0) return '< 1s';
    if ($sec < 60) return $sec . 's';
    return floor($sec / 60) . 'm ' . ($sec % 60) . 's';
}

require_once __DIR__ . '/layout-header.php';

$jDates = json_encode($dailyDates);
$jSess  = json_encode($dailySessions);
$jNew   = json_encode($dailyNew);
$jDevL  = json_encode($deviceLabels);
$jDevC  = json_encode($deviceCounts);
$jBrwL  = json_encode($browserLabels);
$jBrwC  = json_encode($browserCounts);
?>

<div class="topbar"><h1>Visitor Analytics</h1></div>
<div class="content">

<!-- Summary stats -->
<div class="stats" style="margin-bottom:0">
    <div class="stat"><div class="stat-value"><?php echo $totalToday; ?></div><div class="stat-label">Sessions Today</div></div>
    <div class="stat"><div class="stat-value"><?php echo $newToday; ?></div><div class="stat-label">New Visitors</div></div>
    <div class="stat"><div class="stat-value"><?php echo $returningToday; ?></div><div class="stat-label">Returning</div></div>
    <div class="stat"><div class="stat-value"><?php echo $avgPages; ?></div><div class="stat-label">Avg Pages / Session</div></div>
    <div class="stat"><div class="stat-value"><?php echo $totalYest; ?></div><div class="stat-label">Yesterday</div></div>
    <div class="stat"><div class="stat-value"><?php echo $total7; ?></div><div class="stat-label">Last 7 Days</div></div>
    <div class="stat"><div class="stat-value"><?php echo $total30; ?></div><div class="stat-label">Last 30 Days</div></div>
</div>

<!-- Daily trend -->
<div class="card" style="margin-top:24px">
    <div class="card-title">Daily Sessions — Last 14 Days</div>
    <div style="position:relative;height:240px"><canvas id="dailyChart"></canvas></div>
</div>

<!-- Top pages + Device + Browser -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-top:0">

    <div class="card">
        <div class="card-title">Top Pages (7 Days)</div>
        <?php if (empty($topPagesData)): ?>
            <p style="color:#888;font-size:13px">No data yet.</p>
        <?php else: ?>
        <table style="width:100%;font-size:13px;border-collapse:collapse">
            <thead><tr>
                <th style="text-align:left;padding:5px 4px;color:#9ebffe;border-bottom:1px solid #2c2c2a">Page</th>
                <th style="text-align:right;padding:5px 4px;color:#9ebffe;border-bottom:1px solid #2c2c2a">Views</th>
            </tr></thead>
            <tbody>
            <?php foreach ($topPagesData as $tp):
                $path = parse_url($tp['page_url'], PHP_URL_PATH);
                if (!$path) $path = '/';
            ?>
            <tr style="border-bottom:1px solid #1e1e1c">
                <td style="padding:5px 4px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo htmlspecialchars($tp['page_url']); ?>">
                    <?php echo htmlspecialchars($path); ?>
                </td>
                <td style="padding:5px 4px;text-align:right;color:#fff;font-weight:600"><?php echo (int)$tp['views']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title">Devices (7 Days)</div>
        <?php if (empty($deviceLabels)): ?>
            <p style="color:#888;font-size:13px">No data yet.</p>
        <?php else: ?>
        <div style="position:relative;height:180px"><canvas id="deviceChart"></canvas></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title">Browsers (7 Days)</div>
        <?php if (empty($browserLabels)): ?>
            <p style="color:#888;font-size:13px">No data yet.</p>
        <?php else: ?>
        <div style="position:relative;height:180px"><canvas id="browserChart"></canvas></div>
        <?php endif; ?>
    </div>

</div>

<!-- Today's sessions table -->
<div class="card" style="margin-top:0">
    <div class="card-title">Today's Sessions</div>
    <?php if (empty($sessionRows)): ?>
        <p style="color:#888;font-size:13px">No sessions today yet. Make sure tracker-snippet.js is in your header.php.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table style="font-size:13px">
            <thead>
                <tr>
                    <th>#</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Device</th>
                    <th>Browser</th>
                    <th>OS</th>
                    <th>Pages</th>
                    <th>Duration</th>
                    <th>First Seen</th>
                    <th>Last Seen</th>
                    <th>Journey</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sessionRows as $i => $sess):
                $key    = $sess['session_key'];
                $pvList = isset($pvsMap[$key]) ? $pvsMap[$key] : array();
                $isNew  = (int)$sess['is_new_visitor'];
                $badge  = $isNew
                    ? '<span style="background:#1a472a;color:#6fcf97;font-size:10px;padding:1px 6px;border-radius:8px">NEW</span>'
                    : '<span style="background:#2c2a1e;color:#f2c94c;font-size:10px;padding:1px 6px;border-radius:8px">RETURN</span>';
                $ip     = $sess['ip_address'] ? $sess['ip_address'] : '—';
                $dur    = formatDur($sess['duration_sec']);
                $icon   = deviceIcon($sess['device']);
                $firstT = date('H:i:s', strtotime($sess['first_seen']));
                $lastT  = date('H:i:s', strtotime($sess['last_seen']));
            ?>
            <tr>
                <td style="color:#555"><?php echo $i + 1; ?></td>
                <td>
                    <code style="background:#111;padding:2px 6px;border-radius:3px;font-size:12px;color:#9ebffe">
                        <?php echo htmlspecialchars($ip); ?>
                    </code>
                </td>
                <td><?php echo $badge; ?></td>
                <td><?php echo $icon . ' ' . ucfirst($sess['device']); ?></td>
                <td style="color:#ccc"><?php echo htmlspecialchars($sess['browser'] ?: '—'); ?></td>
                <td style="color:#ccc"><?php echo htmlspecialchars($sess['os'] ?: '—'); ?></td>
                <td style="text-align:center;font-weight:600;color:#fff"><?php echo (int)$sess['page_count']; ?></td>
                <td style="color:#888"><?php echo $dur; ?></td>
                <td style="color:#888;white-space:nowrap"><?php echo $firstT; ?></td>
                <td style="color:#888;white-space:nowrap"><?php echo $lastT; ?></td>
                <td>
                    <?php if (!empty($pvList)):
                        foreach ($pvList as $pi => $pv):
                            $path = parse_url($pv['page_url'], PHP_URL_PATH);
                            if (!$path) $path = '/';
                    ?>
                        <?php if ($pi > 0): ?><span style="color:#444;margin:0 2px">›</span><?php endif; ?>
                        <span title="<?php echo htmlspecialchars($pv['page_url']); ?> &#10;<?php echo htmlspecialchars($pv['visited_at']); ?>"
                              style="display:inline-block;background:#1a1a18;border:1px solid #2c2c2a;border-radius:3px;padding:2px 6px;font-size:11px;color:#e0e0e0;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle">
                            <?php echo htmlspecialchars($path); ?>
                        </span>
                    <?php endforeach; endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- User agent tooltip modal -->
<div id="uaOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:#1a1a18;border:1px solid #2c2c2a;border-radius:8px;padding:20px 24px;max-width:520px;width:95vw">
        <div style="color:#9ebffe;font-weight:600;margin-bottom:10px">User Agent</div>
        <div id="uaText" style="color:#ccc;font-size:12px;word-break:break-all;line-height:1.6"></div>
        <button onclick="document.getElementById('uaOverlay').style.display='none'" style="margin-top:16px;background:#2a78d6;color:#fff;border:none;padding:6px 18px;border-radius:4px;cursor:pointer">Close</button>
    </div>
</div>

</div><!-- /content -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
(function(){
    var grid  = '#2c2c2a';
    var label = '#888';
    var colors = ['#2a78d6','#6fcf97','#f2c94c','#eb5757','#bb87fc','#56ccf2'];

    // Daily line chart
    var dates = <?php echo $jDates; ?>;
    var sess  = <?php echo $jSess; ?>;
    var newV  = <?php echo $jNew; ?>;
    if (dates.length && document.getElementById('dailyChart')) {
        new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    { label: 'All Sessions', data: sess, borderColor: '#2a78d6', backgroundColor: 'rgba(42,120,214,0.1)', tension: 0.35, fill: true, pointRadius: 4, pointBackgroundColor: '#2a78d6' },
                    { label: 'New Visitors', data: newV, borderColor: '#6fcf97', backgroundColor: 'rgba(111,207,151,0.08)', tension: 0.35, fill: true, pointRadius: 4, pointBackgroundColor: '#6fcf97' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#ccc', font: { size: 12 } } } },
                scales: {
                    x: { ticks: { color: label, font: { size: 11 } }, grid: { color: grid } },
                    y: { beginAtZero: true, ticks: { color: label, stepSize: 1, precision: 0 }, grid: { color: grid } }
                }
            }
        });
    }

    // Device donut
    var dL = <?php echo $jDevL; ?>;
    var dC = <?php echo $jDevC; ?>;
    if (dL.length && document.getElementById('deviceChart')) {
        new Chart(document.getElementById('deviceChart'), {
            type: 'doughnut',
            data: { labels: dL, datasets: [{ data: dC, backgroundColor: colors, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#ccc', font: { size: 12 }, padding: 10 } } } }
        });
    }

    // Browser donut
    var bL = <?php echo $jBrwL; ?>;
    var bC = <?php echo $jBrwC; ?>;
    if (bL.length && document.getElementById('browserChart')) {
        new Chart(document.getElementById('browserChart'), {
            type: 'doughnut',
            data: { labels: bL, datasets: [{ data: bC, backgroundColor: colors, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#ccc', font: { size: 12 }, padding: 10 } } } }
        });
    }
})();
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>