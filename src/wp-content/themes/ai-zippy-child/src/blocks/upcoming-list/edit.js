import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const events = attributes.events ?? [];

	const updateEvent = (index, patch) => {
		setAttributes({
			events: events.map((event, i) => (i === index ? { ...event, ...patch } : event)),
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
				{events.map((event, i) => (
					<PanelBody key={i} title={`Event ${i + 1}`} initialOpen={false}>
						<TextControl
							label="Badge"
							value={event.badge}
							onChange={(v) => updateEvent(i, { badge: v })}
						/>
						<TextControl
							label="Day"
							value={event.day}
							onChange={(v) => updateEvent(i, { day: v })}
						/>
						<TextControl
							label="Month"
							value={event.month}
							onChange={(v) => updateEvent(i, { month: v })}
						/>
						<TextControl
							label="Title"
							value={event.title}
							onChange={(v) => updateEvent(i, { title: v })}
						/>
						<TextControl
							label="Meta (comma-separated)"
							value={Array.isArray(event.meta) ? event.meta.join(', ') : ''}
							onChange={(v) => updateEvent(i, { meta: v.split(',').map((s) => s.trim()).filter(Boolean) })}
						/>
						<TextareaControl
							label="Desc"
							value={event.desc}
							onChange={(v) => updateEvent(i, { desc: v })}
						/>
						<TextControl
							label="Price"
							help="Parsed live from the card text by the ticket modal."
							value={event.price}
							onChange={(v) => updateEvent(i, { price: v })}
						/>
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={['image']}
								value={event.videoPosterId}
								onSelect={(media) => updateEvent(i, { videoPosterId: media.id, videoPosterUrl: media.url })}
								render={({ open }) => (
									<Button variant="secondary" onClick={open}>Choose poster</Button>
								)}
							/>
						</MediaUploadCheck>
						{event.videoPosterUrl && (
							<Button
								variant="link"
								isDestructive
								onClick={() => updateEvent(i, { videoPosterId: 0, videoPosterUrl: '' })}
							>
								Reset to default image
							</Button>
						)}
					</PanelBody>
				))}
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
