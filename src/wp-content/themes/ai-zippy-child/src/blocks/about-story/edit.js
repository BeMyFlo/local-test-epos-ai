import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const intro = attributes.introBlock ?? { heading: '', paras: [] };
	const storyBlocks = attributes.storyBlocks ?? [];

	const updateIntro = (patch) => {
		setAttributes({ introBlock: { ...intro, ...patch } });
	};

	const updateIntroPara = (index, value) => {
		updateIntro({ paras: intro.paras.map((para, i) => (i === index ? value : para)) });
	};

	const updateStoryBlock = (index, patch) => {
		setAttributes({
			storyBlocks: storyBlocks.map((sb, i) => (i === index ? { ...sb, ...patch } : sb)),
		});
	};

	const updateStoryPara = (blockIndex, paraIndex, value) => {
		const paras = storyBlocks[blockIndex].paras.map((para, i) => (i === paraIndex ? value : para));
		updateStoryBlock(blockIndex, { paras });
	};

	const updateSubCard = (blockIndex, cardIndex, patch) => {
		const subCards = storyBlocks[blockIndex].subCards.map((sc, i) => (i === cardIndex ? { ...sc, ...patch } : sc));
		updateStoryBlock(blockIndex, { subCards });
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
				<PanelBody title="Story image" initialOpen={false}>
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
				<PanelBody title="Intro block (split)" initialOpen={false}>
					<TextControl
						label="Heading"
						value={intro.heading}
						onChange={(v) => updateIntro({ heading: v })}
					/>
					{intro.paras.map((para, i) => (
						<TextareaControl
							key={i}
							label={`Paragraph ${i + 1}`}
							value={para}
							onChange={(v) => updateIntroPara(i, v)}
						/>
					))}
				</PanelBody>
				{storyBlocks.map((sb, i) => (
					<PanelBody key={i} title={`Story block ${i + 1}`} initialOpen={false}>
						<TextControl
							label="Heading"
							value={sb.heading}
							onChange={(v) => updateStoryBlock(i, { heading: v })}
						/>
						{(sb.paras ?? []).map((para, j) => (
							<TextareaControl
								key={j}
								label={`Paragraph ${j + 1}`}
								value={para}
								onChange={(v) => updateStoryPara(i, j, v)}
							/>
						))}
						{(sb.subCards ?? []).map((sc, j) => (
							<PanelBody key={j} title={`Sub-card ${j + 1}`} initialOpen={false}>
								<TextControl
									label="Heading"
									value={sc.heading}
									onChange={(v) => updateSubCard(i, j, { heading: v })}
								/>
								<TextareaControl
									label="Text"
									value={sc.text}
									onChange={(v) => updateSubCard(i, j, { text: v })}
								/>
							</PanelBody>
						))}
					</PanelBody>
				))}
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
