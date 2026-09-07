<?php
defined('ABSPATH') || exit;

$heading = $attributes['heading'] ?? '';
$categories_title = $attributes['categoriesTitle'] ?? 'Categories';
$all_categories_text = $attributes['allCategoriesText'] ?? 'All';
$empty_message = $attributes['emptyMessage'] ?? '';
$products_per_page = max(1, min(48, (int) ($attributes['productsPerPage'] ?? 12)));

// Content source: 'all' | 'category' | 'selection'.
$source = in_array($attributes['source'] ?? 'all', ['all', 'category', 'selection'], true)
    ? ($attributes['source'] ?? 'all')
    : 'all';
$chosen_categories = array_values(array_filter(array_map(
    'sanitize_title',
    (array) ($attributes['categories'] ?? [])
)));
$chosen_product_ids = array_values(array_filter(array_map(
    'absint',
    (array) ($attributes['productIds'] ?? [])
)));
$show_filters = (bool) ($attributes['showFilters'] ?? true);
$orderby = in_array($attributes['orderby'] ?? 'date', ['date', 'title', 'menu_order', 'price', 'popularity', 'rating'], true)
    ? ($attributes['orderby'] ?? 'date')
    : 'date';
$order = strtoupper($attributes['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-woo-listing']);
$woocommerce_ready = class_exists('WooCommerce') && function_exists('wc_get_products') && post_type_exists('product');
$requested_category = isset($_GET['product_cat']) ? sanitize_title(wp_unslash($_GET['product_cat'])) : '';
$selected_category = '';
$products = [];
$categories = [];

if ($woocommerce_ready) {
    $category_args = ['taxonomy' => 'product_cat', 'hide_empty' => true];

    // When the editor pinned specific categories, the filter bar lists only those.
    if ($source === 'category' && $chosen_categories) {
        $category_args['slug'] = $chosen_categories;
    }

    $categories = get_terms($category_args);
    if (is_wp_error($categories)) {
        $categories = [];
    }

    $allowed_category_slugs = wp_list_pluck($categories, 'slug');
    if ($requested_category && in_array($requested_category, $allowed_category_slugs, true)) {
        $selected_category = $requested_category;
    }

    if ($source === 'selection' && $chosen_product_ids) {
        // Explicit product picks keep the editor's chosen order.
        $products = wc_get_products([
            'status' => 'publish',
            'visibility' => 'visible',
            'include' => $chosen_product_ids,
            'orderby' => 'include',
            'limit' => count($chosen_product_ids),
        ]);
    } else {
        $query = [
            'status' => 'publish',
            'visibility' => 'visible',
            'limit' => $products_per_page,
            'orderby' => $orderby,
            'order' => $order,
        ];

        if ($selected_category) {
            $query['category'] = [$selected_category];
        } elseif ($source === 'category' && $chosen_categories) {
            $query['category'] = $chosen_categories;
        }

        $products = wc_get_products($query);
    }
}

$render_filters = $show_filters && $source !== 'selection';
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="achiever-woo-listing__inner">
    <?php if ($heading) : ?><h2><?php echo esc_html($heading); ?></h2><?php endif; ?>
    <?php if ($render_filters && $woocommerce_ready && $categories) : ?>
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
