<?php

declare(strict_types=1);

/**
 * Minimal parse_blocks()/serialize_blocks() stand-ins for CLI use.
 *
 * sync-via-rest.php builds page content outside WordPress, but the manifest's
 * final pass walks blocks to inject the doc copy. Only self-closing blocks with
 * a JSON attribute payload are involved, which is all this handles.
 */

if (!function_exists('parse_blocks')) {
    function parse_blocks(string $content): array
    {
        $blocks = [];
        $pattern = '/<!--\s+wp:([a-z0-9-]+\/[a-z0-9-]+)(?:\s+(\{.*?\}))?\s+\/-->/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $blocks[] = [
                'blockName' => $match[1],
                'attrs' => isset($match[2]) && $match[2] !== ''
                    ? (json_decode($match[2], true) ?: [])
                    : [],
                'innerBlocks' => [],
                'innerHTML' => '',
                'innerContent' => [],
            ];
        }

        return $blocks;
    }
}

if (!function_exists('serialize_blocks')) {
    function serialize_blocks(array $blocks): string
    {
        $out = [];

        foreach ($blocks as $block) {
            if (empty($block['blockName'])) {
                continue;
            }

            $attrs = $block['attrs'] ?? [];
            $json = $attrs
                ? ' ' . json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : '';

            $out[] = '<!-- wp:' . $block['blockName'] . $json . ' /-->';
        }

        return implode("\n", $out);
    }
}
