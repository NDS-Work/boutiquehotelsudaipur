<?php
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../data/venues.php';

$currentPage = 'venues';
$pageTitle = 'All Venues';

// Get all venues from SQLite

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$allVenues = filterVenues();
$venues = array_map(fn($v) => [
    'id' => $v['id'] ?? rand(1000, 9999),
    'name' => $v['name'] ?? 'Unknown',
    'slug' => $v['slug'] ?? '',
    'highlighted' => $v['highlighted'] ?? false,
    'price_per_plate' => $v['pricePerPlate'] ?? 0,
    'capacity_min' => $v['capacity']['min'] ?? 0,
    'capacity_max' => $v['capacity']['max'] ?? 0,
    'rooms' => $v['rooms'] ?? 0,
    'location' => $v['location'] ?? 'Unknown',
    'price_range' => $v['budgetCategory'] ?? 'N/A',
    'thumb' => $v['images'][0] ?? '',
    'google_rating' => $v['googleRating'] ?? null,
    'star_rating' => $v['starRating'] ?? ''
], $allVenues);
if ($search !== '') {
    $venues = array_filter($venues, function($v) use ($search) {
        return stripos($v['name'], $search) !== false;
    });
}

require_once __DIR__ . '/layout-header.php';
?>


<div class="topbar">
    <h1>All Venues</h1>
    <div class="topbar-actions">
        <form method="get" action="" style="display:flex; gap: 5px; margin-right: 12px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by hotel name..." style="padding:4px 8px; border-radius:4px; border:1px solid #ccc; min-width:180px;">
            <button type="submit" class="btn btn-sm btn-secondary">Search</button>
            <?php if ($search !== ''): ?>
                <a href="/admin/venues.php" class="btn btn-sm btn-link">Clear</a>
            <?php endif; ?>
        </form>
        <a href="/admin/venue-add.php" class="btn btn-primary">+ Add Venue</a>
        <span style="color: #556055; font-size: 12px;">Showing <?php echo count($venues); ?> venues</span>
    </div>
</div>

<div class="content">

    <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Venue saved successfully.</div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title"><?php echo count($venues); ?> venues total</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Price/Plate</th>
                        <th>Rating</th>
                        <th>Capacity</th>
                        <th>Rooms</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($venues as $v): ?>
                    <tr>
                        <td>
                            <?php if ($v['thumb']): ?>
                            <img src="<?php echo htmlspecialchars($v['thumb']); ?>" class="venue-thumb" onerror="this.style.display='none'">
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color:var(--text)"><?php echo htmlspecialchars($v['name']); ?></strong><br>
                            <small style="color:var(--muted)"><?php echo htmlspecialchars($v['slug']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($v['location']); ?></td>
                        <td>₹<?php echo number_format($v['price_per_plate']); ?></td>
                        <td>
                            <?php if ($v['google_rating'] !== null): ?>
                            <?php echo htmlspecialchars((string) $v['google_rating']); ?>
                            <?php if ($v['star_rating'] !== ''): ?>
                            <br><small style="color:var(--muted)"><?php echo htmlspecialchars($v['star_rating']); ?></small>
                            <?php endif; ?>
                            <?php else: ?>
                            <span style="color:var(--muted)">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $v['capacity_min']; ?>–<?php echo $v['capacity_max']; ?></td>
                        <td><?php echo $v['rooms']; ?></td>
                        <td>
                            <?php if ($v['highlighted']): ?>
                            <span class="badge badge-green">Featured</span>
                            <?php else: ?>
                            <span class="badge badge-gray">Normal</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap">
                            <a href="/admin/venue-edit.php?slug=<?php echo htmlspecialchars($v['slug']); ?>" class="btn btn-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>