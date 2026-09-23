import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const products = attributes.products ?? [];

	const updateProduct = (index, patch) => {
		setAttributes({
			products: products.map((product, i) => (i === index ? { ...product, ...patch } : product)),
		});
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Content">
					<TextControl
						label="Eyebrow"
						value={attributes.eyebrow}
						onChange={(v) => setAttributes({ eyebrow: v })}
					/>
					<TextControl
						label="Title"
						value={attributes.title}
						onChange={(v) => setAttributes({ title: v })}
					/>
					<TextareaControl
						label="Sub"
						value={attributes.sub}
						onChange={(v) => setAttributes({ sub: v })}
					/>
				</PanelBody>
				{products.map((product, i) => (
					<PanelBody key={i} title={`Product ${i + 1}`} initialOpen={false}>
						<TextControl
							label="Name"
							value={product.name}
							onChange={(v) => updateProduct(i, { name: v })}
						/>
						<TextControl
							label="Price"
							value={product.price}
							onChange={(v) => updateProduct(i, { price: v })}
						/>
						<TextControl
							label="Size"
							value={product.size}
							onChange={(v) => updateProduct(i, { size: v })}
						/>
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={['image']}
								value={product.imgId}
								onSelect={(media) => updateProduct(i, { imgId: media.id, imgUrl: media.url })}
								render={({ open }) => (
									<Button variant="secondary" onClick={open}>Choose image</Button>
								)}
							/>
						</MediaUploadCheck>
						{product.imgUrl && (
							<Button
								variant="link"
								isDestructive
								onClick={() => updateProduct(i, { imgId: 0, imgUrl: '' })}
							>
								Reset to default image
							</Button>
						)}
					</PanelBody>
				))}
				<PanelBody title="Custom order box" initialOpen={false}>
					<TextControl
						label="Eyebrow"
						value={attributes.customEyebrow}
						onChange={(v) => setAttributes({ customEyebrow: v })}
					/>
					<TextControl
						label="Title"
						value={attributes.customTitle}
						onChange={(v) => setAttributes({ customTitle: v })}
					/>
					<TextareaControl
						label="Sub"
						help="Inline markup kept: <b>"
						value={attributes.customSub}
						onChange={(v) => setAttributes({ customSub: v })}
					/>
					<TextareaControl
						label="Lock message"
						help="Inline markup kept: <b>"
						value={attributes.lockMsg}
						onChange={(v) => setAttributes({ lockMsg: v })}
					/>
					<TextareaControl
						label="Unlock message"
						value={attributes.unlockMsg}
						onChange={(v) => setAttributes({ unlockMsg: v })}
					/>
					<TextareaControl
						label="Product options"
						help="One option per line (renders in the custom-order select)."
						value={Array.isArray(attributes.productOptions) ? attributes.productOptions.join('\n') : ''}
						onChange={(v) => setAttributes({ productOptions: v.split('\n').map((s) => s.trim()).filter(Boolean) })}
					/>
				</PanelBody>
				<PanelBody title="Upload fields" initialOpen={false}>
					<TextControl
						label="Image label"
						value={attributes.imageLabel}
						onChange={(v) => setAttributes({ imageLabel: v })}
					/>
					<TextControl
						label="Text label"
						value={attributes.textLabel}
						onChange={(v) => setAttributes({ textLabel: v })}
					/>
					<TextControl
						label="Text placeholder"
						value={attributes.textPlaceholder}
						onChange={(v) => setAttributes({ textPlaceholder: v })}
					/>
					<TextControl
						label="Submit label"
						value={attributes.submitLabel}
						onChange={(v) => setAttributes({ submitLabel: v })}
					/>
					<TextareaControl
						label="OK message"
						value={attributes.okMsg}
						onChange={(v) => setAttributes({ okMsg: v })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
