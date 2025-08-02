<?php

class Fupi_fpinfo_generator {

    private $modules_info = [];
    private $main = [];
    private $tools = [];
    private $cook = [];
    private $data = [];
    private $format = [];
    private $modules_names = [];
    
    public function __construct( $output_format ) {

        $this->cook = get_option('fupi_cook');
        $this->main = get_option('fupi_main');
        $this->tools = get_option('fupi_tools');

        $this->format = $output_format;
        $this->include_modules_datafile();
        $this->check_every_module();
        $this->get_extra_tools_data();
        $this->get_blockscr_data();
        $this->get_iframe_data();
        // $this->check_cdb();
    }

    private function include_modules_datafile() {
        include FUPI_PATH . '/includes/fupi_modules_data.php';
        $this->modules_info = $fupi_modules;
    }

    private function get_module_info( $id ) {
        foreach ( $this->modules_info as $module_info ) {
            if ( $module_info['id'] == $id ) return $module_info;
        }

        return false; // module info not found
    }

    // private function check_cdb(){

    //     if ( ! in_array( 'cook', $this->tools ) ) return;

    //     $cook_opts = get_option('fupi_cook');

    //     if ( ! empty ( $cook_opts ) && ! empty ( $cook_opts['cdb_key'] ) ) {
    //         $this->data[] = [ 'ConsentsDB', 'Paste URL here'];
    //     }
    // }

    private function check_every_module(){
        
        foreach ( $this->tools as $module_id => $mod_value ) {

            $module_info = $this->get_module_info( $module_id );

            if ( $module_info === false ) continue;

            // Check modules

            switch ( $module_id ) {
                case 'cscr':
                    $this->get_custom_script_data( $module_info );
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
        if ( empty( $this->data[ $module_info['name'] ] ) ) $this->data[ $module_info['name'] ] = $module_info['pp'];
    }

    private function get_extra_tools_data(){

        if ( ! empty( $this->main['extra_tools'] ) && is_array( $this->main['extra_tools'] ) ){
            foreach( $this->main['extra_tools'] as $tool ){

                $name   = ! empty( $tool['name'] ) ? $tool['name'] : false;
                $url    = ! empty( $tool['url'] ) ? $tool['url'] : false;
                
                if ( empty( $this->data[ $name ] ) ) $this->data[ $name ] = $url;
            }
        }
    }

    private function get_custom_script_data( $module_info ){

        $scripts_a = get_option('fupi_cscr');

        if ( ! empty( $scripts_a ) && is_array( $scripts_a ) ){

            if ( ! empty( $scripts_a['fupi_head_scripts'] ) && is_array( $scripts_a['fupi_head_scripts'] ) ){
                foreach( $scripts_a['fupi_head_scripts'] as $data ){
                    if ( ! ( isset( $data['disable'] ) || isset( $data['not_installer'] ) ) ) {
                        
                        $name   = ! empty( $data['title'] ) ? $data['title'] : false;
                        $url    = ! empty( $data['pp_url'] ) ? $data['pp_url'] : false;

                        if ( empty( $this->data[ $name ] ) ) $this->data[ $name ] = $url;
                    }
                }
            }

            if ( ! empty( $scripts_a['fupi_footer_scripts'] ) && is_array( $scripts_a['fupi_footer_scripts'] ) ){
                foreach( $scripts_a['fupi_footer_scripts'] as $data ){
                    if ( ! ( isset( $data['disable'] ) || isset( $data['not_installer'] ) ) ) {
                        
                        $name   = ! empty( $data['title'] ) ? $data['title'] : false;
                        $url    = ! empty( $data['pp_url'] ) ? $data['pp_url'] : false;
                        
                        if ( empty( $this->data[ $name ] ) ) $this->data[ $name ] = $url;
                    }
                }
            }
        }
    }

    private function get_iframe_data(){

        // AURO RULES
        if ( ! empty( $this->cook['iframe_auto_rules'] ) && is_array( $this->cook['iframe_auto_rules'] ) ){
            foreach( $this->cook['iframe_auto_rules'] as $rule ){
                switch ( $rule ) {
                    case 'youtube':
                        if ( empty( $this->data['YouTube'] ) ) $this->data['YouTube'] = 'https://business.safety.google/privacy/';
                    break;
                    case 'vimeo':
                        if ( empty( $this->data['Vimeo'] ) ) $this->data['Vimeo'] = 'https://vimeo.com/privacy';
                    break;
                }
            }
        }

        // MANUAL RULES
        if ( ! empty( $this->cook['control_other_iframes'] ) && ! empty( $this->cook['iframe_manual_rules'] ) && is_array( $this->cook['iframe_manual_rules'] ) ){
            foreach( $this->cook['iframe_manual_rules'] as $rule ){

                $needs_consent = ! empty( $rule['stats'] ) || ! empty( $rule['pers'] ) || ! empty( $rule['market'] );
                $has_pp_url    = ! empty( $rule['privacy_url'] );

                if ( $needs_consent && $has_pp_url ) {
                    if ( empty( $this->data[$rule['name']] ) ) $this->data[$rule['name']] = $rule['privacy_url'];
                }
            }
        }
    }

    private function get_blockscr_data(){

        if ( ! empty( $this->cook['scrblk_auto_rules'] ) && is_array( $this->cook['scrblk_auto_rules'] ) ) {
            
            foreach( $this->cook['scrblk_auto_rules'] as $rule ){

                switch ( $rule ) {
                    case 'woo_sbjs':
                        if ( empty( $this->data['WooCommerce Sourcebuster.js'] ) ) $this->data[ 'WooCommerce Sourcebuster.js' ] = 'https://cookiedatabase.org/service/sourcebuster-js/';
                    break;

                    case 'site_kit':
                        if ( empty( $this->data['Google Analytics'] ) ) $this->data['Google Analytics'] = 'https://business.safety.google/privacy/';
                        if ( empty( $this->data['Google Ads'] ) ) $this->data['Google Analytics'] = 'https://business.safety.google/privacy/';
                        if ( empty( $this->data['Google Tag Manager'] ) ) $this->data['Google Analytics'] = 'https://business.safety.google/privacy/';
                    break;
                    
                    case 'ga_jeff_star':
                    case 'rank_math':
                    case 'exact_metrics':
                    case 'monster_insights':
                        if ( empty( $this->data['Google Analytics'] ) ) $this->data['Google Analytics'] = 'https://business.safety.google/privacy/';
                    break;
                    
                    case 'jetpack': 
                        if ( empty( $this->data['Jetpack'] ) ) $this->data['Jetpack'] = 'https://jetpack.com/support/privacy/';
                    break;
                }
            }
        }

        if ( ! empty( $this->cook['control_other_tools'] ) && ! empty( $this->cook['scrblk_manual_rules'] ) && is_array( $this->cook['scrblk_manual_rules'] ) ) {
            
            foreach( $this->cook['scrblk_manual_rules'] as $script ){
               
                $name   = ! empty( $script['title'] ) ? $script['title'] : false;
                $url    = ! empty( $script['pp_url'] ) ? $script['pp_url'] : false;
                
                if ( empty( $this->data[ $name ] ) ) $this->data[ $name ] = $url;
            }
        }
    }

    public function output(){
        
        if ( count( $this->data ) == 0 ) return '';

        $output = '<ol class="fupi_privacy fupi_display_as_' . esc_attr( $this->format ) . '">';
		
        foreach ( $this->data as $name => $url ) {
            
            if ( ! empty( $url ) ) {
                $li = '<li style="text-transform: capitalize;"><a href="' . esc_url( $url ) .'" target="_blank" rel="nofollow">'. esc_attr( $name ) .'</a></li>';
            } else {
                $li = '<li style="text-transform: capitalize;">' . esc_attr( $name ) . '</li>';
            }

            $output .= $li;
		};
        
		return $output . '</ol>';
	}
};