import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
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
        <PanelBody title="Decoration / Cartoon Images" initialOpen={true}>
          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Background Image</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ backgroundImage: media.url })}
                render={({ open }) => (
                  <>
                    {attributes.backgroundImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={attributes.backgroundImage} alt="" style={{ maxWidth: '100%', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {attributes.backgroundImage ? 'Change Background Image' : 'Select Background Image'}
                    </Button>
                    {attributes.backgroundImage && (
                      <Button variant="link" isDestructive onClick={() => setAttributes({ backgroundImage: '' })} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
          </div>

          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Paint Jar Image</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ paintJarImage: media.url })}
                render={({ open }) => (
                  <>
                    {attributes.paintJarImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={attributes.paintJarImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {attributes.paintJarImage ? 'Change Image' : 'Select Image'}
                    </Button>
                    {attributes.paintJarImage && (
                      <Button variant="link" isDestructive onClick={() => setAttributes({ paintJarImage: '' })} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
            <TextControl label="Paint jar alt text" value={attributes.paintJarAlt || ''} onChange={(paintJarAlt) => setAttributes({ paintJarAlt })} style={{ marginTop: '8px' }} />
            <RangeControl
              label="Z-index"
              value={attributes.paintJarZIndex ?? 2}
              onChange={(paintJarZIndex) => setAttributes({ paintJarZIndex })}
              min={-1}
              max={50}
              step={1}
            />
            <p><strong>Desktop position</strong></p>
            <RangeControl
              label="Horizontal position (%)"
              value={attributes.paintJarDesktopX ?? 93}
              onChange={(paintJarDesktopX) => setAttributes({ paintJarDesktopX })}
              min={0}
              max={100}
              step={1}
            />
            <RangeControl
              label="Vertical position (%)"
              value={attributes.paintJarDesktopY ?? 92}
              onChange={(paintJarDesktopY) => setAttributes({ paintJarDesktopY })}
              min={-50}
              max={150}
              step={1}
            />
            <RangeControl
              label="Image size (px)"
              value={attributes.paintJarDesktopSize ?? 90}
              onChange={(paintJarDesktopSize) => setAttributes({ paintJarDesktopSize })}
              min={40}
              max={500}
              step={1}
            />
            <p><strong>Mobile position</strong></p>
            <RangeControl
              label="Horizontal position (%)"
              value={attributes.paintJarMobileX ?? 88}
              onChange={(paintJarMobileX) => setAttributes({ paintJarMobileX })}
              min={0}
              max={100}
              step={1}
            />
            <RangeControl
              label="Vertical position (%)"
              value={attributes.paintJarMobileY ?? 91}
              onChange={(paintJarMobileY) => setAttributes({ paintJarMobileY })}
              min={-50}
              max={150}
              step={1}
            />
            <RangeControl
              label="Image size (px)"
              value={attributes.paintJarMobileSize ?? 90}
              onChange={(paintJarMobileSize) => setAttributes({ paintJarMobileSize })}
              min={32}
              max={500}
              step={1}
            />
          </div>
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
