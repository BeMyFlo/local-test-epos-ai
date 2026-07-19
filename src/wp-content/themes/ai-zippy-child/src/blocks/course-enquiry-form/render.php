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
$preferred_contact_option = $attributes['preferredContactOption'] ?? 'WhatsApp';
$studio_options = $attributes['studioOptions'] ?? [];
$programme_options = $attributes['programmeOptions'] ?? [];
$selected_programme = $attributes['selectedProgramme'] ?? '';

$regular_programmes = [
    'artventurer' => 'Artventurer (Age 3 & up)',
    'canvas-wizard' => 'Canvas Wizard (Age 6 & up)',
    'foundation-art-course' => 'Foundation Art Class (Age 4–6)',
    'sketcher-master' => 'Sketcher Master (Age 7 & up)',
    'little-draws' => 'Little Draws (Age 5 & up)',
    'junior-fine-arts' => 'Junior Fine Arts (Age 8 & up)',
    'drawvinci' => 'DrawVinci (Age 6 & up)',
    'portfolio-art' => 'Portfolio Art (Age 10 & up)',
];
$legacy_regular_programmes = [
    'Artventurer',
    'Canvas Wizard',
    'Foundation Art Class',
    'Sketcher Master',
    'Little Draws',
    'Junior Fine Arts',
    'DrawVinci',
    'Portfolio Art',
];
$current_slug = get_post_field('post_name', get_queried_object_id());
$is_regular_detail = isset($regular_programmes[$current_slug]);
$serialized_attributes = isset($block->parsed_block['attrs']) && is_array($block->parsed_block['attrs'])
    ? $block->parsed_block['attrs']
    : [];
$has_custom_programmes = array_key_exists('programmeOptions', $serialized_attributes);
$has_custom_selection = array_key_exists('selectedProgramme', $serialized_attributes);
$has_legacy_regular_programmes = $programme_options === $legacy_regular_programmes;

if ($is_regular_detail && (! $has_custom_programmes || $has_legacy_regular_programmes)) {
    $programme_options = array_values($regular_programmes);
}
if ($is_regular_detail && $has_legacy_regular_programmes && in_array($selected_programme, $legacy_regular_programmes, true)) {
    $selected_index = array_search($selected_programme, $legacy_regular_programmes, true);
    $selected_programme = array_values($regular_programmes)[$selected_index];
} elseif ($is_regular_detail && ! $has_custom_selection) {
    $selected_programme = $regular_programmes[$current_slug];
}

$instance_id = wp_unique_id('achiever-enquiry-');
$status_id = $instance_id . '-status';
$anchor_id = ! empty($attributes['anchor'])
    ? sanitize_title($attributes['anchor'])
    : ($is_regular_detail ? 'course-enquiry' : $instance_id);
$name_id = $instance_id . '-name';
$phone_id = $instance_id . '-phone';
$email_id = $instance_id . '-email';
$age_id = $instance_id . '-age';
$studio_id = $instance_id . '-studio';
$preferred_contact_id = $instance_id . '-preferred-contact';
$programme_id = $instance_id . '-programme';
$message_id = $instance_id . '-message';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-enquiry-form', 'id' => $anchor_id]);
?>
<div <?php echo $wrapper_attributes; ?>>
  <div class="achiever-enquiry-form__container">
    <div class="achiever-enquiry-form__intro">
      <h2><?php echo esc_html($heading); ?></h2>
      <p><?php echo esc_html($subheading); ?></p>
    </div>
    <form class="achiever-enquiry-form__form" method="post" data-enquiry-form data-submit-text="<?php echo esc_attr($submit_text); ?>" data-success-message="<?php echo esc_attr($success_message); ?>" aria-describedby="<?php echo esc_attr($status_id); ?>">
      <label for="<?php echo esc_attr($name_id); ?>"><span><?php echo esc_html($labels['name'] ?? 'Name'); ?></span></label><input id="<?php echo esc_attr($name_id); ?>" type="text" name="name" placeholder="<?php echo esc_attr($placeholders['name'] ?? 'Name'); ?>" autocomplete="name" required />
      <label for="<?php echo esc_attr($phone_id); ?>"><span><?php echo esc_html($labels['phone'] ?? 'Phone Number'); ?></span></label><input id="<?php echo esc_attr($phone_id); ?>" type="tel" name="phone" placeholder="<?php echo esc_attr($placeholders['phone'] ?? 'Phone Number'); ?>" autocomplete="tel" required />
      <label for="<?php echo esc_attr($email_id); ?>"><span><?php echo esc_html($labels['email'] ?? 'Email'); ?></span></label><input id="<?php echo esc_attr($email_id); ?>" type="email" name="email" placeholder="<?php echo esc_attr($placeholders['email'] ?? 'Email'); ?>" autocomplete="email" required />
      <div class="achiever-enquiry-form__row">
        <div><label for="<?php echo esc_attr($age_id); ?>"><span><?php echo esc_html($labels['childAge'] ?? "Child's Age"); ?></span></label><input id="<?php echo esc_attr($age_id); ?>" type="text" name="children_age" placeholder="<?php echo esc_attr($placeholders['childAge'] ?? "Child's Age"); ?>" required /></div>
        <div><label for="<?php echo esc_attr($studio_id); ?>"><span><?php echo esc_html($labels['studio'] ?? 'Our Studio'); ?></span></label><select id="<?php echo esc_attr($studio_id); ?>" name="studio" required><option value=""><?php echo esc_html($placeholders['studio'] ?? 'Select a studio'); ?></option><?php foreach ($studio_options as $option) : ?><option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option><?php endforeach; ?></select></div>
      </div>
      <fieldset><legend><?php echo esc_html($labels['preferredContact'] ?? 'Preferred Mode of Contact'); ?></legend><div class="achiever-enquiry-form__checkbox"><input id="<?php echo esc_attr($preferred_contact_id); ?>" type="checkbox" name="preferred_contact" value="<?php echo esc_attr($preferred_contact_option); ?>" /><label for="<?php echo esc_attr($preferred_contact_id); ?>"><span><?php echo esc_html($preferred_contact_option); ?></span></label></div></fieldset>
      <label for="<?php echo esc_attr($programme_id); ?>"><span><?php echo esc_html($labels['programType'] ?? 'Programme Type'); ?></span></label><select id="<?php echo esc_attr($programme_id); ?>" name="programme_type" required><option value=""><?php echo esc_html($placeholders['programType'] ?? 'Select a programme'); ?></option><?php foreach ($programme_options as $option) : ?><option value="<?php echo esc_attr($option); ?>"<?php selected($selected_programme, $option); ?>><?php echo esc_html($option); ?></option><?php endforeach; ?></select>
      <label for="<?php echo esc_attr($message_id); ?>"><span><?php echo esc_html($labels['message'] ?? 'Message'); ?></span></label><textarea id="<?php echo esc_attr($message_id); ?>" name="message" placeholder="<?php echo esc_attr($placeholders['message'] ?? 'Messages'); ?>" rows="5"></textarea>
      <button type="submit" class="achiever-btn"><?php echo esc_html($submit_text); ?></button>
      <p id="<?php echo esc_attr($status_id); ?>" class="achiever-enquiry-form__status" role="status" aria-live="polite"></p>
    </form>
  </div>
</div>
