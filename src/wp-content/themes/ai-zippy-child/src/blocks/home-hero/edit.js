import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button, ToggleControl, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const { slides = [], tagline, taglineSubtitle, autoplay, autoplaySpeed } = attributes;

  const updateSlide = (index, field, value) => {
    const updated = [...slides];
    updated[index] = { ...updated[index], [field]: value };
    setAttributes({ slides: updated });
  };

  const addSlide = () => {
    setAttributes({
      slides: [...slides, { heading: 'NEW SLIDE', description: '', ctaText: 'LEARN MORE', ctaUrl: '/' }],
    });
  };

  const removeSlide = (index) => {
    const updated = slides.filter((_, i) => i !== index);
    setAttributes({ slides: updated });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title="Tagline" initialOpen={true}>
          <TextControl
            label="Tagline"
            value={tagline}
            onChange={(v) => setAttributes({ tagline: v })}
          />
          <TextareaControl
            label="Tagline Subtitle"
            value={taglineSubtitle}
            onChange={(v) => setAttributes({ taglineSubtitle: v })}
          />
        </PanelBody>

        <PanelBody title="Slider Settings" initialOpen={true}>
          <ToggleControl
            label="Autoplay"
            checked={autoplay}
            onChange={(v) => setAttributes({ autoplay: v })}
          />
          {autoplay && (
            <RangeControl
              label="Speed (ms)"
              value={autoplaySpeed}
              onChange={(v) => setAttributes({ autoplaySpeed: v })}
              min={2000}
              max={10000}
              step={500}
            />
          )}
        </PanelBody>

        {slides.map((slide, index) => (
          <PanelBody key={index} title={`Slide ${index + 1}`} initialOpen={index === 0}>
            <TextareaControl
              label="Heading"
              value={slide.heading}
              onChange={(v) => updateSlide(index, 'heading', v)}
            />
            <TextareaControl
              label="Description"
              value={slide.description}
              onChange={(v) => updateSlide(index, 'description', v)}
            />
            <TextControl
              label="CTA Text"
              value={slide.ctaText}
              onChange={(v) => updateSlide(index, 'ctaText', v)}
            />
            <TextControl
              label="CTA URL"
              value={slide.ctaUrl}
              onChange={(v) => updateSlide(index, 'ctaUrl', v)}
            />
            {slides.length > 1 && (
              <Button isDestructive variant="secondary" onClick={() => removeSlide(index)}>
                Remove Slide
              </Button>
            )}
          </PanelBody>
        ))}

        <PanelBody title="Add Slide" initialOpen={false}>
          <Button variant="primary" onClick={addSlide}>+ Add New Slide</Button>
        </PanelBody>

        <PanelBody title="Images" initialOpen={false}>
          <TextControl
            label="Background Image URL"
            value={attributes.backgroundImage}
            onChange={(v) => setAttributes({ backgroundImage: v })}
          />
          <TextControl
            label="Decor Left Image URL"
            value={attributes.decorLeftImage}
            onChange={(v) => setAttributes({ decorLeftImage: v })}
          />
          <TextControl
            label="Decor Right Image URL"
            value={attributes.decorRightImage}
            onChange={(v) => setAttributes({ decorRightImage: v })}
          />
          <TextControl
            label="Decor Bottom Image URL"
            value={attributes.decorBottomImage}
            onChange={(v) => setAttributes({ decorBottomImage: v })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block={name} attributes={attributes} />
      </div>
    </>
  );
}
