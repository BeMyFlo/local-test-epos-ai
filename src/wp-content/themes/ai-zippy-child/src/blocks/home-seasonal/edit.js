import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import DecorPositionControls from '../_shared/DecorPositionControls.js';
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
            label="Card Button Text"
            value={attributes.cardCtaText ?? 'BOOK NOW'}
            onChange={(cardCtaText) => setAttributes({ cardCtaText })}
          />
          <TextControl
            label="Bottom CTA Text"
            value={attributes.ctaText ?? 'VIEW MORE WORKSHOP'}
            onChange={(ctaText) => setAttributes({ ctaText })}
          />
          <TextControl
            label="Bottom CTA URL"
            value={attributes.ctaUrl ?? '#'}
            onChange={(ctaUrl) => setAttributes({ ctaUrl })}
          />
        </PanelBody>

        <PanelBody title="Decoration / Cartoon Images" initialOpen={true}>
          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Top Left Mascot Image</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorImage: media.url })}
                render={({ open }) => (
                  <>
                    {attributes.decorImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <SelectedImagePreview url={attributes.decorImage} alt={attributes.decorAlt} fallback="Selected decoration" />
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
            <TextControl label="Top Left Mascot Alt" value={attributes.decorAlt || ''} onChange={(decorAlt) => setAttributes({ decorAlt })} style={{ marginTop: '8px' }} />
            <DecorPositionControls
              attributes={attributes}
              setAttributes={setAttributes}
              prefix="decor"
              defaults={{ desktopX: 12, desktopY: 12, desktopSize: 278, mobileX: 15, mobileY: 10, mobileSize: 150, zIndex: 5 }}
            />
          </div>

          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Bottom Left Mascot Image</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorLeftImage: media.url })}
                render={({ open }) => (
                  <>
                    {attributes.decorLeftImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <SelectedImagePreview url={attributes.decorLeftImage} alt={attributes.decorLeftAlt} fallback="Selected left decoration" />
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
              defaults={{ desktopX: 18, desktopY: 92, desktopSize: 405, mobileX: 20, mobileY: 92, mobileSize: 220, zIndex: 5 }}
            />
          </div>

          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Top Right Pencils Image</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorRightImage: media.url })}
                render={({ open }) => (
                  <>
                    {attributes.decorRightImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <SelectedImagePreview url={attributes.decorRightImage} alt={attributes.decorRightAlt} fallback="Selected right decoration" />
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
              defaults={{ desktopX: 88, desktopY: 10, desktopSize: 310, mobileX: 85, mobileY: 10, mobileSize: 140, zIndex: 5 }}
            />
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
                          <SelectedImagePreview url={product.image} alt={product.alt} fallback="Selected product image" />
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
