<?php
// process_booking.php: Handles AJAX booking form submission
header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->load();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Validate and sanitize input

$guest_name = trim($_POST['guest_name'] ?? '');
$guest_count = (int)($_POST['guest_count'] ?? 0);
$email = trim($_POST['email'] ?? '');
$hotel_name = trim($_POST['hotel_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$daterange = trim($_POST['daterange'] ?? '');


if (!$guest_name || !$guest_count || !$email || !$hotel_name || !$phone || !$daterange) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Parse date range (format: YYYY-MM-DD to YYYY-MM-DD or YYYY-MM-DD,YYYY-MM-DD)
$dates = preg_split('/\s*to\s*|,/', $daterange);
$check_in_date = $dates[0] ?? '';
$check_out_date = $dates[1] ?? '';
if (!$check_in_date || !$check_out_date) {
    echo json_encode(['success' => false, 'message' => 'Invalid date range.']);
    exit;
}

// UTM parameters
$utm_source   = isset($_POST['utm_source'])   ? trim($_POST['utm_source'])   : '';
$utm_medium   = isset($_POST['utm_medium'])   ? trim($_POST['utm_medium'])   : '';
$utm_campaign = isset($_POST['utm_campaign']) ? trim($_POST['utm_campaign']) : '';
$utm_term     = isset($_POST['utm_term'])     ? trim($_POST['utm_term'])     : '';
$utm_content  = isset($_POST['utm_content'])  ? trim($_POST['utm_content'])  : '';

// IP address
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

// Get city from IP using ip-api.com
$ip_city = '';
if ($ip_address) {
    $geo = @file_get_contents('http://ip-api.com/json/' . $ip_address);
    if ($geo) {
        $geo = json_decode($geo, true);
        $ip_city = $geo['city'] ?? '';
    }
}

try {
    $db = new SQLite3(__DIR__ . '/data/new.sqlite.db');
    // Set default status_id to 1 (Pending)
    $status_id = 1;
    $stmt = $db->prepare('INSERT INTO link_book (guest_name, guest_count, email, phone_number, check_in_date, check_out_date, hotel_name, status_id, ip_address, ip_city, utm_source, utm_medium, utm_campaign, utm_term, utm_content) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $guest_name, SQLITE3_TEXT);
    $stmt->bindValue(2, $guest_count, SQLITE3_INTEGER);
    $stmt->bindValue(3, $email, SQLITE3_TEXT);
    $stmt->bindValue(4, $phone, SQLITE3_TEXT);
    $stmt->bindValue(5, $check_in_date, SQLITE3_TEXT);
    $stmt->bindValue(6, $check_out_date, SQLITE3_TEXT);
    $stmt->bindValue(7, $hotel_name, SQLITE3_TEXT);
    $stmt->bindValue(8, $status_id, SQLITE3_INTEGER);
    $stmt->bindValue(9, $ip_address, SQLITE3_TEXT);
    $stmt->bindValue(10, $ip_city, SQLITE3_TEXT);
    $stmt->bindValue(11, $utm_source, SQLITE3_TEXT);
    $stmt->bindValue(12, $utm_medium, SQLITE3_TEXT);
    $stmt->bindValue(13, $utm_campaign, SQLITE3_TEXT);
    $stmt->bindValue(14, $utm_term, SQLITE3_TEXT);
    $stmt->bindValue(15, $utm_content, SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result) {
        // Send email notification
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug  = 0;
            $mail->Debugoutput = function($str, $level) {
                error_log('[PHPMailer] ' . $str);
            };
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];
            $mail->Password   = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = 'tls';
            $mail->Port       = (int)$_ENV['SMTP_PORT'];
            $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            foreach (array_map('trim', explode(',', $_ENV['SMTP_TO'])) as $recipient) {
                if ($recipient) $mail->addAddress($recipient);
            }
            $mail->addReplyTo($email, $guest_name);
            $mail->Subject = 'New Booking Request from ' . $guest_name;
            $mail->isHTML(true);
            $mail->Body =
                '<h2>New Booking Request</h2>' .
                '<p><strong>Name:</strong> ' . htmlspecialchars($guest_name) . '</p>' .
                '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>' .
                '<p><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</p>' .
                '<p><strong>Hotel:</strong> ' . htmlspecialchars($hotel_name) . '</p>' .
                '<p><strong>Guests:</strong> ' . htmlspecialchars($guest_count) . '</p>' .
                '<p><strong>Check-in:</strong> ' . htmlspecialchars($check_in_date) . '</p>' .
                '<p><strong>Check-out:</strong> ' . htmlspecialchars($check_out_date) . '</p>' .
                '<p><strong>IP Address:</strong> ' . htmlspecialchars($ip_address) . ' (' . htmlspecialchars($ip_city) . ')</p>' .
                '<p><strong>UTM Source:</strong> ' . htmlspecialchars($utm_source) . '<br>' .
                '<strong>UTM Medium:</strong> ' . htmlspecialchars($utm_medium) . '<br>' .
                '<strong>UTM Campaign:</strong> ' . htmlspecialchars($utm_campaign) . '<br>' .
                '<strong>UTM Term:</strong> ' . htmlspecialchars($utm_term) . '<br>' .
                '<strong>UTM Content:</strong> ' . htmlspecialchars($utm_content) . '</p>';
            $mail->AltBody =
                "New Booking Request\n" .
                "Name: $guest_name\nEmail: $email\nPhone: $phone\n" .
                "Hotel: $hotel_name\nGuests: $guest_count\n" .
                "Check-in: $check_in_date\nCheck-out: $check_out_date\n" .
                "IP: $ip_address ($ip_city)\n" .
                "UTM Source: $utm_source\nUTM Medium: $utm_medium\n" .
                "UTM Campaign: $utm_campaign\nUTM Term: $utm_term\nUTM Content: $utm_content\n";
            $mail->send();
            error_log('[PHPMailer] Booking email sent successfully');
        } catch (PHPMailer\PHPMailer\Exception $e) {
            error_log('[PHPMailer] Booking email failed: ' . $e->getMessage());
        }
        echo json_encode(['success' => true, 'message' => 'Booking successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save booking.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
