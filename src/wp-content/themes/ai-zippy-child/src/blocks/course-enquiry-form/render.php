<?php

/**
 * Server-side render for Course Enquiry Form block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

$heading         = nl2br( esc_html( $attributes['heading'] ?? '' ) );
$subheading      = esc_html( $attributes['subheading'] ?? '' );
$cta_label       = esc_html( $attributes['ctaLabel'] ?? '' );
$submit_text     = esc_html( $attributes['submitText'] ?? 'SEND ENQUIRY' );
$success_message = esc_attr( $attributes['successMessage'] ?? '' );
$studios         = $attributes['studios'] ?? [];
$programme_types = $attributes['programmeTypes'] ?? [];

$wrapper_attributes = get_block_wrapper_attributes();
?>

<div <?php echo $wrapper_attributes; ?>>
	<div class="achiever-enquiry-form">
		<div class="achiever-enquiry-form__container">
			<div class="achiever-enquiry-form__sidebar">
				<h2 class="achiever-enquiry-form__heading"><?php echo $heading; ?></h2>
				<p class="achiever-enquiry-form__subheading"><?php echo $subheading; ?></p>
				<span class="achiever-enquiry-form__cta-label"><?php echo $cta_label; ?></span>
			</div>
			<form class="achiever-enquiry-form__form" id="achiever-enquiry-form" method="post" data-submit-text="<?php echo $submit_text; ?>" data-success-message="<?php echo $success_message; ?>">
				<div class="achiever-enquiry-form__row">
					<input type="text" name="name" placeholder="Name" required />
				</div>
				<div class="achiever-enquiry-form__row achiever-enquiry-form__row--double">
					<input type="tel" name="phone" placeholder="Phone Number" required />
					<input type="email" name="email" placeholder="Email" required />
				</div>
				<div class="achiever-enquiry-form__row achiever-enquiry-form__row--double">
					<input type="text" name="children_age" placeholder="Children Age" required />
					<select name="studio" required>
						<option value="">Our Studio</option>
						<?php foreach ( $studios as $studio ) : ?>
							<option value="<?php echo esc_attr( $studio ); ?>"><?php echo esc_html( $studio ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="achiever-enquiry-form__row">
					<select name="programme_type" required>
						<option value="">Programme Type</option>
						<?php foreach ( $programme_types as $type ) : ?>
							<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="achiever-enquiry-form__row">
					<textarea name="message" placeholder="Messages" rows="4"></textarea>
				</div>
				<button type="submit" class="achiever-btn achiever-btn--primary"><?php echo $submit_text; ?></button>
			</form>
		</div>
	</div>
</div>
