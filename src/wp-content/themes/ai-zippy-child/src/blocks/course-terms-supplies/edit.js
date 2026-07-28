import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import DecorPositionControls from '../_shared/DecorPositionControls.js';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, ColorPicker, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const mascotDefaults = {
  zIndex: 1,
  desktopX: 7.3684210526,
  desktopY: 88.0390752359,
  desktopSize: 150,
  mobileX: 28,
  mobileY: 90.877150016,
  mobileSize: 150,
};

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps();
  const selectImage = (key, label) => (
    <>
      <SelectedImagePreview
        url={attributes[key]}
        alt={key === 'image' ? attributes.imageAlt : ''}
        fallback={`Selected ${label}`}
      />
      <MediaUploadCheck>
        <MediaUpload
          allowedTypes={['image']}
          onSelect={(media) => setAttributes({
            [key]: media.url,
            ...(key === 'image' ? { imageAlt: media.alt || attributes.imageAlt || '' } : {}),
          })}
          render={({ open }) => (
            <Button variant="secondary" onClick={open}>
              {attributes[key] ? `Change ${label}` : `Select ${label}`}
            </Button>
          )}
        />
      </MediaUploadCheck>
    </>
  );

  return (
    <>
      <InspectorControls>
        <PanelBody title="Content">
          <TextControl label="Terms title" value={attributes.termsTitle || ''} onChange={(termsTitle) => setAttributes({ termsTitle })} />
          <TextareaControl label="Terms (one per line)" value={(attributes.terms || []).join('\n')} onChange={(value) => setAttributes({ terms: value.split('\n').filter(Boolean) })} />
          <TextControl label="Supplies title" value={attributes.suppliesTitle || ''} onChange={(suppliesTitle) => setAttributes({ suppliesTitle })} />
          <TextareaControl label="Supplies content" value={attributes.suppliesText || ''} onChange={(suppliesText) => setAttributes({ suppliesText })} />
          <TextControl label="CTA text" value={attributes.ctaText || ''} onChange={(ctaText) => setAttributes({ ctaText })} />
          <TextControl label="CTA URL" value={attributes.ctaUrl || ''} onChange={(ctaUrl) => setAttributes({ ctaUrl })} />
        </PanelBody>
        <PanelBody title="Image">
          {selectImage('image', 'image')}
          <TextControl label="Image alt text" value={attributes.imageAlt || ''} onChange={(imageAlt) => setAttributes({ imageAlt })} />
          <DecorPositionControls attributes={attributes} setAttributes={setAttributes} prefix="mascot" defaults={mascotDefaults} />
        </PanelBody>
        <PanelBody title="Background">
          <ColorPicker color={attributes.backgroundColor} onChange={(backgroundColor) => setAttributes({ backgroundColor })} />
          {selectImage('backgroundImage', 'background image')}
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block="ai-zippy/course-terms-supplies" attributes={attributes} />
      </div>
    </>
  );
}
