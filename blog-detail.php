<?php

declare(strict_types=1);

require_once __DIR__ . '/data/blogs.php';
require_once __DIR__ . '/data/venues.php';

require_once __DIR__ . '/admin/auth.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$isAdmin = function_exists('isLoggedIn') && isLoggedIn();

// Only allow viewing drafts if an administrator is logged in
$post = getBlogBySlug($slug, !$isAdmin);

if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Increment post view counter
incrementBlogViews((int)$post['id']);

// Related posts
$relatedPosts = getRelatedBlogs((int)($post['category_id'] ?? 0), 5, (int)$post['id']);

// Featured hotels for sidebar
$sidebarHotels = function_exists('getFeaturedVenues') ? getFeaturedVenues(5) : [];

// SEO Meta Fields
$metaTitle = !empty($post['meta_title']) ? $post['meta_title'] : ($post['title'] . ' | Boutique Hotels in Udaipur');
$metaDescription = !empty($post['meta_description']) ? $post['meta_description'] : $post['excerpt'];
$canonicalUrl = !empty($post['canonical_url']) ? $post['canonical_url'] : ('https://boutiquehotelsudaipur.com/blog/' . urlencode($post['slug']));
$ogImage = !empty($post['og_image']) ? $post['og_image'] : ($post['featured_image'] ?: 'https://boutiquehotelsudaipur.com/assets/footer-image/lake-pichola.webp');

// Schema.org Graph
$schemaGraph = [
    [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => 'https://boutiquehotelsudaipur.com/'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Blogs',
                'item' => 'https://boutiquehotelsudaipur.com/blogs'
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $post['title'],
                'item' => $canonicalUrl
            ]
        ]
    ],
    [
        '@type' => 'Article',
        'headline' => $post['title'],
        'description' => $metaDescription,
        'image' => $ogImage,
        'datePublished' => date('Y-m-d', strtotime((string)$post['published_at'])),
        'dateModified' => date('Y-m-d', strtotime((string)$post['updated_at'])),
        'author' => [
            '@type' => 'Organization',
            'name' => $post['author'] ?: 'Boutique Hotels In Udaipur Editorial Team'
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Boutique Hotels In Udaipur',
            'url' => 'https://boutiquehotelsudaipur.com'
        ],
        'url' => $canonicalUrl
    ]
];

// Optional FAQ Schema if configured
$faqEntities = [];
if (!empty($post['schema_faq_json'])) {
    $faqData = json_decode($post['schema_faq_json'], true);
    if (is_array($faqData) && !empty($faqData)) {
        foreach ($faqData as $f) {
            if (!empty($f['q']) && !empty($f['a'])) {
                $faqEntities[] = [
                    '@type' => 'Question',
                    'name' => $f['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $f['a']
                    ]
                ];
            }
        }
        if (!empty($faqEntities)) {
            $schemaGraph[] = [
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities
            ];
        }
    }
}

$schemaJson = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => $schemaGraph
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

require_once __DIR__ . '/includes/header.php';
?>

<style>
  :root {
    --brand-primary: #c9913d;
  }

  .content {
    color: #454545;
  }

  /* Layout matching Top-5-Boutique-Hotels */
  .blog-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 48px;
    align-items: start;
    padding: 100px 0 80px;
  }

  @media (max-width: 991px) {
    .blog-layout { grid-template-columns: 1fr; padding-top: 80px; }
    .blog-sidebar { display: none; }
  }

  /* Meta bar */
  .blog-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
    padding: 10px 0 16px;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(201,145,61,0.2);
    font-size: 0.85rem;
    color: #7d6b56;
  }
  .blog-meta span { display: flex; align-items: center; gap: 6px; }

  /* Content body matching reference page */
  .blog-body {
    color: #38332d;
    font-size: 1.05rem;
    line-height: 1.85;
    font-family: 'Lato', sans-serif;
  }

  .blog-body h1 {
    font-family: 'Cinzel', serif;
    color: #a67c52;
    font-size: 2.3rem;
    line-height: 1.25;
    margin: 18px 0 12px 0;
    font-weight: 700;
  }

  .blog-body h2 {
    font-family: 'Cinzel', serif;
    font-size: 1.6rem;
    color: #a67c52;
    margin: 2.5rem 0 0.8rem;
    line-height: 1.3;
    font-weight: 600;
  }

  .blog-body h3 {
    font-family: 'Cinzel', serif;
    font-size: 1.2rem;
    color: #755535;
    margin: 1.8rem 0 0.8rem;
    font-weight: 500;
  }

  .blog-body p {
    margin-bottom: 1.3rem;
    color: #454545;
  }

  /* Automatically hide empty paragraphs, extra Quill spacing breaks, and blank p tags */
  .blog-body p:empty,
  .blog-body p:has(> br:only-child),
  .blog-body p:has(> br:first-child:last-child) {
    display: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .blog-body ul, .blog-body ol {
    margin-bottom: 1.5rem;
    padding-left: 28px;
    color: #454545;
  }

  .blog-body ul {
    list-style-type: disc;
  }

  .blog-body ol {
    list-style-type: decimal;
  }

  .blog-body li {
    margin-bottom: 10px;
    line-height: 1.75;
  }

  /* Tables inside blog body */
  .blog-body table {
    width: 100%;
    margin: 2rem 0;
    border-collapse: collapse;
    background: #ffffff;
    border: 1px solid rgba(201,145,61,0.25);
    border-radius: 6px;
    overflow: hidden;
    font-size: 0.95rem;
  }

  .blog-body th {
    background: #f4ecdf;
    color: #634d31;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    padding: 12px 16px;
    border-bottom: 2px solid rgba(201,145,61,0.3);
    text-align: left;
  }

  .blog-body td {
    padding: 12px 16px;
    border-bottom: 1px solid #eee5d8;
    color: #454545;
  }

  .blog-body tr:nth-child(even) td {
    background: #faf7f2;
  }

  .blog-body tr:hover td {
    background: #f5efe4;
  }

  /* Code blocks & Preformatted */
  .blog-body pre, .blog-body code {
    font-family: 'DM Mono', monospace;
    font-size: 0.9rem;
  }

  .blog-body pre {
    background: #201712;
    color: #f7e6c4;
    padding: 16px 20px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 1.8rem 0;
    border: 1px solid rgba(201,145,61,0.3);
  }

  .blog-body code {
    background: #ede3d3;
    color: #70471b;
    padding: 2px 6px;
    border-radius: 3px;
  }

  .blog-body pre code {
    background: transparent;
    color: inherit;
    padding: 0;
  }

  /* Responsive embedded videos (YouTube, Vimeo) */
  .blog-body iframe, .blog-body video {
    max-width: 100%;
    border-radius: 6px;
    margin: 20px 0;
  }

  .blog-intro-box {
    background: rgba(201,145,61,0.08);
    border-left: 3px solid var(--brand-primary, #c9913d);
    padding: 20px 24px;
    border-radius: 0 4px 4px 0;
    margin-bottom: 2rem;
    color: #70583b;
    font-style: italic;
    font-size: 1.05rem;
    line-height: 1.75;
  }

  .blog-body blockquote {
    background: rgba(201,145,61,0.08);
    border-left: 4px solid var(--brand-primary, #c9913d);
    padding: 18px 24px;
    margin: 2rem 0;
    color: #634d31;
    font-style: italic;
    border-radius: 0 4px 4px 0;
  }

  .blog-body img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
    margin: 20px 0;
  }

  .blog-body a {
    color: var(--brand-primary, #c9913d);
    text-decoration: underline;
  }

  /* Interactive Content Tabs within Blog */
  .blog-body .nav-tabs {
    border-bottom: 2px solid #e2d7c7;
    margin: 2rem 0 1.5rem 0;
    gap: 8px;
  }
  .blog-body .nav-tabs .nav-link {
    font-family: 'Cinzel', serif;
    font-size: 0.95rem;
    font-weight: 600;
    color: #634d31;
    background: #f4ecdf;
    border: 1px solid #e2d7c7;
    border-bottom: none;
    border-radius: 6px 6px 0 0;
    padding: 10px 20px;
    transition: all 0.2s ease;
  }
  .blog-body .nav-tabs .nav-link.active,
  .blog-body .nav-tabs .nav-link:hover {
    background: #4b1111;
    color: #f7e6c4;
    border-color: #4b1111;
  }
  .blog-body .tab-content {
    background: #ffffff;
    border: 1px solid #e2d7c7;
    border-top: none;
    border-radius: 0 0 6px 6px;
    padding: 24px;
    margin-top: -1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  }

  /* Custom Callout Box / Key Takeaway */
  .blog-callout {
    background: #fffdf9;
    border-left: 4px solid #c9913d;
    border-radius: 0 8px 8px 0;
    padding: 20px 24px;
    margin: 2rem 0;
    box-shadow: 0 2px 10px rgba(201,145,61,0.08);
  }
  .blog-callout-title {
    font-family: 'Cinzel', serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: #4b1111;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* Pros and Cons Grid */
  .pro-con-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 2rem 0;
  }
  @media (max-width: 768px) {
    .pro-con-grid { grid-template-columns: 1fr; }
  }
  .pro-card {
    background: #f4fbf6;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    padding: 18px 20px;
  }
  .con-card {
    background: #fdf6f6;
    border: 1px solid #f5c6cb;
    border-radius: 6px;
    padding: 18px 20px;
  }
  .pro-card h5 { color: #1e7e34; font-size: 1rem; font-weight: 700; margin-bottom: 12px; }
  .con-card h5 { color: #bd2130; font-size: 1rem; font-weight: 700; margin-bottom: 12px; }
  .pro-card ul, .con-card ul { margin-bottom: 0; padding-left: 20px; }

  /* Divider */
  .ornament-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 2.5rem 0;
    color: rgba(201,145,61,0.4);
    font-size: 1rem;
  }
  .ornament-divider::before,
  .ornament-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(201,145,61,0.2);
  }

  /* FAQ */
  .faq-item {
    border-bottom: 1px solid rgba(201,145,61,0.2);
    padding: 18px 0;
  }
  .faq-item:first-child { border-top: 1px solid rgba(201,145,61,0.2); }
  .faq-q {
    font-family: 'Cinzel', serif;
    font-size: 18px;
    color: #594d41;
    font-weight: 600;
    margin-bottom: 8px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
  }
  .faq-q svg { color: var(--brand-primary,#c9913d); flex-shrink: 0; transition: transform 0.3s ease; }
  .faq-a { font-size: 15px; color: #6e6459; line-height: 1.75; display: none; padding-top: 6px; }
  .faq-item.open .faq-a { display: block; }
  .faq-item.open .faq-q svg { transform: rotate(180deg); }

  /* Final thoughts box */
  .final-box {
    background: #fff8ef;
    border: 1px solid rgba(201,145,61,0.3);
    border-radius: 8px;
    padding: 36px 32px;
    margin-top: 3rem;
    text-align: center;
  }
  .final-box p { color: #6b5a45; margin-bottom: 1.8rem; font-size: 0.98rem; line-height: 1.8; }

  /* High contrast button for final box */
  .btn-explore-hotels {
    display: inline-block;
    padding: 12px 32px;
    background: #4b1111;
    color: #f7e6c4 !important;
    font-family: 'Cinzel', serif;
    font-size: 0.92rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    text-decoration: none !important;
    border-radius: 30px;
    box-shadow: 0 4px 14px rgba(75, 17, 17, 0.25);
    border: 1px solid #732222;
    transition: all 0.25s ease;
  }
  .btn-explore-hotels:hover {
    background: #6a1919;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(75, 17, 17, 0.35);
  }

  /* Sidebar styling matching reference page */
  .sidebar-widget {
    background: #ffffff;
    border: 1px solid rgba(201,145,61,0.2);
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border-radius: 6px;
    padding: 24px;
    margin-bottom: 24px;
  }
  .sidebar-widget h4 {
    font-family: 'Cinzel', serif;
    font-size: 0.95rem;
    color: #a67c52;
    margin-bottom: 1rem;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(201,145,61,0.2);
    letter-spacing: 1px;
    font-weight: 700;
  }
  .sidebar-hotel-list { list-style: none; padding: 0; margin: 0; counter-reset: item; }
  .sidebar-hotel-list li {
    padding: 10px 0;
    border-bottom: 1px solid #f0ebe1;
    font-size: 0.88rem;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .sidebar-hotel-list li:last-child { border-bottom: none; }
  .sidebar-hotel-list li::before {
    content: counter(item);
    counter-increment: item;
    background: var(--brand-primary,#c9913d);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    width: 24px; height: 24px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-family: 'Cinzel', serif;
  }
  .sidebar-hotel-list a {
    text-decoration: none;
    color: #4a3e31;
    font-weight: 500;
    transition: color 0.2s;
  }
  .sidebar-hotel-list a:hover { color: var(--brand-primary,#c9913d); }

  .cta-widget {
    background: linear-gradient(135deg, #4b1111 0%, #2b0606 100%);
    color: #fff;
    border-radius: 6px;
    padding: 26px;
    text-align: center;
    margin-bottom: 24px;
  }
  .cta-widget h4 {
    font-family: 'Cinzel', serif;
    color: #f7e6c4;
    font-size: 1.15rem;
    margin-bottom: 10px;
  }
  .cta-widget p {
    font-size: 0.88rem;
    color: #dfddd6;
    line-height: 1.6;
    margin-bottom: 18px;
  }

  .btn-primary-custom {
    display: inline-block;
    padding: 10px 24px;
    background: var(--brand-primary, #c9913d);
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    text-decoration: none;
    border-radius: 20px;
    transition: all 0.25s;
  }
  .btn-primary-custom:hover {
    background: #a67c52;
    color: #fff;
  }
</style>

<!-- ══════════ MAIN CONTENT ══════════ -->
<div style="background-color: #f8f5f0;">
  <div class="container">
    <div class="blog-layout">

      <!-- ── ARTICLE BODY ── -->
      <article class="blog-body">

        <?php if ($post['status'] === 'draft'): ?>
        <div class="alert alert-warning d-flex align-items-center justify-content-between my-3 py-2 px-3" role="alert" style="border-radius: 4px; font-size: 0.9rem;">
          <div>
            <i class="bi bi-eye-slash-fill me-2"></i><strong>Draft Preview:</strong> This article is unpublished and only visible to you because you are logged into admin.
          </div>
          <a href="/admin/blog-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-dark" style="font-size:0.8rem;">Edit in Admin</a>
        </div>
        <?php endif; ?>

        <?php if (!empty($post['featured_image'])): ?>
        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: auto; max-height: 480px; border-radius: 6px; object-fit: cover; margin-top: 15px;">
        <?php endif; ?>

        <h1><?php echo htmlspecialchars($post['title']); ?></h1>

        <div class="blog-meta">
          <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($post['author']); ?></span>
          <span><i class="bi bi-calendar3"></i> <?php echo date('F j, Y', strtotime((string)$post['published_at'])); ?></span>
          <?php if (!empty($post['category_name'])): ?>
          <span><i class="bi bi-tag"></i> <a href="/blogs/category/<?php echo urlencode($post['category_slug']); ?>" style="color:inherit; text-decoration:none;"><?php echo htmlspecialchars($post['category_name']); ?></a></span>
          <?php endif; ?>
          <span><i class="bi bi-eye"></i> <?php echo number_format((int)$post['views'] + 1); ?> views</span>
        </div>

        <?php if (!empty($post['excerpt'])): ?>
        <div class="blog-intro-box">
          <?php echo htmlspecialchars($post['excerpt']); ?>
        </div>
        <?php endif; ?>

        <div class="content">
          <?php echo $post['content']; ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($post['tags'])): ?>
        <div class="ornament-divider">❖</div>
        <div style="margin: 1.5rem 0;">
          <span style="color: #7d6b56; font-size: 0.85rem; margin-right: 8px; font-weight: 600;">Tags:</span>
          <?php 
          $tagList = explode(',', (string)$post['tags']);
          foreach ($tagList as $t): 
              $tagTrim = trim($t);
              if ($tagTrim === '') continue;
          ?>
          <span style="display:inline-block; padding: 4px 12px; background: #fff; border: 1px solid rgba(201,145,61,0.3); border-radius: 14px; font-size: 0.8rem; color: #7d6b56; margin: 0 4px 4px 0;">
            #<?php echo htmlspecialchars($tagTrim); ?>
          </span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Optional FAQ Accordion -->
        <?php if (!empty($faqEntities)): ?>
        <div class="ornament-divider">❖</div>
        <h2 style="margin-top:0;">Frequently Asked Questions</h2>
        <?php foreach ($faqEntities as $index => $faq): ?>
        <div class="faq-item <?php echo $index === 0 ? 'open' : ''; ?>">
          <div class="faq-q">
            <?php echo htmlspecialchars($faq['name']); ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
          </div>
          <div class="faq-a">
            <?php echo htmlspecialchars($faq['acceptedAnswer']['text']); ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Final thoughts box -->
        <div class="final-box">
          <h2 style="color: #a67c52; margin-top:0; font-size:1.4rem;">Plan Your Udaipur Vacation</h2>
          <p>Explore over 500+ curated boutique hotels, palaces, and heritage havelis across Udaipur to find your ideal stay.</p>
          <a href="/hotels" class="btn-explore-hotels">Explore All Boutique Hotels</a>
        </div>

      </article>

      <!-- ── SIDEBAR ── -->
      <aside class="blog-sidebar" style="position: sticky; top: 90px;">
        
        <!-- Booking CTA -->
        <div class="cta-widget">
          <h4>Visiting Udaipur?</h4>
          <p>Get personalized recommendations and exclusive deals on top boutique havelis directly from local experts.</p>
          <a href="/contact" class="btn btn-light btn-sm" style="font-weight: 700; border-radius: 20px; padding: 8px 18px; color: #4b1111;">
            Enquire Now
          </a>
        </div>

        <!-- Related Guides -->
        <?php if (!empty($relatedPosts)): ?>
        <div class="sidebar-widget">
          <h4>Related Travel Guides</h4>
          <ol class="sidebar-hotel-list">
            <?php foreach ($relatedPosts as $r): ?>
            <li>
              <a href="/blog/<?php echo urlencode($r['slug']); ?>">
                <?php echo htmlspecialchars($r['title']); ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ol>
        </div>
        <?php endif; ?>

        <!-- Featured Hotels in Udaipur -->
        <div class="sidebar-widget">
          <h4>Popular Boutique Stays</h4>
          <ol class="sidebar-hotel-list">
            <li><a href="/hotels/kaladwas-lal-haveli">Kaladwas Lal Haveli</a></li>
            <li><a href="/hotels/amet-haveli">Amet Haveli</a></li>
            <li><a href="/hotels/jagat-niwas-palace">Jagat Niwas Palace</a></li>
            <li><a href="/hotels/udai-kothi">Udai Kothi</a></li>
            <li><a href="/hotels/chunda-palace">Chunda Palace</a></li>
          </ol>
          <div class="text-center mt-3">
            <a href="/hotels" class="small text-decoration-none" style="color: var(--brand-primary, #c9913d); font-weight: 600;">View All Hotels →</a>
          </div>
        </div>

      </aside>

    </div><!-- /blog-layout -->
  </div><!-- /container -->
</div>

<script>
  // Toggle FAQ items
  document.querySelectorAll('.faq-q').forEach(function(question) {
    question.addEventListener('click', function() {
      const item = this.closest('.faq-item');
      item.classList.toggle('open');
    });
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
