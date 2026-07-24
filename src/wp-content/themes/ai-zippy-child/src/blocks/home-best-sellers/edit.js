import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { sectionTitle, ctaText, ctaUrl } = attributes;
  const products = attributes.products || [];
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Content', 'ai-zippy')} initialOpen>
          <TextControl
            label={__('Section Title', 'ai-zippy')}
            value={sectionTitle}
            onChange={(val) => setAttributes({ sectionTitle: val })}
          />
          <TextControl
            label={__('CTA Text', 'ai-zippy')}
            value={ctaText}
            onChange={(val) => setAttributes({ ctaText: val })}
          />
          <TextControl
            label={__('CTA URL', 'ai-zippy')}
            value={ctaUrl}
            onChange={(val) => setAttributes({ ctaUrl: val })}
          />
        </PanelBody>

        <PanelBody title={__('Decoration / Cartoon Images', 'ai-zippy')} initialOpen={true}>
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

        {products.map((product, index) => (
          <PanelBody
            key={index}
            title={__('Product ', 'ai-zippy') + (index + 1)}
            initialOpen={false}
          >
            <TextControl
              label={__('Name', 'ai-zippy')}
              value={product.name}
              onChange={(val) => {
                const updated = [...products];
                updated[index] = { ...updated[index], name: val };
                setAttributes({ products: updated });
              }}
            />
            <TextControl
              label={__('Category', 'ai-zippy')}
              value={product.category}
              onChange={(val) => {
                const updated = [...products];
                updated[index] = { ...updated[index], category: val };
                setAttributes({ products: updated });
              }}
            />
            <TextControl
              label={__('Age + Time', 'ai-zippy')}
              value={product.ageTime}
              onChange={(val) => {
                const updated = [...products];
                updated[index] = { ...updated[index], ageTime: val };
                setAttributes({ products: updated });
              }}
            />
            <div style={{ marginBottom: '15px' }}>
              <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Product Image</label>
              <MediaUploadCheck>
                <MediaUpload
                  allowedTypes={['image']}
                  onSelect={(media) => {
                    const updated = [...products];
                    updated[index] = { ...updated[index], image: media.url };
                    setAttributes({ products: updated });
                  }}
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
                        <Button variant="link" isDestructive onClick={() => {
                          const updated = [...products];
                          updated[index] = { ...updated[index], image: '' };
                          setAttributes({ products: updated });
                        }} style={{ marginLeft: '10px' }}>
                          Remove
                        </Button>
                      )}
                    </>
                  )}
                />
              </MediaUploadCheck>
            </div>
            <TextControl
              label={__('Product URL', 'ai-zippy')}
              value={product.url || ''}
              onChange={(val) => {
                const updated = [...products];
                updated[index] = { ...updated[index], url: val };
                setAttributes({ products: updated });
              }}
            />
            <TextControl label={__('Image alt text', 'ai-zippy')} value={product.alt || ''} onChange={(val) => { const updated = [...products]; updated[index] = { ...updated[index], alt: val }; setAttributes({ products: updated }); }} />
            <Button isDestructive variant="secondary" onClick={() => setAttributes({ products: products.filter((_, i) => i !== index) })}>Remove product</Button>
          </PanelBody>
        ))}
        <PanelBody title={__('Add product', 'ai-zippy')} initialOpen={false}>
          <Button variant="primary" onClick={() => setAttributes({ products: [...products, { name: '', category: '', ageTime: '', image: '', alt: '', url: '' }] })}>Add product</Button>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender
          block="ai-zippy/home-best-sellers"
          attributes={attributes}
        />
      </div>
    </>
  );
}
