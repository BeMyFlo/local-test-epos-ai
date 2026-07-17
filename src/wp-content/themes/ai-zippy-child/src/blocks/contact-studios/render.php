<?php
defined('ABSPATH') || exit;

$heading = esc_html($attributes['heading'] ?? 'Our Studios');
$studios = $attributes['studios'] ?? [];
$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; ?>>
  <div class="achiever-studios">
    <div class="achiever-studios__container">
      <h2 class="achiever-studios__heading"><?php echo $heading; ?></h2>
      <div class="achiever-studios__grid">
        <?php foreach ($studios as $studio):
          $name = esc_html($studio['name'] ?? '');
          $phone = esc_html($studio['phone'] ?? '');
          $phone_clean = preg_replace('/[^0-9+]/', '', $phone);
          $address = nl2br(esc_html($studio['address'] ?? ''));
          $hours = nl2br(esc_html($studio['hours'] ?? ''));
        ?>
          <div class="achiever-studios__card">
            <h3 class="achiever-studios__name"><?php echo $name; ?></h3>
            <?php if ($phone): ?>
              <a href="tel:<?php echo esc_attr($phone_clean); ?>" class="achiever-studios__phone"><?php echo $phone; ?></a>
            <?php endif; ?>
            <?php if ($address): ?>
              <div class="achiever-studios__address"><?php echo $address; ?></div>
            <?php endif; ?>
            <?php if ($hours): ?>
              <div class="achiever-studios__hours">
                <strong>Opening Hours</strong>
                <p><?php echo $hours; ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
