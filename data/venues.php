<?php

declare(strict_types=1);

function _sqlPath(): string {
    return __DIR__ . '/new.sqlite.sql';
}

function _dbPath(): string {
    return __DIR__ . '/new.sqlite.db';
}

function _initSqliteFromDump(PDO $db): void {
    $sqlFile = _sqlPath();
    if (!is_file($sqlFile)) {
        throw new RuntimeException('Missing SQLite SQL dump at data/new.sqlite.sql');
    }

    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new RuntimeException('Unable to read SQLite SQL dump');
    }

    $db->exec($sql);
}

function _getVenueSqliteDb(): ?PDO {
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $dbPath = _dbPath();
        $sqlPath = _sqlPath();

        $needsBuild = !is_file($dbPath);
        if (!$needsBuild && is_file($sqlPath)) {
            $needsBuild = filemtime($dbPath) < filemtime($sqlPath);
        }

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_TIMEOUT, 30);
        $pdo->exec('PRAGMA busy_timeout = 30000');
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA synchronous = NORMAL');

        if ($needsBuild) {
            _initSqliteFromDump($pdo);
        }

        $pdo->query('SELECT 1 FROM link_hotels LIMIT 1');
        return $pdo;
    } catch (Throwable $e) {
        error_log('SQLite venue source unavailable: ' . $e->getMessage());
        $pdo = null;
        return null;
    }
}

function _slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

function _decodeJsonArray(?string $json): array {
    if ($json === null || trim($json) === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function _extractVenueMeta(array $row): array {
    $raw = _decodeJsonArray((string) ($row['raw_json'] ?? ''));
    $admin = $raw['admin'] ?? [];
    return is_array($admin) ? $admin : [];
}

function _findHotelRowBySlug(PDO $db, string $slug, bool $onlyActive = true): ?array {
    $activeClause = $onlyActive ? ' AND is_active = 1' : '';
    $stmt = $db->prepare('SELECT * FROM link_hotels WHERE hotel_slug = ?' . $activeClause . ' LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if ($row) {
        return $row;
    }

    $query = 'SELECT * FROM link_hotels';
    if ($onlyActive) {
        $query .= ' WHERE is_active = 1';
    }
    $stmt = $db->query($query);
    foreach ($stmt->fetchAll() as $candidate) {
        $generated = _slugify((string) ($candidate['name'] ?? '') . '-' . (int) $candidate['id']);
        if ($generated === $slug) {
            return $candidate;
        }
    }

    return null;
}

function _deriveLocationCategory(string $location): string {
    $s = strtolower($location);
    if (strpos($s, 'pichola') !== false || strpos($s, 'udaivilas') !== false || strpos($s, 'lake pichola') !== false) {
        return 'lake-pichola';
    }
    if (strpos($s, 'fateh') !== false || strpos($s, 'sagar') !== false) {
        return 'fateh-sagar';
    }
    return 'aravalli-hills';
}

function frontendLocationOptions(): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }
    $stmt = $db->query(
        'SELECT DISTINCT locality_name FROM link_hotels'
        . ' WHERE is_active = 1 AND locality_name IS NOT NULL AND locality_name != \'\''
        . ' ORDER BY locality_name'
    );
    $options = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $label) {
        $options[] = ['label' => (string) $label, 'slug' => _slugify((string) $label)];
    }
    return $options;
}

function _derivePriceRange(?float $price): string {
    if ($price === null || $price <= 0) {
        return 'budget-friendly';
    }
    if ($price <= 1500) {
        return 'budget-friendly';
    }
    if ($price <= 2200) {
        return 'mid-range';
    }
    if ($price <= 4000) {
        return 'premium-luxury';
    }
    return 'ultra-luxury';
}

function _deriveCapacityFallback(int $basePrice, float $rating): array {
    if ($basePrice >= 9000 || $rating >= 4.6) {
        return ['min' => 300, 'max' => 1200];
    }
    if ($basePrice >= 4000 || $rating >= 4.2) {
        return ['min' => 150, 'max' => 500];
    }
    return ['min' => 50, 'max' => 250];
}

function _capacityCategoryFromRange(int $min, int $max): string {
    if ($max >= 800) {
        return 'grand';
    }
    if ($max >= 400) {
        return 'large';
    }
    if ($max >= 150) {
        return 'medium';
    }
    return 'intimate';
}

function _deriveVenueType(array $row, array $meta): array {
    if (!empty($meta['venue_types']) && is_array($meta['venue_types'])) {
        return array_values(array_unique(array_map('strval', $meta['venue_types'])));
    }

    $types = [];
    $starRaw = (string) ($row['star_rating'] ?? '');
    $star = (float) preg_replace('/[^0-9.]/', '', $starRaw);

    if ($star >= 5) {
        $types[] = 'Heritage Palace';
    } elseif ($star >= 4) {
        $types[] = 'Luxury Resort';
    } else {
        $types[] = 'Boutique & Intimate';
    }

    $loc = strtolower((string) ($row['locality_name'] ?? ''));
    if (strpos($loc, 'lake') !== false || strpos($loc, 'pichola') !== false) {
        $types[] = 'Lakeside';
    }
    if ($star >= 4) {
        $types[] = 'Contemporary Luxury Hotel';
    }
    if (strpos($loc, 'hill') !== false || strpos($loc, 'aravalli') !== false) {
        $types[] = 'Hilltop & Mountain View';
    }

    return array_values(array_unique($types));
}

function _hotelImageGallery(PDO $db, int $hotelId): array {
    // Removed the "visible" column check which was causing the crash
    $stmt = $db->prepare('SELECT image_url, caption, image_position, url_position, raw_json 
                          FROM link_hotel_images 
                          WHERE hotel_id = ? 
                          ORDER BY image_position, url_position');
    $stmt->execute([$hotelId]);
    $rows = $stmt->fetchAll();

    $gallery = [];

    foreach ($rows as $row) {
        $caption = (string) ($row['caption'] ?? '');
        $imagePosition = (int) ($row['image_position'] ?? 0);
        $baseUrlPosition = (int) ($row['url_position'] ?? 0);

        $urls = [];
        $directUrl = trim((string) ($row['image_url'] ?? ''));
        if ($directUrl !== '') {
            $urls[] = $directUrl;
        }

        $raw = _decodeJsonArray((string) ($row['raw_json'] ?? ''));
        if (isset($raw['url']) && is_array($raw['url'])) {
            foreach ($raw['url'] as $rawUrl) {
                $candidate = trim((string) $rawUrl);
                if ($candidate !== '') {
                    $urls[] = $candidate;
                }
            }
        } elseif (isset($raw['url']) && is_string($raw['url'])) {
            $candidate = trim($raw['url']);
            if ($candidate !== '') {
                $urls[] = $candidate;
            }
        }

        $urls = array_values(array_unique($urls));
        foreach ($urls as $offset => $url) {
            $gallery[] = [
                'url' => $url,
                'caption' => $caption,
                'image_position' => $imagePosition,
                'url_position' => $baseUrlPosition + $offset,
            ];
        }
    }

    usort($gallery, static function (array $a, array $b): int {
        $byImagePosition = $a['image_position'] <=> $b['image_position'];
        if ($byImagePosition !== 0) {
            return $byImagePosition;
        }
        return $a['url_position'] <=> $b['url_position'];
    });

    return $gallery;
}

function _hotelAmenities(PDO $db, int $hotelId): array {
    $stmt = $db->prepare('SELECT inclusion_text FROM link_hotel_inclusions WHERE hotel_id = ? ORDER BY position_in_hotel');
    $stmt->execute([$hotelId]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $rows ?: ['Room Service', 'In-house Support', 'Parking'];
}

function _parseHotelScrapeData(array $row): array {
    $scrapeRaw = (string) ($row['scrape_json'] ?? '');
    $scrapeJson = $scrapeRaw !== '' ? (json_decode($scrapeRaw, true) ?? []) : [];

    // Quick facts: strip trailing colons from keys, skip empty/zero values
    $quickFacts = [];
    if (!empty($scrapeJson['quick_facts']) && is_array($scrapeJson['quick_facts'])) {
        foreach ($scrapeJson['quick_facts'] as $key => $value) {
            $cleanKey = rtrim(trim((string) $key), ':');
            $cleanValue = trim((string) $value);
            if ($cleanValue === '' || $cleanValue === '0') {
                continue;
            }
            $quickFacts[$cleanKey] = $cleanValue;
        }
    }

    // Categorised amenities: prefer scrape_json.amenities, fall back to amenities column
    $rawAmenities = !empty($scrapeJson['amenities']) && is_array($scrapeJson['amenities'])
        ? $scrapeJson['amenities']
        : (json_decode((string) ($row['amenities'] ?? ''), true) ?? []);

    $amenitiesCategories = [];
    if (!empty($rawAmenities) && is_array($rawAmenities)) {
        $categoryKeys = array_keys($rawAmenities);
        foreach ($rawAmenities as $category => $items) {
            if (!is_array($items)) {
                continue;
            }
            // Strip category-name entries that leaked into items and deduplicate
            $cleanItems = array_values(array_unique(array_filter(
                array_map('strval', $items),
                static fn(string $item): bool => !in_array($item, $categoryKeys, true)
            )));
            if (!empty($cleanItems)) {
                $amenitiesCategories[(string) $category] = $cleanItems;
            }
        }
    }

    return ['quick_facts' => $quickFacts, 'amenities_categories' => $amenitiesCategories];
}

function _hotelOffers(PDO $db, int $hotelId): array {
    $stmt = $db->prepare('SELECT offer_text FROM link_hotel_offers WHERE hotel_id = ? ORDER BY position_in_hotel');
    $stmt->execute([$hotelId]);
    return array_values(array_filter(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
}

function _hotelRoomRates(PDO $db, int $hotelId): array {
    $stmt = $db->prepare('SELECT * FROM link_hotel_room_rates WHERE hotel_id = ? ORDER BY position_in_hotel');
    $stmt->execute([$hotelId]);
    $rows = $stmt->fetchAll();

    return array_map(static function (array $row): array {
        return [
            'refundable' => (string) ($row['refundable'] ?? ''),
            'couponCode' => (string) ($row['coupon_code'] ?? ''),
            'couponDescription' => (string) ($row['coupon_description'] ?? ''),
            'couponDescriptionPerNight' => (string) ($row['coupon_description_per_night'] ?? ''),
            'preAppliedOfferDiscount' => $row['pre_applied_offer_discount'] !== null ? (float) $row['pre_applied_offer_discount'] : null,
            'preAppliedOfferDiscountPerNight' => $row['pre_applied_offer_discount_per_night'] !== null ? (float) $row['pre_applied_offer_discount_per_night'] : null,
            'discountPercentage' => $row['discount_percentage'] !== null ? (int) $row['discount_percentage'] : null,
            'totalAmountPerNight' => $row['total_amount_per_night'] !== null ? (float) $row['total_amount_per_night'] : null,
            'totalTaxPerNight' => $row['total_tax_per_night'] !== null ? (float) $row['total_tax_per_night'] : null,
            'totalDiscountPerNight' => $row['total_discount_per_night'] !== null ? (float) $row['total_discount_per_night'] : null,
            'discountedPrice' => $row['discounted_price'] !== null ? (float) $row['discounted_price'] : null,
            'discountedPricePerNight' => $row['discounted_price_per_night'] !== null ? (float) $row['discounted_price_per_night'] : null,
            'rewardPoints' => $row['reward_points'] !== null ? (int) $row['reward_points'] : null,
            'inclusions' => _decodeJsonArray((string) ($row['inclusions_json'] ?? '[]')),
        ];
    }, $rows);
}

function _rowToVenue(PDO $db, array $row, bool $full = false): array {
    $id = (int) $row['id'];
    $name = (string) ($row['name'] ?? 'Unnamed Property');
    $slug = (string) ($row['hotel_slug'] ?? '');
    if ($slug === '') {
        $slug = _slugify($name . '-' . $id);
    }

    $meta = _extractVenueMeta($row);
    $location = (string) (($row['locality_name'] ?: $row['city_name']) ?: 'Udaipur');
    $locationCategory = _deriveLocationCategory($location);

    $displayPrice = isset($meta['price_per_plate']) && (int) $meta['price_per_plate'] > 0 ? (float) $meta['price_per_plate'] : null;
    $stayPrice = isset($row['discounted_price']) ? (float) $row['discounted_price'] : null;
    if ($stayPrice === null || $stayPrice <= 0) {
        $stayPrice = isset($row['base_price']) ? (float) $row['base_price'] : 0.0;
    }
    if ($displayPrice === null || $displayPrice <= 0) {
        $displayPrice = (float) max(900, round(($stayPrice > 0 ? $stayPrice : 2500) * 0.22));
    }
    $pricePerPlate = (int) $displayPrice;

    $rating = isset($row['google_rating']) ? (float) $row['google_rating'] : 0.0;
    $capacityFallback = _deriveCapacityFallback((int) ($row['base_price'] ?? 0), $rating);
    $capacityMin = isset($meta['capacity_min']) && (int) $meta['capacity_min'] > 0 ? (int) $meta['capacity_min'] : $capacityFallback['min'];
    $capacityMax = isset($meta['capacity_max']) && (int) $meta['capacity_max'] > 0 ? (int) $meta['capacity_max'] : $capacityFallback['max'];
    $capacityCategory = _capacityCategoryFromRange($capacityMin, $capacityMax);

    $gallery = _hotelImageGallery($db, $id);
    $images = array_values(array_filter(array_map(static fn(array $image): string => (string) $image['url'], $gallery)));
    $amenities = $full ? _hotelAmenities($db, $id) : [];
    $scrapeData = $full ? _parseHotelScrapeData($row) : ['quick_facts' => [], 'amenities_categories' => []];
    $offers = $full ? _hotelOffers($db, $id) : [];
    $roomRates = $full ? _hotelRoomRates($db, $id) : [];
    $venueTypes = _deriveVenueType($row, $meta);

    $description = trim((string) ($meta['description'] ?? ''));
    if ($description === '') {
        $description = (string) ($row['distance_text'] ?? '');
    }
    if ($description === '') {
        $description = $name . ' in ' . $location . ' offers curated stay and event experiences with modern amenities.';
    }

    $rooms = isset($meta['rooms']) && (int) $meta['rooms'] > 0 ? (int) $meta['rooms'] : 40 + ($id % 180);
    $acres = isset($meta['acres']) && $meta['acres'] !== null && $meta['acres'] !== '' ? (float) $meta['acres'] : null;
    $packageCost = trim((string) ($meta['package_cost'] ?? ''));
    if ($packageCost === '' && $stayPrice > 0) {
        $packageCost = 'INR ' . number_format($stayPrice, 0);
    }

    $highlighted = array_key_exists('highlighted', $meta) ? (bool) $meta['highlighted'] : $rating >= 4.3;

    return [
        'id' => $id,
        'isActive' => (bool) ($row['is_active'] ?? 0), // Explicitly map active status
        'name' => $name,
        'slug' => $slug,
        'location' => $location,
        'localityName' => (string) ($row['locality_name'] ?? ''),
        'localitySlug' => _slugify((string) ($row['locality_name'] ?? '')),
        'cityName' => (string) ($row['city_name'] ?? ''),
        'stateName' => (string) ($row['state_name'] ?? ''),
        'countryCode' => (string) ($row['country_code'] ?? ''),
        'latitude' => $row['latitude'] !== null ? (float) $row['latitude'] : null,
        'longitude' => $row['longitude'] !== null ? (float) $row['longitude'] : null,
        'locationCategory' => $locationCategory,
        'pricePerPlate' => $pricePerPlate,
        'priceRange' => _derivePriceRange((float) $pricePerPlate),
        'budgetCategory' => _derivePriceRange((float) $pricePerPlate),
        'capacity' => ['min' => $capacityMin, 'max' => $capacityMax],
        'capacityCategory' => $capacityCategory,
        'rooms' => $rooms,
        'acres' => $acres,
        'venueType' => $venueTypes,
        'amenities' => $amenities,
        'offers' => $offers,
        'roomRates' => $roomRates,
        'description' => $description,
        'images' => $images,
        'imageGallery' => $gallery,
        'bookingUrl' => (string) ($row['hotel_details_link'] ?: '#'),
        'detailsLink' => (string) ($row['hotel_details_link'] ?: '#'),
        'hotelDetailsLink' => $row['hotel_details_link'] ?: null,
        'hotel_booking_url' => isset($row['hotel_booking_url']) ? (string) $row['hotel_booking_url'] : null,
        'highlighted' => $highlighted,
        'packageCost' => $packageCost !== '' ? $packageCost : null,
        'starRating' => (string) ($row['star_rating'] ?? ''),
        'googleRating' => $rating > 0 ? $rating : null,
        'googleRatingsTotal' => $row['google_user_ratings_total'] !== null ? (int) $row['google_user_ratings_total'] : null,
        'basePrice' => $row['base_price'] !== null ? (int) $row['base_price'] : null,
        'taxes' => $row['taxes'] !== null ? (int) $row['taxes'] : null,
        'discountedPrice' => $row['discounted_price'] !== null ? (int) $row['discounted_price'] : null,
        'discountPercentage' => $row['discount_percentage'] !== null ? (int) $row['discount_percentage'] : null,
        'rewardPoints' => $row['reward_points'] !== null ? (int) $row['reward_points'] : null,
        'isPerNight' => (bool) ($row['is_per_night'] ?? 1),
        'dndStatus' => (bool) ($row['dnd_status'] ?? 0),
        'distanceText' => (string) ($row['distance_text'] ?? ''),
        'quickFacts' => $scrapeData['quick_facts'],
        'amenitiesCategories' => $scrapeData['amenities_categories'],
    ];
}

function _matchesFilters(array $venue, string $location, string $budget, string $capacity, string $venueType, string $search): bool {
    if ($location !== 'all' && $venue['locationCategory'] !== $location && $venue['localitySlug'] !== $location) {
        return false;
    }
    if ($budget !== 'all' && $venue['priceRange'] !== $budget) {
        return false;
    }
    if ($capacity !== 'all' && $venue['capacityCategory'] !== $capacity) {
        return false;
    }

    if ($venueType !== 'all') {
        $typeMap = [
            'heritage-palace' => 'Heritage Palace',
            'lakeside' => 'Lakeside',
            'luxury-resort' => 'Luxury Resort',
            'hilltop' => 'Hilltop & Mountain View',
            'contemporary' => 'Contemporary Luxury Hotel',
            'boutique' => 'Boutique & Intimate',
        ];
        $expected = $typeMap[$venueType] ?? $venueType;
        if (!in_array($expected, $venue['venueType'], true)) {
            return false;
        }
    }

    if ($search !== '') {
        if (!_fuzzyMatchSearch($search, $venue)) {
            return false;
        }
    }

    return true;
}

/**
 * Fuzzy search for the search-box only.
 * Strategy:
 *   1. Fast path – exact substring match (covers normal typing).
 *   2. Per-token levenshtein with an adaptive threshold based on token length:
 *      - 1–2 chars  → exact only (too short to fuzz safely)
 *      - 3–4 chars  → allow 1 edit
 *      - 5–7 chars  → allow 2 edits
 *      - 8+ chars   → allow 3 edits
 *   All tokens must match for the venue to be included.
 */
function _fuzzyMatchSearch(string $search, array $venue): bool {
    $needle = strtolower(trim($search));
    if ($needle === '') {
        return true;
    }

    $haystack = strtolower(
        $venue['name'] . ' ' . $venue['location'] . ' ' . ($venue['cityName'] ?? '')
    );

    // Fast path: exact substring
    if (strpos($haystack, $needle) !== false) {
        return true;
    }

    // Tokenise both sides
    $tokens        = preg_split('/\s+/', $needle, -1, PREG_SPLIT_NO_EMPTY);
    $haystackWords = preg_split('/[\s,\-\/]+/', $haystack, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($tokens as $token) {
        $tLen = strlen($token);

        // Very short tokens require an exact substring match
        if ($tLen <= 2) {
            if (strpos($haystack, $token) === false) {
                return false;
            }
            continue;
        }

        $threshold = match (true) {
            $tLen === 3 => 1,
            $tLen <= 6  => 2,
            default     => 3,
        };

        $matched = false;
        foreach ($haystackWords as $word) {
            // Substring match inside a haystack word counts too
            if (strpos($word, $token) !== false || levenshtein($token, $word) <= $threshold) {
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            return false;
        }
    }

    return true;
}

function getVenueBySlug(string $slug, bool $onlyActive = true): ?array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return null;
    }

    $row = _findHotelRowBySlug($db, $slug, $onlyActive);
    return $row ? _rowToVenue($db, $row, true) : null;
}

function filterVenues(
    string $location = 'all',
    string $budget = 'all',
    string $capacity = 'all',
    string $venueType = 'all',
    string $search = '',
    bool $onlyActive = true,
    array $amenityIds = []
): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }

    $hotelIds = null;
    $amenityIds = array_values(array_unique(array_filter($amenityIds, static fn($id) => is_int($id) && $id > 0)));
    if (!empty($amenityIds)) {
        $placeholders = implode(',', array_fill(0, count($amenityIds), '?'));
        $stmt = $db->prepare(
            'SELECT hotel_id FROM link_hotel_amenities WHERE amenity_id IN (' . $placeholders . ') GROUP BY hotel_id HAVING COUNT(DISTINCT amenity_id) = ?'
        );
        foreach ($amenityIds as $index => $amenityId) {
            $stmt->bindValue($index + 1, $amenityId, PDO::PARAM_INT);
        }
        $stmt->bindValue(count($amenityIds) + 1, count($amenityIds), PDO::PARAM_INT);
        $stmt->execute();
        $hotelIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        if (empty($hotelIds)) {
            return [];
        }
    }

    $sql = 'SELECT * FROM link_hotels';
    $params = [];
    if ($onlyActive) {
        $sql .= ' WHERE is_active = 1';
    }

    if ($hotelIds !== null) {
        $sql .= $onlyActive ? ' AND ' : ' WHERE ';
        $sql .= 'id IN (' . implode(',', array_fill(0, count($hotelIds), '?')) . ')';
        $params = array_merge($params, $hotelIds);
    }

    $sql .= ' ORDER BY COALESCE(google_rating, 0) DESC, COALESCE(discounted_price, base_price, 0) DESC, name ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $venues = [];

    foreach ($rows as $row) {
        $venue = _rowToVenue($db, $row, false);
        if (_matchesFilters($venue, $location, $budget, $capacity, $venueType, $search)) {
            $venues[] = $venue;
        }
    }

    return $venues;
}

function getFeaturedVenues(int $limit = 4): array {
    $all = filterVenues();
    $featured = array_values(array_filter($all, fn($v) => $v['highlighted']));

    // Fallback to top-rated if no venues are manually featured
    if (empty($featured)) {
        return array_slice($all, 0, $limit);
    }

    return array_slice($featured, 0, $limit);
}

function getOccasions(): array {
    static $occasions = null;
 
    // Cache so multiple calls in one request don't re-query
    if ($occasions !== null) {
        return $occasions;
    }
 
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }

    try {
        $stmt = $db->query('SELECT id, occasion_name FROM link_occasion ORDER BY id ASC');
        $occasions = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Exception $e) {
        $occasions = [];
    }

    return $occasions;
}


function getTopAmenities(int $limit = 10): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }

    $stmt = $db->prepare('
        SELECT a.id, a.name, a.slug, COUNT(ha.hotel_id) as hotel_count
        FROM link_amenities a
        JOIN link_hotel_amenities ha ON a.id = ha.amenity_id
        WHERE a.is_active = 1
        GROUP BY a.id, a.name, a.slug
        ORDER BY hotel_count DESC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCollections(int $limit = 0): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }

    $sql = '
        SELECT c.id, c.collection_name, COUNT(ch.hotel_id) AS hotel_count
        FROM link_collection c
        LEFT JOIN link_collection_hotel ch ON c.id = ch.collection_id
        GROUP BY c.id, c.collection_name
        ORDER BY c.collection_name
    ';

    if ($limit > 0) {
        $sql .= ' LIMIT ?';
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
    } else {
        $stmt = $db->query($sql);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTopAttractions(int $limit = 10): array {
    $db = _getVenueSqliteDb();
    if (!$db) {
        return [];
    }

    $stmt = $db->prepare('
        SELECT a.id, a.attraction_name, COUNT(ah.hotel_id) as hotel_count
        FROM link_attraction a
        LEFT JOIN link_attraction_hotel ah ON a.id = ah.attraction_id
        GROUP BY a.id, a.attraction_name
        ORDER BY hotel_count DESC, a.attraction_name
    ');
    $stmt->execute();
    $attractions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $preferredAttraction = 'Fateh Sagar Lake';
    $excludedAttraction = 'Vintage Car Museum';
    $preferred = [];
    $filtered = [];

    foreach ($attractions as $attraction) {
        $name = trim((string) ($attraction['attraction_name'] ?? ''));
        if (strcasecmp($name, $preferredAttraction) === 0) {
            $preferred[] = $attraction;
            continue;
        }
        if (strcasecmp($name, $excludedAttraction) === 0) {
            continue;
        }
        $filtered[] = $attraction;
    }

    if (!empty($preferred)) {
        array_unshift($filtered, ...$preferred);
    }

    return array_slice($filtered, 0, $limit);
}

$totalVenues = count(filterVenues());


