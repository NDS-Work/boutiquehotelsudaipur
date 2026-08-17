<?php
// ─────────────────────────────────────────────────────────────────
//  migrate.php  –  Run ONCE to populate your database
//
//  HOW TO USE:
//    1. Fill in your DB credentials in db.php first
//    2. Make sure you've already run the CREATE TABLE SQL statements
//    3. Open this file in your browser OR run:  php migrate.php
//    4. When done, DELETE this file from your server
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/db.php';

// ── Static data (your original array) ───────────────────────────
$venues = [
    [
        'id' => 1,
        'name' => 'Amantra Shilpi Resort & Spa',
        'slug' => 'amantra-shilpi-resort',
        'location' => 'Fateh Sagar Lake Area',
        'locationCategory' => 'fateh-sagar',
        'pricePerPlate' => 950,
        'priceRange' => 'budget-friendly',
        'capacity' => ['min' => 20, 'max' => 550],
        'capacityCategory' => 'medium',
        'rooms' => 42,
        'acres' => 5,
        'venueType' => ['Luxury Resort', 'Lakeside'],
        'amenities' => ['Indoor & Outdoor Venues','Poolside Events','In-house Catering','Bridal Dressing Room','In-house DJ & Decor','Valet Parking','42 Ethnically Styled Rooms'],
        'description' => 'Amantra Shilpi Resort offers exceptional value with 5 acres of landscaped gardens near Fateh Sagar Lake. Perfect for intimate to large weddings with versatile indoor and outdoor spaces including Insight & Insearch halls, poolside venues, and live grill areas.',
        'images' => ['https://images.unsplash.com/photo-1715178003372-9f790b685775?w=1200&h=900&fit=crop','https://images.unsplash.com/photo-1706811042876-0073ab8b7527?w=1200&h=900&fit=crop'],
        'bookingUrl' => 'https://www.amantrashilpiresort.com/',
        'highlighted' => true,
        'packageCost' => null,
    ],
    [
        'id' => 2,
        'name' => 'The Oberoi Udaivilas',
        'slug' => 'oberoi-udaivilas',
        'location' => 'Lake Pichola Waterfront',
        'locationCategory' => 'lake-pichola',
        'pricePerPlate' => 8000,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 150, 'max' => 400],
        'capacityCategory' => 'medium',
        'rooms' => 87,
        'acres' => 50,
        'venueType' => ['Heritage Palace', 'Lakeside', 'Contemporary Luxury Hotel'],
        'amenities' => ['50 Acres of Gardens','Lake Pichola Views','Luxury Palace Architecture','Multiple Terraces','Poolside Venues','87 Rooms & Suites','Full Wedding Planning'],
        'description' => "The Oberoi Udaivilas is the epitome of luxury on Lake Pichola's banks. Set across 50 acres, this grand palace hotel offers unmatched elegance with intricate domes, courtyards, and stunning lake views. Perfect for ultra-luxury destination weddings.",
        'images' => ['https://images.unsplash.com/photo-1679234417190-9c443548a40c?w=1200&h=900&fit=crop','https://images.unsplash.com/photo-1696861679643-4f21bfba8fc3?w=1200&h=900&fit=crop'],
        'packageCost' => '₹1.1 - 6 Crore',
        'bookingUrl' => 'https://www.oberoihotels.com/hotels-in-udaipur-udaivilas-resort/',
        'highlighted' => true,
    ],
    [
        'id' => 3,
        'name' => 'Taj Lake Palace',
        'slug' => 'taj-lake-palace',
        'location' => 'Lake Pichola Island',
        'locationCategory' => 'lake-pichola',
        'pricePerPlate' => 12500,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 50, 'max' => 500],
        'capacityCategory' => 'medium',
        'rooms' => 83,
        'acres' => null,
        'venueType' => ['Heritage Palace', 'Island Venue', 'Lakeside'],
        'amenities' => ['Floating Palace on Lake Pichola','Boat Access Only','83 Luxury Rooms','Heritage Halls','Outdoor Terraces','Courtyards & Pavilions','Exclusive Property Buyout'],
        'description' => "The iconic Taj Lake Palace floats majestically on Lake Pichola. This 18th-century marvel offers the ultimate intimate and exclusive wedding experience with boat access, heritage interiors, and breathtaking lake views from every angle.",
        'images' => ['https://images.unsplash.com/photo-1712817616402-122c951e5dae?w=1200&h=900&fit=crop','https://images.unsplash.com/photo-1695956353120-54ce5e91632b?w=1200&h=900&fit=crop'],
        'packageCost' => '₹50 Lakhs - 5 Crore',
        'bookingUrl' => 'https://www.tajhotels.com/en-in/hotels/taj-lake-palace-udaipur/',
        'highlighted' => true,
    ],
    [
        'id' => 4,
        'name' => 'Radisson Blu Palace Resort & Spa',
        'slug' => 'radisson-blu-palace',
        'location' => 'Fateh Sagar Lake Area',
        'locationCategory' => 'fateh-sagar',
        'pricePerPlate' => 4500,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 50, 'max' => 1200],
        'capacityCategory' => 'grand',
        'rooms' => 244,
        'acres' => null,
        'venueType' => ['Contemporary Luxury Hotel', 'Lakeside'],
        'amenities' => ['244 Rooms','Fatehsagar Grand Ballroom (600 seated)','Aravali Lawn (800 seated, 1200 floating)','Multiple Indoor/Outdoor Venues','Fateh Sagar Lake Views','Pillar-less Ballroom','Spa & Wellness'],
        'description' => "Radisson Blu Palace Resort near Fateh Sagar Lake offers grand celebrations with Udaipur's largest pillar-less ballroom and multiple versatile venues. Perfect for 50 to 1,200 guests with stunning Aravali Hills and lake backdrop.",
        'images' => ['https://images.pexels.com/photos/33726143/pexels-photo-33726143.jpeg?w=1200&h=900&fit=crop','https://images.unsplash.com/photo-1759490821541-f78bb13a752d?w=1200&h=900&fit=crop'],
        'packageCost' => '₹43 Lakhs onwards',
        'bookingUrl' => 'https://www.radissonhotels.com/en-us/hotels/radisson-blu-udaipur-palace',
        'highlighted' => true,
    ],
    [
        'id' => 5,
        'name' => 'RAAS Devigarh',
        'slug' => 'raas-devigarh',
        'location' => 'Aravalli Hills (Delwara)',
        'locationCategory' => 'aravalli-hills',
        'pricePerPlate' => 4200,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 150, 'max' => 250],
        'capacityCategory' => 'intimate',
        'rooms' => 39,
        'acres' => null,
        'venueType' => ['Heritage Palace', 'Hilltop & Mountain View', 'Boutique & Intimate'],
        'amenities' => ['18th Century Palace','39 Luxury Suites','Durbar Courtyard','Janana Courtyard','Terrace Gardens','Aravalli Hills Setting','28km from Udaipur'],
        'description' => 'RAAS Devigarh is an 18th-century palace fort nestled in the Aravalli foothills near Eklingji Temple. This intimate heritage property blends royal architecture with modern luxury, perfect for 150-250 guest weddings seeking exclusivity.',
        'images' => ['https://images.unsplash.com/photo-1627490631692-3eb501e21cd1?w=1200&h=900&fit=crop','https://images.unsplash.com/photo-1706811042876-0073ab8b7527?w=1200&h=900&fit=crop'],
        'packageCost' => '₹37 Lakhs onwards',
        'bookingUrl' => 'https://www.raashotels.com/devigarh/',
        'highlighted' => false,
    ],
    [
        'id' => 6,
        'name' => 'The Leela Palace Udaipur',
        'slug' => 'leela-palace-udaipur',
        'location' => 'Lake Pichola Waterfront',
        'locationCategory' => 'lake-pichola',
        'pricePerPlate' => 8500,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 200, 'max' => 400],
        'capacityCategory' => 'medium',
        'rooms' => 80,
        'acres' => null,
        'venueType' => ['Heritage Palace', 'Lakeside', 'Contemporary Luxury Hotel'],
        'amenities' => ['80 Luxury Rooms','Lake Pichola Frontage','Royal Architecture','Multiple Terraces','Poolside Venues','Grand Ballroom','Spa & Wellness'],
        'description' => "The Leela Palace Udaipur combines regal splendor with contemporary luxury on Lake Pichola's shores. With 80 rooms and multiple event spaces, it's ideal for 200-400 guest weddings seeking lakefront grandeur.",
        'images' => ['https://images.unsplash.com/photo-1679234417190-9c443548a40c?w=1200&h=900&fit=crop','https://images.unsplash.com/photo-1696861679643-4f21bfba8fc3?w=1200&h=900&fit=crop'],
        'packageCost' => '₹18-25 Lakhs',
        'bookingUrl' => 'https://www.theleela.com/the-leela-palace-udaipur',
        'highlighted' => false,
    ],
    [
        'id' => 7,
        'name' => 'Trident Udaipur',
        'slug' => 'trident-udaipur',
        'location' => 'Lake Pichola Waterfront',
        'locationCategory' => 'lake-pichola',
        'pricePerPlate' => 3500,
        'priceRange' => 'premium-luxury',
        'capacity' => ['min' => 20, 'max' => 400],
        'capacityCategory' => 'medium',
        'rooms' => 141,
        'acres' => null,
        'venueType' => ['Lakeside', 'Luxury Resort'],
        'amenities' => ['141 Rooms','Lake Pichola Views','Lakeside Resort Setting','Multiple Event Spaces','Gardens & Lawns','Poolside Venues'],
        'description' => "Trident Udaipur offers lakeside elegance with 141 rooms and flexible event spaces for 20-400 guests. Located on Lake Pichola's banks, it provides premium wedding experiences with stunning water views.",
        'images' => ['https://images.unsplash.com/photo-1712817616402-122c951e5dae?w=1200&h=900&fit=crop','https://images.unsplash.com/photo-1715178003372-9f790b685775?w=1200&h=900&fit=crop'],
        'packageCost' => '₹8-10 Lakhs',
        'bookingUrl' => 'https://www.tridenthotels.com/hotels-in-udaipur-lake-city-resort',
        'highlighted' => false,
    ],
    [
        'id' => 8,
        'name' => 'Wyndham Grand Udaipur',
        'slug' => 'wyndham-grand-udaipur',
        'location' => 'Fateh Sagar Lake Area',
        'locationCategory' => 'fateh-sagar',
        'pricePerPlate' => 3000,
        'priceRange' => 'premium-luxury',
        'capacity' => ['min' => 100, 'max' => 400],
        'capacityCategory' => 'medium',
        'rooms' => 140,
        'acres' => 26,
        'venueType' => ['Contemporary Luxury Hotel', 'Luxury Resort'],
        'amenities' => ['140 Rooms','26 Acres Property','2 Lakh Sq Ft Lawn','10,000 Sq M Event Space','4 Different Venues','Near Fateh Sagar Lake'],
        'description' => 'Wyndham Grand Udaipur sprawls across 26 acres near Fateh Sagar Lake with massive event spaces. The 140-room resort features a 2 lakh sq ft lawn and 10,000 sq m of event space, perfect for grand celebrations.',
        'images' => ['https://images.pexels.com/photos/33726143/pexels-photo-33726143.jpeg?w=1200&h=900&fit=crop','https://images.unsplash.com/photo-1696271026800-0959e9cbf38c?w=1200&h=900&fit=crop'],
        'packageCost' => null,
        'bookingUrl' => 'https://www.wyndhamhotels.com/wyndham-grand/udaipur-india',
        'highlighted' => false,
    ],
];

// ── Migration helpers ────────────────────────────────────────────

$db = getDB();
$errors = [];
$counts = ['venues' => 0, 'images' => 0, 'amenities' => 0, 'types' => 0];

/**
 * Find or create a row in a lookup table by slug, return its id.
 */
function upsertLookup(PDO $db, string $table, string $slug, string $label, ?int $sortOrder = null): int {
    $row = $db->prepare("SELECT id FROM `{$table}` WHERE slug = ?");
    $row->execute([$slug]);
    if ($existing = $row->fetchColumn()) {
        return (int) $existing;
    }
    if ($sortOrder !== null) {
        $ins = $db->prepare("INSERT INTO `{$table}` (slug, label, sort_order) VALUES (?, ?, ?)");
        $ins->execute([$slug, $label, $sortOrder]);
    } else {
        $ins = $db->prepare("INSERT INTO `{$table}` (slug, label) VALUES (?, ?)");
        $ins->execute([$slug, $label]);
    }
    return (int) $db->lastInsertId();
}

/**
 * Slugify a venue type label for storage.
 */
function typeToSlug(string $label): string {
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $label), '-'));
}

// ── Price range sort order map ───────────────────────────────────
$priceRangeOrder = [
    'budget-friendly' => 1,
    'premium-luxury'  => 2,
    'ultra-luxury'    => 3,
];

// ── Location label map (slug → human label) ──────────────────────
$locationLabels = [
    'lake-pichola'   => 'Lake Pichola',
    'fateh-sagar'    => 'Fateh Sagar Lake Area',
    'aravalli-hills' => 'Aravalli Hills',
];

$priceLabels = [
    'budget-friendly' => 'Budget Friendly',
    'premium-luxury'  => 'Premium Luxury',
    'ultra-luxury'    => 'Ultra Luxury',
];

$capacityLabels = [
    'intimate' => 'Intimate',
    'medium'   => 'Medium',
    'grand'    => 'Grand',
];

// ── Run migration ────────────────────────────────────────────────
echo "<pre>\n=== VENUE MIGRATION ===\n\n";

$db->beginTransaction();

try {
    foreach ($venues as $v) {

        // 1. Resolve / create lookup IDs
        $locationId = upsertLookup(
            $db, 'location_categories',
            $v['locationCategory'],
            $locationLabels[$v['locationCategory']] ?? $v['location']
        );

        $priceId = upsertLookup(
            $db, 'price_ranges',
            $v['priceRange'],
            $priceLabels[$v['priceRange']] ?? $v['priceRange'],
            $priceRangeOrder[$v['priceRange']] ?? 99
        );

        $capacityId = upsertLookup(
            $db, 'capacity_categories',
            $v['capacityCategory'],
            $capacityLabels[$v['capacityCategory']] ?? $v['capacityCategory']
        );

        // 2. Insert venue
        $stmt = $db->prepare("
            INSERT INTO venues
                (name, slug, location_label, location_category_id, price_range_id,
                 capacity_category_id, price_per_plate, capacity_min, capacity_max,
                 rooms, acres, description, package_cost, booking_url, highlighted)
            VALUES
                (:name, :slug, :location_label, :location_category_id, :price_range_id,
                 :capacity_category_id, :price_per_plate, :capacity_min, :capacity_max,
                 :rooms, :acres, :description, :package_cost, :booking_url, :highlighted)
        ");

        $stmt->execute([
            ':name'                 => $v['name'],
            ':slug'                 => $v['slug'],
            ':location_label'       => $v['location'],
            ':location_category_id' => $locationId,
            ':price_range_id'       => $priceId,
            ':capacity_category_id' => $capacityId,
            ':price_per_plate'      => $v['pricePerPlate'] ?? null,
            ':capacity_min'         => $v['capacity']['min'] ?? null,
            ':capacity_max'         => $v['capacity']['max'] ?? null,
            ':rooms'                => $v['rooms'] ?? null,
            ':acres'                => $v['acres'] ?? null,
            ':description'          => $v['description'] ?? null,
            ':package_cost'         => $v['packageCost'] ?? null,
            ':booking_url'          => $v['bookingUrl'] ?? null,
            ':highlighted'          => $v['highlighted'] ? 1 : 0,
        ]);

        $venueId = (int) $db->lastInsertId();
        $counts['venues']++;
        echo "✓ Inserted venue [{$venueId}]: {$v['name']}\n";

        // 3. Insert images
        foreach (($v['images'] ?? []) as $i => $url) {
            $db->prepare("INSERT INTO venue_images (venue_id, url, sort_order) VALUES (?, ?, ?)")
               ->execute([$venueId, $url, $i]);
            $counts['images']++;
        }

        // 4. Insert amenities (find-or-create each amenity, then link)
        foreach (($v['amenities'] ?? []) as $amenityLabel) {
            $row = $db->prepare("SELECT id FROM amenities WHERE label = ?");
            $row->execute([$amenityLabel]);
            $amenityId = $row->fetchColumn();

            if (!$amenityId) {
                $db->prepare("INSERT INTO amenities (label) VALUES (?)")->execute([$amenityLabel]);
                $amenityId = (int) $db->lastInsertId();
            }

            $db->prepare("INSERT IGNORE INTO venue_amenities (venue_id, amenity_id) VALUES (?, ?)")
               ->execute([$venueId, $amenityId]);
            $counts['amenities']++;
        }

        // 5. Insert venue types (find-or-create, then link)
        foreach (($v['venueType'] ?? []) as $typeLabel) {
            $typeSlug = typeToSlug($typeLabel);
            $row = $db->prepare("SELECT id FROM venue_types WHERE slug = ?");
            $row->execute([$typeSlug]);
            $typeId = $row->fetchColumn();

            if (!$typeId) {
                $db->prepare("INSERT INTO venue_types (slug, label) VALUES (?, ?)")->execute([$typeSlug, $typeLabel]);
                $typeId = (int) $db->lastInsertId();
            }

            $db->prepare("INSERT IGNORE INTO venue_venue_types (venue_id, venue_type_id) VALUES (?, ?)")
               ->execute([$venueId, $typeId]);
            $counts['types']++;
        }
    }

    $db->commit();

    echo "\n=== DONE ✓ ===\n";
    echo "Venues inserted  : {$counts['venues']}\n";
    echo "Images inserted  : {$counts['images']}\n";
    echo "Amenities linked : {$counts['amenities']}\n";
    echo "Types linked     : {$counts['types']}\n";
    echo "\n⚠  DELETE this file from your server now!\n";

} catch (Throwable $e) {
    $db->rollBack();
    echo "\n❌ Migration FAILED — all changes rolled back.\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";