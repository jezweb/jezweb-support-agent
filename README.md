# Jezweb Support Agent WordPress Plugin

A WordPress plugin that exposes site structure and Elementor data via REST API for AI-powered support assistance.

## Overview

This plugin is designed to work with a Cloudflare Agent (AI assistant) to provide intelligent support for WordPress + Elementor websites. It exposes comprehensive site knowledge through clean REST API endpoints.

## Features

- ✅ **Complete Site Knowledge Export** - Pages, posts, plugins, theme, menus
- ✅ **Elementor Structure Parser** - Deep understanding of Elementor page layouts
- ✅ **Navigation Guides** - Human-readable paths to edit any element
- ✅ **Editable Content Mapping** - All editable content mapped to widget IDs
- ✅ **Built-in Chat Widget** - Support chat for logged-in users
- ✅ **Admin Settings Page** - Easy configuration and API testing

## REST API Endpoints

### `/wp-json/jezweb/v1/site-knowledge`
Complete site export including:
- Site info (URL, name, WordPress version)
- All pages with Elementor structures
- Posts (up to 50 recent)
- Installed plugins (with active status)
- Theme information
- Navigation menus
- Elementor global settings (colors, fonts)

### `/wp-json/jezweb/v1/elementor-data/{page_id}`
Detailed Elementor data for specific page:
- Raw Elementor JSON
- Parsed structure (simplified widget tree)
- Navigation guide with human-readable paths
- Editable elements mapped by ID
- Widget count and metadata

**Example navigation guide output:**
```json
{
  "path": "Section 1 → Container → Heading \"Newcastle SEO\" (ID: e2c660c)",
  "element_id": "e2c660c",
  "type": "widget",
  "widget_type": "heading",
  "content_preview": "Newcastle SEO"
}
```

### `/wp-json/jezweb/v1/pages-list`
Simple list of all published pages with:
- ID, title, slug, URL
- Has Elementor flag
- Last modified date

### `/wp-json/jezweb/v1/plugins`
All installed plugins with:
- Name, version, description
- Active status

### `/wp-json/jezweb/v1/theme-info`
Active theme information

## Installation

See [INSTALLATION.md](INSTALLATION.md) for detailed setup instructions.

**Quick install:**
1. Upload `jezweb-support` folder to `/wp-content/plugins/`
2. Activate via Plugins menu
3. Configure in Settings → Jezweb Support

## How It Works

### For AI Support Use Case:

1. **Plugin installed on client sites** (e.g., newcastleseo.com.au)
2. **Cloudflare Worker/Agent** fetches data from REST endpoints
3. **AI processes** the structure and content knowledge
4. **User asks question** via chat widget
5. **AI responds** with specific navigation guidance

### Example User Flow:

**User asks:** "How do I change the hero heading on my homepage?"

**AI Agent:**
1. Fetches `/elementor-data/51` for homepage
2. Searches navigation guide for "heading" in "Section 1"
3. Finds: `Section 1 → Container → Heading "Newcastle SEO" (ID: e2c660c)`
4. Responds:

> "To edit the hero heading 'Newcastle SEO':
> 1. Go to Pages → All Pages
> 2. Click 'Edit with Elementor' on the Home page
> 3. Click on the Heading widget in Section 1 (ID: e2c660c)
> 4. Edit the text in the left panel
> 5. Click Update"

## What Data Is Exposed?

The plugin provides **read-only** access to:
- ✅ Public page/post content
- ✅ Site structure and navigation
- ✅ Plugin names (not settings/configs)
- ✅ Theme name
- ✅ Elementor page structures

It does **NOT** expose:
- ❌ User passwords or credentials
- ❌ Database contents
- ❌ Private posts/pages
- ❌ Plugin configurations
- ❌ Email addresses (unless public)

## Security

**For MVP/Testing:**
- Endpoints are publicly accessible (read-only)
- Safe for testing on development sites

**For Production:**
- Add authentication via API tokens
- Restrict access to Cloudflare IP ranges
- Use WordPress Application Passwords
- See INSTALLATION.md for security setup

## Development

### File Structure:
```
jezweb-support/
├── jezweb-support.php              # Main plugin file
├── includes/
│   ├── class-rest-api.php          # REST endpoint handlers
│   ├── class-elementor-parser.php  # Elementor JSON parser
│   └── class-chat-widget.php       # Chat widget renderer
├── admin/
│   └── settings-page.php           # Admin settings UI
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
├── README.md
├── INSTALLATION.md
└── readme.txt                      # WordPress plugin readme
```

### Extending the Plugin

**Add new REST endpoint:**
```php
// In class-rest-api.php
register_rest_route($this->namespace, '/your-endpoint', array(
    'methods' => 'GET',
    'callback' => array($this, 'your_callback'),
    'permission_callback' => array($this, 'check_permission')
));
```

**Add new widget type to parser:**
```php
// In class-elementor-parser.php extract_widget_content()
case 'your-widget-type':
    $content['your_field'] = isset($settings['your_field']) ? $settings['your_field'] : '';
    break;
```

## Roadmap

- [ ] Webhook support for real-time updates
- [ ] Change detection (compare snapshots)
- [ ] Support for other page builders (Beaver, WPBakery)
- [ ] Admin bar integration
- [ ] Live Cloudflare Agent connection
- [ ] Analytics on common questions

## Testing

After installation, test these URLs:

1. https://your-site.com/wp-json/jezweb/v1/site-knowledge
2. https://your-site.com/wp-json/jezweb/v1/pages-list
3. https://your-site.com/wp-json/jezweb/v1/elementor-data/51

All should return valid JSON.

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Elementor (optional but recommended)

## License

GPL v2 or later

## Author

**Jezweb**
- Website: https://jezweb.com.au
- Email: jeremy@jezweb.net

## Next Steps

1. Install on test site (newcastleseo.com.au)
2. Verify all API endpoints work
3. Build Cloudflare Worker to consume data
4. Create AI Agent using Gemini
5. Connect chat widget to live agent
6. Deploy to production sites
