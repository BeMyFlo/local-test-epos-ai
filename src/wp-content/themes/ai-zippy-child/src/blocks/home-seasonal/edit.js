import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();

  const updateProduct = (index, key, value) => {
    const updated = [...attributes.products];
    updated[index] = { ...updated[index], [key]: value };
    setAttributes({ products: updated });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title="Section" initialOpen={true}>
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
        {attributes.products.map((product, i) => (
          <PanelBody key={i} title={`Product ${i + 1}: ${product.name}`} initialOpen={false}>
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
            <TextControl
              label="Image URL"
              value={product.image}
              onChange={(v) => updateProduct(i, 'image', v)}
            />
            <TextControl
              label="CTA URL"
              value={product.ctaUrl}
              onChange={(v) => updateProduct(i, 'ctaUrl', v)}
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
