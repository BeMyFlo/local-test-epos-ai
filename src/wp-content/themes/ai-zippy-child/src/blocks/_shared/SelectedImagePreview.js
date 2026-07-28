export default function SelectedImagePreview({ url, alt = '', fallback = 'Selected image' }) {
  if (!url) {
    return null;
  }

  return (
    <img
      src={url}
      alt={alt || fallback}
      style={{
        display: 'block',
        width: '100%',
        maxWidth: '240px',
        height: '120px',
        objectFit: 'contain',
        margin: '0 0 12px',
        padding: '8px',
        border: '1px solid #dcdcde',
        borderRadius: '4px',
        background: '#f6f7f7',
      }}
    />
  );
}
