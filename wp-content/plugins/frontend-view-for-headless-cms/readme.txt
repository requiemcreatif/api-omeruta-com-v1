=== Frontend View For Headless CMS ===
Contributors: Dropndot
Tags: headless frontend, next.js, headless CMS WordPress, React, WordPress to Next.js
Requires at least: 5.0
Tested up to: 6.5.2
Requires PHP: 7.2
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Frontend View For Headless CMS links backend WordPress articles, pages, custom post types, taxonomies, and categories to the headless CMS site.

== Description ==

Frontend View For Headless CMS is a plugin that seamlessly links your backend WordPress content, including posts, pages, custom post types, taxonomies, categories, and authors to your headless CMS frontend site. This ensures that any content in your WordPress backend are linked with your headless CMS frontend, therefore, you can easily visit the contents using visit links.

**Features:**

- Redirects from WordPress content to the corresponding URLs on your headless CMS site.

== Installation ==

1. From your WordPress dashboard, go to the Plugins menu and click 'Add New'.
2. In the search bar, type `Frontend View For Headless CMS` and press Enter.
3. Locate the plugin in the search results and click the 'Install Now' button.
4. Once the installation is complete, click the 'Activate' button.
5. Navigate to 'Frontend View Settings' in the WordPress admin to configure the plugin.
6. Set the URL of the front-end site in the From Field and save settings.

**Alternative Way:**

1. Upload the entire `frontend-view-for-headless-cms` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to 'Frontend View Settings' in the WordPress admin to configure the plugin.
4. Set the URL of the front-end site in the From Field and save settings.

== Frequently Asked Questions ==

= Can I use this plugin with any front-end application? =

Yes, this plugin is designed to work with All front-end application that supports Rest API/GraphQL

= How do I configure the plugin? =

After activating the plugin, Navigate to 'Frontend View Settings' in the WordPress admin and enter the URL of your frontend site. The plugin will use this URL to redirect content.

= Does this plugin work with custom post types? =

Yes, the plugin supports redirection for custom post types, taxonomies, and categories.

= Is this plugin compatible with multisite? =

Yes, the plugin is compatible with multisite installations.

= How do I set the frontend site URL? =

Navigate to 'Frontend View Settings' in the WordPress admin and enter the URL of your frontend site.

= Does the plugin support HTTPS? =

Yes, the plugin supports both HTTP and HTTPS protocols.

= How do I disable redirection for specific content types? =

Currently, the plugin redirects all singular content, taxonomies, categories, home, and author pages. To customize this, you will need to modify the plugin code.

== Screenshots ==

1. Plugin settings page.
2. Example of content redirection - Clicking View Button of a post.
3. Example of content redirection - Redirected to frontend site's Page of the respective post.

== Changelog ==

= 1.1 =
* `/preview/` slug will be used for preview posts, pages etc.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.1 =
* `/preview/` slug will be used for preview posts, pages etc.

= 1.0 =
Initial release.

== Development ==

This plugin is developed by Dropndot Solutions. For more information, visit [Dropndot Solutions](https://www.dropndot.com/).

== Contact ==

For support or inquiries, please contact us at [info@dropndot.com](mailto:info@dropndot.com).

== Like the Plugin? ==

If you find this plugin helpful, please leave a positive review on the [WordPress Plugin Directory](https://wordpress.org/plugins/frontend-view-for-headless-cms)
