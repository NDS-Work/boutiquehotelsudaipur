<?php
require_once 'data/venues.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$venue = getVenueBySlug($slug);

if (!$venue) {
    header('Location: /venues.php');
    exit;
}

// Fetch amenities with icons from database for this specific hotel
$hotelAmenitiesWithIcons = [];
$_amenityDb = _getVenueSqliteDb();
if ($_amenityDb && !empty($venue['id'])) {
    try {
        $stmt = $_amenityDb->prepare("
            SELECT 
                la.id,
                la.name,
                la.icon,
                la.icon_type_name
            FROM link_hotel_amenities lha
            INNER JOIN link_amenities la ON lha.amenity_id = la.id
            WHERE lha.hotel_id = ?
              AND la.icon IS NOT NULL
              AND TRIM(la.icon) != ''
            ORDER BY la.name
        ");
        $stmt->execute([$venue['id']]);
        $hotelAmenitiesWithIcons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error fetching hotel amenities: ' . $e->getMessage());
        $hotelAmenitiesWithIcons = [];
    }
}

// Fetch attractions for this specific hotel
$hotelAttractions = [];
if ($_amenityDb && !empty($venue['id'])) {
    try {
        $stmt = $_amenityDb->prepare("
            SELECT 
                la.id,
                la.attraction_name
            FROM link_attraction_hotel lah
            INNER JOIN link_attraction la ON lah.attraction_id = la.id
            WHERE lah.hotel_id = ?
            ORDER BY la.attraction_name
        ");
        $stmt->execute([$venue['id']]);
        $hotelAttractions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error fetching hotel attractions: ' . $e->getMessage());
        $hotelAttractions = [];
    }
}

// $amenityIconMap = [];
// try {
//     $db = new PDO('sqlite:' . __DIR__ . '/data/new.sqlite.db'); // ← update path
//     $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $rows = $db->query("
//         SELECT name, icon 
//         FROM link_amenities 
//         WHERE is_active = 1 
//           AND icon IS NOT NULL 
//           AND TRIM(icon) != ''
//     ")->fetchAll(PDO::FETCH_ASSOC);
//     foreach ($rows as $row) {
//         $amenityIconMap[strtolower(trim($row['name']))] = trim($row['icon']);
//     }
// } catch (Exception $e) {
    
// }

$gallery = $venue['imageGallery'] ?? [];
$heroImage = $gallery[0]['url'] ?? ($venue['images'][0] ?? '');
$additionalGallery = array_slice($gallery, 1);
$galleryItems = array_values(array_filter(array_map(static function (array $image): array {
    return [
        'url' => trim((string) ($image['url'] ?? '')),
        'caption' => (string) ($image['caption'] ?? ''),
    ];
}, $gallery), static fn(array $image): bool => $image['url'] !== ''));
$collageMain = $galleryItems[0] ?? null;
$collageSideTop = $galleryItems[1] ?? null;
$collageSideBottom = $galleryItems[2] ?? null;
$collageExtraCount = max(0, count($galleryItems) - 3);
$pricingUnit = !empty($venue['isPerNight']) ? 'per night' : 'per plate';
$mapParts = array_values(array_filter([
    $venue['name'] ?? '',
    $venue['localityName'] ?? '',
    $venue['cityName'] ?? '',
    $venue['stateName'] ?? '',
]));
$mapQuery = implode(', ', $mapParts);
$mapUrl = $mapQuery !== '' ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapQuery) : null;

$pageTitle = $venue['name'];
$metaTitle = $venue['name'] . ' | Boutique Hotels In Udaipur';
$metaDescription = !empty($venue['description'])
    ? strip_tags($venue['description'])
    : 'Discover details, images, and pricing for ' . $venue['name'] . ' in Udaipur. Perfect for weddings and events.';

// ── Schema markup ─────────────────────────────────────────────
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Hotel',
    'name'     => $venue['name'],
    'url'      => 'https://boutiquehotelsudaipur.com/hotels/' . ($venue['slug'] ?? ''),
    'address'  => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $venue['location'] ?? '',
        'addressLocality' => 'Udaipur',
        'addressRegion'   => 'Rajasthan',
        'postalCode'      => '313001',
        'addressCountry'  => 'IN',
    ],
];

if (!empty($venue['description'])) {
    $schema['description'] = strip_tags($venue['description']);
}

if (!empty($venue['latitude']) && !empty($venue['longitude'])) {
    $schema['geo'] = [
        '@type'     => 'GeoCoordinates',
        'latitude'  => (float) $venue['latitude'],
        'longitude' => (float) $venue['longitude'],
    ];
}

$_heroImg = $venue['imageGallery'][0]['url'] ?? ($venue['images'][0] ?? '');
if (!empty($_heroImg)) {
    $schema['image'] = strpos($_heroImg, 'http') === 0
        ? $_heroImg
        : 'https://boutiquehotelsudaipur.com/' . ltrim($_heroImg, '/');
}

if (!empty($venue['googleRating'])) {
    $schema['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => (string) number_format((float) $venue['googleRating'], 1),
        'reviewCount' => (string) ($venue['googleRatingsTotal'] ?? 1),
        'bestRating'  => '5',
        'worstRating' => '1',
    ];
}

if (!empty($hotelAmenitiesWithIcons)) {
    $schema['amenityFeature'] = array_map(fn($a) => [
        '@type' => 'LocationFeatureSpecification',
        'name'  => $a['name'],
        'value' => true,
    ], $hotelAmenitiesWithIcons);
}

if (!empty($venue['hotelDetailsLink'])) {
    $schema['sameAs'] = $venue['hotelDetailsLink'];
}

$schemaJson = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

require_once 'includes/header.php';
?>

<div style="background-color: var(--bg-page); min-height: 100vh; padding-top: 100px; padding-bottom: 60px;">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="/hotels.php" class="text-decoration-none d-inline-flex align-items-center" style="color: var(--text-secondary);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                Back to All Hotels
            </a>
        </div>

        <!-- Hero Collage -->
            <div class="mb-5">
    <?php if (!empty($galleryItems)): ?>
    <!-- Airbnb-style Image Grid -->
    <div style="display: grid; grid-template-columns: 560px 1fr 1fr; gap: 8px; height: 460px; overflow: hidden; border-radius: 12px; position: relative;">
        
        <!-- Left: Large primary image (spans full height) -->
        <div style="overflow: hidden; height: 460px;">
            <img 
                src="<?php echo htmlspecialchars($galleryItems[0]['url']); ?>" 
                alt="Venue image 1" 
                style="width: 100%; height: 460px; object-fit: cover; display: block; cursor: pointer; transition: transform 0.3s ease;"
                onmouseover="this.style.transform='scale(1.03)'"
                onmouseout="this.style.transform='scale(1.0)'"
                onclick="openGallery(0)"
            >
        </div>

        <!-- Middle column: 2 stacked images -->
        <div style="display: grid; grid-template-rows: 1fr 1fr; gap: 8px; height: 460px;">
            <?php for ($i = 1; $i <= 2; $i++): ?>
            <div style="overflow: hidden;">
                <?php if (!empty($galleryItems[$i])): ?>
                <img 
                    src="<?php echo htmlspecialchars($galleryItems[$i]['url']); ?>" 
                    alt="Venue image <?php echo $i + 1; ?>" 
                    style="width: 100%; height: 100%; object-fit: cover; display: block; cursor: pointer; transition: transform 0.3s ease;"
                    onmouseover="this.style.transform='scale(1.05)'"
                    onmouseout="this.style.transform='scale(1.0)'"
                    onclick="openGallery(<?php echo $i; ?>)"
                >
                <?php else: ?>
                <div style="width:100%; height:100%; background: var(--bg-card, #e8e8e8);"></div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Right column: 2 stacked images -->
        <div style="display: grid; grid-template-rows: 1fr 1fr; gap: 8px; height: 460px;">
            <?php for ($i = 3; $i <= 4; $i++): ?>
            <div style="overflow: hidden;">
                <?php if (!empty($galleryItems[$i])): ?>
                <img 
                    src="<?php echo htmlspecialchars($galleryItems[$i]['url']); ?>" 
                    alt="Venue image <?php echo $i + 1; ?>" 
                    style="width: 100%; height: 100%; object-fit: cover; display: block; cursor: pointer; transition: transform 0.3s ease;"
                    onmouseover="this.style.transform='scale(1.05)'"
                    onmouseout="this.style.transform='scale(1.0)'"
                    onclick="openGallery(<?php echo $i; ?>)"
                >
                <?php else: ?>
                <div style="width:100%; height:100%; background: var(--bg-card, #e8e8e8);"></div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Show all photos button -->
        <?php if (count($galleryItems) > 5): ?>
        <button 
            onclick="openGallery(0)"
            style="position: absolute; bottom: 12px; right: 12px; background: white; border: 1px solid #ccc; border-radius: 8px; padding: 7px 14px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); z-index: 10;"
        >
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="1" width="5" height="5" rx="1" stroke="#222" stroke-width="1.5"/>
                <rect x="10" y="1" width="5" height="5" rx="1" stroke="#222" stroke-width="1.5"/>
                <rect x="1" y="10" width="5" height="5" rx="1" stroke="#222" stroke-width="1.5"/>
                <rect x="10" y="10" width="5" height="5" rx="1" stroke="#222" stroke-width="1.5"/>
            </svg>
            Show all photos
        </button>
        <?php endif; ?>

    </div>

    <!-- Lightbox Modal -->
    <div id="galleryModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.92); z-index:9999; align-items:center; justify-content:center;">
        <button onclick="closeGallery()" style="position:absolute; top:20px; right:28px; background:none; border:none; color:white; font-size:32px; cursor:pointer; line-height:1;">&times;</button>
        <button onclick="prevImage()" style="position:absolute; left:20px; background:rgba(255,255,255,0.15); border:none; color:white; font-size:28px; border-radius:50%; width:48px; height:48px; cursor:pointer;">&#8249;</button>
        <img id="galleryModalImg" src="" alt="" style="max-width:90vw; max-height:85vh; object-fit:contain; border-radius:8px;">
        <button onclick="nextImage()" style="position:absolute; right:20px; background:rgba(255,255,255,0.15); border:none; color:white; font-size:28px; border-radius:50%; width:48px; height:48px; cursor:pointer;">&#8250;</button>
        <div id="galleryCounter" style="position:absolute; bottom:20px; color:white; font-size:14px;"></div>
    </div>

    <?php else: ?>
    <div class="d-flex align-items-center justify-content-center" style="height: 460px; border: 1px solid var(--border-medium); background-color: var(--bg-card); color: var(--text-secondary); border-radius: 12px;">
        No venue images available.
    </div>
    <?php endif; ?>
</div>

        <div class="row g-5">
            <!-- Main Content -->
            <div class="col-lg-8">
                <h1 class="heading-2 mb-4"><?php echo htmlspecialchars($venue['name']); ?></h1>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <?php if (!empty($venue['googleRating'])): ?>
                    <span class="badge px-3 py-2" style="background-color: rgba(174, 93, 48, 0.12); color: var(--brand-primary); border: 1px solid rgba(174, 93, 48, 0.2); font-size: 14px;">
                        Google <?php echo htmlspecialchars(number_format((float) $venue['googleRating'], 1)); ?>/5
                        <?php if (!empty($venue['googleRatingsTotal'])): ?>
                        (<?php echo number_format((int) $venue['googleRatingsTotal']); ?> reviews)
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($venue['starRating'])): ?>
                    <span class="badge px-3 py-2" style="background-color: var(--bg-card); color: var(--bg-light); border: 1px solid var(--border-medium); font-size: 14px;">
                        <?php echo htmlspecialchars((string) $venue['starRating']); ?> star stay
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($venue['distanceText'])): ?>
                    <span class="badge px-3 py-2" style="background-color: var(--bg-card); color: var(--bg-light); border: 1px solid var(--border-medium); font-size: 14px;">
                        <?php echo htmlspecialchars((string) $venue['distanceText']); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex flex-wrap gap-4 mb-5">
                    <div class="d-flex align-items-center" style="color: var(--bg-light);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                        </svg>
                        <span><?php echo htmlspecialchars($venue['location']); ?></span>
                    </div>
                    <div class="d-flex align-items-center" style="color: var(--bg-light);">
                        <!-- <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                        </svg> -->
                        <!-- <span>₹<?php echo number_format($venue['pricePerPlate']); ?>/plate</span> -->
                    </div>
                    <!-- <div class="d-flex align-items-center" style="color: var(--bg-light);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                        </svg>
                        <span><?php echo $venue['capacity']['min']; ?>-<?php echo $venue['capacity']['max']; ?> guests</span>
                    </div> -->
                </div>

                <!-- Description -->
               <?php if (!empty($venue['description'])): ?>
<div class="mb-5">
    <h2 class="heading-5 mb-3" style="color: var(--brand-primary);">About This Hotel</h2>

    <?php
        $fullDesc = htmlspecialchars($venue['description']);
        // Split into sentences or just truncate by character for "2 lines" preview
        $previewLength = 180;
        $isLong = mb_strlen($venue['description']) > $previewLength;
        $previewText = $isLong
            ? htmlspecialchars(mb_substr($venue['description'], 0, $previewLength))
            : $fullDesc;
    ?>

    <p class="lead mb-1" style="color: var(--bg-light); line-height: 1.7;">
        <?php echo $previewText; ?><?php if ($isLong): ?>...<?php endif; ?>
    </p>

    <?php if ($isLong): ?>
    <a href="#"
       data-bs-toggle="modal"
       data-bs-target="#descriptionModal"
       style="color: var(--brand-primary); font-size: 13px; text-decoration: underline; text-underline-offset: 3px; letter-spacing: 0.3px;">
        View more &rsaquo;
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

    <!-- Description Modal  on hold will activate later--> 
<?php if (!empty($venue['description']) && mb_strlen($venue['description']) > 180): ?>
<div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background-color: #fff; border: 1px solid var(--border-medium); font-family: Lato;">
            <div class="modal-header" style="background-color: var(--bg-card); border-bottom: 1px solid #a67c52; padding: 20px 24px;">
                <div>
                    <h5 class="modal-title mb-0" id="descriptionModalLabel" style="color: var(--brand-primary); font-family: inherit; font-size: 1.1rem; font-weight: 600;">
                        About This Venue
                    </h5>
                    <p class="mb-0 mt-1" style="color: var(--text-secondary); font-size: 12px;">
                        <?php echo htmlspecialchars($venue['name']); ?>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-theme="dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background-color: var(--bg-card); padding: 24px;">
                <p style="color: var(--bg-light); line-height: 1.8; font-size: 15px; margin: 0;">
                    <?php echo nl2br(htmlspecialchars($venue['description'])); ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
                <!-- Quick Facts -->
                <?php if (!empty($venue['quickFacts'])): ?>
                <div class="mb-5">
                    <h2 class="heading-5 mb-4" style="color: var(--brand-primary);">Quick Facts</h2>
                    <div class="row g-3">
                        <?php foreach ($venue['quickFacts'] as $label => $value): ?>
                        <div class="col-6 col-md-3">
                            <div class="p-3 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                                <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;"><?php echo htmlspecialchars($label); ?></p>
                                <div style="color: var(--bg-light); font-size: 1rem; font-weight: 600;"><?php echo htmlspecialchars($value); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Key Details -->
                <div class="row g-3 mb-5">
                    <!-- <div class="col-md-6">
                        <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                            <div class="d-flex align-items-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                </svg>
                                <h3 class="heading-6 mb-0" style="color: var(--bg-light);">Accommodation</h3>
                            </div>
                            <p class="small mb-0" style="color: var(--text-secondary);"><?php echo $venue['rooms']; ?> rooms available</p>
                        </div>
                    </div> -->
                    
                    <!-- <div class="col-md-6">
                        <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                            <div class="d-flex align-items-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                                </svg>
                                <h3 class="heading-6 mb-0" style="color: var(--bg-light);">Capacity</h3>
                            </div>
                            <p class="small mb-0" style="color: var(--text-secondary);"><?php echo $venue['capacity']['min']; ?> to <?php echo $venue['capacity']['max']; ?> guests</p>
                        </div>
                    </div> -->
                    
                    <?php if ($venue['acres']): ?>
                    <div class="col-md-6">
                        <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                            <div class="d-flex align-items-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                    <path d="M8 0a.5.5 0 0 1 .5.5V2a.5.5 0 0 1-1 0V.5A.5.5 0 0 1 8 0zm0 13v1.5a.5.5 0 0 1-1 0V13h1zm8-5a.5.5 0 0 1-.5.5H14v1a.5.5 0 0 1-1 0V8.5h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0v1h1.5a.5.5 0 0 1 .5.5zM0 8a.5.5 0 0 1 .5-.5H2a.5.5 0 0 1 0 1H.5A.5.5 0 0 1 0 8zm5.354.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 1 1 .708.708l-2 2z"/>
                                </svg>
                                <h3 class="heading-6 mb-0" style="color: var(--bg-light);">Property Size</h3>
                            </div>
                            <p class="small mb-0" style="color: var(--text-secondary);"><?php echo $venue['acres']; ?> acres</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- <div class="col-md-6">
                        <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);"> 
                            <div class="d-flex align-items-center mb-3">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                    <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                                </svg> 
                                <h3 class="heading-6 mb-0" style="color: var(--bg-light);">Pricing</h3>
                            </div>
                            <p class="small mb-0" style="color: var(--text-secondary);">
                                Starting ₹<?php echo number_format($venue['pricePerPlate']); ?>
                                <?php if ($venue['packageCost']): ?>
                                    <br>Package: <?php echo $venue['packageCost']; ?>
                                <?php endif; ?>
                                <?php if (!empty($venue['discountedPrice'])): ?>
                                    <br>Stay price: ₹<?php echo number_format((int) $venue['discountedPrice']); ?> <?php echo htmlspecialchars($pricingUnit); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div> -->
                </div> 

                <!-- <?php if (!empty($venue['basePrice']) || !empty($venue['taxes']) || !empty($venue['discountPercentage']) || !empty($venue['rewardPoints'])): ?>
                <div class="mb-5">
                    <h2 class="heading-5 mb-4" style="color: var(--brand-primary);">Pricing Breakdown</h2>
                    <div class="row g-3">
                        <?php if (!empty($venue['basePrice'])): ?>
                        <div class="col-md-3 col-6">
                            <div class="p-3 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                                <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Base Price</p>
                                <div style="color: var(--bg-light); font-size: 1.15rem; font-weight: 600;">₹<?php echo number_format((int) $venue['basePrice']); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($venue['taxes'])): ?>
                        <div class="col-md-3 col-6">
                            <div class="p-3 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                                <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Taxes</p>
                                <div style="color: var(--bg-light); font-size: 1.15rem; font-weight: 600;">₹<?php echo number_format((int) $venue['taxes']); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($venue['discountPercentage'])): ?>
                        <div class="col-md-3 col-6">
                            <div class="p-3 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                                <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Discount</p>
                                <div style="color: var(--bg-light); font-size: 1.15rem; font-weight: 600;"><?php echo (int) $venue['discountPercentage']; ?>%</div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($venue['rewardPoints'])): ?>
                        <div class="col-md-3 col-6">
                            <div class="p-3 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                                <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Reward Points</p>
                                <div style="color: var(--bg-light); font-size: 1.15rem; font-weight: 600;"><?php echo number_format((int) $venue['rewardPoints']); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?> -->

                <!-- Book Now Modal moved to end of file, styled like contact form -->

                <?php
                // ── Organize amenities with database icons into semantic groups ─────────────────────
                $_semanticOrder = [
                    'Room & Stay'           => [],
                    'Food & Dining'         => [],
                    'Wellness & Recreation' => [],
                    'Business & Events'     => [],
                    'Transport & Concierge' => [],
                    'Security & Safety'     => [],
                    'Common Areas'          => [],
                ];

                // Map amenity names to semantic categories
                foreach ($hotelAmenitiesWithIcons as $amenity) {
                    $_amenityName = strtolower($amenity['name']);
                    
                    if (preg_match('/restaurant|lounge|bar|banquet|dining|cafe|cafeteria|beverage|food|breakfast|meal|snack|cook|chef|drinks/i', $_amenityName)) {
                        $_semanticOrder['Food & Dining'][] = $amenity;
                    } elseif (preg_match('/swimming pool|pool|jacuzzi|sauna|spa|gym|fitness|game room|game|sport|yoga|massage|wellness|recreation/i', $_amenityName)) {
                        $_semanticOrder['Wellness & Recreation'][] = $amenity;
                    } elseif (preg_match('/business center|conference|meeting room|seminar|photocopying|printing|fax/i', $_amenityName)) {
                        $_semanticOrder['Business & Events'][] = $amenity;
                    } elseif (preg_match('/parking|shuttle|airport|car rental|transport|train station pickup|concierge|tour|sightseeing|travel desk/i', $_amenityName)) {
                        $_semanticOrder['Transport & Concierge'][] = $amenity;
                    } elseif (preg_match('/security|cctv|fire safety|smoke detector|doctor|torch|sanitizer/i', $_amenityName)) {
                        $_semanticOrder['Security & Safety'][] = $amenity;
                    } elseif (preg_match('/lobby|terrace|lawn|garden|common seating|library|elevator|locker|wheelchair|public bathroom/i', $_amenityName)) {
                        $_semanticOrder['Common Areas'][] = $amenity;
                    } else {
                        $_semanticOrder['Room & Stay'][] = $amenity;
                    }
                }
                $semanticAmenities = array_filter($_semanticOrder);

                $previewAmenities = array_slice($hotelAmenitiesWithIcons, 0, 5);
                $totalAmenities   = count($hotelAmenitiesWithIcons);
                ?>
                <?php if (!empty($previewAmenities)): ?>
                <div class="mb-5">
                    <h2 class="heading-5 mb-4" style="color: var(--brand-primary);">Amenities</h2>
                    <div class="row g-3 mb-3">
                        <?php foreach ($previewAmenities as $amenity): ?>
                        <div class="col-6 col-md-4">
                            <div class="d-flex align-items-center gap-2" style="color: var(--bg-light); font-size: 14px;">
                                <i class="bi <?php echo htmlspecialchars($amenity['icon']); ?>" style="color:var(--brand-primary);font-size:16px;flex-shrink:0;"></i>
                                <span><?php echo htmlspecialchars($amenity['name']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($totalAmenities > 5): ?>
                    <a href="#" role="button" data-bs-toggle="modal" data-bs-target="#amenitiesModal"
                        style="color: var(--brand-primary); font-size: 13px; text-decoration: underline; text-underline-offset: 3px; letter-spacing: 0.3px;">
                        View all <?php echo $totalAmenities; ?> amenities &rsaquo;
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($hotelAttractions)): ?>
                <div class="mb-5">
                    <h2 class="heading-5 mb-4" style="color: var(--brand-primary);">Nearby Attractions</h2>
                    <div class="row g-3 mb-3">
                        <?php foreach ($hotelAttractions as $attraction): ?>
                        <div class="col-6 col-md-4">
                            <div class="d-flex align-items-center gap-2" style="color: var(--bg-light); font-size: 14px;">
                                <span><?php echo htmlspecialchars($attraction['attraction_name'] ?? ''); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($venue['latitude']) && !empty($venue['longitude'])): ?>
<?php
    $lat = (float) $venue['latitude'];
    $lng = (float) $venue['longitude'];

    // Google Maps embed URL using lat/lng
    $gmapEmbed = 'https://maps.google.com/maps?q=' . $lat . ',' . $lng . '&z=15&output=embed';

    // Google Maps full URL for clicking
    $gmapFullUrl = 'https://www.google.com/maps?q=' . $lat . ',' . $lng . '&z=15';
?>
<div style="border: 1px solid var(--border-medium); overflow:hidden; height:360px; position: relative; cursor: pointer;">
    <a href="<?php echo htmlspecialchars($gmapFullUrl); ?>" target="_blank" rel="noopener noreferrer" style="display: block; height: 100%; text-decoration: none;">
        <iframe
            src="<?php echo htmlspecialchars($gmapEmbed); ?>"
            width="100%"
            height="360"
            style="border:0; display:block; pointer-events: none;"
            loading="lazy"
            referrerpolicy="no-referrer"
            allowfullscreen
            title="Map showing location of <?php echo htmlspecialchars($venue['name']); ?>">
        </iframe>
        <!-- Overlay to make entire map clickable -->
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: transparent; z-index: 10;"></div>
    </a>
</div>
<p class="small text-center mt-2" style="color: var(--text-secondary);">
    Click the map to view in Google Maps
</p>
<?php else: ?>
<div class="p-4 d-flex align-items-center justify-content-center" style="background-color: var(--bg-card); border: 1px solid var(--border-medium); color: var(--text-secondary); text-align: center; height:360px;">
    No coordinates available for this venue.
</div>
<?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="p-4 sticky-top" style=" top: 100px;">
                    <h3 class="heading-5 mb-4" style="color: var(--brand-primary);">Ready to Book?</h3>
                    
                    <div class="mb-4">
                        <!-- <?php if (!empty($venue['basePrice'])): ?>
                        <div class="pb-3 mb-3" style="border-bottom: 1px solid var(--border-medium);">
                            <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Base Price</p>
                            <h4 class="heading-4 mb-1" style="color: var(--bg-light);">₹<?php echo number_format((int) $venue['basePrice']); ?></h4>
                        </div>
                        <?php endif; ?> -->

                        <!-- <?php if (!empty($venue['discountedPrice'])): ?>
                        <div class="pb-3 mb-3" style="border-bottom: 1px solid var(--border-medium);">
                            <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Stay Price</p>
                            <p class="mb-0" style="color: var(--bg-light); font-size: 1.125rem; font-weight: 500;">
                                ₹<?php echo number_format((int) $venue['discountedPrice']); ?>
                            </p>
                            <p class="small mb-0" style="color: var(--text-secondary);"><?php echo htmlspecialchars($pricingUnit); ?></p>
                        </div>
                        <?php endif; ?> -->

                        <!-- <div class="pb-3 mb-3" style="border-bottom: 1px solid var(--border-medium);">
                            <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Guest Capacity</p>
                            <p class="mb-0" style="color: var(--bg-light); font-size: 1.125rem; font-weight: 500;">
                                <?php echo $venue['capacity']['min']; ?> - <?php echo $venue['capacity']['max']; ?>
                            </p>
                        </div> -->

                        <!-- <div>
                            <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Accommodation</p>
                            <p class="mb-0" style="color: var(--bg-light); font-size: 1.125rem; font-weight: 500;"><?php echo $venue['rooms']; ?> rooms</p>
                        </div> -->

                        <?php if (!empty($venue['googleRating'])): ?>
                        <div class="mt-3">
                            <p class="small text-uppercase mb-1" style="color: var(--text-secondary); letter-spacing: 1px;">Guest Rating</p>
                            <p class="mb-0" style="color: var(--bg-light); font-size: 1.125rem; font-weight: 500;">
                                <?php echo htmlspecialchars(number_format((float) $venue['googleRating'], 1)); ?>/5
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <a href="<?php echo $venue['bookingUrl']; ?>" target="_blank" class="btn d-none btn-primary-custom w-100 mb-3 d-flex align-items-center justify-content-center">
                        <span>Visit Website</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="ms-2" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                            <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                        </svg>
                    </a>



                    <a href="/contact.php?venue=<?php echo urlencode($venue['name']); ?>" class="btn btn-secondary-custom w-100 mb-2">Request Information</a>

                    <!-- Book Now Button: If hotel_booking_url exists, show anchor tag instead of modal button -->
                    <?php if (!empty($venue['hotel_booking_url'])): ?>
                        <a href="<?php echo htmlspecialchars($venue['hotel_booking_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary-custom w-100 mb-2 d-flex align-items-center justify-content-center">
                            <span>Book Now</span>

                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-2" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                                <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                            </svg>
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary-custom w-100" data-bs-toggle="modal" data-bs-target="#bookNowModal">Book Now</button>
                    <?php endif; ?>
                    

                    <?php if (!empty($venue['hotelDetailsLink'])): ?>
                    <a href="<?php echo htmlspecialchars($venue['hotelDetailsLink']); ?>" target="_blank" class="btn btn-secondary-custom w-100 mt-2 d-flex align-items-center justify-content-center">
                        <span>Visit Website</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-2" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                            <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                        </svg>
                    </a>
                    <?php endif; ?>

                    <!-- Flatpickr JS (include if not already) -->
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        flatpickr('.flatpickr-range', {
                            mode: 'range',
                            dateFormat: 'Y-m-d',
                            minDate: 'today'
                        });
                    });
                    </script>

                    <p class="small text-center mt-4 mb-0" style="color: var(--text-secondary);">
                        Need help planning? Contact us for personalized assistance
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Amenities Modal -->
<?php if (!empty($semanticAmenities)): ?>
<div class="modal fade" id="amenitiesModal" tabindex="-1" aria-labelledby="amenitiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background-color: #ffffff; border: 1px solid var(--border-medium); font-family: Lato;">
            <div class="modal-header" style="background-color: var(--bg-card); border-bottom: 1px solid #a67c52; padding: 20px 24px;">
                <div>
                    <h5 class="modal-title mb-0" id="amenitiesModalLabel" style="color: var(--brand-primary); font-family: inherit; font-size: 1.1rem; font-weight: 600;">Guest Amenities</h5>
                    <p class="mb-0 mt-1" style="color: var(--text-secondary); font-size: 12px;"><?php echo $totalAmenities; ?> amenities available</p>
                </div>
                <button type="button" class="btn-close" data-bs-theme="dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background-color: var(--bg-card); padding: 24px;">
                <?php
                $_catIconMap = [
                    'Room & Stay'           => 'bi-door-open',
                    'Food & Dining'         => 'bi-cup-hot',
                    'Wellness & Recreation' => 'bi-heart-pulse',
                    'Business & Events'     => 'bi-briefcase',
                    'Transport & Concierge' => 'bi-car-front',
                    'Security & Safety'     => 'bi-shield-lock',
                    'Common Areas'          => 'bi-building',
                ];
                ?>
                <?php foreach ($semanticAmenities as $_sCat => $_sItems): ?>
                <div class="mb-5">
                    <h3 style="color: var(--bg-light); font-size: 1rem; font-weight: 600; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid var(--border-medium);">
                        <i class="bi <?php echo $_catIconMap[$_sCat] ?? 'bi-grid'; ?> me-2" style="color:var(--brand-primary);"></i><?php echo htmlspecialchars($_sCat); ?>
                    </h3>
                    <div class="row g-4">
                        <?php foreach ($_sItems as $_sItem): ?>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-3" style="color: var(--bg-light); font-size: 14px;">
                                <i class="bi <?php echo htmlspecialchars($_sItem['icon']); ?>" style="color:var(--brand-primary);font-size:18px;flex-shrink:0;"></i>
                                <span><?php echo htmlspecialchars($_sItem['name']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Book Now Modal: Unified, styled like contact form -->
<div class="modal fade" id="bookNowModal" tabindex="-1" aria-labelledby="bookNowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #f8f5f0; font-family: Lato;">
            <div class="modal-header" style="background-color: var(--bg-card); border-bottom: 1px solid var(--border-medium); flex-direction: column; align-items: stretch;">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <!-- <h5 class="modal-title" id="bookNowModalLabel" style="color: var(--brand-primary); font-family: inherit;">Book Now</h5> -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div style="width: 100%; text-align: center; margin-top: 8px;">
                    <span style="font-size: 1.2rem; font-weight: 600; color: var(--text-primary); letter-spacing: 0.5px; display: inline-block;"> <?php echo htmlspecialchars($venue['name']); ?></span>
                </div>
            </div>
            <div class="modal-body" style="background-color: var(--bg-card); font-family: inherit;">
                <form id="bookNowForm" class="needs-validation" novalidate autocomplete="off">
                    <input type="hidden" name="hotel_name" value="<?php echo htmlspecialchars($venue['name']); ?>">
                    <input type="hidden" name="utm_source" id="book_utm_source">
                    <input type="hidden" name="utm_medium" id="book_utm_medium">
                    <input type="hidden" name="utm_campaign" id="book_utm_campaign">
                    <input type="hidden" name="utm_term" id="book_utm_term">
                    <input type="hidden" name="utm_content" id="book_utm_content">
                    <div class="mb-3">
                        <label for="daterange" class="form-label" style="color: var(--text-secondary); font-family: inherit;">Check-in &amp; Check-out Dates</label>
                        <input type="text" class="form-control flatpickr-range" id="daterange" name="daterange" placeholder="Select Date" required>
                        <div class="invalid-feedback">Please select your stay dates.</div>
                    </div>
                    <div class="mb-3">
                        <label for="guestName" class="form-label" style="color: var(--text-secondary); font-family: inherit;">Guest Full Name</label>
                        <input type="text" class="form-control" id="guestName" name="guest_name" required>
                        <div class="invalid-feedback">Please enter your full name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="guestCount" class="form-label" style="color: var(--text-secondary); font-family: inherit;">Guest Count</label>
                        <input type="number" class="form-control" id="guestCount" name="guest_count" min="1" required>
                        <div class="invalid-feedback">Please enter number of guests.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label" style="color: var(--text-secondary); font-family: inherit;">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">Please enter a valid email.</div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label" style="color: var(--text-secondary); font-family: inherit;"> Guest Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9]{10,12}" minlength="10" maxlength="12" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'').slice(0,12)">
                        <div class="invalid-feedback">Please enter a valid phone number (10–12 digits).</div>
                    </div>
                    <button type="submit" class="btn btn-secondary-custom w-100">Book Now</button>
                    <div id="bookNowFeedback" class="mt-3" style="display:none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Contact form style validation for Book Now form -->
<script>
// Populate UTM hidden fields from URL params
(function() {
    var params = new URLSearchParams(window.location.search);
    ['utm_source','utm_medium','utm_campaign','utm_term','utm_content'].forEach(function(key) {
        var el = document.getElementById('book_' + key);
        if (el) el.value = params.get(key) || '';
    });
})();

// Bootstrap validation and AJAX for Book Now form
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('bookNowForm');
    var feedback = document.getElementById('bookNowFeedback');
    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            // AJAX submit
            var formData = new FormData(form);
            fetch('/process_booking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                feedback.style.display = 'block';
                if (data.success) {
                    feedback.className = 'alert alert-success mt-3';
                    feedback.textContent = data.message;
                    form.reset();
                    form.classList.remove('was-validated');
                    setTimeout(function() {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('bookNowModal'));
                        if (modal) modal.hide();
                        feedback.style.display = 'none';
                    }, 1800);
                } else {
                    feedback.className = 'alert alert-danger mt-3';
                    feedback.textContent = data.message;
                }
            })
            .catch(() => {
                feedback.style.display = 'block';
                feedback.className = 'alert alert-danger mt-3';
                feedback.textContent = 'An error occurred. Please try again.';
            });
        }, false);
    }
});


</script>
<?php require_once 'includes/footer.php'; ?>
