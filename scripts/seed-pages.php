<?php
/**
 * Seed the three source-designed pages and create the remaining site pages.
 * Existing content on admin-only pages is preserved.
 */

if (!defined('ABSPATH')) {
    $wp_load = '/var/www/html/wp-load.php';
    if (!file_exists($wp_load)) {
        fwrite(STDERR, "Cannot find wp-load.php\n");
        exit(1);
    }
    define('WP_USE_THEMES', false);
    require_once $wp_load;
}

$pages = [
    [
        'title' => 'Home',
        'slug' => 'home',
        'template' => 'page-home',
        'designed' => true,
        'content' => '<!-- wp:ai-zippy/home-hero {"paintJarImage":"/wp-content/uploads/2026/07/PortfolioArt-2026.png","autoplay":true,"slides":[{"heading":"REGULAR\\nART CLASSES","description":"Weekly guided lessons that build strong creative foundations through progressive skill development and exploration of different art mediums.","ctaText":"ENROL NOW","ctaUrl":"/regular-art-classes/"},{"heading":"ARTY EVENTS\\n\u0026 PARTIES","description":"Celebrate your birthday or host a memorable corporate art bonding session with our customized activities.","ctaText":"BOOK NOW","ctaUrl":"/arty-events-parties/"},{"heading":"CAMPS\\n\u0026 COURSES","description":"Holiday camps and short courses designed to spark imagination and build new skills in a fun setting.","ctaText":"EXPLORE NOW","ctaUrl":"/camps-courses/"}]} /-->
<!-- wp:ai-zippy/home-class-types {"sectionTitle":"EXPLORE OUR CLASSES","types":[{"label":"Regular Art Classes","subtitle":"(Weekly)","image":"/wp-content/uploads/2026/07/download-1.jpg","alt":"Regular Art Classes","url":"/regular-art-classes/"},{"label":"Art Camps","subtitle":"(4/6/8 Lessons)","image":"/wp-content/uploads/2026/07/Regular-Classes_Main-Q.jpg","alt":"Art Camps","url":"/camps-courses/"},{"label":"Art Workshop","subtitle":"(Single Session)","image":"/wp-content/uploads/2026/07/ShortCourse_mainQ.jpg","alt":"Art Workshop","url":"/single-session-art-classes/"},{"label":"Short Courses","subtitle":"(Weekly)","image":"/wp-content/uploads/2026/07/Aarts_White-Studen-Shirt_Sleeve.jpg","alt":"Short Courses","url":"/camps-courses/#short-courses"},{"label":"Express Art Classes","subtitle":"(Single Session)","image":"/wp-content/uploads/2026/07/Artventurer-2025.jpg","alt":"Express Art Classes","url":"/express-art-classes/"}]} /-->
<!-- wp:ai-zippy/home-seasonal {"sectionTitle":"SEASONAL SPECIAL","decorImage":"/wp-content/uploads/2026/07/download-3.png","ctaText":"VIEW MORE & REGISTER","ctaUrl":"/camps-courses/","products":[{"name":"Name of Product","category":"Category","ageTime":"Age + Time Workshop","image":"/wp-content/uploads/2026/07/FBPastel-1.png","alt":"Seasonal product 1","url":"#"},{"name":"Name of Product","category":"Category","ageTime":"Age + Time Workshop","image":"/wp-content/uploads/2026/07/MangaDrawing2-1.png","alt":"Seasonal product 2","url":"#"},{"name":"Name of Product","category":"Category","ageTime":"Age + Time Workshop","image":"/wp-content/uploads/2026/07/Watercolour-1.png","alt":"Seasonal product 3","url":"#"},{"name":"Name of Product","category":"Category","ageTime":"Age + Time Workshop","image":"/wp-content/uploads/2026/07/HolidayCamp-2025.png","alt":"Seasonal product 4","url":"#"}]} /-->
<!-- wp:ai-zippy/home-brands {"heading":"BEYOND\nTHE CANVAS","decorImage":"/wp-content/uploads/2026/07/download-4.png","ctaText":"READ MORE","ctaUrl":"/about-us/","brands":[{"name":"Art Studio","icon":"/wp-content/uploads/2026/07/AartsIcon-scaled.png","alt":"Art Studio"},{"name":"Art Gallery","icon":"/wp-content/uploads/2026/07/2402AALogo_Black-w.avif","alt":"Art Gallery"},{"name":"Sensory Play","icon":"/wp-content/uploads/2026/07/Sensory-Playhaus_COLOR-scaled.png","alt":"Sensory Play"},{"name":"Press-on Nails","icon":"/wp-content/uploads/2026/07/YanailsAtelier-Logo_sparkle.png","alt":"Press-on Nails"},{"name":"Art Activity","icon":"/wp-content/uploads/2026/07/2402AAlogo_Sticker_White-scaled.png","alt":"Art Activity"},{"name":"Young Entrepreneurship","icon":"/wp-content/uploads/2026/07/Sensory-Playhaus_STICKER-scaled.png","alt":"Young Entrepreneurship"}],"galleryImages":[{"url":"/wp-content/uploads/2026/07/Regular-Classes_Main-Q.jpg","alt":"Gallery 1"},{"url":"/wp-content/uploads/2026/07/ShortCourse_mainQ.jpg","alt":"Gallery 2"},{"url":"/wp-content/uploads/2026/07/Aarts_White-Studen-Shirt_Sleeve.jpg","alt":"Gallery 3"},{"url":"/wp-content/uploads/2026/07/ArtWorkshop-Card.jpg","alt":"Gallery 4"}]} /-->
<!-- wp:ai-zippy/home-best-sellers {"sectionTitle":"OUR BEST SELLERS","ctaText":"VIEW MORE & REGISTER","ctaUrl":"/shop/","products":[{"name":"Name of Product","category":"Category","ageTime":"Age + Time Workshop","image":"/wp-content/uploads/2026/07/FBPastel-1.png","alt":"Bestseller product 1","url":"#"},{"name":"Name of Product","category":"Category","ageTime":"Age + Time Workshop","image":"/wp-content/uploads/2026/07/MangaDrawing2-1.png","alt":"Bestseller product 2","url":"#"},{"name":"Name of Product","category":"Category","ageTime":"Age + Time Workshop","image":"/wp-content/uploads/2026/07/Watercolour-1.png","alt":"Bestseller product 3","url":"#"},{"name":"Name of Product","category":"Category","ageTime":"Age + Time Workshop","image":"/wp-content/uploads/2026/07/HolidayCamp-2025.png","alt":"Bestseller product 4","url":"#"}]} /-->
<!-- wp:ai-zippy/home-party {"preHeading":"IT\u0027S","heading":"PARTY TIME!","subtitle":"BIRTHDAYS \u00b7 GROUP BOOKINGS \u00b7 CORPORATE EVENTS \u00b7 TEAM BONDING","decorLeftImage":"/wp-content/uploads/2026/07/download-4.png","decorRightImage":"/wp-content/uploads/2026/07/download-3.png","ctaText":"ENQUIRE NOW","ctaUrl":"/arty-events-parties/","images":[{"url":"/wp-content/uploads/2026/07/Regular-Classes_Main-Q.jpg","alt":"Party 1"},{"url":"/wp-content/uploads/2026/07/Aarts_White-Studen-Shirt_Sleeve.jpg","alt":"Party 2"},{"url":"/wp-content/uploads/2026/07/ShortCourse_mainQ.jpg","alt":"Party 3"},{"url":"/wp-content/uploads/2026/07/download-1.jpg","alt":"Party 4"}]} /-->
<!-- wp:ai-zippy/home-testimonials /-->
<!-- wp:ai-zippy/home-instagram {"heading":"FOLLOW US ON INSTAGRAM","images":[{"url":"/wp-content/uploads/2026/07/FBPastel-scaled.png","alt":"Instagram photo 1","link":"https://instagram.com"},{"url":"/wp-content/uploads/2026/07/InksCalligraphy.png","alt":"Instagram photo 2","link":"https://instagram.com"},{"url":"/wp-content/uploads/2026/07/Watercolour-scaled.png","alt":"Instagram photo 3","link":"https://instagram.com"},{"url":"/wp-content/uploads/2026/07/MangaDrawing2-scaled.png","alt":"Instagram photo 4","link":"https://instagram.com"}]} /-->',
    ],
    [
        'title' => 'Regular Art Classes',
        'slug' => 'regular-art-classes',
        'template' => 'page-classes',
        'designed' => true,
        'content' => '<!-- wp:ai-zippy/classes-hero /-->
<!-- wp:ai-zippy/classes-detail /-->',
    ],
    [
        'title' => 'Foundation Art Course',
        'slug' => 'foundation-art-course',
        'template' => 'page-course',
        'designed' => true,
        'content' => '<!-- wp:ai-zippy/course-intro /-->
<!-- wp:ai-zippy/course-enquiry-form /-->',
    ],
    [
        'title' => 'Book a Class',
        'slug' => 'booking',
        'template' => 'page-booking',
        'designed' => true,
        'content' => '<!-- wp:ai-zippy/booking-calendar /-->',
    ],
    ['title' => 'About Us', 'slug' => 'about-us', 'template' => 'page-about', 'designed' => false],
    ['title' => 'Single-Session Art Classes', 'slug' => 'single-session-art-classes', 'template' => 'page-single-session', 'designed' => false],
    ['title' => 'Express Art Classes', 'slug' => 'express-art-classes', 'template' => 'page-express-art', 'designed' => false],
    ['title' => 'Our Camps & Courses', 'slug' => 'camps-courses', 'template' => 'page-camps-courses', 'designed' => false],
    ['title' => 'Arty Events & Parties', 'slug' => 'arty-events-parties', 'template' => 'page-events', 'designed' => false],
    ['title' => 'Contact Us', 'slug' => 'contact-us', 'template' => 'page-contact', 'designed' => false],
    ['title' => 'Our Studios', 'slug' => 'our-studios', 'template' => 'page-studios', 'designed' => false],
    ['title' => 'Shop', 'slug' => 'shop', 'template' => 'page-shop', 'designed' => false],
];

foreach ($pages as $page) {
    $existing = get_page_by_path($page['slug'], OBJECT, 'page');
    $post_data = [
        'post_title' => $page['title'],
        'post_name' => $page['slug'],
        'post_status' => 'publish',
        'post_type' => 'page',
    ];

    if ($page['designed']) {
        $post_data['post_content'] = $page['content'];
    } elseif (!$existing) {
        $post_data['post_content'] = '';
    }

    if ($existing) {
        $post_data['ID'] = $existing->ID;
        $page_id = wp_update_post($post_data, true);
    } else {
        $page_id = wp_insert_post($post_data, true);
    }

    if (is_wp_error($page_id)) {
        fwrite(STDERR, 'Page failed: ' . $page['title'] . ' - ' . $page_id->get_error_message() . "\n");
        continue;
    }

    update_post_meta($page_id, '_wp_page_template', $page['template']);
    echo ($existing ? 'Updated: ' : 'Created: ') . $page['title'] . "\n";
}

$home = get_page_by_path('home', OBJECT, 'page');
if ($home) {
    update_option('page_on_front', $home->ID);
    update_option('show_on_front', 'page');
}

global $wp_rewrite;
$wp_rewrite->set_permalink_structure('/%postname%/');
$wp_rewrite->flush_rules();

echo "Page setup complete.\n";
