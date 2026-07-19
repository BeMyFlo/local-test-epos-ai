import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, RangeControl, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const slides = attributes.slides || [];

  const updateSlide = (index, field, value) => {
    const updatedSlides = slides.map((slide, slideIndex) => (
      slideIndex === index ? { ...slide, [field]: value } : slide
    ));
    setAttributes({ slides: updatedSlides });
  };

  const addSlide = () => {
    setAttributes({
      slides: [
        ...slides,
        {
          heading: 'NEW SLIDE',
          description: '',
          ctaText: 'LEARN MORE',
          ctaUrl: '#',
        },
      ],
    });
  };

  const removeSlide = (index) => {
    setAttributes({ slides: slides.filter((_, slideIndex) => slideIndex !== index) });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title="Tagline" initialOpen={true}>
          <TextControl label="Tagline" value={attributes.tagline || ''} onChange={(tagline) => setAttributes({ tagline })} />
          <TextareaControl label="Tagline description" value={attributes.taglineDescription || ''} onChange={(taglineDescription) => setAttributes({ taglineDescription })} />
        </PanelBody>
        <PanelBody title="Decoration" initialOpen={true}>
          <TextControl label="Paint jar image URL" value={attributes.paintJarImage || ''} onChange={(paintJarImage) => setAttributes({ paintJarImage })} />
          <TextControl label="Paint jar alt text" value={attributes.paintJarAlt || ''} onChange={(paintJarAlt) => setAttributes({ paintJarAlt })} />
        </PanelBody>
        <PanelBody title="Slider settings" initialOpen={true}>
          <ToggleControl label="Autoplay" checked={attributes.autoplay === true} onChange={(autoplay) => setAttributes({ autoplay })} />
          {attributes.autoplay === true && (
            <RangeControl
              label="Autoplay speed (milliseconds)"
              value={attributes.autoplaySpeed || 5000}
              onChange={(autoplaySpeed) => setAttributes({ autoplaySpeed })}
              min={2000}
              max={10000}
              step={500}
            />
          )}
        </PanelBody>
        {slides.map((slide, index) => (
          <PanelBody key={index} title={`Slide ${index + 1}`} initialOpen={index === 0}>
            <TextareaControl label="Heading" value={slide.heading || ''} onChange={(heading) => updateSlide(index, 'heading', heading)} />
            <TextareaControl label="Description" value={slide.description || ''} onChange={(description) => updateSlide(index, 'description', description)} />
            <TextControl label="CTA text" value={slide.ctaText || ''} onChange={(ctaText) => updateSlide(index, 'ctaText', ctaText)} />
            <TextControl label="CTA URL" value={slide.ctaUrl || ''} onChange={(ctaUrl) => updateSlide(index, 'ctaUrl', ctaUrl)} />
            {slides.length > 1 && (
              <Button variant="secondary" isDestructive onClick={() => removeSlide(index)}>
                Remove slide
              </Button>
            )}
          </PanelBody>
        ))}
        <PanelBody title="Add slide" initialOpen={false}>
          <Button variant="primary" onClick={addSlide}>Add new slide</Button>
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block={name} attributes={attributes} />
      </div>
    </>
  );
}
