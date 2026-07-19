import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { sections = [], galleryTitle = '', galleryImages = [] } = attributes;
  const blockProps = useBlockProps();
  const updateSection = (index, key, value) => {
    const next = [...sections];
    next[index] = { ...next[index], [key]: value };
    setAttributes({ sections: next });
  };
  const updateGalleryImage = (index, media) => {
    const next = [...galleryImages];
    next[index] = { ...next[index], url: media.url, alt: media.alt || next[index]?.alt || '' };
    setAttributes({ galleryImages: next });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Class Features', 'ai-zippy')} initialOpen={true}>
          {sections.map((section, index) => (
            <PanelBody title={section.title || `Feature ${index + 1}`} initialOpen={false} key={index}>
              <TextControl label={__('Title', 'ai-zippy')} value={section.title || ''} onChange={(value) => updateSection(index, 'title', value)} />
              <TextControl label={__('Age', 'ai-zippy')} value={section.age || ''} onChange={(value) => updateSection(index, 'age', value)} />
              <TextareaControl label={__('Description', 'ai-zippy')} value={section.description || ''} onChange={(value) => updateSection(index, 'description', value)} />
              <TextControl label={__('CTA Text', 'ai-zippy')} value={section.ctaText || ''} onChange={(value) => updateSection(index, 'ctaText', value)} />
              <TextControl label={__('CTA URL', 'ai-zippy')} value={section.ctaUrl || ''} onChange={(value) => updateSection(index, 'ctaUrl', value)} />
              <TextControl label={__('Image alt text', 'ai-zippy')} value={section.alt || ''} onChange={(value) => updateSection(index, 'alt', value)} />
              <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => { const next = [...sections]; next[index] = { ...next[index], image: media.url, alt: media.alt || next[index]?.alt || '' }; setAttributes({ sections: next }); }} render={({ open }) => <Button variant="secondary" onClick={open}>{section.image ? __('Change Image', 'ai-zippy') : __('Select Image', 'ai-zippy')}</Button>} /></MediaUploadCheck>
            </PanelBody>
          ))}
        </PanelBody>
        <PanelBody title={__('Gallery', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Gallery Title', 'ai-zippy')} value={galleryTitle} onChange={(value) => setAttributes({ galleryTitle: value })} />
          {galleryImages.map((image, index) => (
            <div key={index}><TextControl label={`${__('Image alt text', 'ai-zippy')} ${index + 1}`} value={image.alt || ''} onChange={(alt) => { const next = [...galleryImages]; next[index] = { ...next[index], alt }; setAttributes({ galleryImages: next }); }} /><MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => updateGalleryImage(index, media)} render={({ open }) => <Button variant="secondary" onClick={open}>{image.url ? __('Change Image', 'ai-zippy') : `Select Image ${index + 1}`}</Button>} /></MediaUploadCheck></div>
          ))}
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/classes-detail" attributes={attributes} /></div>
    </>
  );
}
