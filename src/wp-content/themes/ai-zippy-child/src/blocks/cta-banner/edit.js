import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextareaControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	return (
		<>
			<InspectorControls>
				<PanelBody title="Content">
					<TextControl
						label="Heading"
						value={attributes.heading}
						onChange={(v) => setAttributes({ heading: v })}
					/>
					<TextareaControl
						label="Text"
						value={attributes.text}
						onChange={(v) => setAttributes({ text: v })}
					/>
					<TextControl
						label="Button text"
						value={attributes.btnLabel}
						onChange={(v) => setAttributes({ btnLabel: v })}
					/>
					<TextControl
						label="Button URL"
						value={attributes.btnUrl}
						onChange={(v) => setAttributes({ btnUrl: v })}
					/>
				</PanelBody>
				<PanelBody title="Variant" initialOpen={false}>
					<ToggleControl
						label="Soft band variant — off renders the plain wrap-tight variant used on the events page"
						checked={attributes.soft}
						onChange={(v) => setAttributes({ soft: v })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
