import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
	const { heading, studios } = attributes;
	const blockProps = useBlockProps();

	const updateStudio = (index, field, value) => {
		const updated = [...studios];
		updated[index] = { ...updated[index], [field]: value };
		setAttributes({ studios: updated });
	};

	const addStudio = () => {
		setAttributes({
			studios: [
				...studios,
				{ name: '', phone: '', address: '', hours: '' },
			],
		});
	};

	const removeStudio = (index) => {
		const updated = studios.filter((_, i) => i !== index);
		setAttributes({ studios: updated });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Studios Settings', 'ai-zippy')} initialOpen={true}>
					<TextControl
						label={__('Heading', 'ai-zippy')}
						value={heading}
						onChange={(val) => setAttributes({ heading: val })}
					/>
				</PanelBody>

				{studios.map((studio, index) => (
					<PanelBody
						key={index}
						title={studio.name || __(`Studio ${index + 1}`, 'ai-zippy')}
						initialOpen={false}
					>
						<TextControl
							label={__('Studio Name', 'ai-zippy')}
							value={studio.name}
							onChange={(val) => updateStudio(index, 'name', val)}
						/>
						<TextControl
							label={__('Phone', 'ai-zippy')}
							value={studio.phone}
							onChange={(val) => updateStudio(index, 'phone', val)}
						/>
						<TextareaControl
							label={__('Address', 'ai-zippy')}
							value={studio.address}
							onChange={(val) => updateStudio(index, 'address', val)}
							help={__('Use new lines to separate address parts.', 'ai-zippy')}
						/>
						<TextareaControl
							label={__('Opening Hours', 'ai-zippy')}
							value={studio.hours}
							onChange={(val) => updateStudio(index, 'hours', val)}
							help={__('Use new lines to separate each day.', 'ai-zippy')}
						/>
						<Button
							variant="secondary"
							isDestructive
							onClick={() => removeStudio(index)}
							style={{ marginTop: '8px' }}
						>
							{__('Remove Studio', 'ai-zippy')}
						</Button>
					</PanelBody>
				))}

				<PanelBody>
					<Button variant="primary" onClick={addStudio}>
						{__('Add Studio', 'ai-zippy')}
					</Button>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/contact-studios"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
