import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
	const { heading, subheading, ctaLabel, submitText, successMessage } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Form Settings', 'ai-zippy')} initialOpen={true}>
					<TextControl
						label={__('Heading', 'ai-zippy')}
						value={heading}
						onChange={(val) => setAttributes({ heading: val })}
					/>
					<TextControl
						label={__('Subheading', 'ai-zippy')}
						value={subheading}
						onChange={(val) => setAttributes({ subheading: val })}
					/>
					<TextControl
						label={__('CTA Label', 'ai-zippy')}
						value={ctaLabel}
						onChange={(val) => setAttributes({ ctaLabel: val })}
					/>
					<TextControl
						label={__('Submit Button Text', 'ai-zippy')}
						value={submitText}
						onChange={(val) => setAttributes({ submitText: val })}
					/>
					<TextControl
						label={__('Success Message', 'ai-zippy')}
						value={successMessage}
						onChange={(val) => setAttributes({ successMessage: val })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/course-enquiry-form"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
