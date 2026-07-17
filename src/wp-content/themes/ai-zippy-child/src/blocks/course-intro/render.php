<?php

/**
 * Server-side render for Course Intro block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

$heading            = nl2br( esc_html( $attributes['heading'] ?? '' ) );
$description        = nl2br( esc_html( $attributes['description'] ?? '' ) );
$info_box           = $attributes['infoBox'] ?? [];
$terms              = $attributes['terms'] ?? [];
$supplies_text      = nl2br( esc_html( $attributes['suppliesText'] ?? '' ) );
$programme_content  = $attributes['programmeContent'] ?? [];

$lessons  = esc_html( $info_box['lessons'] ?? '' );
$duration = esc_html( $info_box['duration'] ?? '' );
$age_range = esc_html( $info_box['ageRange'] ?? '' );

$wrapper_attributes = get_block_wrapper_attributes();
?>

<div <?php echo $wrapper_attributes; ?>>
	<div class="achiever-course-intro">
		<div class="achiever-course-intro__container">
			<div class="achiever-course-intro__header">
				<h1 class="achiever-course-intro__heading"><?php echo $heading; ?></h1>
				<div class="achiever-course-intro__description"><?php echo $description; ?></div>
				<div class="achiever-course-intro__info-box">
					<span><?php echo $lessons; ?></span>
					<span><?php echo $duration; ?></span>
					<span><?php echo $age_range; ?></span>
				</div>
			</div>

			<?php if ( ! empty( $terms ) ) : ?>
				<div class="achiever-course-intro__terms">
					<h3>TERMS</h3>
					<ul>
						<?php foreach ( $terms as $term ) : ?>
							<li><?php echo esc_html( $term ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="achiever-course-intro__supplies">
				<h3>ART SUPPLIES</h3>
				<p><?php echo $supplies_text; ?></p>
			</div>

			<?php if ( ! empty( $programme_content ) ) : ?>
				<div class="achiever-course-intro__programme">
					<h3>PROGRAMME CONTENT</h3>
					<div class="achiever-course-intro__programme-grid">
						<?php foreach ( $programme_content as $item ) : ?>
							<div class="achiever-course-intro__programme-item">
								<?php echo esc_html( $item ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
