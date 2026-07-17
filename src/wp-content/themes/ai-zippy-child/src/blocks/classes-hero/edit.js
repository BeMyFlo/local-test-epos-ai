import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
	const { heading, sectionTitle } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Content Settings', 'ai-zippy')} initialOpen={true}>
					<TextControl
						label={__('Section Title', 'ai-zippy')}
						value={sectionTitle}
						onChange={(val) => setAttributes({ sectionTitle: val })}
					/>
					<TextControl
						label={__('Heading (use \\n for line break)', 'ai-zippy')}
						value={heading}
						onChange={(val) => setAttributes({ heading: val })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/classes-hero"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
