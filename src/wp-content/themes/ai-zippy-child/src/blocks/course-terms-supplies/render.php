<?php
defined('ABSPATH') || exit;
$style='background-color:'.sanitize_hex_color($attributes['backgroundColor']??'#ffffff').';';if(!empty($attributes['backgroundImage'])){$style.='background-image:url('.esc_url($attributes['backgroundImage']).');background-size:cover;background-position:center;';}
$mascot_style=function_exists('ai_zippy_child_decor_style')?ai_zippy_child_decor_style($attributes,'mascot',['zIndex'=>1,'desktopX'=>7.3684210526,'desktopY'=>88.0390752359,'desktopSize'=>150,'mobileX'=>28,'mobileY'=>90.877150016,'mobileSize'=>150]):'';
$mascot_style.=' --mascot-z-index:'.intval($attributes['mascotZIndex']??1).';';
$wrapper_attributes=get_block_wrapper_attributes(['class'=>'achiever-course-intro achiever-course-terms-supplies']);
?>
<section <?php echo $wrapper_attributes; ?> style="<?php echo esc_attr($style); ?>"><div class="achiever-course-intro__white-section" style="background:transparent !important"><div class="achiever-course-intro__container">
<div class="achiever-course-intro__terms"><h2 class="achiever-course-intro__section-title"><?php echo esc_html($attributes['termsTitle']??''); ?></h2><?php foreach(($attributes['terms']??[]) as $term): ?><p><?php echo esc_html($term); ?></p><?php endforeach; ?></div>
<div class="achiever-course-intro__supplies"><h2 class="achiever-course-intro__section-title"><?php echo esc_html($attributes['suppliesTitle']??''); ?></h2><p><?php echo nl2br(esc_html(achiever_normalize_newlines($attributes['suppliesText']??''))); ?></p></div>
<?php if(!empty($attributes['ctaText'])&&!empty($attributes['ctaUrl'])):?><div class="achiever-course-intro__cta"><a class="achiever-btn" href="<?php echo esc_url($attributes['ctaUrl']); ?>"><?php echo esc_html($attributes['ctaText']); ?></a></div><?php endif;?></div>
<?php if(!empty($attributes['image'])):?><div class="achiever-course-intro__bear-mascot" style="<?php echo esc_attr($mascot_style); ?>"><img src="<?php echo esc_url($attributes['image']); ?>" alt="<?php echo esc_attr($attributes['imageAlt']??''); ?>" loading="lazy"></div><?php endif;?></div></section>
