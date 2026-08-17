<?php

declare(strict_types=1);

require_once __DIR__ . '/venues.php';

function adminVenueLocationOptions(): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }
    $stmt = $db->query(
        'SELECT DISTINCT locality_name FROM link_hotels'
        . ' WHERE locality_name IS NOT NULL AND locality_name != \'\''
        . ' ORDER BY locality_name'
    );
    $labels = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $options = [];
    foreach ($labels as $i => $label) {
        $options[] = ['id' => $i + 1, 'label' => (string) $label];
    }
    return $options;
}

function adminVenuePriceOptions(): array {
    return [
        ['id' => 1, 'label' => 'Budget-Friendly'],
        ['id' => 2, 'label' => 'Mid-Range'],
        ['id' => 3, 'label' => 'Premium Luxury'],
        ['id' => 4, 'label' => 'Ultra-Luxury'],
    ];
}

function adminVenueCapacityOptions(): array {
    return [
        ['id' => 1, 'label' => 'Intimate (Up to 150)'],
        ['id' => 2, 'label' => 'Medium (150-400)'],
        ['id' => 3, 'label' => 'Large (400-800)'],
        ['id' => 4, 'label' => 'Grand (800+)'],
    ];
}

function adminVenueTypeOptions(): array {
    return [
        ['id' => 1, 'label' => 'Heritage Palace'],
        ['id' => 2, 'label' => 'Lakeside'],
        ['id' => 3, 'label' => 'Luxury Resort'],
        ['id' => 4, 'label' => 'Hilltop & Mountain View'],
        ['id' => 5, 'label' => 'Contemporary Luxury Hotel'],
        ['id' => 6, 'label' => 'Boutique & Intimate'],
    ];
}

function adminAmenityOptions(): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }

    $stmt = $db->query('SELECT id, name FROM link_amenities WHERE is_active = 1 ORDER BY name');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function adminAttractionOptions(): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }

    $stmt = $db->query('SELECT id, attraction_name as name FROM link_attraction ORDER BY attraction_name');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function adminVenueDefaults(): array {
    return [
        'id' => null,
        'name' => '',
        'slug' => '',
        'location_label' => '',
        'location_category_id' => '',
        'price_range_id' => '',
        'capacity_category_id' => '',
        'city_name' => 'Udaipur',
        'state_name' => 'Rajasthan',
        'country_code' => 'IN',
        'latitude' => '',
        'longitude' => '',
        'distance_text' => '',
        'hotel_details_link' => '',
        'description' => '',
        'star_rating' => '',
        'google_rating' => '',
        'google_user_ratings_total' => '',
        'base_price' => '',
        'taxes' => '',
        'discounted_price' => '',
        'discount_percentage' => '',
        'reward_points' => '',
        'is_per_night' => 1,
        'dnd_status' => 0,
        'price_per_plate' => '',
        'package_cost' => '',
        'capacity_min' => '',
        'capacity_max' => '',
        'rooms' => '',
        'acres' => '',
        'highlighted' => 0,
        'venue_types' => [],
        'selected_amenity_ids' => [],
        'selected_attraction_ids' => [],
        'custom_amenities' => '',
        'amenities' => '',
        'offers' => '',
        'image_entries' => '',
        'room_rates_json' => "[]",
    ];
}

function _adminVenueTypeIdMap(): array {
    $map = [];
    foreach (adminVenueTypeOptions() as $type) {
        $map[$type['label']] = (string) $type['id'];
    }
    return $map;
}

function _adminParseImageEntries(string $text): array {
    $entries = [];
    foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $isFeatured = false;
        if (str_starts_with($line, '*')) {
            $isFeatured = true;
            $line = trim(substr($line, 1));
        }

        $parts = array_map('trim', explode('|', $line, 2));
        $url = $parts[0] ?? '';
        if ($url === '') {
            continue;
        }

        $entries[] = [
            'url' => $url,
            'caption' => $parts[1] ?? '',
            'is_featured' => $isFeatured,
        ];
    }
    return $entries;
}

function _adminSerializeImageEntries(array $rows): string {
    $lines = [];
    foreach ($rows as $row) {
        $url = trim((string) ($row['image_url'] ?? ''));
        if ($url === '') {
            continue;
        }
        $caption = trim((string) ($row['caption'] ?? ''));
        $isFeatured = ((int) ($row['image_position'] ?? 9999)) === 0;
        $line = $caption !== '' ? ($url . ' | ' . $caption) : $url;
        $lines[] = $isFeatured ? ('* ' . $line) : $line;
    }
    return implode("\n", $lines);
}

function _adminEnsureAmenityId(PDO $db, string $name): int {
    $name = trim($name);
    if ($name === '') {
        throw new InvalidArgumentException('Amenity name cannot be empty.');
    }

    $slug = _slugify($name);
    if ($slug === '') {
        throw new InvalidArgumentException('Amenity name could not be slugified: ' . $name);
    }

    $stmt = $db->prepare('SELECT id FROM link_amenities WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $existingId = $stmt->fetchColumn();
    if ($existingId !== false) {
        return (int) $existingId;
    }

    $insert = $db->prepare('INSERT INTO link_amenities (name, slug, is_active, icon, icon_type_name) VALUES (?, ?, 1, NULL, NULL)');
    $insert->execute([$name, $slug]);
    return (int) $db->lastInsertId();
}

function _adminSyncHotelAmenityLinks(PDO $db, int $hotelId, array $amenityNames): void {
    $db->prepare('DELETE FROM link_hotel_amenities WHERE hotel_id = ?')->execute([$hotelId]);
    $linkStmt = $db->prepare('INSERT OR IGNORE INTO link_hotel_amenities (hotel_id, amenity_id) VALUES (?, ?)');

    $seen = [];
    foreach ($amenityNames as $name) {
        $name = trim((string) $name);
        if ($name === '') {
            continue;
        }

        $canonical = mb_strtolower($name);
        if (isset($seen[$canonical])) {
            continue;
        }
        $seen[$canonical] = true;

        $amenityId = _adminEnsureAmenityId($db, $name);
        $linkStmt->execute([$hotelId, $amenityId]);
    }
}

function _adminNormalizeRoomRates(string $json): array {
    $json = trim($json);
    if ($json === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Room rates JSON must be a valid JSON array.');
    }

    $rates = [];
    foreach ($decoded as $rate) {
        if (!is_array($rate)) {
            continue;
        }
        $rates[] = [
            'refundable' => $rate['refundable'] ?? '',
            'coupon_code' => $rate['coupon_code'] ?? '',
            'pre_applied_offer_discount' => $rate['pre_applied_offer_discount'] ?? null,
            'pre_applied_offer_discount_per_night' => $rate['pre_applied_offer_discount_per_night'] ?? null,
            'coupon_description' => $rate['coupon_description'] ?? '',
            'coupon_description_per_night' => $rate['coupon_description_per_night'] ?? '',
            'discount_percentage' => $rate['discount_percentage'] ?? null,
            'total_amount_per_night' => $rate['total_amount_per_night'] ?? null,
            'total_tax_per_night' => $rate['total_tax_per_night'] ?? null,
            'total_discount_per_night' => $rate['total_discount_per_night'] ?? null,
            'discounted_price' => $rate['discounted_price'] ?? null,
            'discounted_price_per_night' => $rate['discounted_price_per_night'] ?? null,
            'reward_points' => $rate['reward_points'] ?? null,
            'inclusions_json' => $rate['inclusions'] ?? ($rate['inclusions_json'] ?? []),
        ];
    }

    return $rates;
}

function _adminFindHotelRow(PDO $db, string $slug): ?array {
    $stmt = $db->prepare('SELECT * FROM link_hotels WHERE hotel_slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if ($row) {
        return $row;
    }

    $stmt = $db->query('SELECT * FROM link_hotels');
    foreach ($stmt->fetchAll() as $candidate) {
        $generated = _slugify((string) ($candidate['name'] ?? '') . '-' . (int) $candidate['id']);
        if ($generated === $slug) {
            return $candidate;
        }
    }

    return null;
}

function adminLoadVenueFormData(string $slug): ?array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return null;
    }

    $row = _adminFindHotelRow($db, $slug);
    if (!$row) {
        return null;
    }

    $meta = _extractVenueMeta($row);
    $defaults = adminVenueDefaults();
    $typeMap = _adminVenueTypeIdMap();

    $imagesStmt = $db->prepare('SELECT image_url, caption, image_position FROM link_hotel_images WHERE hotel_id = ? ORDER BY image_position, url_position');
    $imagesStmt->execute([(int) $row['id']]);
    $imageRows = $imagesStmt->fetchAll();

    $amenitiesStmt = $db->prepare('SELECT inclusion_text FROM link_hotel_inclusions WHERE hotel_id = ? ORDER BY position_in_hotel');
    $amenitiesStmt->execute([(int) $row['id']]);
    $amenities = $amenitiesStmt->fetchAll(PDO::FETCH_COLUMN);

    $offersStmt = $db->prepare('SELECT offer_text FROM link_hotel_offers WHERE hotel_id = ? ORDER BY position_in_hotel');
    $offersStmt->execute([(int) $row['id']]);
    $offers = $offersStmt->fetchAll(PDO::FETCH_COLUMN);

    $selectedAmenityIdsStmt = $db->prepare('SELECT amenity_id FROM link_hotel_amenities WHERE hotel_id = ?');
    $selectedAmenityIdsStmt->execute([(int) $row['id']]);
    $selectedAmenityIds = array_map('intval', $selectedAmenityIdsStmt->fetchAll(PDO::FETCH_COLUMN));

    $selectedAttractionIdsStmt = $db->prepare('SELECT attraction_id FROM link_attraction_hotel WHERE hotel_id = ?');
    $selectedAttractionIdsStmt->execute([(int) $row['id']]);
    $selectedAttractionIds = array_map('intval', $selectedAttractionIdsStmt->fetchAll(PDO::FETCH_COLUMN));

    $selectedAmenityNames = [];
    if (!empty($selectedAmenityIds)) {
        $placeholders = implode(',', array_fill(0, count($selectedAmenityIds), '?'));
        $nameStmt = $db->prepare('SELECT name FROM link_amenities WHERE id IN (' . $placeholders . ') ORDER BY name');
        $nameStmt->execute($selectedAmenityIds);
        $selectedAmenityNames = $nameStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    $ratesStmt = $db->prepare('SELECT * FROM link_hotel_room_rates WHERE hotel_id = ? ORDER BY position_in_hotel');
    $ratesStmt->execute([(int) $row['id']]);
    $rateRows = $ratesStmt->fetchAll();
    foreach ($rateRows as &$rateRow) {
        $rateRow['inclusions'] = json_decode((string) ($rateRow['inclusions_json'] ?? '[]'), true);
        unset($rateRow['raw_json'], $rateRow['hotel_id'], $rateRow['position_in_hotel'], $rateRow['inclusions_json'], $rateRow['id']);
    }
    unset($rateRow);

    $venueTypeIds = [];
    foreach (($meta['venue_types'] ?? []) as $label) {
        if (isset($typeMap[$label])) {
            $venueTypeIds[] = $typeMap[$label];
        }
    }

    $customAmenityLines = [];
    foreach ($amenities as $amenity) {
        if (!in_array($amenity, $selectedAmenityNames, true)) {
            $customAmenityLines[] = $amenity;
        }
    }

    return array_merge($defaults, [
        'id' => (int) $row['id'],
        'name' => (string) ($row['name'] ?? ''),
        'slug' => (string) ($row['hotel_slug'] ?? ''),
        'location_label' => (string) ($row['locality_name'] ?? ''),
        'location_category_id' => (string) ($meta['location_category_id'] ?? ''),
        'price_range_id' => (string) ($meta['price_range_id'] ?? ''),
        'capacity_category_id' => (string) ($meta['capacity_category_id'] ?? ''),
        'city_name' => (string) ($row['city_name'] ?? ''),
        'state_name' => (string) ($row['state_name'] ?? ''),
        'country_code' => (string) ($row['country_code'] ?? ''),
        'latitude' => (string) ($row['latitude'] ?? ''),
        'longitude' => (string) ($row['longitude'] ?? ''),
        'distance_text' => (string) ($row['distance_text'] ?? ''),
        'hotel_details_link' => (string) ($row['hotel_details_link'] ?? ''),
        'description' => (string) ($meta['description'] ?? ''),
        'star_rating' => (string) ($row['star_rating'] ?? ''),
        'google_rating' => (string) ($row['google_rating'] ?? ''),
        'google_user_ratings_total' => (string) ($row['google_user_ratings_total'] ?? ''),
        'base_price' => (string) ($row['base_price'] ?? ''),
        'taxes' => (string) ($row['taxes'] ?? ''),
        'discounted_price' => (string) ($row['discounted_price'] ?? ''),
        'discount_percentage' => (string) ($row['discount_percentage'] ?? ''),
        'reward_points' => (string) ($row['reward_points'] ?? ''),
        'is_per_night' => (int) ($row['is_per_night'] ?? 1),
        'dnd_status' => (int) ($row['dnd_status'] ?? 0),
        'price_per_plate' => (string) ($meta['price_per_plate'] ?? ''),
        'package_cost' => (string) ($meta['package_cost'] ?? ''),
        'capacity_min' => (string) ($meta['capacity_min'] ?? ''),
        'capacity_max' => (string) ($meta['capacity_max'] ?? ''),
        'rooms' => (string) ($meta['rooms'] ?? ''),
        'acres' => (string) ($meta['acres'] ?? ''),
        'highlighted' => (int) ($meta['highlighted'] ?? 0),
        'venue_types' => $venueTypeIds,
        'selected_amenity_ids' => $selectedAmenityIds,
        'selected_attraction_ids' => $selectedAttractionIds,
        'custom_amenities' => implode("\n", array_map('strval', $customAmenityLines)),
        'amenities' => implode("\n", array_map('strval', $amenities)),
        'offers' => implode("\n", array_map('strval', $offers)),
        'image_entries' => _adminSerializeImageEntries($imageRows),
        'room_rates_json' => json_encode($rateRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    ]);
}

function adminSaveVenue(array $post, array $files, ?int $existingId = null): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return ['success' => false, 'error' => 'SQLite database is unavailable.'];
    }

    $typeOptions = [];
    foreach (adminVenueTypeOptions() as $type) {
        $typeOptions[(string) $type['id']] = $type['label'];
    }

    $name = trim((string) ($post['name'] ?? ''));
    $slug = _slugify((string) ($post['slug'] ?? ''));
    $locationLabel = trim((string) ($post['location_label'] ?? ''));
    $pricePerPlate = (int) ($post['price_per_plate'] ?? 0);

    if ($name === '' || $slug === '' || $locationLabel === '') {
        return ['success' => false, 'error' => 'Name, slug, and location label are required.'];
    
    }

    $existingMeta = [];
    $existingRow = null;
    if ($existingId !== null) {
        $stmt = $db->prepare('SELECT * FROM link_hotels WHERE id = ? LIMIT 1');
        $stmt->execute([$existingId]);
        $existingRow = $stmt->fetch();
        if (!$existingRow) {
            return ['success' => false, 'error' => 'Venue not found.'];
        }
        $existingMeta = _extractVenueMeta($existingRow);
    }

    $slugStmt = $db->prepare('SELECT id FROM link_hotels WHERE hotel_slug = ? LIMIT 1');
    $slugStmt->execute([$slug]);
    $slugOwner = $slugStmt->fetchColumn();
    if ($slugOwner && (int) $slugOwner !== (int) $existingId) {
        return ['success' => false, 'error' => 'Slug already exists. Choose a different slug.'];
    }

    try {
        $roomRates = _adminNormalizeRoomRates((string) ($post['room_rates_json'] ?? '[]'));

        $venueTypeLabels = [];
        foreach ((array) ($post['venue_types'] ?? []) as $typeId) {
            $typeId = (string) $typeId;
            if (isset($typeOptions[$typeId])) {
                $venueTypeLabels[] = $typeOptions[$typeId];
            }
        }

        $meta = $existingMeta;
        $meta['description'] = trim((string) ($post['description'] ?? ''));
        $meta['capacity_min'] = (int) ($post['capacity_min'] ?? 0);
        $meta['capacity_max'] = (int) ($post['capacity_max'] ?? 0);
        $meta['rooms'] = (int) ($post['rooms'] ?? 0);
        $meta['acres'] = ($post['acres'] ?? '') === '' ? null : (float) $post['acres'];
        $meta['venue_types'] = $venueTypeLabels;
        $meta['price_per_plate'] = $pricePerPlate > 0 ? $pricePerPlate : null;
        $meta['package_cost'] = trim((string) ($post['package_cost'] ?? ''));
        $meta['highlighted'] = isset($post['highlighted']);
        $meta['location_category_id'] = (int) ($post['location_category_id'] ?? 0);
        $meta['price_range_id'] = (int) ($post['price_range_id'] ?? 0);
        $meta['capacity_category_id'] = (int) ($post['capacity_category_id'] ?? 0);

        $rawAmenityInput = $post['amenities'] ?? [];
        $selectedAmenityIds = [];
        if (is_array($rawAmenityInput)) {
            $selectedAmenityIds = array_values(array_filter(array_map('intval', $rawAmenityInput), static fn($id) => $id > 0));
        }

        $rawAttractionInput = $post['attractions'] ?? [];
        $selectedAttractionIds = [];
        if (is_array($rawAttractionInput)) {
            $selectedAttractionIds = array_values(array_filter(array_map('intval', $rawAttractionInput), static fn($id) => $id > 0));
        }

        $customAmenities = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($post['custom_amenities'] ?? '')))));
        if (!is_array($rawAmenityInput)) {
            $legacyAmenities = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $rawAmenityInput))));
            if (empty($selectedAmenityIds)) {
                $customAmenities = array_values(array_unique(array_merge($customAmenities, $legacyAmenities), SORT_STRING));
            }
        }

        $selectedAmenityNames = [];
        if (!empty($selectedAmenityIds)) {
            $placeholders = implode(',', array_fill(0, count($selectedAmenityIds), '?'));
            $nameStmt = $db->prepare('SELECT name FROM link_amenities WHERE id IN (' . $placeholders . ') ORDER BY name');
            $nameStmt->execute($selectedAmenityIds);
            $selectedAmenityNames = $nameStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $amenityNamesToStore = array_values(array_unique(array_filter(array_merge($selectedAmenityNames, $customAmenities), static fn($value) => $value !== ''), SORT_STRING));

        $payload = [
            ':external_hotel_id' => $existingRow['external_hotel_id'] ?? ('admin-' . $slug . '-' . time()),
            ':name' => $name,
            ':hotel_slug' => $slug,
            ':locality_name' => $locationLabel,
            ':latitude' => ($post['latitude'] ?? '') === '' ? null : (float) $post['latitude'],
            ':longitude' => ($post['longitude'] ?? '') === '' ? null : (float) $post['longitude'],
            ':city_name' => trim((string) ($post['city_name'] ?? 'Udaipur')),
            ':state_name' => trim((string) ($post['state_name'] ?? 'Rajasthan')),
            ':country_code' => strtoupper(trim((string) ($post['country_code'] ?? 'IN'))),
            ':star_rating' => trim((string) ($post['star_rating'] ?? '')),
            ':google_rating' => ($post['google_rating'] ?? '') === '' ? null : (float) $post['google_rating'],
            ':google_user_ratings_total' => ($post['google_user_ratings_total'] ?? '') === '' ? null : (int) $post['google_user_ratings_total'],
            ':base_price' => ($post['base_price'] ?? '') === '' ? null : (int) $post['base_price'],
            ':taxes' => ($post['taxes'] ?? '') === '' ? null : (int) $post['taxes'],
            ':discounted_price' => ($post['discounted_price'] ?? '') === '' ? null : (int) $post['discounted_price'],
            ':discount_percentage' => ($post['discount_percentage'] ?? '') === '' ? null : (int) $post['discount_percentage'],
            ':reward_points' => ($post['reward_points'] ?? '') === '' ? null : (int) $post['reward_points'],
            // ^ These will be overridden from the first room rate below if room rates are present
            ':is_per_night' => isset($post['is_per_night']) ? 1 : 0,
            ':dnd_status' => isset($post['dnd_status']) ? 1 : 0,
            ':hotel_details_link' => trim((string) ($post['hotel_details_link'] ?? '')),
            ':distance_text' => trim((string) ($post['distance_text'] ?? '')),
            ':highlighted' => $meta['highlighted'] ? 1 : 0,
            ':raw_json' => json_encode(array_merge((array) json_decode((string) ($existingRow['raw_json'] ?? '{}'), true), ['admin' => $meta]), JSON_UNESCAPED_SLASHES),
        ];

        // Sync hotel-level pricing columns from the first room rate.
        // This ensures that editing "Total Amount / Night", "Total Tax / Night",
        // "Discount %" and "Reward Points" in the Room Rates section is reflected
        // in the pricing overview shown on the venue detail page.
        if (!empty($roomRates)) {
            $firstRate = $roomRates[0];
            if ($firstRate['total_amount_per_night'] !== null) {
                $payload[':base_price'] = (int) $firstRate['total_amount_per_night'];
            }
            if ($firstRate['total_tax_per_night'] !== null) {
                $payload[':taxes'] = (int) $firstRate['total_tax_per_night'];
            }
            if ($firstRate['discounted_price'] !== null) {
                $payload[':discounted_price'] = (int) $firstRate['discounted_price'];
            }
            if ($firstRate['discounted_price_per_night'] !== null && $payload[':discounted_price'] === null) {
                $payload[':discounted_price'] = (int) $firstRate['discounted_price_per_night'];
            }
            if ($firstRate['discount_percentage'] !== null) {
                $payload[':discount_percentage'] = (int) $firstRate['discount_percentage'];
            }
            if ($firstRate['reward_points'] !== null) {
                $payload[':reward_points'] = (int) $firstRate['reward_points'];
            }
        }

        $db->beginTransaction();

        if ($existingId !== null) {
            $payload[':id'] = $existingId;
            $stmt = $db->prepare('UPDATE link_hotels SET external_hotel_id = :external_hotel_id, name = :name, hotel_slug = :hotel_slug, locality_name = :locality_name, latitude = :latitude, longitude = :longitude, city_name = :city_name, state_name = :state_name, country_code = :country_code, star_rating = :star_rating, google_rating = :google_rating, google_user_ratings_total = :google_user_ratings_total, base_price = :base_price, taxes = :taxes, discounted_price = :discounted_price, discount_percentage = :discount_percentage, reward_points = :reward_points, is_per_night = :is_per_night, dnd_status = :dnd_status, hotel_details_link = :hotel_details_link, distance_text = :distance_text, highlighted = :highlighted, raw_json = :raw_json WHERE id = :id');
            $stmt->execute($payload);
            $hotelId = $existingId;
        } else {
            $stmt = $db->prepare('INSERT INTO link_hotels (external_hotel_id, name, hotel_slug, locality_name, latitude, longitude, city_name, state_name, country_code, star_rating, google_rating, google_user_ratings_total, base_price, taxes, discounted_price, discount_percentage, reward_points, is_per_night, dnd_status, hotel_details_link, distance_text, highlighted, raw_json) VALUES (:external_hotel_id, :name, :hotel_slug, :locality_name, :latitude, :longitude, :city_name, :state_name, :country_code, :star_rating, :google_rating, :google_user_ratings_total, :base_price, :taxes, :discounted_price, :discount_percentage, :reward_points, :is_per_night, :dnd_status, :hotel_details_link, :distance_text, :highlighted, :raw_json)');
            $stmt->execute($payload);
            $hotelId = (int) $db->lastInsertId();
        }

        $db->prepare('DELETE FROM link_hotel_images WHERE hotel_id = ?')->execute([$hotelId]);
        $db->prepare('DELETE FROM link_hotel_inclusions WHERE hotel_id = ?')->execute([$hotelId]);
        $db->prepare('DELETE FROM link_hotel_offers WHERE hotel_id = ?')->execute([$hotelId]);
        $db->prepare('DELETE FROM link_hotel_room_rates WHERE hotel_id = ?')->execute([$hotelId]);
        $db->prepare('DELETE FROM link_hotel_amenities WHERE hotel_id = ?')->execute([$hotelId]);
        $db->prepare('DELETE FROM link_attraction_hotel WHERE hotel_id = ?')->execute([$hotelId]);

        $imageEntries = _adminParseImageEntries((string) ($post['image_entries'] ?? ''));
        $storedImages = [];
        $uploadDir = __DIR__ . '/../assets/uploads/venues/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!empty($files['featured_image']['tmp_name'])) {
            $ext = strtolower(pathinfo((string) $files['featured_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $filename = 'featured_' . $slug . '_' . time() . '.' . $ext;
                if (move_uploaded_file((string) $files['featured_image']['tmp_name'], $uploadDir . $filename)) {
                    $storedImages[] = ['url' => '/assets/uploads/venues/' . $filename, 'caption' => 'Featured image'];
                }
            }
        }

        $storedImages = array_merge($storedImages, $imageEntries);

        if (!empty($files['images']['name']) && is_array($files['images']['name'])) {
            foreach ($files['images']['tmp_name'] as $index => $tmpPath) {
                if (!$tmpPath) {
                    continue;
                }
                $ext = strtolower(pathinfo((string) $files['images']['name'][$index], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    continue;
                }
                $filename = 'gallery_' . $slug . '_' . time() . '_' . $index . '.' . $ext;
                if (move_uploaded_file((string) $tmpPath, $uploadDir . $filename)) {
                    $storedImages[] = ['url' => '/assets/uploads/venues/' . $filename, 'caption' => ''];
                }
            }
        }

        $featuredIndex = null;
        foreach ($storedImages as $i => $entry) {
            if (!empty($entry['is_featured'])) {
                $featuredIndex = $i;
                break;
            }
        }

        if ($featuredIndex !== null) {
            $featured = $storedImages[$featuredIndex];
            unset($storedImages[$featuredIndex]);
            $storedImages = array_values($storedImages);
            array_unshift($storedImages, $featured);
        }

        foreach ($storedImages as &$entry) {
            unset($entry['is_featured']);
        }
        unset($entry);

        $imageStmt = $db->prepare('INSERT INTO link_hotel_images (hotel_id, image_position, url_position, caption, image_url, raw_json) VALUES (?, ?, ?, ?, ?, NULL)');
        foreach (array_values($storedImages) as $index => $entry) {
            if (trim((string) ($entry['url'] ?? '')) === '') {
                continue;
            }
            $imageStmt->execute([$hotelId, $index, 0, trim((string) ($entry['caption'] ?? '')), trim((string) $entry['url'])]);
        }

        $amenityStmt = $db->prepare('INSERT INTO link_hotel_inclusions (hotel_id, position_in_hotel, inclusion_text) VALUES (?, ?, ?)');
        foreach ($amenityNamesToStore as $index => $amenity) {
            $amenityStmt->execute([$hotelId, $index, $amenity]);
        }

        $hotelAmenityLinkStmt = $db->prepare('INSERT OR IGNORE INTO link_hotel_amenities (hotel_id, amenity_id) VALUES (?, ?)');
        foreach ($selectedAmenityIds as $amenityId) {
            $hotelAmenityLinkStmt->execute([$hotelId, $amenityId]);
        }
        foreach (array_values(array_unique($customAmenities, SORT_STRING)) as $customAmenity) {
            if ($customAmenity === '') {
                continue;
            }
            $hotelAmenityLinkStmt->execute([$hotelId, _adminEnsureAmenityId($db, $customAmenity)]);
        }

        $attractionStmt = $db->prepare('INSERT OR IGNORE INTO link_attraction_hotel (hotel_id, attraction_id) VALUES (?, ?)');
        foreach ($selectedAttractionIds as $attractionId) {
            $attractionStmt->execute([$hotelId, $attractionId]);
        }

        $offerStmt = $db->prepare('INSERT INTO link_hotel_offers (hotel_id, position_in_hotel, offer_text) VALUES (?, ?, ?)');
        $offers = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($post['offers'] ?? '')))));
        foreach ($offers as $index => $offer) {
            $offerStmt->execute([$hotelId, $index, $offer]);
        }

        $rateStmt = $db->prepare('INSERT INTO link_hotel_room_rates (hotel_id, position_in_hotel, inclusions_json, refundable, coupon_code, pre_applied_offer_discount, pre_applied_offer_discount_per_night, coupon_description, coupon_description_per_night, discount_percentage, total_amount_per_night, total_tax_per_night, total_discount_per_night, discounted_price, discounted_price_per_night, reward_points, raw_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)');
        foreach ($roomRates as $index => $rate) {
            $rateStmt->execute([
                $hotelId,
                $index,
                json_encode($rate['inclusions_json'], JSON_UNESCAPED_SLASHES),
                (string) ($rate['refundable'] ?? ''),
                (string) ($rate['coupon_code'] ?? ''),
                $rate['pre_applied_offer_discount'],
                $rate['pre_applied_offer_discount_per_night'],
                (string) ($rate['coupon_description'] ?? ''),
                (string) ($rate['coupon_description_per_night'] ?? ''),
                $rate['discount_percentage'],
                $rate['total_amount_per_night'],
                $rate['total_tax_per_night'],
                $rate['total_discount_per_night'],
                $rate['discounted_price'],
                $rate['discounted_price_per_night'],
                $rate['reward_points'],
            ]);
        }

        $db->commit();
        return ['success' => true, 'slug' => $slug, 'id' => $hotelId];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'error' => $e->getMessage()];
    }
}