<?php
defined('ABSPATH') || exit;
$description = $attributes['description'] ?? '';
$style = 'background-color:' . sanitize_hex_color($attributes['backgroundColor'] ?? '#ffe4ee') . ';';
if (!empty($attributes['backgroundImage'])) {$style .= 'background-image:url(' . esc_url($attributes['backgroundImage']) . ');background-size:cover;background-position:center;';}
$decor_left_style = function_exists('ai_zippy_child_decor_style')
    ? ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex'=>5,'desktopX'=>15,'desktopY'=>85,'desktopSize'=>200,'mobileX'=>15,'mobileY'=>85,'mobileSize'=>130])
    : '';
$decor_rocket_style = function_exists('ai_zippy_child_decor_style')
    ? ai_zippy_child_decor_style($attributes, 'decorRocket', ['zIndex'=>1,'desktopX'=>86,'desktopY'=>50,'desktopSize'=>450,'mobileX'=>86,'mobileY'=>50,'mobileSize'=>450])
    : '';
$wrapper_attributes = get_block_wrapper_attributes(['class'=>'achiever-course-intro achiever-course-overview']);
?>
<section <?php echo $wrapper_attributes; ?> style="<?php echo esc_attr($style); ?>">
 <div class="achiever-course-intro__pink-section" style="background:transparent !important">
  <div class="achiever-course-intro__container">
   <div class="achiever-course-intro__description"><?php foreach (preg_split('/\R{2,}/',(string)$description) as $paragraph) : ?><p><?php echo esc_html($paragraph); ?></p><?php endforeach; ?></div>
   <div class="achiever-course-intro__info-wrapper">
   <div class="achiever-course-intro__decor-scissors achiever-course-overview__decor-scissors" style="<?php echo esc_attr($decor_left_style); ?>" aria-hidden="true">
    <?php if (!empty($attributes['decorLeftImage'])) : ?>
     <img src="<?php echo esc_url($attributes['decorLeftImage']); ?>" alt="<?php echo esc_attr($attributes['decorLeftAlt'] ?? ''); ?>" loading="lazy">
    <?php else : ?>
     <svg viewBox="0 0 138 126" role="img"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="31" cy="78" rx="21" ry="13" stroke="#ed512f" stroke-width="10" transform="rotate(-10 31 78)"/><ellipse cx="74" cy="44" rx="19" ry="13" stroke="#bd3b40" stroke-width="10" transform="rotate(-22 74 44)"/><path d="M46 70 116 112M59 57 66 116" stroke="#d5eef6" stroke-width="13"/><path d="m47 69 13-12" stroke="#ef7b35" stroke-width="8"/></g></svg>
    <?php endif; ?>
   </div>
   <div class="achiever-course-intro__info-pill achiever-course-overview__info-pill"><span><?php echo esc_html($attributes['lessons'] ?? ''); ?></span><span><?php echo esc_html($attributes['duration'] ?? ''); ?></span><strong><?php echo esc_html($attributes['ageRange'] ?? ''); ?></strong></div>
   <?php if (!empty($attributes['image'])) : ?><div class="achiever-course-intro__decor-rocket achiever-course-overview__decor-rocket" style="<?php echo esc_attr($decor_rocket_style); ?>"><img src="<?php echo esc_url($attributes['image']); ?>" alt="<?php echo esc_attr($attributes['imageAlt'] ?? ''); ?>" loading="lazy"></div><?php endif; ?></div>
  </div><div class="achiever-course-intro__wave" aria-hidden="true"><svg viewBox="0 0 1440 120" preserveAspectRatio="none"><path d="M0,70 C260,70 520,84 780,84 C1040,84 1240,40 1440,52 L1440,130 L0,130 Z" fill="#fff"/></svg></div>
 </div>
</section>
