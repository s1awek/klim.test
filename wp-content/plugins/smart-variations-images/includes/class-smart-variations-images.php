<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.rosendo.pt
 * @since      1.0.0
 *
 * @package    Smart_Variations_Images
 * @subpackage Smart_Variations_Images/includes
 */
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Smart_Variations_Images
 * @subpackage Smart_Variations_Images/includes
 * @author     David Rosendo <david@rosendo.pt>
 */
class Smart_Variations_Images {
    /**
     * The loader responsible for maintaining and registering all hooks that power the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Smart_Variations_Images_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * The WordPress Settings Framework instance for managing plugin settings.
     *
     * @since    1.0.0
     * @access   private
     * @var      WordPressSettingsFramework $wpsf The settings framework instance.
     */
    private $wpsf;

    /**
     * The single instance of the class.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Smart_Variations_Images|null $instance The singleton instance of the class.
     */
    protected static $instance = null;

    /**
     * The plugin options.
     *
     * @since    1.0.0
     * @access   public
     * @var      stdClass|null $options The current plugin options.
     */
    public $options = null;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->version = SMART_VARIATIONS_IMAGES_VERSION;
        $this->plugin_name = 'smart-variations-images';
        $this->load_dependencies();
        $this->options = $this->run_reduxMigration();
        $this->options->rtl = is_rtl();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Convenience wrapper to check if a given option is enabled.
     *
     * @since 5.2.20
     * @param string $option  Option property to inspect.
     * @param bool   $default Optional fallback if the property does not exist.
     * @return bool
     */
    private function is_option_enabled( string $option, bool $default = false ) : bool {
        if ( !is_object( $this->options ) ) {
            return $default;
        }
        if ( !property_exists( $this->options, $option ) ) {
            return $default;
        }
        return (bool) $this->options->{$option};
    }

    /**
     * Get the main instance of the plugin.
     *
     * Ensures only one instance of the class is loaded or can be loaded (Singleton pattern).
     *
     * @since    1.0.0
     * @return   Smart_Variations_Images The single instance of the class.
     */
    public static function instance() : Smart_Variations_Images {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     * - Smart_Variations_Images_Loader: Orchestrates the hooks of the plugin.
     * - Smart_Variations_Images_i18n: Defines internationalization functionality.
     * - Smart_Variations_Images_Admin: Defines all hooks for the admin area.
     * - Smart_Variations_Images_Public: Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() : void {
        /**
         * The class responsible for adding the options core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/wp-settings-framework/wp-settings-framework.php';
        $this->wpsf = new WordPressSettingsFrameworkSVI(plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/wp-settings-framework/settings/svi-settings.php', 'woosvi_options');
        // Add admin menu
        add_action( 'admin_menu', [$this, 'add_settings_page'], 20 );
        add_filter( $this->wpsf->get_option_group() . '_settings_validate', [$this, 'validate_settings'] );
        /**
         * The class responsible for orchestrating the actions and filters of the core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smart-variations-images-loader.php';
        /**
         * The class responsible for defining internationalization functionality of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smart-variations-images-i18n.php';
        /**
         * The classes responsible for rendering HTML tags efficiently.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/php-html-generator/Markup.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/library/php-html-generator/HtmlTag.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-smart-variations-images-admin.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-smart-variations-images-public.php';
        $this->loader = new Smart_Variations_Images_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Smart_Variations_Images_i18n class to set the domain and register the hook with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() : void {
        $plugin_i18n = new Smart_Variations_Images_i18n();
        $plugin_i18n->load_plugin_textdomain();
    }

    /**
     * Register all hooks related to the admin area functionality of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() : void {
        $plugin_admin = new Smart_Variations_Images_Admin($this->get_plugin_name(), $this->get_version(), $this->options);
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action(
            'woocommerce_variation_options',
            $plugin_admin,
            'variation_btn_builder',
            10,
            3
        );
        $this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'images_section' );
        $panels = ( $this->version_check() ? 'woocommerce_product_write_panels' : 'woocommerce_product_data_panels' );
        $this->loader->add_action( $panels, $plugin_admin, 'images_settings' );
        $this->loader->add_action( 'wp_ajax_woosvi_esc_html', $plugin_admin, 'woosvi_esc_html' );
        $this->loader->add_action( 'wp_ajax_woosvi_reloadselect', $plugin_admin, 'reloadSelect_json' );
        $this->loader->add_action(
            'woocommerce_product_options_advanced',
            $plugin_admin,
            'sviDisableProduct_advancedTab',
            10,
            0
        );
        $this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'sviSaveData' );
        if ( svi_fs()->is_free_plan() ) {
            $this->loader->add_filter( 'woocommerce_product_import_process_item_data', $plugin_admin, 'wc_ignore_svimeta_in_import' );
        }
        $this->loader->add_filter(
            'woocommerce_product_export_meta_value',
            $plugin_admin,
            'woo_handle_export',
            10,
            4
        );
    }

    /**
     * Register premium-only admin hooks.
     *
     * @since 5.2.20
     * @param Smart_Variations_Images_Admin $plugin_admin Admin handler instance.
     */
    private function register_premium_admin_hooks( Smart_Variations_Images_Admin $plugin_admin ) : void {
        $this->loader->add_action(
            'woocommerce_product_import_inserted_product_object',
            $plugin_admin,
            'handleImports__premium_only',
            10,
            2
        );
        if ( $this->is_option_enabled( 'sviemailadmin' ) ) {
            $this->loader->add_filter(
                'woocommerce_admin_order_item_thumbnail',
                $plugin_admin,
                'filter_woocommerce_admin_order_item_thumbnail__premium_only',
                10,
                3
            );
        }
    }

    /**
     * Register all hooks related to the public-facing functionality of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() : void {
        if ( !property_exists( $this->options, 'default' ) || !$this->options->default ) {
            return;
        }
        $plugin_public = new Smart_Variations_Images_Public($this->get_plugin_name(), $this->get_version(), $this->options);
        if ( $this->is_option_enabled( 'placeholder' ) ) {
            $this->options->placeholder_img = $plugin_public->imgtagger( wc_placeholder_img( $this->options->main_imagesize ) );
        }
        $this->loader->add_action(
            'wp_enqueue_scripts',
            $plugin_public,
            'load_scripts',
            99999
        );
        $this->loader->add_filter(
            'wc_get_template',
            $plugin_public,
            'filter_wc_get_template',
            1,
            5
        );
        $this->loader->add_action(
            'after_setup_theme',
            $plugin_public,
            'after_setup_theme',
            20
        );
        $this->loader->add_action(
            'woocommerce_before_single_product',
            $plugin_public,
            'remove_hooks',
            20
        );
        $this->loader->add_action(
            'woocommerce_before_single_product_summary',
            $plugin_public,
            'render_frontend',
            20
        );
        // Divi Builder compatibility - replace Divi's WooCommerce Images module with SVI
        $this->loader->add_filter(
            'et_module_shortcode_output',
            $plugin_public,
            'replace_divi_module_output',
            10,
            3
        );
        add_shortcode( 'svi_wcsc', [$plugin_public, 'render_sc_frontend'] );
        if ( $this->is_option_enabled( 'variation_thumbnails' ) ) {
            $this->loader->add_action(
                'woocommerce_single_variation',
                $plugin_public,
                'render_before_add_to_cart_button',
                5
            );
        }
        $this->register_loop_showcase_hooks( $plugin_public );
        if ( $this->is_option_enabled( 'filter_attribute' ) ) {
            $this->loader->add_filter(
                'woocommerce_product_get_image',
                $plugin_public,
                'filter_main_product_image',
                10,
                5
            );
        }
        $this->loader->add_action( 'wp_ajax_woosvi_slugify', $plugin_public, 'woosvi_slugify' );
        $this->loader->add_action( 'wp_ajax_loadProduct', $plugin_public, 'render_quick_view_frontend' );
        $this->loader->add_action( 'wp_ajax_nopriv_loadProduct', $plugin_public, 'render_quick_view_frontend' );
        $this->loader->add_action( 'svi_before_images', $plugin_public, 'run_integrations' );
    }

    /**
     * Register loop showcase hooks depending on plan/option availability.
     *
     * @since 5.2.20
     * @param Smart_Variations_Images_Public $plugin_public Public handler instance.
     */
    private function register_loop_showcase_hooks( Smart_Variations_Images_Public $plugin_public ) : void {
        if ( !$this->is_option_enabled( 'loop_showcase' ) ) {
            return;
        }
        $this->loader->add_action(
            'woocommerce_before_shop_loop_item_title',
            $plugin_public,
            'svi_product_tn_images',
            10
        );
    }

    /**
     * Register premium-only public hooks.
     *
     * @since 5.2.20
     * @param Smart_Variations_Images_Public $plugin_public Public handler instance.
     */
    private function register_premium_public_hooks( Smart_Variations_Images_Public $plugin_public ) : void {
        if ( $this->is_option_enabled( 'svicart' ) ) {
            $this->loader->add_filter(
                'woocommerce_cart_item_thumbnail',
                $plugin_public,
                'filter_woocommerce_cart_item_thumbnail__premium_only',
                150,
                3
            );
            $this->loader->add_filter(
                'woocommerce_store_api_cart_item_images',
                $plugin_public,
                'filter_woocommerce_store_api_cart_item_images__premium_only',
                10,
                3
            );
        }
        if ( $this->is_option_enabled( 'sviemail' ) ) {
            $this->loader->add_filter(
                'woocommerce_email_order_items_args',
                $plugin_public,
                'filter_add_images_woocommerce_emails__premium_only',
                100,
                1
            );
            $this->loader->add_filter(
                'woocommerce_order_item_thumbnail',
                $plugin_public,
                'filter_woocommerce_order_item_email_thumbnail__premium_only',
                10,
                2
            );
        }
        if ( $this->is_option_enabled( 'order_details' ) ) {
            $this->loader->add_filter(
                'woocommerce_order_item_name',
                $plugin_public,
                'filter_woocommerce_order_item_thumbnail__premium_only',
                10,
                2
            );
        }
        if ( $this->is_option_enabled( 'quick_view' ) ) {
            remove_action( 'yith_wcqv_product_image', 'woocommerce_show_product_images', 20 );
            $this->loader->add_action(
                'yith_wcqv_product_image',
                $plugin_public,
                'render_frontend',
                20
            );
            $this->loader->add_action(
                'woocommerce_before_single_product_lightbox_summary',
                $plugin_public,
                'render_frontend',
                20
            );
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() : void {
        $this->loader->run();
    }

    /**
     * Get the name of the plugin used to uniquely identify it within the context of WordPress
     * and to define internationalization functionality.
     *
     * @since    1.0.0
     * @return   string The name of the plugin.
     */
    public function get_plugin_name() : string {
        return $this->plugin_name;
    }

    /**
     * Get the reference to the class that orchestrates the hooks with the plugin.
     *
     * @since    1.0.0
     * @return   Smart_Variations_Images_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader() : Smart_Variations_Images_Loader {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since    1.0.0
     * @return   string The version number of the plugin.
     */
    public function get_version() : string {
        return $this->version;
    }

    /**
     * Check WooCommerce version.
     *
     * @since    1.0.0
     * @param    string $version The version to compare against (default: '3.0').
     * @return   bool True if the WooCommerce version is less than or equal to the specified version, false otherwise.
     */
    public function version_check( string $version = '3.0' ) : bool {
        if ( class_exists( 'WooCommerce' ) ) {
            global $woocommerce;
            if ( version_compare( $woocommerce->version, $version, '<=' ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add the settings page to the admin menu.
     *
     * @since    1.0.0
     */
    public function add_settings_page() : void {
        $this->wpsf->add_settings_page( [
            'parent_slug' => 'woocommerce',
            'page_title'  => esc_html__( 'Smart Variations Images & Swatches for WooCommerce', 'text-domain' ),
            'menu_title'  => esc_html__( 'SVI', 'text-domain' ),
            'capability'  => 'edit_products',
            'page_slug'   => 'woocommerce_svi',
        ] );
    }

    /**
     * Migrate settings from Redux Framework to WordPress Settings Framework.
     *
     * @since    1.0.0
     * @return   stdClass The migrated plugin options.
     */
    public function run_reduxMigration() : stdClass {
        $redux = get_option( 'woosvi_options' );
        $redux_imported = get_option( 'woosvi_options_settings_imported', false );
        if ( $redux && !$redux_imported ) {
            $wpsfsvi = $this->wpsf->get_settings();
            foreach ( $wpsfsvi as $key => $new ) {
                foreach ( $redux as $rx_key => $old ) {
                    if ( $this->str_ends_with( $key, $rx_key ) ) {
                        if ( is_array( $old ) ) {
                            $wpsfsvi[$key] = array_keys( array_filter( $old ) );
                        } else {
                            $wpsfsvi[$key] = $old;
                        }
                    }
                }
            }
            update_option( 'woosvi_options_settings_imported', true );
            update_option( 'woosvi_options_settings', $wpsfsvi );
        }
        return (object) $this->wpsf->get_settings( true );
    }

    /**
     * Check if a string ends with a specific substring.
     *
     * @since    1.0.0
     * @param    string $string The string to check.
     * @param    string $endString The substring to look for at the end of the string.
     * @return   bool True if the string ends with the specified substring, false otherwise.
     */
    public function str_ends_with( string $string, string $endString ) : bool {
        $len = strlen( $endString );
        if ( $len === 0 ) {
            return true;
        }
        return substr( $string, -$len ) === $endString;
    }

    /**
     * Validate settings before saving.
     *
     * @since    1.0.0
     * @param    array $input The input settings to validate.
     * @return   array The validated settings.
     */
    public function validate_settings( array $input ) : array {
        // Validation logic can be added here if needed
        return $input;
    }

}
