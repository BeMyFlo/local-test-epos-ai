import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const updateArray = (key, index, patch) => {
    const current = attributes[key] || [];
    const next = key === 'galleryImages'
      ? Array.from({ length: Math.max(4, current.length) }, (_, itemIndex) => current[itemIndex] || { url: '', alt: `Student artwork photo ${itemIndex + 1}` })
      : [...current];
    next[index] = { ...next[index], ...patch };
    setAttributes({ [key]: next });
  };
  const galleryImages = Array.from({ length: Math.max(4, (attributes.galleryImages || []).length) }, (_, index) => attributes.galleryImages?.[index] || { url: '', alt: `Student artwork photo ${index + 1}` });
  const removeArrayItem = (key, index) => setAttributes({ [key]: (attributes[key] || []).filter((_, itemIndex) => itemIndex !== index) });

  return <>
    <InspectorControls>
      <PanelBody title="Service Introduction" initialOpen={true}>
        <TextControl label="Service Type" value={attributes.serviceType} onChange={(serviceType) => setAttributes({ serviceType })} />
        <TextControl label="Heading" value={attributes.heading} onChange={(heading) => setAttributes({ heading })} />
        <TextControl label="Age Range" value={attributes.ageRange} onChange={(ageRange) => setAttributes({ ageRange })} />
        <TextareaControl label="Description" value={attributes.description} onChange={(description) => setAttributes({ description })} />
        <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => setAttributes({ mainImage: media.url, mainImageAlt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{attributes.mainImage ? 'Change image' : 'Select image'}</Button>} /></MediaUploadCheck>
        <TextControl label="Image URL" value={attributes.mainImage} onChange={(mainImage) => setAttributes({ mainImage })} />
        <TextControl label="Image Alt Text" value={attributes.mainImageAlt} onChange={(mainImageAlt) => setAttributes({ mainImageAlt })} />
        <TextControl label="Information Title" value={attributes.infoTitle} onChange={(infoTitle) => setAttributes({ infoTitle })} />
        {(attributes.infoItems || []).map((item, index) => <div key={index}><TextControl label={`Information ${index + 1}`} value={item.text || ''} onChange={(text) => updateArray('infoItems', index, { text })} /><Button isDestructive variant="link" onClick={() => removeArrayItem('infoItems', index)}>Remove</Button></div>)}
        <Button variant="secondary" onClick={() => setAttributes({ infoItems: [...(attributes.infoItems || []), { text: '' }] })}>Add information</Button>
        <TextControl label="CTA Text" value={attributes.ctaText} onChange={(ctaText) => setAttributes({ ctaText })} />
        <TextControl label="CTA URL" value={attributes.ctaUrl} onChange={(ctaUrl) => setAttributes({ ctaUrl })} />
      </PanelBody>
      <PanelBody title="Related Services" initialOpen={false}>
        <TextControl label="Section Title" value={attributes.relatedTitle} onChange={(relatedTitle) => setAttributes({ relatedTitle })} />
        {(attributes.relatedItems || []).map((item, index) => <PanelBody title={item.title || `Related ${index + 1}`} initialOpen={false} key={index}>
          <TextControl label="Title" value={item.title || ''} onChange={(title) => updateArray('relatedItems', index, { title })} />
          <TextControl label="URL" value={item.url || ''} onChange={(url) => updateArray('relatedItems', index, { url })} />
          <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => updateArray('relatedItems', index, { image: media.url, alt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{item.image ? 'Change image' : 'Select image'}</Button>} /></MediaUploadCheck>
          <TextControl label="Image URL" value={item.image || ''} onChange={(image) => updateArray('relatedItems', index, { image })} />
          <TextControl label="Image Alt Text" value={item.alt || ''} onChange={(alt) => updateArray('relatedItems', index, { alt })} />
          <Button isDestructive variant="secondary" onClick={() => removeArrayItem('relatedItems', index)}>Remove related service</Button>
        </PanelBody>)}
        <Button variant="primary" onClick={() => setAttributes({ relatedItems: [...(attributes.relatedItems || []), { title: '', url: '', image: '', alt: '' }] })}>Add related service</Button>
      </PanelBody>
      <PanelBody title="Gallery" initialOpen={false}>
        <TextControl label="Gallery Title" value={attributes.galleryTitle} onChange={(galleryTitle) => setAttributes({ galleryTitle })} />
        {galleryImages.map((item, index) => <div key={index}>
          <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => updateArray('galleryImages', index, { url: media.url, alt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{item.url ? 'Change image' : `Select image ${index + 1}`}</Button>} /></MediaUploadCheck>
          <TextControl label="Image URL" value={item.url || ''} onChange={(url) => updateArray('galleryImages', index, { url })} />
          <TextControl label="Alt Text" value={item.alt || ''} onChange={(alt) => updateArray('galleryImages', index, { alt })} />
          <Button isDestructive variant="link" onClick={() => removeArrayItem('galleryImages', index)}>Remove image</Button>
        </div>)}
        <Button variant="primary" onClick={() => setAttributes({ galleryImages: [...(attributes.galleryImages || []), { url: '', alt: '' }] })}>Add gallery image</Button>
      </PanelBody>
    </InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
