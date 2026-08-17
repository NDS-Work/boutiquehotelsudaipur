<?php

// Front-controller fallback: when PHP built-in server runs without router.php,
// all unknown paths land here. Route /hotels/{slug} to venue-detail.php.
$_uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
if (preg_match('#^/hotels/([^/]+)/?$#', $_uri, $_m)) {
    $_GET['slug'] = $_m[1];
    require __DIR__ . '/venue-detail.php';
    exit;
}

$pageTitle = "Home";
$canonicalUrl = "https://boutiquehotelsudaipur.com/";
require_once 'data/venues.php';

$schemaJson = json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'       => 'WebSite',
            'name'        => 'Boutique Hotels In Udaipur',
            'url'         => 'https://boutiquehotelsudaipur.com',
            'description' => 'A complete directory and comparison guide for 500+ boutique hotels in Udaipur, Rajasthan — including heritage havelis, luxury palaces, lake-view stays, and honeymoon properties.',
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => 'https://boutiquehotelsudaipur.com/hotels?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            '@type'       => 'Organization',
            'name'        => 'Boutique Hotels In Udaipur',
            'url'         => 'https://boutiquehotelsudaipur.com',
            'logo'        => 'https://boutiquehotelsudaipur.com/assets/favicon/boutique-favicon.jpeg',
            'description' => 'Udaipur\'s most comprehensive boutique hotel directory, helping travelers compare 500+ stays across heritage havelis, lake-view properties, honeymoon stays, and luxury palaces.',
            'areaServed'  => [
                '@type'          => 'City',
                'name'           => 'Udaipur',
                'addressRegion'  => 'Rajasthan',
                'addressCountry' => 'IN',
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

require_once 'includes/header.php';
// Get featured venues (highlighted by admin, fallback to top-rated)
$featuredVenues = getFeaturedVenues(4);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-background">
        <img src="./assets/images/udaipur.jpg" alt="Udaipur Palace Wedding" class="hero-image">
        <div class="hero-overlay"></div>
    </div>
    
    <div class="container position-relative" style="z-index: 1;">
        <div class="row">
            <div class="col-lg-7">
                <div class="d-flex align-items-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-warning me-2" viewBox="0 0 16 16">
                        <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                        <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                    </svg>
                    <span class="text-uppercase fw-semibold small" style="color: var(--brand-primary); letter-spacing: 2px;">Your Complete Guide</span>
                </div>
                <h1 class="heading-1 mb-4">Udaipur's Most Stunning Boutique Hotels</h1>
                <p class="lead mb-4">
                    Discover and compare 500+ boutique hotels in Udaipur, including luxury palaces, heritage havelis, and unique stays.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="/hotels" class="btn btn-primary-custom">Explore All Hotels</a>
                    <a href="/about" class="hero btn btn-secondary-custom">Know More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Browse Categories -->
<!-- <section class="py-5" style="background-color: var(--bg-page);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="heading-4 mb-3">Find Your Perfect Stay</h2>
            <p class="lead" style="color: var(--text-secondary);">Browse boutique hotels by location, amenities, or style</p>
        </div>
        
        <div class="browse row g-4">
            <div class="col-lg-3 col-md-6">
                <a href="/venues?category=location" class="text-decoration-none">
                    <div class="p-4 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium); transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--brand-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--border-medium)'; this.style.transform='translateY(0)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="mb-3" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                        </svg>
                        <h5 class="heading-5 mb-2" style="color: var(--bg-light);">By Location</h5>
                        <p class="small mb-3" style="color: var(--text-secondary);">Lake Pichola, Fateh Sagar, Aravalli Hills & More</p>
                        <span class="small fw-semibold" style="color: var(--brand-primary);">Browse →</span>
                    </div>
                </a>
            </div>
            
             <div class="col-lg-3 col-md-6">
                <a href="/venues?category=budget" class="text-decoration-none">
                    <div class="p-4 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium); transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--brand-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--border-medium)'; this.style.transform='translateY(0)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="mb-3" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                        </svg>
                        <h5 class="heading-5 mb-2" style="color: var(--bg-light);">By Budget</h5>
                        <p class="small mb-3" style="color: var(--text-secondary);">From ₹950 to ₹12,500 Per Plate</p>
                        <span class="small fw-semibold" style="color: var(--brand-primary);">Browse →</span>
                    </div>
                </a>
            </div> 
            
            <div class="col-lg-3 col-md-6">
                <a href="/venues?category=capacity" class="text-decoration-none">
                    <div class="p-4 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium); transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--brand-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--border-medium)'; this.style.transform='translateY(0)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="mb-3" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                        </svg>
                        <h5 class="heading-5 mb-2" style="color: var(--bg-light);">By Capacity</h5>
                        <p class="small mb-3" style="color: var(--text-secondary);">Intimate to Grand Celebrations</p>
                        <span class="small fw-semibold" style="color: var(--brand-primary);">Browse →</span>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <a href="/venues?category=venue-type" class="text-decoration-none">
                    <div class="p-4 h-100" style="background-color: var(--bg-card); border: 1px solid var(--border-medium); transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--brand-primary)'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='var(--border-medium)'; this.style.transform='translateY(0)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="mb-3" style="color: var(--brand-primary);" viewBox="0 0 16 16">
                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                        </svg>
                        <h5 class="heading-5 mb-2" style="color: var(--bg-light);">By Hotel Type</h5>
                        <p class="small mb-3" style="color: var(--text-secondary);">Palaces, Resorts, Lakeside & More</p>
                        <span class="small fw-semibold" style="color: var(--brand-primary);">Browse →</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section> -->

<!-- Featured Venues -->
<section class="py-5 mt-5" style="background-color: #f8e7ca8a;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="heading-4 mb-3">Featured Boutique Hotels</h2>
            <p class="lead" style="color: var(--text-secondary);">Handpicked stays offering exceptional comfort, design, and experience</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featuredVenues as $venue): ?>
            <?php
            $gallery = $venue['imageGallery'] ?? [];
            $primaryImage = $gallery[0]['url'] ?? ($venue['images'][0] ?? '');
            $thumbImages = array_slice($gallery, 1, 2);
            $galleryCount = count($gallery);
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="venue-card overflow-hidden h-100">
                    <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>" class="text-decoration-none">
                        <div class="position-relative" style="height: 250px; overflow: hidden;">
                            <?php if ($primaryImage !== ''): ?>
                            <img src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?>" class="w-100 h-100" style="object-fit: cover; transition: transform 0.5s ease;">
                            <?php else: ?>
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: var(--bg-card); color: var(--text-secondary);">No image</div>
                            <?php endif; ?>
                            <?php if ($galleryCount > 1): ?>
                            <!-- <div class="position-absolute bottom-0 end-0 m-3 px-3 py-1 small fw-semibold" style="background-color: rgba(15,15,16,0.78); color: #fff; backdrop-filter: blur(4px);">
                                <?php echo $galleryCount; ?> photos
                            </div> -->
                            <?php endif; ?>
                        </div>
                    </a>
                    <!-- <?php if ($thumbImages): ?>
                    <div class="d-flex gap-1 p-2" style="background-color: rgba(15,15,16,0.35);">
                        <?php foreach ($thumbImages as $image): ?>
                        <div style="width: calc(50% - 2px); height: 64px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars((string) $image['url']); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?> thumbnail" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?> -->
                    <div class="p-3">
                        <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>" class="text-decoration-none">
                            <h5 class="heading-6 mb-2" style="color: var(--brand-primary);"><?php echo htmlspecialchars($venue['name']); ?></h5>
                        </a>
                        <div class="d-flex align-items-center" style="color: var(--text-secondary);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                            </svg>
                            <?php echo htmlspecialchars($venue['location']); ?>
                        </div>
                        <!-- <div class="d-flex justify-content-between align-items-center small">
                            <span class="fw-semibold" style="color: var(--bg-light);">₹<?php echo number_format($venue['pricePerPlate']); ?>/plate</span>
                            <span style="color: var(--text-secondary);"><?php echo $venue['capacity']['max']; ?> guests</span>
                        </div> -->
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="/hotels" class="btn btn-primary-custom">View All Boutique Hotels</a>
        </div>
    </div>
</section>

<!-- Why Udaipur Section -->
<section class="py-5" style="background-color: var(--bg-page);">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="heading-4 mb-4">Why Stay in Boutique Hotels in Udaipur?</h2>
                <div class="mb-4">
                    <div class="d-flex align-items-start mb-4">
                        <div class="rounded-circle me-3" style="width: 8px; height: 8px; background-color: var(--brand-primary); margin-top: 8px; flex-shrink: 0;"></div>
                        <div>
                            <h5 class="fw-semibold mb-2" style="color: var(--bg-light);">Authentic Heritage Experience</h5>
                            <p class="small mb-0" style="color: var(--text-secondary);">Stay in beautifully restored palaces and havelis that reflect true Rajasthani culture</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <div class="rounded-circle me-3" style="width: 8px; height: 8px; background-color: var(--brand-primary); margin-top: 8px; flex-shrink: 0;"></div>
                        <div>
                            <h5 class="fw-semibold mb-2" style="color: var(--bg-light);">Stunning Lake & Scenic Views</h5>
                            <p class="small mb-0" style="color: var(--text-secondary);">Enjoy breathtaking views of Lake Pichola, Fateh Sagar, and the Aravalli hills</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <div class="rounded-circle me-3" style="width: 8px; height: 8px; background-color: var(--brand-primary); margin-top: 8px; flex-shrink: 0;"></div>
                        <div>
                            <h5 class="fw-semibold mb-2" style="color: var(--bg-light);">Unique & Personalized Stays</h5>
                            <p class="small mb-0" style="color: var(--text-secondary);">Boutique hotels offer curated experiences, unlike generic chain hotels</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle me-3" style="width: 8px; height: 8px; background-color: var(--brand-primary); margin-top: 8px; flex-shrink: 0;"></div>
                        <div>
                            <h5 class="fw-semibold mb-2" style="color: var(--bg-light);">Options for Every Budget</h5>
                            <p class="small mb-0" style="color: var(--text-secondary);">From luxury palace stays to affordable boutique hotels</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1695956353120-54ce5e91632b?w=800&h=1000&fit=crop" alt="City Palace Udaipur" class="w-100" style="height: 600px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background-color: #f8e7ca8a;">
    <div class="container text-center">
        <h2 class="heading-4 mb-4">Ready to Find Your Perfect Boutique Stay in Udaipur?</h2>
        <p class="lead mb-4 mx-auto" style="max-width: 700px; color: var(--bg-light);">
            Explore our complete collection of 500+ boutique hotels and discover the ideal stay for your next trip.
        </p>
        <a href="/hotels" class="btn btn-primary-custom d-inline-flex align-items-center">
            <span>Start Exploring</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="ms-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
            </svg>
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
