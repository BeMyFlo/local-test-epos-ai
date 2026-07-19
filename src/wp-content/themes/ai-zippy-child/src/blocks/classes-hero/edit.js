import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { heading = '', sectionTitle = '', classes = [] } = attributes;
  const blockProps = useBlockProps();
  const updateClass = (index, key, value) => {
    const next = [...classes];
    next[index] = { ...next[index], [key]: value };
    setAttributes({ classes: next });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Tagline', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Tagline', 'ai-zippy')} value={attributes.tagline || ''} onChange={(tagline) => setAttributes({ tagline })} />
          <TextareaControl label={__('Tagline description', 'ai-zippy')} value={attributes.taglineDescription || ''} onChange={(taglineDescription) => setAttributes({ taglineDescription })} />
        </PanelBody>
        <PanelBody title={__('Page Titles', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Heading (use \\n for line break)', 'ai-zippy')} value={heading} onChange={(value) => setAttributes({ heading: value })} />
          <TextControl label={__('Carousel Title', 'ai-zippy')} value={sectionTitle} onChange={(value) => setAttributes({ sectionTitle: value })} />
          <TextControl label={__('Breadcrumb home text', 'ai-zippy')} value={attributes.breadcrumbHomeText || ''} onChange={(breadcrumbHomeText) => setAttributes({ breadcrumbHomeText })} />
          <TextControl label={__('Breadcrumb home URL', 'ai-zippy')} value={attributes.breadcrumbHomeUrl || ''} onChange={(breadcrumbHomeUrl) => setAttributes({ breadcrumbHomeUrl })} />
          <TextControl label={__('Breadcrumb current page', 'ai-zippy')} value={attributes.breadcrumbCurrent || ''} onChange={(breadcrumbCurrent) => setAttributes({ breadcrumbCurrent })} />
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
              <MediaUploadCheck>
                <MediaUpload allowedTypes={['image']} onSelect={(media) => { const next = [...classes]; next[index] = { ...next[index], image: media.url, alt: media.alt || next[index]?.alt || '' }; setAttributes({ classes: next }); }} render={({ open }) => <Button variant="secondary" onClick={open}>{item.image ? __('Change Image', 'ai-zippy') : __('Select Image', 'ai-zippy')}</Button>} />
              </MediaUploadCheck>
            </PanelBody>
          ))}
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/classes-hero" attributes={attributes} /></div>
    </>
  );
}
