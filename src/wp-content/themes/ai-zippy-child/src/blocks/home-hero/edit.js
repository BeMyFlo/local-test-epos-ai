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
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Decor Left Image (Top/Left mascot)</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorLeftImage: media.url })}
                render={({ open }) => (
                  <>
                    {attributes.decorLeftImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={attributes.decorLeftImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {attributes.decorLeftImage ? 'Change Image' : 'Select Image'}
                    </Button>
                    {attributes.decorLeftImage && (
                      <Button variant="link" isDestructive onClick={() => setAttributes({ decorLeftImage: '' })} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
            <TextControl label="Decor Left Alt" value={attributes.decorLeftAlt || ''} onChange={(decorLeftAlt) => setAttributes({ decorLeftAlt })} style={{ marginTop: '8px' }} />
          </div>

          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Decor Right Image (Top/Right mascot)</label>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorRightImage: media.url })}
                render={({ open }) => (
                  <>
                    {attributes.decorRightImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={attributes.decorRightImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {attributes.decorRightImage ? 'Change Image' : 'Select Image'}
                    </Button>
                    {attributes.decorRightImage && (
                      <Button variant="link" isDestructive onClick={() => setAttributes({ decorRightImage: '' })} style={{ marginLeft: '10px' }}>
                        Remove
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
            <TextControl label="Decor Right Alt" value={attributes.decorRightAlt || ''} onChange={(decorRightAlt) => setAttributes({ decorRightAlt })} style={{ marginTop: '8px' }} />
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
