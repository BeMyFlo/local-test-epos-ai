<?php

namespace AiZippy\Product;

defined('ABSPATH') || exit;

/**
 * Single product page renderer.
 *
 * Two shortcodes:
 *
 *   [ai_zippy_product_summary] — right-column content (title, price, variations,
 *     stock, qty + add-to-cart, badges, meta). Used inside templates/single-product.html.
 *
 *   [ai_zippy_product_tabs] — custom tabs (Description + Additional info).
 *     Replaces WC's product-details block for full markup control.
 *
 * Both shortcodes return early with empty string if not on a single product page —
 * safe to embed without breaking other contexts.
 */
class ProductShortcode
{
    public static function register(): void
    {
        add_shortcode('ai_zippy_product_summary', [self::class, 'renderSummary']);
        add_shortcode('ai_zippy_product_tabs',    [self::class, 'renderTabs']);

        // FSE's wp:shortcode block runs do_shortcode() then wpautop() — and
        // because shortcode block markers get stripped entirely, no
        // render_block filter fires on it. We can't hook into the per-block
        // pipeline. Instead, post-process the entire output buffer on
        // product pages and unwrap the wpautop-inserted <p> tags around our
        // block-level elements.
        add_action('template_redirect', [self::class, 'startOutputBuffer'], 1);
    }

    /**
     * Buffer the whole HTML response on single product pages so we can
     * unwrap stray <p> tags around our shortcode output that wpautop
     * inserted upstream of the render_block filter chain.
     */
    public static function startOutputBuffer(): void
    {
        if (!is_product()) {
            return;
        }
        ob_start([self::class, 'cleanProductPageOutput']);
    }

    /**
     * Output buffer callback — runs once at end of request on product pages.
     */
    public static function cleanProductPageOutput(string $html): string
    {
        // Run the unwrap pass over the whole HTML body. The unwrap regex
        // patterns are specific enough (only target <p> directly adjacent to
        // block-level / <a> tags) that they won't mangle normal paragraph
        // content elsewhere on the page.
        return self::unwrapAutopParagraphs($html);
    }

    /**
     * Aggressively undo wpautop's transformations on our shortcode output.
     * Strategy: any <p> that contains a block-level tag (form/div/select/etc.)
     * is unwrapped — those <p>s were created by wpautop, not by us.
     * Standalone <p>...</p> with only inline/text content is left alone.
     */
    private static function unwrapAutopParagraphs(string $html): string
    {
        // wpautop typically inserts <p> right before block-level tags WITHOUT
        // a matching </p> (because browsers auto-close <p> when they see a
        // block tag — wpautop relies on that). The closing </p> only exists
        // virtually in the parsed DOM, not in the HTML source.
        //
        // Strategy: strip any <p> tag that appears immediately before a
        // block-level opening tag. Also strip </p> that appears immediately
        // after a block-level closing tag (the symmetric case for paranoia).
        $blockTags = 'form|div|select|button|input|table|ul|ol|li|h[1-6]|address|fieldset|figure|hr|nav|section|article|aside|blockquote|pre|details|summary|dl|dt|dd|img|svg';

        // 1. Strip <p> (with or without attrs) directly before a block-level tag
        $html = preg_replace(
            '#<p[^>]*>\s*(<(?:' . $blockTags . ')\b)#i',
            '$1',
            $html
        );

        // 2. Strip </p> directly after a block-level closing tag
        $html = preg_replace(
            '#(</(?:' . $blockTags . ')>)\s*</p>#i',
            '$1',
            $html
        );

        // 3. Strip </p> right after an <a> opening tag — wpautop sometimes
        //    inserts a closing </p> when an <a> wraps block content (slim
        //    product card uses <a> as the card wrapper).
        $html = preg_replace(
            '#(<a\b[^>]*>)\s*</p>#i',
            '$1',
            $html
        );

        // 4. Strip <p> right before an </a> closing tag (symmetric case)
        $html = preg_replace(
            '#<p[^>]*>\s*(</a>)#i',
            '$1',
            $html
        );

        // 5. Strip </p> sandwiched between an inline closing tag (span/em/strong)
        //    and a block opening tag — wpautop pattern: text → block follows.
        //    e.g.  <span>x</span></p><div>...
        $html = preg_replace(
            '#(</(?:span|em|strong|small|i|b|code)>)\s*</p>\s*(<(?:' . $blockTags . ')\b)#i',
            '$1$2',
            $html
        );

        // 3. Strip <br> that wpautop inserted right before another tag
        $html = preg_replace('#<br\s*/?>\s*(<)#i', '$1', $html);

        // 4. Strip empty paragraphs left behind
        $html = preg_replace('#<p[^>]*>\s*</p>#i', '', $html);

        return $html;
    }

    // -------------------------------------------------------------------------
    // [ai_zippy_product_summary]
    // -------------------------------------------------------------------------

    public static function renderSummary(): string
    {
        global $product;
        if (!$product instanceof \WC_Product) {
            $product = wc_get_product(get_queried_object_id());
        }
        if (!$product instanceof \WC_Product) {
            return '';
        }

        ob_start();
        ?>
        <div class="zp-summary">

            <h1 class="zp-summary__title"><?php echo wp_kses_post($product->get_name()); ?></h1>

            <!-- Price + stock indicator on the same row -->
            <div class="zp-summary__price-row">
                <div class="zp-summary__price">
                    <?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php if ($product->is_on_sale()) :
                        $regular = (float) $product->get_regular_price();
                        $sale    = (float) $product->get_sale_price();
                        if ($regular > 0 && $sale > 0 && $regular > $sale) {
                            $pct = (int) round((($regular - $sale) / $regular) * 100);
                            echo '<span class="zp-summary__discount">-' . esc_html($pct) . '%</span>';
                        }
                    endif; ?>
                </div>
                <?php if ($product->is_type('variable')) : ?>
                    <!-- Variation stock pill — populated by /js/frontend/product/variations.js
                         when a valid variation is matched. Hidden until then. -->
                    <div class="zp-stock zp-stock--variation zp-stock--inline" hidden></div>
                <?php else : ?>
                    <?php self::renderStockIndicator($product); ?>
                <?php endif; ?>
            </div>

            <?php if ($product->get_short_description()) : ?>
                <div class="zp-summary__excerpt">
                    <?php echo apply_filters('woocommerce_short_description', $product->get_short_description()); // phpcs:ignore ?>
                </div>
            <?php endif; ?>

            <!-- Variations (variable products only) -->
            <?php if ($product->is_type('variable')) : ?>
                <?php self::renderVariationsForm($product); ?>
            <?php else : ?>
                <?php self::renderAddToCartForm($product); ?>
            <?php endif; ?>

            <!-- Buy Now: adds to cart + redirects to checkout -->
            <?php self::renderBuyNowButton($product); ?>

            <!-- Trust badges -->
            <?php self::renderTrustBadges(); ?>

            <!-- Meta -->
            <div class="zp-summary__meta">
                <?php if ($sku = $product->get_sku()) : ?>
                    <div class="zp-meta-row">
                        <span class="zp-meta-label"><?php esc_html_e('SKU:', 'ai-zippy'); ?></span>
                        <span class="zp-meta-value"><?php echo esc_html($sku); ?></span>
                    </div>
                <?php endif; ?>

                <?php
                $cats = wc_get_product_category_list($product->get_id(), ', ', '', '');
                if ($cats) : ?>
                    <div class="zp-meta-row">
                        <span class="zp-meta-label"><?php esc_html_e('Category:', 'ai-zippy'); ?></span>
                        <span class="zp-meta-value zp-meta-pills"><?php echo wp_kses_post($cats); ?></span>
                    </div>
                <?php endif; ?>

                <?php
                $tags = wc_get_product_tag_list($product->get_id(), ', ', '', '');
                if ($tags) : ?>
                    <div class="zp-meta-row">
                        <span class="zp-meta-label"><?php esc_html_e('Tags:', 'ai-zippy'); ?></span>
                        <span class="zp-meta-value zp-meta-pills"><?php echo wp_kses_post($tags); ?></span>
                    </div>
                <?php endif; ?>
            </div>

        </div>
        <?php

        return self::cleanShortcodeOutput(ob_get_clean());
    }

    /**
     * Sanitize HTML before returning from a shortcode so wpautop doesn't
     * wrap stray text/comments in <p> tags or insert <br> for our newlines.
     *
     * wpautop runs on the post content (including shortcode output) and
     * - converts blank lines into </p><p>
     * - converts single newlines inside text content into <br>
     *
     * Strategy:
     *   1. Strip HTML comments (they create blank-line patterns wpautop hates)
     *   2. Replace ALL newlines + tabs with single spaces (kills wpautop's
     *      newline triggers)
     *   3. Collapse multiple spaces between tags down to nothing
     *      (prevents stray spaces between block elements rendering as visible)
     *
     * Inline text spacing like "<strong>bold</strong> text" is preserved
     * because we only collapse whitespace *between tags*, never between text
     * and a tag.
     */
    private static function cleanShortcodeOutput(string $html): string
    {
        // 1. Strip HTML comments
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // 2. Flatten newlines + tabs to single spaces (kills wpautop's line-break logic)
        $html = preg_replace('/[\r\n\t]+/', ' ', $html);

        // 3. Collapse whitespace between adjacent tags (>...< only, NOT between text and tags)
        $html = preg_replace('/>\s+</', '><', $html);

        return $html;
    }

    // -------------------------------------------------------------------------
    // Sub-renderers
    // -------------------------------------------------------------------------

    private static function renderStockIndicator(\WC_Product $product): void
    {
        $stock_status = $product->get_stock_status();
        $stock_qty    = $product->get_stock_quantity();
        $manage_stock = $product->managing_stock();

        if ($stock_status === 'outofstock') {
            echo '<div class="zp-stock zp-stock--out">';
            echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
            esc_html_e('Out of stock', 'ai-zippy');
            echo '</div>';
            return;
        }

        if ($manage_stock && $stock_qty !== null && $stock_qty > 0 && $stock_qty <= 5) {
            echo '<div class="zp-stock zp-stock--low">';
            echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
            printf(
                /* translators: %d: stock quantity remaining */
                esc_html__('Only %d left in stock!', 'ai-zippy'),
                (int) $stock_qty
            );
            echo '</div>';
            return;
        }

        echo '<div class="zp-stock zp-stock--in">';
        echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>';
        esc_html_e('In stock', 'ai-zippy');
        echo '</div>';
    }

    /**
     * Quantity stepper + Add to Cart form for simple products.
     * For variable products this is rendered inside the variations form.
     */
    private static function renderAddToCartForm(\WC_Product $product): void
    {
        if (!$product->is_purchasable() || !$product->is_in_stock()) {
            return;
        }
        ?>
        <form class="zp-add-form cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype="multipart/form-data">
            <?php do_action('woocommerce_before_add_to_cart_button'); ?>

            <?php if (!$product->is_sold_individually()) : ?>
                <?php self::renderQuantityStepper($product); ?>
            <?php endif; ?>

            <button type="submit"
                name="add-to-cart"
                value="<?php echo esc_attr($product->get_id()); ?>"
                class="zp-add-btn single_add_to_cart_button button alt az-add-to-cart"
                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
            >
                <span class="zp-add-btn__text"><?php echo esc_html($product->single_add_to_cart_text()); ?></span>
                <svg class="zp-add-btn__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span class="zp-add-btn__spinner" aria-hidden="true"></span>
            </button>

            <?php do_action('woocommerce_after_add_to_cart_button'); ?>
        </form>
        <?php
    }

    /**
     * Render a custom quantity stepper (-/+).
     * Mirrors WC's standard input name/attrs so server-side handler still works.
     */
    private static function renderQuantityStepper(\WC_Product $product): void
    {
        $min  = (float) apply_filters('woocommerce_quantity_input_min',  0,  $product);
        $max  = (float) apply_filters('woocommerce_quantity_input_max', -1, $product);
        $step = (float) apply_filters('woocommerce_quantity_input_step', 1, $product);
        $value = $product->is_sold_individually() ? 1 : 1;
        ?>
        <div class="zp-qty">
            <button type="button" class="zp-qty__btn zp-qty__btn--minus" aria-label="<?php esc_attr_e('Decrease quantity', 'ai-zippy'); ?>">−</button>
            <input
                type="number"
                class="zp-qty__input input-text qty text"
                name="quantity"
                value="<?php echo esc_attr($value); ?>"
                min="<?php echo esc_attr($min); ?>"
                <?php if ($max > 0) : ?>max="<?php echo esc_attr($max); ?>"<?php endif; ?>
                step="<?php echo esc_attr($step); ?>"
                inputmode="numeric"
                aria-label="<?php esc_attr_e('Quantity', 'ai-zippy'); ?>"
            />
            <button type="button" class="zp-qty__btn zp-qty__btn--plus" aria-label="<?php esc_attr_e('Increase quantity', 'ai-zippy'); ?>">+</button>
        </div>
        <?php
    }

    /**
     * Variations form for variable products. Renders attribute swatches/dropdowns,
     * then a hidden .single_variation div WC's variations script populates with
     * the matching variation's price / stock / add-to-cart button.
     */
    private static function renderVariationsForm(\WC_Product_Variable $product): void
    {
        $attributes        = $product->get_variation_attributes();
        $available         = $product->get_available_variations();
        $selected          = $product->get_default_attributes();
        ?>
        <form class="zp-add-form variations_form cart"
            action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
            method="post"
            enctype="multipart/form-data"
            data-product_id="<?php echo esc_attr($product->get_id()); ?>"
            data-product_variations="<?php echo esc_attr(wp_json_encode($available)); ?>"
        >
            <div class="zp-variations variations">
                <?php foreach ($attributes as $attribute_name => $options) :
                    $taxonomy     = wc_attribute_taxonomy_name($attribute_name);
                    $is_taxonomy  = taxonomy_exists($taxonomy);
                    $label        = wc_attribute_label($attribute_name, $product);
                    $selected_val = isset($_REQUEST['attribute_' . sanitize_title($attribute_name)])
                        ? wc_clean(wp_unslash($_REQUEST['attribute_' . sanitize_title($attribute_name)]))
                        : ($selected[sanitize_title($attribute_name)] ?? '');
                    $kind = self::detectAttributeKind($attribute_name, $is_taxonomy);
                ?>
                    <div class="zp-variation" data-attribute="<?php echo esc_attr(sanitize_title($attribute_name)); ?>">
                        <div class="zp-variation__head">
                            <span class="zp-variation__label"><?php echo esc_html($label); ?>:</span>
                            <span class="zp-variation__value" data-selected-label></span>
                        </div>

                        <?php if ($kind === 'color') : ?>
                            <div class="zp-swatches zp-swatches--color" role="radiogroup" aria-label="<?php echo esc_attr($label); ?>">
                                <?php self::renderColorSwatches($attribute_name, $options, $is_taxonomy, $selected_val); ?>
                            </div>
                        <?php elseif ($kind === 'button') : ?>
                            <div class="zp-swatches zp-swatches--button" role="radiogroup" aria-label="<?php echo esc_attr($label); ?>">
                                <?php self::renderButtonSwatches($attribute_name, $options, $is_taxonomy, $selected_val); ?>
                            </div>
                        <?php else : ?>
                            <select
                                name="attribute_<?php echo esc_attr(sanitize_title($attribute_name)); ?>"
                                class="zp-variation__select"
                                data-attribute_name="attribute_<?php echo esc_attr(sanitize_title($attribute_name)); ?>"
                            >
                                <option value=""><?php printf(esc_html__('Choose %s', 'ai-zippy'), esc_html(strtolower($label))); ?></option>
                                <?php foreach ($options as $option) :
                                    if ($is_taxonomy) {
                                        $term = get_term_by('slug', $option, $taxonomy);
                                        $val   = $option;
                                        $text  = $term ? $term->name : $option;
                                    } else {
                                        $val   = $option;
                                        $text  = $option;
                                    }
                                ?>
                                    <option value="<?php echo esc_attr($val); ?>" <?php selected($selected_val, $val); ?>><?php echo esc_html($text); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <?php if ($kind !== 'select') : ?>
                            <?php
                            // WC's variation script only scans `.variations select` (not hidden inputs)
                            // so we render a visually-hidden <select> that JS syncs with swatch clicks.
                            $sanitized_name = sanitize_title($attribute_name);
                            $taxonomy_name  = wc_attribute_taxonomy_name($attribute_name);
                            ?>
                            <select
                                name="attribute_<?php echo esc_attr($sanitized_name); ?>"
                                class="zp-variation__select zp-variation__select--hidden"
                                data-attribute_name="attribute_<?php echo esc_attr($sanitized_name); ?>"
                                aria-hidden="true"
                                tabindex="-1"
                                style="position:absolute;opacity:0;pointer-events:none;width:1px;height:1px;"
                            >
                                <option value=""></option>
                                <?php foreach ($options as $option) :
                                    if ($is_taxonomy) {
                                        $term = get_term_by('slug', $option, $taxonomy_name);
                                        $val  = $option;
                                    } else {
                                        $val  = $option;
                                    }
                                ?>
                                    <option value="<?php echo esc_attr($val); ?>" <?php selected($selected_val, $val); ?>><?php echo esc_html($val); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="reset" class="zp-variation-reset" style="display:none;">
                    <?php esc_html_e('Clear selection', 'ai-zippy'); ?>
                </button>
            </div>

            <!-- WC's variations script populates this -->
            <div class="single_variation_wrap">
                <div class="single_variation"></div>
                <div class="woocommerce-variation-add-to-cart variations_button woocommerce-variation-add-to-cart-disabled">
                    <?php self::renderQuantityStepper($product); ?>
                    <button type="submit" class="zp-add-btn single_add_to_cart_button button alt az-add-to-cart disabled wc-variation-selection-needed" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                        <span class="zp-add-btn__text"><?php esc_html_e('Add to Cart', 'ai-zippy'); ?></span>
                        <svg class="zp-add-btn__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <span class="zp-add-btn__spinner" aria-hidden="true"></span>
                    </button>
                    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>" />
                    <input type="hidden" name="variation_id" class="variation_id" value="0" />
                </div>
            </div>
        </form>
        <?php
    }

    /**
     * Decide how to render an attribute: color swatches, button swatches, or select.
     * Heuristic by name; can be overridden via filter.
     */
    private static function detectAttributeKind(string $name, bool $is_taxonomy): string
    {
        $lower = strtolower($name);
        $kind  = 'select';

        if (preg_match('/(color|colour)/', $lower)) {
            $kind = 'color';
        } elseif (preg_match('/(size|fit)/', $lower)) {
            $kind = 'button';
        }

        return apply_filters('ai_zippy_variation_attribute_kind', $kind, $name, $is_taxonomy);
    }

    private static function renderColorSwatches(string $attribute_name, array $options, bool $is_taxonomy, string $selected): void
    {
        $taxonomy = wc_attribute_taxonomy_name($attribute_name);

        foreach ($options as $option) {
            if ($is_taxonomy) {
                $term  = get_term_by('slug', $option, $taxonomy);
                $val   = $option;
                $label = $term ? $term->name : $option;
                $color = $term ? get_term_meta($term->term_id, 'product_attribute_color', true) : '';
            } else {
                $val   = $option;
                $label = $option;
                $color = '';
            }
            $color = $color ?: self::guessColorFromName($label);
            ?>
            <button
                type="button"
                class="zp-swatch zp-swatch--color <?php echo $selected === $val ? 'is-selected' : ''; ?>"
                data-value="<?php echo esc_attr($val); ?>"
                data-label="<?php echo esc_attr($label); ?>"
                role="radio"
                aria-checked="<?php echo $selected === $val ? 'true' : 'false'; ?>"
                aria-label="<?php echo esc_attr($label); ?>"
                title="<?php echo esc_attr($label); ?>"
            >
                <span class="zp-swatch__dot" style="background-color: <?php echo esc_attr($color); ?>;"></span>
            </button>
            <?php
        }
    }

    private static function renderButtonSwatches(string $attribute_name, array $options, bool $is_taxonomy, string $selected): void
    {
        $taxonomy = wc_attribute_taxonomy_name($attribute_name);

        foreach ($options as $option) {
            if ($is_taxonomy) {
                $term  = get_term_by('slug', $option, $taxonomy);
                $val   = $option;
                $label = $term ? $term->name : $option;
            } else {
                $val   = $option;
                $label = $option;
            }
            ?>
            <button
                type="button"
                class="zp-swatch zp-swatch--button <?php echo $selected === $val ? 'is-selected' : ''; ?>"
                data-value="<?php echo esc_attr($val); ?>"
                data-label="<?php echo esc_attr($label); ?>"
                role="radio"
                aria-checked="<?php echo $selected === $val ? 'true' : 'false'; ?>"
            >
                <?php echo esc_html($label); ?>
            </button>
            <?php
        }
    }

    /**
     * Best-effort color from common name (Red, Blue, Black). Used when an admin
     * hasn't set a swatch color via term meta. Returns "#888" for unknowns.
     */
    private static function guessColorFromName(string $name): string
    {
        $map = [
            'black' => '#000000', 'white' => '#ffffff', 'red' => '#e53935',
            'blue'  => '#1976d2', 'green' => '#43a047', 'yellow' => '#fdd835',
            'orange'=> '#fb8c00', 'purple'=> '#8e24aa', 'pink' => '#ec407a',
            'grey'  => '#757575', 'gray'  => '#757575', 'brown' => '#6d4c41',
            'beige' => '#d7c5a5', 'navy'  => '#1a237e',
        ];
        $key = strtolower(trim($name));
        return $map[$key] ?? '#888888';
    }

    /**
     * "Buy Now" button — adds the product to cart, redirects to /checkout.
     *
     * Implemented as a plain anchor with a query string. WC's add-to-cart
     * handler runs on `template_redirect` for any URL with `?add-to-cart=`,
     * adds the item, and the `redirect` query param sends the user to
     * checkout afterward.
     *
     * For variable products: Buy Now is hidden until a variation is chosen
     * (handled by JS — toggles `is-disabled` based on variation_id input).
     */
    private static function renderBuyNowButton(\WC_Product $product): void
    {
        if (!$product->is_purchasable() || !$product->is_in_stock()) {
            return;
        }

        $checkout_url = wc_get_checkout_url();
        $href         = add_query_arg([
            'add-to-cart' => $product->get_id(),
        ], $checkout_url);
        $is_variable  = $product->is_type('variable');
        ?>
        <a
            href="<?php echo esc_url($href); ?>"
            class="zp-buy-now <?php echo $is_variable ? 'is-disabled wc-variation-selection-needed' : ''; ?>"
            data-product-id="<?php echo (int) $product->get_id(); ?>"
            data-buy-now
        >
            <?php esc_html_e('Buy Now', 'ai-zippy'); ?>
        </a>
        <?php
    }

    /**
     * Trust badges row. Hardcoded for v1; configurable later via theme options
     * if any client wants to override. Filter `ai_zippy_product_trust_badges`
     * exposes the array so dev can override per-client without touching here.
     */
    private static function renderTrustBadges(): void
    {
        $defaults = [
            [
                'label' => __('Secure Checkout', 'ai-zippy'),
                'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
            ],
            [
                'label' => __('Fast Delivery', 'ai-zippy'),
                'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
            ],
        ];
        $badges = apply_filters('ai_zippy_product_trust_badges', $defaults);
        if (empty($badges)) {
            return;
        }

        echo '<div class="zp-trust">';
        foreach ($badges as $badge) {
            $icon  = $badge['icon']  ?? '';
            $label = $badge['label'] ?? '';
            echo '<div class="zp-trust__item">';
            if ($icon)  echo '<span class="zp-trust__icon">' . wp_kses($icon, self::svgAllowedHtml()) . '</span>';
            if ($label) echo '<span class="zp-trust__label">' . esc_html($label) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Allow-list for inline SVG icons in trust badges and similar hardcoded
     * markup. wp_kses_post() strips <svg> by default for XSS safety; we use
     * an explicit allow-list since the SVG strings are author-controlled.
     */
    private static function svgAllowedHtml(): array
    {
        // wp_kses lowercases all attribute keys, so allow-list keys must be
        // lowercase. `viewbox` matches the attribute the browser would use.
        $shared = [
            'class'           => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
            'stroke-linejoin' => true,
            'aria-hidden'     => true,
            'focusable'       => true,
        ];
        return [
            'svg' => array_merge($shared, [
                'xmlns'   => true,
                'width'   => true,
                'height'  => true,
                'viewbox' => true, // lowercase — wp_kses normalizes to lowercase
                'role'    => true,
            ]),
            'path'     => array_merge($shared, ['d' => true]),
            'rect'     => array_merge($shared, ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true]),
            'circle'   => array_merge($shared, ['cx' => true, 'cy' => true, 'r' => true]),
            'line'     => array_merge($shared, ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true]),
            'polyline' => array_merge($shared, ['points' => true]),
            'polygon'  => array_merge($shared, ['points' => true]),
            'g'        => $shared,
        ];
    }

    // -------------------------------------------------------------------------
    // [ai_zippy_product_tabs] — custom tabs (Description + Additional info)
    // -------------------------------------------------------------------------

    public static function renderTabs(): string
    {
        global $product;
        if (!$product instanceof \WC_Product) {
            $product = wc_get_product(get_queried_object_id());
        }
        if (!$product instanceof \WC_Product) {
            return '';
        }

        $description = $product->get_description();
        $attributes  = self::getDisplayableAttributes($product);

        $tabs = [];
        if ($description) {
            $tabs['description'] = [
                'label'   => __('Description', 'ai-zippy'),
                'content' => apply_filters('the_content', $description),
            ];
        }
        if (!empty($attributes)) {
            $tabs['additional'] = [
                'label'   => __('Additional Information', 'ai-zippy'),
                'content' => self::renderAttributesTable($attributes),
            ];
        }

        if (empty($tabs)) {
            return '';
        }

        ob_start();
        ?>
        <div class="zp-tabs" data-tabs>
            <div class="zp-tabs__nav" role="tablist">
                <?php $i = 0;
                foreach ($tabs as $key => $tab) :
                    $active = $i === 0; ?>
                    <button
                        type="button"
                        class="zp-tabs__tab <?php echo $active ? 'is-active' : ''; ?>"
                        role="tab"
                        aria-selected="<?php echo $active ? 'true' : 'false'; ?>"
                        aria-controls="zp-tab-<?php echo esc_attr($key); ?>"
                        data-tab="<?php echo esc_attr($key); ?>"
                    ><?php echo esc_html($tab['label']); ?></button>
                <?php $i++; endforeach; ?>
            </div>

            <?php $i = 0;
            foreach ($tabs as $key => $tab) :
                $active = $i === 0; ?>
                <div
                    id="zp-tab-<?php echo esc_attr($key); ?>"
                    class="zp-tabs__panel <?php echo $active ? 'is-active' : ''; ?>"
                    role="tabpanel"
                    data-panel="<?php echo esc_attr($key); ?>"
                    <?php if (!$active) echo 'hidden'; ?>
                >
                    <?php echo $tab['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            <?php $i++; endforeach; ?>
        </div>
        <?php

        return self::cleanShortcodeOutput(ob_get_clean());
    }

    private static function getDisplayableAttributes(\WC_Product $product): array
    {
        $rows = [];
        foreach ($product->get_attributes() as $attribute) {
            if (!$attribute->get_visible() || $attribute->get_variation()) {
                continue;
            }
            $name = wc_attribute_label($attribute->get_name(), $product);
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
                $value = is_array($terms) ? implode(', ', $terms) : '';
            } else {
                $value = implode(', ', $attribute->get_options());
            }
            if ($value !== '') {
                $rows[$name] = $value;
            }
        }

        // Surface weight + dimensions if filled
        if ($product->has_weight()) {
            $rows[__('Weight', 'ai-zippy')] = wc_format_weight($product->get_weight());
        }
        if ($product->has_dimensions()) {
            $rows[__('Dimensions', 'ai-zippy')] = wc_format_dimensions($product->get_dimensions(false));
        }

        return $rows;
    }

    private static function renderAttributesTable(array $rows): string
    {
        $html = '<table class="zp-attrs">';
        foreach ($rows as $name => $value) {
            $html .= '<tr><th>' . esc_html($name) . '</th><td>' . wp_kses_post($value) . '</td></tr>';
        }
        $html .= '</table>';
        return $html;
    }
}
