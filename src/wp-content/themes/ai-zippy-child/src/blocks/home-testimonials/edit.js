import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import DecorPositionControls from '../_shared/DecorPositionControls.js';
export default function Edit({ attributes, setAttributes }) {
  const { heading } = attributes;
  const testimonials = attributes.testimonials || [];
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Content', 'ai-zippy')} initialOpen>
          <TextareaControl
            label={__('Heading', 'ai-zippy')}
            value={heading}
            onChange={(val) => setAttributes({ heading: val })}
            help={__('Use line breaks for multi-line heading.', 'ai-zippy')}
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
              defaults={{ desktopX: 8, desktopY: 85, desktopSize: 110, mobileX: 12, mobileY: 85, mobileSize: 75, zIndex: 5 }}
            />
          </div>

          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Decor Right Image (Pencil cartoon mascot)</label>
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
              defaults={{ desktopX: 92, desktopY: 85, desktopSize: 110, mobileX: 88, mobileY: 85, mobileSize: 75, zIndex: 5 }}
            />
          </div>
        </PanelBody>

        {testimonials.map((testimonial, index) => (
          <PanelBody
            key={index}
            title={__('Testimonial ', 'ai-zippy') + (index + 1)}
            initialOpen={false}
          >
            <TextControl
              label={__('Name', 'ai-zippy')}
              value={testimonial.name}
              onChange={(val) => {
                const updated = [...testimonials];
                updated[index] = { ...updated[index], name: val };
                setAttributes({ testimonials: updated });
              }}
            />
            <TextareaControl
              label={__('Quote', 'ai-zippy')}
              value={testimonial.quote}
              onChange={(val) => {
                const updated = [...testimonials];
                updated[index] = { ...updated[index], quote: val };
                setAttributes({ testimonials: updated });
              }}
            />
            <Button isDestructive variant="secondary" onClick={() => setAttributes({ testimonials: testimonials.filter((_, i) => i !== index) })}>Remove testimonial</Button>
          </PanelBody>
        ))}
        <PanelBody title={__('Add testimonial', 'ai-zippy')} initialOpen={false}>
          <Button variant="primary" onClick={() => setAttributes({ testimonials: [...testimonials, { name: '', quote: '' }] })}>Add testimonial</Button>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender
          block="ai-zippy/home-testimonials"
          attributes={attributes}
        />
      </div>
    </>
  );
}
