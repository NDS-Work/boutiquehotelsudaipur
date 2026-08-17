<?php
$metaTitle       = 'Top 10 Hidden Boutique Hotels in Rajasthan with Royal Hospitality | Boutique Hotels In Udaipur';
$metaDescription = 'Discover 10 hidden boutique hotels across Rajasthan offering royal heritage hospitality, palace architecture, and curated cultural experiences.';
$canonicalUrl    = 'https://boutiquehotelsudaipur.com/Top-10-Hidden-Boutique-Hotels-in-Rajasthan-with-Royal-Hospitality';
$schemaJson = json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'         => 'Article',
            'headline'      => 'Top 10 Hidden Boutique Hotels in Rajasthan with Royal Hospitality',
            'description'   => 'Discover the most hidden and underrated boutique hotels across Rajasthan offering authentic royal hospitality, heritage architecture, and off-the-beaten-path charm.',
            'url'           => 'https://boutiquehotelsudaipur.com/Top-10-Hidden-Boutique-Hotels-in-Rajasthan-with-Royal-Hospitality',
            'datePublished' => '2026-01-01',
            'dateModified'  => date('Y-m-d'),
            'publisher'     => ['@type' => 'Organization', 'name' => 'Boutique Hotels In Udaipur', 'url' => 'https://boutiquehotelsudaipur.com'],
            'author'        => ['@type' => 'Organization', 'name' => 'Boutique Hotels In Udaipur Editorial Team'],
            'image'         => 'https://boutiquehotelsudaipur.com/assets/footer-image/lake-pichola.webp',
        ],
        [
            '@type'      => 'FAQPage',
            'mainEntity' => [
                ['@type' => 'Question', 'name' => 'What makes a boutique hotel in Rajasthan "royal"?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'A royal boutique hotel in Rajasthan is typically a restored palace, haveli, or fort that belonged to a royal or noble family. These properties offer personalised hospitality, heritage architecture, traditional Rajasthani cuisine, and cultural experiences rooted in the region\'s regal history.']],
                ['@type' => 'Question', 'name' => 'Are there hidden boutique hotels in Rajasthan away from tourist crowds?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. Beyond Jaipur and Udaipur, Rajasthan has many hidden boutique hotels in smaller towns and villages such as Bundi, Sawai Madhopur, Narlai, and Deogarh — offering authentic royal hospitality with far fewer crowds.']],
                ['@type' => 'Question', 'name' => 'What is the best time to visit boutique heritage hotels in Rajasthan?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'The best time to visit heritage boutique hotels in Rajasthan is between October and March, when the weather is cool and pleasant. This period coincides with major cultural festivals including Diwali, Pushkar Fair, and the Jaipur Literature Festival.']],
                ['@type' => 'Question', 'name' => 'How do hidden boutique hotels in Rajasthan differ from luxury palace hotels?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Hidden boutique hotels in Rajasthan are typically smaller, more intimate properties with fewer rooms and more personalised service compared to large luxury palace hotels. They offer authentic local character and cultural immersion at a more accessible price point.']],
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

require_once 'includes/header.php';
?>
    <style>
        :root {
            --brand-primary: #c9913d;
        }

        html {
            scroll-behavior: smooth;
        }

        .blog-title {
            font-family: 'Cinzel', serif;
            font-size: clamp(1.8rem, 4vw, 2.9rem);
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
        }

        .blog-subtitle {
            font-size: 1.05rem;
            color: #fff;
            max-width: 680px;
            line-height: 1.7;
        }

        .blog-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            padding: 14px 0;
            font-size: 0.82rem;
            color: #fff;
        }

        .blog-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Layout ── */
        .blog-section {
            background-color: #f8f5f0;
            padding: 60px 0 80px;
        }

        /* ── Body text ── */
        .blog-body {
            color: #c8b49a;
            font-size: 1rem;
            line-height: 1.85;
        }
        .blog-body h1 {
            color: #a67c52;
            font-size: 2.5rem;
            line-height: 1.2;
            margin: 25px 0 15px 0;
        }

        .blog-body p {
            margin-bottom: 1.3rem;
        }

        .content {
            color: #888680bd;
        }

        .blog-body h2 {
            font-family: 'Cinzel', serif;
            font-size: 1.5rem;
            color: #a67c52;
            margin: 2.5rem 0 0.5rem;
            line-height: 1.3;
        }

        .blog-body h3 {
            font-family: 'Cinzel', serif;
            font-size: 1.1rem;
            color: #e8d5b5;
            margin: 0.3rem 0 0.8rem;
            font-weight: 400;
            font-style: italic;
        }

        /* ── Criteria grid ── */
        .criteria-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(201, 145, 61, 0.2);
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #c8b49a;
        }

        /* ── Section label ── */
        .section-label {
            font-family: 'Cinzel', serif;
            font-size: 0.68rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--brand-primary);
            margin-bottom: 0.5rem;
        }

        /* ── Hotel cards ── */
        .hotel-card {
            background: rgba(255, 255, 255, 0.035);
            border: 1px solid rgba(201, 145, 61, 0.18) !important;
            border-radius: 6px;
            padding: 28px 28px 22px;
            margin: 2rem 0;
            position: relative;
            transition: border-color 0.3s;
        }

        .hotel-card:hover {
            border-color: rgba(201, 145, 61, 0.5) !important;
        }

        .hotel-number {
            position: absolute;
            top: -16px;
            left: 24px;
            background: var(--brand-primary);
            color: #fff;
            font-family: 'Cinzel', serif;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 14px;
            letter-spacing: 2px;
            border-radius: 2px;
        }

        .hotel-card h2 {
            font-family: 'Cinzel', serif;
            font-size: 30px;
            color: var(--brand-primary);
            margin: 0.5rem 0 0.2rem;
            font-weight: 500;
        }

        .hotel-card h3 {
            font-size: 18px;
            color: #868580;
            margin: 0 0 1rem;
            font-family: inherit;
            font-style: normal;
        }

        /* ── Highlights list ── */
        .highlights-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        .highlights-list>li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 0.85rem;
            color: #b8a48a;
            font-weight: 600;
        }

        .highlights-list li::before {
            content: '✦';
            color: var(--brand-primary);
            font-size: 0.6rem;
            margin-top: 3px;
            flex-shrink: 0;
        }

        @media (max-width: 600px) {
            .highlights-list {
                grid-template-columns: 1fr;
            }
        }

        /* ── Card CTA ── */
        .card-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            padding: 9px 20px;
            background: var(--brand-primary);
            color: #fff;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-decoration: none;
            border-radius: 20px;
            transition: background 0.25s, transform 0.2s;
        }

        .card-cta:hover {
            background: #fff;
            transform: translateY(-1px);
            color: #a67c52;
        }

        /* ── Why grid ── */
        .why-item {
            background: rgba(201, 145, 61, 0.07);
            padding: 14px 16px;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #c8b49a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .why-item::before {
            content: '◈';
            color: var(--brand-primary);
        }

        /* ── Final box ── */
        .final-box {
            background: #f8e7ca8a;
            border: 1px solid rgba(201, 145, 61, 0.3);
            border-radius: 8px;
            padding: 32px;
            margin-top: 3rem;
            text-align: center;
        }

        .final-box p {
            color: #c8b49a;
            margin-bottom: 1.5rem;
            font-size: 0.98rem;
            line-height: 1.8;
        }

        .btn-primary-custom {
            display: inline-block;
            padding: 10px 28px;
            background: var(--brand-primary);
            color: #fff;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: background 0.25s;
        }

        .btn-primary-custom:hover {
            background: #a67c52;
            color: #fff;
        }

        /* ── Sidebar ── */
        .sidebar-widget {
            background: rgba(255, 255, 255, 0.035);
            border: 1px solid rgba(201, 145, 61, 0.2);
            border-radius: 6px;
            padding: 24px;
        }

        .sidebar-widget h4 {
            font-family: 'Cinzel', serif;
            font-size: 0.9rem;
            color: var(--brand-primary);
            margin-bottom: 1rem;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(201, 145, 61, 0.2);
            letter-spacing: 1px;
        }

        .sidebar-hotel-list {
            list-style: none;
            padding: 0;
            margin: 0;
            counter-reset: item;
        }

        .sidebar-hotel-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.84rem;
            color: #b8a48a;
            display: flex;
            align-items: center;
            gap: 8px;
            counter-increment: item;
        }

        .sidebar-hotel-list li:last-child {
            border-bottom: none;
        }

        .sidebar-hotel-list li::before {
            content: counter(item);
            background: var(--brand-primary);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            min-width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-family: 'Cinzel', serif;
        }

        .sidebar-hotel-list>li>a {
            text-decoration: none;
            color: inherit;
            transition: color 0.2s;
        }

        .sidebar-hotel-list li a:hover,
        .sidebar-hotel-list li a.active {
            color: var(--brand-primary);
            font-weight: 700;
        }

        /* ── Divider ── */
        .ornament-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 2.5rem 0;
            color: rgba(201, 145, 61, 0.4);
            font-size: 1rem;
        }

        .ornament-divider::before,
        .ornament-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(201, 145, 61, 0.2);
        }
    </style>
</head>

<body>
    <!-- ══════════ MAIN CONTENT ══════════ -->
    <div class="blog-section">
        <div class="container">
            <div class="row g-5" style="align-items: flex-start;">

                <!-- ── ARTICLE ── -->
                <div class="col-lg-8">
                    <article class="blog-body">
                        <img src="./assets/footer-image/preview.webp" alt="Boutique Hotels in Rajasthan with Royal Hospitality in Udaipur" style="width: 100%; height: auto; border-radius: 6px; object-fit: cover; margin-top: 30px;">
                        <h1>Top 10 Hidden Boutique Hotels in Rajasthan with Royal Hospitality</h1>
                        <div class="content">
                            <p>From royal palaces and centuries-old havelis to peaceful countryside retreats, Rajasthan
                                is home to some of India's most extraordinary boutique heritage stays. While many
                                travelers know Rajasthan for its famous palace hotels, there are also several hidden
                                boutique hotels that offer intimate luxury experiences rooted in royal hospitality and
                                authentic cultural traditions.</p>
                            <p>Unlike crowded commercial resorts, hidden boutique hotels in Rajasthan focus on
                                delivering experiences that feel personal, authentic, and deeply connected to the
                                region's royal heritage.</p>
                            <p>This curated list features some of the best hidden boutique hotels in Rajasthan that
                                combine royal hospitality, heritage charm, and unique travel experiences.</p>
                        </div>

                        <!-- Criteria -->
                        <div class="row row-cols-2 row-cols-sm-3 g-2 my-3">
                            <div class="col">
                                <div class="criteria-item">Personalized hospitality</div>
                            </div>
                            <div class="col">
                                <div class="criteria-item">Heritage architecture</div>
                            </div>
                            <div class="col">
                                <div class="criteria-item">Authentic Rajasthan experiences</div>
                            </div>
                            <div class="col">
                                <div class="criteria-item">Peaceful luxury stays</div>
                            </div>
                            <div class="col">
                                <div class="criteria-item">Cultural immersion</div>
                            </div>
                            <div class="col">
                                <div class="criteria-item">Traditional Rajputana ambiance</div>
                            </div>
                        </div>

                        <!-- HOTEL 1 -->
                        <div id="kaladwas-lal-haveli" class="hotel-card">
                            <div class="hotel-number">No. 01</div>
                            <h2>Kaladwas Lal Haveli</h2>
                            <h3>A Hidden 300-Year-Old Heritage Boutique Hotel in the Heart of Udaipur</h3>
                            <div class="content">
                                <p>Among the most beautiful hidden boutique hotels in Rajasthan, Kaladwas Lal Haveli
                                    offers travelers an intimate heritage experience inspired by the royal traditions of
                                    Mewar.</p>
                                <p>Located in the old city of Udaipur near Lake Pichola, City Palace, and Jagdish
                                    Temple, this beautifully restored 300-year-old haveli combines traditional Mewari
                                    architecture with warm personalized hospitality. Unlike large luxury hotels,
                                    Kaladwas Lal Haveli focuses on authentic cultural experiences through heritage
                                    storytelling, handcrafted interiors, rooftop dining, local experiences, and boutique
                                    hospitality that feels deeply personal.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Authentic 300-year-old heritage haveli</li>
                                <li>Traditional Mewari architecture</li>
                                <li>Personalized royal hospitality</li>
                                <li>Rooftop dining experience</li>
                                <li>Prime location near Lake Pichola</li>
                                <li>Cultural experiences and heritage walks</li>
                                <li>Ideal for couples and heritage travelers</li>
                            </ul>
                            <a href="https://kaladwashotels.com/" target="_blank" rel="noopener" class="card-cta">
                                Explore &amp; Book Your Stay
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                                </svg>
                            </a>
                        </div>

                        <!-- HOTEL 2 -->
                        <div id="rawla-narlai" class="hotel-card">
                            <div class="hotel-number">No. 02</div>
                            <h2>Rawla Narlai</h2>
                            <h3>A Royal Boutique Retreat Between Udaipur and Jodhpur</h3>
                            <div class="content">
                                <p>Rawla Narlai is one of Rajasthan's hidden luxury heritage gems, offering travelers an
                                    elegant countryside stay surrounded by history and natural beauty.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Heritage palace ambiance</li>
                                <li>Luxury rural Rajasthan experience</li>
                                <li>Traditional hospitality</li>
                                <li>Historic architecture</li>
                            </ul>
                        </div>

                        <!-- HOTEL 3 -->
                        <div id="chanoud-garh" class="hotel-card">
                            <div class="hotel-number">No. 03</div>
                            <h2>Chanoud Garh</h2>
                            <h3>A Boutique Heritage Palace Preserving Rajasthan's Aristocratic Legacy</h3>
                            <div class="content">
                                <p>Chanoud Garh offers travelers an authentic glimpse into Rajasthan's royal history
                                    through its restored palace architecture and intimate boutique hospitality.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Historic royal architecture</li>
                                <li>Personalized hospitality</li>
                                <li>Authentic Rajasthan culture</li>
                                <li>Peaceful heritage ambiance</li>
                            </ul>
                        </div>

                        <!-- HOTEL 4 -->
                        <div id="suryagarh" class="hotel-card">
                            <div class="hotel-number">No. 04</div>
                            <h2>Suryagarh</h2>
                            <h3>A Luxury Boutique Hotel Inspired by Desert Royalty</h3>
                            <div class="content">
                                <p>Suryagarh combines royal architecture, curated cultural experiences, and luxury
                                    hospitality inspired by Rajasthan's desert heritage.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Desert luxury experience</li>
                                <li>Royal architecture</li>
                                <li>Cultural immersion experiences</li>
                                <li>Luxury boutique hospitality</li>
                            </ul>
                        </div>

                        <!-- HOTEL 5 -->
                        <div id="samode-haveli" class="hotel-card">
                            <div class="hotel-number">No. 05</div>
                            <h2>Samode Haveli</h2>
                            <h3>A Hidden Heritage Oasis in the Heart of Jaipur</h3>
                            <div class="content">
                                <p>Samode Haveli beautifully blends Mughal and Rajput architecture with intimate
                                    boutique luxury and royal Rajasthan hospitality.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Royal courtyard architecture</li>
                                <li>Traditional Rajasthan interiors</li>
                                <li>Boutique luxury ambiance</li>
                                <li>Premium heritage hospitality</li>
                            </ul>
                        </div>

                        <!-- HOTEL 6 -->
                        <div id="narain-niwas-palace" class="hotel-card">
                            <div class="hotel-number">No. 06</div>
                            <h2>Narain Niwas Palace</h2>
                            <h3>A Boutique Palace Hotel with Vintage Rajasthan Charm</h3>
                            <div class="content">
                                <p>Narain Niwas Palace is admired for its old-world elegance, royal décor, and peaceful
                                    heritage atmosphere.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Vintage palace ambiance</li>
                                <li>Traditional architecture</li>
                                <li>Heritage dining experiences</li>
                                <li>Boutique-style luxury hospitality</li>
                            </ul>
                        </div>

                        <!-- HOTEL 7 -->
                        <div id="raas-jodhpur" class="hotel-card">
                            <div class="hotel-number">No. 07</div>
                            <h2>RAAS Jodhpur</h2>
                            <h3>A Stylish Boutique Heritage Hotel Overlooking Mehrangarh Fort</h3>
                            <div class="content">
                                <p>RAAS Jodhpur offers a unique blend of traditional architecture and contemporary
                                    luxury near one of Rajasthan's most iconic forts.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Mehrangarh Fort views</li>
                                <li>Luxury boutique stay</li>
                                <li>Heritage-inspired design</li>
                                <li>Premium hospitality</li>
                            </ul>
                        </div>

                        <!-- HOTEL 8 -->
                        <div id="dera-mandawa" class="hotel-card">
                            <div class="hotel-number">No. 08</div>
                            <h2>Dera Mandawa</h2>
                            <h3>A Traditional Boutique Haveli with Royal Rajasthan Hospitality</h3>
                            <div class="content">
                                <p>Dera Mandawa is a peaceful heritage haveli offering travelers a more intimate and
                                    culturally authentic Jaipur experience.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Traditional haveli architecture</li>
                                <li>Personalized hospitality</li>
                                <li>Boutique heritage ambiance</li>
                                <li>Authentic Rajasthan culture</li>
                            </ul>
                        </div>

                        <!-- HOTEL 9 -->
                        <div id="fort-pokaran" class="hotel-card">
                            <div class="hotel-number">No. 09</div>
                            <h2>The Fort Pokaran</h2>
                            <h3>A Hidden Heritage Fort Hotel in Rajasthan's Desert Region</h3>
                            <div class="content">
                                <p>The Fort Pokaran offers travelers a unique stay experience inside a restored royal
                                    fort featuring traditional Rajput architecture and cultural elegance.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Historic fort architecture</li>
                                <li>Desert heritage experience</li>
                                <li>Boutique luxury hospitality</li>
                                <li>Traditional Rajasthan ambiance</li>
                            </ul>
                        </div>

                        <!-- HOTEL 10 -->
                        <div id="shahpura-house" class="hotel-card">
                            <div class="hotel-number">No. 10</div>
                            <h2>Shahpura House</h2>
                            <h3>A Royal Boutique Heritage Hotel Inspired by Rajputana Grandeur</h3>
                            <div class="content">
                                <p>Shahpura House combines traditional Rajasthan craftsmanship, palace-style interiors,
                                    and warm hospitality to create an elegant boutique stay experience.</p>
                            </div>
                            <ul class="highlights-list">
                                <li>Palace-inspired architecture</li>
                                <li>Traditional Rajasthan artwork</li>
                                <li>Luxury boutique suites</li>
                                <li>Royal hospitality experience</li>
                            </ul>
                        </div>

                        <!-- Why Section -->
                        <div class="ornament-divider">❖</div>
                        <div class="section-label">Insights</div>
                        <h2 style="margin-top:0;">Why Travelers Prefer Hidden Boutique Hotels in Rajasthan</h2>
                        <div class="content">
                            <p>Travelers increasingly prefer hidden boutique hotels in Rajasthan because they offer
                                experiences that go beyond conventional luxury accommodation:</p>
                        </div>

                        <div class="row row-cols-2 row-cols-sm-3 g-2 my-3">
                            <div class="col">
                                <div class="why-item">Peaceful & intimate stays</div>
                            </div>
                            <div class="col">
                                <div class="why-item">Personalized royal hospitality</div>
                            </div>
                            <div class="col">
                                <div class="why-item">Authentic heritage architecture</div>
                            </div>
                            <div class="col">
                                <div class="why-item">Cultural immersion</div>
                            </div>
                            <div class="col">
                                <div class="why-item">Unique local experiences</div>
                            </div>
                            <div class="col">
                                <div class="why-item">Connection with Rajasthan traditions</div>
                            </div>
                        </div>

                        <div class="content">
                            <p>Unlike large commercial hotels, boutique heritage stays provide a more meaningful and
                                memorable Rajasthan experience rooted in authenticity and storytelling.</p>
                        </div>

                        <!-- Final Thoughts -->
                        <div class="final-box">
                            <div class="section-label text-center">Final Thoughts</div>
                            <h2 style="color:#a67c52; margin-top:0.5rem; font-size:1.4rem;">The Timeless Allure of
                                Rajasthan's Hidden Boutique Hotels</h2>
                            <p>Whether you are planning a romantic getaway, cultural Rajasthan tour, luxury heritage
                                vacation, or peaceful boutique escape, hidden boutique hotels in Rajasthan offer
                                unforgettable royal hospitality experiences.</p>
                            <p>Among them, <strong style="color:var(--brand-primary);">Kaladwas Lal Haveli</strong>
                                stands out for its authentic 300-year-old heritage architecture, personalized
                                hospitality, traditional Mewari charm, and immersive cultural experiences — making it
                                one of Rajasthan's finest hidden boutique heritage hotels.</p>
                            <a href="/hotels" class="btn-primary-custom">Explore All Boutique Hotels</a>
                        </div>

                    </article>
                </div><!-- /col -->

                <!-- ── SIDEBAR ── -->
                <div class="col-lg-4 d-none d-lg-block" style="align-self: flex-start; position: sticky; top: 30px;">
                    <div class="sidebar-widget">
                        <h4>Hotels in This Guide</h4>
                        <ol class="sidebar-hotel-list" id="sidebar-hotel-list">
                            <li><a href="#kaladwas-lal-haveli">Kaladwas Lal Haveli</a></li>
                            <li><a href="#rawla-narlai">Rawla Narlai</a></li>
                            <li><a href="#chanoud-garh">Chanoud Garh</a></li>
                            <li><a href="#suryagarh">Suryagarh</a></li>
                            <li><a href="#samode-haveli">Samode Haveli</a></li>
                            <li><a href="#narain-niwas-palace">Narain Niwas Palace</a></li>
                            <li><a href="#raas-jodhpur">RAAS Jodhpur</a></li>
                            <li><a href="#dera-mandawa">Dera Mandawa</a></li>
                            <li><a href="#fort-pokaran">The Fort Pokaran</a></li>
                            <li><a href="#shahpura-house">Shahpura House</a></li>
                        </ol>
                    </div>
                </div>

            </div><!-- /row -->
        </div><!-- /container -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll on sidebar link click
        document.querySelectorAll('#sidebar-hotel-list a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // Highlight active sidebar link while scrolling
        var hotelCards = document.querySelectorAll('.hotel-card');
        var sidebarLinks = document.querySelectorAll('#sidebar-hotel-list a');
        window.addEventListener('scroll', function () {
            var current = '';
            hotelCards.forEach(function (card) {
                if (window.scrollY >= card.offsetTop - 120) current = card.getAttribute('id');
            });
            sidebarLinks.forEach(function (link) {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) link.classList.add('active');
            });
        });
    </script>

<?php require_once 'includes/footer.php'; ?>