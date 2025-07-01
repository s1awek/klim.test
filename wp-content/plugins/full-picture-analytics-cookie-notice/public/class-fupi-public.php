<?php

class Fupi_Public {
    public $plugin_name;

    public $version;

    public $main;

    public $tools;

    protected $cook;

    public $track_current_user;

    private $modules = [];

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->tools = get_option( 'fupi_tools' );
        $this->main = get_option( 'fupi_main' );
        $this->cook = get_option( 'fupi_cook' );
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
            'notice'          => [
                'enabled' => false,
            ],
        ];
        $fpdata = [];
        include_once dirname( __FILE__ ) . '/in_head/data-main.php';
        include_once dirname( __FILE__ ) . '/in_head/data-wp.php';
        $fp = apply_filters( 'fupi_modify_fp_object', $fp );
        $fpdata = apply_filters( 'fupi_modify_fpdata_object', $fpdata );
        // OUTPUT THE DATA
        $output = '<!--noptimize--><script id=\'fp_data_js\' type="text/javascript" data-no-optimize="1" nowprocket>
			
			var fp_premium = ' . json_encode( fupi_fs()->can_use_premium_code() ) . ',
				FP = { \'fns\' : {} },
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

    //
    // SERVER-SIDE TRACKING
    //
    private function get_current_user_ip() {
        if ( !empty( $this->user_ip ) ) {
            return;
        }
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
                } else {
                    $user_ip = false;
                }
            }
            if ( !filter_var( $user_ip, FILTER_VALIDATE_IP ) ) {
                $user_ip = false;
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
        if ( !(isset( $this->cook ) && isset( $this->cook['cdb_key'] )) ) {
            return;
        }
        // MAKE PAYLOAD
        $gmt_offset = get_option( 'gmt_offset' );
        $timezone = ( $gmt_offset >= 0 ? '+' . $gmt_offset : $gmt_offset . '' );
        $payload = [
            'installID'       => fupi_fs()->get_site()->id,
            'consentID'       => $cdbID . '_' . $visit_info->timestamp,
            'serverTimezone'  => $timezone,
            'serverTimestamp' => current_time( 'Y-m-d H:i:s' ),
            'visit'           => $visit_info,
        ];
        // RETURN REQUEST DATA
        $requests_a[] = [
            'url'             => 'https://prod-fr.consentsdb.com/api/cookies',
            'headers'         => ['Content-Type: application/json', 'x-api-key: ' . $this->cook['cdb_key']],
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
        // get IP
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

}
