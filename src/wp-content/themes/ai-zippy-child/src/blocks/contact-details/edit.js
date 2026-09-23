import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const items = attributes.items ?? [];

	const updateItem = (index, patch) => {
		setAttributes({
			items: items.map((item, i) => (i === index ? { ...item, ...patch } : item)),
		});
	};

	const updateLine = (itemIndex, lineIndex, value) => {
		const lines = (items[itemIndex].lines ?? []).map((line, i) => (i === lineIndex ? value : line));
		updateItem(itemIndex, { lines });
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
				{items.map((item, i) => (
					<PanelBody key={i} title={`Item ${i + 1}`} initialOpen={false}>
						<TextControl
							label="Icon"
							value={item.icon}
							onChange={(v) => updateItem(i, { icon: v })}
						/>
						<TextControl
							label="Heading"
							help="Renders inside <b> (only when non-empty)"
							value={item.heading}
							onChange={(v) => updateItem(i, { heading: v })}
						/>
						{(item.lines ?? []).map((line, li) => (
							<TextControl
								key={li}
								label={`Line ${li + 1}`}
								value={line}
								onChange={(v) => updateLine(i, li, v)}
							/>
						))}
					</PanelBody>
				))}
				<PanelBody title="Map" initialOpen={false}>
					<TextControl
						label="Embed URL"
						help="Google Maps embed URL (…&output=embed)"
						value={attributes.mapUrl}
						onChange={(v) => setAttributes({ mapUrl: v })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
