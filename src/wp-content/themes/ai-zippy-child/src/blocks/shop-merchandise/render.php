<?php

/**
 * Server-side render — `ai-zippy/shop-merchandise`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L618-826
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (films-list precedent); product
 * grid + custom-order box styles live in src/scss/sections/
 * _shop-merchandise.scss (mockup <style> L117-132 + L136-141); the `.field`
 * controls (L133-135) already live in _components.scss; `.btn/.form-ok`
 * likewise. One block for the whole page section (block-map §2, D10).
 *
 * Authoring layer stripped (block-map §6.2 / D5): every in-place editing
 * attribute, the image-editing class + upload-hint title on every product
 * `<img>`, the `<!-- CUSTOM ORDER -->` comment, and the §6.3 editing residue
 * — products 4/9 render one clean `<h3>` with the real title (the empty
 * heading + inline-styled duplicate at L661/L721 are dropped). Base64 `src`
 * payloads resolve through `ai_zippy_child_img('product-NN.jpg')` (S5
 * sideloads them into the Media Library).
 *
 * P5-D1: block-map §2's "custom-order copy" = customEyebrow/customTitle/
 * customSub; "upload fields copy" = imageLabel/textLabel/textPlaceholder/
 * submitLabel/okMsg. Static chrome stays static: the Product/Quantity
 * labels, the `Order` button label (×14), the ⚠️/✅/🎉 emoji prefixes (they
 * sit outside the mockup's editable spans).
 * P5-D2: no `imgAlt` attribute — every mockup product `alt` is byte-identical
 * to its `name`, so `alt` derives from `name`.
 * P5-D3: the select's `selected=""` marker (mockup L800, option 13
 * "Notebook Sets (Boxed) — $55") is hardcoded at loop index 12.
 * Kept verbatim: the inline styles on #lockMsg (`display: flex;`),
 * #unlockMsg (`display:none`) and #customPreview
 * (`display:none;margin-top:12px`); the `id="shopGrid"`/`id="customBox"`
 * ids; the label without `for=` on "Your Custom Image"; the qty inputs with
 * no id/name (aria-label per ui-spec §11).
 *
 * customSub/lockMsg render through the guarded `shop_merchandise_inline_html()`
 * kses helper so their `<b>` markup survives editor round-trips (P2-D2 /
 * P3-D3 / P4-D3 precedent); unlockMsg/okMsg are plain and stay `esc_html()`.
 *
 * B1-B4 live in this block's view.js (block-map §2). No-JS defaults are
 * already correct in the markup: #lockMsg visible, #unlockMsg/#customOk
 * hidden, .custom-upload locked, #customPreview hidden.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('shop_merchandise_inline_html')) :
	/**
	 * Minimal inline allowlist for the editable custom-order copy (P5-D1).
	 *
	 * customSub and lockMsg carry the mockup's `<b>` accents (L796/L808);
	 * esc_html() would strip them, and the i/em/b/strong/br/div/span[style]
	 * set keeps any later inline accents intact across editor round-trips.
	 *
	 * @param string $text Attribute value.
	 * @return string Sanitized HTML with div/span[style]/i/em/b/strong/br preserved.
	 */
	function shop_merchandise_inline_html(string $text): string
	{
		return wp_kses($text, [
			'i'      => [],
			'em'     => [],
			'b'      => [],
			'strong' => [],
			'br'     => [],
			'div'    => [],
			'span'   => ['style' => []],
		]);
	}
endif;

$eyebrow          = (string) ($attributes['eyebrow'] ?? '');
$title            = (string) ($attributes['title'] ?? '');
$sub              = (string) ($attributes['sub'] ?? '');
$products         = (array) ($attributes['products'] ?? []);

$custom_eyebrow   = (string) ($attributes['customEyebrow'] ?? '');
$custom_title     = (string) ($attributes['customTitle'] ?? '');
$custom_sub       = (string) ($attributes['customSub'] ?? '');
$product_options  = (array) ($attributes['productOptions'] ?? []);
$lock_msg         = (string) ($attributes['lockMsg'] ?? '');
$unlock_msg       = (string) ($attributes['unlockMsg'] ?? '');

$image_label      = (string) ($attributes['imageLabel'] ?? '');
$text_label       = (string) ($attributes['textLabel'] ?? '');
$text_placeholder = (string) ($attributes['textPlaceholder'] ?? '');
$submit_label     = (string) ($attributes['submitLabel'] ?? '');
$ok_msg           = (string) ($attributes['okMsg'] ?? '');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<p class="section-sub"><?php echo esc_html($sub); ?></p>

	<div class="shop-grid" id="shopGrid">
		<?php foreach ($products as $product) : ?>
			<?php
			$name    = (string) ($product['name'] ?? '');
			$price   = (string) ($product['price'] ?? '');
			$size    = (string) ($product['size'] ?? '');
			$img_src = !empty($product['imgUrl']) ? (string) $product['imgUrl'] : ai_zippy_child_img((string) ($product['imgFile'] ?? ''));
			?>
			<div class="product">
				<img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($name); ?>">
				<div class="pbody">
					<h3><?php echo esc_html($name); ?></h3>
					<div class="pmeta">
						<span class="price"><?php echo esc_html($price); ?></span>
						<span class="size"><?php echo esc_html($size); ?></span>
					</div>
					<div class="qty-row">
						<input type="number" min="1" value="1" aria-label="Quantity for <?php echo esc_attr($name); ?>">
						<button class="btn btn-primary order-btn">Order</button>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="custom-box" id="customBox">
		<span class="eyebrow"><?php echo esc_html($custom_eyebrow); ?></span>
		<h2 class="section-title"><?php echo esc_html($custom_title); ?></h2>
		<p class="section-sub"><?php echo shop_merchandise_inline_html($custom_sub); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses()'d inside. ?></p>

		<div class="custom-controls">
			<div class="field">
				<label for="customProduct">Product</label>
				<select id="customProduct">
					<?php foreach ($product_options as $i => $option) : ?>
						<option<?php echo 12 === $i ? ' selected=""' : ''; ?>><?php echo esc_html((string) $option); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="field">
				<label for="customQty">Quantity</label>
				<input type="number" id="customQty" min="1" value="1">
			</div>
		</div>

		<div class="lock-msg" id="lockMsg" style="display: flex;">⚠️ <span><?php echo shop_merchandise_inline_html($lock_msg); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses()'d inside. ?></span></div>
		<div class="unlock-msg" id="unlockMsg" style="display:none">✅ <span><?php echo esc_html($unlock_msg); ?></span></div>

		<div class="custom-upload locked" id="customUpload">
			<div class="field">
				<label><?php echo esc_html($image_label); ?></label>
				<input type="file" id="customImgInput" accept="image/*">
				<img id="customPreview" class="upload-preview" alt="Custom image preview" style="display:none;margin-top:12px">
			</div>
			<div class="field">
				<label for="customText"><?php echo esc_html($text_label); ?></label>
				<textarea id="customText" rows="3" placeholder="<?php echo esc_attr($text_placeholder); ?>"></textarea>
			</div>
			<div><button class="btn btn-primary" id="customSubmit"><?php echo esc_html($submit_label); ?></button></div>
			<div class="form-ok" id="customOk">🎉 <span><?php echo esc_html($ok_msg); ?></span></div>
		</div>
	</div>
</section>
