<?php

class Fupi_CSCR_public {
    private $settings;

    private $main;

    public function __construct() {
        $this->settings = get_option( 'fupi_cscr' );
        $this->main = get_option( 'fupi_main' );
        if ( !empty( $this->settings ) ) {
            $this->add_actions_and_filters();
        }
    }

    private function add_actions_and_filters() {
        // if we link to JS files
        if ( isset( $this->main ) && !empty( $this->main['save_cscr_file'] ) ) {
            add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
            add_action( 'wp_footer', array($this, 'fupi_output_html_in_footer') );
            // if we add JS directly to the HTML
        } else {
            add_action( 'wp_head', array($this, 'fupi_output_in_head'), 50 );
            add_action( 'wp_footer', array($this, 'fupi_output_in_footer') );
        }
        add_filter(
            'fupi_modify_fp_object',
            array($this, 'add_data_to_fp_object'),
            10,
            1
        );
    }

    public function enqueue_scripts() {
        $upload_dir_a = wp_upload_dir();
        $folder_url = trailingslashit( $upload_dir_a['baseurl'] ) . 'wpfp/js/';
        $folder_path = trailingslashit( $upload_dir_a['basedir'] ) . 'wpfp/js/';
        // if $folder_url starts with "http:" but FUPI_URL starts with httpS, then replace it with "https:"
        if ( substr( $folder_url, 0, 5 ) === 'http:' && substr( FUPI_URL, 0, 6 ) === 'https:' ) {
            $folder_url = 'https:' . substr( $folder_url, 5 );
        }
        $head_args = [
            'in_footer' => false,
        ];
        $footer_args = [
            'in_footer' => true,
        ];
        if ( !empty( $this->settings['fupi_head_scripts'] ) && file_exists( $folder_path . 'cscr_head.js' ) ) {
            /* ^ */
            wp_enqueue_script(
                'fupi-cscr-head-js',
                $folder_url . 'cscr_head.js',
                array('fupi-helpers-js'),
                filemtime( $folder_path . 'cscr_head.js' ),
                $head_args
            );
        }
        if ( !empty( $this->settings['fupi_footer_scripts'] ) && file_exists( $folder_path . 'cscr_footer.js' ) ) {
            /* _ */
            wp_enqueue_script(
                'fupi-cscr-footer-js',
                $folder_url . 'cscr_footer.js',
                array('fupi-helpers-js', 'fupi-helpers-footer-js'),
                filemtime( $folder_path . 'cscr_footer.js' ),
                $footer_args
            );
        }
    }

    private function fupi_output_inline_scripts( $location ) {
        if ( !(isset( $this->settings[$location] ) && is_array( $this->settings[$location] )) ) {
            return '';
        }
        $location_count = count( $this->settings[$location] );
        $output = '';
        for ($i = 0; $i < $location_count; $i++) {
            $script_data = $this->settings[$location][$i];
            // $atrig_id = ! empty ( $script_data['adv_trigger'] ) && ! empty ( $script_data['adv_trigger']['atrig_id'] ) ? $script_data['adv_trigger']['atrig_id'] : false;
            if ( !empty( $script_data['disable'] ) || empty( $script_data['id'] ) || empty( $script_data['scr'] ) ) {
                continue;
            }
            // if ( $atrig_id == 'removed' ) continue;
            // GET script id
            if ( $location == 'fupi_footer_scripts' ) {
                $scr_id = 'fp_' . $script_data['id'] . '_footer';
            } else {
                $scr_id = 'fp_' . $script_data['id'];
            }
            // GET cookie permissions
            $permissions_a = [];
            if ( !empty( $script_data['stats'] ) && $script_data['stats'] == '1' ) {
                array_push( $permissions_a, '\'stats\'' );
            }
            if ( !empty( $script_data['pers'] ) && $script_data['pers'] == '1' ) {
                array_push( $permissions_a, '\'personalisation\'' );
            }
            if ( !empty( $script_data['market'] ) && $script_data['market'] == '1' ) {
                array_push( $permissions_a, '\'marketing\'' );
            }
            $permissions_s = '[' . implode( ',', $permissions_a ) . ']';
            // GET force load
            $force_load = ( empty( $script_data['force_load'] ) ? 'false' : 'true' );
            // GET geo requirements
            $geo = 0;
            // GET HTML
            if ( $location == 'fupi_footer_scripts' && !empty( $script_data['html'] ) ) {
                $output .= html_entity_decode( $script_data['html'], ENT_QUOTES );
            }
            // GET title
            $cscr_title = esc_attr( $script_data['title'] );
            // CREATE an optional custom trigger wrapper
            $atrig_start = '';
            $atrig_end = '';
            //     if ( $atrig_id ) {
            //         $atrig_start = "
            // // trigger condition start
            // if ( fp?.atrig?.triggers && fp?.cscr?.{$location}[{$i}]?.adv_trigger && fp?.atrig?.triggers.some( trigger => trigger.id == '{$atrig_id}' ) ) {
            //     FP.initCondTracking( [fp.cscr.{$location}[{$i}].adv_trigger], evt =>{";
            //         $atrig_end = "
            //     } );
            // }
            // // trigger condition end ";
            //     }
            // OUTPUT
            $output .= "<!--noptimize-->\r\n            <script id='{$scr_id}_temp' class='fupi_no_defer' type='text/plain' data-no-optimize=\"1\" nowprocket>\r\n                fp.loaded.push('{$scr_id}');\r\n                if ( fp.main.debug ) console.log('[FP] Custom script loaded: {$cscr_title} ');\r\n                " . $atrig_start . "\r\n\r\n                // Script start\r\n                " . html_entity_decode( $script_data['scr'], ENT_QUOTES ) . "\r\n                // Script end\r\n                " . $atrig_end . "\r\n            </script>\r\n            <script class='fupi_cscr fupi_no_defer' data-no-optimize='1' nowprocket>\r\n                if ( FP.isAllowedToLoad_basic( '{$scr_id}', {$force_load}, {$permissions_s}, {$geo} ) ) {\r\n                    FP.loadScript('{$scr_id}');\r\n                } else {\r\n                    fp.blocked_scripts.push( [ false, 'empty', '{$scr_id}', {$force_load}, {$permissions_s}, {$geo} ] );\r\n                }\r\n            </script>\r\n            <!--/noptimize-->";
        }
        return $output;
    }

    public function fupi_output_in_head() {
        echo $this->fupi_output_inline_scripts( 'fupi_head_scripts' );
    }

    public function fupi_output_in_footer() {
        echo $this->fupi_output_inline_scripts( 'fupi_footer_scripts' );
    }

    // OUTPUT HTML in footer
    // !! used when the JS is saved in a file
    public function fupi_output_html_in_footer() {
        if ( isset( $this->settings['fupi_footer_scripts'] ) && is_array( $this->settings['fupi_footer_scripts'] ) ) {
            $html_before_script = '';
            foreach ( $this->settings['fupi_footer_scripts'] as $script_data ) {
                if ( empty( $script_data['html'] ) || !empty( $script_data['disable'] ) || empty( $script_data['id'] ) || empty( $script_data['scr'] ) ) {
                    continue;
                }
                $html_before_script .= html_entity_decode( $script_data['html'], ENT_QUOTES );
            }
            if ( !empty( $html_before_script ) ) {
                echo '<!-- START WP FP Custom Footer Scripts -->
                    ' . $html_before_script . '
                <!-- END WP FP Custom Footer Scripts -->';
            }
        }
    }

    public function add_data_to_fp_object( $fp ) {
        $filtered_settings = $this->settings;
        $fp['tools'][] = 'cscr';
        if ( !empty( $filtered_settings['fupi_head_scripts'] ) ) {
            for ($i = 0; $i < count( $filtered_settings['fupi_head_scripts'] ); $i++) {
                $fp['tools'][] = $filtered_settings['fupi_head_scripts'][$i]['id'];
                unset($filtered_settings['fupi_head_scripts'][$i]['scr']);
            }
        }
        if ( !empty( $filtered_settings['fupi_footer_scripts'] ) ) {
            for ($i = 0; $i < count( $filtered_settings['fupi_footer_scripts'] ); $i++) {
                $fp['tools'][] = $filtered_settings['fupi_footer_scripts'][$i]['id'];
                unset($filtered_settings['fupi_footer_scripts'][$i]['scr']);
                unset($filtered_settings['fupi_head_scripts'][$i]['html']);
            }
        }
        $fp['cscr'] = $filtered_settings;
        return $fp;
    }

}
