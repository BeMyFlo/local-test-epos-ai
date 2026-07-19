<?php
defined('ABSPATH') || exit;
$heading = $attributes['heading'] ?? 'Ask us questions';
$subheading = $attributes['subheading'] ?? 'Book an appointment with us to save time!';
$phone_label = $attributes['phoneLabel'] ?? 'Contact us at';
$phone = $attributes['phone'] ?? '';
$email_label = $attributes['emailLabel'] ?? 'Email';
$email = $attributes['email'] ?? '';
$hours_title = $attributes['hoursTitle'] ?? 'Opening Hours';
$hours = $attributes['hours'] ?? [];
$phone_href = preg_replace('/[^0-9+]/', '', $phone);
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-contact-overview']);
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="achiever-contact-overview__inner">
    <div class="achiever-contact-overview__intro"><h2><?php echo esc_html($heading); ?></h2><p><?php echo esc_html($subheading); ?></p></div>
    <div class="achiever-contact-overview__contact">
      <div><span><?php echo esc_html($phone_label); ?></span><?php if ($phone) : ?><a href="tel:<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?></div>
      <div><span><?php echo esc_html($email_label); ?></span><?php if ($email) : ?><a href="mailto:<?php echo esc_attr(sanitize_email($email)); ?>"><?php echo esc_html($email); ?></a><?php endif; ?></div>
    </div>
    <div class="achiever-contact-overview__hours">
      <h3><?php echo esc_html($hours_title); ?></h3>
      <?php foreach ($hours as $row) : ?><p><strong><?php echo esc_html($row['days'] ?? ''); ?></strong><span><?php echo esc_html($row['time'] ?? ''); ?></span></p><?php endforeach; ?>
    </div>
  </div>
</section>
