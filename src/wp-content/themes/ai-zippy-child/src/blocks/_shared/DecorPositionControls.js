import { RangeControl } from '@wordpress/components';

const valueOrDefault = (value, fallback) => (
  typeof value === 'number' ? value : fallback
);

export default function DecorPositionControls({
  attributes,
  setAttributes,
  prefix,
  defaults,
}) {
  const update = (suffix) => (value) => {
    setAttributes({ [`${prefix}${suffix}`]: value });
  };

  return (
    <div
      className="achiever-decor-position-controls"
      style={{
        marginTop: '12px',
        padding: '12px',
        border: '1px solid #dcdcde',
        borderRadius: '4px',
        background: '#f6f7f7',
      }}
    >
      <strong style={{ display: 'block', marginBottom: '12px' }}>Position, Size &amp; Layer</strong>
      <RangeControl
        label="Z-index"
        value={valueOrDefault(attributes[`${prefix}ZIndex`], defaults.zIndex)}
        onChange={update('ZIndex')}
        min={-1}
        max={50}
        step={1}
        withInputField
      />
      <strong style={{ display: 'block', margin: '16px 0 10px' }}>Desktop</strong>
      <RangeControl
        label="Horizontal position (%)"
        value={valueOrDefault(attributes[`${prefix}DesktopX`], defaults.desktopX)}
        onChange={update('DesktopX')}
        min={-25}
        max={125}
        step={1}
        withInputField
      />
      <RangeControl
        label="Vertical position (%)"
        value={valueOrDefault(attributes[`${prefix}DesktopY`], defaults.desktopY)}
        onChange={update('DesktopY')}
        min={-50}
        max={150}
        step={1}
        withInputField
      />
      <RangeControl
        label="Image size (px)"
        value={valueOrDefault(attributes[`${prefix}DesktopSize`], defaults.desktopSize)}
        onChange={update('DesktopSize')}
        min={20}
        max={1000}
        step={5}
        withInputField
      />
      <strong style={{ display: 'block', margin: '16px 0 10px' }}>Mobile</strong>
      <RangeControl
        label="Horizontal position (%)"
        value={valueOrDefault(attributes[`${prefix}MobileX`], defaults.mobileX)}
        onChange={update('MobileX')}
        min={-25}
        max={125}
        step={1}
        withInputField
      />
      <RangeControl
        label="Vertical position (%)"
        value={valueOrDefault(attributes[`${prefix}MobileY`], defaults.mobileY)}
        onChange={update('MobileY')}
        min={-50}
        max={150}
        step={1}
        withInputField
      />
      <RangeControl
        label="Image size (px)"
        value={valueOrDefault(attributes[`${prefix}MobileSize`], defaults.mobileSize)}
        onChange={update('MobileSize')}
        min={20}
        max={1000}
        step={5}
        withInputField
      />
    </div>
  );
}
