import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import DecorPositionControls from '../_shared/DecorPositionControls.js';
export default function Edit({ attributes, setAttributes }) {
  const { heading } = attributes;
  const images = attributes.images || [];
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Content', 'ai-zippy')} initialOpen>
          <TextControl
            label={__('Heading', 'ai-zippy')}
            value={heading}
            onChange={(val) => setAttributes({ heading: val })}
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
              defaults={{ desktopX: 8, desktopY: 12, desktopSize: 100, mobileX: 12, mobileY: 12, mobileSize: 75, zIndex: 5 }}
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
              defaults={{ desktopX: 92, desktopY: 12, desktopSize: 100, mobileX: 88, mobileY: 12, mobileSize: 75, zIndex: 5 }}
            />
          </div>
        </PanelBody>

        <PanelBody title={__('Images', 'ai-zippy')} initialOpen={false}>
          {images.map((image, index) => (
            <div key={index} style={{ marginBottom: '16px', borderBottom: '1px solid #eee', paddingBottom: '16px' }}>
              <div style={{ marginBottom: '15px' }}>
                <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Instagram Photo {index + 1}</label>
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
                            <SelectedImagePreview url={image.url} alt={image.alt} fallback="Selected image" />
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
              <TextControl
                label={__('Link URL ', 'ai-zippy') + (index + 1)}
                value={image.link || ''}
                onChange={(val) => {
                  const updated = [...images];
                  updated[index] = { ...updated[index], link: val };
                  setAttributes({ images: updated });
                }}
              />
              <Button isDestructive variant="secondary" onClick={() => setAttributes({ images: images.filter((_, i) => i !== index) })}>Remove image</Button>
            </div>
          ))}
          <Button variant="primary" onClick={() => setAttributes({ images: [...images, { url: '', alt: '', link: '' }] })}>Add image</Button>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender
          block="ai-zippy/home-instagram"
          attributes={attributes}
        />
      </div>
    </>
  );
}
