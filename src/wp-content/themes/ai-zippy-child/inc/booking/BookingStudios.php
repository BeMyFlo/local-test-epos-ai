<?php
namespace AiZippyChild;

defined('ABSPATH') || exit;

/**
 * Read-only studio/programme registry. No names are hardcoded here — studios
 * come from the studios-directory block.json and programmes from the
 * course-enquiry-form block.json, which are the sources of truth.
 */
class BookingStudios
{
    private const SLUGS = [
        'tampines'  => 'tampines-oth',
        'pasir-ris' => 'pasir-ris-dte',
        'bedok'     => 'bedok-hbb',
    ];

    private static ?array $studios = null;
    private static ?array $programmes = null;

    public static function all(): array
    {
        if (self::$studios === null) {
            $entries = self::loadJson('src/blocks/studios-directory/block.json')['attributes']['studios']['default'] ?? [];
            self::$studios = [];
            foreach ($entries as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $entry['slug'] = self::slugForName((string) ($entry['name'] ?? ''));
                self::$studios[] = $entry;
            }
        }
        return self::$studios;
    }

    public static function get(string $slug): ?array
    {
        foreach (self::all() as $entry) {
            if ($entry['slug'] === $slug) {
                return $entry;
            }
        }
        return null;
    }

    public static function slugForName(string $display_name): string
    {
        if (in_array($display_name, self::SLUGS, true)) {
            return $display_name;
        }
        $key = mb_strtolower($display_name);
        $key = preg_replace('/\([^)]*\)/', '', $key);
        $key = preg_replace('/\bstudio\b/u', '', $key);
        $key = trim($key);
        $key = preg_replace('/\s+/', '-', $key);
        return self::SLUGS[$key] ?? '';
    }

    public static function programmes(): array
    {
        if (self::$programmes === null) {
            $opts = self::loadJson('src/blocks/course-enquiry-form/block.json')['attributes']['programmeOptions']['default'] ?? [];
            self::$programmes = array_values(array_filter($opts, 'is_string'));
        }
        return self::$programmes;
    }

    private static function loadJson(string $relative): array
    {
        $file = get_stylesheet_directory() . '/' . $relative;
        if (!file_exists($file)) {
            return [];
        }
        $decoded = wp_json_file_decode($file, ['associative' => true]);
        return is_array($decoded) ? $decoded : [];
    }
}
