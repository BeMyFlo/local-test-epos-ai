import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();

  const updateType = (index, key, value) => {
    const updated = [...attributes.types];
    updated[index] = { ...updated[index], [key]: value };
    setAttributes({ types: updated });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title="Section" initialOpen={true}>
          <TextControl
            label="Section Title"
            value={attributes.sectionTitle}
            onChange={(v) => setAttributes({ sectionTitle: v })}
          />
        </PanelBody>
        {attributes.types.map((type, i) => (
          <PanelBody key={i} title={`Type ${i + 1}: ${type.name}`} initialOpen={false}>
            <TextControl
              label="Name"
              value={type.name}
              onChange={(v) => updateType(i, 'name', v)}
            />
            <TextControl
              label="Format"
              value={type.format}
              onChange={(v) => updateType(i, 'format', v)}
            />
            <TextControl
              label="Icon URL"
              value={type.icon}
              onChange={(v) => updateType(i, 'icon', v)}
            />
          </PanelBody>
        ))}
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block={name} attributes={attributes} />
      </div>
    </>
  );
}
