import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { sectionTitle, products, ctaText, ctaUrl } = attributes;
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
            <TextControl
              label={__('Image URL', 'ai-zippy')}
              value={product.image}
              onChange={(val) => {
                const updated = [...products];
                updated[index] = { ...updated[index], image: val };
                setAttributes({ products: updated });
              }}
            />
            <TextControl
              label={__('Product URL', 'ai-zippy')}
              value={product.ctaUrl}
              onChange={(val) => {
                const updated = [...products];
                updated[index] = { ...updated[index], ctaUrl: val };
                setAttributes({ products: updated });
              }}
            />
          </PanelBody>
        ))}
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
