import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import DecorPositionControls from '../_shared/DecorPositionControls.js';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title="Hero Content" initialOpen={true}>
          <TextControl
            label="Eyebrow"
            value={attributes.eyebrow}
            onChange={(value) => setAttributes({ eyebrow: value })}
          />
          <TextareaControl
            label="Heading"
            value={attributes.heading}
            onChange={(value) => setAttributes({ heading: value })}
          />
          <TextareaControl
            label="Subtitle"
            value={attributes.subtitle}
            onChange={(value) => setAttributes({ subtitle: value })}
          />
          <TextControl
            label="Breadcrumb Home Text"
            value={attributes.breadcrumbHomeText}
            onChange={(value) => setAttributes({ breadcrumbHomeText: value })}
          />
          <TextControl
            label="Breadcrumb Home URL"
            value={attributes.breadcrumbHomeUrl}
            onChange={(value) => setAttributes({ breadcrumbHomeUrl: value })}
          />
          <TextControl
            label="Breadcrumb Current Page"
            value={attributes.breadcrumbCurrent}
            onChange={(value) => setAttributes({ breadcrumbCurrent: value })}
          />
          <TextControl
            label="CTA Text"
            value={attributes.ctaText}
            onChange={(value) => setAttributes({ ctaText: value })}
          />
          <TextControl
            label="CTA URL"
            value={attributes.ctaUrl}
            onChange={(value) => setAttributes({ ctaUrl: value })}
          />
          <SelectControl
            label="Color Variant"
            value={attributes.variant}
            options={[
              { label: 'Pink', value: 'pink' },
              { label: 'Blue', value: 'blue' },
              { label: 'Yellow', value: 'yellow' },
              { label: 'Lavender', value: 'lavender' },
            ]}
            onChange={(value) => setAttributes({ variant: value })}
          />
        </PanelBody>
        <PanelBody title="Banner Image" initialOpen={false}>
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              onSelect={(media) => setAttributes({ backgroundImage: media.url, backgroundAlt: media.alt || '' })}
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {attributes.backgroundImage ? 'Change image' : 'Select image'}
                </Button>
              )}
            />
          </MediaUploadCheck>
          <TextControl
            label="Image URL"
            value={attributes.backgroundImage}
            onChange={(value) => setAttributes({ backgroundImage: value })}
          />
          <TextControl
            label="Image Alt Text"
            value={attributes.backgroundAlt}
            onChange={(value) => setAttributes({ backgroundAlt: value })}
          />
        </PanelBody>
        <PanelBody title="Mascot Image" initialOpen={false}>
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              onSelect={(media) => setAttributes({ mascotImage: media.url, mascotAlt: media.alt || '' })}
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {attributes.mascotImage ? 'Change image' : 'Select image'}
                </Button>
              )}
            />
          </MediaUploadCheck>
          {attributes.mascotImage && (
            <Button variant="link" isDestructive onClick={() => setAttributes({ mascotImage: '', mascotAlt: '' })}>
              Remove image
            </Button>
          )}
          <TextControl
            label="Image URL"
            value={attributes.mascotImage}
            onChange={(value) => setAttributes({ mascotImage: value })}
          />
          <TextControl
            label="Image Alt Text"
            value={attributes.mascotAlt}
            onChange={(value) => setAttributes({ mascotAlt: value })}
          />
          <DecorPositionControls
            attributes={attributes}
            setAttributes={setAttributes}
            prefix="mascot"
            defaults={{ desktopX: 88, desktopY: 68, desktopSize: 220, mobileX: 82, mobileY: 78, mobileSize: 120, zIndex: 5 }}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block={name} attributes={attributes} />
      </div>
    </>
  );
}
