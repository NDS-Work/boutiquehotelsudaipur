<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NH2DT7Q7');</script>
<!-- End Google Tag Manager -->
	<meta name="google-site-verification" content="EbtFdPmBQrCIdFyGIR8SW7JUUroVhsOEolQfRyW-41I" />
    <title><?php echo isset($metaTitle) ? htmlspecialchars($metaTitle) : 'Explore Udaipur’s Best Boutique Hotels | Compare 500+ Stays, Prices & Amenities'; ?></title>
    <meta name="description" content="<?php echo isset($metaDescription) ? htmlspecialchars($metaDescription) : 'Explore 500+ boutique hotels in Udaipur including luxury palaces, heritage havelis, and budget stays. Compare prices, locations, and amenities to find your perfect stay in the City of Lakes.'; ?>">
    <?php if (!empty($schemaJson)): ?>
<script type="application/ld+json">
<?php echo $schemaJson; ?>
</script>
<?php endif; ?>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- favicon -->
     <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/boutique-favicon.jpeg">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="/assets/favicon/site.webmanifest">
    <!-- Visitor Tracker -->
<script>
(function () {
  if (window.location.pathname.indexOf('/admin') === 0) return;
  var payload = {
    page_url:   window.location.href,
    page_title: document.title,
    referrer:   document.referrer || ''
  };
  var body = JSON.stringify(payload);
  if (navigator.sendBeacon) {
    navigator.sendBeacon('/track.php', new Blob([body], { type: 'application/json' }));
  } else {
    fetch('/track.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: body, keepalive: true }).catch(function(){});
  }
})();
</script>
</head>
</head>

<body>
   <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NH2DT7Q7"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-E5W3QSZ0DZ');
</script>
<!-- End Google Tag Manager (noscript) -->
    <?php
    $currentRequestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $currentRequestPath = '/' . trim($currentRequestPath, '/');
    $isHotelsPage = strpos($currentRequestPath, '/hotels') === 0;

    $isHeritagePage = $isHotelsPage && (
        $currentRequestPath === '/hotels/collection/heritage-boutique-hotels' ||
        strpos($currentRequestPath, '/hotels/collection/heritage-boutique-hotels/') === 0 ||
        (basename($_SERVER['PHP_SELF']) === 'hotels.php' && isset($_GET['venueType']) && $_GET['venueType'] === 'heritage-palace')
    );

    $isAllHotelsPage = $currentRequestPath === '/hotels' || $currentRequestPath === '/hotels/';

    $isHoneymoonPage = $isHotelsPage && (
        $currentRequestPath === '/hotels/occasion/honeymoon-boutique-hotels' ||
        strpos($currentRequestPath, '/hotels/occasion/honeymoon-boutique-hotels/') === 0 ||
        (basename($_SERVER['PHP_SELF']) === 'hotels.php' && isset($_GET['occasion']) && $_GET['occasion'] === 'honeymoon-stay')
    );

    $isLakeViewPage = $isHotelsPage && (
        $currentRequestPath === '/hotels/collection/lakeview-boutique-hotels' ||
        strpos($currentRequestPath, '/hotels/collection/lakeview-boutique-hotels/') === 0 ||
        (basename($_SERVER['PHP_SELF']) === 'hotels.php' && isset($_GET['location']) && $_GET['location'] === 'lake-pichola')
    );

    $isLuxuryPage = $isHotelsPage && (
        $currentRequestPath === '/hotels/collection/luxury-boutique-hotels' ||
        strpos($currentRequestPath, '/hotels/collection/luxury-boutique-hotels/') === 0 ||
        (basename($_SERVER['PHP_SELF']) === 'hotels.php' && isset($_GET['collection']) && $_GET['collection'] === 'luxury-heritage-hotel')
    );
    ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #4b1111; border-bottom: 1px solid #3f4816;">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-black text-uppercase" href="/" style="color: #fff; font-size: 1.25rem; letter-spacing: -0.5px; font-family: 'Cinzel', serif">
                Boutique Hotels In Udaipur
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isHeritagePage ? 'active' : ''; ?>"
       href="/hotels/collection/heritage-boutique-hotels">Heritage Hotel in Udaipur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isAllHotelsPage ? 'active' : ''; ?>" href="/hotels">All Boutique Hotels</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isHoneymoonPage ? 'active' : ''; ?>"
                        href="/hotels/occasion/honeymoon-boutique-hotels">Honeymoon Stay in Udaipur</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link <?php echo $isLakeViewPage ? 'active' : ''; ?>"
                        href="/hotels/collection/lakeview-boutique-hotels">Lake View Hotel Udaipur</a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isLuxuryPage ? 'active' : ''; ?>"
       href="/hotels/collection/luxury-boutique-hotels">Luxury Heritage Hotel</a>
                    </li>
                </ul>
            </div>
        </div>
        </div>
    </nav>
