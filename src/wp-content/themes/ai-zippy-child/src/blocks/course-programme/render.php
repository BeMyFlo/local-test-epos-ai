<?php
defined('ABSPATH') || exit;
$style='background-color:'.sanitize_hex_color($attributes['backgroundColor']??'#fffec9').';';if(!empty($attributes['backgroundImage'])){$style.='background-image:url('.esc_url($attributes['backgroundImage']).');background-size:cover;background-position:center;';}
$wrapper_attributes=get_block_wrapper_attributes(['class'=>'achiever-course-intro achiever-course-programme']);
?>
<section <?php echo $wrapper_attributes; ?>><div class="achiever-course-intro__programme" style="<?php echo esc_attr($style); ?>"><div class="achiever-course-intro__container">
<?php if(!empty($attributes['image'])):?><div class="achiever-course-programme__image"><img src="<?php echo esc_url($attributes['image']); ?>" alt="<?php echo esc_attr($attributes['imageAlt']??''); ?>" loading="lazy"></div><?php endif;?>
<h2 class="achiever-course-intro__section-title"><?php echo esc_html($attributes['heading']??''); ?></h2><p class="achiever-course-intro__programme-subtitle"><?php echo esc_html($attributes['subheading']??''); ?></p><div class="achiever-course-intro__programme-grid"><?php foreach(($attributes['items']??[]) as $item):?><div class="achiever-course-intro__programme-card"><?php echo esc_html($item); ?></div><?php endforeach;?></div></div></div></section>
