<?php

class Fupi_generate_cscr_files {
    private $settings;

    private $main;

    private $head_output = '';

    private $footer_output = '';

    public function __construct( $opts = false ) {
        $this->settings = ( empty( $opts ) ? get_option( 'fupi_cscr' ) : $opts );
        if ( empty( $this->settings ) ) {
            return;
        }
        $this->main = get_option( 'fupi_main' );
        $this->get_files_contents();
        $this->save_files();
    }

    private function wrap_js_content( $code_to_save ) {
        return "(function(window){\r\n{$code_to_save}\r\n})(window);";
    }

    private function generate_single_script_code( $script_data, $location, $i ) {
        // $atrig_id = ! empty ( $script_data['adv_trigger'] ) && ! empty ( $script_data['adv_trigger']['atrig_id'] ) ? $script_data['adv_trigger']['atrig_id'] : false;
        if ( !empty( $script_data['disable'] ) || empty( $script_data['id'] ) || empty( $script_data['scr'] ) ) {
            return '';
        }
        // if ( $atrig_id == 'removed' ) return '';
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
        // CREATE an optional custom trigger wrapper
        $atrig_start = '';
        $atrig_end = '';
        // if ( $atrig_id ) {
        //     $atrig_start = "// trigger condition start
        // if ( fp?.atrig?.triggers && fp?.cscr?.{$location}[{$i}]?.adv_trigger && fp?.atrig?.triggers.some( trigger => trigger.id == '{$atrig_id}' ) ) {
        // FP.initCondTracking( [fp.cscr.{$location}[{$i}].adv_trigger], evt =>{";
        //     $atrig_end = "} );
        // }; // trigger condition end";
        // }
        // GET title
        $cscr_title = esc_attr( $script_data['title'] );
        // BUILD SCRIPT
        return "\r\n\r\n    // {$scr_id}\r\n    \r\n    if ( allow_loading_{$scr_id}() ) load_{$scr_id}();\r\n\r\n    document.addEventListener( 'fp_load_scripts', () => { if ( allow_loading_{$scr_id}() ) load_{$scr_id}(); } );\r\n\r\n    function allow_loading_{$scr_id}(){\r\n        return FP.isAllowedToLoad_basic( '{$scr_id}', {$force_load}, {$permissions_s}, {$geo} );\r\n    };\r\n        \r\n    function load_{$scr_id}(){\r\n        fp.loaded.push('{$scr_id}');\r\n        if ( fp.main.debug ) console.log('[FP] Custom script loaded: {$cscr_title} ');\r\n        " . $atrig_start . "\r\n\r\n        // Script start\r\n        " . html_entity_decode( $script_data['scr'], ENT_QUOTES ) . "\r\n        // Script end\r\n        " . $atrig_end . "\r\n    };\r\n";
    }

    private function get_files_contents() {
        // Head scripts
        if ( isset( $this->settings['fupi_head_scripts'] ) && is_array( $this->settings['fupi_head_scripts'] ) ) {
            for ($i = 0; $i < count( $this->settings['fupi_head_scripts'] ); $i++) {
                $this->head_output .= $this->generate_single_script_code( $this->settings['fupi_head_scripts'][$i], 'fupi_head_scripts', $i );
            }
        }
        // Footer scripts
        if ( isset( $this->settings['fupi_footer_scripts'] ) && is_array( $this->settings['fupi_footer_scripts'] ) ) {
            for ($i = 0; $i < count( $this->settings['fupi_footer_scripts'] ); $i++) {
                $this->footer_output .= $this->generate_single_script_code( $this->settings['fupi_footer_scripts'][$i], 'fupi_footer_scripts', $i );
            }
        }
    }

    private function save_files() {
        $js_folder_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'wpfp/js/';
        if ( !file_exists( $js_folder_path ) ) {
            mkdir( $js_folder_path, 0755, true );
        }
        // save head file
        if ( !empty( $this->head_output ) ) {
            $full_content = $this->wrap_js_content( $this->head_output );
            $head_js_file_path = $js_folder_path . '/cscr_head.js';
            $result = file_put_contents( $head_js_file_path, $full_content );
        }
        // save footer file
        if ( !empty( $this->footer_output ) ) {
            $full_content = $this->wrap_js_content( $this->footer_output );
            $footer_js_file_path = $js_folder_path . '/cscr_footer.js';
            $result = file_put_contents( $footer_js_file_path, $full_content );
        }
        // make sure index.php file is in the same folder
        $index_file_path = $js_folder_path . '/index.php';
        if ( !file_exists( $index_file_path ) ) {
            $index_file_content = '<?php
			header("HTTP/1.0 403 Forbidden");
			echo "Access denied.";
			exit;';
            file_put_contents( $index_file_path, $index_file_content );
        }
        trigger_error( '[FP] Generated Custom Scripts JS File(s)' );
    }

}
