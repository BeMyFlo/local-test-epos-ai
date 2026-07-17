import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
	const { galleryTitle } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Gallery Settings', 'ai-zippy')} initialOpen={true}>
					<TextControl
						label={__('Gallery Title', 'ai-zippy')}
						value={galleryTitle}
						onChange={(val) => setAttributes({ galleryTitle: val })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/classes-detail"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
