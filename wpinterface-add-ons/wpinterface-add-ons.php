<?php
/**
 * Plugin Name: WPInterface Add-ons
 * Requires Plugins: one-click-demo-import
 * Author: WPInterface
 * Author URI: https://www.wpinterface.com
 * Version: 1.0.1
 * Description: WPInterface Add-ons enhances user-friendliness and simplifies the website-building process by allowing users to import demo data with a single click, creating a website identical to the demo effortlessly.
 * Text Domain: wpinterface-add-ons
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 5.2
 * Tested up to: 6.6
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *
 *  This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 *  General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 *  that you can use any other version of the GPL.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 *  even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('WPINTEERFACE_FILE', __FILE__);
define('WPINTEERFACE_ROOT', dirname(plugin_basename(WPINTEERFACE_FILE)));
define('WPINTEERFACE_PLUGIN_NAME', 'wpinterface-add-ons');
define('WPINTEERFACE_PLUGIN_SHORT_NAME', 'wpinterface-add-ons');
define('WPINTEERFACE_IC_URL', plugin_dir_url(__FILE__));
define('WPINTEERFACE_IC_DIR', plugin_dir_path(__FILE__));


if (!version_compare(PHP_VERSION, '5.6', '>=')) {
    add_action('admin_notices', 'wpinterface_add_ons_php_version_check');
} elseif (!version_compare(get_bloginfo('version'), '4.7', '>=')) {
    add_action('admin_notices', 'wpinterface_add_ons_wp_version_check');
} else {
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    if (!is_plugin_active('one-click-demo-import/one-click-demo-import.php')) {
        add_action('admin_notices', 'wpinterface_add_ons_ocdi_check');
    } else {
        require_once 'classes/class-wpintf-demo-import.php';
    }
}

/**
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @return void
 * @since 1.0.0
 *
 */
function wpinterface_add_ons_php_version_check()
{
    /* translators: %s: PHP version */
    $message = sprintf(esc_html__('WPInterface Add-ons requires PHP version %s+, plugin is currently NOT RUNNING.', 'wpinterface-add-ons'), '5.6');
    $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($html_message);
}

/**
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @return void
 * @since 1.0.0
 *
 */
function wpinterface_add_ons_wp_version_check()
{
    /* translators: %s: WordPress version */
    $message = sprintf(esc_html__('WPInterface Add-ons requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'wpinterface-add-ons'), '4.7');
    $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($html_message);
}

/**
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @return void
 * @since 1.0.0
 *
 */
function wpinterface_add_ons_ocdi_check() {
    $plugin_name = '<a href="' . esc_url( admin_url( 'plugin-install.php?s=One%20Click%20Demo%20Import&tab=search&type=term' ) ) . '" target="_blank">One Click Demo Import</a>';
    /* translators: %s: One Click Demo Import plugin link */
    $message = sprintf( esc_html__( 'WPInterface Add-ons is currently inactive. Please install and activate the %s plugin to proceed.', 'wpinterface-add-ons' ), $plugin_name );
    $html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
    echo wp_kses_post( $html_message );
}