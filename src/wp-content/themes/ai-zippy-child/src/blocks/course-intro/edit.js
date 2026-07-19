import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
  const {
    heading = '', description = '', serviceImage = '', serviceImageAlt = '', infoBox = {}, termsTitle = '', terms = [], suppliesTitle = '', suppliesText = '',
    ctaText = '', ctaUrl = '', programmeTitle = '', programmeSubtitle = '', programmeContent = [], lessonsTitle = '', lessons = [], galleryTitle = '', galleryImages = [],
  } = attributes;
  const blockProps = useBlockProps();
  const updateInfo = (key, value) => setAttributes({ infoBox: { ...infoBox, [key]: value } });
  const updateGalleryImage = (index, media) => {
    const next = Array.from({ length: 4 }, (_, itemIndex) => galleryImages[itemIndex] || { url: '', alt: `Student artwork photo ${itemIndex + 1}` });
    next[index] = { ...next[index], url: media.url, alt: media.alt || next[index]?.alt || '' };
    setAttributes({ galleryImages: next });
  };
  const updateLesson = (index, key, value) => {
    const next = lessons.map((lesson, lessonIndex) => lessonIndex === index ? { ...lesson, [key]: value } : lesson);
    setAttributes({ lessons: next });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Tagline', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Tagline', 'ai-zippy')} value={attributes.tagline || ''} onChange={(tagline) => setAttributes({ tagline })} />
          <TextareaControl label={__('Tagline description', 'ai-zippy')} value={attributes.taglineDescription || ''} onChange={(taglineDescription) => setAttributes({ taglineDescription })} />
        </PanelBody>
        <PanelBody title={__('Course Overview', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Heading', 'ai-zippy')} value={heading} onChange={(value) => setAttributes({ heading: value })} />
          <TextareaControl label={__('Description', 'ai-zippy')} value={description} onChange={(value) => setAttributes({ description: value })} />
          <MediaUploadCheck>
            <MediaUpload allowedTypes={['image']} onSelect={(media) => setAttributes({ serviceImage: media.url, serviceImageAlt: media.alt || serviceImageAlt })} render={({ open }) => <Button variant="secondary" onClick={open}>{serviceImage ? __('Change Service Image', 'ai-zippy') : __('Select Service Image', 'ai-zippy')}</Button>} />
          </MediaUploadCheck>
          <TextControl label={__('Service Image Alt Text', 'ai-zippy')} value={serviceImageAlt} onChange={(value) => setAttributes({ serviceImageAlt: value })} />
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
        <PanelBody title={__('Other Lessons', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Section Title', 'ai-zippy')} value={lessonsTitle} onChange={(value) => setAttributes({ lessonsTitle: value })} />
          {lessons.map((lesson, index) => (
            <div key={`${lesson.url}-${index}`} className="achiever-editor-list-item">
              <TextControl label={__('Lesson Name', 'ai-zippy')} value={lesson.name || ''} onChange={(value) => updateLesson(index, 'name', value)} />
              <TextControl label={__('Age Range', 'ai-zippy')} value={lesson.age || ''} onChange={(value) => updateLesson(index, 'age', value)} />
              <TextControl label={__('Lesson URL', 'ai-zippy')} value={lesson.url || ''} onChange={(value) => updateLesson(index, 'url', value)} />
            </div>
          ))}
        </PanelBody>
        <PanelBody title={__('Gallery', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Gallery Title', 'ai-zippy')} value={galleryTitle} onChange={(value) => setAttributes({ galleryTitle: value })} />
          {Array.from({ length: 4 }, (_, index) => galleryImages[index] || { url: '', alt: `Student artwork photo ${index + 1}` }).map((image, index) => (
            <MediaUploadCheck key={index}><MediaUpload allowedTypes={['image']} onSelect={(media) => updateGalleryImage(index, media)} render={({ open }) => <Button variant="secondary" onClick={open}>{image.url ? __('Change Image', 'ai-zippy') : `Select Image ${index + 1}`}</Button>} /></MediaUploadCheck>
          ))}
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/course-intro" attributes={attributes} /></div>
    </>
  );
}
