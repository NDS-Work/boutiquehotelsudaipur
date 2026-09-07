<?php
http_response_code(404);
$pageTitle = 'Page Not Found';
$metaTitle = '404 - Page Not Found | Boutique Hotels Udaipur';
$metaDescription = 'The page you requested could not be found. Explore luxury boutique hotels, heritage havelis, and travel guides in Udaipur.';

require_once __DIR__ . '/includes/header.php';
?>

<style>
.error-page-wrapper {
    background-color: var(--bg-page, #f8f5f0);
    min-height: 75vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 140px 15px 70px;
}
.error-card {
    background: #ffffff;
    border: 1px solid rgba(166, 124, 82, 0.22);
    border-radius: 8px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.04);
    max-width: 680px;
    width: 100%;
    padding: 50px 40px;
    text-align: center;
}
.error-code {
    font-family: 'Cinzel', serif;
    font-size: 5.5rem;
    font-weight: 700;
    line-height: 1;
    color: #4b1111;
    letter-spacing: 4px;
    margin-bottom: 12px;
}
.error-subtitle {
    font-family: 'Cinzel', serif;
    font-size: 1.6rem;
    color: var(--brand-primary, #a67c52);
    margin-bottom: 16px;
    letter-spacing: 1px;
}
.error-text {
    font-size: 1.05rem;
    color: #63615b;
    line-height: 1.7;
    margin-bottom: 35px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}
.error-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    justify-content: center;
    margin-bottom: 35px;
}
.btn-gold-primary {
    background-color: #4b1111;
    color: #f7e6c4 !important;
    border: 1px solid #4b1111;
    border-radius: 4px;
    padding: 12px 28px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.25s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-gold-primary:hover {
    background-color: #380c0c;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(75, 17, 17, 0.2);
}
.btn-gold-outline {
    background: transparent;
    color: #4b1111 !important;
    border: 1.5px solid #4b1111;
    border-radius: 4px;
    padding: 12px 24px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.25s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-gold-outline:hover {
    background-color: #4b1111;
    color: #f7e6c4 !important;
    transform: translateY(-2px);
}
.error-quick-links {
    border-top: 1px solid rgba(166, 124, 82, 0.15);
    padding-top: 25px;
}
.error-quick-links-title {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #99958d;
    margin-bottom: 15px;
    font-weight: 600;
}
.error-quick-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
}
.error-tag {
    background: #fbf9f6;
    border: 1px solid #e7dfd5;
    color: #55524c !important;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.2s ease;
}
.error-tag:hover {
    background: #4b1111;
    color: #f7e6c4 !important;
    border-color: #4b1111;
}
@media (max-width: 576px) {
    .error-card {
        padding: 35px 20px;
    }
    .error-code {
        font-size: 4rem;
    }
    .error-actions {
        flex-direction: column;
    }
    .btn-gold-primary, .btn-gold-outline {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="error-page-wrapper">
    <div class="error-card">
        <div class="error-code">404</div>
        <h1 class="error-subtitle">Destination Not Found</h1>
        <p class="error-text">
            It looks like this path led to an unmapped corner. The boutique hotel, guide, or page you were seeking may have been relocated or no longer exists.
        </p>

        <div class="error-actions">
            <a href="/" class="btn-gold-primary">
                <i class="bi bi-house-door"></i> Return Home
            </a>
            <a href="/hotels" class="btn-gold-outline">
                <i class="bi bi-building"></i> Explore 500+ Hotels
            </a>
            <a href="/blogs" class="btn-gold-outline">
                <i class="bi bi-journal-richtext"></i> Read Blogs
            </a>
        </div>

        <div class="error-quick-links">
            <div class="error-quick-links-title">Popular Collections & Searches</div>
            <div class="error-quick-tags">
                <a href="/hotels/collection/lake-view" class="error-tag"><i class="bi bi-water me-1"></i>Lake View Havelis</a>
                <a href="/hotels/collection/luxury-palaces" class="error-tag"><i class="bi bi-gem me-1"></i>Luxury Palaces</a>
                <a href="/hotels/collection/honeymoon-special" class="error-tag"><i class="bi bi-heart me-1"></i>Honeymoon Suites</a>
                <a href="/hotels/collection/heritage-stays" class="error-tag"><i class="bi bi-bank me-1"></i>Heritage Havelis</a>
                <a href="/contact" class="error-tag"><i class="bi bi-envelope me-1"></i>Contact Concierge</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
