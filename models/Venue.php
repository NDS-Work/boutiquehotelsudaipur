<?php
require_once __DIR__ . '/../config/database.php';

function getAllVenues() {
    global $conn;

    $result = $conn->query("SELECT * FROM venues");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getVenueBySlug($slug) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM venues WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getVenueFull($slug) {
    global $conn;

    $venue = getVenueBySlug($slug);
    if (!$venue) return null;

    $venue_id = $venue['id'];

    // Images
    $images = $conn->query("SELECT image_url FROM venue_images WHERE venue_id = $venue_id");
    $venue['images'] = $images->fetch_all(MYSQLI_ASSOC);

    // Amenities
    $amenities = $conn->query("
        SELECT a.name FROM amenities a
        JOIN venue_amenity va ON a.id = va.amenity_id
        WHERE va.venue_id = $venue_id
    ");
    $venue['amenities'] = $amenities->fetch_all(MYSQLI_ASSOC);

    return $venue;
}

function filterVenues($location = 'all', $budget = 'all', $capacity = 'all') {
    global $conn;

    $query = "SELECT * FROM venues WHERE 1=1";

    if ($location !== 'all') {
        $query .= " AND locationCategory = '$location'";
    }

    if ($budget !== 'all') {
        $query .= " AND priceRange = '$budget'";
    }

    if ($capacity !== 'all') {
        $query .= " AND capacityCategory = '$capacity'";
    }

    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

