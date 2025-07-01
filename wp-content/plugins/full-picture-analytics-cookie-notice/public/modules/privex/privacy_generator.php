<?php

class Fupi_policy_generator {

    private $modules_info = [];
    private $tools = [];
    private $data = [];
    private $format = [];
    private $modules_names = [];
    
    public function __construct( $output_format ) {
        $this->format = $output_format;
        $this->include_modules_datafile();
        $this->get_enabled_modules();
        $this->check_every_module();
        // $this->check_cdb();
    }

    private function include_modules_datafile() {
        include FUPI_PATH . '/includes/fupi_modules_data.php';
        include FUPI_PATH . '/includes/fupi_modules_names.php';
        $this->modules_info = $fupi_modules;
        $this->modules_names = $fupi_modules_names;
    }

    private function get_enabled_modules(){
        $tools = get_option('fupi_tools');
        if ( ! empty( $tools ) ) $this->tools = array_keys( $tools );
    }

    private function get_module_info( $id ) {
        foreach ( $this->modules_info as $module_info ) {
            if ( $module_info['id'] == $id ) {
                return $module_info;
            }
        }
    }

    // private function check_cdb(){

    //     if ( ! in_array( 'cook', $this->tools ) ) return;

    //     $cook_opts = get_option('fupi_cook');

    //     if ( ! empty ( $cook_opts ) && ! empty ( $cook_opts['cdb_key'] ) ) {
    //         $this->data[] = [ 'ConsentsDB', 'Paste URL here'];
    //     }
    // }

    private function check_every_module(){
        
        foreach ( $this->tools as $module_id ) {

            $module_info = $this->get_module_info( $module_id );

            // Check modules

            switch ( $module_id ) {
                case 'privex':
                    $this->get_privex_data( $module_info );
                break;
                case 'cscr':
                    $this->get_custom_script_data( $module_info );
                break;
                case 'blockscr':
                    $this->get_blockscr_data( $module_info );
                break;
                case 'gtm':
                    $this->get_module_data( $module_info );
                break;
                default:
                    if ( $module_info['type'] == 'integr' && isset( $module_info['pp'] ) ) $this->get_module_data( $module_info );
                break;
            }
        }
    }

    private function get_module_data( $module_info ){
        $this->data[] = [
            $this->modules_names[$module_info['id']],
            $module_info['pp']
        ];
    }

    private function get_privex_data( $module_info ){

        $privex_opts = get_option('fupi_privex');

        if ( ! empty( $privex_opts ) && ! empty( $privex_opts['extra_tools'] ) && is_array( $privex_opts['extra_tools'] ) ){
            foreach( $privex_opts['extra_tools'] as $tool ){
                $name   = ! empty( $tool['name'] ) ? esc_attr( $tool['name'] ) : false;
                $url    = ! empty( $tool['url'] ) ? esc_attr( $tool['url'] ) : false;
                $this->data[] = [ $name, $url ];
            }
        }
    }

    private function get_custom_script_data( $module_info ){

        $scripts_a = get_option('fupi_cscr');

        if ( ! empty( $scripts_a ) && is_array( $scripts_a ) ){

            if ( ! empty( $scripts_a['fupi_head_scripts'] ) && is_array( $scripts_a['fupi_head_scripts'] ) ){
                foreach( $scripts_a['fupi_head_scripts'] as $data ){
                    if ( ! ( isset( $data['disable'] ) || isset( $data['not_installer'] ) ) ) {
                        $name   = ! empty( $data['title'] ) ? esc_attr( $data['title'] ) : false;
                        $url    = ! empty( $data['pp_url'] ) ? esc_attr( $data['pp_url'] ) : false;
                        $this->data[] = [ $name, $url ];
                    }
                }
            }

            if ( ! empty( $scripts_a['fupi_footer_scripts'] ) && is_array( $scripts_a['fupi_footer_scripts'] ) ){
                foreach( $scripts_a['fupi_footer_scripts'] as $data ){
                    if ( ! ( isset( $data['disable'] ) || isset( $data['not_installer'] ) ) ) {
                        $name   = ! empty( $data['title'] ) ? esc_attr( $data['title'] ) : false;
                        $url    = ! empty( $data['pp_url'] ) ? esc_attr( $data['pp_url'] ) : false;
                        $this->data[] = [ $name, $url ];
                    }
                }
            }
        }
    }

    private function get_blockscr_data( $module_info ){

        $blockscr_a = get_option('fupi_blockscr');

        if ( ! empty( $blockscr_a ) && is_array($blockscr_a) ){

            if ( ! empty( $blockscr_a['blocked_scripts'] ) && is_array( $blockscr_a['blocked_scripts'] ) ){
                foreach( $blockscr_a['blocked_scripts'] as $data ){
                    $name   = ! empty( $data['title'] ) ? esc_attr( $data['title'] ) : false;
                    $url    = ! empty( $data['pp_url'] ) ? esc_attr( $data['pp_url'] ) : false;
                    $this->data[] = [ $name, $url ];
                }
            }
        }
    }

    public function output(){
        
        if ( count( $this->data ) == 0 ) return '';

        $output = '<ol class="fupi_privacy fupi_display_as_' . esc_attr( $this->format ) . '">';
        $has_content = false;
		
        foreach ( $this->data as $data ) {

            if ( empty( $data[0] ) ) continue;
            
            $has_content = true;
            $is_link = ! empty( $data[1] );
            
            if ( $is_link ) {
                $li = '<li><a href="' . esc_url( $data[1] ) .'" target="_blank" rel="nofollow">'. esc_attr( $data[0] ) .'</a></li>';
            } else {
                $li = '<li>' . esc_attr( $data[0] ) . '</li>';
            }

            $output .= $li;
		};

        // trigger_error( serialize( $this->data ) );
        
		return $has_content ? $output . '</ol>' : '';
	}
};