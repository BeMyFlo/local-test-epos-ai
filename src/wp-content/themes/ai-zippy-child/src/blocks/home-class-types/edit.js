import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const types = attributes.types || [];

  const updateType = (index, key, value) => {
    const nextTypes = [...types];
    nextTypes[index] = { ...nextTypes[index], [key]: value };
    setAttributes({ types: nextTypes });
  };

  const removeType = (index) => setAttributes({ types: types.filter((_, itemIndex) => itemIndex !== index) });
  const addType = () => setAttributes({ types: [...types, { label: '', image: '', alt: '', url: '' }] });

  return (
    <>
      <InspectorControls>
        <PanelBody title="Section" initialOpen={true}>
          <TextControl label="Section title" value={attributes.sectionTitle || ''} onChange={(sectionTitle) => setAttributes({ sectionTitle })} />
        </PanelBody>
        <PanelBody title="Classes" initialOpen={true}>
          {types.map((type, index) => (
            <PanelBody key={index} title={type.label || `Class ${index + 1}`} initialOpen={false}>
              <TextControl label="Label" value={type.label || ''} onChange={(value) => updateType(index, 'label', value)} />
              <MediaUploadCheck>
                <MediaUpload
                  allowedTypes={['image']}
                  onSelect={(media) => updateType(index, 'image', media.url || '')}
                  render={({ open }) => (
                    <>
                      {type.image && (
                        <img src={type.image} alt="" style={{ width: '120px', height: '120px', marginBottom: '8px', borderRadius: '50%', objectFit: 'cover' }} />
                      )}
                      <Button variant="secondary" onClick={open}>
                        {type.image ? 'Change image' : 'Select image'}
                      </Button>
                      {type.image && (
                        <Button isDestructive variant="tertiary" onClick={() => updateType(index, 'image', '')}>
                          Remove image
                        </Button>
                      )}
                    </>
                  )}
                />
              </MediaUploadCheck>
              <TextControl label="Image URL" value={type.image || ''} onChange={(value) => updateType(index, 'image', value)} />
              <TextControl label="Image alt text" value={type.alt || ''} onChange={(value) => updateType(index, 'alt', value)} />
              <TextControl label="Link URL" value={type.url || ''} onChange={(value) => updateType(index, 'url', value)} />
              <Button isDestructive variant="secondary" onClick={() => removeType(index)}>Remove class</Button>
            </PanelBody>
          ))}
          <Button variant="primary" onClick={addType}>Add class</Button>
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block={name} attributes={attributes} />
      </div>
    </>
  );
}
