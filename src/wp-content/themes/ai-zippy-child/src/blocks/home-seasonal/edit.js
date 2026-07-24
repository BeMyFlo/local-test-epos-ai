import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();

  const updateProduct = (index, key, value) => {
    const updated = [...(attributes.products || [])];
    updated[index] = { ...updated[index], [key]: value };
    setAttributes({ products: updated });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title="Section Settings" initialOpen={true}>
          <TextControl
            label="Section Title"
            value={attributes.sectionTitle}
            onChange={(v) => setAttributes({ sectionTitle: v })}
          />
          <TextControl
            label="CTA Text"
            value={attributes.ctaText}
            onChange={(v) => setAttributes({ ctaText: v })}
          />
          <TextControl
            label="CTA URL"
            value={attributes.ctaUrl}
            onChange={(v) => setAttributes({ ctaUrl: v })}
          />
        </PanelBody>

        <PanelBody title="Decoration / Cartoon Images" initialOpen={true}>
          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Brush Decor Image</label>
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
            <TextControl label="Brush Decor Alt" value={attributes.decorAlt || ''} onChange={(decorAlt) => setAttributes({ decorAlt })} style={{ marginTop: '8px' }} />
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
          </div>
        </PanelBody>
        {(attributes.products || []).map((product, i) => (
          <PanelBody key={i} title={`Product ${i + 1}: ${product.name || 'Untitled'}`} initialOpen={false}>
            <TextControl
              label="Name"
              value={product.name}
              onChange={(v) => updateProduct(i, 'name', v)}
            />
            <TextControl
              label="Category"
              value={product.category}
              onChange={(v) => updateProduct(i, 'category', v)}
            />
            <TextControl
              label="Age + Time"
              value={product.ageTime}
              onChange={(v) => updateProduct(i, 'ageTime', v)}
            />
            <div style={{ marginBottom: '15px' }}>
              <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Product Image</label>
              <MediaUploadCheck>
                <MediaUpload
                  allowedTypes={['image']}
                  onSelect={(media) => updateProduct(i, 'image', media.url)}
                  render={({ open }) => (
                    <>
                      {product.image && (
                        <div style={{ marginBottom: '8px' }}>
                          <img src={product.image} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                        </div>
                      )}
                      <Button variant="secondary" onClick={open}>
                        {product.image ? 'Change Image' : 'Select Image'}
                      </Button>
                      {product.image && (
                        <Button variant="link" isDestructive onClick={() => updateProduct(i, 'image', '')} style={{ marginLeft: '10px' }}>
                          Remove
                        </Button>
                      )}
                    </>
                  )}
                />
              </MediaUploadCheck>
            </div>
            <TextControl
              label="Image alt text"
              value={product.alt || ''}
              onChange={(v) => updateProduct(i, 'alt', v)}
            />
            <TextControl label="Product URL" value={product.url || ''} onChange={(v) => updateProduct(i, 'url', v)} />
            <Button isDestructive variant="secondary" onClick={() => setAttributes({ products: attributes.products.filter((_, index) => index !== i) })}>Remove product</Button>
          </PanelBody>
        ))}
        <PanelBody title="Add product" initialOpen={false}>
          <Button variant="primary" onClick={() => setAttributes({ products: [...(attributes.products || []), { name: '', category: '', ageTime: '', image: '', alt: '', url: '' }] })}>Add product</Button>
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block={name} attributes={attributes} />
      </div>
    </>
  );
}
