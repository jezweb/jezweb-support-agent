=== Jezweb Support Agent ===
Contributors: jezweb
Tags: support, ai, elementor, rest-api
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Exposes WordPress and Elementor data for AI-powered support assistance.

== Description ==

Jezweb Support Agent makes your WordPress site's structure and content accessible to an AI support assistant. It provides REST API endpoints that expose:

* Complete site knowledge (pages, posts, plugins, theme info)
* Full Elementor page structures with navigation guides
* Editable content mapped to widget IDs
* Helpful metadata for AI understanding

Perfect for managed WordPress hosting providers who want to offer intelligent, automated support to their clients.

== Features ==

* **REST API Endpoints** - Clean, documented endpoints for site knowledge export
* **Elementor Parser** - Understands Elementor page structure and creates navigation guides
* **Chat Widget** - Built-in support chat for logged-in users
* **No External Dependencies** - Works with standard WordPress and Elementor
* **Privacy Friendly** - Data only accessible via API (can be secured with auth)

== Installation ==

1. Upload the `jezweb-support` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Jezweb Support to configure
4. Set your Site ID and Agent URL
5. Test the API endpoints using the links provided

== REST API Endpoints ==

* `/wp-json/jezweb/v1/site-knowledge` - Complete site export
* `/wp-json/jezweb/v1/elementor-data/{page_id}` - Elementor data for specific page
* `/wp-json/jezweb/v1/pages-list` - All published pages
* `/wp-json/jezweb/v1/plugins` - Installed plugins
* `/wp-json/jezweb/v1/theme-info` - Active theme info

== Frequently Asked Questions ==

= Does this work without Elementor? =

Yes! The plugin works with any WordPress site. If Elementor is not installed, it will still provide site structure, pages, posts, and plugin information.

= Is my data secure? =

The REST API endpoints are publicly accessible by default (read-only). For production use, you should implement authentication via API keys or restrict access via server configuration.

= Does this send data to external servers? =

No. This plugin only exposes data via REST API endpoints. It's up to you to configure an external AI agent to read this data.

== Changelog ==

= 1.0.0 =
* Initial release
* REST API endpoints for site knowledge
* Elementor structure parser
* Basic chat widget
* Admin settings page
