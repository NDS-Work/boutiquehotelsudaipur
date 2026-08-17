<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$currentPage = 'inquiries';
$pageTitle = 'Inquiries';

$db = new SQLite3(__DIR__ . '/../data/new.sqlite.db');

function deleteInquiry($db, $id) {
    $id = (int)$id;
    $db->exec("DELETE FROM link_inqueries WHERE id = $id");
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    deleteInquiry($db, $_GET['delete']);
    header('Location: inquiries.php');
    exit;
}

$today = date('Y-m-d');
$countToday = $db->querySingle("SELECT COUNT(*) FROM link_inqueries WHERE DATE(created_at) = '$today'");

$results = $db->query("SELECT * FROM link_inqueries ORDER BY created_at DESC");

// Fetch venue inquiry counts for bar chart (most first, exclude 0)
$venueRows = $db->query("
    SELECT venue_name, COUNT(*) as cnt
    FROM link_inqueries
    WHERE venue_name IS NOT NULL AND TRIM(venue_name) != ''
    GROUP BY venue_name
    ORDER BY cnt DESC
");
$venueLabels = [];
$venueCounts = [];
while ($row = $venueRows->fetchArray(SQLITE3_ASSOC)) {
    if ($row['cnt'] > 0) {
        $venueLabels[] = $row['venue_name'];
        $venueCounts[] = (int)$row['cnt'];
    }
}
$venueLabelsJson = json_encode($venueLabels);
$venueCountsJson = json_encode($venueCounts);
$chartHeight = max(300, count($venueLabels) * 52 + 60);

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Inquiries</h1>
</div>

<div class="content">
    <div class="card">
        <div class="card-title">Contact Inquiries</div>
        <div class="stats" style="margin-bottom: 0;">
            <div class="stat">
                <div class="stat-value"><?php echo $countToday; ?></div>
                <div class="stat-label">Inquiries Today</div>
            </div>
        </div>
    </div>

    <!-- Venue Bar Chart -->
    <div class="card">
        <div class="card-title">Inquiries by Venue</div>
        <?php if (count($venueLabels) === 0): ?>
            <p style="color: var(--text-secondary); font-size: 14px;">No venue inquiry data yet.</p>
        <?php else: ?>
        <div style="position: relative; width: 100%; height: <?php echo $chartHeight; ?>px;">
            <canvas id="venueChart"
                role="img"
                aria-label="Horizontal bar chart showing inquiry counts per venue.">
            </canvas>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
        <script>
        (function () {
            const labels = <?php echo $venueLabelsJson; ?>;
            const counts = <?php echo $venueCountsJson; ?>;
            const isDark = matchMedia('(prefers-color-scheme: dark)').matches;
            const gridColor  = isDark ? '#2c2c2a' : '#e1e0d9';
            const labelColor = '#898781';
            const textColor  = isDark ? '#ffffff' : '#0b0b0b';

            new Chart(document.getElementById('venueChart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Inquiries',
                        data: counts,
                        backgroundColor: '#2a78d6',
                        borderRadius: { topLeft: 4, topRight: 4 },
                        borderSkipped: 'bottom',
                        maxBarThickness: 40
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.x} ${ctx.parsed.x === 1 ? 'inquiry' : 'inquiries'}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                color: labelColor,
                                font: { size: 12 }
                            },
                            grid: { color: gridColor },
                            border: { color: isDark ? '#383835' : '#c3c2b7' },
                            title: {
                                display: true,
                                text: 'Number of inquiries',
                                color: labelColor,
                                font: { size: 12 }
                            }
                        },
                        y: {
                            ticks: {
                                color: textColor,
                                font: { size: 13 },
                                crossAlign: 'far'
                            },
                            grid: { display: false },
                            border: { display: false }
                        }
                    },
                    layout: { padding: { right: 32 } }
                },
                plugins: [{
                    afterDatasetsDraw(chart) {
                        const { ctx, scales: { x, y } } = chart;
                        counts.forEach((val, i) => {
                            const xPos = x.getPixelForValue(val);
                            const yPos = y.getPixelForValue(i);
                            ctx.fillStyle = textColor;
                            ctx.font = '500 13px sans-serif';
                            ctx.textAlign = 'left';
                            ctx.fillText(val, xPos + 6, yPos + 4);
                        });
                    }
                }]
            });
        })();
        </script>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title">Latest Inquiries</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Venue</th>
                        <th>Message</th>
                        <th>Date/Time</th>
                        <th>Actions</th>
                        <th>Info</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['venue_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this inquiry?');">Delete</a>
                        </td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm info-modal-btn"
                                style="background-color: transparent; color: green; border: 1px solid green"
                                data-booking='<?php echo json_encode([
                                    "utm_source"   => $row["utm_source"]   ?? "",
                                    "utm_campaign" => $row["utm_campaign"] ?? "",
                                    "utm_medium"   => $row["utm_medium"]   ?? "",
                                    "utm_term"     => $row["utm_term"]     ?? "",
                                    "utm_content"  => $row["utm_content"]  ?? "",
                                    "ip_address"   => $row["ip_address"]   ?? "",
                                    "ip_city"      => $row["ip_city"]      ?? ""
                                ]); ?>'>
                                info
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Info Modal Overlay -->
<div id="additionalInfoOverlay" style="display:none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.35); z-index: 9999; align-items: center; justify-content: center;">
    <div id="additionalInfoCard" class="card" style="max-width: 480px; width: 95vw; border: 2px solid #9ebffe; box-shadow: 0 8px 32px rgba(0,0,0,0.18); position: relative; margin: 0 auto;">
        <div class="card-title" style="color: #fff; font-weight: 700; font-size: 1.2rem; border-radius: 8px 8px 0 0; padding: 16px 20px;">Additional Information</div>
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
document.addEventListener('DOMContentLoaded', function () {
    var lastBtn = null;
    var infoOverlay = document.getElementById('additionalInfoOverlay');
    var infoCard    = document.getElementById('additionalInfoCard');
    var closeBtn    = document.getElementById('additionalInfoCloseBtn');

    function closeInfoCard() {
        infoOverlay.style.display = 'none';
        lastBtn = null;
    }

    document.querySelectorAll('.info-modal-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (lastBtn === btn && infoCard.style.display === 'block') {
                closeInfoCard();
                return;
            }
            lastBtn = btn;
            var info = {};
            try { info = JSON.parse(btn.getAttribute('data-booking')); } catch (e) {}
            document.getElementById('utm_source').textContent   = info.utm_source   || '-';
            document.getElementById('utm_medium').textContent   = info.utm_medium   || '-';
            document.getElementById('utm_campaign').textContent = info.utm_campaign || '-';
            document.getElementById('utm_term').textContent     = info.utm_term     || '-';
            document.getElementById('utm_content').textContent  = info.utm_content  || '-';
            document.getElementById('ip_address').textContent   = info.ip_address   || '-';
            document.getElementById('ip_city').textContent      = info.ip_city      || '-';
            infoOverlay.style.display = 'flex';
            closeBtn.focus();
        });
    });

    closeBtn.addEventListener('click', closeInfoCard);
    infoOverlay.addEventListener('click', function (e) {
        if (e.target === infoOverlay) closeInfoCard();
    });
});
</script>