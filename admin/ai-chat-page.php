<?php
/**
 * AI Chat Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add AI chat page to WordPress admin menu
add_action('admin_menu', 'jezweb_support_add_chat_page');
function jezweb_support_add_chat_page() {
    add_menu_page(
        'AI Support',           // Page title
        'AI Support',           // Menu title
        'edit_posts',          // Capability
        'jezweb-ai-chat',      // Menu slug
        'jezweb_support_render_chat_page', // Callback
        'dashicons-format-chat', // Icon
        30                      // Position
    );
}

// Enqueue scripts for the chat page
add_action('admin_enqueue_scripts', 'jezweb_support_enqueue_chat_scripts');
function jezweb_support_enqueue_chat_scripts($hook) {
    // Only load on our AI chat page
    if ($hook !== 'toplevel_page_jezweb-ai-chat') {
        return;
    }

    // Enqueue React app CSS
    wp_enqueue_style(
        'jezweb-chat-app',
        JEZWEB_SUPPORT_PLUGIN_URL . 'assets/chat-app.css',
        array(),
        JEZWEB_SUPPORT_VERSION
    );

    // Enqueue React app JS
    wp_enqueue_script(
        'jezweb-chat-app',
        JEZWEB_SUPPORT_PLUGIN_URL . 'assets/chat-app.js',
        array(),
        JEZWEB_SUPPORT_VERSION,
        true
    );

    // Pass WordPress data to React app
    // Site ID is auto-generated from site URL for Durable Object namespace
    $site_id = get_option('jezweb_site_id', sanitize_title(get_bloginfo('name')));
    if (empty($site_id)) {
        $site_id = sanitize_title(get_bloginfo('name'));
    }

    // Worker URL is hard-coded (same for all sites)
    define('JEZWEB_WORKER_URL', 'https://jezweb-support-agent.webfonts.workers.dev');

    wp_localize_script('jezweb-chat-app', 'wpData', array(
        'siteUrl' => get_site_url(),
        'siteName' => get_bloginfo('name'),
        'workerUrl' => JEZWEB_WORKER_URL,
        'siteId' => $site_id,
        'restUrl' => rest_url('jezweb/v1/'),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}

// Render the chat page
function jezweb_support_render_chat_page() {
    ?>
    <div class="wrap" style="margin: 0; padding: 0;">

        <!-- React app mounts here -->
        <div id="root"></div>

        <noscript>
            <div class="notice notice-error" style="margin: 20px;">
                <p>JavaScript is required to use the AI Support chat. Please enable JavaScript in your browser.</p>
            </div>
        </noscript>
    </div>

    <style>
        /* Make the chat app fill the admin area */
        .wrap {
            margin: 0 !important;
            padding: 0 !important;
        }

        #root {
            min-height: calc(100vh - 32px); /* Account for admin bar */
            background: #f0f0f1;
        }

        /* Override WordPress admin styles for the React app */
        #root .app {
            margin: 0;
            padding: 20px;
            max-width: none;
        }
    </style>
    <?php
}
