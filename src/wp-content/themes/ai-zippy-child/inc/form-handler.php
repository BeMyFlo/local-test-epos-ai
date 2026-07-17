<?php
/**
 * Achiever's Art — Enquiry Form Handler
 * 
 * Registers REST API endpoint: /wp-json/achiever-art/v1/enquiry
 * Processes form submissions and sends email notifications.
 */

defined('ABSPATH') || exit;

add_action('rest_api_init', function () {
    register_rest_route('achiever-art/v1', '/enquiry', [
        'methods'             => 'POST',
        'callback'            => 'achiever_art_handle_enquiry',
        'permission_callback' => '__return_true',
    ]);
});

/**
 * Handle enquiry form submission.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function achiever_art_handle_enquiry(WP_REST_Request $request): WP_REST_Response
{
    $params = $request->get_json_params();

    // Required fields
    $name           = sanitize_text_field($params['name'] ?? '');
    $phone          = sanitize_text_field($params['phone'] ?? '');
    $email          = sanitize_email($params['email'] ?? '');
    $children_age   = sanitize_text_field($params['children_age'] ?? '');
    $studio         = sanitize_text_field($params['studio'] ?? '');
    $programme_type = sanitize_text_field($params['programme_type'] ?? '');
    $message        = sanitize_textarea_field($params['message'] ?? '');

    // Validation
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    }
    if (empty($email) || !is_email($email)) {
        $errors[] = 'A valid email address is required.';
    }
    if (empty($children_age)) {
        $errors[] = 'Children age is required.';
    }
    if (empty($studio)) {
        $errors[] = 'Please select a studio.';
    }

    if (!empty($errors)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => implode(' ', $errors),
        ], 400);
    }

    // Rate limiting (simple: max 5 submissions per IP per hour)
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $transient_key = 'achiever_enquiry_' . md5($ip);
    $submissions = (int) get_transient($transient_key);

    if ($submissions >= 5) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Too many submissions. Please try again later.',
        ], 429);
    }

    set_transient($transient_key, $submissions + 1, HOUR_IN_SECONDS);

    // Compose email
    $admin_email = get_option('admin_email');
    $subject     = sprintf('[Achiever\'s Art] New Enquiry from %s', $name);

    $body = sprintf(
        "New enquiry received:\n\n" .
        "Name: %s\n" .
        "Phone: %s\n" .
        "Email: %s\n" .
        "Children Age: %s\n" .
        "Studio: %s\n" .
        "Programme Type: %s\n" .
        "Message:\n%s\n",
        $name,
        $phone,
        $email,
        $children_age,
        $studio,
        $programme_type,
        $message
    );

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf('Reply-To: %s <%s>', $name, $email),
    ];

    $sent = wp_mail($admin_email, $subject, $body, $headers);

    if (!$sent) {
        // Log failure but still return success to user (email may be queued)
        error_log('[Achiever Art] Failed to send enquiry email from: ' . $email);
    }

    // Store as custom post type (optional backup)
    wp_insert_post([
        'post_type'   => 'achiever_enquiry',
        'post_title'  => sprintf('Enquiry from %s - %s', $name, wp_date('Y-m-d H:i')),
        'post_status' => 'private',
        'post_content' => $body,
        'meta_input'  => [
            '_enquiry_name'           => $name,
            '_enquiry_email'          => $email,
            '_enquiry_phone'          => $phone,
            '_enquiry_children_age'   => $children_age,
            '_enquiry_studio'         => $studio,
            '_enquiry_programme_type' => $programme_type,
        ],
    ]);

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Thank you for your enquiry! We will get back to you soon.',
    ], 200);
}

/**
 * Register custom post type for enquiry storage.
 */
add_action('init', function () {
    register_post_type('achiever_enquiry', [
        'labels' => [
            'name'          => 'Enquiries',
            'singular_name' => 'Enquiry',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-email-alt',
        'supports'     => ['title', 'editor', 'custom-fields'],
        'capability_type' => 'post',
    ]);
});
