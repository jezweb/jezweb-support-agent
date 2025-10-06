# Jezweb Support Agent - Installation Guide

## Quick Installation on newcastleseo.com.au

### Method 1: FTP/SFTP Upload

1. **Zip the plugin folder:**
   ```bash
   cd /home/jez/Documents/jezweb-clients-automated-support-agents
   zip -r jezweb-support.zip jezweb-support/
   ```

2. **Upload via WordPress Admin:**
   - Login to https://www.newcastleseo.com.au/wp-admin
   - Go to Plugins → Add New → Upload Plugin
   - Choose `jezweb-support.zip`
   - Click "Install Now"
   - Activate the plugin

### Method 2: Direct File Upload (SFTP)

1. **Upload the folder directly:**
   ```bash
   # If you have SFTP access:
   scp -r jezweb-support/ user@newcastleseo.com.au:/path/to/wp-content/plugins/
   ```

2. **Activate in WordPress:**
   - Go to Plugins → Installed Plugins
   - Find "Jezweb Support Agent"
   - Click "Activate"

### Method 3: Using WP-CLI (if available)

```bash
# SSH into the server
ssh user@newcastleseo.com.au

# Navigate to WordPress root
cd /path/to/wordpress

# Create symlink or copy plugin
cp -r /path/to/jezweb-support wp-content/plugins/

# Activate
wp plugin activate jezweb-support
```

## Configuration

After activation:

1. **Go to Settings → Jezweb Support**

2. **Set Site ID:**
   - Enter: `newcastleseo.com.au`

3. **Set Agent URL (optional for now):**
   - Leave as default: `https://support.jezweb.workers.dev`
   - (This will be the Cloudflare Worker we build next)

4. **Save Settings**

## Testing the Installation

### Test 1: Check REST API Endpoints

Visit these URLs in your browser (replace with actual domain):

1. **Site Knowledge:**
   ```
   https://www.newcastleseo.com.au/wp-json/jezweb/v1/site-knowledge
   ```
   Should return JSON with site info, pages, plugins, theme, etc.

2. **Pages List:**
   ```
   https://www.newcastleseo.com.au/wp-json/jezweb/v1/pages-list
   ```
   Should return array of all pages.

3. **Elementor Data (Homepage - ID 51):**
   ```
   https://www.newcastleseo.com.au/wp-json/jezweb/v1/elementor-data/51
   ```
   Should return complete Elementor structure with navigation guide!

4. **Plugins:**
   ```
   https://www.newcastleseo.com.au/wp-json/jezweb/v1/plugins
   ```

### Test 2: Check Chat Widget

1. Login to wp-admin
2. Visit any page on the front-end
3. Look for the 💬 chat bubble in bottom-right corner
4. Click it to open the chat widget

### Test 3: Verify Elementor Parsing

Check the Elementor data endpoint for your homepage:

```bash
curl -s "https://www.newcastleseo.com.au/wp-json/jezweb/v1/elementor-data/51" | jq '.navigation_guide'
```

You should see navigation paths like:
```json
[
  {
    "path": "Section 1 → Container (ID: 89077f6) → Heading \"Newcastle SEO\" (ID: e2c660c)",
    "element_id": "e2c660c",
    "type": "widget",
    "widget_type": "heading",
    "content_preview": "Newcastle SEO"
  },
  ...
]
```

## What You Should See

### In Settings Page:
- Site statistics (number of pages, Elementor pages, plugins)
- API endpoint links (all clickable and working)
- Configuration form

### In API Responses:

**`/site-knowledge` should include:**
- site_info (URL, name, WordPress version)
- theme (name, version)
- pages (all pages with Elementor structure)
- plugins (list with active status)
- menus
- elementor_globals (colors, fonts)

**`/elementor-data/51` should include:**
- Complete raw Elementor JSON
- Parsed structure (simplified tree)
- Navigation guide (human-readable paths)
- Editable elements (mapped by ID)

## Troubleshooting

### Plugin doesn't activate
- Check PHP version (requires 7.4+)
- Check for plugin conflicts
- Enable WP_DEBUG to see errors

### REST API returns 404
- Go to Settings → Permalinks
- Click "Save Changes" (flushes rewrite rules)
- Try again

### No Elementor data
- Make sure the page actually uses Elementor
- Check that page ID is correct
- Visit `/pages-list` to see which pages have Elementor

### Chat widget doesn't appear
- Make sure you're logged in as an editor or admin
- Check browser console for JavaScript errors
- Clear site cache if using caching plugin

## Next Steps

Once the plugin is working:

1. ✅ Test all API endpoints
2. ✅ Verify Elementor parsing is accurate
3. 🔜 Build Cloudflare Worker to consume this data
4. 🔜 Create AI Agent using Gemini
5. 🔜 Connect chat widget to live agent

## Security Considerations (Production)

For production deployment:

1. **Add authentication to REST endpoints:**
   ```php
   // In class-rest-api.php, update check_permission():
   public function check_permission() {
       $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
       $expected_token = get_option('jezweb_api_token');
       return $auth_header === "Bearer {$expected_token}";
   }
   ```

2. **Or restrict via .htaccess:**
   ```apache
   <Files "wp-json">
       Order Deny,Allow
       Deny from all
       Allow from cloudflare-ip-ranges
   </Files>
   ```

3. **Use Application Passwords for authenticated requests**

## Support

Issues or questions?
- GitHub: (repo URL)
- Email: jeremy@jezweb.net
