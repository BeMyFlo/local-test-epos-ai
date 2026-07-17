<?php
/**
 * Server-side render for Home Best Sellers block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$section_title = $attributes['sectionTitle'] ?? 'our best sellers';
$products      = $attributes['products'] ?? [];
$cta_text      = $attributes['ctaText'] ?? 'VIEW MORE WORKSHOP';
$cta_url       = $attributes['ctaUrl'] ?? '/shop/';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-products achiever-products--best-sellers',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <h2 class="achiever-products__title"><?php echo esc_html($section_title); ?></h2>

    <div class="achiever-products__grid">
        <?php foreach ($products as $product) :
            $name     = $product['name'] ?? 'Name of Product';
            $category = $product['category'] ?? 'Category';
            $age_time = $product['ageTime'] ?? 'Age + Time Workshop';
            $image    = $product['image'] ?? '';
            $product_url = $product['ctaUrl'] ?? '#';
        ?>
            <a href="<?php echo esc_url($product_url); ?>" class="achiever-products__card">
                <div class="achiever-products__card-img">
                    <?php if (!empty($image)) : ?>
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($name); ?>" />
                    <?php else : ?>
                        <div class="achiever-products__card-placeholder"></div>
                    <?php endif; ?>
                </div>
                <div class="achiever-products__card-body">
                    <span class="achiever-products__card-name"><?php echo esc_html($name); ?></span>
                    <span class="achiever-products__card-meta"><?php echo esc_html($category); ?> · <?php echo esc_html($age_time); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="achiever-products__cta">
        <a href="<?php echo esc_url($cta_url); ?>" class="achiever-btn achiever-btn--primary"><?php echo esc_html($cta_text); ?></a>
    </div>
</div>
