import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();

  const updateBrand = (index, key, value) => {
    const updated = [...attributes.brands];
    updated[index] = { ...updated[index], [key]: value };
    setAttributes({ brands: updated });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title="Content" initialOpen={true}>
          <TextareaControl
            label="Heading"
            value={attributes.heading}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextareaControl
            label="Description"
            value={attributes.description}
            onChange={(v) => setAttributes({ description: v })}
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
        {attributes.brands.map((brand, i) => (
          <PanelBody key={i} title={`Brand ${i + 1}`} initialOpen={false}>
            <TextControl
              label="Name"
              value={brand.name}
              onChange={(v) => updateBrand(i, 'name', v)}
            />
            <TextControl
              label="Icon URL"
              value={brand.icon}
              onChange={(v) => updateBrand(i, 'icon', v)}
            />
          </PanelBody>
        ))}
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block={name} attributes={attributes} />
      </div>
    </>
  );
}
