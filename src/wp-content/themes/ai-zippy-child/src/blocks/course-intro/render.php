<?php
/**
 * Server-side render for Foundation Course Content block.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$heading            = $attributes['heading'] ?? 'FOUNDATION ART COURSE';
$tagline            = $attributes['tagline'] ?? "Let's Artventure";
$tagline_description = $attributes['taglineDescription'] ?? 'Discover your creative journey through art classes, camps, short courses and workshops for all ages.';
$description        = $attributes['description'] ?? "Specially designed for our little Picassos, our program focuses on refining fine motor skills, mastering pen control, and grasping fundamental art theories and concepts through creative expression.\n\nYour child will have the opportunity to explore a plethora of mediums, including acrylic paints, watercolours, oil pastels, clay, and beyond, all while fostering confidence in their creative abilities.\n\nJoin us in nurturing the budding artist within your child!";
$info_box           = $attributes['infoBox'] ?? [
    'lessons'  => 'Each Term: 8 Lessons',
    'duration' => 'Each Session: 60mins',
    'ageRange' => 'Age 3 - 5',
];
$terms_title        = $attributes['termsTitle'] ?? 'TERMS';
$terms              = $attributes['terms'] ?? [
    'There are a total of 6 terms to complete.',
    'Each term consists of 8 lessons.',
    'Following through all 6 terms is non-compulsory.',
    'One time registration fees is applicable.',
    'A T-shirt, Apron, & Bag will be given to new sign ups.',
];
$supplies_title     = $attributes['suppliesTitle'] ?? 'ART SUPPLIES';
$supplies_text      = $attributes['suppliesText'] ?? "Due to hygiene concerns, students are required to bring their own supplies.\nConnect with us to know if the supplies you owned are compatible!\nWe do sell them in our studio should you need them.";
$cta_text           = $attributes['ctaText'] ?? 'ENQUIRY NOW';
$cta_url            = $attributes['ctaUrl'] ?? '#course-enquiry';
$programme_title    = $attributes['programmeTitle'] ?? 'PROGRAMME CONTENT';
$programme_subtitle = $attributes['programmeSubtitle'] ?? 'Exploration on Different Mediums';
$programme_content  = $attributes['programmeContent'] ?? [
    'Techniques of Mediums (Acrylic Paints, Oil Pastels, Soft Pastel, Watercolour, Clay, etc)',
    'Principle of Art',
    'Pen-Control and Motor Skills',
    'Shading and Styles',
    'Element of Art',
    'Observation'
];
$gallery_images     = $attributes['galleryImages'] ?? [
    '/wp-content/uploads/2026/07/ArtCamp-2025-768x543.png',
    '/wp-content/uploads/2026/07/CraftCamp-2025-768x543.png',
    '/wp-content/uploads/2026/07/MangaDrawing2-768x543.png',
];

$decor_left_image  = $attributes['decorLeftImage'] ?? '/wp-content/uploads/2026/07/Sensory-Playhaus_STICKER.png';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '/wp-content/uploads/2026/07/Sensory-Playhaus_COLOR.png';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Cartoon mascot right decoration';

$decor_left_style  = function_exists('ai_zippy_child_decor_style')
    ? ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex' => 5, 'desktopX' => 85, 'desktopY' => 65, 'desktopSize' => 180, 'mobileX' => 85, 'mobileY' => 65, 'mobileSize' => 120])
    : '';
$decor_right_style = function_exists('ai_zippy_child_decor_style')
    ? ai_zippy_child_decor_style($attributes, 'decorRight', ['zIndex' => 5, 'desktopX' => 15, 'desktopY' => 85, 'desktopSize' => 200, 'mobileX' => 15, 'mobileY' => 85, 'mobileSize' => 130])
    : '';

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-course-intro']);
?>
<div <?php echo $wrapper_attributes; ?>>

  <!-- SECTION 1: HERO / TITLE SECTION -->
  <section class="achiever-course-intro__hero">
    <div class="achiever-course-intro__tagline">
      <p class="achiever-course-intro__tagline-title"><?php echo esc_html($tagline); ?></p>
      <p class="achiever-course-intro__tagline-copy"><?php echo esc_html($tagline_description); ?></p>
    </div>
    <h1 class="achiever-course-intro__heading"><?php echo esc_html($heading); ?></h1>
  </section>

  <!-- SECTION 2: DESCRIPTION & INFO CARD (PINK SECTION) -->
  <section class="achiever-course-intro__pink-section">
    <div class="achiever-course-intro__container">
      <div class="achiever-course-intro__description">
        <?php foreach (preg_split('/\R{2,}/', (string)$description) as $paragraph) : ?>
          <p><?php echo esc_html($paragraph); ?></p>
        <?php endforeach; ?>
      </div>

      <div class="achiever-course-intro__info-wrapper">
        <div class="achiever-course-intro__decor-scissors" aria-hidden="true">
          <svg viewBox="0 0 100 80" width="70" height="56"><path d="M20 20C10 20 5 30 15 40L45 45L15 50C5 60 10 70 20 70C30 70 35 55 45 45L75 75L85 65L45 45L85 25L75 15L45 45C35 35 30 20 20 20Z" fill="#ff4d6d"/><circle cx="20" cy="30" r="5" fill="#ffffff"/><circle cx="20" cy="60" r="5" fill="#ffffff"/><polygon points="45,45 95,50 85,75" fill="#e0f7fa"/></svg>
        </div>
        
        <div class="achiever-course-intro__info-pill">
          <span><?php echo esc_html($info_box['lessons'] ?? 'Each Term: 8 Lessons'); ?></span>
          <span><?php echo esc_html($info_box['duration'] ?? 'Each Session: 60mins'); ?></span>
          <strong><?php echo esc_html($info_box['ageRange'] ?? 'Age 3 - 5'); ?></strong>
        </div>

        <div class="achiever-course-intro__decor-rocket">
          <img src="<?php echo esc_url($decor_left_image ?: '/wp-content/uploads/2026/07/Sensory-Playhaus_COLOR.png'); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
        </div>
      </div>
    </div>
    
    <!-- Curved Wave Divider -->
    <div class="achiever-course-intro__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 120" preserveAspectRatio="none"><path d="M0,32L80,42.7C160,53,320,75,480,80C640,85,800,75,960,58.7C1120,43,1280,21,1360,10.7L1440,0L1440,120L1360,120C1280,120,1120,120,960,120C320,120,160,120,80,120L0,120Z" fill="#ffffff"></path></svg>
    </div>
  </section>

  <!-- SECTION 3: TERMS & ART SUPPLIES (WHITE SECTION) -->
  <section class="achiever-course-intro__white-section">
    <!-- Left Yellow Pencil Decor -->
    <div class="achiever-course-intro__decor-pencil-left" aria-hidden="true">
      <svg viewBox="0 0 120 40" width="70" height="24"><path d="M10 20L90 8L105 15L90 22L10 20Z" fill="#ffd100"/><path d="M10 20L25 18L25 22Z" fill="#ff6584"/><path d="M90 8L105 15L90 22Z" fill="#ffb800"/></svg>
    </div>

    <!-- Top Right Double Pencil Decor -->
    <div class="achiever-course-intro__decor-pencils" aria-hidden="true">
      <svg viewBox="0 0 180 50" width="110" height="32"><path d="M10 20L150 5L170 15L150 25L10 20Z" fill="#ff6584"/><path d="M10 20L30 18L30 22Z" fill="#ffb800"/><path d="M40 40L160 25L180 35L160 45L40 40Z" fill="#7b61ff"/><path d="M40 40L60 38L60 42Z" fill="#ffb800"/></svg>
    </div>

    <div class="achiever-course-intro__container">
      <div class="achiever-course-intro__terms">
        <h2 class="achiever-course-intro__section-title"><?php echo esc_html($terms_title); ?></h2>
        <?php 
        $terms_arr = is_array($terms) ? $terms : explode("\n", (string)$terms);
        foreach ($terms_arr as $index => $term) : 
        ?>
          <p<?php echo $index === 3 ? ' class="achiever-course-intro__term-break"' : ''; ?>><?php echo esc_html($term); ?></p>
        <?php endforeach; ?>
      </div>

      <div class="achiever-course-intro__supplies">
        <h2 class="achiever-course-intro__section-title"><?php echo esc_html($supplies_title); ?></h2>
        <?php foreach (explode("\n", (string)$supplies_text) as $suppline) : ?>
          <p><?php echo esc_html($suppline); ?></p>
        <?php endforeach; ?>
      </div>

      <div class="achiever-course-intro__cta">
        <a class="achiever-btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a>
      </div>
    </div>

    <!-- Bottom Left Bear Mascot -->
    <div class="achiever-course-intro__bear-mascot">
      <img src="<?php echo esc_url($decor_right_image ?: '/wp-content/uploads/2026/07/Sensory-Playhaus_STICKER.png'); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  </section>

  <!-- SECTION 4: PROGRAMME CONTENT (YELLOW SECTION) -->
  <section class="achiever-course-intro__programme">
    <div class="achiever-course-intro__container">
      <h2 class="achiever-course-intro__section-title"><?php echo esc_html($programme_title); ?></h2>
      <p class="achiever-course-intro__programme-subtitle"><?php echo esc_html($programme_subtitle); ?></p>
      <div class="achiever-course-intro__programme-grid">
        <?php 
        $programme_arr = is_array($programme_content) ? $programme_content : explode("\n", (string)$programme_content);
        foreach ($programme_arr as $item) : 
        ?>
          <div class="achiever-course-intro__programme-card"><?php echo esc_html($item); ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- SECTION 5: STUDENT GALLERY SLIDER -->
  <section class="achiever-course-intro__gallery">
    <div class="achiever-course-intro__gallery-slider" data-course-gallery-slider>
      <button type="button" class="achiever-course-intro__gallery-arrow achiever-course-intro__gallery-arrow--prev" aria-label="Previous gallery image" data-course-gallery-prev>‹</button>
      <div class="achiever-course-intro__gallery-track" data-course-gallery-track>
        <?php 
        foreach ($gallery_images as $image) : 
            $url = is_array($image) ? ($image['url'] ?? '') : $image;
            $alt = is_array($image) ? ($image['alt'] ?? 'Student artwork') : 'Student artwork';
        ?>
          <div class="achiever-course-intro__gallery-item">
            <?php if ($url) : ?>
              <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
            <?php else : ?>
              <div class="achiever-course-intro__gallery-placeholder"></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="achiever-course-intro__gallery-arrow achiever-course-intro__gallery-arrow--next" aria-label="Next gallery image" data-course-gallery-next>›</button>
    </div>
  </section>

</div>
