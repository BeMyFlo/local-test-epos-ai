import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title="Hero Content" initialOpen={true}>
          <TextControl
            label="Eyebrow"
            value={attributes.eyebrow}
            onChange={(value) => setAttributes({ eyebrow: value })}
          />
          <TextareaControl
            label="Heading"
            value={attributes.heading}
            onChange={(value) => setAttributes({ heading: value })}
          />
          <TextareaControl
            label="Subtitle"
            value={attributes.subtitle}
            onChange={(value) => setAttributes({ subtitle: value })}
          />
          <TextControl
            label="CTA Text"
            value={attributes.ctaText}
            onChange={(value) => setAttributes({ ctaText: value })}
          />
          <TextControl
            label="CTA URL"
            value={attributes.ctaUrl}
            onChange={(value) => setAttributes({ ctaUrl: value })}
          />
          <SelectControl
            label="Color Variant"
            value={attributes.variant}
            options={[
              { label: 'Pink', value: 'pink' },
              { label: 'Blue', value: 'blue' },
              { label: 'Yellow', value: 'yellow' },
              { label: 'Lavender', value: 'lavender' },
            ]}
            onChange={(value) => setAttributes({ variant: value })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block={name} attributes={attributes} />
      </div>
    </>
  );
}
