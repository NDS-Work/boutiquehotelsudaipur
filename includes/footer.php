<?php
                        if (!function_exists('getOccasions')) {
                            require_once __DIR__ . '/../data/venues.php';
                        }
                        $occasions = getOccasions();
                                                $topAttractions = getTopAttractions(10);
                        $collections = getCollections();
                        $topAmenities = getTopAmenities(10);

                        ?>
                        
                        <!-- Footer -->
    <footer style="background-color: #4b1111; border-top: 1px solid #3f4816; margin-top: 6rem;">
        <div class="container py-5">
            <div class="row g-4">
                <!-- Brand Section -->
                <div class="col-12 text-center">
                    <h5 class="text-uppercase fw-bold mb-3" style="color: #fff;font-size: 35px; text-decoration: underline;">Explore Boutique Hotels In Udaipur</h5>
                    <p class="small" style="color: #ccc; font-size: 18px;">Your complete directory to explore and compare boutique hotels in Udaipur’s most stunning locations.</p>
                    <!-- <div class="d-flex align-items-center" style="color: #ccc;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#fff" viewBox="0 0 16 16" class="me-2">
                            <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                        </svg>
                        <span class="small">Curated with care</span>
                    </div> -->
                </div>

                <!-- Attractions -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3" style="color: #dfddd6;"> <i class="bi bi-geo-alt"></i> By Attractions</h6>
                    <ul class="list-unstyled">
                        <?php
                       foreach ($topAttractions as $attraction):
    $slug = strtolower(str_replace(' ', '-', $attraction['attraction_name']));
?>
<li class="mb-2">
  <a href="/hotels/attraction/<?php echo urlencode($slug); ?>"
     class="text-decoration-none small" style="color:#ccc;">
    Boutique Hotels near <?php echo htmlspecialchars($attraction['attraction_name']); ?>
    <i class="bi bi-arrow-right"></i>
  </a>
</li>
<?php endforeach; ?>
                    </ul>
                </div>

                <!-- Collections -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3" style="color: #dfddd6;"> <i class="bi bi-list-stars"></i> By Collections</h6>
                    <ul class="list-unstyled">
                       <?php foreach ($collections as $collection):
    $slug = strtolower(str_replace(' ', '-', $collection['collection_name']));
?>
<li class="mb-2">
  <a href="/hotels/collection/<?php echo urlencode($slug); ?>"
     class="text-decoration-none small" style="color:#ccc;">
    <?php echo htmlspecialchars($collection['collection_name']); ?>
    <i class="bi bi-arrow-right"></i>
  </a>
</li>
<?php endforeach; ?>
                    </ul>
                </div>

                <!-- By Amenities -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3" style="color: #dfddd6;"> <i class="bi bi-wifi"></i> By Amenities</h6>
                    <ul class="list-unstyled">
<?php foreach ($topAmenities as $amenity):
    $slug = strtolower(str_replace(' ', '-', $amenity['name']));
?>
<li class="mb-2">
  <a href="/hotels/amenity/<?php echo urlencode($slug); ?>"
     class="text-decoration-none small" style="color:#ccc;">
    Boutique Hotels with <?php echo htmlspecialchars($amenity['name']); ?>
    <i class="bi bi-arrow-right"></i>
  </a>
</li>
<?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- By Occasions -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-3" style="color: #dfddd6;"> <i class="bi bi-rainbow"></i> By Occasions</h6>
                    <ul class="list-unstyled">
                        <?php foreach ($occasions as $occasion):
    $slug = strtolower(str_replace(' ', '-', $occasion['occasion_name']));
?>
<li class="mb-2">
  <a href="/hotels/occasion/<?php echo urlencode($slug); ?>"
     class="text-decoration-none small" style="color:#ccc;">
    <?php echo htmlspecialchars($occasion['occasion_name']); ?>
    <i class="bi bi-arrow-right"></i>
  </a>
</li>
<?php endforeach; ?>
                    </ul>
                </div>

                <!-- Featured Boutique Hotels -->
                <div class="col-12">
                    <h5 class="text-uppercase fw-bold mb-4 text-center" style="color: #dfddd6;"> <i class="bi bi-binoculars"></i> Featured Boutique Hotels</h5>
                    
                    <?php $featuredVenues = getFeaturedVenues(4); ?>
                    <div class="row g-4 justify-content-center">
            <?php foreach ($featuredVenues as $venue): ?>
            <?php
            $gallery = $venue['imageGallery'] ?? [];
            $primaryImage = $gallery[0]['url'] ?? ($venue['images'][0] ?? '');
            $thumbImages = array_slice($gallery, 1, 2);
            $galleryCount = count($gallery);
            ?>
            <div class="col-6 col-md-3 text-center">
                <div class="venue-card overflow-hidden h-100" style="background: none; border: none; gap: 10px;">
                    <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>" class="text-decoration-none">
                        <div class="position-relative">  
                            <?php if ($primaryImage !== ''): ?>
                            <img src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?>" class="w-200 h-150" style="object-fit: cover; transition: transform 0.5s ease;">
                            <?php else: ?>
                            <div class="w-200 h-100 d-flex align-items-center justify-content-center" style="background-color: var(--bg-card); color: var(--text-secondary);">No image</div>
                            <?php endif; ?>
                            <?php if ($galleryCount > 1): ?>
                            <!-- <div class="position-absolute bottom-0 end-0 m-3 px-3 py-1 small fw-semibold" style="background-color: rgba(15,15,16,0.78); color: #fff; backdrop-filter: blur(4px);">
                                <?php echo $galleryCount; ?> photos
                            </div> -->
                            <?php endif; ?>
                        </div>
                    </a>
                    
                    <div class="p-2">
                        <a href="/hotels/<?php echo htmlspecialchars($venue['slug']); ?>" class="text-decoration-none">
                            <h5 class="mb-2" style="color: #ffffff; font-size: 18px; text-decoration: underline;"><?php echo htmlspecialchars($venue['name']); ?> <i class="bi bi-arrow-right"></i></h5>
                        </a>
                        
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
                </div>
            </div> 
            <!-- Rohit bhaiya ne bola file ke naam ke liye same as title of the page earlier article-01...  -->
            <div class="travel-guides">
                <h5 class="text-uppercase fw-bold mb-3 text-center mt-4" style="color: #dfddd6;">Travel guides</h5>
                <ul style="list-style: none; padding-left: 0; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-top: 2rem;">
                     <li style="display: flex; flex-direction: column; align-items: center; gap: 10px;"> 
                        <a href="/10-Best-Boutique-Hotels-in-Udaipur-Near-Lake-Pichola"><img src="/assets/footer-image/lake-pichola.webp"  alt="10 Best Boutique Hotels in Udaipur Near Lake Pichola" style="width: 181px; height: 99px; object-fit: cover;"></a>
                    </li>
                    <li style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <a href="/Top-5-Boutique-Hotels-in-Udaipur-for-Couples-&-Luxury-Travelers"><img src="/assets/footer-image/couple-&-luxury.webp"  alt="Top 5 Boutique Hotels in Udaipur for Couples & Luxury Travelers" style="width: 180px; height: 99px; object-fit: cover;"></a>
                    </li>
                    <li style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <a href="/10-Famous-Heritage-Hotels-in-Udaipur-with-Traditional-Mewari-Charm"><img src="/assets/footer-image/Traditional-mewari-charm.webp"  alt="10 Famous Heritage Hotels in Udaipur with Traditional Mewari Charm" style="width: 180px; height: 99px; object-fit: cover;"></a>
                    </li>
                    <li style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <a href="/Top-10-Hidden-Boutique-Hotels-in-Rajasthan-with-Royal-Hospitality"><img src="/assets/footer-image/preview.webp"  alt="Top 10 Hidden Boutique Hotels in Rajasthan with Royal Hospitality" style="width: 180px; height: 99px; object-fit: cover;"></a>
                    </li>
                    <li style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <a href="/10-Famous-Heritage-Hotels-in-Rajasthan-You-Must-Experience"><img src="/assets/footer-image/heritage-hotel-rajasthan.webp"  alt="10 Famous Heritage Hotels in Rajasthan You Must Experience" style="width: 180px; height: 99px; object-fit: cover;"></a>
                    </li>
                    <li style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <a href="/why-udaipur-is-becoming-a-global-luxury-travel-destination"><img src="/assets/footer-image/Picture4.jpg"  alt="Why Udaipur Is Becoming a Global Luxury Travel Destination" style="width: 180px; height: 99px; object-fit: cover;"></a>
                    </li>
                </ul>
            </div>     
            <!-- Bottom Bar -->
            <div class="border-top mt-4 pt-4" style="border-color: #ccc !important;">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0 small" style="color: #ccc;">© <?php echo date('Y'); ?> Boutique Hotels In Udaipur. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <a href="#" class="text-decoration-none small me-3" style="color: #ccc;">Privacy Policy</a>
                        <a href="#" class="text-decoration-none small" style="color: #ccc;">Terms of Use</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
