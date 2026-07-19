import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const brands = attributes.brands || [];
  const galleryImages = attributes.galleryImages || [];
  const updateItem = (key, index, field, value) => {
    const items = [...(attributes[key] || [])];
    items[index] = { ...items[index], [field]: value };
    setAttributes({ [key]: items });
  };
  return <>
    <InspectorControls>
      <PanelBody title="Content" initialOpen={true}>
        <TextareaControl label="Heading" value={attributes.heading || ''} onChange={(heading) => setAttributes({ heading })} />
        <TextareaControl label="Description" value={attributes.description || ''} onChange={(description) => setAttributes({ description })} />
        <TextControl label="Decoration image URL" value={attributes.decorImage || ''} onChange={(decorImage) => setAttributes({ decorImage })} />
        <TextControl label="Decoration alt text" value={attributes.decorAlt || ''} onChange={(decorAlt) => setAttributes({ decorAlt })} />
        <TextControl label="CTA text" value={attributes.ctaText || ''} onChange={(ctaText) => setAttributes({ ctaText })} />
        <TextControl label="CTA URL" value={attributes.ctaUrl || ''} onChange={(ctaUrl) => setAttributes({ ctaUrl })} />
      </PanelBody>
      <PanelBody title="Brand items" initialOpen={false}>
        {brands.map((item, index) => <PanelBody key={index} title={item.name || `Item ${index + 1}`} initialOpen={false}>
          <TextControl label="Name" value={item.name || ''} onChange={(v) => updateItem('brands', index, 'name', v)} />
          <TextControl label="Icon URL" value={item.icon || ''} onChange={(v) => updateItem('brands', index, 'icon', v)} />
          <TextControl label="Icon alt text" value={item.alt || ''} onChange={(v) => updateItem('brands', index, 'alt', v)} />
          <Button isDestructive variant="secondary" onClick={() => setAttributes({ brands: brands.filter((_, i) => i !== index) })}>Remove item</Button>
        </PanelBody>)}
        <Button variant="primary" onClick={() => setAttributes({ brands: [...brands, { name: '', icon: '', alt: '' }] })}>Add item</Button>
      </PanelBody>
      <PanelBody title="Gallery photos" initialOpen={false}>
        {galleryImages.map((item, index) => <PanelBody key={index} title={`Photo ${index + 1}`} initialOpen={false}>
          <TextControl label="Image URL" value={item.url || ''} onChange={(v) => updateItem('galleryImages', index, 'url', v)} />
          <TextControl label="Alt text" value={item.alt || ''} onChange={(v) => updateItem('galleryImages', index, 'alt', v)} />
          <Button isDestructive variant="secondary" onClick={() => setAttributes({ galleryImages: galleryImages.filter((_, i) => i !== index) })}>Remove photo</Button>
        </PanelBody>)}
        <Button variant="primary" onClick={() => setAttributes({ galleryImages: [...galleryImages, { url: '', alt: '' }] })}>Add photo</Button>
      </PanelBody>
    </InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
