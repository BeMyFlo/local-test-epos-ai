import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const tiers = attributes.tiers ?? [];
	const subjectOptions = attributes.subjectOptions ?? [];

	const updateTier = (index, patch) => {
		setAttributes({
			tiers: tiers.map((tier, i) => (i === index ? { ...tier, ...patch } : tier)),
		});
	};

	const updateSubjectOption = (index, value) => {
		setAttributes({
			subjectOptions: subjectOptions.map((option, i) => (i === index ? value : option)),
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
				{tiers.map((tier, i) => (
					<PanelBody key={i} title={`Tier ${i + 1}`} initialOpen={false}>
						<TextControl
							label="Tag"
							value={tier.tag}
							onChange={(v) => updateTier(i, { tag: v })}
						/>
						<TextControl
							label="Price"
							value={tier.price}
							onChange={(v) => updateTier(i, { price: v })}
						/>
						<TextControl
							label="Runtime"
							value={tier.runtime}
							onChange={(v) => updateTier(i, { runtime: v })}
						/>
						<TextareaControl
							label="Text"
							help="Inline markup kept: <div> <span style> <b> <i> <br> (Tier 1 uses the mockup's nested span markup)"
							value={tier.text}
							onChange={(v) => updateTier(i, { text: v })}
						/>
					</PanelBody>
				))}
				<PanelBody title="Note" initialOpen={false}>
					<TextareaControl
						label="Note"
						value={attributes.note}
						onChange={(v) => setAttributes({ note: v })}
					/>
				</PanelBody>
				<PanelBody title="Form" initialOpen={false}>
					<TextControl
						label="Form heading"
						value={attributes.formHeading}
						onChange={(v) => setAttributes({ formHeading: v })}
					/>
					<TextControl
						label="Form sub"
						value={attributes.formSub}
						onChange={(v) => setAttributes({ formSub: v })}
					/>
					<TextControl
						label="Subject option 1"
						value={subjectOptions[0]}
						onChange={(v) => updateSubjectOption(0, v)}
					/>
					<TextControl
						label="Subject option 2"
						value={subjectOptions[1]}
						onChange={(v) => updateSubjectOption(1, v)}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
