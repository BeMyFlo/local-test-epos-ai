<?php
/**
 * Server-side render for Course Banner block.
 *
 * Renders the course hero (tagline + heading + background image).
 * Reuses the existing .achiever-course-intro__* CSS so no style changes needed.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$tagline             = $attributes['tagline'] ?? "Let's Artventure";
$tagline_description = $attributes['taglineDescription'] ?? '';
$heading             = $attributes['heading'] ?? 'FOUNDATION ART COURSE';
$background_image    = $attributes['backgroundImage'] ?? '';
$background_color    = sanitize_hex_color($attributes['backgroundColor'] ?? '#ffffff') ?: '#ffffff';
$background_position = in_array(($attributes['backgroundPosition'] ?? ''), ['center top', 'center center', 'center bottom'], true) ? $attributes['backgroundPosition'] : 'center top';

// Default banner image (same mascots/rainbow art used elsewhere). A custom image
// set in the editor overrides it via inline style.
$default_bg = '/wp-content/uploads/2026/07/hero-bg-mascots.png';
$bg_url     = $background_image ?: $default_bg;
$hero_style = sprintf(
    ' style="background-color:%s;background-image:url(%s);background-size:cover;background-position:%s;background-repeat:no-repeat"',
    esc_attr($background_color),
    esc_url($bg_url),
    esc_attr($background_position)
);

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-course-intro achiever-course-banner']);
?>
<div <?php echo $wrapper_attributes; ?>>
  <section class="achiever-course-intro__hero"<?php echo $hero_style; ?>>
    <?php if ($tagline || $tagline_description) : ?>
      <div class="achiever-course-intro__tagline">
        <?php if ($tagline) : ?>
          <p class="achiever-course-intro__tagline-title"><?php echo esc_html($tagline); ?></p>
        <?php endif; ?>
        <?php if ($tagline_description) : ?>
          <p class="achiever-course-intro__tagline-copy"><?php echo esc_html($tagline_description); ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <h1 class="achiever-course-intro__heading"><?php echo esc_html($heading); ?></h1>
  </section>
</div>
