import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { preHeading, heading, subtitle, images, ctaText, ctaUrl } = attributes;
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
        <PanelBody title={__('Images', 'ai-zippy')} initialOpen={false}>
          {images.map((image, index) => (
            <div key={index} style={{ marginBottom: '16px' }}>
              <TextControl
                label={__('Image URL ', 'ai-zippy') + (index + 1)}
                value={image.url}
                onChange={(val) => {
                  const updated = [...images];
                  updated[index] = { ...updated[index], url: val };
                  setAttributes({ images: updated });
                }}
              />
              <TextControl
                label={__('Alt Text ', 'ai-zippy') + (index + 1)}
                value={image.alt}
                onChange={(val) => {
                  const updated = [...images];
                  updated[index] = { ...updated[index], alt: val };
                  setAttributes({ images: updated });
                }}
              />
            </div>
          ))}
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
