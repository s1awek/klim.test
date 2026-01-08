<?php

class Fupi_Public {
    public $plugin_name;

    public $version;

    public $main;

    public $track;

    public $tools;

    public $woo;

    public $proofrec;

    protected $cook;

    public $track_current_user;

    private $ver;

    private $modules = [];

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->tools = get_option( 'fupi_tools' );
        $this->main = get_option( 'fupi_main' );
        $this->track = get_option( 'fupi_track' );
        $this->cook = get_option( 'fupi_cook' );
        $this->woo = get_option( 'fupi_woo' );
        $this->proofrec = get_option( 'fupi_proofrec' );
        $this->ver = get_option( 'fupi_versions' );
    }

    public function load_module( $moduleName, $is_premium = false ) {
        if ( $is_premium && !fupi_fs()->can_use_premium_code() ) {
            return;
        }
        // do not load premium modules
        // do not load a module that is already loaded
        $moduleClass = 'Fupi_' . strtoupper( $moduleName ) . '_public';
        if ( class_exists( $moduleClass ) ) {
            trigger_error( "Module {$moduleName} is already loaded.", E_USER_WARNING );
            return;
        }
        // load file
        if ( $is_premium ) {
            $modulePath = FUPI_PATH . "/public/modules/{$moduleName}__premium_only/{$moduleName}-public.php";
        } else {
            $modulePath = FUPI_PATH . "/public/modules/{$moduleName}/{$moduleName}-public.php";
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
        $this->modules[$moduleName] = new $moduleClass();
        // you can pass $this here or any other vars if needed. Passing $this will let the module access the main class and all of its methods and properties.
    }

    public function __call( $method, $args ) {
        foreach ( $this->modules as $module ) {
            if ( method_exists( $module, $method ) ) {
                return call_user_func_array( [$module, $method], $args );
            }
        }
        throw new Exception("Method {$method} not found.");
    }

    public function fupi_output_fupi_data_in_head() {
        // GET THE DATA
        global $wp;
        global $post;
        $fp = [
            'loaded'          => [],
            'loading'         => [],
            'blocked_scripts' => [],
            'waitlist'        => [],
            'actions'         => [],
            'observers'       => [],
            'tools'           => [],
            'vars'            => [],
            'notice'          => [
                'enabled' => false,
            ],
        ];
        $fpdata = [];
        include_once dirname( __FILE__ ) . '/modules/main/data-main.php';
        include_once dirname( __FILE__ ) . '/modules/track/data-track.php';
        include_once dirname( __FILE__ ) . '/in_head/data-wp.php';
        $fp = apply_filters( 'fupi_modify_fp_object', $fp );
        $fpdata = apply_filters( 'fupi_modify_fpdata_object', $fpdata );
        // OUTPUT THE DATA
        $output = '<!--noptimize--><script id=\'fp_data_js\' class="fupi_no_defer" type="text/javascript" data-no-defer="1" data-no-optimize="1" nowprocket>
			
			var FP = { \'fns\' : {} },
				fp = ' . json_encode( $fp ) . ',
				fpdata = ' . json_encode( $fpdata ) . ';';
        // fp_nonce = "' . wp_create_nonce('wp_rest'). '";'; // It has to be "wp_rest" This is required!
        $extra_scr = apply_filters( 'fupi_add_js_to_head_data', '' );
        if ( !empty( $extra_scr ) ) {
            $output .= $extra_scr;
        }
        if ( empty( $this->main ) || empty( $this->main['save_settings_file'] ) ) {
            include_once dirname( __FILE__ ) . '/in_head/head-js.php';
        }
        $output .= '</script><!--/noptimize-->';
        echo $output;
    }

    public function fupi_enqueue_js_helpers() {
        if ( !empty( $this->main ) && !empty( $this->main['save_settings_file'] ) ) {
            $file_url = trailingslashit( wp_upload_dir()['baseurl'] ) . 'wpfp/js/head.js';
            $file_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'wpfp/js/head.js';
            // if $file_url starts with "http:" but FUPI_URL starts with httpS, then replace it with "https:"
            if ( substr( $file_url, 0, 5 ) === 'http:' && substr( FUPI_URL, 0, 6 ) === 'https:' ) {
                $file_url = 'https:' . substr( $file_url, 5 );
            }
            /* ^ */
            wp_enqueue_script(
                'fupi-helpers-js',
                $file_url,
                array(),
                filemtime( $file_path ),
                false
            );
            // can delete fp_cookies when ?tracking=off
        } else {
            /* ^ */
            wp_enqueue_script(
                'fupi-helpers-js',
                FUPI_URL . 'public/common/fupi-helpers.js',
                array(),
                $this->version,
                false
            );
            // can delete fp_cookies when ?tracking=off
        }
        /* _ */
        wp_enqueue_script(
            'fupi-helpers-footer-js',
            FUPI_URL . 'public/common/fupi-helpers-footer.js',
            array('fupi-helpers-js'),
            $this->version,
            true
        );
        // jquery was set as dependancy before 7.2.2
    }

    /**
     * Selectively add data-no-defer="1" attribute to specific enqueued scripts and styles
     */
    // DEFERRING EXCLUSIONS
    // Add "Data-no-defer" to file links for Litespeed cache and some compatible plugins
    function add_nodefer_to_fupi_scripts( $tag, $handle, $src ) {
        // Check if current script should have no-defer attribute
        if ( str_contains( $handle, 'fupi-' ) ) {
            $tag = str_replace( '<script ', '<script data-no-defer="1" ', $tag );
        }
        return $tag;
    }

    // For WP Rocket
    // exclude inline scripts
    public function fupi_rocket_exclude_inline_js( $inline_excludes ) {
        if ( !empty( $this->main['wprocket_compat'] ) ) {
            if ( !is_array( $inline_excludes ) ) {
                $inline_excludes = array();
            }
            $inline_excludes[] = 'fupi_no_defer';
        }
        return $inline_excludes;
    }

    // exclude script files
    public function fupi_rocket_exclude_js_files( $excludes ) {
        if ( !empty( $this->main['wprocket_compat'] ) ) {
            if ( !is_array( $excludes ) ) {
                $excludes = array();
            }
            $excludes[] = 'hooks.js';
            $excludes[] = 'hooks.min.js';
            $excludes[] = '/wp-includes/js/jquery/(.*).js';
            $excludes[] = '/wp-content/plugins/full-picture-analytics-cookie-notice/(.*)';
            $excludes[] = '/wp-content/plugins/full-picture-premium/(.*)';
            // standard WP install
            $excludes[] = '/wp-content/uploads/wpfp/js/head.js';
            $excludes[] = '/wp-content/uploads/wpfp/js/footer.js';
            $excludes[] = '/wp-content/uploads/wpfp/js/cscr_head.js';
            $excludes[] = '/wp-content/uploads/wpfp/js/cscr_footer.js';
            // multisite install
            $excludes[] = '/wp-content/uploads/sites/([0-9]?)/wpfp/js/head.js';
            $excludes[] = '/wp-content/uploads/sites/([0-9]?)/wpfp/js/footer.js';
            $excludes[] = '/wp-content/uploads/sites/([0-9]?)/wpfp/js/cscr_head.js';
            $excludes[] = '/wp-content/uploads/sites/([0-9]?)/wpfp/js/cscr_footer.js';
        }
        return $excludes;
    }

    //
    // SERVER-SIDE TRACKING
    //
    private function get_current_user_ip() {
        // Get current user IP address
        if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $user_ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $user_ip = $_SERVER['REMOTE_ADDR'];
        }
        // validate IP
        if ( !filter_var( $user_ip, FILTER_VALIDATE_IP ) ) {
            if ( str_contains( $user_ip, ',' ) ) {
                // fix for Cloudflare
                $user_ip = explode( ',', $user_ip )[0];
            } else {
                if ( !empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
                    $user_ip = $_SERVER['HTTP_X_REAL_IP'];
                }
            }
            if ( !filter_var( $user_ip, FILTER_VALIDATE_IP ) ) {
                $user_ip = '127.0.0.1';
            }
        }
        return $user_ip;
    }

    // PREPARE REQUEST TO CDB
    private function prepare_cdb_data( $requests_a, $event_payload ) {
        $visit_info = (object) $event_payload;
        $cdbID = $visit_info->cdbID;
        if ( empty( $cdbID ) ) {
            return;
        }
        if ( empty( $this->proofrec['cdb_key'] ) ) {
            return;
        }
        // MAKE PAYLOAD
        $gmt_offset = get_option( 'gmt_offset' );
        $timezone = ( $gmt_offset >= 0 ? '+' . $gmt_offset : $gmt_offset . '' );
        $payload = [
            'consentID'       => $cdbID . '_' . $visit_info->timestamp,
            'serverTimezone'  => $timezone,
            'serverTimestamp' => current_time( 'Y-m-d H:i:s' ),
            'visit'           => $visit_info,
        ];
        $payload['installID'] = 999999;
        // RETURN REQUEST DATA
        $requests_a[] = [
            'url'             => 'https://prod-fr.consentsdb.com/api/cookies',
            'headers'         => ['Content-Type: application/json', 'x-api-key: ' . $this->proofrec['cdb_key']],
            'payload'         => $payload,
            'return_response' => 'CDB',
        ];
        return $requests_a;
    }

    // REST HOOKS
    public function fupi_rest_hooks() {
        register_rest_route( 'fupi/v1', '/sender', [
            'methods'             => 'POST',
            'callback'            => [$this, 'fupi_process_server_calls'],
            'permission_callback' => '__return_true',
        ] );
    }

    // AJAX HOOKS
    public function fupi_ajax_hooks() {
        // Get the payload
        $input = file_get_contents( 'php://input' );
        $data_arr = json_decode( $input, true );
        // process data and echo response
        $response = json_encode( $this->fupi_process_server_calls( $data_arr, true ) );
        echo $response;
        die;
    }

    // Process the payload
    // ( the returned response is automatically converted to JSON )
    public function fupi_process_server_calls( $request, $is_ajax = false ) {
        $data_arr = ( $is_ajax ? $request : json_decode( $request->get_body() ) );
        // you can also use "$request->get_params();" or these >>> https://www.coditty.com/code/wordpress-api-custom-route-access-post-parameters
        $userIP = $this->get_current_user_ip();
        $requests_a = [];
        foreach ( $data_arr as $event_data ) {
            $event_type = $event_data[0];
            $event_id = $event_data[1];
            $event_payload = $event_data[2];
            if ( $event_type == 'cdb' ) {
                $requests_a = $this->prepare_cdb_data( $requests_a, $event_payload );
            } else {
                if ( $event_type == 'send' ) {
                    $requests_a = apply_filters(
                        'fupi_prepare_' . $event_id . '_server_request_data',
                        [],
                        $event_payload,
                        $userIP
                    );
                } else {
                    if ( $event_type == 'process' ) {
                        // do server action if we are not sending anything
                        do_action( 'fupi_do_' . $event_id . '_server_action', $event_payload, $userIP );
                    }
                }
            }
        }
        // send results to servers and return the response
        if ( empty( $requests_a ) ) {
            return 'Server call has been processed.';
        } else {
            $responses = [];
            include_once FUPI_PATH . '/public/common/send-to-remote-server.php';
            return ( count( $responses ) > 0 ? $responses : 'Server call has been processed.' );
        }
    }

    // HTML MODS
    public function fupi_maybe_buffer_output() {
        ob_start( array($this, 'fupi_return_buffer') );
    }

    public function fupi_return_buffer( $html ) {
        if ( !$html ) {
            return $html;
        }
        // Copy HTML
        $orig_html = $html;
        if ( !empty( $this->tools['cook'] ) && !empty( $this->cook ) ) {
            // SCRIPTS BLOCKER
            $blockscr_enabled = !empty( $this->cook['scrblk_auto_rules'] ) || !empty( $this->cook['control_other_tools'] ) && !empty( $this->cook['scrblk_manual_rules'] );
            if ( !empty( $blockscr_enabled ) ) {
                include_once dirname( __FILE__ ) . '/common/blockscr_parser.php';
            }
            // IFRAMES BLOCKER
            $iframeblock_enabled = !empty( $this->cook['iframe_auto_rules'] ) || !empty( $this->cook['control_other_iframes'] ) && !empty( $this->cook['iframe_manual_rules'] );
            if ( $iframeblock_enabled ) {
                // make sure we do not try to manage iframes in the bricks builder editor (it breaks)
                $can_load_iframe_parser = !(function_exists( 'bricks_is_builder' ) && bricks_is_builder());
                if ( $can_load_iframe_parser ) {
                    include_once dirname( __FILE__ ) . '/common/iframeblock_parser.php';
                }
            }
        }
        // SAFE FONTS
        if ( isset( $this->tools['safefonts'] ) ) {
            include_once dirname( __FILE__ ) . '/common/safefonts_parser.php';
        }
        if ( !empty( $html ) ) {
            return $html;
        }
        return $orig_html;
    }

}
