<?php
$pageTitle = "All Hotels";
require_once 'data/venues.php';

// Get filter parameters
$location = isset($_GET['location']) ? $_GET['location'] : 'all';
$budget = isset($_GET['budget']) ? $_GET['budget'] : 'all';
$capacity = isset($_GET['capacity']) ? $_GET['capacity'] : 'all';
$venueType = isset($_GET['venueType']) ? $_GET['venueType'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$attractionId = isset($_GET['attraction']) ? (int) $_GET['attraction'] : null;
$attractionName = null;

// Filter venues by attraction if specified
if ($attractionId !== null && $attractionId > 0) {
    $db = _getVenueSqliteDb();
    $allFilteredVenues = [];
    if ($db) {
        try {
            $stmt = $db->prepare('SELECT attraction_name FROM link_attraction WHERE id = ?');
            $stmt->execute([$attractionId]);
            $attractionName = $stmt->fetchColumn() ?: null;

            // Get hotel IDs linked to this attraction
            $stmt = $db->prepare('SELECT DISTINCT hotel_id FROM link_attraction_hotel WHERE attraction_id = ?');
            $stmt->execute([$attractionId]);
            $hotelIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($hotelIds)) {
                // Apply additional filters
                $allFilteredVenues = filterVenues($location, $budget, $capacity, $venueType, $search);
                // Filter to only include hotels linked to this attraction
                $allFilteredVenues = array_filter($allFilteredVenues, function($venue) use ($hotelIds) {
                    return in_array($venue['id'], $hotelIds, false);
                });
            }
        } catch (Exception $e) {
            error_log('Error filtering by attraction: ' . $e->getMessage());
            $allFilteredVenues = filterVenues($location, $budget, $capacity, $venueType, $search);
        }
    }
} else {
    // Filter venues normally
    $allFilteredVenues = filterVenues($location, $budget, $capacity, $venueType, $search);
}

$pageTitle = $attractionName !== null ? 'Hotels near ' . $attractionName : 'All Hotels';

$perPage = 9;
$totalFiltered = count($allFilteredVenues);
$totalPages = max(1, (int) ceil($totalFiltered / $perPage));
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;
$filteredVenues = array_slice($allFilteredVenues, $offset, $perPage);
$showingFrom = $totalFiltered > 0 ? $offset + 1 : 0;
$showingTo = min($offset + count($filteredVenues), $totalFiltered);
$hasActiveFilters = ($location !== 'all' || $budget !== 'all' || $capacity !== 'all' || $venueType !== 'all' || trim($search) !== '' || $attractionId !== null);

require_once 'includes/header.php';
?>

<div style="background-color: var(--bg-page); min-height: 100vh; padding-top: 100px; padding-bottom: 60px;">
    <div class="container">
        <!-- Header -->
        <div class="mb-5">
            <h1 class="heading-2 mb-3"><?php echo htmlspecialchars($attractionName !== null ? 'Hotels near ' . $attractionName : 'Explore All Boutique Hotels in Udaipur (500+ Stays)'); ?></h1>
            <!-- <p class="lead" style="color: var(--text-secondary);">
                Showing <?php echo $showingFrom; ?>-<?php echo $showingTo; ?> of <?php echo $totalFiltered; ?> matching venues (<?php echo $totalVenues; ?> total)
            </p> -->
        </div>

        <div class="row g-4">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="p-4 sticky-top" style="background-color: #fff; border: 1px solid #a67c52; top: 100px;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="heading-5 mb-0" style="color: var(--brand-primary);">Filters</h5>
                        <?php if ($location !== 'all' || $budget !== 'all' || $capacity !== 'all' || $venueType !== 'all' || !empty($search)): ?>
                        <a href="/venues.php" class="btn btn-sm text-decoration-none" style="color: var(--text-secondary);">Clear All</a>
                        <?php endif; ?>
                    </div>

                    <form method="GET" action="/venues.php" id="filters-form">
                        <!-- Search -->
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-semibold" style="color: var(--text-secondary); letter-spacing: 1px;">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search venues..." value="<?php echo htmlspecialchars($search); ?>" style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light);">
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
                                if ($location === 'lake-pichola') $locationLabel = 'Lake Pichola Waterfront';
                                elseif ($location === 'fateh-sagar') $locationLabel = 'Fateh Sagar Lake Area';
                                elseif ($location === 'aravalli-hills') $locationLabel = 'Aravalli Hills & Elevated';
                            ?>
                            <!-- Trigger button styled like other selects -->
                            <button type="button" id="loc-trigger" class="form-select text-start" style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light); cursor:pointer;">
                                <?php echo htmlspecialchars($locationLabel); ?>
                            </button>
                            <!-- Panel: hidden by default -->
                            <div id="loc-panel" style="display:none; border:1px solid var(--border-medium); background:var(--bg-page); margin-top:2px; padding:8px;">
                                <input type="text" id="location-search" placeholder="Search location..." autocomplete="off" class="form-control mb-1" style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light); font-size: 13px;">
                                <select name="location" id="location-select" size="6" class="form-select" style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light); height: auto; border: none;">
                                    <option value="all" <?php echo $location === 'all' ? 'selected' : ''; ?>>All Locations</option>
                                    <option value="lake-pichola" class="opt-2" <?php echo $location === 'lake-pichola' ? 'selected' : ''; ?>>Lake Pichola Waterfront</option>
                                    <option value="fateh-sagar" class="opt-2" <?php echo $location === 'fateh-sagar' ? 'selected' : ''; ?>>Fateh Sagar Lake Area</option>
                                    <option value="aravalli-hills" class="opt-2" <?php echo $location === 'aravalli-hills' ? 'selected' : ''; ?>>Aravalli Hills & Elevated</option>
                                    <?php foreach (frontendLocationOptions() as $loc): ?>
                                        <option value="<?php echo htmlspecialchars($loc['slug']); ?>" class="opt-2" <?php echo $location === $loc['slug'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($loc['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Budget Filter -->
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-semibold d-flex align-items-center" style="color: var(--text-secondary); letter-spacing: 1px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                                </svg>
                                Budget (Per Plate)
                            </label>
                            <select name="budget" class="form-select" style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light);">
                                <option value="all" <?php echo $budget === 'all' ? 'selected' : ''; ?>>All Budgets</option>
                                <option value="budget-friendly" <?php echo $budget === 'budget-friendly' ? 'selected' : ''; ?>>Budget-Friendly (₹950-1,500)</option>
                                <option value="mid-range" <?php echo $budget === 'mid-range' ? 'selected' : ''; ?>>Mid-Range (₹1,500-2,200)</option>
                                <option value="premium-luxury" <?php echo $budget === 'premium-luxury' ? 'selected' : ''; ?>>Premium Luxury (₹2,200-4,000)</option>
                                <option value="ultra-luxury" <?php echo $budget === 'ultra-luxury' ? 'selected' : ''; ?>>Ultra-Luxury (₹4,000+)</option>
                            </select>
                        </div>

                        <!-- Capacity Filter -->
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-semibold d-flex align-items-center" style="color: var(--text-secondary); letter-spacing: 1px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                                </svg>
                                Guest Capacity
                            </label>
                            <select name="capacity" class="form-select" style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light);">
                                <option value="all" <?php echo $capacity === 'all' ? 'selected' : ''; ?>>All Capacities</option>
                                <option value="intimate" <?php echo $capacity === 'intimate' ? 'selected' : ''; ?>>Intimate (Up to 150)</option>
                                <option value="medium" <?php echo $capacity === 'medium' ? 'selected' : ''; ?>>Medium (150-400)</option>
                                <option value="large" <?php echo $capacity === 'large' ? 'selected' : ''; ?>>Large (400-800)</option>
                                <option value="grand" <?php echo $capacity === 'grand' ? 'selected' : ''; ?>>Grand (800+)</option>
                            </select>
                        </div>

                        <!-- Venue Type Filter -->
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-semibold d-flex align-items-center" style="color: var(--text-secondary); letter-spacing: 1px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                </svg>
                                Venue Type
                            </label>
                            <select name="venueType" class="form-select" style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light);">
                                <option value="all" <?php echo $venueType === 'all' ? 'selected' : ''; ?>>All Venue Types</option>
                                <option value="heritage-palace" <?php echo $venueType === 'heritage-palace' ? 'selected' : ''; ?>>Heritage Palaces</option>
                                <option value="lakeside" <?php echo $venueType === 'lakeside' ? 'selected' : ''; ?>>Lakeside & Waterfront</option>
                                <option value="luxury-resort" <?php echo $venueType === 'luxury-resort' ? 'selected' : ''; ?>>Luxury Resorts</option>
                                <option value="hilltop" <?php echo $venueType === 'hilltop' ? 'selected' : ''; ?>>Hilltop & Mountain View</option>
                                <option value="contemporary" <?php echo $venueType === 'contemporary' ? 'selected' : ''; ?>>Contemporary Luxury Hotels</option>
                                <option value="boutique" <?php echo $venueType === 'boutique' ? 'selected' : ''; ?>>Boutique & Intimate</option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            id="apply-filters-btn"
                            class="btn <?php echo $hasActiveFilters ? 'btn-primary-custom' : 'btn-secondary-custom'; ?> w-100"
                        >Apply Filters</button>
                    </form>
                </div>
            </div>

            <!-- Venues Grid -->
            <div class="col-lg-9">
                <?php if (count($filteredVenues) === 0): ?>
                    <div class="text-center py-5">
                        <h3 class="mb-4" style="color: var(--text-secondary);">No venues found matching your filters</h3>
                        <a href="/venues.php" class="btn btn-secondary-custom">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($filteredVenues as $venue): ?>
                        <?php
                        $gallery = $venue['imageGallery'] ?? [];
                        $primaryImage = $gallery[0]['url'] ?? ($venue['images'][0] ?? '');
                        ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="venue-card overflow-hidden h-100">
                                <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>" class="text-decoration-none">
                                    <div class="position-relative" style="height: 250px; overflow: hidden;">
                                        <?php if ($primaryImage !== ''): ?>
                                        <img src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?>" class="w-100 h-100" style="object-fit: cover;">
                                        <?php else: ?>
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: var(--bg-card); color: var(--text-secondary);">No image</div>
                                        <?php endif; ?>
                                        <?php if ($venue['highlighted']): ?>
                                        <div class="position-absolute top-0 start-0 m-3 px-3 py-1 small fw-bold text-uppercase" style="background-color: var(--brand-primary); color: var(--text-inverse);">
                                            Featured
                                        </div>
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
                                        <div class="d-flex align-items-center mb-2 small" style="color: var(--text-secondary);">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                                <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                                            </svg>
                                            ₹<?php echo number_format($venue['pricePerPlate']); ?>/plate
                                        </div>
                                        <div class="d-flex align-items-center mb-2 small" style="color: var(--text-secondary);">
                                            <svg xmlns="http:/Per Plate/www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                                            </svg>
                                            <?php echo $venue['capacity']['min']; ?>-<?php echo $venue['capacity']['max']; ?> guests
                                        </div>
                                        <div class="d-flex align-items-center small" style="color: var(--text-secondary);">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                            </svg>
                                            <?php echo $venue['rooms']; ?> rooms
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>" class="btn btn-primary-custom flex-fill text-center small py-2">
                                            View Details
                                        </a>
                                        <!-- <a href="<?php echo $venue['bookingUrl']; ?>" target="_blank" class="btn btn-secondary-custom flex-fill text-center small py-2 d-flex align-items-center justify-content-center">
                                            <span>Book</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                                                <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                                            </svg>
                                        </a> -->
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
                                'location' => $location,
                                'budget' => $budget,
                                'capacity' => $capacity,
                                'venueType' => $venueType,
                                'search' => $search,
                            ];
                            ?>

                            <?php if ($currentPage > 1): ?>
                            <?php $prevQuery = http_build_query(array_merge($baseQuery, ['page' => $currentPage - 1])); ?>
                            <a href="/venues.php?<?php echo htmlspecialchars($prevQuery); ?>" class="btn btn-secondary-custom">Previous</a>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            for ($p = $startPage; $p <= $endPage; $p++):
                                $pageQuery = http_build_query(array_merge($baseQuery, ['page' => $p]));
                            ?>
                            <a
                                href="/venues.php?<?php echo htmlspecialchars($pageQuery); ?>"
                                class="btn <?php echo $p === $currentPage ? 'btn-primary-custom' : 'btn-secondary-custom'; ?>"
                                style="min-width: 44px;"
                            ><?php echo $p; ?></a>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                            <?php $nextQuery = http_build_query(array_merge($baseQuery, ['page' => $currentPage + 1])); ?>
                            <a href="/venues.php?<?php echo htmlspecialchars($nextQuery); ?>" class="btn btn-secondary-custom">Next</a>
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
    var form = document.getElementById('filters-form');
    var button = document.getElementById('apply-filters-btn');
    if (!form || !button) {
        return;
    }

    function hasActiveFilters() {
        var location = form.elements['location'];
        var budget = form.elements['budget'];
        var capacity = form.elements['capacity'];
        var venueType = form.elements['venueType'];
        var search = form.elements['search'];

        return (location && location.value !== 'all')
            || (budget && budget.value !== 'all')
            || (capacity && capacity.value !== 'all')
            || (venueType && venueType.value !== 'all')
            || (search && search.value.trim() !== '');
    }

    function syncApplyButtonState() {
        var active = hasActiveFilters();
        button.classList.toggle('btn-primary-custom', active);
        button.classList.toggle('btn-secondary-custom', !active);
    }

    form.addEventListener('change', syncApplyButtonState);
    form.addEventListener('input', syncApplyButtonState);
    syncApplyButtonState();

    // Location search box — filters visible options live
    var locSearch = document.getElementById('location-search');
    var locSelect = document.getElementById('location-select');
    var locTrigger = document.getElementById('loc-trigger');
    var locPanel = document.getElementById('loc-panel');
    var locWrap = document.getElementById('loc-filter-wrap');

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
                opt.hidden = q !== '' && opt.value !== 'all' && opt.text.toLowerCase().indexOf(q) === -1;
            });
        });
        // Update trigger label when an option is selected
        locSelect.addEventListener('change', function () {
            var sel = locSelect.options[locSelect.selectedIndex];
            if (locTrigger) locTrigger.textContent = sel ? sel.text : 'All Locations';
            locPanel.style.display = 'none';
            syncApplyButtonState();
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
