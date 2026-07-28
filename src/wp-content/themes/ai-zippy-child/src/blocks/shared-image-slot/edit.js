import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps({ className: 'achiever-shared-image-slot-editor' });
  const selectImage = (media) => setAttributes({
    imageId: media.id || 0,
    imageUrl: media.url || '',
    alt: media.alt || attributes.alt || '',
  });
  const removeImage = () => setAttributes({ imageId: 0, imageUrl: '', alt: '' });

  return (
    <>
      <InspectorControls>
        <PanelBody title="Image" initialOpen={true}>
          <TextControl label="Image URL" value={attributes.imageUrl || ''} onChange={(imageUrl) => setAttributes({ imageUrl, imageId: 0 })} />
          <TextControl label="Alt text" value={attributes.alt || ''} onChange={(alt) => setAttributes({ alt })} />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <SelectedImagePreview url={attributes.imageUrl} alt={attributes.alt} fallback="Selected decorative image" />
        <MediaUploadCheck>
          <MediaUpload allowedTypes={['image']} value={attributes.imageId || 0} onSelect={selectImage} render={({ open }) => <Button variant="secondary" onClick={open}>{attributes.imageUrl ? 'Replace image' : 'Select image'}</Button>} />
        </MediaUploadCheck>
        {attributes.imageUrl && <Button variant="tertiary" isDestructive onClick={removeImage}>Remove image</Button>}
      </div>
    </>
  );
}
