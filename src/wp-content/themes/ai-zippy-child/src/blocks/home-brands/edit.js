import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import DecorPositionControls from '../_shared/DecorPositionControls.js';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const brands = attributes.brands || [];
  const features = attributes.features || [];
  const galleryImages = attributes.galleryImages || [];
  const updateItem = (key, index, field, value) => {
    const items = [...(attributes[key] || [])];
    items[index] = { ...items[index], [field]: value };
    setAttributes({ [key]: items });
  };
  return <>
    <InspectorControls>
      <PanelBody title="Content Settings" initialOpen={true}>
        <TextareaControl label="Heading" value={attributes.heading || ''} onChange={(heading) => setAttributes({ heading })} />
        <TextareaControl label="Description" value={attributes.description || ''} onChange={(description) => setAttributes({ description })} />
        <TextControl label="CTA text" value={attributes.ctaText || ''} onChange={(ctaText) => setAttributes({ ctaText })} />
        <TextControl label="CTA URL" value={attributes.ctaUrl || ''} onChange={(ctaUrl) => setAttributes({ ctaUrl })} />
      </PanelBody>

      <PanelBody title="Decoration / Cartoon Images" initialOpen={true}>
        <div style={{ marginBottom: '15px' }}>
          <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Decor Image</label>
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              onSelect={(media) => setAttributes({ decorImage: media.url })}
              render={({ open }) => (
                <>
                  {attributes.decorImage && (
                    <div style={{ marginBottom: '8px' }}>
                      <img src={attributes.decorImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                    </div>
                  )}
                  <Button variant="secondary" onClick={open}>
                    {attributes.decorImage ? 'Change Image' : 'Select Image'}
                  </Button>
                  {attributes.decorImage && (
                    <Button variant="link" isDestructive onClick={() => setAttributes({ decorImage: '' })} style={{ marginLeft: '10px' }}>
                      Remove
                    </Button>
                  )}
                </>
              )}
            />
          </MediaUploadCheck>
          <TextControl label="Decoration alt text" value={attributes.decorAlt || ''} onChange={(decorAlt) => setAttributes({ decorAlt })} style={{ marginTop: '8px' }} />
          <DecorPositionControls
            attributes={attributes}
            setAttributes={setAttributes}
            prefix="decor"
            defaults={{ desktopX: 8, desktopY: 15, desktopSize: 60, mobileX: 12, mobileY: 12, mobileSize: 60, zIndex: 5 }}
          />
        </div>

        <div style={{ marginBottom: '15px' }}>
          <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Decor Left Image (Bottom/Left mascot)</label>
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              onSelect={(media) => setAttributes({ decorLeftImage: media.url })}
              render={({ open }) => (
                <>
                  {attributes.decorLeftImage && (
                    <div style={{ marginBottom: '8px' }}>
                      <img src={attributes.decorLeftImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                    </div>
                  )}
                  <Button variant="secondary" onClick={open}>
                    {attributes.decorLeftImage ? 'Change Image' : 'Select Image'}
                  </Button>
                  {attributes.decorLeftImage && (
                    <Button variant="link" isDestructive onClick={() => setAttributes({ decorLeftImage: '' })} style={{ marginLeft: '10px' }}>
                      Remove
                    </Button>
                  )}
                </>
              )}
            />
          </MediaUploadCheck>
          <TextControl label="Decor Left Alt" value={attributes.decorLeftAlt || ''} onChange={(decorLeftAlt) => setAttributes({ decorLeftAlt })} style={{ marginTop: '8px' }} />
          <DecorPositionControls
            attributes={attributes}
            setAttributes={setAttributes}
            prefix="decorLeft"
            defaults={{ desktopX: 8, desktopY: 85, desktopSize: 120, mobileX: 12, mobileY: 85, mobileSize: 75, zIndex: 5 }}
          />
        </div>

        <div style={{ marginBottom: '15px' }}>
          <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Decor Right Image (Top/Right mascot)</label>
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              onSelect={(media) => setAttributes({ decorRightImage: media.url })}
              render={({ open }) => (
                <>
                  {attributes.decorRightImage && (
                    <div style={{ marginBottom: '8px' }}>
                      <img src={attributes.decorRightImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                    </div>
                  )}
                  <Button variant="secondary" onClick={open}>
                    {attributes.decorRightImage ? 'Change Image' : 'Select Image'}
                  </Button>
                  {attributes.decorRightImage && (
                    <Button variant="link" isDestructive onClick={() => setAttributes({ decorRightImage: '' })} style={{ marginLeft: '10px' }}>
                      Remove
                    </Button>
                  )}
                </>
              )}
            />
          </MediaUploadCheck>
          <TextControl label="Decor Right Alt" value={attributes.decorRightAlt || ''} onChange={(decorRightAlt) => setAttributes({ decorRightAlt })} style={{ marginTop: '8px' }} />
          <DecorPositionControls
            attributes={attributes}
            setAttributes={setAttributes}
            prefix="decorRight"
            defaults={{ desktopX: 92, desktopY: 15, desktopSize: 120, mobileX: 88, mobileY: 15, mobileSize: 75, zIndex: 5 }}
          />
        </div>
      </PanelBody>

      <PanelBody title="Brand items" initialOpen={false}>
        {brands.map((item, index) => <PanelBody key={index} title={item.name || `Item ${index + 1}`} initialOpen={false}>
          <TextControl label="Name" value={item.name || ''} onChange={(v) => updateItem('brands', index, 'name', v)} />
          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Brand Icon</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => updateItem('brands', index, 'icon', media.url)}
                render={({ open }) => (
                  <>
                    {item.icon && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={item.icon} alt="" style={{ maxWidth: '60px', maxHeight: '60px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {item.icon ? 'Change Icon' : 'Select Icon'}
                    </Button>
                    {item.icon && (
                      <Button variant="link" isDestructive onClick={() => updateItem('brands', index, 'icon', '')} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
          </div>
          <TextControl label="Icon alt text" value={item.alt || ''} onChange={(v) => updateItem('brands', index, 'alt', v)} />
          <Button isDestructive variant="secondary" onClick={() => setAttributes({ brands: brands.filter((_, i) => i !== index) })}>Remove item</Button>
        </PanelBody>)}
        <Button variant="primary" onClick={() => setAttributes({ brands: [...brands, { name: '', icon: '', alt: '' }] })}>Add item</Button>
      </PanelBody>

      <PanelBody title="Features (bottom icon strip)" initialOpen={false}>
        {features.map((item, index) => <PanelBody key={index} title={item.label || `Feature ${index + 1}`} initialOpen={false}>
          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Feature Icon</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => updateItem('features', index, 'icon', media.url)}
                render={({ open }) => (
                  <>
                    {item.icon && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={item.icon} alt="" style={{ maxWidth: '60px', maxHeight: '60px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {item.icon ? 'Change Icon' : 'Select Icon'}
                    </Button>
                    {item.icon && (
                      <Button variant="link" isDestructive onClick={() => updateItem('features', index, 'icon', '')} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
          </div>
          <TextControl label="Label" value={item.label || ''} onChange={(v) => updateItem('features', index, 'label', v)} />
          <Button isDestructive variant="secondary" onClick={() => setAttributes({ features: features.filter((_, i) => i !== index) })}>Remove feature</Button>
        </PanelBody>)}
        <Button variant="primary" onClick={() => setAttributes({ features: [...features, { icon: '', label: '' }] })}>Add feature</Button>
      </PanelBody>

      <PanelBody title="Studio gallery photos" initialOpen={false}>
        {galleryImages.map((item, index) => <PanelBody key={index} title={`Photo ${index + 1}`} initialOpen={false}>
          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Gallery Photo</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => updateItem('galleryImages', index, 'url', media.url)}
                render={({ open }) => (
                  <>
                    {item.url && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={item.url} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {item.url ? 'Change Photo' : 'Select Photo'}
                    </Button>
                    {item.url && (
                      <Button variant="link" isDestructive onClick={() => updateItem('galleryImages', index, 'url', '')} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
          </div>
          <TextControl label="Alt text" value={item.alt || ''} onChange={(v) => updateItem('galleryImages', index, 'alt', v)} />
          <Button isDestructive variant="secondary" onClick={() => setAttributes({ galleryImages: galleryImages.filter((_, i) => i !== index) })}>Remove photo</Button>
        </PanelBody>)}
        <Button variant="primary" onClick={() => setAttributes({ galleryImages: [...galleryImages, { url: '', alt: '' }] })}>Add photo</Button>
      </PanelBody>
    </InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
