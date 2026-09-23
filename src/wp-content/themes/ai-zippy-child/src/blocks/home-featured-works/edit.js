import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const works = attributes.works ?? [];

	const updateWork = (index, patch) => {
		setAttributes({
			works: works.map((work, i) => (i === index ? { ...work, ...patch } : work)),
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
				</PanelBody>
				{works.map((work, i) => (
					<PanelBody key={i} title={`Work ${i + 1}`} initialOpen={false}>
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={['image']}
								value={work.imgId}
								onSelect={(media) => updateWork(i, { imgId: media.id, imgUrl: media.url })}
								render={({ open }) => (
									<Button variant="secondary" onClick={open}>Choose image</Button>
								)}
							/>
						</MediaUploadCheck>
						{work.imgUrl && (
							<Button
								variant="link"
								isDestructive
								onClick={() => updateWork(i, { imgId: 0, imgUrl: '' })}
							>
								Reset to default image
							</Button>
						)}
						<TextControl
							label="Caption heading"
							value={work.capHeading}
							onChange={(v) => updateWork(i, { capHeading: v })}
						/>
						<TextareaControl
							label="Caption text"
							value={work.capText}
							onChange={(v) => updateWork(i, { capText: v })}
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
