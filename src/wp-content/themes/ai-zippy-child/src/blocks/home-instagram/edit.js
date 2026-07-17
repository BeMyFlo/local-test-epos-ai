import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { heading, images, instagramUrl } = attributes;
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
          <TextControl
            label={__('Instagram URL', 'ai-zippy')}
            value={instagramUrl}
            onChange={(val) => setAttributes({ instagramUrl: val })}
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
          block="ai-zippy/home-instagram"
          attributes={attributes}
        />
      </div>
    </>
  );
}
