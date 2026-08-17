<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../data/venues.php';

$currentPage = 'dashboard';
$pageTitle = 'Dashboard';

// CRITICAL CHANGE: Pass 'false' to show ALL venues (including inactive ones) in Admin
// You must provide all 5 string arguments before the boolean 'false'
$allVenues = filterVenues('all', 'all', 'all', 'all', '', false);

$totalVenues = count($allVenues);
$highlighted = count(array_filter($allVenues, fn($v) => $v['highlighted'] ?? false));

// Count amenities and images
$totalAmenities = 0;
$totalImages = 0;
foreach ($allVenues as $venue) {
    $totalAmenities += count($venue['amenities'] ?? []);
    $totalImages += count($venue['images'] ?? []);
}

// Get recent venues (first 5)
$recentVenues = array_slice($allVenues, 0, 5);
$recentVenues = array_map(fn($v) => [
    'id' => $v['id'] ?? rand(1000, 9999),
    'name' => $v['name'] ?? 'Unknown',
    'slug' => $v['slug'] ?? '',
    'highlighted' => $v['highlighted'] ?? false,
    'isActive' => $v['isActive'] ?? false, // Added for status badge
    'price_per_plate' => $v['pricePerPlate'] ?? 0,
    'location' => $v['location'] ?? 'Unknown',
    'thumb' => $v['images'][0] ?? ''
], $recentVenues);

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Dashboard</h1>
    <div class="topbar-actions">
        <a href="/admin/venue-add.php" class="btn btn-primary">+ Add Venue</a>
    </div>
</div>

<div class="content">

    <div class="stats">
        <div class="stat">
            <div class="stat-value"><?php echo $totalVenues; ?></div>
            <div class="stat-label">Total Venues</div>
        </div>
        <div class="stat">
            <div class="stat-value"><?php echo $highlighted; ?></div>
            <div class="stat-label">Featured</div>
        </div>
        <div class="stat">
            <div class="stat-value"><?php echo $totalImages; ?></div>
            <div class="stat-label">Images</div>
        </div>
        <div class="stat">
            <div class="stat-value"><?php echo $totalAmenities; ?></div>
            <div class="stat-label">Amenities</div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Recent Venues</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Price/Plate</th>
                        <th>Status</th>
                        <th>Visibility</th> <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentVenues as $v): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($v['thumb'] ?? ''); ?>" class="venue-thumb" onerror="this.style.display='none'"></td>
                        <td><?php echo htmlspecialchars($v['name']); ?></td>
                        <td><?php echo htmlspecialchars($v['location']); ?></td>
                        <td>₹<?php echo number_format($v['price_per_plate']); ?></td>
                        <td>
                            <?php if ($v['highlighted']): ?>
                            <span class="badge badge-green">Featured</span>
                            <?php else: ?>
                            <span class="badge badge-gray">Normal</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($v['isActive']): ?>
                                <span class="badge badge-green">Active</span>
                            <?php else: ?>
                                <span class="badge badge-red" style="background-color: #fee2e2; color: #991b1b;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/admin/venue-edit.php?slug=<?php echo urlencode($v['slug']); ?>" class="btn btn-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>