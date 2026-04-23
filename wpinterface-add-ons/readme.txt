=== WPInterface Add-ons ===
Contributors: WPInterface
Tags: demo import, theme addon
Requires at least: 5.6
Tested up to: 6.9
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Import WPInterface demos in one click. Quickly set up your site and apply settings, widgets, and customization to match the demo in minutes.

== Description ==

Easily import all official WPInterface theme demos with a single click, eliminating the need to manually recreate layouts or configure settings from scratch. This feature is designed to save time and simplify the website setup process for both beginners and experienced users. With a streamlined installation process, you can quickly activate the theme and automatically install any recommended or required plugins. There’s no need to search for compatible tools, everything is handled within a guided workflow to ensure your site functions exactly as intended. The importer doesn’t just replicate the visual layout; it brings in complete demo content, including pages, posts, images, menus, and widgets. This allows you to start with a fully structured website that mirrors the official demo, making it easier to understand how each element is built and customized.

== Notes ==
* The plugin remotely accesses our GitHub repository at https://github.com/wpinterface/free-themes-templates to import static demo content.

= Requirements =
* WordPress 5.6 or later.
* [WPInterface Official Themes](https://wpinterface.com/themes/).

== Copyright ==

WPInterface Add-ons WordPress Plugin, Copyright 2019-2024 WPInterface
WPInterface Add-ons is distributed under the terms of the GNU GPL.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

##  One Click Demo Import: No Coding Skills Required! 🌟

##  Fetching Theme Demo Data from URL 🌟
The 'WPInterface Add-ons' plugin requires the 'One Click Demo Import' plugin to operate correctly. It retrieves demo data from the GitHub repository at https://github.com/wpinterface/free-themes-templates using raw URLs such as https://raw.githubusercontent.com/wpinterface/free-themes-templates/main/newsmass/init.json

## For the plugin to operate correctly 🌟
The 'WPInterface Add-ons' plugin needs the 'One Click Demo Import' plugin to function correctly.

== Installation ==

    = Using The WordPress Dashboard =
	* Navigate to the 'Add New' in the plugins dashboard
	* Search for WPInterface Add-ons
	* Click Install Now
	* Activate the plugin on the Plugin dashboard

	= Uploading in WordPress Dashboard =
	* Navigate to the 'Add New' in the plugins dashboard
	* Navigate to the 'Upload' area
	* Select 'wpinterface-add-ons.zip' from your computer
	* Click 'Install Now'
	* Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= What is the plugin license? =
* This plugin is released under the GPLv2 or later license.

= What themes this plugin supports? =
* The plugin currently supports only official WPInterface Themes.


== Changelog ==

= 1.0.2 =
* Added: Support for "Smart One Click Setup" plugin alongside the existing One Click Demo Import (OCDI) integration.
* Improved: Removed hard `Requires Plugins: one-click-demo-import` header so the plugin can activate without either importer installed.
* Tested up to: WordPress 6.9.

= 1.0.0 =
* Initial release.


== Third-Party Services ==
This plugin utilizes third-party services to enhance functionality. Below are the details of the external services used:

### GitHub Content Delivery
This plugin retrieves templates from GitHub for demo import functionality.

- **Service URL:** https://github.com/wpinterface/free-themes-templates
- **Privacy Policy:** [GitHub Privacy Policy](https://docs.github.com/en/github/site-policy/github-privacy-statement)
- **Terms of Use:** [GitHub Terms of Service](https://docs.github.com/en/github/site-policy/github-terms-of-service)

Data retrieved from this service includes JSON configuration files for template imports.