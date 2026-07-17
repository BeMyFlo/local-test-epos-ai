<?php

/**
 * Server-side render for Classes Hero block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

$heading       = nl2br( esc_html( $attributes['heading'] ?? '' ) );
$section_title = esc_html( $attributes['sectionTitle'] ?? '' );
$classes       = $attributes['classes'] ?? [];

$wrapper_attributes = get_block_wrapper_attributes();
?>

<div <?php echo $wrapper_attributes; ?>>
	<div class="achiever-classes-hero">
		<div class="achiever-classes-hero__container">
			<div class="achiever-classes-hero__header">
				<span class="achiever-classes-hero__label"><?php echo $section_title; ?></span>
				<h1 class="achiever-classes-hero__heading"><?php echo $heading; ?></h1>
			</div>
			<div class="achiever-classes-hero__grid">
				<?php foreach ( $classes as $class_item ) :
					$name     = esc_html( $class_item['name'] ?? '' );
					$age      = esc_html( $class_item['ageRange'] ?? '' );
					$tagline  = esc_html( $class_item['tagline'] ?? '' );
					$image    = esc_url( $class_item['image'] ?? '' );
				?>
					<div class="achiever-classes-hero__card">
						<?php if ( $image ) : ?>
							<div class="achiever-classes-hero__card-image">
								<img src="<?php echo $image; ?>" alt="<?php echo $name; ?>" />
							</div>
						<?php else : ?>
							<div class="achiever-classes-hero__card-image achiever-classes-hero__card-image--placeholder"></div>
						<?php endif; ?>
						<h3 class="achiever-classes-hero__card-name"><?php echo $name; ?></h3>
						<span class="achiever-classes-hero__card-age"><?php echo $age; ?></span>
						<p class="achiever-classes-hero__card-tagline"><?php echo $tagline; ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
