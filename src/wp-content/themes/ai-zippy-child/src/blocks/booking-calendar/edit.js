import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const LABEL_KEYS = ['name', 'phone', 'email', 'childAge', 'preferredContact', 'message', 'date', 'time', 'selectedSlot'];

export default function Edit({ attributes, setAttributes }) {
  const labels = attributes.labels || {};
  const blockProps = useBlockProps();
  const updateLabel = (key, value) => setAttributes({ labels: { ...labels, [key]: value } });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Calendar Content', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Heading', 'ai-zippy')} value={attributes.heading || ''} onChange={(heading) => setAttributes({ heading })} />
          <TextControl label={__('Subheading', 'ai-zippy')} value={attributes.subheading || ''} onChange={(subheading) => setAttributes({ subheading })} />
          <TextControl label={__('Success Message', 'ai-zippy')} value={attributes.successMessage || ''} onChange={(successMessage) => setAttributes({ successMessage })} />
        </PanelBody>
        <PanelBody title={__('Labels and Buttons', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Studio select label', 'ai-zippy')} value={attributes.studioLabel || ''} onChange={(studioLabel) => setAttributes({ studioLabel })} />
          <TextControl label={__('Programme select label', 'ai-zippy')} value={attributes.programmeLabel || ''} onChange={(programmeLabel) => setAttributes({ programmeLabel })} />
          <TextControl label={__('Enroll button', 'ai-zippy')} value={attributes.enrollText || ''} onChange={(enrollText) => setAttributes({ enrollText })} />
          <TextControl label={__('Full label', 'ai-zippy')} value={attributes.fullText || ''} onChange={(fullText) => setAttributes({ fullText })} />
          <TextControl label={__('Remaining places text (%d = count)', 'ai-zippy')} value={attributes.remainingText || ''} onChange={(remainingText) => setAttributes({ remainingText })} />
          <TextControl label={__('Unlimited capacity text', 'ai-zippy')} value={attributes.unlimitedText || ''} onChange={(unlimitedText) => setAttributes({ unlimitedText })} />
          <TextControl label={__('Submit button', 'ai-zippy')} value={attributes.submitText || ''} onChange={(submitText) => setAttributes({ submitText })} />
        </PanelBody>
        <PanelBody title={__('Field Labels', 'ai-zippy')} initialOpen={false}>
          {LABEL_KEYS.map((key) => (
            <TextControl key={key} label={`${key} label`} value={labels[key] || ''} onChange={(value) => updateLabel(key, value)} />
          ))}
          <TextareaControl label={__('Preferred Contact Options (one per line)', 'ai-zippy')} value={(attributes.preferredContactOptions || []).join('\n')} onChange={(value) => setAttributes({ preferredContactOptions: value.split('\n').filter((line) => line.trim()) })} />
        </PanelBody>
        <PanelBody title={__('Preselection', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Preselected studio slug', 'ai-zippy')} value={attributes.selectedStudio || ''} onChange={(selectedStudio) => setAttributes({ selectedStudio })} />
          <TextControl label={__('Preselected programme', 'ai-zippy')} value={attributes.selectedProgramme || ''} onChange={(selectedProgramme) => setAttributes({ selectedProgramme })} />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/booking-calendar" attributes={attributes} /></div>
    </>
  );
}
