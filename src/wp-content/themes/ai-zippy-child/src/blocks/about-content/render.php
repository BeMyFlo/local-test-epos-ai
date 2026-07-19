<?php
defined('ABSPATH') || exit;
$heading = $attributes['heading'] ?? 'About Achievers’ Art';
$description = $attributes['description'] ?? '';
$logo_image = $attributes['logoImage'] ?? '';
$logo_alt = $attributes['logoAlt'] ?? '';
$values = $attributes['values'] ?? [];
$gallery_title = $attributes['galleryTitle'] ?? 'Your Smile, Our Passion';
$gallery_images = array_values(array_filter(
    $attributes['galleryImages'] ?? [],
    static fn($image): bool => is_array($image) && !empty($image['url'])
));
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-about-content']);
$render_value_icon = static function (int $index): void {
    $icons = [
        '<svg viewBox="0 0 48 48" aria-hidden="true" focusable="false"><path d="M24 7C14.6 7 7 13.8 7 22.2 7 30 13.6 36 21.5 36H25a3 3 0 0 0 0-6h-1.2a2.8 2.8 0 0 1 0-5.6H29c7 0 12-3.8 12-9.2C41 10.6 34 7 24 7Z"/><circle cx="15" cy="20" r="2.5"/><circle cx="20" cy="14" r="2.5"/><circle cx="28" cy="14" r="2.5"/><circle cx="34" cy="19" r="2.5"/></svg>',
        '<svg viewBox="0 0 48 48" aria-hidden="true" focusable="false"><rect x="7" y="8" width="15" height="15" rx="4"/><circle cx="33.5" cy="15.5" r="7.5"/><path d="M8 39 15.5 27 23 39H8Zm18 0V27h14v12H26Z"/></svg>',
        '<svg viewBox="0 0 48 48" aria-hidden="true" focusable="false"><path d="M24 41S8 32.2 8 19.4C8 12.9 15.8 9 24 16c8.2-7 16-3.1 16 3.4C40 32.2 24 41 24 41Z"/><path d="M18 23.5 22.2 28 31 19.5"/></svg>',
    ];
    echo $icons[$index % count($icons)];
};
?>
<div <?php echo $wrapper_attributes; ?>>
  <div class="achiever-about-content__heading-block">
    <h2><?php echo esc_html($heading); ?></h2>
    <p><?php echo esc_html($description); ?></p>
  </div>
  <section class="achiever-about-content__intro">
    <div class="achiever-about-content__logo"><?php if ($logo_image) : ?><img src="<?php echo esc_url($logo_image); ?>" alt="<?php echo esc_attr($logo_alt); ?>" loading="lazy" /><?php else : ?><div class="achiever-about-content__brand-art" role="img" aria-label="Achievers’ Art creative enrichment studio"><svg viewBox="0 0 240 180" aria-hidden="true" focusable="false"><path class="achiever-about-content__brand-rainbow achiever-about-content__brand-rainbow--one" d="M37 105a83 83 0 0 1 166 0"/><path class="achiever-about-content__brand-rainbow achiever-about-content__brand-rainbow--two" d="M55 105a65 65 0 0 1 130 0"/><path class="achiever-about-content__brand-rainbow achiever-about-content__brand-rainbow--three" d="M73 105a47 47 0 0 1 94 0"/><path class="achiever-about-content__brand-pencil" d="m167 43 25-25 11 11-25 25-17 6 6-17Z"/><path class="achiever-about-content__brand-star" d="m48 42 5 10 11 2-8 8 2 11-10-5-10 5 2-11-8-8 11-2 5-10Z"/></svg><div class="achiever-about-content__wordmark">Achievers’ <strong>Art</strong><small>Creative enrichment studio</small></div></div><?php endif; ?></div>
    <div class="achiever-about-content__copy">
      <div class="achiever-about-content__values">
        <?php foreach ($values as $index => $value) : ?>
          <article class="achiever-about-content__value">
            <div class="achiever-about-content__value-icon"><?php if (!empty($value['icon'])) : ?><img src="<?php echo esc_url($value['icon']); ?>" alt="<?php echo esc_attr($value['alt'] ?? ''); ?>" loading="lazy" /><?php else : ?><?php $render_value_icon((int) $index); ?><?php endif; ?></div>
            <div><h3><?php echo esc_html($value['title'] ?? ''); ?></h3><p><?php echo esc_html($value['description'] ?? ''); ?></p></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php if ($gallery_images) : ?>
    <section class="achiever-about-content__gallery">
      <?php if ($gallery_title) : ?><h2><?php echo esc_html($gallery_title); ?></h2><?php endif; ?>
      <div class="achiever-about-content__gallery-grid">
        <?php foreach ($gallery_images as $image) : ?><div><img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" /></div><?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>
