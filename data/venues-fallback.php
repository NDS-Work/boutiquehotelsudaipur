<?php

// Static fallback data used when database connectivity is unavailable.
$fallbackVenues = [
    [
        'id' => 1,
        'name' => 'Amantra Shilpi Resort & Spa',
        'slug' => 'amantra-shilpi-resort',
        'location' => 'Fateh Sagar Lake Area',
        'locationCategory' => 'fateh-sagar',
        'pricePerPlate' => 950,
        'priceRange' => 'budget-friendly',
        'capacity' => ['min' => 20, 'max' => 550],
        'capacityCategory' => 'medium',
        'rooms' => 42,
        'acres' => 5,
        'venueType' => ['Luxury Resort', 'Lakeside'],
        'amenities' => ['Indoor & Outdoor Venues', 'Poolside Events', 'In-house Catering', 'Bridal Dressing Room', 'In-house DJ & Decor', 'Valet Parking', '42 Ethnically Styled Rooms'],
        'description' => 'Amantra Shilpi Resort offers exceptional value with 5 acres of landscaped gardens near Fateh Sagar Lake. Perfect for intimate to large weddings with versatile indoor and outdoor spaces including Insight & Insearch halls, poolside venues, and live grill areas.',
        'images' => ['https://images.unsplash.com/photo-1715178003372-9f790b685775?w=1200&h=900&fit=crop', 'https://images.unsplash.com/photo-1706811042876-0073ab8b7527?w=1200&h=900&fit=crop'],
        'bookingUrl' => 'https://www.amantrashilpiresort.com/',
        'highlighted' => true,
        'packageCost' => null,
    ],
    [
        'id' => 2,
        'name' => 'The Oberoi Udaivilas',
        'slug' => 'oberoi-udaivilas',
        'location' => 'Lake Pichola Waterfront',
        'locationCategory' => 'lake-pichola',
        'pricePerPlate' => 8000,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 150, 'max' => 400],
        'capacityCategory' => 'medium',
        'rooms' => 87,
        'acres' => 50,
        'venueType' => ['Heritage Palace', 'Lakeside', 'Contemporary Luxury Hotel'],
        'amenities' => ['50 Acres of Gardens', 'Lake Pichola Views', 'Luxury Palace Architecture', 'Multiple Terraces', 'Poolside Venues', '87 Rooms & Suites', 'Full Wedding Planning'],
        'description' => "The Oberoi Udaivilas is the epitome of luxury on Lake Pichola's banks. Set across 50 acres, this grand palace hotel offers unmatched elegance with intricate domes, courtyards, and stunning lake views. Perfect for ultra-luxury destination weddings.",
        'images' => ['https://images.unsplash.com/photo-1679234417190-9c443548a40c?w=1200&h=900&fit=crop', 'https://images.unsplash.com/photo-1696861679643-4f21bfba8fc3?w=1200&h=900&fit=crop'],
        'packageCost' => '₹1.1 - 6 Crore',
        'bookingUrl' => 'https://www.oberoihotels.com/hotels-in-udaipur-udaivilas-resort/',
        'highlighted' => true,
    ],
    [
        'id' => 3,
        'name' => 'Taj Lake Palace',
        'slug' => 'taj-lake-palace',
        'location' => 'Lake Pichola Island',
        'locationCategory' => 'lake-pichola',
        'pricePerPlate' => 12500,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 50, 'max' => 500],
        'capacityCategory' => 'medium',
        'rooms' => 83,
        'acres' => null,
        'venueType' => ['Heritage Palace', 'Island Venue', 'Lakeside'],
        'amenities' => ['Floating Palace on Lake Pichola', 'Boat Access Only', '83 Luxury Rooms', 'Heritage Halls', 'Outdoor Terraces', 'Courtyards & Pavilions', 'Exclusive Property Buyout'],
        'description' => "The iconic Taj Lake Palace floats majestically on Lake Pichola. This 18th-century marvel offers the ultimate intimate and exclusive wedding experience with boat access, heritage interiors, and breathtaking lake views from every angle.",
        'images' => ['https://images.unsplash.com/photo-1712817616402-122c951e5dae?w=1200&h=900&fit=crop', 'https://images.unsplash.com/photo-1695956353120-54ce5e91632b?w=1200&h=900&fit=crop'],
        'packageCost' => '₹50 Lakhs - 5 Crore',
        'bookingUrl' => 'https://www.tajhotels.com/en-in/hotels/taj-lake-palace-udaipur/',
        'highlighted' => true,
    ],
    [
        'id' => 4,
        'name' => 'Radisson Blu Palace Resort & Spa',
        'slug' => 'radisson-blu-palace',
        'location' => 'Fateh Sagar Lake Area',
        'locationCategory' => 'fateh-sagar',
        'pricePerPlate' => 4500,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 50, 'max' => 1200],
        'capacityCategory' => 'grand',
        'rooms' => 244,
        'acres' => null,
        'venueType' => ['Contemporary Luxury Hotel', 'Lakeside'],
        'amenities' => ['244 Rooms', 'Fatehsagar Grand Ballroom (600 seated)', 'Aravali Lawn (800 seated, 1200 floating)', 'Multiple Indoor/Outdoor Venues', 'Fateh Sagar Lake Views', 'Pillar-less Ballroom', 'Spa & Wellness'],
        'description' => "Radisson Blu Palace Resort near Fateh Sagar Lake offers grand celebrations with Udaipur's largest pillar-less ballroom and multiple versatile venues. Perfect for 50 to 1,200 guests with stunning Aravali Hills and lake backdrop.",
        'images' => ['https://images.pexels.com/photos/33726143/pexels-photo-33726143.jpeg?w=1200&h=900&fit=crop', 'https://images.unsplash.com/photo-1759490821541-f78bb13a752d?w=1200&h=900&fit=crop'],
        'packageCost' => '₹43 Lakhs onwards',
        'bookingUrl' => 'https://www.radissonhotels.com/en-us/hotels/radisson-blu-udaipur-palace',
        'highlighted' => true,
    ],
    [
        'id' => 5,
        'name' => 'RAAS Devigarh',
        'slug' => 'raas-devigarh',
        'location' => 'Aravalli Hills (Delwara)',
        'locationCategory' => 'aravalli-hills',
        'pricePerPlate' => 4200,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 150, 'max' => 250],
        'capacityCategory' => 'intimate',
        'rooms' => 39,
        'acres' => null,
        'venueType' => ['Heritage Palace', 'Hilltop & Mountain View', 'Boutique & Intimate'],
        'amenities' => ['18th Century Palace', '39 Luxury Suites', 'Durbar Courtyard', 'Janana Courtyard', 'Terrace Gardens', 'Aravalli Hills Setting', '28km from Udaipur'],
        'description' => 'RAAS Devigarh is an 18th-century palace fort nestled in the Aravalli foothills near Eklingji Temple. This intimate heritage property blends royal architecture with modern luxury, perfect for 150-250 guest weddings seeking exclusivity.',
        'images' => ['https://images.unsplash.com/photo-1627490631692-3eb501e21cd1?w=1200&h=900&fit=crop', 'https://images.unsplash.com/photo-1706811042876-0073ab8b7527?w=1200&h=900&fit=crop'],
        'packageCost' => '₹37 Lakhs onwards',
        'bookingUrl' => 'https://www.raashotels.com/devigarh/',
        'highlighted' => false,
    ],
    [
        'id' => 6,
        'name' => 'The Leela Palace Udaipur',
        'slug' => 'leela-palace-udaipur',
        'location' => 'Lake Pichola Waterfront',
        'locationCategory' => 'lake-pichola',
        'pricePerPlate' => 8500,
        'priceRange' => 'ultra-luxury',
        'capacity' => ['min' => 200, 'max' => 400],
        'capacityCategory' => 'medium',
        'rooms' => 80,
        'acres' => null,
        'venueType' => ['Heritage Palace', 'Lakeside', 'Contemporary Luxury Hotel'],
        'amenities' => ['80 Luxury Rooms', 'Lake Pichola Frontage', 'Royal Architecture', 'Multiple Terraces', 'Poolside Venues', 'Grand Ballroom', 'Spa & Wellness'],
        'description' => "The Leela Palace Udaipur combines regal splendor with contemporary luxury on Lake Pichola's shores. With 80 rooms and multiple event spaces, it's ideal for 200-400 guest weddings seeking lakefront grandeur.",
        'images' => ['https://images.unsplash.com/photo-1679234417190-9c443548a40c?w=1200&h=900&fit=crop', 'https://images.unsplash.com/photo-1696861679643-4f21bfba8fc3?w=1200&h=900&fit=crop'],
        'packageCost' => '₹18-25 Lakhs',
        'bookingUrl' => 'https://www.theleela.com/the-leela-palace-udaipur',
        'highlighted' => false,
    ],
    [
        'id' => 7,
        'name' => 'Trident Udaipur',
        'slug' => 'trident-udaipur',
        'location' => 'Lake Pichola Waterfront',
        'locationCategory' => 'lake-pichola',
        'pricePerPlate' => 3500,
        'priceRange' => 'premium-luxury',
        'capacity' => ['min' => 20, 'max' => 400],
        'capacityCategory' => 'medium',
        'rooms' => 141,
        'acres' => null,
        'venueType' => ['Lakeside', 'Luxury Resort'],
        'amenities' => ['141 Rooms', 'Lake Pichola Views', 'Lakeside Resort Setting', 'Multiple Event Spaces', 'Gardens & Lawns', 'Poolside Venues'],
        'description' => "Trident Udaipur offers lakeside elegance with 141 rooms and flexible event spaces for 20-400 guests. Located on Lake Pichola's banks, it provides premium wedding experiences with stunning water views.",
        'images' => ['https://images.unsplash.com/photo-1712817616402-122c951e5dae?w=1200&h=900&fit=crop', 'https://images.unsplash.com/photo-1715178003372-9f790b685775?w=1200&h=900&fit=crop'],
        'packageCost' => '₹8-10 Lakhs',
        'bookingUrl' => 'https://www.tridenthotels.com/hotels-in-udaipur-lake-city-resort',
        'highlighted' => false,
    ],
    [
        'id' => 8,
        'name' => 'Wyndham Grand Udaipur',
        'slug' => 'wyndham-grand-udaipur',
        'location' => 'Fateh Sagar Lake Area',
        'locationCategory' => 'fateh-sagar',
        'pricePerPlate' => 3000,
        'priceRange' => 'premium-luxury',
        'capacity' => ['min' => 100, 'max' => 400],
        'capacityCategory' => 'medium',
        'rooms' => 140,
        'acres' => 26,
        'venueType' => ['Contemporary Luxury Hotel', 'Luxury Resort'],
        'amenities' => ['140 Rooms', '26 Acres Property', '2 Lakh Sq Ft Lawn', '10,000 Sq M Event Space', '4 Different Venues', 'Near Fateh Sagar Lake'],
        'description' => 'Wyndham Grand Udaipur sprawls across 26 acres near Fateh Sagar Lake with massive event spaces. The 140-room resort features a 2 lakh sq ft lawn and 10,000 sq m of event space, perfect for grand celebrations.',
        'images' => ['https://images.pexels.com/photos/33726143/pexels-photo-33726143.jpeg?w=1200&h=900&fit=crop', 'https://images.unsplash.com/photo-1696271026800-0959e9cbf38c?w=1200&h=900&fit=crop'],
        'packageCost' => null,
        'bookingUrl' => 'https://www.wyndhamhotels.com/wyndham-grand/udaipur-india',
        'highlighted' => false,
    ],
];

function getVenueBySlug(string $slug): ?array {
    global $fallbackVenues;

    foreach ($fallbackVenues as $venue) {
        if ($venue['slug'] === $slug) {
            return $venue;
        }
    }

    return null;
}

function filterVenues(
    string $location = 'all',
    string $budget = 'all',
    string $capacity = 'all',
    string $venueType = 'all',
    string $search = ''
): array {
    global $fallbackVenues;

    return array_values(array_filter($fallbackVenues, static function (array $venue) use ($location, $budget, $capacity, $venueType, $search): bool {
        if ($location !== 'all' && $venue['locationCategory'] !== $location) {
            return false;
        }

        if ($budget !== 'all' && $venue['priceRange'] !== $budget) {
            return false;
        }

        if ($capacity !== 'all' && $venue['capacityCategory'] !== $capacity) {
            return false;
        }

        if ($venueType !== 'all') {
            $typeMap = [
                'heritage-palace' => 'Heritage Palace',
                'lakeside' => 'Lakeside',
                'luxury-resort' => 'Luxury Resort',
                'hilltop' => 'Hilltop & Mountain View',
                'contemporary' => 'Contemporary Luxury Hotel',
                'boutique' => 'Boutique & Intimate',
            ];
            $mapped = $typeMap[$venueType] ?? $venueType;
            if (!in_array($mapped, $venue['venueType'], true)) {
                return false;
            }
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $haystack = mb_strtolower($venue['name'] . ' ' . $venue['location']);
            if (mb_strpos($haystack, $needle) === false) {
                return false;
            }
        }

        return true;
    }));
}
