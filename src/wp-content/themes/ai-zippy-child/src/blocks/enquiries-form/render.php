<?php

/**
 * Server-side render — `ai-zippy/enquiries-form`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L928-951
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (events-process precedent). The
 * form is component-driven — form.enquiry/.field/.form-ok live in
 * _components.scss (mockup L133-135 + L163-164, ported at S2a) and
 * _enquiries-form.scss ships empty permanently (P9-D6).
 *
 * Authoring layer stripped (block-map §6.2 / D5): the 6 in-place editing
 * attributes on L929-949 are removed.
 *
 * P9-D4: form chrome is static markup — labels, placeholders, the
 * `— Select a subject —` empty option, the submit label and the `.form-ok`
 * message (incl. ✅ emoji and the straight ASCII apostrophes, unlike
 * film-tiers' curly `’`) are pasted verbatim; only the select's option
 * body is attribute-driven.
 * P9-D5: the select body renders in the mockup's DOM order — the 3
 * standalone subject options first, then the `<optgroup>` wrapping the 2
 * feature film options.
 * P9-D3: every editable field is plain text → `esc_html()` only (the
 * optgroup label through `esc_attr()`); no kses helper, and with no helper
 * function there is no `function_exists` guard (P7-D3 precedent).
 * P9-D2: the inner form ids (`enquiryForm`, `enq*`, `enquiryOk`) are clone
 * markup kept verbatim — B13 targets them (films-list P4-D10 precedent);
 * the wrapper itself carries no hardcoded id (anchor UI only).
 * The film option 2 "$400,000" copy repeats the P3-D5 price mismatch —
 * cloned verbatim (the mockup is the specification).
 *
 * B13 lives in this block's view.js (block-map §2) — no other listeners in
 * L928-951.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow          = (string) ($attributes['eyebrow'] ?? '');
$title            = (string) ($attributes['title'] ?? '');
$sub              = (string) ($attributes['sub'] ?? '');
$subject_options  = (array) ($attributes['subjectOptions'] ?? []);
$film_group_label = (string) ($attributes['filmGroupLabel'] ?? '');
$film_options     = (array) ($attributes['filmOptions'] ?? []);

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<p class="section-sub"><?php echo esc_html($sub); ?></p>
	<form class="enquiry" id="enquiryForm">
		<div class="field"><label for="enqName">Your Name</label><input type="text" id="enqName" required="" placeholder="Full name" value=""></div>
		<div class="field"><label for="enqEmail">Email</label><input type="email" id="enqEmail" required="" placeholder="you@company.com" value=""></div>
		<div class="field"><label for="enqSubject">Subject</label>
			<select id="enqSubject" required="">
				<option value="" selected="">— Select a subject —</option>
				<?php foreach ($subject_options as $option) : ?>
					<option><?php echo esc_html((string) $option); ?></option>
				<?php endforeach; ?>
				<optgroup label="<?php echo esc_attr($film_group_label); ?>">
					<?php foreach ($film_options as $option) : ?>
						<option><?php echo esc_html((string) $option); ?></option>
					<?php endforeach; ?>
				</optgroup>
			</select>
		</div>
		<div class="field"><label for="enqMsg">Message</label><textarea id="enqMsg" rows="5" required="" placeholder="Tell us about your project…"></textarea></div>
		<div><button type="submit" class="btn btn-primary">Send Enquiry</button></div>
		<div class="form-ok" id="enquiryOk">✅ <span>Thank you! Your enquiry has been received. We'll be in touch shortly.</span></div>
	</form>
</section>
