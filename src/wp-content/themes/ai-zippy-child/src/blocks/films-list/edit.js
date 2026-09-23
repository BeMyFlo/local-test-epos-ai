import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const films = attributes.films ?? [];

	const updateFilm = (index, patch) => {
		setAttributes({
			films: films.map((film, i) => (i === index ? { ...film, ...patch } : film)),
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
				{films.map((film, i) => (
					<PanelBody key={i} title={`Film ${i + 1}`} initialOpen={false}>
						<TextControl
							label="Tag"
							value={film.tag}
							onChange={(v) => updateFilm(i, { tag: v })}
						/>
						<TextControl
							label="Runtime"
							value={film.runtime}
							onChange={(v) => updateFilm(i, { runtime: v })}
						/>
						<TextControl
							label="Title"
							value={film.title}
							onChange={(v) => updateFilm(i, { title: v })}
						/>
						<TextControl
							label="Meta (comma-separated)"
							value={Array.isArray(film.meta) ? film.meta.join(', ') : ''}
							onChange={(v) => updateFilm(i, { meta: v.split(',').map((s) => s.trim()).filter(Boolean) })}
						/>
						<TextareaControl
							label="Desc"
							help="Inline markup kept: <br>"
							value={film.desc}
							onChange={(v) => updateFilm(i, { desc: v })}
						/>
						<TextControl
							label="Video URL"
							help="Empty shows the “⚠ No video yet” chip and the modal’s “No video added yet” state."
							value={film.videoUrl}
							onChange={(v) => updateFilm(i, { videoUrl: v })}
						/>
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={['image']}
								value={film.imgId}
								onSelect={(media) => updateFilm(i, { imgId: media.id, imgUrl: media.url })}
								render={({ open }) => (
									<Button variant="secondary" onClick={open}>Choose image</Button>
								)}
							/>
						</MediaUploadCheck>
						{film.imgUrl && (
							<Button
								variant="link"
								isDestructive
								onClick={() => updateFilm(i, { imgId: 0, imgUrl: '' })}
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
