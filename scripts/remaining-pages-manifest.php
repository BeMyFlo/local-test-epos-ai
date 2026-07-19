<?php

defined('ABSPATH') || exit;

$pages = [
    'about-us' => [
        'title' => 'About Us',
        'template' => 'page-about',
        'existing_empty_only' => true,
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"ABOUT US","subtitle":"","breadcrumbCurrent":"About Us","variant":"pink"} /-->
<!-- wp:ai-zippy/about-content /-->
<!-- wp:ai-zippy/course-enquiry-form {"heading":"Ask us questions","subheading":"Book an appointment","submitText":"SEND ENQUIRY"} /-->
BLOCKS,
    ],
    'single-session-art-classes' => [
        'title' => 'Single-Session Art Classes',
        'template' => 'page-single-session',
        'existing_empty_only' => true,
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"SINGLE-SESSION ART CLASSES","subtitle":"","breadcrumbCurrent":"Single-Session Art Classes","variant":"blue"} /-->
<!-- wp:ai-zippy/offering-listing {"heading":"Our Classes","layout":"grid","items":[{"title":"Express Art","age":"Ages 4 & up","tagline":"Quick, Creative, and Incredibly Satisfying","description":"Our Express Art workshops are fast-paced, 1-hour creative sessions designed for anyone who wants to enjoy the joy of creating without a long commitment. Perfect for both kids and adults seeking a quick and fulfilling art experience.","features":["Choose from a curated selection of artworks","All materials fully provided","Complete your artwork in just 60 minutes","Beginner-friendly — no experience needed","Relaxed, fun, and stress-free environment"],"image":"/wp-content/uploads/2026/07/ExpressArt-Card.jpg","alt":"Express Art","ctaText":"See More","ctaUrl":"/express-art-classes/"},{"title":"Art Workshop","age":"Ages 4 & up","tagline":"Themed Creative Experiences for Fun Exploration","description":"Our Art Workshops are immersive, theme-based sessions that let students explore different artistic styles, materials, and techniques in a relaxed and enjoyable environment. Great for creative discovery and special occasions.","features":["Fun, themed art projects & creative experiences","Explore a variety of mediums and techniques","Perfect for parent-child bonding or group participation","All materials fully provided","Beginner-friendly and highly engaging"],"image":"/wp-content/uploads/2026/07/ArtWorkshop-Card.jpg","alt":"Art Workshop","ctaText":"See More","ctaUrl":"/art-workshops/"}]} /-->
BLOCKS,
    ],
    'express-art-classes' => [
        'title' => 'Express Art Classes',
        'template' => 'page-express-art',
        'existing_empty_only' => true,
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"EXPRESS ART","subtitle":"","breadcrumbCurrent":"Express Art","variant":"yellow"} /-->
<!-- wp:ai-zippy/service-detail {"serviceType":"Express Art","heading":"Express Art","ageRange":"Ages 4 & up","description":"Quick, Creative, and Oh-So-Satisfying\n\nDiscover our Express Art workshops — quick, inspiring 1-hour art sessions perfect for busy kids and adults who want to create something beautiful without the long commitment.","mainImage":"/wp-content/uploads/2026/07/ExpressArt-Card.jpg","mainImageAlt":"Express Art","infoTitle":"","infoItems":[{"text":"Choose Your Artwork from our gallery"},{"text":"All materials provided"},{"text":"Complete artwork in just 60 minutes!"},{"text":"Beginner-friendly — no experience needed"},{"text":"Fun & stress-free atmosphere"}],"ctaText":"Book Now","ctaUrl":"/contact-us/","relatedTitle":"","relatedItems":[],"galleryTitle":"","galleryImages":[]} /-->
<!-- wp:ai-zippy/offering-listing {"heading":"Express Art Gallery","layout":"carousel","items":[]} /-->
<!-- wp:ai-zippy/course-enquiry-form {"heading":"Ask us questions","subheading":"Book an appointment","programmeOptions":["Express Art"],"selectedProgramme":"Express Art","submitText":"SEND ENQUIRY"} /-->
BLOCKS,
    ],
    'camps-courses' => [
        'title' => 'Our Camps & Courses',
        'template' => 'page-camps-courses',
        'existing_empty_only' => true,
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"OUR CAMPS & COURSES","subtitle":"","breadcrumbCurrent":"Our Camps & Courses","variant":"blue"} /-->
<!-- wp:ai-zippy/offering-listing {"heading":"Our Classes","layout":"grid","items":[{"title":"Camps","age":"Ages 4 & up","tagline":"Creative Holiday Adventures Filled with Fun & Exploration","description":"Our Art Camps are exciting multi-day creative programmes designed to keep children engaged through hands-on art projects, themed activities, and interactive experiences during the school holidays. Perfect for young artists who love to create, explore, and make new friends.","features":["Fun-filled holiday art experiences","Explore different mediums, crafts & creative techniques","Engaging themed activities & guided projects","All materials fully provided","Beginner-friendly and highly interactive"],"image":"","alt":"","ctaText":"See More","ctaUrl":"/our-camps/"},{"title":"Short Courses","age":"Ages 4 & up","tagline":"Focused Creative Learning in a Flexible Format","description":"Our Short Courses are specially designed programmes that allow students to explore and develop specific art skills over a shorter commitment period. Perfect for learners who want a structured yet flexible creative learning experience.","features":["Learn focused art skills through guided lessons","Explore painting, sketching, crafts & mixed media","Structured learning in a shorter course format","All materials fully provided","Suitable for beginners and growing young artists"],"image":"","alt":"","ctaText":"See More","ctaUrl":"/short-courses/"}]} /-->
BLOCKS,
    ],
    'our-camps' => [
        'title' => 'Our Camps',
        'template' => 'page-listing',
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"OUR CAMPS","subtitle":"","breadcrumbCurrent":"Our Camps","variant":"yellow"} /-->
<!-- wp:ai-zippy/offering-listing {"heading":"OUR FUN CAMPS!","layout":"carousel","items":[{"title":"Arts","image":"/wp-content/uploads/2026/07/ArtCamp-2025.png","alt":"Arts Camp","ctaText":"See More","ctaUrl":"/arts-camp/"},{"title":"Crafts","image":"/wp-content/uploads/2026/07/CraftCamp-2025.png","alt":"Crafts Camp","ctaText":"See More","ctaUrl":"/crafts-camp/"},{"title":"Holiday Camp","image":"/wp-content/uploads/2026/07/HolidayCamp-2025.png","alt":"Holiday Camp","ctaText":"See More","ctaUrl":"/holiday-camp/"},{"title":"Artivity","image":"/wp-content/uploads/2026/07/ArtivityCamp.png","alt":"Artivity Camp","ctaText":"See More","ctaUrl":"/artivity-camp/"}]} /-->
<!-- wp:ai-zippy/offering-listing {"heading":"Camp Offerings","layout":"grid","items":[{"title":"Arts","image":"/wp-content/uploads/2026/07/ArtCamp-2025.png","alt":"Arts Camp","ctaText":"See More","ctaUrl":"/arts-camp/"},{"title":"Crafts","image":"/wp-content/uploads/2026/07/CraftCamp-2025.png","alt":"Crafts Camp","ctaText":"See More","ctaUrl":"/crafts-camp/"},{"title":"Holiday Camp","image":"/wp-content/uploads/2026/07/HolidayCamp-2025.png","alt":"Holiday Camp","ctaText":"See More","ctaUrl":"/holiday-camp/"},{"title":"Artivity","image":"/wp-content/uploads/2026/07/ArtivityCamp.png","alt":"Artivity Camp","ctaText":"See More","ctaUrl":"/artivity-camp/"}]} /-->
BLOCKS,
    ],
    'arts-camp' => [
        'title' => 'Arts Camp',
        'template' => 'page-service-detail',
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"ARTS","subtitle":"","breadcrumbCurrent":"Arts","variant":"pink"} /-->
<!-- wp:ai-zippy/service-detail {"serviceType":"Camp","heading":"Arts","ageRange":"","description":"","mainImage":"/wp-content/uploads/2026/07/ArtCamp-2025.png","mainImageAlt":"Arts Camp","infoTitle":"Programme Details","infoItems":[],"ctaText":"Enquire Now","ctaUrl":"#course-enquiry","relatedTitle":"Other Camps","relatedItems":[{"title":"Crafts","url":"/crafts-camp/","image":"/wp-content/uploads/2026/07/CraftCamp-2025.png","alt":"Crafts Camp"},{"title":"Holiday Camp","url":"/holiday-camp/","image":"/wp-content/uploads/2026/07/HolidayCamp-2025.png","alt":"Holiday Camp"},{"title":"Artivity","url":"/artivity-camp/","image":"/wp-content/uploads/2026/07/ArtivityCamp.png","alt":"Artivity Camp"}],"galleryTitle":"Your Smile, Our Passion","galleryImages":[]} /-->
<!-- wp:ai-zippy/course-enquiry-form {"anchor":"course-enquiry","programmeOptions":["Arts","Crafts","Holiday Camp","Artivity"],"selectedProgramme":"Arts"} /-->
BLOCKS,
    ],
    'crafts-camp' => [
        'title' => 'Crafts Camp',
        'template' => 'page-service-detail',
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"CRAFTS","subtitle":"","breadcrumbCurrent":"Crafts","variant":"blue"} /-->
<!-- wp:ai-zippy/service-detail {"serviceType":"Camp","heading":"Crafts","ageRange":"","description":"","mainImage":"/wp-content/uploads/2026/07/CraftCamp-2025.png","mainImageAlt":"Crafts Camp","infoTitle":"Programme Details","infoItems":[],"ctaText":"Enquire Now","ctaUrl":"#course-enquiry","relatedTitle":"Other Camps","relatedItems":[{"title":"Arts","url":"/arts-camp/","image":"/wp-content/uploads/2026/07/ArtCamp-2025.png","alt":"Arts Camp"},{"title":"Holiday Camp","url":"/holiday-camp/","image":"/wp-content/uploads/2026/07/HolidayCamp-2025.png","alt":"Holiday Camp"},{"title":"Artivity","url":"/artivity-camp/","image":"/wp-content/uploads/2026/07/ArtivityCamp.png","alt":"Artivity Camp"}],"galleryTitle":"Your Smile, Our Passion","galleryImages":[]} /-->
<!-- wp:ai-zippy/course-enquiry-form {"anchor":"course-enquiry","programmeOptions":["Arts","Crafts","Holiday Camp","Artivity"],"selectedProgramme":"Crafts"} /-->
BLOCKS,
    ],
    'holiday-camp' => [
        'title' => 'Holiday Camp',
        'template' => 'page-service-detail',
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"HOLIDAY CAMP","subtitle":"","breadcrumbCurrent":"Holiday Camp","variant":"yellow"} /-->
<!-- wp:ai-zippy/service-detail {"serviceType":"Camp","heading":"Holiday Camp","ageRange":"","description":"","mainImage":"/wp-content/uploads/2026/07/HolidayCamp-2025.png","mainImageAlt":"Holiday Camp","infoTitle":"Programme Details","infoItems":[],"ctaText":"Enquire Now","ctaUrl":"#course-enquiry","relatedTitle":"Other Camps","relatedItems":[{"title":"Arts","url":"/arts-camp/","image":"/wp-content/uploads/2026/07/ArtCamp-2025.png","alt":"Arts Camp"},{"title":"Crafts","url":"/crafts-camp/","image":"/wp-content/uploads/2026/07/CraftCamp-2025.png","alt":"Crafts Camp"},{"title":"Artivity","url":"/artivity-camp/","image":"/wp-content/uploads/2026/07/ArtivityCamp.png","alt":"Artivity Camp"}],"galleryTitle":"Your Smile, Our Passion","galleryImages":[]} /-->
<!-- wp:ai-zippy/course-enquiry-form {"anchor":"course-enquiry","programmeOptions":["Arts","Crafts","Holiday Camp","Artivity"],"selectedProgramme":"Holiday Camp"} /-->
BLOCKS,
    ],
    'artivity-camp' => [
        'title' => 'Artivity Camp',
        'template' => 'page-service-detail',
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"ARTIVITY","subtitle":"","breadcrumbCurrent":"Artivity","variant":"lavender"} /-->
<!-- wp:ai-zippy/service-detail {"serviceType":"Camp","heading":"Artivity","ageRange":"","description":"","mainImage":"/wp-content/uploads/2026/07/ArtivityCamp.png","mainImageAlt":"Artivity Camp","infoTitle":"Programme Details","infoItems":[],"ctaText":"Enquire Now","ctaUrl":"#course-enquiry","relatedTitle":"Other Camps","relatedItems":[{"title":"Arts","url":"/arts-camp/","image":"/wp-content/uploads/2026/07/ArtCamp-2025.png","alt":"Arts Camp"},{"title":"Crafts","url":"/crafts-camp/","image":"/wp-content/uploads/2026/07/CraftCamp-2025.png","alt":"Crafts Camp"},{"title":"Holiday Camp","url":"/holiday-camp/","image":"/wp-content/uploads/2026/07/HolidayCamp-2025.png","alt":"Holiday Camp"}],"galleryTitle":"Your Smile, Our Passion","galleryImages":[]} /-->
<!-- wp:ai-zippy/course-enquiry-form {"anchor":"course-enquiry","programmeOptions":["Arts","Crafts","Holiday Camp","Artivity"],"selectedProgramme":"Artivity"} /-->
BLOCKS,
    ],
    'short-courses' => [
        'title' => 'Short Courses',
        'template' => 'page-listing',
        'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"SHORT COURSES","subtitle":"","breadcrumbCurrent":"Short Courses","variant":"blue"} /-->
<!-- wp:ai-zippy/offering-listing {"heading":"OUR SHORT COURSES","layout":"carousel","items":[{"title":"Foundation Art","age":"Age 3–5","image":"/wp-content/uploads/2026/07/FoundationArt-5.png","alt":"Foundation Art","ctaText":"See More","ctaUrl":"/foundation-art-course/"},{"title":"Manga Drawing","image":"/wp-content/uploads/2026/07/MangaDrawing2.png","alt":"Manga Drawing","ctaText":"See More","ctaUrl":"/manga-drawing/"},{"title":"Water Colour","image":"/wp-content/uploads/2026/07/Watercolour.png","alt":"Water Colour","ctaText":"See More","ctaUrl":"/watercolour/"},{"title":"Digital Art","image":"/wp-content/uploads/2026/07/DigitalArt3.png","alt":"Digital Art","ctaText":"See More","ctaUrl":"/digital-art/"},{"title":"Fashion Illustration","image":"/wp-content/uploads/2026/07/FashionIllustration.jpg","alt":"Fashion Illustration","ctaText":"See More","ctaUrl":"/fashion-illustration/"}]} /-->
<!-- wp:ai-zippy/offering-listing {"heading":"Course Offerings","layout":"grid","items":[{"title":"Foundation Art","age":"Age 3–5","image":"/wp-content/uploads/2026/07/FoundationArt-5.png","alt":"Foundation Art","ctaText":"See More","ctaUrl":"/foundation-art-course/"},{"title":"Manga Drawing","image":"/wp-content/uploads/2026/07/MangaDrawing2.png","alt":"Manga Drawing","ctaText":"See More","ctaUrl":"/manga-drawing/"},{"title":"Water Colour","image":"/wp-content/uploads/2026/07/Watercolour.png","alt":"Water Colour","ctaText":"See More","ctaUrl":"/watercolour/"},{"title":"Digital Art","image":"/wp-content/uploads/2026/07/DigitalArt3.png","alt":"Digital Art","ctaText":"See More","ctaUrl":"/digital-art/"},{"title":"Fashion Illustration","image":"/wp-content/uploads/2026/07/FashionIllustration.jpg","alt":"Fashion Illustration","ctaText":"See More","ctaUrl":"/fashion-illustration/"}]} /-->
BLOCKS,
    ],
];

$regular_classes = [
    'artventurer' => ['title' => 'Artventurer', 'age' => 'Age 3 & up', 'description' => 'Explore the world of Arts', 'image' => '/wp-content/uploads/2026/07/Artventurer-2025.jpg'],
    'canvas-wizard' => ['title' => 'Canvas Wizard', 'age' => 'Age 6 & up', 'description' => 'For the Painters', 'image' => '/wp-content/uploads/2026/07/CanvasWizard-2026.jpg'],
    'foundation-art-course' => ['title' => 'Foundation Art Class', 'age' => 'Age 4–6', 'description' => 'For Strong Art Beginnings', 'existing' => true, 'image' => '/wp-content/uploads/2026/07/FoundationArt-5.png'],
    'sketcher-master' => ['title' => 'Sketcher Master', 'age' => 'Age 7 & up', 'description' => 'For the Sketch Enthusiast', 'image' => '/wp-content/uploads/2026/07/SketcherMaster-2026.jpg'],
    'little-draws' => ['title' => 'Little Draws', 'age' => 'Age 5 & up', 'description' => 'Joyful Little Draws', 'image' => '/wp-content/uploads/2026/07/LittleDraws-2026.jpg'],
    'junior-fine-arts' => ['title' => 'Junior Fine Arts', 'age' => 'Age 8 & up', 'description' => 'For the Prodigy', 'image' => '/wp-content/uploads/2026/07/JuniorFineArts-2026.jpg'],
    'drawvinci' => ['title' => 'DrawVinci', 'age' => 'Age 6 & up', 'description' => 'The Art of Drawing', 'image' => '/wp-content/uploads/2026/07/DrawVinci.png'],
    'portfolio-art' => ['title' => 'Portfolio Art', 'age' => 'Age 10 & up', 'description' => 'For the Future Artist', 'image' => '/wp-content/uploads/2026/07/PortfolioArt-2027.jpg'],
];

foreach ($regular_classes as $slug => $class) {
    if (!empty($class['existing'])) {
        continue;
    }

    $related = [];
    foreach ($regular_classes as $related_slug => $related_class) {
        if ($related_slug === $slug) {
            continue;
        }
        $related[] = [
            'title' => $related_class['title'],
            'url' => '/' . $related_slug . '/',
            'image' => !empty($related_class['image']) ? home_url($related_class['image']) : '',
            'alt' => $related_class['title'],
        ];
    }

    $hero_attributes = [
        'eyebrow' => '',
        'heading' => strtoupper($class['title']),
        'subtitle' => '',
        'breadcrumbCurrent' => $class['title'],
        'variant' => 'pink',
    ];
    $detail_attributes = [
        'serviceType' => 'Regular Art Class',
        'heading' => $class['title'],
        'ageRange' => $class['age'],
        'description' => $class['description'],
        'mainImage' => !empty($class['image']) ? home_url($class['image']) : '',
        'mainImageAlt' => $class['title'],
        'infoTitle' => 'Lesson Information',
        'infoItems' => [],
        'ctaText' => 'Enquire Now',
        'ctaUrl' => '#course-enquiry',
        'relatedTitle' => 'Other Classes',
        'relatedItems' => $related,
        'galleryTitle' => 'Your Smile, Our Passion',
        'galleryImages' => [],
    ];
    $form_attributes = [
        'anchor' => 'course-enquiry',
        'programmeOptions' => array_values(array_column($regular_classes, 'title')),
        'selectedProgramme' => $class['title'],
    ];

    $pages[$slug] = [
        'title' => $class['title'],
        'template' => 'page-service-detail',
        'content' => '<!-- wp:ai-zippy/page-hero ' . json_encode($hero_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ' /-->' . "\n"
            . '<!-- wp:ai-zippy/service-detail ' . json_encode($detail_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ' /-->' . "\n"
            . '<!-- wp:ai-zippy/course-enquiry-form ' . json_encode($form_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ' /-->',
    ];
}

$pages['arty-events-parties'] = [
    'title' => 'Arty Events & Parties',
    'template' => 'page-events',
    'existing_empty_only' => true,
    'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"ARTY EVENTS & PARTIES","subtitle":"","breadcrumbCurrent":"Arty Events & Parties","variant":"pink"} /-->
<!-- wp:ai-zippy/events-content /-->
<!-- wp:ai-zippy/course-enquiry-form {"anchor":"course-enquiry","heading":"Ask us questions","subheading":"Book an appointment","programmeOptions":["Arty Time","Private Parties / Events"],"submitText":"SEND ENQUIRY"} /-->
BLOCKS,
];

$pages['art-workshops'] = [
    'title' => 'Art Workshops',
    'template' => 'page-listing',
    'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"ART WORKSHOPS","subtitle":"","breadcrumbCurrent":"Art Workshops","variant":"yellow"} /-->
<!-- wp:ai-zippy/woo-product-listing {"heading":"Workshops","categoriesTitle":"Categories","allCategoriesText":"All Workshops","emptyMessage":"Workshop products will appear here once they are published.","productsPerPage":12} /-->
BLOCKS,
];

$pages['shop'] = [
    'title' => 'Shop',
    'template' => 'page-shop',
    'existing_empty_only' => true,
    'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"SHOP","subtitle":"","breadcrumbCurrent":"Shop","variant":"blue"} /-->
<!-- wp:ai-zippy/woo-product-listing {"heading":"Shop","categoriesTitle":"Categories","allCategoriesText":"All Products","emptyMessage":"Products will appear here once WooCommerce products are published.","productsPerPage":12} /-->
BLOCKS,
];

$workshops = [
    'express-art-classes' => ['title' => 'Express Art', 'existing' => true],
    'acrylic-painting' => ['title' => 'Acrylic Painting', 'image' => '/wp-content/uploads/2026/07/AcrylicPainting.jpg'],
    'inks-calligraphy' => ['title' => 'Inks & Calligraphy', 'image' => '/wp-content/uploads/2026/07/InksCalligraphy.png'],
    'clay-artivity' => ['title' => 'Clay x Artivity'],
    'crafts-artivity' => ['title' => 'Crafts x Artivity'],
    'digital-art' => ['title' => 'Digital Art', 'image' => '/wp-content/uploads/2026/07/DigitalArt3.png'],
    'dry-medium-sketching' => ['title' => 'Dry Medium Sketching'],
    'fashion-illustration' => ['title' => 'Fashion Illustration', 'image' => '/wp-content/uploads/2026/07/FashionIllustration.jpg'],
    'manga-drawing' => ['title' => 'Manga Drawing', 'image' => '/wp-content/uploads/2026/07/MangaDrawing2.png'],
    'watercolour' => ['title' => 'Watercolour', 'image' => '/wp-content/uploads/2026/07/Watercolour.png'],
];

foreach ($workshops as $slug => $workshop) {
    if (!empty($workshop['existing'])) {
        continue;
    }

    $related = [];
    foreach ($workshops as $related_slug => $related_workshop) {
        if ($related_slug === $slug) {
            continue;
        }
        $related[] = [
            'title' => $related_workshop['title'],
            'url' => '/' . $related_slug . '/',
            'image' => !empty($related_workshop['image']) ? home_url($related_workshop['image']) : '',
            'alt' => $related_workshop['title'],
        ];
    }

    $hero_attributes = [
        'eyebrow' => '',
        'heading' => strtoupper($workshop['title']),
        'subtitle' => '',
        'breadcrumbCurrent' => $workshop['title'],
        'variant' => 'yellow',
    ];
    $detail_attributes = [
        'serviceType' => 'Art Workshop',
        'heading' => $workshop['title'],
        'ageRange' => '',
        'description' => '',
        'mainImage' => !empty($workshop['image']) ? home_url($workshop['image']) : '',
        'mainImageAlt' => $workshop['title'],
        'infoTitle' => 'Workshop Information',
        'infoItems' => [],
        'ctaText' => 'Enquire Now',
        'ctaUrl' => '#course-enquiry',
        'relatedTitle' => 'Other Workshops',
        'relatedItems' => $related,
        'galleryTitle' => 'Your Smile, Our Passion',
        'galleryImages' => [],
    ];
    $form_attributes = [
        'anchor' => 'course-enquiry',
        'programmeOptions' => array_values(array_column($workshops, 'title')),
        'selectedProgramme' => $workshop['title'],
    ];

    $pages[$slug] = [
        'title' => $workshop['title'],
        'template' => 'page-service-detail',
        'content' => '<!-- wp:ai-zippy/page-hero ' . json_encode($hero_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ' /-->' . "\n"
            . '<!-- wp:ai-zippy/service-detail ' . json_encode($detail_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ' /-->' . "\n"
            . '<!-- wp:ai-zippy/course-enquiry-form ' . json_encode($form_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ' /-->',
    ];
}

$pages['contact-us'] = [
    'title' => 'Contact Us',
    'template' => 'page-contact',
    'existing_empty_only' => true,
    'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"CONTACTS","subtitle":"","breadcrumbCurrent":"Contacts","variant":"blue"} /-->
<!-- wp:ai-zippy/contact-overview /-->
<!-- wp:ai-zippy/contact-studios {"heading":"VISIT US TO SEE OUR STUDIOS!"} /-->
<!-- wp:ai-zippy/course-enquiry-form {"heading":"Ask us questions","subheading":"Book an appointment","submitText":"SEND ENQUIRY"} /-->
BLOCKS,
];

$pages['our-studios'] = [
    'title' => 'Our Studios',
    'template' => 'page-studios',
    'existing_empty_only' => true,
    'content' => <<<'BLOCKS'
<!-- wp:ai-zippy/page-hero {"eyebrow":"","heading":"OUR STUDIOS","subtitle":"","breadcrumbCurrent":"Our Studios","variant":"pink"} /-->
<!-- wp:ai-zippy/studios-directory /-->
BLOCKS,
];

return $pages;
