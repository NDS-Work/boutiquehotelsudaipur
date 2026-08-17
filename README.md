# Udaipur Wedding Venues - PHP + Bootstrap 5

## 📁 Project Structure

```
/app/backend/public/
├── index.php              # Homepage
├── venues.php             # All venues listing with filters
├── venue-detail.php       # Single venue detail page
├── about.php              # About page
├── contact.php            # Contact form page
├── includes/
│   ├── header.php         # Navigation header
│   └── footer.php         # Footer
├── data/
│   └── venues.php         # Venue data array (8 venues)
└── assets/
    └── css/
        └── style.css      # Custom Pixel Pushers CSS
```

## 🚀 How to Run

The PHP server is already running on **http://localhost:3000**

To restart the server manually:

```bash
cd /app/backend/public
php -S 0.0.0.0:3000
```

## 📄 File Descriptions

### Main Pages

**index.php** - Homepage featuring:

- Hero section with background image
- Browse by categories (Location, Budget, Capacity, Venue Type)
- Featured venues grid (4 venues)
- Why Udaipur section
- CTA section

**venues.php** - All Venues page with:

- Sticky filter sidebar (search, location, budget, capacity, venue type)
- Venue grid with cards
- View Details and Book buttons
- Shows filtered count

**venue-detail.php** - Individual venue page:

- Image gallery
- Full venue description
- Key details (accommodation, capacity, pricing, property size)
- Amenities list
- Venue style tags
- Booking sidebar with CTA buttons

**about.php** - About page:

- Mission cards
- Why Choose Udaipur sections
- How to use directory guide

**contact.php** - Contact page:

- Contact form (name, email, phone, venue interest, message)
- Contact information display
- Office hours
- Form submission handling

### Includes

**includes/header.php** - Navigation:

- Fixed top navbar
- Responsive hamburger menu
- Active page highlighting
- Bootstrap 5 navbar

**includes/footer.php** - Footer:

- 4 column layout
- Browse Venues links
- Resources links
- Contact information
- Copyright and bottom links

### Data

**data/venues.php** - Contains:

- `$venues` array with 8 complete venue objects
- `getVenueBySlug()` function
- `filterVenues()` function
- All venue data (images, pricing, capacity, amenities, etc.)

### Assets

**assets/css/style.css** - Custom CSS:

- Pixel Pushers color variables
- Navigation styles
- Button styles (pill-shaped)
- Typography classes
- Card styles
- Hero section styles
- Form control styles
- Responsive breakpoints

## 🎨 Design System

### Colors

- Background: `#1a1c1b` (Black)
- Card Background: `#302f2c` (Dark gray)
- Primary: `#d9fb06` (Lime green)
- Text: `#dfddd6` (Light gray)
- Secondary Text: `#888680` (Mid gray)
- Borders: `#3f4816` (Olive green)

### Typography

- Font: Inter (Google Fonts)
- Weights: 500, 600, 700, 900

### Components

- Pill-shaped buttons (border-radius: 50px)
- Bootstrap 5.3.3 framework
- Hover effects and transitions
- Responsive grid layout

## 📊 Venue Data

Currently includes 8 venues:

1. Amantra Shilpi Resort & Spa - ₹950/plate (Featured)
2. The Oberoi Udaivilas - ₹8,000/plate (Featured)
3. Taj Lake Palace - ₹12,500/plate (Featured)
4. Radisson Blu Palace Resort - ₹4,500/plate (Featured)
5. RAAS Devigarh - ₹4,200/plate
6. The Leela Palace - ₹8,500/plate
7. Trident Udaipur - ₹3,500/plate
8. Wyndham Grand Udaipur - ₹3,000/plate

## 🔧 Customization

### Adding More Venues

Edit `/app/backend/public/data/venues.php` and add venue objects to the `$venues` array following the existing structure.

### Changing Colors

Edit `/app/backend/public/assets/css/style.css` and update the CSS variables in the `:root` selector.

### Modifying Navigation

Edit `/app/backend/public/includes/header.php` to add/remove menu items.

## 📝 Notes

- No database required - all data is in PHP arrays
- No JavaScript frameworks - pure PHP + Bootstrap
- Fully responsive design
- Direct booking links to actual venue websites
- Contact form uses POST method (add email sending logic as needed)

## 🌐 Live URLs

- Homepage: http://localhost:3000
- All Venues: http://localhost:3000/venues.php
- About: http://localhost:3000/about.php
- Contact: http://localhost:3000/contact.php
- Venue Detail Example: http://localhost:3000/venue-detail.php?slug=amantra-shilpi-resort

---

**Built with PHP 8.2.29 + Bootstrap 5.3.3**

## Importing data/new.sql into this project

This repository includes a one-time SQL importer script:

- `import-new-sql.php`

It uses the existing database connection defined in `db.php` and imports `data/new.sql`.

Run from terminal:

```bash
php import-new-sql.php
```

Or run from browser:

```text
http://localhost:3000/import-new-sql.php?run=1
```

After successful import, delete `import-new-sql.php` from production.
