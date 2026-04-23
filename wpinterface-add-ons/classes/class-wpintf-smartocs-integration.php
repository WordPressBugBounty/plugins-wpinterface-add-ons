<?php
/**
 * @package wpinterface-add-ons
 * @since 1.0.2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Wpinterface_Add_Ons_Smartocs_Companion')):
    /**
     * SMARTOCS Integration class.
     *
     * @since 1.0.2
     */
    class Wpinterface_Add_Ons_Smartocs_Companion
    {

        /**
         * Instance
         *
         * @access private
         * @var null $instance
         * @since 1.0.2
         */
        private static $instance;

        /**
         * Initiator
         *
         * @return object initialized object of class.
         * @since 1.0.2
         */
        public static function get_instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Hold the manifest data
         *
         * @access private
         * @var array $manifest_data
         * @since 1.0.2
         */
        private $manifest_data;

        /**
         * Current theme slug
         *
         * @access private
         * @var string $theme_slug
         * @since 1.0.2
         */
        private $theme_slug;

        /**
         * Theme category
         *
         * @access private
         * @var string $theme_category
         * @since 1.0.2
         */
        private $theme_category;

        /**
         * API base url
         *
         * @access private
         * @var string $api_base_url
         * @since 1.0.2
         */
        private $api_base_url;

        /**
         * Constructor.
         *
         * @since 1.0.2
         */
        public function __construct()
        {
            add_action('after_setup_theme', array($this, 'wpinterface_add_ons_smartocs_init'), 20);
            add_action('admin_head', array($this, 'admin_styles_head'));
            add_action('admin_footer', array($this, 'admin_scripts_footer'));
        }

        /**
         * Inject styles in head.
         *
         * @since 1.0.2
         */
        public function admin_styles_head()
        {
            $screen = get_current_screen();
            if ($screen && 'appearance_page_business-interface-starter-demos' !== $screen->id) {
                return;
            }

            echo '<style id="wpintf-smartocs-pro-styles">
                .smartocs__gl-item.is-pro-demo .smartocs-smart-import-error-notice { display: none !important; }
                .smartocs__gl-item.is-pro-demo .smartocs__gl-item-footer { position: relative; }
                .smartocs__gl-item.is-pro-demo::before {
                  content: "Premium";
                  position: absolute;
                  top: 16px;
                  right: 16px;
                  padding: 6px 14px;
                  font-size: 11px;
                  font-weight: 600;
                  letter-spacing: 0.08em;
                  text-transform: uppercase;
                  color: #fff;
                  background: linear-gradient(135deg, #6a5cff, #9b8cff);
                  border-radius: 4px;
                  box-shadow: 0 4px 12px rgba(106, 92, 255, 0.35);
                  z-index: 10;
                  overflow: hidden;
                }
                .smartocs__gl-item.is-pro-demo:hover::before {
                  box-shadow: 0 6px 18px rgba(106, 92, 255, 0.6);
                  transform: translateY(-1px);
                }
                .smartocs__gl-item.is-pro-demo .button-upgrade {
                    background: #3857f1;
                    color: #fff;
                    border-color: #3857f1;
                }
                .smartocs__gl-item.is-pro-demo .button-upgrade:hover {
                    background: #2541d8;
                    border-color: #2541d8;
                    color: #fff;
                }
            </style>';
        }

        /**
         * Inject scripts in footer.
         *
         * @since 1.0.2
         */
        public function admin_scripts_footer()
        {
            $screen = get_current_screen();
            if ($screen && 'appearance_page_business-interface-starter-demos' !== $screen->id) {
                return;
            }

            echo '<script id="wpintf-smartocs-pro-scripts">
                jQuery(document).ready(function($) {
                    var applyProFix = function() {
                        $(".smartocs-smart-import-description").each(function() {
                            var $desc = $(this);
                            var $item = $desc.closest(".smartocs__gl-item");
                            var descText = $desc.text();

                            if (descText.indexOf("[PRO]") !== -1) {
                                if ($item.hasClass("is-pro-demo")) return;

                                $item.addClass("is-pro-demo");
                                $desc.text(descText.replace("[PRO] ", ""));
                                
                                var upgradeUrl = "' . (isset($this->manifest_data['pro']['upgrade_url']) ? esc_url($this->manifest_data['pro']['upgrade_url']) : '#') . '";
                                $item.find(".smartocs__gl-item-buttons").append(\'<a href="\' + upgradeUrl + \'" class="button button-primary button-upgrade" target="_blank">' . esc_html__('Upgrade to Pro', 'wpinterface-add-ons') . '</a>\');
                            } else if (descText.indexOf("[PREMIUM]") !== -1) {
                                if ($item.hasClass("is-pro-demo")) return;

                                $item.addClass("is-pro-demo");
                                $desc.text(descText.replace("[PREMIUM] ", ""));
                            }
                        });
                    };
                    applyProFix();
                    // Catch dynamically loaded content if any.
                    $(document).ajaxComplete(applyProFix);
                });
            </script>';
        }

        /**
         * Initialize the integration.
         *
         * @since 1.0.2
         */
        public function wpinterface_add_ons_smartocs_init()
        {
            if (!is_admin()) {
                return;
            }

            // Get Current Theme Slug.
            $theme = wp_get_theme();
            $this->theme_slug = $theme->get_template(); // Use template (parent) if child theme.

            // Get Theme Category.
            $this->theme_category = apply_filters('wpinterface_theme_category', 'business');

            $this->api_base_url = 'https://wpinterface.com/api/demo-content/';

            // Fetch manifest.json
            $this->fetch_manifest();

            if (!empty($this->manifest_data)) {
                // Register SMARTOCS Filters.
                add_filter('smartocs/predefined_import_files', array($this, 'register_import_files'));
                add_action('smartocs/before_content_import_execution', array($this, 'before_content_import'));
                add_action('smartocs/after_all_import_execution', array($this, 'after_import_setup'), 10, 3);
            }

            // Enable attachment/image fetching during import and aggressive URL search.
            add_filter('smartocs/importer_options', array($this, 'set_importer_options'));

            // Increase HTTP timeout for large image downloads.
            add_filter('http_request_timeout', array($this, 'increase_http_timeout'), 10, 2);

            // Sideload external images from Elementor data AFTER post meta is saved.
            add_action('smartocs_wxr_importer.processed.post', array($this, 'sideload_elementor_images'), 10, 5);
        }

        /**
         * Fetch manifest.json from API.
         *
         * @since 1.0.2
         */
        private function fetch_manifest()
        {
            $manifest_url = $this->api_base_url . $this->theme_category . '/' . $this->theme_slug . '/manifest.json';
            $cache_key = 'wpintf_manifest_' . md5($manifest_url);

            // Allow cache bypass via query parameter.
            $refresh = false;
            if (isset($_GET['wpintf_refresh']) && check_admin_referer('wpintf_refresh_manifest')) {
                $refresh = true;
            }

            if (!$refresh) {
                $cached_data = get_transient($cache_key);
                if (false !== $cached_data) {
                    $this->manifest_data = $cached_data;
                    return;
                }
            }

            $response = wp_remote_get($manifest_url);

            if (is_array($response) && !is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);

                if (!empty($data)) {
                    $this->manifest_data = $data;
                    set_transient($cache_key, $data, 12 * HOUR_IN_SECONDS);
                }
            }
        }

        /**
         * Register import files for SMARTOCS.
         *
         * @since 1.0.2
         * @param array $predefined_imports
         * @return array
         */
        public function register_import_files($predefined_imports)
        {
            $base_url = $this->api_base_url . $this->theme_category . '/' . $this->theme_slug . '/';
            $local_zip = $this->local_demo_zip_url();

            // Register Free Demo.
            if (!empty($this->manifest_data['free'])) {
                $free = $this->manifest_data['free'];

                $new_import = array(
                    'slug' => 'free',
                    'name' => isset($free['name']) ? $free['name'] : 'Main Demo',
                    /* translators: %s: Theme slug. */
                    'description' => sprintf(esc_html__('Default demo for %s.', 'wpinterface-add-ons'), $this->theme_slug),
                    'preview_image' => $base_url . $free['screenshot'],
                    'preview_url' => $free['demo_url'],
                    // Use locally-served ZIP when available so images can be fetched reliably.
                    'zip_url' => !empty($local_zip) ? $local_zip : $base_url . $free['download'],
                );

                // Avoid double registration.
                $exists = false;
                foreach ($predefined_imports as $import) {
                    if (isset($import['zip_url']) && $import['zip_url'] === $new_import['zip_url']) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $predefined_imports[] = $new_import;
                }
            }

            // Register Pro Demos from all sections starting with 'pro'.
            foreach ($this->manifest_data as $section_key => $section_data) {
                if (strpos($section_key, 'pro') === 0 && !empty($section_data['demos'])) {
                    foreach ($section_data['demos'] as $pro_demo) {
                        $pro_active = false;

                        // Support per-demo activation check.
                        if (!empty($pro_demo['is_pro']) && !empty($pro_demo['pro_class'])) {
                            if (class_exists($pro_demo['pro_class'])) {
                                $pro_active = true;
                            }
                        }

                        // Fallback to top-level activation check if per-demo is not set.
                        if (!$pro_active && !empty($this->manifest_data['is_pro']) && !empty($this->manifest_data['pro_class'])) {
                            if (class_exists($this->manifest_data['pro_class'])) {
                                $pro_active = true;
                            }
                        }

                        $import_data = array(
                            'slug' => isset($pro_demo['slug']) ? $pro_demo['slug'] : '',
                            'name' => isset($pro_demo['name']) ? $pro_demo['name'] : 'Pro Demo',
                            'preview_image' => $base_url . $pro_demo['screenshot'],
                            'preview_url' => $pro_demo['demo_url'],
                        );

                        if ($pro_active) {
                            $import_data['description'] = '[PREMIUM] ' . esc_html__('Premium starter site.', 'wpinterface-add-ons');
                            $import_data['zip_url'] = $base_url . (isset($pro_demo['download']) ? $pro_demo['download'] : '');
                        } else {
                            $import_data['description'] = '[PRO] ' . esc_html__('Premium starter site. Upgrade to Pro to import this demo.', 'wpinterface-add-ons');
                            $import_data['is_pro'] = true; // Custom flag for potential CSS/badge usage.
                        }

                        $predefined_imports[] = $import_data;
                    }
                }
            }

            return $predefined_imports;
        }

        /**
         * Get the URL for the locally-served demo ZIP file.
         *
         * @since 1.0.3
         * @return string URL or empty string if file does not exist.
         */
        private function local_demo_zip_url()
        {
            $upload_dir = wp_upload_dir();
            $local_zip = trailingslashit($upload_dir['basedir']) . 'business-interface-demo.zip';
            $local_url = trailingslashit($upload_dir['baseurl']) . 'business-interface-demo.zip';
            return file_exists($local_zip) ? $local_url : '';
        }

        /**
         * Enable attachment/image fetching and aggressive URL search during import.
         *
         * @since 1.0.3
         * @param array $options Importer options.
         * @return array
         */
        public function set_importer_options($options)
        {
            $options['fetch_attachments'] = true;
            $options['aggressive_url_search'] = true;
            return $options;
        }

        /**
         * Increase HTTP timeout for large image imports.
         *
         * @since 1.0.3
         * @param int    $timeout Default timeout in seconds.
         * @param string $url     Request URL.
         * @return int
         */
        public function increase_http_timeout($timeout, $url)
        {
            // Apply extended timeout only during admin import operations.
            if (is_admin() && function_exists('get_current_screen')) {
                return 300; // 5 minutes for large image downloads.
            }
            return $timeout;
        }

        /**
         * Sideload external images embedded in Elementor data after each post is imported.
         *
         * Images in the demo XML are stored only as external URLs inside _elementor_data
         * (not as attachment posts). This method fetches each unique external image URL,
         * uploads it to the local media library, and rewrites the URL inside the stored
         * Elementor JSON so the page renders correctly on the new site.
         *
         * @since 1.0.3
         * @param int   $post_id     New (local) post ID.
         * @param int   $original_id Original post ID from the import file.
         * @param array $postdata    Processed post data that was inserted.
         * @param array $data        Raw post data from the WXR file.
         * @return void
         */
        public function sideload_elementor_images($post_id, $data, $meta = array(), $comments = array(), $terms = array())
        {
            if (!function_exists('download_url')) {
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }

            // Read stored Elementor data for this post.
            $elementor_data = get_post_meta($post_id, '_elementor_data', true);
            if (empty($elementor_data) || !is_string($elementor_data)) {
                return;
            }

            // Extract all external image URLs from the JSON string.
            // Note: Elementor saves its data as a JSON string, so slashes are escaped (e.g. https:\/\/...)
            $demo_domain = apply_filters('wpinterface_demo_domain', 'elementor.wpinterface.com');
            preg_match_all(
                '#https?:\\\\?/\\\\?/' . preg_quote($demo_domain, '#') . '[^\s\'"<>]+\.(?:jpg|jpeg|png|gif|webp|svg)#i',
                $elementor_data,
                $matches
            );

            if (empty($matches[0])) {
                return;
            }

            $unique_urls = array_unique($matches[0]);
            $url_map = array();
            $id_map = array();

            foreach ($unique_urls as $remote_url) {
                // Slashes are escaped in the JSON string, unescape them for WP functions
                $clean_url = stripslashes($remote_url);

                // Skip if already sideloaded in a previous import run.
                $existing = attachment_url_to_postid($clean_url);
                if ($existing) {
                    $local_url = wp_get_attachment_url($existing);
                    $url_map[$remote_url] = $local_url;
                    $url_map[$clean_url] = $local_url;

                    // Add ID mapping for Elementor
                    $id_map[$clean_url] = $existing;
                    $id_map[$remote_url] = $existing;

                    continue;
                }

                // Sideload the image into the media library.
                $tmp = download_url($clean_url);
                if (is_wp_error($tmp)) {
                    continue;
                }

                $file_array = array(
                    'name' => basename(wp_parse_url($clean_url, PHP_URL_PATH)),
                    'tmp_name' => $tmp,
                );

                $attachment_id = media_handle_sideload($file_array, $post_id);
                @wp_delete_file($tmp); // Clean up temp file even on error.

                if (!is_wp_error($attachment_id)) {
                    $local_url = wp_get_attachment_url($attachment_id);
                    // Elementor saves URLs with \/ escaped slashes. str_replace replaces the escaped literal string.
                    // The replacement ($local_url) will have unescaped slashes (/) which is completely valid JSON.
                    $url_map[$remote_url] = $local_url;

                    // We must also do a secondary replacement: sometimes elementor saves the same URL unescaped
                    // in another meta key or JSON structure. So map both.
                    $url_map[$clean_url] = $local_url;
                    $id_map[$clean_url] = $attachment_id;
                    $id_map[$remote_url] = $attachment_id;

                    // It's also critical to try to map JSON-escaped local URLs just in case
                    $escaped_local = str_replace('/', '\/', $local_url);
                    $url_map[$remote_url] = $escaped_local;
                }
            }

            if (empty($url_map)) {
                return;
            }

            // Decode the elementor data, recursively update IDs, encode again.
            $decoded_data = json_decode($elementor_data, true);
            if (is_array($decoded_data)) {
                $this->update_elementor_media_recursive($decoded_data, $url_map, $id_map);
                $updated_data = wp_json_encode($decoded_data);
            } else {
                // Fallback to basic string replacement if json_decode failed
                $updated_data = str_replace(
                    array_keys($url_map),
                    array_values($url_map),
                    $elementor_data
                );
            }

            if ($updated_data !== $elementor_data) {
                update_post_meta($post_id, '_elementor_data', wp_slash($updated_data));
                // Invalidate Elementor's file-based CSS cache so the page regenerates.
                if (defined('ELEMENTOR_PATH') && class_exists('\Elementor\Plugin')) {
                    \Elementor\Plugin::$instance->files_manager->clear_cache();
                }
            }
        }

        /**
         * Recursively update Elementor JSON arrays to fix attachment IDs.
         * 
         * @since 1.0.3
         * @param mixed $element Reference to the elementor data array.
         * @param array $url_map Map of remote URLs to local URLs.
         * @param array $id_map  Map of remote URLs to local attachment IDs.
         */
        private function update_elementor_media_recursive(&$element, $url_map, $id_map)
        {
            if (is_array($element)) {
                // If it's a media control with a URL, replace it and optionally update the ID
                if (isset($element['url']) && is_string($element['url'])) {
                    $current_url = $element['url'];
                    if (isset($url_map[$current_url])) {
                        $element['url'] = $url_map[$current_url];
                        if (isset($element['id']) && isset($id_map[$current_url])) {
                            $element['id'] = $id_map[$current_url];
                        }
                    } else {
                        // Check if the clean version is mapped
                        $clean = stripslashes($current_url);
                        if (isset($url_map[$clean])) {
                            $element['url'] = $url_map[$clean];
                            if (isset($element['id']) && isset($id_map[$clean])) {
                                $element['id'] = $id_map[$clean];
                            }
                        }
                    }
                }

                // Recurse into all children
                foreach ($element as $key => &$value) {
                    if (is_array($value)) {
                        $this->update_elementor_media_recursive($value, $url_map, $id_map);
                    } else if (is_string($value)) {
                        // For any inline URLs in text or inner stringified JSON, do a basic string replacement
                        $value = str_replace(array_keys($url_map), array_values($url_map), $value);
                    }
                }
            }
        }


        /**
         * Setup before content import.
         *
         * @since 1.0.2
         * @param array $selected_import_files
         * @param array $import_files
         * @param int $selected_index
         */

        public function before_content_import($selected_import_files = array(), $import_files = array(), $selected_index = 0)
        {
            // Trash default "hello word" post.
            if (isset($selected_import_files['content']) && !empty($selected_import_files['content'])) {
                $post = get_post(1);
                if ($post && 'hello-world' == $post->post_name) {
                    wp_trash_post(1);
                }
            }

            // Empty default sidebar widgetarea.
            $registered_sidebars = get_option('sidebars_widgets');
            if (isset($registered_sidebars['sidebar-1']) && !empty($registered_sidebars['sidebar-1'])) {
                update_option('sidebars_widgets', array('sidebar-1' => array()));
            }
        }

        /**
         * Setup after finishing demo import.
         *
         * @since 1.0.2
         */
        public function after_import_setup($selected_import_files, $import_files, $selected_index)
        {
            // Assign front page and posts page.
            $front_page_id = 0;
            $blog_page_id = 0;

            $pages = get_posts(
                array(
                    'post_type' => 'page',
                    'title' => 'Homepage',
                    'posts_per_page' => 1,
                    'post_status' => 'publish',
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                )
            );

            if (!empty($pages)) {
                $front_page_id = $pages[0]->ID;
            } else {
                $pages = get_posts(
                    array(
                        'post_type' => 'page',
                        'title' => 'Home',
                        'posts_per_page' => 1,
                        'post_status' => 'publish',
                        'no_found_rows' => true,
                        'ignore_sticky_posts' => true,
                        'update_post_term_cache' => false,
                        'update_post_meta_cache' => false,
                    )
                );
                if (!empty($pages)) {
                    $front_page_id = $pages[0]->ID;
                }
            }

            $pages = get_posts(
                array(
                    'post_type' => 'page',
                    'title' => 'Blog',
                    'posts_per_page' => 1,
                    'post_status' => 'publish',
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                )
            );

            if (!empty($pages)) {
                $blog_page_id = $pages[0]->ID;
            }

            if ($front_page_id) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $front_page_id);
            }

            if ($blog_page_id) {
                update_option('page_for_posts', $blog_page_id);
            }

            // Assign navigation menu locations.
            $menu_locations = array(
                'primary',
                'top-menu',
                'social',
                'footer',
            );

            $navigation_settings = get_theme_mod('nav_menu_locations', array());
            $menus = wp_get_nav_menus();

            if (!is_wp_error($menus) && !empty($menus)) {
                foreach ($menus as $menu) {
                    $slug = $menu->slug;
                    // Try to match menu slug or name with location keys.
                    foreach ($menu_locations as $location) {
                        if ($slug === $location || strpos(strtolower($menu->name), $location) !== false) {
                            $navigation_settings[$location] = $menu->term_id;
                        }
                    }
                }
                set_theme_mod('nav_menu_locations', $navigation_settings);
            }
        }
    }

endif;

Wpinterface_Add_Ons_Smartocs_Companion::get_instance();
