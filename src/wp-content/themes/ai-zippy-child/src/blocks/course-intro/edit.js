import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
	const { heading, description, suppliesText } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Content Settings', 'ai-zippy')} initialOpen={true}>
					<TextControl
						label={__('Heading', 'ai-zippy')}
						value={heading}
						onChange={(val) => setAttributes({ heading: val })}
					/>
					<TextareaControl
						label={__('Description', 'ai-zippy')}
						value={description}
						onChange={(val) => setAttributes({ description: val })}
					/>
					<TextareaControl
						label={__('Supplies Text', 'ai-zippy')}
						value={suppliesText}
						onChange={(val) => setAttributes({ suppliesText: val })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/course-intro"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
