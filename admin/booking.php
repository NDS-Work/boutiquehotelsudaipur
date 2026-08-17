<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$currentPage = 'booking';
$pageTitle = 'Bookings';


$db = new SQLite3(__DIR__ . '/../data/new.sqlite.db');

// Fetch all statuses for select box
$statusOptions = [];
$statusDefaultId = 1;
$statusRes = $db->query('SELECT * FROM link_status ORDER BY sort_order ASC');
while ($row = $statusRes->fetchArray(SQLITE3_ASSOC)) {
    $statusOptions[] = $row;
}
if (count($statusOptions)) {
    // Default is the one with lowest sort_order
    $statusDefaultId = $statusOptions[0]['id'];
}

// Function to delete a booking by ID
function deleteBooking($db, $id) {
    $id = (int)$id;
    $db->exec("DELETE FROM link_book WHERE id = $id");
}

// Handle delete action BEFORE any output
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    deleteBooking($db, $_GET['delete']);
    header('Location: booking.php');
    exit;
}

// Count today's bookings
$today = date('Y-m-d');

$tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='link_book'");
$hotelSearch = isset($_GET['hotel_search']) ? trim($_GET['hotel_search']) : '';
if ($tableExists) {
    $countToday = $db->querySingle("SELECT COUNT(*) FROM link_book WHERE DATE(created_at) = '$today'");
    if ($hotelSearch !== '') {
        $stmt = $db->prepare("SELECT * FROM link_book WHERE hotel_name LIKE ? ORDER BY created_at DESC");
        $stmt->bindValue(1, '%' . $hotelSearch . '%', SQLITE3_TEXT);
        $results = $stmt->execute();
    } else {
        $results = $db->query("SELECT * FROM link_book ORDER BY created_at DESC");
    }
} else {
    $countToday = 0;
    $results = false;
}

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Bookings</h1>
</div>

<div class="content">
    <div class="card">
        <div class="card-title">Booking Information</div>
        <div class="d-flex align-items-center justify-content-between stats" style="margin-bottom: 0; gap: 16px; flex-wrap: wrap;">
            <div class="stat">
                <div class="stat-value"><?php echo $countToday; ?></div>
                <div class="stat-label">Bookings Today</div>
            </div>
            <form method="get" action="booking.php" class="d-flex align-items-center flex-nowrap ms-3" style="gap: 0; flex-direction: row; white-space: nowrap;">
                <input type="text" name="hotel_search" value="<?php echo htmlspecialchars($_GET['hotel_search'] ?? ''); ?>" placeholder="Search hotel..." style="background: #000; color: #fff; border: 2px solid #9ebffe; border-radius: 4px 0 0 4px; padding: 6px 12px; outline: none; min-width: 180px; font-size: 15px;">
                <button type="submit" class="btn" style="background: #9ebffe; color: #000; border: 2px solid #9ebffe; border-left: none; border-radius: 0 4px 4px 0; padding: 6px 18px; font-weight: 600;">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Latest Bookings</div>
        <div class="table-wrap">
            <?php if ($results): ?>
            <table>
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Guest Count</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Hotel Name</th>
                        <th>Status</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Date/Time</th>
                        <th>Actions</th>
                        <th>Info</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['guest_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['guest_count']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['hotel_name']); ?></td>
                        <td>
                            <form method="post" action="update_status.php" style="margin:0;">
                                <input type="hidden" name="booking_id" value="<?php echo (int)$row['id']; ?>">
                                <?php
                                // Find the color for the current status
                                $currentStatusId = ($row['status_id'] ?? $statusDefaultId);
                                $currentColor = '#6c757d';
                                foreach ($statusOptions as $status) {
                                    if ($status['id'] == $currentStatusId) {
                                        $currentColor = htmlspecialchars($status['color_code']);
                                        break;
                                    }
                                }
                                ?>
                                <select name="status_id" class="form-select status-select" style="min-width:120px; font-weight:600; color:<?php echo $currentColor; ?>;"
                                    onchange="this.form.submit(); updateSelectColor(this)">
                                    <?php foreach ($statusOptions as $status): 
                                        $selected = $currentStatusId == $status['id'] ? 'selected' : '';
                                        $color = htmlspecialchars($status['color_code']);
                                        $slug = htmlspecialchars($status['status_slug']);
                                    ?>
                                        <option value="<?php echo (int)$status['id']; ?>" <?php echo $selected; ?> style="color:<?php echo $color; ?>;">
                                            <?php echo htmlspecialchars($status['status_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td><?php echo htmlspecialchars($row['check_in_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['check_out_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this booking?');">Delete</a>
                        </td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm info-modal-btn" style="background-color: transparent; color: green; border: 1px solid green" 
                                data-booking='<?php echo json_encode([
                                    "utm_source" => $row["utm_source"] ?? "",
                                    "utm_campaign" => $row["utm_campaign"] ?? "",
                                    "utm_medium" => $row["utm_medium"] ?? "",
                                    "utm_term" => $row["utm_term"] ?? "",
                                    "utm_content" => $row["utm_content"] ?? "",
                                    "ip_address" => $row["ip_address"] ?? "",
                                    "ip_city" => $row["ip_city"] ?? ""
                                ]); ?>'>
                                info
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div style="padding: 24px; color: var(--text-secondary); text-align: center;">No booking table found. Please ensure the booking form is set up and bookings are being stored.</div>
            <?php endif; ?>
        </div>
    </div>
</div>



<!-- Additional Information Card as Centered Modal Overlay (hidden by default) -->
<div id="additionalInfoOverlay" style="display:none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.35); z-index: 9999; align-items: center; justify-content: center;">
    <div id="additionalInfoCard" class="card" style="max-width: 480px; width: 95vw; border: 2px solid #9ebffe; box-shadow: 0 8px 32px rgba(0,0,0,0.18); position: relative; margin: 0 auto;">
        <div class="card-title" style=" color: #fff; font-weight: 700; font-size: 1.2rem; border-radius: 8px 8px 0 0; padding: 16px 20px;">Additional Information</div>
        <div class="card-body" style="padding: 20px 24px 12px 24px;">
            <div style="margin-bottom: 18px;">
                <div style="font-weight: 600; color: #9ebffe; margin-bottom: 6px;">UTM Parameters</div>
                <div style="margin-left: 12px;">
                    <div><b>Source:</b> <span id="utm_source">-</span></div>
                    <div><b>Medium:</b> <span id="utm_medium">-</span></div>
                    <div><b>Campaign:</b> <span id="utm_campaign">-</span></div>
                    <div><b>Term:</b> <span id="utm_term">-</span></div>
                    <div><b>Content:</b> <span id="utm_content">-</span></div>
                </div>
            </div>
            <div style="border-top: 1px solid #eee; padding-top: 10px;">
                <div style="font-weight: 600; color: #9ebffe; margin-bottom: 6px;">IP Information</div>
                <div style="margin-left: 12px;">
                    <div><b>IP Address:</b> <span id="ip_address">-</span></div>
                    <div><b>IP City:</b> <span id="ip_city">-</span></div>
                </div>
            </div>
        </div>
        <div class="card-footer" style="border-radius: 0 0 8px 8px; text-align: right; padding: 12px 24px;">
            <button type="button" class="btn btn-secondary" id="additionalInfoCloseBtn" style="background: #9ebffe; color: #fff; border: none;">Close</button>
        </div>
    </div>
</div>



<?php require_once __DIR__ . '/layout-footer.php'; ?>
<script>
function updateSelectColor(select) {
        var selectedOption = select.options[select.selectedIndex];
        select.style.color = selectedOption.style.color;
}
document.addEventListener('DOMContentLoaded', function () {
    // Set initial color for all status selects
    document.querySelectorAll('.status-select').forEach(function(select) {
        var selectedOption = select.options[select.selectedIndex];
        select.style.color = selectedOption.style.color;
    });

    // Additional Info Card logic
    var lastBtn = null;
    var infoOverlay = document.getElementById('additionalInfoOverlay');
    var infoCard = document.getElementById('additionalInfoCard');
    var closeBtn = document.getElementById('additionalInfoCloseBtn');
    function closeInfoCard() {
        infoOverlay.style.display = 'none';
        lastBtn = null;
    }
    document.querySelectorAll('.info-modal-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            // If the same button is clicked again, close the card
            if (lastBtn === btn && infoCard.style.display === 'block') {
                closeInfoCard();
                return;
            }
            lastBtn = btn;
            var data = btn.getAttribute('data-booking');
            var info = {};
            try { info = JSON.parse(data); } catch (e) {}
            // Set UTM fields
            document.getElementById('utm_source').textContent = info.utm_source || '-';
            document.getElementById('utm_medium').textContent = info.utm_medium || '-';
            document.getElementById('utm_campaign').textContent = info.utm_campaign || '-';
            document.getElementById('utm_term').textContent = info.utm_term || '-';
            document.getElementById('utm_content').textContent = info.utm_content || '-';
            // Set IP fields
            document.getElementById('ip_address').textContent = info.ip_address || '-';
            document.getElementById('ip_city').textContent = info.ip_city || '-';
            infoOverlay.style.display = 'flex';
            // Optionally focus the close button for accessibility
            closeBtn.focus();
        });
    });
    closeBtn.addEventListener('click', closeInfoCard);
    // Also close overlay if user clicks outside the card
    infoOverlay.addEventListener('click', function(e) {
        if (e.target === infoOverlay) closeInfoCard();
    });
});
</script>