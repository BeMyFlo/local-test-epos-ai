import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { preHeading, heading, subtitle, ctaText, ctaUrl } = attributes;
  const images = attributes.images || [];
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Content', 'ai-zippy')} initialOpen>
          <TextControl
            label={__('Pre-Heading', 'ai-zippy')}
            value={preHeading}
            onChange={(val) => setAttributes({ preHeading: val })}
          />
          <TextControl
            label={__('Heading', 'ai-zippy')}
            value={heading}
            onChange={(val) => setAttributes({ heading: val })}
          />
          <TextControl
            label={__('Subtitle', 'ai-zippy')}
            value={subtitle}
            onChange={(val) => setAttributes({ subtitle: val })}
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

        <PanelBody title={__('Images', 'ai-zippy')} initialOpen={false}>
          {images.map((image, index) => (
            <div key={index} style={{ marginBottom: '16px', borderBottom: '1px solid #eee', paddingBottom: '16px' }}>
              <div style={{ marginBottom: '15px' }}>
                <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Party Photo {index + 1}</label>
                <MediaUploadCheck>
                  <MediaUpload
                    allowedTypes={['image']}
                    onSelect={(media) => {
                      const updated = [...images];
                      updated[index] = { ...updated[index], url: media.url };
                      setAttributes({ images: updated });
                    }}
                    render={({ open }) => (
                      <>
                        {image.url && (
                          <div style={{ marginBottom: '8px' }}>
                            <img src={image.url} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                          </div>
                        )}
                        <Button variant="secondary" onClick={open}>
                          {image.url ? 'Change Photo' : 'Select Photo'}
                        </Button>
                        {image.url && (
                          <Button variant="link" isDestructive onClick={() => {
                            const updated = [...images];
                            updated[index] = { ...updated[index], url: '' };
                            setAttributes({ images: updated });
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
                label={__('Alt Text ', 'ai-zippy') + (index + 1)}
                value={image.alt}
                onChange={(val) => {
                  const updated = [...images];
                  updated[index] = { ...updated[index], alt: val };
                  setAttributes({ images: updated });
                }}
              />
              <Button isDestructive variant="secondary" onClick={() => setAttributes({ images: images.filter((_, i) => i !== index) })}>Remove photo</Button>
            </div>
          ))}
          <Button variant="primary" onClick={() => setAttributes({ images: [...images, { url: '', alt: '' }] })}>Add photo</Button>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender
          block="ai-zippy/home-party"
          attributes={attributes}
        />
      </div>
    </>
  );
}
