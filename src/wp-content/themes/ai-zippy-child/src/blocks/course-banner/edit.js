import SelectedImagePreview from '../_shared/SelectedImagePreview.js';
import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, ColorPicker, PanelBody, SelectControl, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
export default function Edit({ attributes, setAttributes }) {
  const { heading = '' } = attributes;
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Tagline', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Tagline', 'ai-zippy')} value={attributes.tagline || ''} onChange={(tagline) => setAttributes({ tagline })} />
          <TextareaControl label={__('Tagline description', 'ai-zippy')} value={attributes.taglineDescription || ''} onChange={(taglineDescription) => setAttributes({ taglineDescription })} />
        </PanelBody>
        <PanelBody title={__('Heading', 'ai-zippy')} initialOpen={true}>
          <TextControl label={__('Heading', 'ai-zippy')} value={heading} onChange={(value) => setAttributes({ heading: value })} />
        </PanelBody>
        <PanelBody title={__('Banner Background', 'ai-zippy')} initialOpen={false}>
          <SelectedImagePreview url={attributes.backgroundImage} alt={attributes.backgroundAlt} fallback="Selected banner background" />
          <ColorPicker color={attributes.backgroundColor} onChange={(backgroundColor) => setAttributes({ backgroundColor })} />
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              onSelect={(media) => setAttributes({ backgroundImage: media.url, backgroundAlt: media.alt || attributes.backgroundAlt || '' })}
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {attributes.backgroundImage ? __('Change Background Image', 'ai-zippy') : __('Select Background Image', 'ai-zippy')}
                </Button>
              )}
            />
          </MediaUploadCheck>
          {attributes.backgroundImage && (
            <Button variant="link" isDestructive onClick={() => setAttributes({ backgroundImage: '' })} style={{ marginTop: '8px' }}>
              {__('Reset to default image', 'ai-zippy')}
            </Button>
          )}
          <SelectControl label={__('Background position', 'ai-zippy')} value={attributes.backgroundPosition || 'center top'} options={[{label:'Center top',value:'center top'},{label:'Center center',value:'center center'},{label:'Center bottom',value:'center bottom'}]} onChange={(backgroundPosition)=>setAttributes({backgroundPosition})} />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}><ServerSideRender block="ai-zippy/course-banner" attributes={attributes} /></div>
    </>
  );
}
