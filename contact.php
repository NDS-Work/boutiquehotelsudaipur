<?php
$pageTitle = "Contact";
$messageSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would process the form and send an email
    $messageSent = true;
}

require_once 'includes/header.php';
?>

<div style="background-color: var(--bg-page); min-height: 100vh; padding-top: 100px; padding-bottom: 60px;">
    <div class="container">
        <div class="mx-auto" style="max-width: 1000px;">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="heading-2 mb-4" style="font-size: 35px; line-height: normal;">Have questions about boutique hotels or need help finding the perfect stay? We’re here to help.</h1>
                <p class="lead" style="color: var(--text-secondary);">
                    Have questions about venues or need help planning? We're here to assist you.
                </p>
            </div>



            <?php
            // Show a small card if message sent
            if (isset($_GET['success']) && $_GET['success'] == '1') {
                echo '<div class="alert alert-success mb-5" role="alert" style="max-width:600px;margin:20px auto 0;"><strong>Message sent!</strong> We\'ll get back to you soon.</div>';
            }
            ?>
            <script>
            // Show alert only for error
            window.addEventListener('DOMContentLoaded', function() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('error')) {
                    alert('Unable to send message. Please try again.');
                }
            });
            </script>

            <div class="row g-5">
                <!-- Contact Form -->
                <div class="col-lg-7">
                    <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                        <h2 class="heading-5 mb-4" style="color: var(--brand-primary);">Send Us a Message</h2>
                        
                        <form method="POST" action="process_contact.php">
                                                        <!-- UTM Parameters as hidden fields -->
                                                        <input type="hidden" name="utm_source" id="utm_source">
                                                        <input type="hidden" name="utm_medium" id="utm_medium">
                                                        <input type="hidden" name="utm_campaign" id="utm_campaign">
                                                        <input type="hidden" name="utm_term" id="utm_term">
                                                        <input type="hidden" name="utm_content" id="utm_content">
                            <div class="mb-4">
                                <label class="form-label small text-uppercase fw-semibold" style="color: var(--text-secondary); letter-spacing: 1px;">Your Name *</label>
                                <input type="text" name="name" required class=" info form-control" placeholder="John Doe" style="background-color: var(--bg-page); border-color: var(--border-medium); color: #9c9c9c;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small text-uppercase fw-semibold" style="color: var(--text-secondary); letter-spacing: 1px;">Email Address *</label>
                                <input type="email" name="email" required class=" info form-control" placeholder="john@example.com" style="background-color: var(--bg-page); border-color: var(--border-medium); color: #9c9c9c;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small text-uppercase fw-semibold" style="color: var(--text-secondary); letter-spacing: 1px;">Phone Number</label>
                                <input type="tel" name="phone" class=" info form-control" placeholder="+91 1234567890" style="background-color: var(--bg-page); border-color: var(--border-medium); color: #9c9c9c;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small text-uppercase fw-semibold" style="color: var(--text-secondary); letter-spacing: 1px;">Interested Venue</label>
                                <?php
                                // Fetch all hotels from link_hotels
                                $pdo = null;
                                try {
                                    $pdo = new PDO('sqlite:' . __DIR__ . '/data/new.sqlite.db');
                                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                } catch (Throwable $e) {}
                                $hotelOptions = [];
                                if ($pdo) {
                                    $stmt = $pdo->query('SELECT name FROM link_hotels WHERE name IS NOT NULL AND TRIM(name) != "" ORDER BY name ASC');
                                    $hotelOptions = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
                                }
                                $preselectedVenue = isset($_GET['venue']) ? trim($_GET['venue']) : '';
                                $defaultHotel = ($preselectedVenue && in_array($preselectedVenue, $hotelOptions)) ? $preselectedVenue : (count($hotelOptions) ? $hotelOptions[0] : '');
                                ?>
                                <div style="position: relative;">
                                    <input type="text" id="hotelSearchBoxContact" class="form-control mb-2" placeholder="Search hotel..." style="background: var(--bg-page); color: var(--text); border: 2px solid var(--accent); border-radius: 4px; font-size: 15px;" autocomplete="off" readonly onclick="showHotelDropdownContact()" onfocus="showHotelDropdownContact()" onkeyup="filterHotelOptionsContact()">
                                    <div id="hotelDropdownWrapContact" style="display: none; position: absolute; left: 0; right: 0; z-index: 1000; background: #f8f5f0; border: 2px solid var(--accent); border-radius: 0 0 4px 4px; box-shadow: 0 4px 16px rgba(0,0,0,0.18); max-height: 180px; overflow-y: auto;">
                                        <select class="form-select" id="hotelNameSelectContact" name="venue_name" size="5" style="background: #f8f5f0; color: #a67c52; border: none; border-radius: 0 0 4px 4px; font-size: 15px; min-height: 120px; max-height: 180px; outline: none;">
                                            <?php foreach ($hotelOptions as $hotel): ?>
                                                <option class="opt" value="<?php echo htmlspecialchars($hotel); ?>" <?php if ($defaultHotel === $hotel) echo 'selected'; ?>><?php echo htmlspecialchars($hotel); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Please select a hotel.</div>
                                <script>
                                var hotelDropdownVisibleContact = false;
                                function showHotelDropdownContact() {
                                    var wrap = document.getElementById('hotelDropdownWrapContact');
                                    var select = document.getElementById('hotelNameSelectContact');
                                    var input = document.getElementById('hotelSearchBoxContact');
                                    if (!hotelDropdownVisibleContact) {
                                        wrap.style.display = 'block';
                                        hotelDropdownVisibleContact = true;
                                        input.removeAttribute('readonly');
                                        input.focus();
                                        if (input.value === '' && select.selectedIndex >= 0) {
                                            input.value = select.options[select.selectedIndex].value;
                                        }
                                    }
                                }
                                document.addEventListener('click', function(e) {
                                    var wrap = document.getElementById('hotelDropdownWrapContact');
                                    var input = document.getElementById('hotelSearchBoxContact');
                                    if (!wrap.contains(e.target) && e.target !== input) {
                                        wrap.style.display = 'none';
                                        hotelDropdownVisibleContact = false;
                                        input.setAttribute('readonly', true);
                                    }
                                });
                                function hideHotelDropdownContact(e) {
                                    var wrap = document.getElementById('hotelDropdownWrapContact');
                                    var input = document.getElementById('hotelSearchBoxContact');
                                    if (!wrap.contains(e.relatedTarget) && e.target !== input) {
                                        wrap.style.display = 'none';
                                    }
                                }
                                function filterHotelOptionsContact() {
                                    var input = document.getElementById('hotelSearchBoxContact');
                                    var filter = input.value.toLowerCase();
                                    var select = document.getElementById('hotelNameSelectContact');
                                    var options = select.options;
                                    for (var i = 0; i < options.length; i++) {
                                        var txt = options[i].text.toLowerCase();
                                        options[i].style.display = txt.indexOf(filter) > -1 ? '' : 'none';
                                    }
                                    document.getElementById('hotelDropdownWrapContact').style.display = 'block';
                                }
                                document.getElementById('hotelSearchBoxContact').addEventListener('blur', hideHotelDropdownContact);
                                document.getElementById('hotelNameSelectContact').addEventListener('blur', hideHotelDropdownContact);
                                document.getElementById('hotelNameSelectContact').addEventListener('change', function() {
                                    document.getElementById('hotelSearchBoxContact').value = this.value;
                                    document.getElementById('hotelDropdownWrapContact').style.display = 'none';
                                });
                                // On form submit, set the selected hotel value
                                document.querySelector('form[action="process_contact.php"]').addEventListener('submit', function(e) {
                                    var select = document.getElementById('hotelNameSelectContact');
                                    if (select) {
                                        var selected = select.options[select.selectedIndex];
                                        if (selected) {
                                            select.value = selected.value;
                                        }
                                    }
                                });
                                // Set hotel name in input on page load
                                document.addEventListener('DOMContentLoaded', function() {
                                    var select = document.getElementById('hotelNameSelectContact');
                                    var input = document.getElementById('hotelSearchBoxContact');
                                    if (select && input && select.selectedIndex >= 0) {
                                        input.value = select.options[select.selectedIndex].value;
                                    }
                                });
                                </script>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small text-uppercase fw-semibold" style="color: var(--text-secondary); letter-spacing: 1px;">Message *</label>
                                <textarea name="message" required rows="5" class="info form-control" placeholder="Tell us about your wedding plans..." style="background-color: var(--bg-page); border-color: var(--border-medium); color: var(--bg-light); resize: none;"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center">
                                <span>Send Message</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="ms-2" viewBox="0 0 16 16">
                                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-15Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.47 1.178Z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-5">
                    <div class="mb-4">
                        <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                            <h2 class="heading-5 mb-4" style="color: var(--brand-primary);">Contact Information</h2>
                            
                            <div class="mb-4">
                                <div class="d-flex align-items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-3 mt-1 flex-shrink-0" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/>
                                    </svg>
                                    <div>
                                        <h3 class="fw-mb-1" style="color: var(--bg-light); font-size: 20px;">Email</h3>
                                        <a href="mailto:info@boutiquehotelsinudaipur.com" class="text-decoration-none small" style="color: var(--text-secondary);">
                                            info@boutiquehotelsinudaipur.com
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex align-items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-3 mt-1 flex-shrink-0" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                        <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                    </svg>
                                    <div>
                                        <h3 class="fw-mb-1" style="color: var(--bg-light); font-size: 20px;">Phone</h3>
                                        <a href="tel:+911234567890" class="text-decoration-none small" style="color: var(--text-secondary);">
                                            +91 1234567890
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex align-items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-3 mt-1 flex-shrink-0" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                    </svg>
                                    <div>
                                        <h3 class="fw-mb-1" style="color: var(--bg-light); font-size: 20px;">Location</h3>
                                        <p class="small mb-0" style="color: var(--text-secondary);">
                                            Udaipur, Rajasthan<br>
                                            India
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 mb-4" style="background-color: var(--bg-card);">
                        <h3 class="heading-6 mb-3" style="color: var(--brand-primary);">Office Hours</h3>
                        <div style="color: var(--bg-light);">
                            <p class="small mb-2">Monday - Friday: 9:00 AM - 6:00 PM</p>
                            <p class="small mb-2">Saturday: 10:00 AM - 4:00 PM</p>
                            <p class="small mb-0">Sunday: Closed</p>
                        </div>
                    </div>

                    <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                        <h3 class="heading-6 mb-3" style="color: var(--brand-primary);">Quick Response Time</h3>
                        <p class="small mb-0" style="color: var(--text-secondary);">
                            We typically respond to all inquiries within 24 hours during business days.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
