import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const emptyItem = { title: '', age: '', category: '', tagline: '', description: '', features: [], image: '', alt: '', ctaText: '', ctaUrl: '' };

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const items = attributes.items || [];
  const updateItem = (index, patch) => {
    const next = [...items];
    next[index] = { ...next[index], ...patch };
    setAttributes({ items: next });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title="Section" initialOpen={true}>
          <TextControl label="Heading" value={attributes.heading} onChange={(heading) => setAttributes({ heading })} />
          <TextareaControl label="Subheading" value={attributes.subheading} onChange={(subheading) => setAttributes({ subheading })} />
          <SelectControl label="Layout" value={attributes.layout} options={[{ label: 'Grid', value: 'grid' }, { label: 'Carousel', value: 'carousel' }]} onChange={(layout) => setAttributes({ layout })} />
        </PanelBody>
        <PanelBody title="Offerings" initialOpen={false}>
          {items.map((item, index) => (
            <PanelBody title={item.title || `Offering ${index + 1}`} initialOpen={false} key={index}>
              <TextControl label="Title" value={item.title || ''} onChange={(title) => updateItem(index, { title })} />
              <TextControl label="Age" value={item.age || ''} onChange={(age) => updateItem(index, { age })} />
              <TextControl label="Category" value={item.category || ''} onChange={(category) => updateItem(index, { category })} />
              <TextControl label="Tagline" value={item.tagline || ''} onChange={(tagline) => updateItem(index, { tagline })} />
              <TextareaControl label="Description" value={item.description || ''} onChange={(description) => updateItem(index, { description })} />
              <TextareaControl label="Features (one per line)" value={(item.features || []).join('\n')} onChange={(value) => updateItem(index, { features: value.split('\n').filter((line) => line.trim()) })} />
              <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => updateItem(index, { image: media.url, alt: media.alt || item.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{item.image ? 'Change image' : 'Select image'}</Button>} /></MediaUploadCheck>
              <TextControl label="Image URL" value={item.image || ''} onChange={(image) => updateItem(index, { image })} />
              <TextControl label="Image Alt Text" value={item.alt || ''} onChange={(alt) => updateItem(index, { alt })} />
              <TextControl label="CTA Text" value={item.ctaText || ''} onChange={(ctaText) => updateItem(index, { ctaText })} />
              <TextControl label="CTA URL" value={item.ctaUrl || ''} onChange={(ctaUrl) => updateItem(index, { ctaUrl })} />
              <Button isDestructive variant="secondary" onClick={() => setAttributes({ items: items.filter((_, itemIndex) => itemIndex !== index) })}>Remove offering</Button>
            </PanelBody>
          ))}
          <Button variant="primary" onClick={() => setAttributes({ items: [...items, { ...emptyItem }] })}>Add offering</Button>
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
    </>
  );
}
