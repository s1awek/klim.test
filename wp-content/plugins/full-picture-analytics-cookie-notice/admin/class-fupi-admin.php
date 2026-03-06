<?php

class Fupi_Admin {
    private $plugin_name;

    private $version;

    private $versions;

    private $tools;

    private $proofrec;

    private $main;

    private $cook_enabled;

    private $cook;

    private $user_cap;

    private $is_woo_enabled;

    private $reports;

    // private $sync_run;
    private $fupi_report_pages = [];

    private $modules = [];

    private $fupi_modules = [];

    private $ver;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->versions = get_option( 'fupi_versions' );
        $this->tools = get_option( 'fupi_tools' );
        $this->ver = get_option( 'fupi_versions' );
        // Enable GTAG module if google ads or analytics are enabled
        if ( !empty( $this->tools['ga41'] ) || !empty( $this->tools['gads'] ) ) {
            $this->tools['gtag'] = true;
        }
        // add gtag to tools if ga or gads are active
        $this->reports = get_option( 'fupi_reports' );
        $this->main = get_option( 'fupi_main' );
        $this->proofrec = get_option( 'fupi_proofrec' );
        $this->cook_enabled = !empty( $this->tools ) && isset( $this->tools['cook'] );
        $this->cook = get_option( 'fupi_cook' );
        $this->user_cap = 'manage_options';
        $this->is_woo_enabled = false;
        $this->get_modules_data();
        // Test to see if WooCommerce is active (including network activated).
        // https://woocommerce.com/document/create-a-plugin/#section-1
        if ( isset( $this->tools['woo'] ) ) {
            $plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';
            if ( function_exists( 'wp_get_active_and_valid_plugins' ) && in_array( $plugin_path, wp_get_active_and_valid_plugins() ) || function_exists( 'wp_get_active_network_plugins' ) && in_array( $plugin_path, wp_get_active_network_plugins() ) ) {
                $this->is_woo_enabled = true;
            }
        }
    }

    public function get_modules_data() {
        include FUPI_PATH . '/includes/fupi_modules_data.php';
        $this->fupi_modules = $fupi_modules;
        // ( Do not add here any filters or actions - it's too early and they will not trigger at all )
    }

    public function load_module( $moduleName, $is_premium = false ) {
        if ( $is_premium && !fupi_fs()->can_use_premium_code() ) {
            return;
        }
        // do not load premium modules
        // do not load a module that is already loaded
        $moduleClass = 'Fupi_' . strtoupper( $moduleName ) . '_admin';
        if ( class_exists( $moduleClass ) ) {
            trigger_error( "Module {$moduleName} is already loaded.", E_USER_WARNING );
            return;
        }
        // load file
        if ( $is_premium ) {
            $modulePath = FUPI_PATH . "/admin/modules/{$moduleName}__premium_only/{$moduleName}-admin.php";
        } else {
            $modulePath = FUPI_PATH . "/admin/modules/{$moduleName}/{$moduleName}-admin.php";
        }
        if ( !file_exists( $modulePath ) ) {
            return;
        }
        require_once $modulePath;
        // return if the loaded file has no necessary class
        if ( !class_exists( $moduleClass ) ) {
            return;
        }
        // // Check if this module has dependencies
        // if (method_exists($moduleClass, 'getDependencies')) {
        //     $dependencies = $moduleClass::getDependencies();
        //     foreach ($dependencies as $dependency) {
        //         if (!isset($this->modules[$dependency])) {
        //             $this->loadModule($dependency);
        //         }
        //     }
        // }
        // Add the module to the main class
        new $moduleClass();
        // $this->modules[$moduleName] = new $moduleClass(); // you can pass $this here or any other vars if needed. Passing $this will let the module access the main class and all of its methods and properties.
    }

    // public function __call($method, $args) {
    //     foreach ($this->modules as $module) {
    //         if (method_exists($module, $method)) {
    //             return call_user_func_array([$module, $method], $args);
    //         }
    //     }
    //     throw new Exception("Method {$method} not found.");
    // }
    //
    // ADD NECESSARY SCRIPTS
    //
    public function fupi_enqueue_scripts( $hook ) {
        $req = array();
        // everything that is not customizer
        if ( !is_customize_preview() ) {
            wp_enqueue_script(
                'fupi-whole_admin-js',
                plugin_dir_url( __FILE__ ) . 'assets/js/fupi-whole-admin.js',
                array(),
                $this->version,
                true
            );
            wp_register_style(
                'fupi-select2-css',
                plugin_dir_url( __FILE__ ) . 'assets/css/select2.min.css',
                array(),
                '4.1.0-rc.0'
            );
            wp_register_script(
                'fupi-select2-js',
                plugin_dir_url( __FILE__ ) . 'assets/js/select2.min.js',
                array('jquery', 'fupi-admin-helpers-js'),
                '4.1.0-rc.0'
            );
        }
        // SETTINGS PAGE
        if ( strrpos( $hook, 'full_picture_' ) !== false ) {
            // for top level page use "toplevel_page_fupi"
            // Add WP Internal Pointers...
            // https://stackoverflow.com/questions/30945793/how-do-you-create-a-basic-wordpress-admin-pointer
            // wp_enqueue_style( 'wp-pointer' );
            // wp_enqueue_script( 'wp-pointer' );
            array_push( $req, 'jquery', 'fupi-admin-helpers-js' );
            wp_enqueue_style(
                'fupi-admin',
                plugin_dir_url( __FILE__ ) . 'assets/css/fupi-admin.min.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_script(
                'fupi-admin-helpers-js',
                plugin_dir_url( __FILE__ ) . 'assets/js/fupi-admin-helpers.js',
                array(),
                $this->version,
                true
            );
            wp_enqueue_script(
                'fupi-admin-js',
                plugin_dir_url( __FILE__ ) . 'assets/js/fupi-admin.js',
                $req,
                $this->version,
                true
            );
            if ( strrpos( $hook, 'full_picture_main' ) !== false ) {
                wp_enqueue_script(
                    'fupi-admin-import-export-js',
                    plugin_dir_url( __FILE__ ) . 'assets/js/fupi-admin-import-export.js',
                    $req,
                    $this->version,
                    true
                );
            }
            // Enqueue UTM parameter variables
            $fupi_version = str_replace( '.', '_', $this->version );
            $fupi_licence = ( fupi_fs()->can_use_premium_code() ? 'pro' : 'free' );
            wp_add_inline_script( 'fupi-admin-helpers-js', "\r\n\t\t\t\twindow.fupi_version = '{$fupi_version}';\r\n\t\t\t\twindow.fupi_licence = '{$fupi_licence}';\r\n\t\t\t", 'before' );
            // Localize script for conflict checker
            wp_localize_script( 'fupi-admin-js', 'fupi_conflicts_data', array(
                'nonce'    => wp_create_nonce( 'fupi_check_conflicts_nonce' ),
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'i18n'     => array(
                    'checking'       => esc_html__( 'Checking...', 'full-picture-analytics-cookie-notice' ),
                    'error_occurred' => esc_html__( 'An error occurred while checking for conflicts. Please try again.', 'full-picture-analytics-cookie-notice' ),
                    'no_conflicts'   => esc_html__( 'No conflicts detected! Your setup looks good.', 'full-picture-analytics-cookie-notice' ),
                    'error_generic'  => esc_html__( 'An error occurred while checking for conflicts.', 'full-picture-analytics-cookie-notice' ),
                ),
            ) );
        }
        // REPORTS PAGE
        // this cannot be called in the reports-admin since reports page is also shown when plausible stats are enabled
        if ( strrpos( $hook, 'fp_reports' ) !== false ) {
            wp_enqueue_style(
                'fupi-admin-reports',
                plugin_dir_url( __FILE__ ) . 'common/pages/fupi_reports.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_script(
                'fupi-admin-helpers-js',
                plugin_dir_url( __FILE__ ) . 'assets/js/fupi-admin-helpers.js',
                array(),
                $this->version,
                true
            );
        }
    }

    public function fupi_custom_admin_styles() {
        echo '<style>
			.column-fupi_order_data{
				width: 30px !important;
				max-width: 30px !important;
				box-sizing: border-box;
				text-align: center;
			}
		</style>';
    }

    //
    // ADD PAGE LINKS TO ADMIN MENU
    //
    public function fupi_add_admin_page_links() {
        include FUPI_PATH . '/includes/fupi_modules_names.php';
        // Main menu item text
        $fupi_page_title = ( !empty( $this->main ) && isset( $this->main['custom_menu_title'] ) ? esc_attr( $this->main['custom_menu_title'] ) : "WP Full Picture" );
        $show_main = false;
        // MAIN PAGE - now points to Home
        add_menu_page(
            'WP Full Picture',
            // page title
            $fupi_page_title,
            // menu title
            $this->user_cap,
            // capability
            'full_picture_home',
            // menu slug - changed to home
            array($this, 'fupi_display_home_page'),
            // changed to home display method
            'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDU5NSA2MzciIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM6c2VyaWY9Imh0dHA6Ly93d3cuc2VyaWYuY29tLyIgc3R5bGU9ImZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDoyOyI+CiAgICA8ZyB0cmFuc2Zvcm09Im1hdHJpeCgyLjk5NTM2LDAsMCwyLjk5NTM2LC01NTcuMzQ0LC04NjYuNTA0KSI+CiAgICAgICAgPHBhdGggZD0iTTM2My43ODQsNDk4LjQ5MkwzMzkuOTI5LDQ3NC42NzJDMzM5LjMzNCw0NzQuMDcgMzM4LjgxMSw0NzMuNDMyIDMzOC4zODgsNDcyLjc1MkMzMjMuMDYsNDgyLjQxOCAzMDQuOTAyLDQ4OC4wMjIgMjg1LjQzOSw0ODguMDIyQzIzMC41NzcsNDg4LjAyMiAxODYuMDQ4LDQ0My41IDE4Ni4wNjksMzg4LjY2NkMxODYuMDU1LDMzMy43OTcgMjMwLjU4NCwyODkuMjgyIDI4NS40MjUsMjg5LjI4MkMzNDAuMjgsMjg5LjI3NSAzODQuODA5LDMzMy43OTcgMzg0LjgwMiwzODguNjY2QzM4NC44MDksNDE1Ljg0IDM3My44ODEsNDQwLjQ3NiAzNTYuMTc0LDQ1OC40MzRMMzgwLjAzNyw0ODIuMjU0QzM4NC40ODcsNDg2Ljc1NCAzODQuNTA4LDQ5NC4wMiAzODAuMDIyLDQ5OC40OTJDMzc1LjU0NCw1MDIuOTkyIDM2OC4yNjMsNTAyLjk5MiAzNjMuNzg0LDQ5OC40OTJaTTM2NC4wMzUsMzg4LjY1MkMzNjQuMDM1LDM0NS43NjQgMzI5LjI0NCwzMTAuOTk1IDI4Ni4zODUsMzEwLjk5NUMyNDMuNTExLDMxMC45OTUgMjA4LjcxMywzNDUuNzcxIDIwOC43MTMsMzg4LjY2NkMyMDguNzEzLDQzMS41MjYgMjQzLjUwNCw0NjYuMzE3IDI4Ni4zNjMsNDY2LjMxN0MzMjkuMjQ0LDQ2Ni4zMTcgMzY0LjA0Miw0MzEuNTI2IDM2NC4wMzUsMzg4LjY1MloiLz4KICAgIDwvZz4KICAgIDxnIHRyYW5zZm9ybT0ibWF0cml4KDIuOTk1MzYsMCwwLDIuOTk1MzYsLTU1Ny4zNDQsLTc1Ni4yOTYpIj4KICAgICAgICA8cGF0aCBkPSJNMjM2Ljc5NywzNjkuNDk3TDIzNi43OTcsMzg1Ljk3N0MyMzYuNzk3LDM5NC42NjUgMjQxLjk4OCw0MDEuNzE5IDI0OC4zODEsNDAxLjcxOUwyNDguMzg0LDQwMS43MTlDMjU0Ljc3Nyw0MDEuNzE5IDI1OS45NjgsMzk0LjY2NSAyNTkuOTY4LDM4NS45NzdMMjU5Ljk2OCwzNjkuNDk3QzI1OS45NjgsMzYwLjgwOSAyNTQuNzc3LDM1My43NTUgMjQ4LjM4NCwzNTMuNzU1TDI0OC4zODEsMzUzLjc1NUMyNDEuOTg4LDM1My43NTUgMjM2Ljc5NywzNjAuODA5IDIzNi43OTcsMzY5LjQ5N1oiLz4KICAgIDwvZz4KICAgIDxnIHRyYW5zZm9ybT0ibWF0cml4KDIuOTk1MzYsMCwwLDIuOTk1MzYsLTU1Ny4zNDQsLTgyMy4zNSkiPgogICAgICAgIDxwYXRoIGQ9Ik0yNzMuODI3LDM2OS41MDJMMjczLjgyNyw0MDguMzU4QzI3My44MjcsNDE3LjA0NiAyNzkuMDE4LDQyNC4xIDI4NS40MTQsNDI0LjFMMjg1LjQxOCw0MjQuMUMyOTEuODE0LDQyNC4xIDI5Ny4wMDUsNDE3LjA0NiAyOTcuMDA1LDQwOC4zNThMMjk3LjAwNSwzNjkuNTAyQzI5Ny4wMDUsMzYwLjgxNCAyOTEuODE0LDM1My43NiAyODUuNDE4LDM1My43NkwyODUuNDE0LDM1My43NkMyNzkuMDE4LDM1My43NiAyNzMuODI3LDM2MC44MTQgMjczLjgyNywzNjkuNTAyWiIvPgogICAgPC9nPgogICAgPGcgdHJhbnNmb3JtPSJtYXRyaXgoMi45OTUzNiwwLDAsMi45OTUzNiwtNTU3LjM0NCwtODk5LjkzOCkiPgogICAgICAgIDxwYXRoIGQ9Ik0zMTAuODg1LDM2OS41MDFMMzEwLjg4NSw0MzMuOTI4QzMxMC44ODUsNDQyLjYxNiAzMTYuMDc3LDQ0OS42NyAzMjIuNDY5LDQ0OS42N0wzMjIuNDczLDQ0OS42N0MzMjguODY1LDQ0OS42NyAzMzQuMDU3LDQ0Mi42MTYgMzM0LjA1Nyw0MzMuOTI4TDMzNC4wNTcsMzY5LjUwMUMzMzQuMDU3LDM2MC44MTMgMzI4Ljg2NSwzNTMuNzU5IDMyMi40NzMsMzUzLjc1OUwzMjIuNDY5LDM1My43NTlDMzE2LjA3NywzNTMuNzU5IDMxMC44ODUsMzYwLjgxMyAzMTAuODg1LDM2OS41MDFaIi8+CiAgICA8L2c+Cjwvc3ZnPgo=',
            90
        );
        add_submenu_page(
            'full_picture_home',
            // parent slug - changed to home
            esc_attr__( 'Dashboard', 'full-picture-analytics-cookie-notice' ),
            // page title
            esc_attr__( 'Dashboard', 'full-picture-analytics-cookie-notice' ),
            // menu title
            $this->user_cap,
            // capability
            'full_picture_home',
            // menu slug
            array($this, 'fupi_display_home_page')
        );
        // SUBPAGES
        $modules_opts = [];
        $sections_to_show = [];
        // Filter subpages to show
        foreach ( $this->fupi_modules as $module ) {
            // STOP if module is not avail
            if ( !$module['is_avail'] || !$module['has_admin_page'] ) {
                continue;
            }
            // STOP if module is not enabled
            if ( !isset( $this->tools[$module['id']] ) && !isset( $module['always_enabled'] ) ) {
                continue;
            }
            // STOP Woo module if WooCommerce is deactivated
            if ( $module['id'] == 'woo' && empty( $this->is_woo_enabled ) ) {
                continue;
            }
            // MARK this section if it contains non-sticky links
            if ( !isset( $module['sticky_link'] ) ) {
                $sections_to_show[] = $module['type'];
            }
            array_push( $modules_opts, [$module['type'], [
                'full_picture_home',
                // parent slug - changed to home
                $fupi_modules_names[$module['id']],
                // page title
                $fupi_modules_names[$module['id']],
                // menu title
                $this->user_cap,
                // capability
                'full_picture_' . $module['id'],
                // menu slug
                array($this, 'fupi_display_admin_page'),
            ]] );
        }
        // Add modules from addons
        $addons_opts = apply_filters( 'fupi_add_page', [] );
        // !! ADDON
        $modules_opts = array_merge( $modules_opts, $addons_opts );
        // Add links to subpages
        foreach ( $modules_opts as $module_options ) {
            $module_section = $module_options[0];
            $module_data = $module_options[1];
            // SKIP this link if its section contains only "sticky" modules
            if ( !in_array( $module_section, $sections_to_show ) ) {
                continue;
            }
            // make sure that only premium users can use premium function
            if ( empty( $module_data[3] ) ) {
                $module_data[3] = ( fupi_fs()->can_use_premium_code() ? $this->user_cap : 'manage_options' );
            }
            if ( empty( $module_data[5] ) ) {
                $module_data[5] = array($this, 'fupi_display_admin_page');
            }
            add_submenu_page( ...$module_data );
        }
    }

    public function fupi_display_admin_page() {
        include_once 'common/pages/fupi-admin-page-display.php';
    }

    public function fupi_display_home_page() {
        include_once 'common/pages/fupi-home-page-display.php';
    }

    public function fupi_settings_permissions( $cap ) {
        return $this->user_cap;
    }

    //
    // GENERATE CONTENT OF SETTINGS PAGES
    //
    public function fupi_field_html( $recipe, $field_id = false, $saved_value = false ) {
        include 'common/fupi-admin-fields-html.php';
    }

    public function fupi_register_settings() {
        $active_slug = false;
        $active_page = ( isset( $_GET['page'] ) ? sanitize_html_class( $_GET['page'] ) : false );
        // find active slug
        if ( $active_slug == false && $active_page !== false && strpos( $active_page, 'full_picture_' ) === 0 ) {
            $active_slug = str_replace( 'full_picture_', '', $active_page );
        }
        // ADD addons settings
        $addons_data = apply_filters( 'fupi_register_addon', [] );
        // ! ADDON
        $all_modules_data = array_merge( $this->fupi_modules, $addons_data );
        // register all options and display the requested page
        foreach ( $all_modules_data as $module ) {
            if ( !$module['is_avail'] || !empty( $module['custom_page_content'] ) ) {
                continue;
            }
            $option_group_name = 'fupi_' . $module['id'];
            $option_arr_id = 'fupi_' . $module['id'];
            // $slug_part = empty ( $module['is_premium'] ) ? $module['id'] . '__premium_only' : $module['id'];
            do_action( 'fupi_register_setting_' . $module['id'] );
            if ( $active_slug == $module['id'] ) {
                $sections = apply_filters( 'fupi_' . $module['id'] . '_add_fields_settings', [] );
                foreach ( $sections as $section ) {
                    add_settings_section(
                        $section['section_id'],
                        esc_html( $section['section_title'] ),
                        array($this, 'fupi_sections_descriptions'),
                        $option_arr_id
                    );
                    // ! ADDON
                    $section_fields = ( isset( $section['fields'] ) ? $section['fields'] : array() );
                    // $fields = apply_filters( 'fupi_add_fields_in_section_' . $section['section_id'], $section_fields, $option_arr_id); // ! ADDON ??
                    if ( isset( $section_fields ) ) {
                        foreach ( $section_fields as $field ) {
                            add_settings_field(
                                $field['field_id'],
                                $field['label'],
                                array($this, 'fupi_field_html'),
                                $option_arr_id,
                                $section['section_id'],
                                $field
                            );
                        }
                    }
                }
            }
        }
    }

    // DESCRIPTIONS
    public function fupi_sections_descriptions( $a ) {
        $arr = explode( '_', $a['id'] );
        $tab_slug = $arr[1];
        $no_woo_descr_text = '';
        if ( !$this->is_woo_enabled ) {
            $no_woo_descr_text = '<div class="fupi_enable_woo_notice">' . esc_html__( 'Enable WooCommerce Tracking module.', 'full-picture-analytics-cookie-notice' ) . '</div>';
        }
        $addons_data = apply_filters( 'fupi_register_addon', [] );
        // ! ADDON
        $all_modules_data = array_merge( $this->fupi_modules, $addons_data );
        foreach ( $all_modules_data as $module ) {
            if ( $module['id'] == $tab_slug ) {
                $ret_val = apply_filters( 'fupi_' . $module['id'] . '_get_page_descr', $a['id'], $no_woo_descr_text );
                if ( !empty( $ret_val ) ) {
                    if ( is_array( $ret_val ) ) {
                        $ret_txt = $ret_val['content'];
                        $classes = ( empty( $ret_val['classes'] ) ? '' : $ret_val['classes'] );
                        $style = ( empty( $ret_val['style'] ) ? '' : ' style="' . $ret_val['style'] . '"' );
                    } else {
                        $ret_txt = $ret_val;
                        $classes = '';
                        $style = '';
                    }
                    echo '<div class="fupi_section_descr fupi_el ' . $classes . '" ' . $style . '>' . $ret_txt . '</div>';
                    break;
                }
            }
        }
    }

    //
    // ADD REPORTS PAGES
    //
    public function fupi_add_stats_reports_pages() {
        $current_user_id = get_current_user_id();
        $user_is_admin = current_user_can( 'manage_options' );
        $capability = ( $user_is_admin ? 'manage_options' : 'edit_posts' );
        $is_allowed_user = !empty( $this->main ) && !empty( $this->main['extra_users_2'] ) && in_array( $current_user_id, $this->main['extra_users_2'] );
        // check if current user is allowed to modify settings
        // Report from the Plausible module
        // at the moment the report is shown only to admins
        if ( isset( $this->tools['pla'] ) ) {
            $pla_opts = get_option( 'fupi_pla' );
            // Get dashboard data
            if ( !empty( $pla_opts ) && !empty( $pla_opts['shared_link_url'] ) ) {
                $show_to_current_user = false;
                if ( $user_is_admin || $is_allowed_user || $show_to_current_user ) {
                    $this->fupi_report_pages[] = array(
                        'type'   => 'module',
                        'id'     => 'module_pla',
                        'iframe' => '<iframe plausible-embed="" src="' . esc_url( $pla_opts['shared_link_url'] ) . '&embed=true&theme=light&background=transparent" scrolling="no" frameborder="0" loading="lazy"></iframe>',
                        'title'  => 'Plausible',
                        'width'  => 1200,
                        'height' => 2000,
                    );
                }
            }
        }
        // Reports from the "Analytics Dashboards" module
        if ( isset( $this->tools['reports'] ) && !empty( $this->reports ) && !empty( $this->reports['dashboards'] ) ) {
            $show_to_current_user = false;
            // check if current user is allowed to view all reports
            if ( !$user_is_admin && !empty( $this->reports['selected_users'] ) ) {
                $show_to_current_user = in_array( $current_user_id, $this->reports['selected_users'] );
            }
            // go through all dashboards
            foreach ( $this->reports['dashboards'] as $dash ) {
                if ( $user_is_admin || $is_allowed_user || $show_to_current_user ) {
                    $this->fupi_report_pages[] = $dash;
                    $show_to_current_user = false;
                }
            }
        }
        // STOP if there are no reports to show to the current user
        if ( count( $this->fupi_report_pages ) == 0 ) {
            return;
        }
        // GET menu position
        $menu_position = ( isset( $this->tools['reports'] ) && !empty( $this->reports ) && !empty( $this->reports['menu_pos'] ) ? (int) $this->reports['menu_pos'] : 10 );
        // ADD menu page
        add_menu_page(
            $this->fupi_report_pages[0]['title'],
            // page title
            esc_html__( 'Reports', 'full-picture-analytics-cookie-notice' ),
            // menu title
            $capability,
            // capability
            'fp_reports_' . $this->fupi_report_pages[0]['id'],
            // menu slug
            array($this, 'fupi_display_reports_page'),
            'dashicons-chart-pie',
            $menu_position
        );
        // ADD subpages
        foreach ( $this->fupi_report_pages as $i => $db ) {
            add_submenu_page(
                'fp_reports_' . $this->fupi_report_pages[0]['id'],
                // parent slug
                $db['title'],
                // page title
                $db['title'],
                // menu title
                $capability,
                // capability
                'fp_reports_' . $db['id'],
                // menu slug
                array($this, 'fupi_display_reports_page')
            );
        }
    }

    public function fupi_display_reports_page() {
        include_once 'common/pages/fupi-reports-page-display.php';
    }

    //
    // ADD ADMIN NOTICES
    //
    public function fupi_admin_notices() {
        // show notices only to admins
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        // load class
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fupi-notices.php';
        $fupi_notices = new \FUPI\FUPI_Notices();
        // USED IDS:
        // !! MUST BE SMALL CAPS
        // fupi_fth_nosupport_notice
        // fupi_fth_delete_notice
        // fupi_pla_featureremoval_notice
        // fupi_update_to_2-0-0_notice
        // fupi_update_to_2-4-0_notice
        // fupi_updated_to_3-0-0_notice
        // fupi_author_ids
        // fupi_custom_notice_removal
        // fupi_fresh_install_tutorials
        // fupi_autoupdate_reminder3
        // fupi_remind_to_move_to_ga4
        // fupi_changes_in_free
        // fupi_gtm_v1_deprecation
        // fupi_review_14_days
        // fupi_gtm_v2_deprecation
        // fupi_gtm_v2_deprecation_2
        // fupi_uses_oceanwp_theme
        // fupi_bf_2025
        // if ( is_multisite() ){
        // 	$plugins_page_url = network_home_url() . 'wp-admin/network/plugins.php';
        // } else {
        // 	$plugins_page_url = get_admin_url() . 'plugins.php';
        // };
        // REMINDER TO LEAVE REVIEW
        // show only if the plugin was installed at least 14 days ago
        // $fupi_version = get_option('fupi_versions');
        // $date = new DateTime();
        // if ( ! empty( $fupi_version ) && ! empty( $fupi_version[0] ) && $date->getTimestamp() - $fupi_version[0] > ( 14 * 24 * 60 * 60 ) ) {
        // 	$fupi_notices->add(
        // 		'fupi_review_14_days',
        // 		'',
        // 		sprintf( esc_html__('It took 4100+ hours to build WP Full Picture? Please, take 5 minutes of your time to %1$srate it ★★★★★%2$s. Thank you!','full-picture-analytics-cookie-notice'),'<a href="https://wordpress.org/support/plugin/full-picture-analytics-cookie-notice/reviews/">','</a>' ),
        // 		array(
        // 			'type'  => 'warning',
        // 			'scope' => 'user',
        // 		)
        // 	);
        // }
        $theme = wp_get_theme();
        if ( $this->cook_enabled && $theme->get( 'Name' ) == 'OceanWP' ) {
            $fupi_notices->add(
                'fupi_uses_oceanwp_theme',
                // !! MUST BE SMALL CAPS
                '',
                sprintf( esc_html__( 'WP Full Picture has detected that you are using OceanWP theme. This theme breaks the controls for styling Consent Banner. %1$sLearn what to do about it%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-go-around-the-incompatibility-issues-with-oceanwp-theme/" target="_blank">', '</a>' ),
                array(
                    'type'  => 'error',
                    'scope' => 'user',
                )
            );
        }
        if ( fupi_fs()->is_not_paying() ) {
            // Black Friday notification
            // date range: October 31, 2025 to November 30, 2025
            $start_date = strtotime( '2025-10-31 00:00:00' );
            $end_date = strtotime( '2025-11-30 23:59:59' );
            $current_time = current_time( 'timestamp' );
            // Check if current date is within the range
            if ( $current_time >= $start_date && $current_time <= $end_date ) {
                $fupi_notices->add(
                    'fupi_bf_2025',
                    // !! MUST BE SMALL CAPS
                    '',
                    sprintf( esc_html__( 'There is no point in waiting. Get Black Friday deal for WP Full Picture Pro right now. %1$sSee Black Friday Pricing%2$s', 'full-picture-analytics-cookie-notice' ), '<a class="button-primary" href="https://wpfullpicture.com/pricing" target="_blank">', '</a>' ),
                    array(
                        'type'  => 'info',
                        'scope' => 'user',
                    )
                );
            }
        }
        // init
        $fupi_notices->boot();
    }

    //
    // SETTINGS UPDATER
    //
    public function perform_updates() {
        if ( !empty( $this->versions ) && $this->versions[1] == FUPI_VERSION ) {
            return;
        }
        require_once FUPI_PATH . '/admin/common/fupi_updater.php';
        $updater = new Fupi_Updater();
        $updater->run();
    }

    // UPDATE SETUP HELPER AND EASY MODE IN MAIN SETTINGS WITH AJAX
    public function fupi_update_main_options_callback() {
        // Check if the current user is an administrator
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'full-picture-analytics-cookie-notice' ) );
        }
        check_ajax_referer( 'fupi_update_modes_nonce', 'security' );
        // Update main
        if ( $_POST['mode'] === 'setup_mode' ) {
            $versions_opts = get_option( 'fupi_versions' );
            if ( empty( $versions_opts ) ) {
                return;
            }
            if ( $_POST['value'] === 'false' ) {
                // for some reason it is a string
                if ( isset( $versions_opts['debug'] ) ) {
                    unset($versions_opts['debug']);
                    update_option( 'fupi_versions', $versions_opts );
                    wp_send_json_success( '[FP] Setup helper is disabled' );
                }
            } else {
                if ( !isset( $versions_opts['debug'] ) ) {
                    $versions_opts['debug'] = '1';
                    // for some reason it is a string too
                    update_option( 'fupi_versions', $versions_opts );
                    require_once FUPI_PATH . '/admin/common/fupi-clear-cache.php';
                    wp_send_json_success( '[FP] Setup helper is enabled' );
                }
            }
            // save the value of easy mode in user's meta
        } else {
            $current_user_id = get_current_user_id();
            if ( $_POST['value'] === 'true' && $current_user_id > 0 ) {
                update_user_meta( $current_user_id, 'fupi_adv_mode', 'yes' );
                wp_send_json_success( '[FP] Advanced settings are enabled for the current user' );
            } else {
                update_user_meta( $current_user_id, 'fupi_adv_mode', 'no' );
                wp_send_json_success( '[FP] Advanced settings are disabled for the current user' );
            }
        }
    }

    //
    // FEATURES OF SETTINGS FIELDS
    //
    //  Search users with Ajax
    public function fupi_search_users_callback() {
        // Check if the current user is an administrator
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'full-picture-analytics-cookie-notice' ) );
        }
        $search = ( isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '' );
        $users = get_users( array(
            'search'         => "*{$search}*",
            'search_columns' => array('user_login', 'user_email'),
            'number'         => 20,
        ) );
        $results = array();
        foreach ( $users as $user ) {
            $results[] = array(
                'id'   => $user->ID,
                'text' => sprintf( '%s (%s)', $user->user_login, $user->user_email ),
            );
        }
        wp_send_json( $results );
    }

    // Search pages with Ajax
    public function fupi_search_pages_callback() {
        // Check if the current user is an administrator
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'full-picture-analytics-cookie-notice' ) );
        }
        $search = ( isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '' );
        $pages = get_posts( array(
            's'         => "{$search}",
            'post_type' => 'page',
            'number'    => 20,
        ) );
        $results = array();
        foreach ( $pages as $page ) {
            $status_info = ( $page->post_status == 'publish' ? '' : '(' . $page->post_status . ')' );
            $results[] = array(
                'id'   => $page->ID,
                'text' => sprintf( '%s %s', $page->post_title, $status_info ),
            );
        }
        wp_send_json( $results );
    }

    // Check for plugin/theme conflicts via AJAX
    public function fupi_check_conflicts_callback() {
        // Check if the current user is an administrator
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( esc_html__( 'You do not have sufficient permissions to access this page.', 'full-picture-analytics-cookie-notice' ) );
        }
        // Verify nonce
        check_ajax_referer( 'fupi_check_conflicts_nonce', 'security' );
        $conflicts = array();
        // Check for Bricks Builder
        if ( defined( 'BRICKS_NAME' ) && $this->is_woo_enabled ) {
            $conflicts[] = array(
                'type'     => 'bricks_builder',
                'message'  => sprintf( esc_html__( 'We have detected that you are using Bricks Builder. This theme removes one of the core WooCommerce hooks that WP FP needs to track products in lists. %1$sSee how to re-enable it%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-fix-tracking-issues-in-bricks-builder/" target="_blank">', '</a>' ),
                'severity' => 'warning',
            );
        }
        // Check for OceanWP theme
        $theme = wp_get_theme();
        if ( $this->cook_enabled && $theme->get( 'Name' ) == 'OceanWP' ) {
            $conflicts[] = array(
                'type'     => 'oceanwp',
                'message'  => sprintf( esc_html__( 'We have detected that you are using OceanWP theme. This theme breaks the controls for styling Consent Banner. %1$sLearn what to do about it%2$s.', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/how-to-go-around-the-incompatibility-issues-with-oceanwp-theme/" target="_blank">', '</a>' ),
                'severity' => 'warning',
            );
        }
        // Check for WP Rocket
        if ( defined( 'WP_ROCKET_VERSION' ) ) {
            $conflicts[] = array(
                'type'     => 'wp_rocket',
                'message'  => sprintf( esc_html__( 'We have detected that you are using WP Rocket. Please, enable the compatibility mode in %1$sGeneral Settings%2$s > Performance', 'full-picture-analytics-cookie-notice' ), '<a href="' . admin_url( 'admin.php?page=full_picture_main' ) . '" target="_blank">', '</a>' ),
                'severity' => 'warning',
            );
        }
        // Check for Autoptimize
        if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
            $conflicts[] = array(
                'type'     => 'autoptimize',
                'message'  => esc_html__( 'We have detected that you are using Autoptimize. Please, make sure to disable the option "Also disable inline JS". It is not compatible with WP FP.', 'full-picture-analytics-cookie-notice' ),
                'severity' => 'warning',
            );
        }
        // Return results
        wp_send_json_success( array(
            'conflicts'     => $conflicts,
            'has_conflicts' => count( $conflicts ) > 0,
        ) );
    }

}
