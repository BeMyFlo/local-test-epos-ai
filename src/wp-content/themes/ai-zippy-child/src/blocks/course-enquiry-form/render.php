<?php
/**
 * Server-side render for Course Enquiry Form block.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$heading         = $attributes['heading'] ?? 'ASK US QUESTIONS';
$subheading      = $attributes['subheading'] ?? 'Book an appointment';
$submit_text     = $attributes['submitText'] ?? 'SEND ENQUIRY';
$success_message = $attributes['successMessage'] ?? "Thanks! We'll get back to you shortly.";
$placeholders    = $attributes['placeholders'] ?? [];
$labels          = $attributes['labels'] ?? [];
$studio_options  = $attributes['studioOptions'] ?? ['Studio 1', 'Studio 2'];
$programme_options = $attributes['programmeOptions'] ?? ['Artventurer', 'Canvas Wizard', 'Foundation Art Class', 'Sketcher Master'];
$selected_programme = $attributes['selectedProgramme'] ?? '';
$background_style = 'background-color:' . (sanitize_hex_color($attributes['backgroundColor'] ?? '#fce4ee') ?: '#fce4ee') . ' !important;';
if (!empty($attributes['backgroundImage'])) {$background_style .= 'background-image:url(' . esc_url($attributes['backgroundImage']) . ') !important;background-size:cover;background-position:center;';}
$decor_pencils_style = function_exists('ai_zippy_child_decor_style')
  ? ai_zippy_child_decor_style($attributes, 'decorPencils', ['zIndex' => 5, 'desktopX' => 87, 'desktopY' => 88, 'desktopSize' => 140, 'mobileX' => 78, 'mobileY' => 95, 'mobileSize' => 90])
  : '';

$instance_id = wp_unique_id('achiever-enquiry-');
$status_id = $instance_id . '-status';
$anchor_id = ! empty($attributes['anchor']) ? sanitize_title($attributes['anchor']) : 'course-enquiry';
$name_id = $instance_id . '-name';
$phone_id = $instance_id . '-phone';
$email_id = $instance_id . '-email';
$age_id = $instance_id . '-age';
$studio_id = $instance_id . '-studio';
$programme_id = $instance_id . '-programme';
$message_id = $instance_id . '-message';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-enquiry-form', 'id' => $anchor_id, 'style' => $background_style]);
?>
<div <?php echo $wrapper_attributes; ?>>
  <div class="achiever-enquiry-form__container">
    
    <!-- LEFT SIDE: HEADING & MASCOT -->
    <div class="achiever-enquiry-form__left">
      <h2 class="achiever-enquiry-form__heading"><?php echo esc_html($heading); ?></h2>
      <p class="achiever-enquiry-form__subheading"><?php echo esc_html($subheading); ?></p>
      
      <div class="achiever-enquiry-form__mascot">
        <?php if (!empty($attributes['mascotImage'])) : ?><img src="<?php echo esc_url($attributes['mascotImage']); ?>" alt="<?php echo esc_attr($attributes['mascotImageAlt'] ?? ''); ?>" loading="lazy" /><?php endif; ?>
      </div>
    </div>

    <!-- RIGHT SIDE: FORM -->
    <div class="achiever-enquiry-form__right">
      <form class="achiever-enquiry-form__form" method="post" data-enquiry-form data-submit-text="<?php echo esc_attr($submit_text); ?>" data-success-message="<?php echo esc_attr($success_message); ?>" aria-describedby="<?php echo esc_attr($status_id); ?>">
        
        <input id="<?php echo esc_attr($name_id); ?>" type="text" name="name" placeholder="<?php echo esc_attr($placeholders['name'] ?? 'Name'); ?>" autocomplete="name" required />
        <input id="<?php echo esc_attr($phone_id); ?>" type="tel" name="phone" placeholder="<?php echo esc_attr($placeholders['phone'] ?? 'Phone Number'); ?>" autocomplete="tel" required />
        <input id="<?php echo esc_attr($email_id); ?>" type="email" name="email" placeholder="<?php echo esc_attr($placeholders['email'] ?? 'Email'); ?>" autocomplete="email" required />
        
        <div class="achiever-enquiry-form__row">
          <input id="<?php echo esc_attr($age_id); ?>" type="text" name="children_age" placeholder="<?php echo esc_attr($placeholders['childAge'] ?? 'Children Age'); ?>" required />
          <select id="<?php echo esc_attr($studio_id); ?>" name="studio" required>
            <option value=""><?php echo esc_html($placeholders['studio'] ?? 'Our Studio'); ?></option>
            <?php foreach ($studio_options as $option) : ?>
              <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <select id="<?php echo esc_attr($programme_id); ?>" name="programme_type" required>
          <option value=""><?php echo esc_html($placeholders['programType'] ?? 'Programme Type'); ?></option>
          <?php foreach ($programme_options as $option) : ?>
            <option value="<?php echo esc_attr($option); ?>"<?php selected($selected_programme, $option); ?>><?php echo esc_html($option); ?></option>
          <?php endforeach; ?>
        </select>

        <textarea id="<?php echo esc_attr($message_id); ?>" name="message" placeholder="<?php echo esc_attr($placeholders['message'] ?? 'Messages'); ?>" rows="5"></textarea>

        <div class="achiever-enquiry-form__submit-wrap">
          <button type="submit" class="achiever-btn"><?php echo esc_html($submit_text); ?></button>
        </div>

        <p id="<?php echo esc_attr($status_id); ?>" class="achiever-enquiry-form__status" role="status" aria-live="polite"></p>
      </form>
    </div>

  </div>
  <div class="achiever-enquiry-form__decor-pencils" style="<?php echo esc_attr($decor_pencils_style); ?>">
    <?php if (!empty($attributes['decorPencilsImage'])) : ?>
      <img src="<?php echo esc_url($attributes['decorPencilsImage']); ?>" alt="<?php echo esc_attr($attributes['decorPencilsAlt'] ?? ''); ?>" loading="lazy" />
    <?php else : ?>
      <svg viewBox="0 0 180 50" aria-hidden="true">
        <path d="M10 20L150 5L170 15L150 25L10 20Z" fill="#ffb800"/>
        <path d="M10 20L30 18L30 22Z" fill="#ff6584"/>
        <path d="M40 40L160 25L180 35L160 45L40 40Z" fill="#7b61ff"/>
        <path d="M40 40L60 38L60 42Z" fill="#ff6584"/>
      </svg>
    <?php endif; ?>
  </div>
</div>
