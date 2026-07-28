import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import DecorPositionControls from '../_shared/DecorPositionControls.js';
import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
export default function Edit({ attributes, setAttributes }) {
  const { sections = [], galleryTitle = '', galleryImages = [] } = attributes;
  const blockProps = useBlockProps();
  const updateSection = (index, key, value) => {
    const next = [...sections];
    next[index] = { ...next[index], [key]: value };
    setAttributes({ sections: next });
  };
  const updateGalleryImage = (index, media) => {
    const next = [...galleryImages];
    next[index] = { ...next[index], url: media.url, alt: media.alt || next[index]?.alt || '' };
    setAttributes({ galleryImages: next });
  };
  const decorations = [
    { label: __('Top pencil', 'ai-zippy'), prefix: 'topPencil', image: 'topPencilImage', alt: 'topPencilAlt', defaults: { zIndex: 5, desktopX: 14, desktopY: 0, desktopSize: 140, mobileX: 28, mobileY: 0, mobileSize: 140 } },
    { label: __('Left pencil', 'ai-zippy'), prefix: 'leftPencil', image: 'leftPencilImage', alt: 'leftPencilAlt', defaults: { zIndex: 5, desktopX: 3, desktopY: 94, desktopSize: 80, mobileX: 15, mobileY: 94, mobileSize: 80 } },
    { label: __('Scissors', 'ai-zippy'), prefix: 'scissors', image: 'scissorsImage', alt: 'scissorsAlt', defaults: { zIndex: 10, desktopX: 50, desktopY: 100, desktopSize: 90, mobileX: 50, mobileY: 100, mobileSize: 90 } },
    { label: __('Bottom pencil', 'ai-zippy'), prefix: 'bottomPencil', image: 'bottomPencilImage', alt: 'bottomPencilAlt', defaults: { zIndex: 5, desktopX: 95, desktopY: 95, desktopSize: 110, mobileX: 76, mobileY: 94, mobileSize: 110 } },
  ];

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Class Features', 'ai-zippy')} initialOpen={true}>
          {sections.map((section, index) => (
            <PanelBody title={section.title || `Feature ${index + 1}`} initialOpen={false} key={index}>
              <TextControl label={__('Title', 'ai-zippy')} value={section.title || ''} onChange={(value) => updateSection(index, 'title', value)} />
              <TextControl label={__('Age', 'ai-zippy')} value={section.age || ''} onChange={(value) => updateSection(index, 'age', value)} />
              <TextareaControl label={__('Description', 'ai-zippy')} value={section.description || ''} onChange={(value) => updateSection(index, 'description', value)} />
              <TextControl label={__('CTA Text', 'ai-zippy')} value={section.ctaText || ''} onChange={(value) => updateSection(index, 'ctaText', value)} />
              <TextControl label={__('CTA URL', 'ai-zippy')} value={section.ctaUrl || ''} onChange={(value) => updateSection(index, 'ctaUrl', value)} />
              <TextControl label={__('Image alt text', 'ai-zippy')} value={section.alt || ''} onChange={(value) => updateSection(index, 'alt', value)} />
              <SelectedImagePreview url={section.image} alt={section.alt} fallback="Selected feature image" /><MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={(media) => { const next = [...sections]; next[index] = { ...next[index], image: media.url, alt: media.alt || next[index]?.alt || '' }; setAttributes({ sections: next }); }} render={({ open }) => <Button variant="secondary" onClick={open}>{section.image ? __('Change Image', 'ai-zippy') : __('Select Image', 'ai-zippy')}</Button>} /></MediaUploadCheck>
            </PanelBody>
          ))}
        </PanelBody>
        <PanelBody title={__('Decorative Icons', 'ai-zippy')} initialOpen={false}>
          {decorations.map((decor) => (
            <PanelBody title={decor.label} initialOpen={false} key={decor.prefix}>
              <SelectedImagePreview url={attributes[decor.image]} alt={attributes[decor.alt]} fallback={`Selected ${decor.label}`} />
              <MediaUploadCheck>
                <MediaUpload
                  allowedTypes={['image']}
                  onSelect={(media) => setAttributes({
                    [decor.image]: media.url,
                    [decor.alt]: media.alt || attributes[decor.alt] || '',
                  })}
                  render={({ open }) => <Button variant="secondary" onClick={open}>{attributes[decor.image] ? __('Change Image', 'ai-zippy') : __('Select Image', 'ai-zippy')}</Button>}
                />
              </MediaUploadCheck>
              {attributes[decor.image] && <Button variant="link" isDestructive onClick={() => setAttributes({ [decor.image]: '' })}>{__('Use default SVG', 'ai-zippy')}</Button>}
              <TextControl label={`${decor.label} ${__('alt text', 'ai-zippy')}`} value={attributes[decor.alt] || ''} onChange={(value) => setAttributes({ [decor.alt]: value })} />
              <DecorPositionControls attributes={attributes} setAttributes={setAttributes} prefix={decor.prefix} defaults={decor.defaults} />
            </PanelBody>
          ))}
        </PanelBody>
        <PanelBody title={__('Gallery', 'ai-zippy')} initialOpen={false}>
          <TextControl label={__('Gallery Title', 'ai-zippy')} value={galleryTitle} onChange={(value) => setAttributes({ galleryTitle: value })} />
          <SelectedImagePreview url={attributes.galleryMascotImage} alt={attributes.galleryMascotAlt} fallback="Selected gallery mascot" />
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              onSelect={(media) => setAttributes({
                galleryMascotImage: media.url,
                galleryMascotAlt: media.alt || attributes.galleryMascotAlt || '',
              })}
              render={({ open }) => <Button variant="secondary" onClick={open}>{attributes.galleryMascotImage ? __('Change Gallery Mascot', 'ai-zippy') : __('Select Gallery Mascot', 'ai-zippy')}</Button>}
            />
          </MediaUploadCheck>
          <TextControl label={__('Gallery mascot alt text', 'ai-zippy')} value={attributes.galleryMascotAlt || ''} onChange={(galleryMascotAlt) => setAttributes({ galleryMascotAlt })} />
          <DecorPositionControls
            attributes={attributes}
            setAttributes={setAttributes}
            prefix="galleryMascot"
            defaults={{ zIndex: 2, desktopX: 12.5967325881, desktopY: 21.5631443299, desktopSize: 245, mobileX: 19.2, mobileY: 18.6666666667, mobileSize: 132 }}
          />
          {galleryImages.map((image, index) => (
            <div key={index}><TextControl label={`${__('Image alt text', 'ai-zippy')} ${index + 1}`} value={image.alt || ''} onChange={(alt) => { const next = [...galleryImages]; next[index] = { ...next[index], alt }; setAttributes({ galleryImages: next }); }} /><MediaUploadCheck><SelectedImagePreview url={image.url} alt={image.alt} fallback="Selected gallery image" /><MediaUpload allowedTypes={['image']} onSelect={(media) => updateGalleryImage(index, media)} render={({ open }) => <Button variant="secondary" onClick={open}>{image.url ? __('Change Image', 'ai-zippy') : `Select Image ${index + 1}`}</Button>} /></MediaUploadCheck></div>
          ))}
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/classes-detail" attributes={attributes} /></div>
    </>
  );
}
