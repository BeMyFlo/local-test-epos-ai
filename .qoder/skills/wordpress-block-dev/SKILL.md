---
name: wordpress-block-dev
description: Create, update, and debug WordPress Full Site Editing (FSE) custom Gutenberg blocks with server-side rendering. Use when creating new blocks, modifying block attributes/templates, updating block styling, or troubleshooting block registration and rendering issues.
---

# WordPress FSE Block Development

## Block Architecture

Each block lives in `src/wp-content/themes/ai-zippy-child/src/blocks/<block-name>/` with:

| File | Purpose |
|------|---------|
| `block.json` | Registration metadata, attributes, asset references |
| `render.php` | Server-side HTML output (dynamic block) |
| `edit.js` | Block Editor UI (Gutenberg sidebar) |
| `save.js` | Returns `null` (server-rendered blocks) |
| `editor.scss` | Editor-only styles |
| `style.scss` | Frontend styles (compiled by build) |
| `view.js` | Frontend JS (sliders, interactions) |

## block.json Template

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "ai-zippy/<block-name>",
  "version": "1.0.0",
  "title": "Block Title",
  "category": "achiever-home",
  "description": "What this block does.",
  "supports": { "html": false },
  "attributes": {
    "heading": { "type": "string", "default": "Default Text" },
    "items": { "type": "array", "default": [] },
    "decorImage": { "type": "string", "default": "" }
  },
  "textdomain": "ai-zippy",
  "editorScript": "file:./index.js",
  "render": "file:./render.php"
}
```

### Attribute Conventions
- Use `camelCase` for attribute names
- Images: `type: "string"` storing URL
- Arrays of items: `type: "array"` with full default objects
- CTA: always pair `ctaText` + `ctaUrl`
- Decorative images: `decorLeftImage`, `decorRightImage`, `decorBottomImage`

## render.php Template

```php
<?php
defined('ABSPATH') || exit;

$heading = $attributes['heading'] ?? 'Default';
$items   = $attributes['items'] ?? [];

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-<section-name>',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <h2 class="achiever-<section>__title"><?php echo esc_html($heading); ?></h2>
    <div class="achiever-<section>__grid">
        <?php foreach ($items as $item) :
            $name  = $item['name'] ?? '';
            $image = $item['image'] ?? '';
        ?>
            <div class="achiever-<section>__card">
                <?php if ($image) : ?>
                    <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" />
                <?php endif; ?>
                <span><?php echo esc_html($name); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

### PHP Rules
- Always `defined('ABSPATH') || exit;` first line
- Use `esc_html()`, `esc_url()`, `esc_attr()` for all output
- Use null coalescing `??` for defaults
- Use `get_block_wrapper_attributes()` for the root element
- Add `loading="lazy"` to all images

## SCSS Conventions

File: `src/scss/achiever.scss`

```scss
@use "@parent-scss/variables" as *;

.achiever-<section> {
  padding: 60px 20px;

  @include from(md) {
    padding: 80px 40px;
  }

  &__title {
    text-align: center;
    font-family: 'Playfair Display', serif;
    color: $achiever-navy;
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;

    @include from(md) {
      grid-template-columns: repeat(3, 1fr);
    }

    @include from(lg) {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  &__card {
    border-radius: $achiever-radius;
    overflow: hidden;
    box-shadow: $achiever-shadow;
    transition: all $achiever-transition;

    &:hover {
      transform: translateY(-3px);
      box-shadow: $achiever-shadow-lg;
    }
  }
}
```

### CSS Variables Available
- `$achiever-navy` — dark navy text
- `$achiever-pink` — brand pink
- `$achiever-white`, `$achiever-grey`, `$achiever-blush`
- `$achiever-muted` — muted text
- `$achiever-radius` — border-radius
- `$achiever-shadow`, `$achiever-shadow-lg`
- `$achiever-transition` — transition timing
- Mixin: `@include from(md)`, `@include from(lg)` — responsive breakpoints

## Build & Registration

### Build Command
```bash
npm run build:child
```

### How Blocks Register
`functions.php` auto-registers all blocks in `assets/blocks/`:

```php
add_action('init', function (): void {
    $blocks_dir = get_stylesheet_directory() . '/assets/blocks';
    foreach (glob($blocks_dir . '/*/block.json') as $block_json) {
        register_block_type(dirname($block_json));
    }
});
```

### Important: Dual File Locations
- **Source**: `src/blocks/<name>/` — edit here
- **Built**: `assets/blocks/<name>/` — WordPress reads from here

After editing source, you MUST either:
1. Run `npm run build:child` to compile
2. OR manually copy `render.php` + `block.json` to `assets/blocks/<name>/`

## Inline CSS/JS Fallback (No-Build)

When build is unavailable, inject styles/scripts directly:

```php
// Inline CSS
add_action('wp_enqueue_scripts', function (): void {
    $css = '.achiever-section { background: #fff !important; }';
    wp_add_inline_style('wp-block-library', $css);
}, 99);

// Inline JS
add_action('wp_footer', function (): void {
    ?>
    <script>
    (function(){
        // slider logic, etc.
    })();
    </script>
    <?php
}, 99);
```

Use `!important` when overriding compiled CSS from older builds.

## Slider Pattern

For carousel/slider sections:

### HTML Structure
```html
<div class="achiever-<section>__slider" data-<section>-slider>
    <button class="achiever-<section>__arrow achiever-<section>__arrow--prev">‹</button>
    <div class="achiever-<section>__track">
        <div class="achiever-<section>__slide">...</div>
    </div>
    <button class="achiever-<section>__arrow achiever-<section>__arrow--next">›</button>
</div>
```

### CSS (scroll-snap)
```scss
&__track {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scrollbar-width: none;
}
&__slide {
  flex: 0 0 calc(33.333% - 8px);
  scroll-snap-align: start;
  border-radius: 12px;
  overflow: hidden;
}
```

### JS (arrow navigation)
```js
const track = slider.querySelector('.__track');
prev.addEventListener('click', () => {
    track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
});
```

## Template Integration

Blocks are placed in `templates/page-<name>.html`:

```html
<!-- wp:template-part {"slug":"header","area":"header"} /-->
<!-- wp:group {"tagName":"main","className":"achiever-page"} -->
<main class="wp-block-group achiever-page">
<!-- wp:ai-zippy/home-hero /-->
<!-- wp:ai-zippy/home-class-types /-->
</main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","area":"footer"} /-->
```

### DB Template Override
WordPress Site Editor saves templates to database. DB version **overrides** file on disk.
To force file version: delete the `wp_template` post via WP-CLI:
```bash
docker exec <container> wp post delete $(wp post list --post_type=wp_template --format=ids --allow-root) --force --allow-root
```

## Decorative Images Pattern

For illustration overlays (mascots, brushes, etc.):

```php
<?php if ($decor_image) : ?>
    <img src="<?php echo esc_url($decor_image); ?>" alt=""
         class="achiever-<section>__decor achiever-<section>__decor--left" loading="lazy" />
<?php endif; ?>
```

```scss
&__decor {
  position: absolute;
  z-index: 2;
  pointer-events: none;

  &--left { top: 0; left: 0; width: 180px; }
  &--right { top: 0; right: 0; width: 200px; }
}
```

## Checklist: New Block

- [ ] Create `block.json` with correct category + attributes
- [ ] Create `render.php` with proper escaping
- [ ] Create `edit.js` with InspectorControls
- [ ] Create `save.js` returning `null`
- [ ] Add SCSS section to `achiever.scss`
- [ ] Add block to template HTML file
- [ ] Run build OR copy to `assets/blocks/`
- [ ] Verify block appears in editor inserter
- [ ] Test frontend rendering
