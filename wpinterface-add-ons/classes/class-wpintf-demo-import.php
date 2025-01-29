<?php
/**
 * @package wpinterface-add-ons
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('Wpinterface_Add_Ons_Demo_Import_Companion')) :
    /**
     * Main class.
     *
     * @since 1.0.0
     */
    class Wpinterface_Add_Ons_Demo_Import_Companion
    {

        /**
         * Instance
         *
         * @access private
         * @var null $instance
         * @since 1.0.0
         */
        private static $instance;

        /**
         * Initiator
         *
         * @return object initialized object of class.
         * @since 1.0.0
         */
        public static function get_instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Hold the config settings
         *
         * @access private
         * @var array $config_data
         * @since 1.0.0
         */
        private $config_data;

        /**
         * Current theme
         *
         * @access private
         * @var string $current_theme
         * @since 1.0.0
         */
        private $current_theme;

        /**
         * Current template
         *
         * @access private
         * @var string $current_template
         * @since 1.0.0
         */
        private $current_template;

        /**
         * Theme url
         *
         * @access private
         * @var string $theme_url
         * @since 1.0.0
         */
        private $theme_url;

        /**
         * Github base url
         *
         * @access private
         * @var string $base_url
         * @since 1.0.0
         */
        private $base_url;

        /**
         * Constructor.
         *
         * @since 1.0.0
         */
        public function __construct()
        {

            add_action('plugins_loaded', array($this, 'wpinterface_add_ons_setup_init'));

            add_action('wpinterface_add_ons_starter_templates', array($this, 'wpinterface_add_ons_display_templates'));
            add_action('admin_enqueue_scripts', array($this, 'wpinterface_add_ons_enqueue_scripts_and_styles'));
            add_filter(
                'admin_body_class',
                function ($classes) {
                    $classes .= ' wpinterface-add-ons ';
                    return $classes;
                }
            );
        }

        /**
         * Setup OCDI Filters
         *
         * @since 1.0.0
         */
        public function wpinterface_add_ons_setup_init()
        {

            // Only execute on the admin side.
            if (is_admin()) {

                // Get Current Theme.
                $current_theme = wp_get_theme();
                if ($current_theme->exists() && $current_theme->parent()) {
                    $parent_theme = $current_theme->parent();
                    if ($parent_theme->exists()) {
                        $this->current_theme = $parent_theme->get_stylesheet();
                    }
                    // Set current theme template for child theme.
                    $this->current_template = $current_theme->get_stylesheet();
                } elseif ($current_theme->exists()) {
                    $this->current_theme = $current_theme->get_stylesheet();
                    // Set current theme template.
                    $this->current_template = $this->current_theme;
                }

                $this->theme_url = 'https://wpinterface.com/theme/' . $this->current_theme;

                // Base url of the repository.
                $this->base_url = 'https://raw.githubusercontent.com/wpinterface/free-themes-templates/master/';

                // Get the json file to populate proper demo setup settings.
                $config_file = $this->base_url . $this->current_template . '/init.json';

                $data = wp_remote_get($config_file);

                // Only execute if our config is loaded properly.
                if (is_array($data) && !is_wp_error($data)) {

                    $data = wp_remote_retrieve_body($data);
                    $this->config_data = json_decode($data, true);

                    add_filter('ocdi/plugin_page_title', array($this, 'wpinterface_add_ons_disable_ocdi_title'));
                    add_filter('ocdi/plugin_intro_text', array($this, 'wpinterface_add_ons_disable_ocdi_intro'));

                    add_filter('ocdi/register_plugins', array($this, 'wpinterface_add_ons_ocdi_register_plugins'));
                    add_filter('ocdi/plugin_page_setup', array($this, 'wpinterface_add_ons_ocdi_setup'));
                    add_filter('ocdi/import_files', array($this, 'wpinterface_add_ons_manage_import'));
                    add_action('ocdi/before_content_import', array($this, 'wpinterface_add_ons_before_content_import'));
                    add_action('ocdi/before_widgets_import', array($this, 'wpinterface_add_ons_before_widgets_import'));
                    add_action('ocdi/after_import', array($this, 'wpinterface_add_ons_after_import'));
                }
            }

        }

        /**
         * Disable OCDI title
         *
         * @param string $plugin_title OCDI Title.
         * @since 1.0.0
         */
        public function wpinterface_add_ons_disable_ocdi_title($plugin_title)
        {
            $plugin_title = '';
            return $plugin_title;
        }

        /**
         * Disable OCDI Intro Text.
         *
         * @param string $plugin_intro_text OCDI Intro Text.
         * @since 1.0.0
         */
        public function wpinterface_add_ons_disable_ocdi_intro($plugin_intro_text)
        {
            $plugin_intro_text = '';
            return $plugin_intro_text;
        }

        /**
         * Register recommended plugins for OCDI.
         *
         * @param array $plugins Array of plugins.
         * @since 1.0.0
         */
        public function wpinterface_add_ons_ocdi_register_plugins($plugins)
        {

            if (isset($this->config_data['plugins'])) {

                $recommended_plugins = array();

                foreach ($this->config_data['plugins'] as $plugin_data) {
                    $recommended_plugins[] = array(
                        'name' => $plugin_data['name'],
                        'slug' => $plugin_data['slug'],
                        'required' => $plugin_data['required'],
                        'preselected' => $plugin_data['preselected'],
                    );
                }

                return array_merge($plugins, $recommended_plugins);

            } else {
                return $plugins;
            }
        }

        /**
         * Change OCDI default texts
         *
         * @param array $default_settings Default text array.
         * @since 1.0.0
         */
        public function wpinterface_add_ons_ocdi_setup($default_settings)
        {

            $default_settings['page_title'] = esc_html__('Demo Import', 'wpinterface-add-ons');
            $default_settings['menu_slug'] = $this->current_theme . '-demo-import';

            return $default_settings;
        }

        /**
         * Init array for the OCDI demos
         *
         * @since 1.0.0
         */
        public function wpinterface_add_ons_manage_import()
        {

            $output = array();

            if ($this->config_data && isset($this->config_data['import_files'])) {
                $free_demos = $this->config_data['import_files']['free'];
                foreach ($free_demos as $demo_data) {
                    $file_url = $this->base_url . $this->current_template . '/' . $demo_data['import_path'] . '/';
                    $output[] = array(
                        'import_file_name' => $demo_data['import_name'],
                        'import_file_url' => $file_url . 'content.xml',
                        'import_widget_file_url' => $file_url . 'widgets.wie',
                        'import_customizer_file_url' => $file_url . 'customizer.dat',
                        'import_preview_image_url' => $file_url . 'screenshot.webp',
                        'preview_url' => $demo_data['preview_url'],
                        'import_notice' => esc_html('Make sure to leave the preselected plugins as it is to make the starter sites working as in our preview sites. Other plugins are optional and you can install them only if you need them.', 'wpinterface-add-ons'),
                    );
                }
            }

            return $output;
        }

        /**
         * Before Content import.
         *
         * @since 1.0.0
         */
        function wpinterface_add_ons_before_content_import()
        {
            // Trash default "hello word" post.
            $post = get_post(1);
            $slug = isset($post->post_name) ? $post->post_name : '';
            if ('hello-world' == $slug) {
                wp_trash_post(1);
            }
        }

        /**
         * Before widgets import.
         *
         * @since 1.0.0
         */
        function wpinterface_add_ons_before_widgets_import()
        {
            // Empty default sidebar widgetarea.
            $registered_sidebars = get_option('sidebars_widgets');
            if (isset($registered_sidebars['sidebar-1']) && !empty($registered_sidebars['sidebar-1'])) {
                update_option('sidebars_widgets', array('sidebar-1' => array()));
            }
        }

        /**
         * Setup after finishing demo import
         *
         * @since 1.0.0
         */
        public function wpinterface_add_ons_after_import()
        {

            // Assign front page and posts page (blog page) if any.
            $front_page_id = null;
            $blog_page_id = null;

            $front_page = new WP_Query(
                array(
                    'post_type' => 'page',
                    'title' => 'Homepage',
                    'post_status' => 'all',
                    'posts_per_page' => 1,
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'orderby' => 'post_date ID',
                    'order' => 'ASC',
                )
            );

            if (!empty($front_page->post)) {
                $front_page_id = $front_page->post->ID;
            }

            $blog_page = new WP_Query(
                array(
                    'post_type' => 'page',
                    'title' => 'Blog',
                    'post_status' => 'all',
                    'posts_per_page' => 1,
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'update_post_term_cache' => false,
                    'update_post_meta_cache' => false,
                    'orderby' => 'post_date ID',
                    'order' => 'ASC',
                )
            );

            if (!empty($blog_page->post)) {
                $blog_page_id = $blog_page->post->ID;
            }

            if ($front_page_id && $blog_page_id) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $front_page_id);
                update_option('page_for_posts', $blog_page_id);
            }

            // Assign navigation menu locations.
            $menu_location_details = array(
                'top-menu',
                'primary-menu',
                'social-menu',
                'footer-menu',
            );

            if (!empty($menu_location_details)) {
                $navigation_settings = array();
                $current_navigation_menus = wp_get_nav_menus();
                if (!empty($current_navigation_menus) && !is_wp_error($current_navigation_menus)) {
                    foreach ($current_navigation_menus as $menu) {
                        if (in_array($menu->slug, $menu_location_details)) {
                            $navigation_settings[$menu->slug] = $menu->term_id;
                        }
                    }
                }
                set_theme_mod('nav_menu_locations', $navigation_settings);
            }
        }

        /**
         * Render Site templates
         *
         * @since 1.0.0
         */
        public function wpinterface_add_ons_display_templates()
        {
            if ($this->config_data && isset($this->config_data['import_files'])) {
                $free_demos = $this->config_data['import_files']['free'];
                $pro_demos = $this->config_data['import_files']['pro'];
                ?>
                <div class="wpi-import-section">
                    <div class="wpi-import-header">
                        <h2 class="wpi-import-title"><?php esc_html_e('Starter Templates', 'wpinterface-add-ons'); ?></h2>
                        <p class="wpi-import-description">
                            <?php esc_html_e('Get access to carefully crafted professional and visually appealing website templates, saving you valuable time and effort in the development process.', 'wpinterface-add-ons'); ?>
                        </p>
                    </div>
                    <div class="wpi-import-content">
                        <?php
                        foreach ($free_demos as $index => $demo_data) {
                            $file_url = $this->base_url . $this->current_template . '/' . $demo_data['import_path'] . '/';
                            $import_url = add_query_arg(
                                array(
                                    'page' => $this->current_theme . '-demo-import',
                                    'step' => 'import',
                                    'import' => esc_attr($index),
                                ),
                                admin_url('themes.php')
                            );
                            ?>
                            <div class="wpi-import-panel import-panel-free">
                                <div class="import-panel-image">
                                    <img class="import-attachment-image" src="<?php echo esc_url($file_url . 'screenshot.webp'); ?>">
                                </div>
                                <div class="import-panel-details">
                                    <h4 class="import-panel-title" title="<?php echo esc_attr($demo_data['import_name']); ?>">
                                        <?php echo esc_html($demo_data['import_name']); ?>
                                    </h4>
                                    <div class="import-button-group">
                                        <a class="import-button button button-secondary" href="<?php echo esc_url($demo_data['preview_url']); ?>" target="_blank"><?php esc_html_e('Preview Demo', 'wpinterface-add-ons'); ?></a>
                                        <a class="import-button button button-primary" href="<?php echo esc_url($import_url); ?>"><?php esc_html_e('Import Demo', 'wpinterface-add-ons'); ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        foreach ($pro_demos as $index => $demo_data) {
                            $file_url = $this->base_url . $this->current_theme . '/' . $demo_data['import_path'] . '/';
                            ?>
                            <div class="wpi-import-panel import-panel-premium">
                                <span class="import-badge-premium"><?php esc_html_e('Premium', 'wpinterface-add-ons'); ?></span>
                                <div class="import-panel-image">
                                    <img class="import-attachment-image"
                                         src="<?php echo esc_url($file_url . 'screenshot.webp'); ?>">
                                </div>
                                <div class="import-panel-details">
                                    <h4 class="import-panel-title"
                                        title="<?php echo esc_attr($demo_data['import_name']); ?>"><?php echo esc_html($demo_data['import_name']); ?></h4>
                                    <span class="import-button-group">
											<a class="import-button button button-secondary" href="<?php echo esc_url($demo_data['preview_url']); ?>" target="_blank"><?php esc_html_e('Preview Demo', 'wpinterface-add-ons'); ?></a>
											<a class="import-button button button-primary" href="<?php echo esc_url($this->theme_url . '/?utm_source=wp&utm_medium=theme-dashboard&utm_campaign=templates'); ?>" target="_blank"><?php esc_html_e('Upgrade Now', 'wpinterface-add-ons'); ?></a>
										</span>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                </div>
                <?php
            }
        }

        /**
         * Enqueue admin scripts and styles
         *
         * @since 1.0.0
         */
        public function wpinterface_add_ons_enqueue_scripts_and_styles()
        {
            wp_enqueue_style('wpinterface-add-on-admin', WPINTEERFACE_IC_URL . 'admin/assets/css/wpinterface-add-on-admin.css', array(), '1.0.0', 'all');
        }
    }

endif;

/**
 *  Prepare if class 'Wpinterface_Add_Ons_Demo_Import_Companion' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Wpinterface_Add_Ons_Demo_Import_Companion::get_instance();