<?php

/**
 * Server-side render for Classes Detail block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

$sections       = $attributes['sections'] ?? [];
$gallery_title  = nl2br( esc_html( $attributes['galleryTitle'] ?? '' ) );
$gallery_images = $attributes['galleryImages'] ?? [];

$wrapper_attributes = get_block_wrapper_attributes();
?>

<div <?php echo $wrapper_attributes; ?>>
	<div class="achiever-classes-detail">
		<div class="achiever-classes-detail__container">
			<?php foreach ( $sections as $index => $section ) :
				$title       = nl2br( esc_html( $section['title'] ?? '' ) );
				$description = wp_kses_post( nl2br( esc_html( $section['description'] ?? '' ) ) );
				$image       = esc_url( $section['image'] ?? '' );
				$layout      = esc_attr( $section['layout'] ?? 'text-left' );
				$cta_text    = esc_html( $section['ctaText'] ?? '' );
				$cta_url     = esc_url( $section['ctaUrl'] ?? '#' );
			?>
				<div class="achiever-classes-detail__section achiever-classes-detail__section--<?php echo $layout; ?>">
					<div class="achiever-classes-detail__text">
						<h2 class="achiever-classes-detail__title"><?php echo $title; ?></h2>
						<div class="achiever-classes-detail__description"><?php echo $description; ?></div>
						<?php if ( $cta_text ) : ?>
							<a href="<?php echo $cta_url; ?>" class="achiever-btn achiever-btn--outline"><?php echo $cta_text; ?></a>
						<?php endif; ?>
					</div>
					<div class="achiever-classes-detail__image">
						<?php if ( $image ) : ?>
							<img src="<?php echo $image; ?>" alt="<?php echo esc_attr( $section['title'] ?? '' ); ?>" />
						<?php else : ?>
							<div class="achiever-classes-detail__image-placeholder"></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( $gallery_title || ! empty( $gallery_images ) ) : ?>
				<div class="achiever-classes-detail__gallery">
					<?php if ( $gallery_title ) : ?>
						<h2 class="achiever-classes-detail__gallery-title"><?php echo $gallery_title; ?></h2>
					<?php endif; ?>
					<div class="achiever-classes-detail__gallery-grid">
						<?php foreach ( $gallery_images as $img ) :
							$url = esc_url( $img['url'] ?? '' );
							$alt = esc_attr( $img['alt'] ?? '' );
						?>
							<?php if ( $url ) : ?>
								<div class="achiever-classes-detail__gallery-item">
									<img src="<?php echo $url; ?>" alt="<?php echo $alt; ?>" />
								</div>
							<?php else : ?>
								<div class="achiever-classes-detail__gallery-item achiever-classes-detail__gallery-item--placeholder"></div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
