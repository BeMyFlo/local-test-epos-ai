import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const update = (key, index, patch) => { const next = [...(attributes[key] || [])]; next[index] = { ...next[index], ...patch }; setAttributes({ [key]: next }); };
  const remove = (key, index) => setAttributes({ [key]: (attributes[key] || []).filter((_, itemIndex) => itemIndex !== index) });
  return <>
    <InspectorControls>
      <PanelBody title="About Introduction" initialOpen={true}>
        <TextControl label="Heading" value={attributes.heading} onChange={(heading) => setAttributes({ heading })} />
        <TextareaControl label="Description" value={attributes.description} onChange={(description) => setAttributes({ description })} />
        <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => setAttributes({ logoImage: media.url, logoAlt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{attributes.logoImage ? 'Change logo' : 'Select logo'}</Button>} /></MediaUploadCheck>
        <TextControl label="Logo URL" value={attributes.logoImage} onChange={(logoImage) => setAttributes({ logoImage })} />
        <TextControl label="Logo Alt Text" value={attributes.logoAlt} onChange={(logoAlt) => setAttributes({ logoAlt })} />
      </PanelBody>
      <PanelBody title="Values" initialOpen={false}>
        {(attributes.values || []).map((item, index) => <PanelBody title={item.title || `Value ${index + 1}`} initialOpen={false} key={index}>
          <TextControl label="Title" value={item.title || ''} onChange={(title) => update('values', index, { title })} />
          <TextareaControl label="Description" value={item.description || ''} onChange={(description) => update('values', index, { description })} />
          <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => update('values', index, { icon: media.url, alt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{item.icon ? 'Change icon' : 'Select icon'}</Button>} /></MediaUploadCheck>
          <TextControl label="Icon URL" value={item.icon || ''} onChange={(icon) => update('values', index, { icon })} />
          <TextControl label="Icon Alt Text" value={item.alt || ''} onChange={(alt) => update('values', index, { alt })} />
          <Button isDestructive variant="secondary" onClick={() => remove('values', index)}>Remove value</Button>
        </PanelBody>)}
        <Button variant="primary" onClick={() => setAttributes({ values: [...(attributes.values || []), { title: '', description: '', icon: '', alt: '' }] })}>Add value</Button>
      </PanelBody>
      <PanelBody title="Gallery" initialOpen={false}>
        <TextControl label="Gallery Title" value={attributes.galleryTitle} onChange={(galleryTitle) => setAttributes({ galleryTitle })} />
        {(attributes.galleryImages || []).map((item, index) => <div key={index}>
          <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => update('galleryImages', index, { url: media.url, alt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{item.url ? 'Change image' : `Select image ${index + 1}`}</Button>} /></MediaUploadCheck>
          <TextControl label="Image URL" value={item.url || ''} onChange={(url) => update('galleryImages', index, { url })} />
          <TextControl label="Alt Text" value={item.alt || ''} onChange={(alt) => update('galleryImages', index, { alt })} />
          <Button isDestructive variant="link" onClick={() => remove('galleryImages', index)}>Remove image</Button>
        </div>)}
        <Button variant="primary" onClick={() => setAttributes({ galleryImages: [...(attributes.galleryImages || []), { url: '', alt: '' }] })}>Add gallery image</Button>
      </PanelBody>
    </InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
