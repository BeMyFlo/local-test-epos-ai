import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import DecorPositionControls from '../_shared/DecorPositionControls.js';

export default function Edit({ attributes, setAttributes }) {
  const {
    heading = '', description = '', serviceImage = '', serviceImageAlt = '', infoBox = {}, termsTitle = '', terms = [], suppliesTitle = '', suppliesText = '',
    ctaText = '', ctaUrl = '', programmeTitle = '', programmeSubtitle = '', programmeContent = [], lessonsTitle = '', lessons = [], galleryTitle = '', galleryImages = [],
    decorLeftImage = '', decorLeftAlt = '', decorRightImage = '', decorRightAlt = '',
  } = attributes;
  const blockProps = useBlockProps();
  const updateInfo = (key, value) => setAttributes({ infoBox: { ...infoBox, [key]: value } });
  const updateGalleryImage = (index, media) => {
    const next = Array.from({ length: 4 }, (_, itemIndex) => galleryImages[itemIndex] || { url: '', alt: `Student artwork photo ${itemIndex + 1}` });
    next[index] = { ...next[index], url: media.url, alt: media.alt || next[index]?.alt || '' };
    setAttributes({ galleryImages: next });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Tagline', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Tagline', 'ai-zippy')} value={attributes.tagline || ''} onChange={(tagline) => setAttributes({ tagline })} />
          <TextareaControl label={__('Tagline description', 'ai-zippy')} value={attributes.taglineDescription || ''} onChange={(taglineDescription) => setAttributes({ taglineDescription })} />
        </PanelBody>

        <PanelBody title={__('Cartoon & Mascot Decorations', 'ai-zippy')} initialOpen={true}>
          <div style={{ marginBottom: '20px' }}>
            <strong style={{ display: 'block', marginBottom: '8px' }}>Mascot Left / Rocket Image</strong>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorLeftImage: media.url })}
                render={({ open }) => (
                  <>
                    {decorLeftImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={decorLeftImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {decorLeftImage ? __('Change Image', 'ai-zippy') : __('Select Image', 'ai-zippy')}
                    </Button>
                    {decorLeftImage && (
                      <Button variant="link" isDestructive onClick={() => setAttributes({ decorLeftImage: '' })} style={{ marginLeft: '10px' }}>
                        {__('Remove', 'ai-zippy')}
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
            <TextControl label={__('Alt Text', 'ai-zippy')} value={decorLeftAlt} onChange={(val) => setAttributes({ decorLeftAlt: val })} style={{ marginTop: '8px' }} />
            <DecorPositionControls
              attributes={attributes}
              setAttributes={setAttributes}
              prefix="decorLeft"
              defaults={{ desktopX: 85, desktopY: 65, desktopSize: 180, mobileX: 85, mobileY: 65, mobileSize: 120, zIndex: 5 }}
            />
          </div>

          <div style={{ marginBottom: '20px' }}>
            <strong style={{ display: 'block', marginBottom: '8px' }}>Mascot Right / Bear Image</strong>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                onSelect={(media) => setAttributes({ decorRightImage: media.url })}
                render={({ open }) => (
                  <>
                    {decorRightImage && (
                      <div style={{ marginBottom: '8px' }}>
                        <img src={decorRightImage} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                      </div>
                    )}
                    <Button variant="secondary" onClick={open}>
                      {decorRightImage ? __('Change Image', 'ai-zippy') : __('Select Image', 'ai-zippy')}
                    </Button>
                    {decorRightImage && (
                      <Button variant="link" isDestructive onClick={() => setAttributes({ decorRightImage: '' })} style={{ marginLeft: '10px' }}>
                        {__('Remove', 'ai-zippy')}
                      </Button>
                    )}
                  </>
                )}
              />
            </MediaUploadCheck>
            <TextControl label={__('Alt Text', 'ai-zippy')} value={decorRightAlt} onChange={(val) => setAttributes({ decorRightAlt: val })} style={{ marginTop: '8px' }} />
            <DecorPositionControls
              attributes={attributes}
              setAttributes={setAttributes}
              prefix="decorRight"
              defaults={{ desktopX: 15, desktopY: 85, desktopSize: 200, mobileX: 15, mobileY: 85, mobileSize: 130, zIndex: 5 }}
            />
          </div>
        </PanelBody>

        <PanelBody title={__('Course Overview', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Heading', 'ai-zippy')} value={heading} onChange={(value) => setAttributes({ heading: value })} />
          <TextareaControl label={__('Description', 'ai-zippy')} value={description} onChange={(value) => setAttributes({ description: value })} />
          <TextControl label={__('Lessons', 'ai-zippy')} value={infoBox.lessons || ''} onChange={(value) => updateInfo('lessons', value)} />
          <TextControl label={__('Duration', 'ai-zippy')} value={infoBox.duration || ''} onChange={(value) => updateInfo('duration', value)} />
          <TextControl label={__('Age Range', 'ai-zippy')} value={infoBox.ageRange || ''} onChange={(value) => updateInfo('ageRange', value)} />
        </PanelBody>

        <PanelBody title={__('Terms and Supplies', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Terms Title', 'ai-zippy')} value={termsTitle} onChange={(value) => setAttributes({ termsTitle: value })} />
          <TextareaControl label={__('Terms (one per line)', 'ai-zippy')} value={terms.join('\n')} onChange={(value) => setAttributes({ terms: value.split('\n').filter(Boolean) })} />
          <TextControl label={__('Supplies Title', 'ai-zippy')} value={suppliesTitle} onChange={(value) => setAttributes({ suppliesTitle: value })} />
          <TextareaControl label={__('Supplies Text', 'ai-zippy')} value={suppliesText} onChange={(value) => setAttributes({ suppliesText: value })} />
          <TextControl label={__('CTA Text', 'ai-zippy')} value={ctaText} onChange={(value) => setAttributes({ ctaText: value })} />
          <TextControl label={__('CTA URL', 'ai-zippy')} value={ctaUrl} onChange={(value) => setAttributes({ ctaUrl: value })} />
        </PanelBody>

        <PanelBody title={__('Programme Content', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Programme Title', 'ai-zippy')} value={programmeTitle} onChange={(value) => setAttributes({ programmeTitle: value })} />
          <TextControl label={__('Programme Subtitle', 'ai-zippy')} value={programmeSubtitle} onChange={(value) => setAttributes({ programmeSubtitle: value })} />
          <TextareaControl label={__('Items (one per line)', 'ai-zippy')} value={programmeContent.join('\n')} onChange={(value) => setAttributes({ programmeContent: value.split('\n').filter(Boolean) })} />
        </PanelBody>

        <PanelBody title={__('Gallery', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Gallery Title', 'ai-zippy')} value={galleryTitle} onChange={(value) => setAttributes({ galleryTitle: value })} />
          {Array.from({ length: 3 }, (_, index) => galleryImages[index] || { url: '', alt: `Student artwork photo ${index + 1}` }).map((image, index) => (
            <MediaUploadCheck key={index}>
              <MediaUpload allowedTypes={['image']} onSelect={(media) => updateGalleryImage(index, media)} render={({ open }) => <Button variant="secondary" onClick={open}>{image.url ? __('Change Image', 'ai-zippy') : `Select Image ${index + 1}`}</Button>} />
            </MediaUploadCheck>
          ))}
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/course-intro" attributes={attributes} /></div>
    </>
  );
}
