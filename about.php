<?php
$pageTitle = "About";
$metaTitle = "About Us | Boutique Hotels In Udaipur";
$metaDescription = "Boutique Hotels In Udaipur is an independent directory maintained by Udaipur-based hospitality researchers helping travelers find the best boutique heritage stays in Rajasthan.";
require_once 'includes/header.php';
?>

<div style="background-color: var(--bg-page); min-height: 100vh; padding-top: 100px; padding-bottom: 60px;">
    <div class="container">
        <!-- Header -->
        <div class="text-center mb-5">
            <div class="d-flex align-items-center justify-content-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-warning me-2" viewBox="0 0 16 16">
                    <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                    <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                </svg>
                <span class="text-uppercase fw-semibold small" style="color: var(--brand-primary); letter-spacing: 2px;">About Us</span>
            </div>
            <h1 class="heading-2 mb-4">YOUR TRUSTED GUIDE TO BOUTIQUE HOTELS IN UDAIPUR</h1>
            <p class="lead mx-auto" style="max-width: 800px; color: var(--bg-light);">
                Boutique Hotels In Udaipur is an independent directory maintained by a team of Udaipur-based hospitality researchers and travel writers who have personally assessed hotels across the city since 2023. We are not affiliated with any hotel chain and do not accept payment for rankings or featured placements.
            </p>
        </div>

        <!-- Mission Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="p-4 text-center h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="mb-3" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg>
                    <h3 class="heading-6 mb-3" style="color: var(--bg-light);">Comprehensive Hotel Listings</h3>
                    <p class="small mb-0" style="color: var(--text-secondary);">
                        500+ boutique hotels across prime locations, budgets, and stay styles in Udaipur
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4 text-center h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="mb-3" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                        <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                        <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                    </svg>
                    <h3 class="heading-6 mb-3" style="color: var(--bg-light);">Verified & Detailed Information</h3>
                    <p class="small mb-0" style="color: var(--text-secondary);">
                        Accurate pricing, amenities, location insights, and real hotel details
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4 text-center h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="mb-3" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                        <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                    </svg>
                    <h3 class="heading-6 mb-3" style="color: var(--bg-light);">Curated With Care</h3>
                    <p class="small mb-0" style="color: var(--text-secondary);">
                        Every Boutique hotel is handpicked for its exceptional quality, unique character, and memorable guest experience
                    </p>
                </div>
            </div>
        </div>

        <!-- Why Udaipur -->
        <div class="mb-5">
            <h2 class="heading-4 text-center mb-5" style="color: var(--brand-primary);">
                Why Choose Boutique Hotels in Udaipur for Your Stay?
            </h2>
            
            <div class="row g-4">
                <div class="col-12">
                    <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                        <h3 class="heading-6 mb-3" style="color: var(--brand-primary);">The City of Lakes</h3>
                        <p class="mb-0" style="color: var(--bg-light); line-height: 1.7;">
                            Udaipur, known as the “City of Lakes,” offers breathtaking views, serene surroundings, and stunning Aravalli Hills—making it one of India’s most beautiful travel destinations.
                        </p>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                        <h3 class="heading-6 mb-3" style="color: var(--brand-primary);">Royal Heritage</h3>
                        <p class="mb-0" style="color: var(--bg-light); line-height: 1.7;">
                            From lakefront palaces to centuries-old havelis, Udaipur’s boutique hotels reflect rich Rajasthani culture, architecture, and timeless elegance.
                        </p>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                        <h3 class="heading-6 mb-3" style="color: var(--brand-primary);">Stays for Every Budget</h3>
                        <p class="mb-0" style="color: var(--bg-light); line-height: 1.7;">
                            Whether you prefer luxury palace hotels or budget-friendly boutique stays, Udaipur offers a wide range of options for every traveler.
                        </p>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-4" style="background-color: var(--bg-card); border: 1px solid var(--border-medium);">
                        <h3 class="heading-6 mb-3" style="color: var(--brand-primary);">Perfect Travel Season</h3>
                        <p class="mb-0" style="color: var(--bg-light); line-height: 1.7;">
                            October to March is ideal for visiting Udaipur, with pleasant weather, clear skies, and the perfect setting to explore the city comfortably
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How to Use -->
        <div class="p-5 text-center" style="background-color: #f8e7ca3b;">
            <h2 class="heading-4 mb-4" style="color: var(--brand-primary);">How to Use This Directory</h2>
            <div class="row g-4 text-start mx-auto" style="max-width: 800px;">
                <div class="col-12">
                    <p class="mb-0" style="color: var(--bg-light); font-size: 1.125rem;">
                        <strong style="color: var(--brand-primary);">1. Browse & Filter:</strong> Use our advanced filters to narrow down venues by location, budget, capacity, or venue type.
                    </p>
                </div>
                <div class="col-12">
                    <p class="mb-0" style="color: var(--bg-light); font-size: 1.125rem;">
                        <strong style="color: var(--brand-primary);">2. Compare Options:</strong> View detailed information including pricing, amenities, and real photos for each venue.
                    </p>
                </div>
                <div class="col-12">
                    <p class="mb-0" style="color: var(--bg-light); font-size: 1.125rem;">
                        <strong style="color: var(--brand-primary);">3. Book Direct:</strong> Click through to venue websites or contact them directly for quotes and availability.
                    </p>
                </div>
                <div class="col-12">
                    <p class="mb-0" style="color: var(--bg-light); font-size: 1.125rem;">
                        <strong style="color: var(--brand-primary);">4. Plan With Confidence:</strong> Use our verified information to make informed decisions about your dream wedding venue.
                    </p>
                </div>
                <div class="col-12" style="border-top: 1px solid var(--border-medium); padding-top: 24px; margin-top: 8px;">
    <p class="mb-1" style="color: var(--bg-light); font-size: 1.125rem;">
        <strong style="color: var(--brand-primary);">Our Editorial Process:</strong> Our team analyses guest reviews, cross-references amenity data, and maintains direct communication with hotel management to keep listings accurate and up to date.
    </p>
    <p class="mb-1 mt-3" style="color: var(--text-secondary);">
        📍 Based in: Udaipur, Rajasthan, India
    </p>
    <p class="mb-0" style="color: var(--text-secondary);">
        ✉️ Contact us: <a href="mailto:hello@boutiquehotelsudaipur.com" style="color: var(--brand-primary);">hello@boutiquehotelsudaipur.com</a>
    </p>
    </div>
            </div>
        </div>
    </div>
</div>
<!-- Author Byline -->
<div class="container" style="max-width: 860px; margin: 0 auto; padding-bottom: 60px;">
    <div style="border-top: 1px solid #e0d5c5; margin-top: 40px; padding-top: 20px;">
        <p style="font-size: 14px; color: #666; margin: 0;">
            Written by the <strong>Boutique Hotels In Udaipur</strong> editorial team —
            an independent group of Udaipur-based hospitality researchers helping travelers
            find the best boutique heritage stays in Rajasthan.
            <a href="/about" style="color: #4b1111;">Learn more about us</a>.
        </p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
