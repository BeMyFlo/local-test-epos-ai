<?php
/**
 * Server-side render for Classes Banner block.
 *
 * Renders only the banner (tagline + breadcrumb + heading + background image).
 * Reuses the existing .achiever-classes-hero__* CSS so no style changes needed.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$tagline             = $attributes['tagline'] ?? '';
$tagline_description = $attributes['taglineDescription'] ?? '';
$heading             = $attributes['heading'] ?? "REGULAR\nCLASSES";

// The "Let's Artventure" tagline belongs to the home page only. Inner pages
// saved it into their block attributes, so clear it here rather than relying on
// each page's stored value being edited.
if (!is_front_page()) {
    $tagline             = '';
    $tagline_description = '';
}
$breadcrumb_home_text = $attributes['breadcrumbHomeText'] ?? 'Home';
$breadcrumb_home_url = $attributes['breadcrumbHomeUrl'] ?? '/';
$breadcrumb_current  = $attributes['breadcrumbCurrent'] ?? 'Regular Classes';

// Saved attributes still carry the old page names — rewrite them on output.
if (function_exists('ai_zippy_child_rename_page_text')) {
    $heading            = ai_zippy_child_rename_page_text($heading);
    $breadcrumb_current = ai_zippy_child_rename_page_text($breadcrumb_current);
}
$background_image    = $attributes['backgroundImage'] ?? '';

// When a custom background image is set in the editor, output it as an inline
// style so it overrides the default image defined in CSS. Empty = keep CSS default.
$title_section_style = $background_image
    ? sprintf(' style="background-image:url(%s)"', esc_url($background_image))
    : '';

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-classes-hero']);
?>
<div <?php echo $wrapper_attributes; ?>>
  <section class="achiever-classes-hero__title-section"<?php echo $title_section_style; ?>>
    <div class="achiever-classes-hero__inner">
      <?php if ($tagline || $tagline_description) : ?>
        <div class="achiever-classes-hero__tagline">
          <?php if ($tagline) : ?>
            <p class="achiever-classes-hero__tagline-title"><?php echo esc_html($tagline); ?></p>
          <?php endif; ?>
          <?php if ($tagline_description) : ?>
            <p class="achiever-classes-hero__tagline-copy"><?php echo esc_html($tagline_description); ?></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <nav class="achiever-classes-hero__breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url($breadcrumb_home_url); ?>"><?php echo esc_html($breadcrumb_home_text); ?></a>
        <span aria-hidden="true">›</span>
        <span aria-current="page"><?php echo esc_html($breadcrumb_current); ?></span>
      </nav>
      <h1 class="achiever-classes-hero__heading"><?php echo nl2br(esc_html(achiever_normalize_newlines($heading))); ?></h1>
    </div>
  </section>
</div>
