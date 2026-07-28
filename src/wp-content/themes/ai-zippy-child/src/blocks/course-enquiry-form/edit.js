import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import DecorPositionControls from '../_shared/DecorPositionControls.js';
import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, ColorPicker, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
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
        <PanelBody title={__('Image and Background', 'ai-zippy')} initialOpen={false}>
          <SelectedImagePreview url={attributes.mascotImage} alt={attributes.mascotImageAlt} fallback="Selected mascot" /><MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => setAttributes({ mascotImage: media.url, mascotImageAlt: media.alt || attributes.mascotImageAlt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{attributes.mascotImage ? __('Change mascot', 'ai-zippy') : __('Select mascot', 'ai-zippy')}</Button>} /></MediaUploadCheck>
          <TextControl label={__('Mascot alt text', 'ai-zippy')} value={attributes.mascotImageAlt || ''} onChange={(mascotImageAlt) => setAttributes({ mascotImageAlt })} />
          <ColorPicker color={attributes.backgroundColor} onChange={(backgroundColor) => setAttributes({ backgroundColor })} />
          <SelectedImagePreview url={attributes.backgroundImage} fallback="Selected background image" /><MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => setAttributes({ backgroundImage: media.url })} render={({ open }) => <Button variant="secondary" onClick={open}>{attributes.backgroundImage ? __('Change background', 'ai-zippy') : __('Select background', 'ai-zippy')}</Button>} /></MediaUploadCheck>
        </PanelBody>
        <PanelBody title={__('Decorative Pencils', 'ai-zippy')} initialOpen={false}>
          <SelectedImagePreview url={attributes.decorPencilsImage} alt={attributes.decorPencilsAlt} fallback="Selected pencils decoration" />
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              onSelect={(media) => setAttributes({
                decorPencilsImage: media.url,
                decorPencilsAlt: media.alt || attributes.decorPencilsAlt || '',
              })}
              render={({ open }) => <Button variant="secondary" onClick={open}>{attributes.decorPencilsImage ? __('Change pencils image', 'ai-zippy') : __('Select pencils image', 'ai-zippy')}</Button>}
            />
          </MediaUploadCheck>
          {attributes.decorPencilsImage && <Button variant="link" isDestructive onClick={() => setAttributes({ decorPencilsImage: '' })}>{__('Use default SVG', 'ai-zippy')}</Button>}
          <TextControl label={__('Pencils alt text', 'ai-zippy')} value={attributes.decorPencilsAlt || ''} onChange={(decorPencilsAlt) => setAttributes({ decorPencilsAlt })} />
          <DecorPositionControls
            attributes={attributes}
            setAttributes={setAttributes}
            prefix="decorPencils"
            defaults={{ zIndex: 5, desktopX: 87, desktopY: 88, desktopSize: 140, mobileX: 78, mobileY: 95, mobileSize: 90 }}
          />
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
