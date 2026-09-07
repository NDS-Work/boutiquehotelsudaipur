<?php
require_once __DIR__ . '/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 0;
    if ($booking_id > 0 && $status_id > 0) {
        require_once __DIR__ . '/../data/db.php';
        $db = getDbConnection();
        $stmt = $db->prepare('UPDATE link_book SET status_id = :status_id WHERE id = :booking_id');
        $stmt->execute([
            ':status_id' => $status_id,
            ':booking_id' => $booking_id,
        ]);
    }
}
// Redirect back to bookings page
header('Location: booking.php');
exit;
