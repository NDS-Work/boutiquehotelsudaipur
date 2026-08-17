<?php
require_once __DIR__ . '/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 0;
    if ($booking_id > 0 && $status_id > 0) {
        $db = new SQLite3(__DIR__ . '/../data/new.sqlite.db');
        $stmt = $db->prepare('UPDATE link_book SET status_id = :status_id WHERE id = :booking_id');
        $stmt->bindValue(':status_id', $status_id, SQLITE3_INTEGER);
        $stmt->bindValue(':booking_id', $booking_id, SQLITE3_INTEGER);
        $stmt->execute();
    }
}
// Redirect back to bookings page
header('Location: booking.php');
exit;
