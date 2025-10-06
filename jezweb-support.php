<?php
/**
 * Plugin Name: Jezweb Support Agent
 * Plugin URI: https://jezweb.com.au
 * Description: Exposes WordPress and Elementor data for AI-powered support assistance with AI chat
 * Version: 1.4.2
 * Author: Jezweb
 * Author URI: https://jezweb.com.au
 * License: GPL v2 or later
 * Text Domain: jezweb-support
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JEZWEB_SUPPORT_VERSION', '1.4.2');
define('JEZWEB_SUPPORT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JEZWEB_SUPPORT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Jezweb_Support_Agent {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once JEZWEB_SUPPORT_PLUGIN_DIR . 'includes/class-rest-api.php';
        require_once JEZWEB_SUPPORT_PLUGIN_DIR . 'includes/class-elementor-parser.php';
        require_once JEZWEB_SUPPORT_PLUGIN_DIR . 'includes/class-chat-widget.php';

        if (is_admin()) {
            require_once JEZWEB_SUPPORT_PLUGIN_DIR . 'admin/settings-page.php';
            require_once JEZWEB_SUPPORT_PLUGIN_DIR . 'admin/ai-chat-page.php';
        }

        // Load plugin update checker
        require_once JEZWEB_SUPPORT_PLUGIN_DIR . 'lib/plugin-update-checker/plugin-update-checker.php';
        $this->init_update_checker();
    }

    /**
     * Initialize plugin update checker
     */
    private function init_update_checker() {
        $updateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/jezweb/jezweb-support-agent',
            __FILE__,
            'jezweb-support'
        );

        // Optional: Set the branch to track for updates
        $updateChecker->setBranch('main');
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_menu'), 100);
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        $rest_api = new Jezweb_Support_REST_API();
        $rest_api->register_routes();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        wp_enqueue_style(
            'jezweb-support-admin',
            JEZWEB_SUPPORT_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JEZWEB_SUPPORT_VERSION
        );

        wp_enqueue_script(
            'jezweb-support-admin',
            JEZWEB_SUPPORT_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            JEZWEB_SUPPORT_VERSION,
            true
        );

        wp_localize_script('jezweb-support-admin', 'jezwebSupport', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jezweb_support_nonce'),
            'restUrl' => rest_url('jezweb/v1/'),
            'siteId' => get_option('jezweb_site_id', '')
        ));
    }

    /**
     * Add admin bar menu link
     */
    public function add_admin_bar_menu($wp_admin_bar) {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $wp_admin_bar->add_node(array(
            'id'    => 'jezweb-ai-support',
            'title' => '💬 AI Support',
            'href'  => admin_url('admin.php?page=jezweb-ai-chat'),
            'meta'  => array(
                'title' => 'AI Support Assistant',
            ),
        ));
    }
}

/**
 * Initialize the plugin
 */
function jezweb_support_init() {
    return Jezweb_Support_Agent::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'jezweb_support_init');

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'jezweb_support_activate');
function jezweb_support_activate() {
    // Set default options
    if (!get_option('jezweb_site_id')) {
        update_option('jezweb_site_id', '');
    }

    // Flush rewrite rules to register REST routes
    flush_rewrite_rules();
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'jezweb_support_deactivate');
function jezweb_support_deactivate() {
    flush_rewrite_rules();
}
