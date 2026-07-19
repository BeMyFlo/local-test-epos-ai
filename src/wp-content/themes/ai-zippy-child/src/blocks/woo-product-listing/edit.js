import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  return <>
    <InspectorControls><PanelBody title="Shop Listing" initialOpen={true}>
      <TextControl label="Heading" value={attributes.heading} onChange={(heading) => setAttributes({ heading })} />
      <TextControl label="Categories Title" value={attributes.categoriesTitle} onChange={(categoriesTitle) => setAttributes({ categoriesTitle })} />
      <TextControl label="All Categories Text" value={attributes.allCategoriesText} onChange={(allCategoriesText) => setAttributes({ allCategoriesText })} />
      <TextControl label="Empty State Message" value={attributes.emptyMessage} onChange={(emptyMessage) => setAttributes({ emptyMessage })} />
      <RangeControl label="Products Per Page" min={1} max={48} value={attributes.productsPerPage} onChange={(productsPerPage) => setAttributes({ productsPerPage })} />
    </PanelBody></InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
