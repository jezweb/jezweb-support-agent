<?php
/**
 * Admin Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add settings page to WordPress admin menu
add_action('admin_menu', 'jezweb_support_add_settings_page');
function jezweb_support_add_settings_page() {
    add_options_page(
        'Jezweb Support Settings',
        'Jezweb Support',
        'manage_options',
        'jezweb-support',
        'jezweb_support_render_settings_page'
    );
}

// Register settings
add_action('admin_init', 'jezweb_support_register_settings');
function jezweb_support_register_settings() {
    register_setting('jezweb_support_options', 'jezweb_site_id');
    register_setting('jezweb_support_options', 'jezweb_agent_url');
}

// Render settings page
function jezweb_support_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Jezweb Support Agent Settings</h1>

        <?php if (isset($_GET['settings-updated'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>Settings saved successfully!</p>
            </div>
        <?php endif; ?>

        <div style="background: white; padding: 20px; margin-top: 20px; border: 1px solid #ccc; border-radius: 5px;">
            <h2>Configuration</h2>
            <form method="post" action="options.php">
                <?php settings_fields('jezweb_support_options'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="jezweb_site_id">Site ID</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="jezweb_site_id"
                                name="jezweb_site_id"
                                value="<?php echo esc_attr(get_option('jezweb_site_id', '')); ?>"
                                class="regular-text"
                            />
                            <p class="description">
                                Unique identifier for this site (e.g., newcastleseo.com.au)
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="jezweb_agent_url">Agent URL</label>
                        </th>
                        <td>
                            <input
                                type="url"
                                id="jezweb_agent_url"
                                name="jezweb_agent_url"
                                value="<?php echo esc_attr(get_option('jezweb_agent_url', 'https://support.jezweb.workers.dev')); ?>"
                                class="regular-text"
                            />
                            <p class="description">
                                URL of the Cloudflare Agent endpoint
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>

        <div style="background: white; padding: 20px; margin-top: 20px; border: 1px solid #ccc; border-radius: 5px;">
            <h2>API Endpoints</h2>
            <p>The following REST API endpoints are available for the AI agent:</p>

            <table class="widefat" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>/wp-json/jezweb/v1/site-knowledge</code></td>
                        <td>Complete site knowledge export (pages, plugins, theme, etc.)</td>
                        <td><a href="<?php echo rest_url('jezweb/v1/site-knowledge'); ?>" target="_blank" class="button button-small">View JSON</a></td>
                    </tr>
                    <tr>
                        <td><code>/wp-json/jezweb/v1/pages-list</code></td>
                        <td>List of all published pages</td>
                        <td><a href="<?php echo rest_url('jezweb/v1/pages-list'); ?>" target="_blank" class="button button-small">View JSON</a></td>
                    </tr>
                    <tr>
                        <td><code>/wp-json/jezweb/v1/elementor-data/{id}</code></td>
                        <td>Elementor data for specific page</td>
                        <td>
                            <?php
                            $homepage = get_posts(array(
                                'post_type' => 'page',
                                'meta_key' => '_elementor_data',
                                'posts_per_page' => 1
                            ));
                            if (!empty($homepage)):
                                $page_id = $homepage[0]->ID;
                            ?>
                                <a href="<?php echo rest_url('jezweb/v1/elementor-data/' . $page_id); ?>" target="_blank" class="button button-small">
                                    Example (Page <?php echo $page_id; ?>)
                                </a>
                            <?php else: ?>
                                <span style="color: #999;">No Elementor pages found</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><code>/wp-json/jezweb/v1/plugins</code></td>
                        <td>List of installed plugins</td>
                        <td><a href="<?php echo rest_url('jezweb/v1/plugins'); ?>" target="_blank" class="button button-small">View JSON</a></td>
                    </tr>
                    <tr>
                        <td><code>/wp-json/jezweb/v1/theme-info</code></td>
                        <td>Active theme information</td>
                        <td><a href="<?php echo rest_url('jezweb/v1/theme-info'); ?>" target="_blank" class="button button-small">View JSON</a></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="background: white; padding: 20px; margin-top: 20px; border: 1px solid #ccc; border-radius: 5px;">
            <h2>Site Statistics</h2>
            <?php
            $pages_with_elementor = get_posts(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'meta_key' => '_elementor_data',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ));

            $all_pages = wp_count_posts('page');
            $all_posts = wp_count_posts('post');

            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $all_plugins = get_plugins();
            $active_plugins = get_option('active_plugins', array());
            ?>

            <table class="form-table">
                <tr>
                    <th>Total Pages:</th>
                    <td><?php echo $all_pages->publish; ?></td>
                </tr>
                <tr>
                    <th>Pages with Elementor:</th>
                    <td><?php echo count($pages_with_elementor); ?></td>
                </tr>
                <tr>
                    <th>Total Posts:</th>
                    <td><?php echo $all_posts->publish; ?></td>
                </tr>
                <tr>
                    <th>Active Plugins:</th>
                    <td><?php echo count($active_plugins); ?> of <?php echo count($all_plugins); ?> installed</td>
                </tr>
                <tr>
                    <th>Theme:</th>
                    <td><?php echo wp_get_theme()->get('Name'); ?> v<?php echo wp_get_theme()->get('Version'); ?></td>
                </tr>
                <tr>
                    <th>WordPress Version:</th>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
            </table>
        </div>

        <div style="background: #e7f7ff; padding: 20px; margin-top: 20px; border: 1px solid #b3d9f2; border-radius: 5px;">
            <h2>💡 How It Works</h2>
            <ol style="line-height: 1.8;">
                <li><strong>This plugin exposes your site data</strong> via REST API endpoints that the AI agent can read</li>
                <li><strong>The AI agent</strong> (running on Cloudflare) fetches this data to understand your site structure</li>
                <li><strong>When users ask questions</strong>, the AI uses this knowledge to provide specific guidance</li>
                <li><strong>Chat widget</strong> appears in the bottom-right for logged-in editors</li>
            </ol>

            <h3>Example Questions the AI Can Answer:</h3>
            <ul style="line-height: 1.8;">
                <li>"How do I change the hero heading on my homepage?"</li>
                <li>"Where is the contact form?"</li>
                <li>"What plugins are currently active?"</li>
                <li>"How do I edit the footer menu?"</li>
            </ul>
        </div>
    </div>

    <style>
        .jezweb-support-settings h2 {
            margin-top: 0;
        }
        .jezweb-support-settings code {
            background: #f0f0f1;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 13px;
        }
    </style>
    <?php
}
