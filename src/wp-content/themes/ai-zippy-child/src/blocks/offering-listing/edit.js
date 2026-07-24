import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import DecorPositionControls from '../_shared/DecorPositionControls.js';

const emptyItem = { title: '', age: '', category: '', tagline: '', description: '', features: [], image: '', alt: '', ctaText: '', ctaUrl: '' };

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const items = attributes.items || [];
  const updateItem = (index, patch) => {
    const next = [...items];
    next[index] = { ...next[index], ...patch };
    setAttributes({ items: next });
  };

  const {
    decorLeftImage = '', decorLeftAlt = '', decorRightImage = '', decorRightAlt = '',
  } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title="Section" initialOpen={true}>
          <TextControl label="Heading" value={attributes.heading} onChange={(heading) => setAttributes({ heading })} />
          <TextareaControl label="Subheading" value={attributes.subheading} onChange={(subheading) => setAttributes({ subheading })} />
          <SelectControl label="Layout" value={attributes.layout} options={[{ label: 'Grid', value: 'grid' }, { label: 'Carousel', value: 'carousel' }]} onChange={(layout) => setAttributes({ layout })} />
        </PanelBody>

        <PanelBody title="Cartoon & Mascot Decorations" initialOpen={true}>
          <div style={{ marginBottom: '20px' }}>
            <strong style={{ display: 'block', marginBottom: '8px' }}>Mascot Left Image</strong>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorLeftImage: media.url })}
                render={({ open }) => (
                  <>
                    {decorLeftImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={decorLeftImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {decorLeftImage ? 'Change Image' : 'Select Image'}
                    </Button>
                    {decorLeftImage && (
                      <Button variant="link" isDestructive onClick={() => setAttributes({ decorLeftImage: '' })} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
            <TextControl label="Alt Text" value={decorLeftAlt} onChange={(val) => setAttributes({ decorLeftAlt: val })} style={{ marginTop: '8px' }} />
            <DecorPositionControls
              attributes={attributes}
              setAttributes={setAttributes}
              prefix="decorLeft"
              defaults={{ desktopX: 5, desktopY: 10, desktopSize: 140, mobileX: 5, mobileY: 10, mobileSize: 90, zIndex: 5 }}
            />
          </div>

          <div style={{ marginBottom: '20px' }}>
            <strong style={{ display: 'block', marginBottom: '8px' }}>Mascot Right Image</strong>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorRightImage: media.url })}
                render={({ open }) => (
                  <>
                    {decorRightImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={decorRightImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {decorRightImage ? 'Change Image' : 'Select Image'}
                    </Button>
                    {decorRightImage && (
                      <Button variant="link" isDestructive onClick={() => setAttributes({ decorRightImage: '' })} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
            <TextControl label="Alt Text" value={decorRightAlt} onChange={(val) => setAttributes({ decorRightAlt: val })} style={{ marginTop: '8px' }} />
            <DecorPositionControls
              attributes={attributes}
              setAttributes={setAttributes}
              prefix="decorRight"
              defaults={{ desktopX: 90, desktopY: 85, desktopSize: 160, mobileX: 85, mobileY: 85, mobileSize: 100, zIndex: 5 }}
            />
          </div>
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
