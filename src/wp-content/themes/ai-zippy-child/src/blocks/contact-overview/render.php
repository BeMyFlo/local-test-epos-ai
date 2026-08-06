<?php
defined('ABSPATH') || exit;
$heading = $attributes['heading'] ?? 'Ask us questions';
$subheading = $attributes['subheading'] ?? 'Book an appointment with us to save time!';
$phone_label = $attributes['phoneLabel'] ?? 'Whatsapp us at';
$phone = $attributes['phone'] ?? '';
$email_label = $attributes['emailLabel'] ?? 'Email';
$email = $attributes['email'] ?? '';
$hours_title = $attributes['hoursTitle'] ?? 'Opening Hours';
$hours = $attributes['hours'] ?? [];

// Older saved values used the "Contact us at" label. The number is a WhatsApp
// line, so normalize the label and point the link at wa.me instead of tel:.
if (stripos($phone_label, 'contact us') !== false) {
    $phone_label = 'Whatsapp us at';
}

// wa.me expects digits only, including the country code and no leading "+".
// Singapore numbers entered without one (e.g. "9123 4537") get 65 prefixed.
$phone_digits = preg_replace('/\D/', '', $phone);
if ($phone_digits !== '' && strlen($phone_digits) === 8) {
    $phone_digits = '65' . $phone_digits;
}
$whatsapp_url = $phone_digits !== '' ? 'https://wa.me/' . $phone_digits : '';

$social_html = function_exists('ai_zippy_child_social_links_html')
    ? ai_zippy_child_social_links_html('achiever-social')
    : '';
$whatsapp_icon = function_exists('ai_zippy_child_icon_svg') ? ai_zippy_child_icon_svg('whatsapp') : '';
$email_icon    = function_exists('ai_zippy_child_icon_svg') ? ai_zippy_child_icon_svg('email') : '';

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-contact-overview']);
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="achiever-contact-overview__inner">
    <div class="achiever-contact-overview__intro">
      <h2><?php echo esc_html($heading); ?></h2>
      <p><?php echo esc_html($subheading); ?></p>
      <?php if ($social_html) : ?>
        <div class="achiever-contact-overview__social">
          <?php echo $social_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="achiever-contact-overview__contact">
      <div class="achiever-contact-overview__method">
        <span><?php echo esc_html($phone_label); ?></span>
        <?php if ($phone && $whatsapp_url) : ?>
          <a class="achiever-contact-overview__value" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener noreferrer">
            <span class="achiever-contact-overview__icon achiever-contact-overview__icon--whatsapp"><?php echo $whatsapp_icon; // phpcs:ignore WordPress.Security.EscapeOutput -- static theme SVG ?></span>
            <?php echo esc_html($phone); ?>
          </a>
        <?php endif; ?>
      </div>
      <div class="achiever-contact-overview__method">
        <span><?php echo esc_html($email_label); ?></span>
        <?php if ($email) : ?>
          <a class="achiever-contact-overview__value" href="mailto:<?php echo esc_attr(sanitize_email($email)); ?>">
            <span class="achiever-contact-overview__icon achiever-contact-overview__icon--email"><?php echo $email_icon; // phpcs:ignore WordPress.Security.EscapeOutput -- static theme SVG ?></span>
            <?php echo esc_html($email); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="achiever-contact-overview__hours">
      <h3><?php echo esc_html($hours_title); ?></h3>
      <?php foreach ($hours as $row) : ?><p><strong><?php echo esc_html($row['days'] ?? ''); ?></strong><span><?php echo esc_html($row['time'] ?? ''); ?></span></p><?php endforeach; ?>
    </div>
  </div>
</section>
