import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const steps = attributes.steps ?? [];

	const updateStep = (index, patch) => {
		setAttributes({
			steps: steps.map((step, i) => (i === index ? { ...step, ...patch } : step)),
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
						label="Intro"
						value={attributes.intro}
						onChange={(v) => setAttributes({ intro: v })}
					/>
				</PanelBody>
				<PanelBody title="Image" initialOpen={false}>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={['image']}
							value={attributes.imgId}
							onSelect={(media) => setAttributes({ imgId: media.id, imgUrl: media.url })}
							render={({ open }) => (
								<Button variant="secondary" onClick={open}>Choose image</Button>
							)}
						/>
					</MediaUploadCheck>
					{attributes.imgUrl && (
						<Button
							variant="link"
							isDestructive
							onClick={() => setAttributes({ imgId: 0, imgUrl: '' })}
						>
							Reset to default image
						</Button>
					)}
				</PanelBody>
				{steps.map((step, i) => (
					<PanelBody key={i} title={`Step ${i + 1}`} initialOpen={false}>
						<TextControl
							label="Number"
							value={step.n}
							onChange={(v) => updateStep(i, { n: v })}
						/>
						<TextControl
							label="Heading"
							value={step.heading}
							onChange={(v) => updateStep(i, { heading: v })}
						/>
						<TextareaControl
							label="Text"
							value={step.text}
							onChange={(v) => updateStep(i, { text: v })}
						/>
					</PanelBody>
				))}
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
