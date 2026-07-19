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
        'content' => '<!-- wp:ai-zippy/home-hero /-->
<!-- wp:ai-zippy/home-class-types /-->
<!-- wp:ai-zippy/home-seasonal /-->
<!-- wp:ai-zippy/home-brands /-->
<!-- wp:ai-zippy/home-best-sellers /-->
<!-- wp:ai-zippy/home-party /-->
<!-- wp:ai-zippy/home-testimonials /-->
<!-- wp:ai-zippy/home-instagram /-->',
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
