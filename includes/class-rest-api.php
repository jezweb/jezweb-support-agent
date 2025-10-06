<?php
/**
 * REST API Endpoints
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jezweb_Support_REST_API {

    private $namespace = 'jezweb/v1';

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Complete site knowledge export
        register_rest_route($this->namespace, '/site-knowledge', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_site_knowledge'),
            'permission_callback' => array($this, 'check_permission')
        ));

        // Elementor data for specific page
        register_rest_route($this->namespace, '/elementor-data/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_elementor_data'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ));

        // List all pages
        register_rest_route($this->namespace, '/pages-list', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_pages_list'),
            'permission_callback' => array($this, 'check_permission')
        ));

        // Get plugins list
        register_rest_route($this->namespace, '/plugins', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_plugins_list'),
            'permission_callback' => array($this, 'check_permission')
        ));

        // Get theme info
        register_rest_route($this->namespace, '/theme-info', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_theme_info'),
            'permission_callback' => array($this, 'check_permission')
        ));
    }

    /**
     * Permission callback - allow public access for now (can be restricted later)
     */
    public function check_permission() {
        // For MVP, we'll allow access
        // In production, you'd want to check for API key or token
        return true;
    }

    /**
     * Get complete site knowledge
     */
    public function get_site_knowledge($request) {
        $parser = new Jezweb_Support_Elementor_Parser();

        $data = array(
            'site_info' => array(
                'url' => get_site_url(),
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'wordpress_version' => get_bloginfo('version'),
                'plugin_version' => JEZWEB_SUPPORT_VERSION,
                'timestamp' => current_time('mysql'),
            ),
            'theme' => $this->get_theme_info($request),
            'pages' => $this->get_pages_with_elementor(),
            'posts' => $this->get_posts_with_elementor(),
            'plugins' => $this->get_plugins_list($request),
            'menus' => $this->get_menus_data(),
            'elementor_globals' => $this->get_elementor_globals(),
        );

        return rest_ensure_response($data);
    }

    /**
     * Get Elementor data for specific page
     */
    public function get_elementor_data($request) {
        $page_id = $request['id'];

        if (!get_post($page_id)) {
            return new WP_Error('not_found', 'Page not found', array('status' => 404));
        }

        $parser = new Jezweb_Support_Elementor_Parser();
        $elementor_raw = get_post_meta($page_id, '_elementor_data', true);
        $elementor_settings = get_post_meta($page_id, '_elementor_page_settings', true);

        if (empty($elementor_raw)) {
            return new WP_Error('no_elementor', 'This page does not use Elementor', array('status' => 400));
        }

        $elementor_data = json_decode($elementor_raw, true);

        $data = array(
            'id' => $page_id,
            'title' => get_the_title($page_id),
            'url' => get_permalink($page_id),
            'type' => get_post_type($page_id),
            'elementor_raw' => $elementor_data,
            'elementor_settings' => $elementor_settings,
            'parsed' => $parser->parse_elementor_structure($elementor_data),
            'navigation_guide' => $parser->build_navigation_tree($elementor_data),
            'editable_elements' => $parser->extract_editable_content($elementor_data),
        );

        return rest_ensure_response($data);
    }

    /**
     * Get list of all pages
     */
    public function get_pages_list($request) {
        $pages = get_posts(array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        $pages_data = array();
        foreach ($pages as $page) {
            $has_elementor = metadata_exists('post', $page->ID, '_elementor_data');

            $pages_data[] = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'slug' => $page->post_name,
                'url' => get_permalink($page->ID),
                'modified' => $page->post_modified,
                'has_elementor' => $has_elementor,
            );
        }

        return rest_ensure_response($pages_data);
    }

    /**
     * Get pages with Elementor data
     */
    private function get_pages_with_elementor() {
        $pages = get_posts(array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_key' => '_elementor_data',
        ));

        $parser = new Jezweb_Support_Elementor_Parser();
        $pages_data = array();

        foreach ($pages as $page) {
            $elementor_raw = get_post_meta($page->ID, '_elementor_data', true);
            $elementor_data = json_decode($elementor_raw, true);

            $pages_data[] = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'url' => get_permalink($page->ID),
                'structure' => $parser->parse_elementor_structure($elementor_data),
                'widget_count' => $parser->count_widgets($elementor_data),
            );
        }

        return $pages_data;
    }

    /**
     * Get posts with Elementor data
     */
    private function get_posts_with_elementor() {
        $posts = get_posts(array(
            'post_type' => 'post',
            'posts_per_page' => 50,
            'post_status' => 'publish',
            'meta_key' => '_elementor_data',
        ));

        $posts_data = array();
        foreach ($posts as $post) {
            $posts_data[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
            );
        }

        return $posts_data;
    }

    /**
     * Get plugins list
     */
    public function get_plugins_list($request) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());

        $plugins_data = array();
        foreach ($all_plugins as $plugin_path => $plugin_data) {
            $plugins_data[] = array(
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'active' => in_array($plugin_path, $active_plugins),
                'description' => $plugin_data['Description'],
            );
        }

        return rest_ensure_response($plugins_data);
    }

    /**
     * Get theme info
     */
    public function get_theme_info($request) {
        $theme = wp_get_theme();

        return array(
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'author' => $theme->get('Author'),
            'description' => $theme->get('Description'),
        );
    }

    /**
     * Get menus data
     */
    private function get_menus_data() {
        $menus = wp_get_nav_menus();
        $menus_data = array();

        foreach ($menus as $menu) {
            $items = wp_get_nav_menu_items($menu->term_id);
            $menu_items = array();

            if ($items) {
                foreach ($items as $item) {
                    $menu_items[] = array(
                        'title' => $item->title,
                        'url' => $item->url,
                        'parent' => $item->menu_item_parent,
                    );
                }
            }

            $menus_data[] = array(
                'name' => $menu->name,
                'items' => $menu_items,
            );
        }

        return $menus_data;
    }

    /**
     * Get Elementor global settings
     */
    private function get_elementor_globals() {
        return array(
            'colors' => get_option('elementor_scheme_color', array()),
            'fonts' => get_option('elementor_scheme_typography', array()),
            'custom_colors' => get_option('elementor_custom_colors', array()),
        );
    }
}
