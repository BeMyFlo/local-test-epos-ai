import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const studios = attributes.studios || [];
  const updateStudio = (index, patch) => { const next = [...studios]; next[index] = { ...next[index], ...patch }; setAttributes({ studios: next }); };
  const updateImage = (studioIndex, imageIndex, patch) => { const gallery = [...(studios[studioIndex].galleryImages || [])]; gallery[imageIndex] = { ...gallery[imageIndex], ...patch }; updateStudio(studioIndex, { galleryImages: gallery }); };
  return <>
    <InspectorControls><PanelBody title="Studios" initialOpen={true}>
      {studios.map((studio, index) => <PanelBody title={studio.name || `Studio ${index + 1}`} initialOpen={false} key={index}>
        <TextControl label="Name" value={studio.name || ''} onChange={(nameValue) => updateStudio(index, { name: nameValue })} />
        <TextareaControl label="Address" value={studio.address || ''} onChange={(address) => updateStudio(index, { address })} />
        <TextControl label="Phone" value={studio.phone || ''} onChange={(phone) => updateStudio(index, { phone })} />
        <TextareaControl label="Opening Hours" value={studio.hours || ''} onChange={(hours) => updateStudio(index, { hours })} />
        <TextControl label="Map URL" value={studio.mapUrl || ''} onChange={(mapUrl) => updateStudio(index, { mapUrl })} />
        <TextControl label="Map Link Text" value={studio.mapText || ''} onChange={(mapText) => updateStudio(index, { mapText })} />
        {(studio.galleryImages || []).map((image, imageIndex) => <div key={imageIndex}>
          <SelectedImagePreview url={image.url} alt={image.alt} fallback={`Selected studio image ${imageIndex + 1}`} />
          <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => updateImage(index, imageIndex, { url: media.url, alt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{image.url ? 'Change image' : `Select image ${imageIndex + 1}`}</Button>} /></MediaUploadCheck>
          <TextControl label="Image URL" value={image.url || ''} onChange={(url) => updateImage(index, imageIndex, { url })} />
          <TextControl label="Alt Text" value={image.alt || ''} onChange={(alt) => updateImage(index, imageIndex, { alt })} />
          <Button isDestructive variant="link" onClick={() => updateStudio(index, { galleryImages: studio.galleryImages.filter((_, itemIndex) => itemIndex !== imageIndex) })}>Remove image</Button>
        </div>)}
        <Button variant="secondary" onClick={() => updateStudio(index, { galleryImages: [...(studio.galleryImages || []), { url: '', alt: '' }] })}>Add gallery image</Button>
        <Button isDestructive variant="secondary" onClick={() => setAttributes({ studios: studios.filter((_, itemIndex) => itemIndex !== index) })}>Remove studio</Button>
      </PanelBody>)}
      <Button variant="primary" onClick={() => setAttributes({ studios: [...studios, { name: '', address: '', phone: '', hours: '', mapUrl: '', mapText: '', galleryImages: [] }] })}>Add studio</Button>
    </PanelBody></InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
