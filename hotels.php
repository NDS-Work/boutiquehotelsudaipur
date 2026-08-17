<?php
$pageTitle = "All Hotels";
require_once 'data/venues.php';

// ── Standard filters ──────────────────────────────────────────
$location  = isset($_GET['location'])  ? $_GET['location']  : 'all';
$budget    = isset($_GET['budget'])    ? $_GET['budget']    : 'all';
$capacity  = isset($_GET['capacity'])  ? $_GET['capacity']  : 'all';
$venueType = isset($_GET['venueType']) ? $_GET['venueType'] : 'all';
$search    = isset($_GET['search'])    ? $_GET['search']    : '';

$selectedAmenityIds = [];
if (isset($_GET['amenities']) && is_array($_GET['amenities'])) {
    $selectedAmenityIds = array_values(
        array_filter(array_map('intval', $_GET['amenities']), static fn($id) => $id > 0)
    );
}

// ── Slug-based filter params (set by .htaccess rewrites) ──────
$attractionSlug = isset($_GET['attraction'])  ? trim($_GET['attraction'])  : null;
$collectionSlug = isset($_GET['collection'])  ? trim($_GET['collection'])  : null;
$occasionSlug   = isset($_GET['occasion'])    ? trim($_GET['occasion'])    : null;
$amenitySlug    = isset($_GET['amenitySlug']) ? trim($_GET['amenitySlug']) : null;

// ── Resolve IDs + display names from the database ─────────────
$attractionId   = null;
$collectionId   = null;
$occasionId     = null;
$attractionName = null;
$collectionName = null;
$occasionName   = null;
$amenityName    = null;

$amenityOptions        = [];
$selectedAmenityLabels = [];

$_amenityDb = _getVenueSqliteDb();

if ($_amenityDb) {

    // All active amenities for the sidebar filter UI
    $rows = $_amenityDb
        ->query('SELECT id, name FROM link_amenities WHERE is_active = 1 ORDER BY name')
        ->fetchAll(PDO::FETCH_ASSOC);
    $amenityOptions = $rows;

    // Labels for already-selected amenity IDs (sidebar chips)
    if (!empty($selectedAmenityIds)) {
        $idMap = array_column($rows, 'name', 'id');
        foreach ($selectedAmenityIds as $amenityId) {
            if (isset($idMap[$amenityId])) {
                $selectedAmenityLabels[] = $idMap[$amenityId];
            }
        }
    }

    // Resolve attraction slug → id + name
    if ($attractionSlug) {
        $stmt = $_amenityDb->prepare(
            'SELECT id, attraction_name FROM link_attraction
              WHERE LOWER(REPLACE(attraction_name, " ", "-")) = ?'
        );
        $stmt->execute([strtolower($attractionSlug)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $attractionId   = $row['id'];
            $attractionName = $row['attraction_name'];
        }
    }

    // Resolve collection slug → id + name
    if ($collectionSlug) {
        $stmt = $_amenityDb->prepare(
            'SELECT id, collection_name FROM link_collection
              WHERE LOWER(REPLACE(collection_name, " ", "-")) = ?'
        );
        $stmt->execute([strtolower($collectionSlug)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $collectionId   = $row['id'];
            $collectionName = $row['collection_name'];
        }
    }

    // Resolve occasion slug → id + name
    if ($occasionSlug) {
        $stmt = $_amenityDb->prepare(
            'SELECT id, occasion_name FROM link_occasion
              WHERE LOWER(REPLACE(occasion_name, " ", "-")) = ?'
        );
        $stmt->execute([strtolower($occasionSlug)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $occasionId   = $row['id'];
            $occasionName = $row['occasion_name'];
        }
    }

    // Resolve amenity slug → id, inject into $selectedAmenityIds
    if ($amenitySlug && empty($selectedAmenityIds)) {
        $stmt = $_amenityDb->prepare(
            'SELECT id, name FROM link_amenities
              WHERE is_active = 1
                AND LOWER(REPLACE(name, " ", "-")) = ?'
        );
        $stmt->execute([strtolower($amenitySlug)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $selectedAmenityIds    = [(int) $row['id']];
            $selectedAmenityLabels = [$row['name']];
            $amenityName           = $row['name'];
        }
    }
}

// ── Fetch + filter venues ──────────────────────────────────────
$allFilteredVenues = filterVenues(
    $location, $budget, $capacity, $venueType, $search, true, $selectedAmenityIds
);

// Narrow by attraction
if ($attractionId > 0 && $_amenityDb) {
    $stmt = $_amenityDb->prepare(
        'SELECT DISTINCT hotel_id FROM link_attraction_hotel WHERE attraction_id = ?'
    );
    $stmt->execute([$attractionId]);
    $hotelIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allFilteredVenues = !empty($hotelIds)
        ? array_filter($allFilteredVenues, static fn($v) => in_array($v['id'], $hotelIds, false))
        : [];
}

// Narrow by collection
if ($collectionId > 0 && $_amenityDb) {
    $stmt = $_amenityDb->prepare(
        'SELECT DISTINCT hotel_id FROM link_collection_hotel WHERE collection_id = ?'
    );
    $stmt->execute([$collectionId]);
    $hotelIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allFilteredVenues = !empty($hotelIds)
        ? array_filter($allFilteredVenues, static fn($v) => in_array($v['id'], $hotelIds, false))
        : [];
}

// Narrow by occasion
if ($occasionId > 0 && $_amenityDb) {
    $stmt = $_amenityDb->prepare(
        'SELECT DISTINCT hotel_id FROM link_occasion_hotel WHERE occasion_id = ?'
    );
    $stmt->execute([$occasionId]);
    $hotelIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allFilteredVenues = !empty($hotelIds)
        ? array_filter($allFilteredVenues, static fn($v) => in_array($v['id'], $hotelIds, false))
        : [];
}

// Re-index after array_filter calls
$allFilteredVenues = array_values($allFilteredVenues);

// Featured first
usort($allFilteredVenues, fn($a, $b) => (int) $b['highlighted'] - (int) $a['highlighted']);

// ── Pagination ─────────────────────────────────────────────────
$perPage       = 9;
$totalFiltered = count($allFilteredVenues);
$totalPages    = max(1, (int) ceil($totalFiltered / $perPage));
$currentPage   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$currentPage   = max(1, min($currentPage, $totalPages));

$offset         = ($currentPage - 1) * $perPage;
$filteredVenues = array_slice($allFilteredVenues, $offset, $perPage);
$showingFrom    = $totalFiltered > 0 ? $offset + 1 : 0;
$showingTo      = min($offset + count($filteredVenues), $totalFiltered);

$hasActiveFilters = (
    $location  !== 'all' ||
    $budget    !== 'all' ||
    $capacity  !== 'all' ||
    $venueType !== 'all' ||
    trim($search) !== '' ||
    !empty($selectedAmenityIds) ||
    $attractionId > 0 ||
    $collectionId > 0 ||
    $occasionId   > 0
);

// ── Page heading & Canonical ──────────────────────────────────
$pageHeading =
    $collectionName !== null ? ''    . $collectionName :
    ($attractionName !== null ? 'Hotels near '  . $attractionName :
    ($occasionName   !== null ? $occasionName :
    ($amenityName    !== null ? 'Hotels with '  . $amenityName :
    'Explore All Boutique Hotels in Udaipur')));

if (!empty($collectionSlug)) {
    $canonicalUrl = 'https://boutiquehotelsudaipur.com/hotels/collection/' . rawurlencode($collectionSlug);
    $metaTitle = ($collectionName ?: 'Boutique Hotels') . ' in Udaipur | Boutique Hotels In Udaipur';
} elseif (!empty($occasionSlug)) {
    $canonicalUrl = 'https://boutiquehotelsudaipur.com/hotels/occasion/' . rawurlencode($occasionSlug);
    $metaTitle = ($occasionName ?: 'Occasion Stays') . ' in Udaipur | Boutique Hotels In Udaipur';
} elseif (!empty($attractionSlug)) {
    $canonicalUrl = 'https://boutiquehotelsudaipur.com/hotels/attraction/' . rawurlencode($attractionSlug);
    $metaTitle = 'Hotels near ' . ($attractionName ?: 'Attraction') . ' Udaipur | Boutique Hotels In Udaipur';
} elseif (!empty($amenitySlug)) {
    $canonicalUrl = 'https://boutiquehotelsudaipur.com/hotels/amenity/' . rawurlencode($amenitySlug);
    $metaTitle = 'Hotels with ' . ($amenityName ?: 'Amenities') . ' in Udaipur | Boutique Hotels In Udaipur';
} else {
    $canonicalUrl = 'https://boutiquehotelsudaipur.com/hotels';
    $metaTitle = 'All Boutique Hotels in Udaipur | Compare Stays, Prices & Amenities';
}

require_once 'includes/header.php';
?>

<div style="background-color: var(--bg-page); min-height: 100vh; padding-top: 100px; padding-bottom: 60px;">
    <div class="container">
        <!-- Header -->
        <div class="mb-5">
            <h1 class="heading-2 mb-3"><?php echo htmlspecialchars($pageHeading); ?></h1>
        </div>

        <?php if (!empty($selectedAmenityLabels)): ?>
        <div class="mb-4">
            <h2 class="heading-5 mb-2" style="color: var(--brand-primary);">
                Filtered by amenities: <?php echo htmlspecialchars(implode(', ', $selectedAmenityLabels)); ?>
            </h2>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="p-4 sticky-top" style="background-color: #fff; border: 1px solid #a67c52; top: 100px;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="heading-5 mb-0" style="color: var(--brand-primary);">Filters</h5>
                        <?php if ($hasActiveFilters): ?>
                        <a href="/hotels" class="btn btn-sm text-decoration-none" style="color: var(--text-secondary);">Clear All</a>
                        <?php endif; ?>
                    </div>

                    <form method="GET" action="/hotels" id="filters-form">
                        <?php if ($attractionSlug): ?>
                        <input type="hidden" name="attraction" value="<?php echo htmlspecialchars($attractionSlug); ?>">
                        <?php endif; ?>
                        <?php if ($collectionSlug): ?>
                        <input type="hidden" name="collection" value="<?php echo htmlspecialchars($collectionSlug); ?>">
                        <?php endif; ?>
                        <?php if ($occasionSlug): ?>
                        <input type="hidden" name="occasion" value="<?php echo htmlspecialchars($occasionSlug); ?>">
                        <?php endif; ?>

                        <!-- Search -->
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-semibold" style="color: var(--text-secondary); letter-spacing: 1px;">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search venues..."
                                value="<?php echo htmlspecialchars($search); ?>"
                                style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light);">
                        </div>

                        <!-- Location Filter -->
                        <div class="mb-4" id="loc-filter-wrap">
                            <label class="form-label small text-uppercase fw-semibold d-flex align-items-center" style="color: var(--text-secondary); letter-spacing: 1px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                </svg>
                                Location
                            </label>
                            <?php
                            $locationLabel = 'All Locations';
                            foreach (frontendLocationOptions() as $loc) {
                                if ($loc['slug'] === $location) { $locationLabel = $loc['label']; break; }
                            }
                            if ($location === 'lake-pichola')   $locationLabel = 'Lake Pichola Waterfront';
                            elseif ($location === 'fateh-sagar')     $locationLabel = 'Fateh Sagar Lake Area';
                            elseif ($location === 'aravalli-hills')  $locationLabel = 'Aravalli Hills & Elevated';
                            ?>
                            <button type="button" id="loc-trigger" class="form-select text-start"
                                style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light); cursor:pointer;">
                                <?php echo htmlspecialchars($locationLabel); ?>
                            </button>
                            <div id="loc-panel" style="display:none; border:1px solid var(--border-medium); background:var(--bg-page); margin-top:2px; padding:8px;">
                                <input type="text" id="location-search" placeholder="Search location..." autocomplete="off" class="form-control mb-1"
                                    style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light); font-size: 13px;">
                                <select name="location" id="location-select" size="6" class="form-select"
                                    style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light); height: auto; border: none;">
                                    <option value="lake-pichola"   <?php echo $location === 'lake-pichola'   ? 'selected' : ''; ?>>Lake Pichola Waterfront</option>
                                    <option value="fateh-sagar"    <?php echo $location === 'fateh-sagar'    ? 'selected' : ''; ?>>Fateh Sagar Lake Area</option>
                                    <option value="aravalli-hills" <?php echo $location === 'aravalli-hills' ? 'selected' : ''; ?>>Aravalli Hills & Elevated</option>
                                    <?php foreach (frontendLocationOptions() as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc['slug']); ?>"
                                        <?php echo $location === $loc['slug'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($loc['label']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Venue Type Filter -->
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-semibold d-flex align-items-center" style="color: var(--text-secondary); letter-spacing: 1px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                </svg>
                                Hotel Type
                            </label>
                            <select name="venueType" class="form-select"
                                style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light);">
                                <option value="all"               <?php echo $venueType === 'all'               ? 'selected' : ''; ?>>All Venue Types</option>
                                <option value="heritage-palace"   <?php echo $venueType === 'heritage-palace'   ? 'selected' : ''; ?>>Heritage Palaces</option>
                                <option value="lakeside"          <?php echo $venueType === 'lakeside'          ? 'selected' : ''; ?>>Lakeside & Waterfront</option>
                                <option value="luxury-resort"     <?php echo $venueType === 'luxury-resort'     ? 'selected' : ''; ?>>Luxury Resorts</option>
                                <option value="hilltop"           <?php echo $venueType === 'hilltop'           ? 'selected' : ''; ?>>Hilltop & Mountain View</option>
                                <option value="contemporary"      <?php echo $venueType === 'contemporary'      ? 'selected' : ''; ?>>Contemporary Luxury Hotels</option>
                                <option value="boutique"          <?php echo $venueType === 'boutique'          ? 'selected' : ''; ?>>Boutique & Intimate</option>
                            </select>
                        </div>

                        <!-- Amenities Filter -->
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-semibold d-flex align-items-center" style="color: var(--text-secondary); letter-spacing: 1px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M3 4a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v1H2V4zm0 2h10v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6zm2 2v1h6V8H5z"/>
                                </svg>
                                Amenities
                            </label>
                            <input type="text" id="amenities-search" class="form-control mb-3" placeholder="Search amenities..."
                                style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light);">
                            <div id="amenities-checkboxes" class="amenities-checkbox-container"
                                style="max-height: 200px; overflow-y: auto; display: none; border: 1px solid var(--border-medium); border-radius: 4px; padding: 8px; background-color: var(--bg-page);">
                                <?php foreach ($amenityOptions as $amenity): ?>
                                <div class="form-check amenity-item" style="margin-bottom: 8px;">
                                    <input class="form-check-input" type="checkbox"
                                        name="amenities[]"
                                        value="<?php echo (int) $amenity['id']; ?>"
                                        id="amenity-<?php echo (int) $amenity['id']; ?>"
                                        <?php echo in_array((int) $amenity['id'], $selectedAmenityIds, true) ? 'checked' : ''; ?>
                                        style="margin-right: 8px;">
                                    <label class="form-check-label" for="amenity-<?php echo (int) $amenity['id']; ?>"
                                        style="color: var(--bg-light); font-size: 14px; cursor: pointer; user-select: none;">
                                        <?php echo htmlspecialchars($amenity['name']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" id="apply-filters-btn"
                            class="btn <?php echo $hasActiveFilters ? 'btn-primary-custom' : 'btn-secondary-custom'; ?> w-100">
                            Apply Filters
                        </button>

                        <div class="mt-3 text-center">
                            <a href="/hotels" class="text-decoration-none" style="color: var(--text-secondary); font-size: 14px;">Clear All Filters</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Venues Grid -->
            <div class="col-lg-9">
                <?php if (count($filteredVenues) === 0): ?>
                <div class="text-center py-5">
                    <h3 class="mb-4" style="color: var(--text-secondary);">No venues found matching your filters</h3>
                    <a href="/hotels" class="btn btn-secondary-custom">Clear Filters</a>
                </div>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($filteredVenues as $venue): ?>
                    <?php
                    $gallery      = $venue['imageGallery'] ?? [];
                    $primaryImage = $gallery[0]['url'] ?? ($venue['images'][0] ?? '');
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="venue-card overflow-hidden h-100">
                            <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>" class="text-decoration-none">
                                <div class="position-relative" style="height: 250px; overflow: hidden;">
                                    <?php if ($primaryImage !== ''): ?>
                                    <img src="<?php echo htmlspecialchars($primaryImage); ?>"
                                        alt="<?php echo htmlspecialchars($venue['name']); ?>"
                                        class="w-100 h-100" style="object-fit: cover;">
                                    <?php else: ?>
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center"
                                        style="background-color: var(--bg-card); color: var(--text-secondary);">No image</div>
                                    <?php endif; ?>
                                    <?php if ($venue['highlighted']): ?>
                                    <div class="position-absolute top-0 start-0 m-3 px-3 py-1 small fw-bold text-uppercase"
                                        style="background-color: var(--brand-primary); color: var(--text-inverse);">Featured</div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="p-3">
                                <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>" class="text-decoration-none">
                                    <h5 class="heading-6 mb-3" style="color: var(--brand-primary);"><?php echo htmlspecialchars($venue['name']); ?></h5>
                                </a>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2 small" style="color: var(--text-secondary);">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                        </svg>
                                        <?php echo htmlspecialchars($venue['location']); ?>
                                    </div>
                                    <!-- <div class="d-flex align-items-center small" style="color: var(--text-secondary);">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                        </svg>
                                        <?php echo $venue['rooms']; ?> rooms
                                    </div> -->
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>"
                                        class="btn btn-primary-custom flex-fill text-center small py-2">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-5">
                    <div class="small" style="color: var(--text-secondary);">
                        Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        $baseQuery = [
                            'location'   => $location,
                            'budget'     => $budget,
                            'capacity'   => $capacity,
                            'venueType'  => $venueType,
                            'search'     => $search,
                            'amenities'  => $selectedAmenityIds,
                            'attraction' => $attractionSlug,
                            'collection' => $collectionSlug,
                            'occasion'   => $occasionSlug,
                        ];
                        ?>
                        <?php if ($currentPage > 1): ?>
                        <a href="/hotels?<?php echo htmlspecialchars(http_build_query(array_merge($baseQuery, ['page' => $currentPage - 1]))); ?>"
                            class="btn btn-secondary-custom">Previous</a>
                        <?php endif; ?>

                        <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                        <a href="/hotels?<?php echo htmlspecialchars(http_build_query(array_merge($baseQuery, ['page' => $p]))); ?>"
                            class="btn <?php echo $p === $currentPage ? 'btn-primary-custom' : 'btn-secondary-custom'; ?>"
                            style="min-width: 44px;"><?php echo $p; ?></a>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                        <a href="/hotels?<?php echo htmlspecialchars(http_build_query(array_merge($baseQuery, ['page' => $currentPage + 1]))); ?>"
                            class="btn btn-secondary-custom">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form   = document.getElementById('filters-form');
    var button = document.getElementById('apply-filters-btn');
    if (!form || !button) return;

    function hasActiveFilters() {
        var location  = form.elements['location'];
        var venueType = form.elements['venueType'];
        var search    = form.elements['search'];
        return (location  && location.value  !== 'all')
            || (venueType && venueType.value !== 'all')
            || (search    && search.value.trim() !== '');
    }

    function syncApplyButtonState() {
        var active = hasActiveFilters();
        button.classList.toggle('btn-primary-custom',   active);
        button.classList.toggle('btn-secondary-custom', !active);
    }

    form.addEventListener('change', syncApplyButtonState);
    form.addEventListener('input',  syncApplyButtonState);
    syncApplyButtonState();

    // Location dropdown
    var locSearch  = document.getElementById('location-search');
    var locSelect  = document.getElementById('location-select');
    var locTrigger = document.getElementById('loc-trigger');
    var locPanel   = document.getElementById('loc-panel');
    var locWrap    = document.getElementById('loc-filter-wrap');

    if (locTrigger && locPanel) {
        locTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = locPanel.style.display !== 'none';
            locPanel.style.display = isOpen ? 'none' : 'block';
            if (!isOpen && locSearch) { locSearch.value = ''; locSearch.focus(); }
        });
        document.addEventListener('click', function (e) {
            if (!locWrap.contains(e.target)) locPanel.style.display = 'none';
        });
    }

    if (locSearch && locSelect) {
        var allOptions = Array.from(locSelect.options);
        locSearch.addEventListener('input', function () {
            var q = locSearch.value.trim().toLowerCase();
            allOptions.forEach(function (opt) {
                opt.hidden = q !== '' && opt.text.toLowerCase().indexOf(q) === -1;
            });
        });
        locSelect.addEventListener('change', function () {
            var sel = locSelect.options[locSelect.selectedIndex];
            if (locTrigger) locTrigger.textContent = sel ? sel.text : 'All Locations';
            locPanel.style.display = 'none';
            syncApplyButtonState();
        });
    }

    // Amenities search + show/hide
    var amenitiesSearch    = document.getElementById('amenities-search');
    var amenitiesCheckboxes = document.getElementById('amenities-checkboxes');

    if (amenitiesSearch && amenitiesCheckboxes) {
        var allAmenityItems = Array.from(amenitiesCheckboxes.querySelectorAll('.amenity-item'));

        amenitiesCheckboxes.style.display = 'none';

        amenitiesSearch.addEventListener('focus', function () {
            amenitiesCheckboxes.style.display = 'block';
        });
        amenitiesSearch.addEventListener('blur', function () {
            setTimeout(function () {
                var hasSelected = Array.from(amenitiesCheckboxes.querySelectorAll('input[type="checkbox"]'))
                    .some(function (cb) { return cb.checked; });
                if (!hasSelected && !amenitiesCheckboxes.contains(document.activeElement)) {
                    amenitiesCheckboxes.style.display = 'none';
                }
            }, 150);
        });

        // Keep open if pre-selected on load
        var hasPreSelected = Array.from(amenitiesCheckboxes.querySelectorAll('input[type="checkbox"]'))
            .some(function (cb) { return cb.checked; });
        if (hasPreSelected) amenitiesCheckboxes.style.display = 'block';

        amenitiesSearch.addEventListener('input', function () {
            var q = amenitiesSearch.value.trim().toLowerCase();
            allAmenityItems.forEach(function (item) {
                var label = item.querySelector('.form-check-label');
                item.style.display = (q === '' || label.textContent.toLowerCase().indexOf(q) !== -1) ? 'block' : 'none';
            });
        });

        amenitiesCheckboxes.addEventListener('change', syncApplyButtonState);
        amenitiesCheckboxes.addEventListener('focusin', function () {
            amenitiesCheckboxes.style.display = 'block';
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>