<?php
defined('ABSPATH') || exit;

$heading = $attributes['heading'] ?? '';
$categories_title = $attributes['categoriesTitle'] ?? 'Categories';
$all_categories_text = $attributes['allCategoriesText'] ?? 'All';
$empty_message = $attributes['emptyMessage'] ?? '';
$products_per_page = max(1, min(48, (int) ($attributes['productsPerPage'] ?? 12)));
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-woo-listing']);
$woocommerce_ready = class_exists('WooCommerce') && function_exists('wc_get_products') && post_type_exists('product');
$requested_category = isset($_GET['product_cat']) ? sanitize_title(wp_unslash($_GET['product_cat'])) : '';
$selected_category = '';
$products = [];
$categories = [];

if ($woocommerce_ready) {
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
    if (is_wp_error($categories)) {
        $categories = [];
    }
    $allowed_category_slugs = wp_list_pluck($categories, 'slug');
    if ($requested_category && in_array($requested_category, $allowed_category_slugs, true)) {
        $selected_category = $requested_category;
    }
    $query = [
        'status' => 'publish',
        'visibility' => 'visible',
        'limit' => $products_per_page,
        'orderby' => 'date',
        'order' => 'DESC',
    ];
    if ($selected_category) {
        $query['category'] = [$selected_category];
    }
    $products = wc_get_products($query);
}
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="achiever-woo-listing__inner">
    <?php if ($heading) : ?><h2><?php echo esc_html($heading); ?></h2><?php endif; ?>
    <?php if ($woocommerce_ready && $categories) : ?>
      <nav class="achiever-woo-listing__filters" aria-label="<?php echo esc_attr($categories_title); ?>">
        <strong><?php echo esc_html($categories_title); ?></strong>
        <a href="<?php echo esc_url(remove_query_arg('product_cat')); ?>"<?php if (!$selected_category) : ?> aria-current="page"<?php endif; ?>><?php echo esc_html($all_categories_text); ?></a>
        <?php foreach ($categories as $category) : ?><a href="<?php echo esc_url(add_query_arg('product_cat', $category->slug)); ?>"<?php if ($selected_category === $category->slug) : ?> aria-current="page"<?php endif; ?>><?php echo esc_html($category->name); ?> <span>(<?php echo esc_html((string) $category->count); ?>)</span></a><?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <?php if ($woocommerce_ready && $products) : ?>
      <div class="achiever-woo-listing__grid">
        <?php foreach ($products as $product) : ?>
          <article class="achiever-woo-listing__product">
            <?php $image_id = $product->get_image_id(); ?>
            <?php if ($image_id) : ?>
              <a class="achiever-woo-listing__image" href="<?php echo esc_url($product->get_permalink()); ?>" aria-label="<?php echo esc_attr(sprintf(__('View %s', 'ai-zippy'), $product->get_name())); ?>"><?php echo wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, ['loading' => 'lazy', 'alt' => $product->get_name()]); ?></a>
            <?php else : ?>
              <div class="achiever-woo-listing__image" aria-hidden="true"><span></span></div>
            <?php endif; ?>
            <div class="achiever-woo-listing__content">
              <h3><a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
              <div class="achiever-woo-listing__price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
              <a class="achiever-btn" href="<?php echo esc_url($product->add_to_cart_url()); ?>" data-quantity="1" data-product_id="<?php echo esc_attr((string) $product->get_id()); ?>" rel="nofollow"><?php echo esc_html($product->add_to_cart_text()); ?></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php elseif ($empty_message) : ?>
      <p class="achiever-woo-listing__empty" role="status"><?php echo esc_html($empty_message); ?></p>
    <?php endif; ?>
  </div>
</section>
