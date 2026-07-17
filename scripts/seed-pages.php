<?php
/**
 * Achiever's Art — Seed Pages Script
 * 
 * Creates WordPress pages and assigns FSE templates.
 * Run via: docker exec {container} php /tmp/seed-pages.php
 */

if (!defined('ABSPATH')) {
    $wp_load = null;
    $candidates = [
        __DIR__ . '/../src/wp-load.php',
        __DIR__ . '/../wp-load.php',
        '/var/www/html/wp-load.php',
    ];
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            $wp_load = $path;
            break;
        }
    }
    if ($wp_load) {
        define('WP_USE_THEMES', false);
        require_once($wp_load);
    } else {
        die("Cannot find wp-load.php\n");
    }
}

$pages = [
    [
        'title'    => 'Home',
        'slug'     => 'home',
        'template' => 'page-home',
        'content'  => '<!-- wp:ai-zippy/home-hero /-->
<!-- wp:ai-zippy/home-class-types /-->
<!-- wp:ai-zippy/home-seasonal /-->
<!-- wp:ai-zippy/home-brands /-->
<!-- wp:ai-zippy/home-best-sellers /-->
<!-- wp:ai-zippy/home-party /-->
<!-- wp:ai-zippy/home-testimonials /-->
<!-- wp:ai-zippy/home-instagram /-->',
    ],
    [
        'title'    => 'Regular Art Classes',
        'slug'     => 'regular-art-classes',
        'template' => 'page-classes',
        'content'  => '<!-- wp:ai-zippy/classes-hero /-->
<!-- wp:ai-zippy/classes-detail /-->',
    ],
    [
        'title'    => 'Foundation Art Course',
        'slug'     => 'foundation-art-course',
        'template' => 'page-course',
        'content'  => '<!-- wp:ai-zippy/course-intro /-->
<!-- wp:ai-zippy/course-enquiry-form /-->',
    ],
    [
        'title'    => 'About Us',
        'slug'     => 'about-us',
        'template' => 'page-about',
        'content'  => '<!-- wp:ai-zippy/classes-hero {"heading":"About\\nUs","sectionTitle":"about us","classes":[]} /-->
<!-- wp:ai-zippy/home-brands /-->
<!-- wp:ai-zippy/classes-detail {"sections":[{"title":"Abstract Art","description":"Expressive abstract art experiences that encourage freedom of expression and creative exploration through bold colours and dynamic compositions.","image":"","layout":"text-left","ctaText":"","ctaUrl":""},{"title":"Press-On Nails","description":"Custom hand-painted press-on nail artistry combining fashion and fine art for unique wearable creations.","image":"","layout":"image-left","ctaText":"","ctaUrl":""},{"title":"Crafts","description":"Handmade charms and creative accessories workshops where participants learn to create beautiful handcrafted items.","image":"","layout":"text-left","ctaText":"","ctaUrl":""},{"title":"Young Creator","description":"Young creator and mini entrepreneurship programmes designed to nurture business skills alongside artistic development.","image":"","layout":"image-left","ctaText":"","ctaUrl":""},{"title":"Art Gallery","description":"Curated art gallery and professional art showcases providing a platform for emerging and established artists.","image":"","layout":"text-left","ctaText":"","ctaUrl":""},{"title":"Sensory Play","description":"Sensory play and messy art exploration for children, encouraging tactile learning and creative discovery through hands-on experiences.","image":"","layout":"image-left","ctaText":"","ctaUrl":""}],"galleryTitle":"","galleryImages":[]} /-->',
    ],
    [
        'title'    => 'Single-Session Art Classes',
        'slug'     => 'single-session-art-classes',
        'template' => 'page-single-session',
        'content'  => '<!-- wp:ai-zippy/classes-hero {"heading":"Single-Session\\nArt Classes","sectionTitle":"single-session art classes","classes":[{"name":"Express Art Classes","ageRange":"Age 4 & up","tagline":"Quick Creative Sessions","image":""},{"name":"Art Workshop","ageRange":"All Ages","tagline":"Guided Single-Session Projects","image":""}]} /-->
<!-- wp:ai-zippy/classes-detail {"sections":[{"title":"EXPRESS ART\\nCLASSES","description":"Perfect for busy schedules! Our Express Art Classes offer single-session creative experiences where children can dive into a fun art project and take home a completed masterpiece. No commitment required — just pure creative joy in one session.","image":"","layout":"text-left","ctaText":"SEE MORE","ctaUrl":"/express-art-classes/"},{"title":"Art Workshop","description":"Our Art Workshops are single-session guided projects designed for all ages. Each workshop focuses on a specific theme or technique, allowing participants to explore new mediums and create something beautiful in just one sitting.","image":"","layout":"image-left","ctaText":"SEE MORE","ctaUrl":"/art-workshop/"}],"galleryTitle":"your smile,\\nour passion.","galleryImages":[{"url":"","alt":"Gallery 1"},{"url":"","alt":"Gallery 2"},{"url":"","alt":"Gallery 3"},{"url":"","alt":"Gallery 4"},{"url":"","alt":"Gallery 5"},{"url":"","alt":"Gallery 6"}]} /-->
<!-- wp:ai-zippy/course-enquiry-form {"heading":"Ask us\\nquestions","programmeTypes":["Express Art Classes","Art Workshop"]} /-->',
    ],
    [
        'title'    => 'Express Art Classes',
        'slug'     => 'express-art-classes',
        'template' => 'page-express-art',
        'content'  => '<!-- wp:ai-zippy/classes-hero {"heading":"Express Art\\nClasses","sectionTitle":"express art classes","classes":[{"name":"Express Art","ageRange":"Age 4 & up","tagline":"Single-Session Fun","image":""}]} /-->
<!-- wp:ai-zippy/classes-detail {"sections":[{"title":"EXPRESS ART\\nCLASSES","description":"Our Express Art Classes are perfect for children who want a quick creative fix! Each session is a standalone project that allows young artists to explore different mediums and techniques without the commitment of a regular programme.\\n\\nFrom painting to mixed media, every class offers a unique theme and a completed artwork to take home.","image":"","layout":"text-left","ctaText":"BOOK NOW","ctaUrl":"/contact-us/"}],"galleryTitle":"creative moments","galleryImages":[{"url":"","alt":"Gallery 1"},{"url":"","alt":"Gallery 2"},{"url":"","alt":"Gallery 3"},{"url":"","alt":"Gallery 4"}]} /-->
<!-- wp:ai-zippy/course-enquiry-form {"heading":"Book a\\nSession","programmeTypes":["Express Art Classes"]} /-->',
    ],
    [
        'title'    => 'Our Camps & Courses',
        'slug'     => 'our-camps-courses',
        'template' => 'page-camps-courses',
        'content'  => '<!-- wp:ai-zippy/classes-hero {"heading":"Our Camps\\n& Courses","sectionTitle":"camps & courses","classes":[{"name":"Art Camps","ageRange":"Age 4 & up","tagline":"Holiday Fun","image":""},{"name":"Short Courses","ageRange":"Age 3 & up","tagline":"Structured Learning","image":""}]} /-->
<!-- wp:ai-zippy/home-class-types {"sectionTitle":"explore our programmes","types":[{"name":"Art Camps","format":"(4/6/8 Lessons)","icon":""},{"name":"Short Courses","format":"(Weekly)","icon":""},{"name":"Foundation Art Course","format":"(8 Lessons/Term)","icon":""}]} /-->
<!-- wp:ai-zippy/classes-detail {"sections":[{"title":"ART CAMPS","description":"Our Art Camps run during school holidays and provide an immersive creative experience over multiple days. Children explore various art forms, develop new skills, and make lasting memories with fellow young artists.","image":"","layout":"text-left","ctaText":"SEE MORE","ctaUrl":"/art-camps/"},{"title":"Short Courses","description":"Structured weekly programmes that run for a set number of lessons. Short Courses offer a focused deep-dive into specific art techniques or mediums, perfect for those who want more than a single session but less than a full-term commitment.","image":"","layout":"image-left","ctaText":"SEE MORE","ctaUrl":"/short-courses/"}],"galleryTitle":"","galleryImages":[]} /-->',
    ],
    [
        'title'    => 'Arty Events & Parties',
        'slug'     => 'arty-events-parties',
        'template' => 'page-events',
        'content'  => '<!-- wp:ai-zippy/classes-hero {"heading":"Arty Events\\n& Parties","sectionTitle":"events & parties","classes":[]} /-->
<!-- wp:ai-zippy/home-party {"preHeading":"it\u0027s","heading":"PARTY TIME!","subtitle":"Birthdays \u00b7 Group Bookings \u00b7 Corporate Events \u00b7 Team Bonding","ctaText":"ENQUIRE NOW","ctaUrl":"#enquiry"} /-->
<!-- wp:ai-zippy/course-enquiry-form {"heading":"Plan your\\nevent","subheading":"Book a party","programmeTypes":["Birthday Party","Corporate Event","Team Bonding","Group Booking"]} /-->',
    ],
    [
        'title'    => 'Contact Us',
        'slug'     => 'contact-us',
        'template' => 'page-contact',
        'content'  => '<!-- wp:ai-zippy/classes-hero {"heading":"Contact\\nUs","sectionTitle":"get in touch","classes":[]} /-->
<!-- wp:ai-zippy/contact-studios /-->
<!-- wp:ai-zippy/course-enquiry-form {"heading":"Ask us\\nquestions","subheading":"Book an appointment","programmeTypes":["Foundation Art Course","Artventurer","Canvas Wizard","Express Art Classes","Art Workshop","Art Camp","Short Course","Birthday Party","Corporate Event"]} /-->',
    ],
    [
        'title'    => 'Our Studios',
        'slug'     => 'our-studios',
        'template' => 'page-studios',
        'content'  => '<!-- wp:ai-zippy/classes-hero {"heading":"Our\\nStudios","sectionTitle":"visit us","classes":[]} /-->
<!-- wp:ai-zippy/contact-studios /-->',
    ],
    [
        'title'    => 'Shop',
        'slug'     => 'shop',
        'template' => 'page-shop',
        'content'  => '<!-- wp:ai-zippy/classes-hero {"heading":"Shop","sectionTitle":"art supplies & kits","classes":[]} /-->
<!-- wp:shortcode -->[products limit="12" columns="4"]<!-- /wp:shortcode -->',
    ],
];

foreach ($pages as $page) {
    $existing = get_page_by_path($page['slug'], OBJECT, 'page');

    $post_data = [
        'post_title'   => $page['title'],
        'post_name'    => $page['slug'],
        'post_content' => $page['content'],
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ];

    if ($existing) {
        $post_data['ID'] = $existing->ID;
        wp_update_post($post_data);
        $page_id = $existing->ID;
        echo "Updated page: {$page['title']} (ID: {$page_id})\n";
    } else {
        $page_id = wp_insert_post($post_data);
        echo "Created page: {$page['title']} (ID: {$page_id})\n";
    }

    update_post_meta($page_id, '_wp_page_template', $page['template']);
}

// Set static front page
$home = get_page_by_path('home');
if ($home) {
    update_option('page_on_front', $home->ID);
    update_option('show_on_front', 'page');
    echo "Set Home as front page.\n";
}

// Set permalink structure
global $wp_rewrite;
$wp_rewrite->set_permalink_structure('/%postname%/');
$wp_rewrite->flush_rules();
echo "Permalink structure set to /%postname%/\n";

echo "\nDone! Pages seeded successfully.\n";
