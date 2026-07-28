import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
const emptyOffering = { sectionLabel: '', title: '', age: '', tagline: '', description: '', features: [], image: '', alt: '', ctaText: '', ctaUrl: '' };

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const offerings = attributes.offerings || [];
  const update = (index, patch) => { const next = [...offerings]; next[index] = { ...next[index], ...patch }; setAttributes({ offerings: next }); };
  return <>
    <InspectorControls><PanelBody title="Event Offerings" initialOpen={true}>
      {offerings.map((item, index) => <PanelBody title={item.sectionLabel || item.title || `Offering ${index + 1}`} initialOpen={false} key={index}>
        <TextControl label="Section Label" value={item.sectionLabel || ''} onChange={(sectionLabel) => update(index, { sectionLabel })} />
        <TextControl label="Title" value={item.title || ''} onChange={(title) => update(index, { title })} />
        <TextControl label="Age" value={item.age || ''} onChange={(age) => update(index, { age })} />
        <TextControl label="Tagline" value={item.tagline || ''} onChange={(tagline) => update(index, { tagline })} />
        <TextareaControl label="Description" value={item.description || ''} onChange={(description) => update(index, { description })} />
        <TextareaControl label="Features (one per line)" value={(item.features || []).join('\n')} onChange={(value) => update(index, { features: value.split('\n').filter((line) => line.trim()) })} />
        <SelectedImagePreview url={item.image} alt={item.alt} fallback="Selected offering image" /><MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => update(index, { image: media.url, alt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{item.image ? 'Change image' : 'Select image'}</Button>} /></MediaUploadCheck>
        <TextControl label="Image URL" value={item.image || ''} onChange={(image) => update(index, { image })} />
        <TextControl label="Image Alt Text" value={item.alt || ''} onChange={(alt) => update(index, { alt })} />
        <TextControl label="CTA Text" value={item.ctaText || ''} onChange={(ctaText) => update(index, { ctaText })} />
        <TextControl label="CTA URL" value={item.ctaUrl || ''} onChange={(ctaUrl) => update(index, { ctaUrl })} />
        <Button isDestructive variant="secondary" onClick={() => setAttributes({ offerings: offerings.filter((_, itemIndex) => itemIndex !== index) })}>Remove offering</Button>
      </PanelBody>)}
      <Button variant="primary" onClick={() => setAttributes({ offerings: [...offerings, { ...emptyOffering }] })}>Add offering</Button>
    </PanelBody></InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
