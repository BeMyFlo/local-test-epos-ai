import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  FormTokenField,
  PanelBody,
  RangeControl,
  SelectControl,
  TextControl,
  ToggleControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
  const blockProps = useBlockProps();
  const { source, categories, productIds } = attributes;

  // Pull live WooCommerce taxonomy + products so editors pick real records.
  const { termList, productList } = useSelect((select) => {
    const { getEntityRecords } = select(coreStore);
    return {
      termList: getEntityRecords('taxonomy', 'product_cat', { per_page: -1, _fields: 'id,name,slug' }) || [],
      productList: getEntityRecords('postType', 'product', { per_page: 100, _fields: 'id,title' }) || [],
    };
  }, []);

  const termNames = termList.map((term) => term.name);
  const productTitles = productList.map((product) => product.title?.rendered || `#${product.id}`);

  const selectedTermNames = categories
    .map((slug) => termList.find((term) => term.slug === slug)?.name)
    .filter(Boolean);

  const selectedProductTitles = productIds
    .map((id) => productList.find((product) => product.id === id))
    .filter(Boolean)
    .map((product) => product.title?.rendered || `#${product.id}`);

  return <>
    <InspectorControls>
      <PanelBody title="Shop Listing" initialOpen={true}>
        <SelectControl
          label="Show products from"
          value={source}
          options={[
            { label: 'All products', value: 'all' },
            { label: 'Selected categories', value: 'category' },
            { label: 'Selected products', value: 'selection' },
          ]}
          onChange={(value) => setAttributes({ source: value })}
        />

        {source === 'category' && (
          <FormTokenField
            label="Categories"
            value={selectedTermNames}
            suggestions={termNames}
            onChange={(names) => setAttributes({
              categories: names
                .map((label) => termList.find((term) => term.name === label)?.slug)
                .filter(Boolean),
            })}
          />
        )}

        {source === 'selection' && (
          <FormTokenField
            label="Products"
            value={selectedProductTitles}
            suggestions={productTitles}
            onChange={(titles) => setAttributes({
              productIds: titles
                .map((label) => productList.find((product) => (product.title?.rendered || `#${product.id}`) === label)?.id)
                .filter(Boolean),
            })}
          />
        )}

        <TextControl label="Heading" value={attributes.heading} onChange={(heading) => setAttributes({ heading })} />

        {source !== 'selection' && <>
          <ToggleControl
            label="Show category filter bar"
            checked={attributes.showFilters}
            onChange={(showFilters) => setAttributes({ showFilters })}
          />
          <TextControl label="Categories Title" value={attributes.categoriesTitle} onChange={(categoriesTitle) => setAttributes({ categoriesTitle })} />
          <TextControl label="All Categories Text" value={attributes.allCategoriesText} onChange={(allCategoriesText) => setAttributes({ allCategoriesText })} />
          <RangeControl label="Products Per Page" min={1} max={48} value={attributes.productsPerPage} onChange={(productsPerPage) => setAttributes({ productsPerPage })} />
          <SelectControl
            label="Order by"
            value={attributes.orderby}
            options={[
              { label: 'Date', value: 'date' },
              { label: 'Title', value: 'title' },
              { label: 'Menu order', value: 'menu_order' },
              { label: 'Price', value: 'price' },
              { label: 'Popularity', value: 'popularity' },
              { label: 'Rating', value: 'rating' },
            ]}
            onChange={(orderby) => setAttributes({ orderby })}
          />
          <SelectControl
            label="Order"
            value={attributes.order}
            options={[
              { label: 'Descending', value: 'DESC' },
              { label: 'Ascending', value: 'ASC' },
            ]}
            onChange={(order) => setAttributes({ order })}
          />
        </>}

        <TextControl label="Empty State Message" value={attributes.emptyMessage} onChange={(emptyMessage) => setAttributes({ emptyMessage })} />
      </PanelBody>
    </InspectorControls>
    <div {...blockProps}><ServerSideRender block={name} attributes={attributes} /></div>
  </>;
}
