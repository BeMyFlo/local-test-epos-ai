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
  const addType = () => setAttributes({ types: [...types, { label: '', subtitle: '', image: '', alt: '', url: '' }] });

  return (
    <>
      <InspectorControls>
        <PanelBody title="Section Settings" initialOpen={true}>
          <TextControl label="Section title" value={attributes.sectionTitle || ''} onChange={(sectionTitle) => setAttributes({ sectionTitle })} />
        </PanelBody>

        <PanelBody title="Decoration / Cartoon Images" initialOpen={true}>
          <div style={{ marginBottom: '15px' }}>
            <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Decor Left Image (Bottom/Left mascot)</label>
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
        </PanelBody>

        <PanelBody title="Classes" initialOpen={true}>
          {types.map((type, index) => (
            <PanelBody key={index} title={type.label || `Class ${index + 1}`} initialOpen={false}>
              <TextControl label="Label" value={type.label || ''} onChange={(value) => updateType(index, 'label', value)} />
              <TextControl label="Subtitle (e.g. Weekly)" value={type.subtitle || ''} onChange={(value) => updateType(index, 'subtitle', value)} />
              <div style={{ marginBottom: '15px' }}>
                <label className="components-base-control__label" style={{ display: 'block', marginBottom: '8px' }}>Class Image</label>
                <MediaUploadCheck>
                  <MediaUpload
                    allowedTypes={['image']}
                    onSelect={(media) => updateType(index, 'image', media.url || '')}
                    render={({ open }) => (
                      <>
                        {type.image && (
                          <div style={{ marginBottom: '8px' }}>
                            <img src={type.image} alt="" style={{ maxWidth: '100px', maxHeight: '100px', borderRadius: '4px', border: '1px solid #ccc' }} />
                          </div>
                        )}
                        <Button variant="secondary" onClick={open}>
                          {type.image ? 'Change image' : 'Select image'}
                        </Button>
                        {type.image && (
                          <Button variant="link" isDestructive onClick={() => updateType(index, 'image', '')} style={{ marginLeft: '10px' }}>
                            Remove image
                          </Button>
                        )}
                      </>
                    )}
                  />
                </MediaUploadCheck>
              </div>
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
