import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	return (
		<>
			<InspectorControls>
				<PanelBody title="Hero image" initialOpen={false}>
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
				<PanelBody title="Content">
					<TextControl
						label="Eyebrow"
						value={attributes.eyebrow}
						onChange={(v) => setAttributes({ eyebrow: v })}
					/>
					<TextControl
						label="Title — part 1"
						value={attributes.titlePart1}
						onChange={(v) => setAttributes({ titlePart1: v })}
					/>
					<TextControl
						label="Title — accent 1"
						value={attributes.titleAccent1}
						onChange={(v) => setAttributes({ titleAccent1: v })}
					/>
					<TextControl
						label="Title — part 2"
						value={attributes.titlePart2}
						onChange={(v) => setAttributes({ titlePart2: v })}
					/>
					<TextControl
						label="Title — accent 2"
						value={attributes.titleAccent2}
						onChange={(v) => setAttributes({ titleAccent2: v })}
					/>
					<TextareaControl
						label="Lead"
						value={attributes.lead}
						onChange={(v) => setAttributes({ lead: v })}
					/>
				</PanelBody>
				<PanelBody title="Buttons" initialOpen={false}>
					<TextControl
						label="Primary button text"
						value={attributes.primaryBtnText}
						onChange={(v) => setAttributes({ primaryBtnText: v })}
					/>
					<TextControl
						label="Primary button URL"
						value={attributes.primaryBtnUrl}
						onChange={(v) => setAttributes({ primaryBtnUrl: v })}
					/>
					<TextControl
						label="Ghost button text"
						value={attributes.ghostBtnText}
						onChange={(v) => setAttributes({ ghostBtnText: v })}
					/>
					<TextControl
						label="Ghost button URL"
						value={attributes.ghostBtnUrl}
						onChange={(v) => setAttributes({ ghostBtnUrl: v })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
