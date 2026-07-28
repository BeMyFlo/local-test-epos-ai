import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
export default function Edit({ attributes, setAttributes }) {
  const { sectionTitle = '', classes = [] } = attributes;
  const blockProps = useBlockProps();
  const updateClass = (index, key, value) => {
    const next = [...classes];
    next[index] = { ...next[index], [key]: value };
    setAttributes({ classes: next });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Carousel Title', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Carousel Title', 'ai-zippy')} value={sectionTitle} onChange={(value) => setAttributes({ sectionTitle: value })} />
        </PanelBody>
        <PanelBody title={__('Class Items', 'ai-zippy')} initialOpen={false}>
          {classes.map((item, index) => (
            <PanelBody title={item.tag || `Image ${index + 1}`} initialOpen={false} key={index}>
              <TextControl label={__('Tag', 'ai-zippy')} value={item.tag || ''} onChange={(value) => updateClass(index, 'tag', value)} />
              <TextControl label={__('Tag Colour', 'ai-zippy')} value={item.tagBg || ''} onChange={(value) => updateClass(index, 'tagBg', value)} />
              <TextControl label={__('Age', 'ai-zippy')} value={item.age || ''} onChange={(value) => updateClass(index, 'age', value)} />
              <TextControl label={__('Description', 'ai-zippy')} value={item.description || ''} onChange={(value) => updateClass(index, 'description', value)} />
              <TextControl label={__('Class URL', 'ai-zippy')} value={item.ctaUrl || ''} onChange={(value) => updateClass(index, 'ctaUrl', value)} />
              <TextControl label={__('Image alt text', 'ai-zippy')} value={item.alt || ''} onChange={(value) => updateClass(index, 'alt', value)} />
              <SelectedImagePreview url={item.image} alt={item.alt} fallback="Selected class image" />
              <MediaUploadCheck>
                <MediaUpload allowedTypes={['image']} onSelect={(media) => { const next = [...classes]; next[index] = { ...next[index], image: media.url, alt: media.alt || next[index]?.alt || '' }; setAttributes({ classes: next }); }} render={({ open }) => <Button variant="secondary" onClick={open}>{item.image ? __('Change Image', 'ai-zippy') : __('Select Image', 'ai-zippy')}</Button>} />
              </MediaUploadCheck>
            </PanelBody>
          ))}
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/classes-carousel" attributes={attributes} /></div>
    </>
  );
}
