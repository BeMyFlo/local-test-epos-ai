import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { heading = '', subheading = '', submitText = '', successMessage = '', placeholders = {}, labels = {} } = attributes;
  const blockProps = useBlockProps();
  const updatePlaceholder = (key, value) => setAttributes({ placeholders: { ...placeholders, [key]: value } });
  const updateLabel = (key, value) => setAttributes({ labels: { ...labels, [key]: value } });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Form Content', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Heading', 'ai-zippy')} value={heading} onChange={(value) => setAttributes({ heading: value })} />
          <TextControl label={__('Subheading', 'ai-zippy')} value={subheading} onChange={(value) => setAttributes({ subheading: value })} />
          <TextControl label={__('Submit Button', 'ai-zippy')} value={submitText} onChange={(value) => setAttributes({ submitText: value })} />
          <TextControl label={__('Success Message', 'ai-zippy')} value={successMessage} onChange={(value) => setAttributes({ successMessage: value })} />
        </PanelBody>
        <PanelBody title={__('Field Placeholders', 'ai-zippy')} initialOpen={false}>
          {['name', 'phone', 'email', 'childAge', 'studio', 'programType', 'message'].map((key) => (
            <TextControl key={key} label={key} value={placeholders[key] || ''} onChange={(value) => updatePlaceholder(key, value)} />
          ))}
        </PanelBody>
        <PanelBody title={__('Field Labels and Options', 'ai-zippy')} initialOpen={false}>
          {['name', 'phone', 'email', 'childAge', 'preferredContact', 'studio', 'programType', 'message'].map((key) => (
            <TextControl key={key} label={`${key} label`} value={labels[key] || ''} onChange={(value) => updateLabel(key, value)} />
          ))}
          <TextControl label={__('Preferred Contact Option', 'ai-zippy')} value={attributes.preferredContactOption || ''} onChange={(preferredContactOption) => setAttributes({ preferredContactOption })} />
          <TextareaControl label={__('Studio Options (one per line)', 'ai-zippy')} value={(attributes.studioOptions || []).join('\n')} onChange={(value) => setAttributes({ studioOptions: value.split('\n').filter((line) => line.trim()) })} />
          <TextareaControl label={__('Programme Options (one per line)', 'ai-zippy')} value={(attributes.programmeOptions || []).join('\n')} onChange={(value) => setAttributes({ programmeOptions: value.split('\n').filter((line) => line.trim()) })} />
          <TextControl label={__('Preselected Programme', 'ai-zippy')} value={attributes.selectedProgramme || ''} onChange={(selectedProgramme) => setAttributes({ selectedProgramme })} />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/course-enquiry-form" attributes={attributes} /></div>
    </>
  );
}
