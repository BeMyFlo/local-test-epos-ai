import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const { heading, testimonials } = attributes;
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
            <TextControl
              label={__('Avatar URL', 'ai-zippy')}
              value={testimonial.avatar}
              onChange={(val) => {
                const updated = [...testimonials];
                updated[index] = { ...updated[index], avatar: val };
                setAttributes({ testimonials: updated });
              }}
            />
          </PanelBody>
        ))}
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
