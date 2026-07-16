<?php

namespace AiZippy\Audit\Listeners;

use AiZippy\Audit\AuditLogger;

defined('ABSPATH') || exit;

/**
 * Tracks post + page lifecycle: create, update, delete.
 *
 * Field diff strategy: capture the previous post in a static cache on
 * `pre_post_update` (fires *before* WP saves), then on `save_post` compare
 * against the new post object. Only the names of changed fields are stored.
 *
 * Skipped:
 *   - autosaves (`wp_is_post_autosave`)
 *   - revisions (`wp_is_post_revision`)
 *   - non-post/page post types (handled by WooCommerceListener for products)
 */
class PostListener
{
    /** @var array<int,\WP_Post>  Snapshots keyed by post ID. */
    private static array $previous = [];

    private const TRACKED_FIELDS = [
        'post_title',
        'post_content',
        'post_excerpt',
        'post_status',
        'post_name',
        'post_parent',
        'menu_order',
    ];

    private const TRACKED_TYPES = ['post', 'page'];

    public static function register(): void
    {
        add_action('pre_post_update',   [self::class, 'captureBefore'], 10, 1);
        add_action('save_post',         [self::class, 'onSave'],        10, 3);
        add_action('before_delete_post', [self::class, 'onDelete'],     10, 2);
    }

    /**
     * Snapshot the existing post so save_post can diff against it.
     */
    public static function captureBefore(int $post_id): void
    {
        try {
            $post = get_post($post_id);
            if ($post && in_array($post->post_type, self::TRACKED_TYPES, true)) {
                self::$previous[$post_id] = clone $post;
            }
        } catch (\Throwable $e) {
            // Snapshot failure is non-fatal — diff will just be empty.
        }
    }

    public static function onSave(int $post_id, \WP_Post $post, bool $update): void
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        if (!in_array($post->post_type, self::TRACKED_TYPES, true)) {
            return;
        }

        // Drafts auto-saved by Gutenberg also fire save_post — skip the
        // 'auto-draft' status which is the placeholder before the first real save.
        if ($post->post_status === 'auto-draft') {
            return;
        }

        $event = $update ? "{$post->post_type}.update" : "{$post->post_type}.create";

        $meta = [];
        if ($update && isset(self::$previous[$post_id])) {
            $changed = self::diffFields(self::$previous[$post_id], $post);
            if (!empty($changed)) {
                $meta['changed_fields'] = $changed;
            } else {
                // No tracked fields changed — this was a metadata-only save.
                // Still log it but mark explicitly so admins can filter it out.
                $meta['changed_fields'] = [];
            }
        }

        AuditLogger::log(
            $event,
            $post->post_type,
            $post_id,
            wp_specialchars_decode($post->post_title, ENT_QUOTES),
            $meta
        );

        unset(self::$previous[$post_id]);
    }

    public static function onDelete(int $post_id, \WP_Post $post): void
    {
        if (!in_array($post->post_type, self::TRACKED_TYPES, true)) {
            return;
        }

        AuditLogger::log(
            "{$post->post_type}.delete",
            $post->post_type,
            $post_id,
            wp_specialchars_decode($post->post_title, ENT_QUOTES)
        );
    }

    /**
     * @return string[] List of changed tracked field names.
     */
    private static function diffFields(\WP_Post $before, \WP_Post $after): array
    {
        $changed = [];
        foreach (self::TRACKED_FIELDS as $field) {
            if ((string) $before->$field !== (string) $after->$field) {
                $changed[] = $field;
            }
        }
        return $changed;
    }
}
