import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const hours = attributes.hours || [];
  const updateHour = (index, patch) => { const next = [...hours]; next[index] = { ...next[index], ...patch }; setAttributes({ hours: next }); };
  return <>
    <InspectorControls>
      <PanelBody title="Contact Details" initialOpen={true}>
        <TextControl label="Heading" value={attributes.heading} onChange={(heading) => setAttributes({ heading })} />
        <TextControl label="Subheading" value={attributes.subheading} onChange={(subheading) => setAttributes({ subheading })} />
        <TextControl label="Phone Label" value={attributes.phoneLabel} onChange={(phoneLabel) => setAttributes({ phoneLabel })} />
        <TextControl label="Phone" value={attributes.phone} onChange={(phone) => setAttributes({ phone })} />
        <TextControl label="Email Label" value={attributes.emailLabel} onChange={(emailLabel) => setAttributes({ emailLabel })} />
        <TextControl label="Email" value={attributes.email} onChange={(email) => setAttributes({ email })} />
      </PanelBody>
      <PanelBody title="Opening Hours" initialOpen={false}>
        <TextControl label="Hours Title" value={attributes.hoursTitle} onChange={(hoursTitle) => setAttributes({ hoursTitle })} />
        {hours.map((row, index) => <div key={index}><TextControl label="Days" value={row.days || ''} onChange={(days) => updateHour(index, { days })} /><TextControl label="Time" value={row.time || ''} onChange={(time) => updateHour(index, { time })} /><Button isDestructive variant="link" onClick={() => setAttributes({ hours: hours.filter((_, itemIndex) => itemIndex !== index) })}>Remove hours</Button></div>)}
        <Button variant="primary" onClick={() => setAttributes({ hours: [...hours, { days: '', time: '' }] })}>Add hours</Button>
      </PanelBody>
    </InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
