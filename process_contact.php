<?php
// process_contact.php
// Handles contact form submission, stores in SQLite, and collects UTM/IP info

// Helper: Get value from POST or fallback to empty string
function get_post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

// Collect form data
$name = get_post('name');
$email = get_post('email');
$phone = get_post('phone');
$venue_name = get_post('venue_name');
$message = get_post('message');

// UTM parameters (from hidden fields or fallback to empty)
$utm_source = get_post('utm_source');
$utm_medium = get_post('utm_medium');
$utm_campaign = get_post('utm_campaign');
$utm_term = get_post('utm_term');
$utm_content = get_post('utm_content');

// IP address
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

// Get city from IP using ip-api.com
$ip_city = '';
if ($ip_address) {
    $geo = @file_get_contents("http://ip-api.com/json/" . $ip_address);
    if ($geo) {
        $geo = json_decode($geo, true);
        $ip_city = $geo['city'] ?? '';
    }
}

// Validate required fields
if (!$name || !$email || !$message) {
    header('Location: contact.php?error=missing');
    exit;
}

// Load .env
require_once __DIR__ . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->load();

try {
    $db = new SQLite3(__DIR__ . '/data/new.sqlite.db');
    $db->busyTimeout(3000); // Wait up to 3 seconds for lock
    $stmt = $db->prepare('INSERT INTO link_inqueries (name, email, phone, venue_name, message, ip_address, ip_city, utm_source, utm_medium, utm_campaign, utm_term, utm_content) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $name);
    $stmt->bindValue(2, $email);
    $stmt->bindValue(3, $phone);
    $stmt->bindValue(4, $venue_name);
    $stmt->bindValue(5, $message);
    $stmt->bindValue(6, $ip_address);
    $stmt->bindValue(7, $ip_city);
    $stmt->bindValue(8, $utm_source);
    $stmt->bindValue(9, $utm_medium);
    $stmt->bindValue(10, $utm_campaign);
    $stmt->bindValue(11, $utm_term);
    $stmt->bindValue(12, $utm_content);
        // Retry logic for database locked
        $maxRetries = 5;
        $retryDelay = 200000; // 0.2 seconds
        $result = false;
        for ($i = 0; $i < $maxRetries; $i++) {
            $result = $stmt->execute();
            if ($result !== false) break;
            if ($db->lastErrorCode() === 6) { // SQLITE_BUSY (database is locked)
                usleep($retryDelay);
            } else {
                break;
            }
        }
        if ($result) {
            if ($stmt) $stmt->close();
            if ($db) $db->close();
            // Send email using PHPMailer and .env variables
            try {
                ini_set('log_errors', '1');
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->SMTPDebug  = 0;
                $mail->Debugoutput = function($str, $level) {
                    error_log('[PHPMailer] ' . $str);
                };
                $mail->Host = $_ENV['SMTP_HOST'];
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['SMTP_USER'];
                $mail->Password = $_ENV['SMTP_PASS'];
                $mail->SMTPSecure = 'tls';
                $mail->Port = (int)$_ENV['SMTP_PORT'];
                $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
                foreach (array_map('trim', explode(',', $_ENV['SMTP_TO'])) as $recipient) {
                    if ($recipient) $mail->addAddress($recipient);
                }
                $mail->addReplyTo($email, $name);
                $mail->Subject = 'New Contact Inquiry from ' . $name;
                $mail->isHTML(true);
                $mail->Body = '<h2>New Inquiry Received</h2>' .
                    '<p><strong>Name:</strong> ' . htmlspecialchars($name) . '</p>' .
                    '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>' .
                    '<p><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</p>' .
                    '<p><strong>Venue:</strong> ' . htmlspecialchars($venue_name) . '</p>' .
                    '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>' .
                    '<p><strong>IP Address:</strong> ' . htmlspecialchars($ip_address) . ' (' . htmlspecialchars($ip_city) . ')</p>' .
                    '<p><strong>UTM Source:</strong> ' . htmlspecialchars($utm_source) . '<br>' .
                    '<strong>UTM Medium:</strong> ' . htmlspecialchars($utm_medium) . '<br>' .
                    '<strong>UTM Campaign:</strong> ' . htmlspecialchars($utm_campaign) . '<br>' .
                    '<strong>UTM Term:</strong> ' . htmlspecialchars($utm_term) . '<br>' .
                    '<strong>UTM Content:</strong> ' . htmlspecialchars($utm_content) . '</p>';
                $mail->AltBody = "New Inquiry Received\n" .
                    "Name: $name\n" .
                    "Email: $email\n" .
                    "Phone: $phone\n" .
                    "Venue: $venue_name\n" .
                    "Message: $message\n" .
                    "IP Address: $ip_address ($ip_city)\n" .
                    "UTM Source: $utm_source\n" .
                    "UTM Medium: $utm_medium\n" .
                    "UTM Campaign: $utm_campaign\n" .
                    "UTM Term: $utm_term\n" .
                    "UTM Content: $utm_content\n";
                $mail->send();
                error_log('[PHPMailer] Email sent successfully to ' . $_ENV['SMTP_TO']);
            } catch (PHPMailer\PHPMailer\Exception $e) {
                error_log('[PHPMailer] Send failed: ' . $e->getMessage());
            }
            if (!headers_sent()) {
                header('Location: contact.php?success=1');
                exit;
            }
        } else {
            if ($stmt) $stmt->close();
            if ($db) $db->close();
            if (!headers_sent()) {
                header('Location: contact.php?error=db');
                exit;
            }
        }

// (duplicate email block removed — handled above)
} catch (Exception $e) {
    header('Location: contact.php?error=db');
    exit;
}
