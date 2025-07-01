<?php

class Fupi {
    // public $fupi_versions;
    protected $version;

    protected $loader;

    protected $tools;

    protected $main;

    protected $modules;

    protected $plugin_name;

    public function __construct() {
        if ( defined( 'FUPI_VERSION' ) ) {
            $this->version = FUPI_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'full_picture';
        $this->tools = get_option( 'fupi_tools' );
        $this->main = get_option( 'fupi_main' );
        // $this->fupi_versions = get_option('fupi_versions');
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    private function load_dependencies() {
        // MODULES DATA
        require_once FUPI_PATH . '/includes/fupi_modules_data.php';
        $this->modules = $fupi_modules;
        // STANDARD FUPI
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fupi-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fupi-i18n.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fupi-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fupi-public.php';
        $this->loader = new Fupi_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new Fupi_i18n();
        $this->loader->add_action( 'init', $plugin_i18n, 'fupi_load_plugin_textdomain' );
        $this->loader->add_filter(
            'load_textdomain_mofile',
            $plugin_i18n,
            'fupi_load_textdomain_mofile',
            10,
            2
        );
    }

    //
    // ADMIN HOOKS
    //
    private function define_admin_hooks() {
        $plugin_admin = new Fupi_Admin($this->get_plugin_name(), $this->get_version());
        // Perform updates
        $this->loader->add_action( 'init', $plugin_admin, 'perform_updates' );
        // LOAD MODULES
        foreach ( $this->modules as $module ) {
            if ( $module['type'] != 'settings' && empty( $this->tools[$module['id']] ) ) {
                continue;
            }
            if ( is_customize_preview() && empty( $module['load_in_customizer'] ) ) {
                continue;
            }
            switch ( $module['id'] ) {
                default:
                    $plugin_admin->load_module( $module['id'], $module['is_premium'] );
                    break;
            }
        }
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'fupi_enqueue_scripts' );
        $this->loader->add_action( 'admin_head', $plugin_admin, 'fupi_custom_admin_styles' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'fupi_add_admin_page_links' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'fupi_add_stats_reports_pages' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'fupi_register_settings' );
        // $this->loader->add_action( 'admin_init', $plugin_admin, 'fupi_activation_redirect' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'fupi_admin_notices' );
        // AJAX USER SEARCH (for the settings field)
        $this->loader->add_action( 'wp_ajax_fupi_search_users', $plugin_admin, 'fupi_search_users_callback' );
        $this->loader->add_action( 'wp_ajax_fupi_search_pages', $plugin_admin, 'fupi_search_pages_callback' );
    }

    //
    // PUBLIC HOOKS
    //
    private function define_public_hooks() {
        if ( empty( $this->tools ) ) {
            return;
        }
        $plugin_public = new Fupi_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action(
            'wp_head',
            $plugin_public,
            'fupi_output_fupi_data_in_head',
            -1
        );
        // includes all the "Get" scripts
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'fupi_enqueue_js_helpers' );
        // LOAD MODULES
        $gtools_loaded = false;
        $html_mods_loaded = false;
        if ( !empty( $this->tools ) ) {
            foreach ( $this->modules as $module ) {
                if ( empty( $this->tools[$module['id']] ) || $module['type'] == 'settings' ) {
                    continue;
                }
                if ( is_customize_preview() && empty( $module['load_in_customizer'] ) ) {
                    continue;
                }
                switch ( $module['id'] ) {
                    case 'ga41':
                    case 'ga42':
                    case 'gads':
                        if ( !$gtools_loaded ) {
                            $gtools_loaded = true;
                            $plugin_public->load_module( 'gtools', false );
                        }
                        break;
                    case 'iframeblock':
                    case 'blockscr':
                    case 'safefonts':
                        if ( !$html_mods_loaded ) {
                            $html_mods_loaded = true;
                            $plugin_public->load_module( 'htmlmods', false );
                        }
                        break;
                    case 'gtm':
                        $plugin_public->load_module( 'gotm', false );
                        break;
                    default:
                        $plugin_public->load_module( $module['id'], $module['is_premium'] );
                        break;
                }
            }
        }
        // SERVER OPERATIONS
        if ( !empty( $this->main['server_method'] ) && $this->main['server_method'] == 'ajax' ) {
            // Add AJAX CB
            $this->loader->add_action( 'wp_ajax_nopriv_fupi_ajax', $plugin_public, 'fupi_ajax_hooks' );
            // AJAX for non-logged-in users
        } else {
            // Add a Rest API endpoint
            $this->loader->add_action( 'rest_api_init', $plugin_public, 'fupi_rest_hooks' );
            // REST API
        }
    }

    public function run() {
        $this->loader->run();
    }

}
