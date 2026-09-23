import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const ICON_VARIANTS = [
	{ value: 'i1', label: 'i1 (aqua → sky)' },
	{ value: 'i2', label: 'i2 (cream → aqua)' },
	{ value: 'i3', label: 'i3 (yellow → cream)' },
];

export default function Edit({ attributes, setAttributes, name }) {
	const cards = attributes.cards ?? [];

	const updateCard = (index, patch) => {
		setAttributes({
			cards: cards.map((card, i) => (i === index ? { ...card, ...patch } : card)),
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
				{cards.map((card, i) => (
					<PanelBody key={i} title={`Card ${i + 1}`} initialOpen={false}>
						<TextControl
							label="Emoji"
							value={card.emoji}
							onChange={(v) => updateCard(i, { emoji: v })}
						/>
						<SelectControl
							label="Icon variant"
							value={card.iconVariant}
							options={ICON_VARIANTS}
							onChange={(v) => updateCard(i, { iconVariant: v })}
						/>
						<TextControl
							label="Heading"
							value={card.heading}
							onChange={(v) => updateCard(i, { heading: v })}
						/>
						<TextareaControl
							label="Text"
							value={card.text}
							onChange={(v) => updateCard(i, { text: v })}
						/>
					</PanelBody>
				))}
				<PanelBody title="Button" initialOpen={false}>
					<TextControl
						label="Label"
						value={attributes.btnText}
						onChange={(v) => setAttributes({ btnText: v })}
					/>
					<TextControl
						label="URL"
						value={attributes.btnUrl}
						onChange={(v) => setAttributes({ btnUrl: v })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
