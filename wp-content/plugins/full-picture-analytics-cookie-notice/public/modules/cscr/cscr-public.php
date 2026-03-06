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
        add_action( 'wp_head', array($this, 'fupi_output_in_head'), 50 );
        add_action( 'wp_footer', array($this, 'fupi_output_in_footer') );
        add_filter(
            'fupi_modify_fp_object',
            array($this, 'add_data_to_fp_object'),
            10,
            1
        );
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
            // GET cookie permissions
            $permissions_a = [];
            if ( !empty( $script_data['stats'] ) && $script_data['stats'] == '1' ) {
                array_push( $permissions_a, 'stats' );
            }
            if ( !empty( $script_data['pers'] ) && $script_data['pers'] == '1' ) {
                array_push( $permissions_a, 'personalisation' );
            }
            if ( !empty( $script_data['market'] ) && $script_data['market'] == '1' ) {
                array_push( $permissions_a, 'marketing' );
            }
            $permissions_s = implode( ' ', $permissions_a );
            // GET force load
            $force_load = ( empty( $script_data['force_load'] ) ? '0' : '1' );
            // GET geo requirements
            $geo = '';
            // GET HTML
            if ( $location == 'fupi_footer_scripts' && !empty( $script_data['html'] ) ) {
                $output .= html_entity_decode( $script_data['html'], ENT_QUOTES );
            }
            // GET title
            $cscr_title = esc_attr( $script_data['title'] );
            // OUTPUT
            $output .= "<!--noptimize-->\r\n            <script type='text/plain' data-fupi_type='inline' data-module='cscr' data-fupi_id='fp_{$script_data['id']}' data-fupi_class='fupi_blocked_script fupi_no_defer' data-fupi_force='{$force_load}' data-fupi_permiss='{$permissions_s}' data-fupi_geo='{$geo}' data-no-optimize=\"1\" data-noptimize nowprocket>\r\n                " . html_entity_decode( $script_data['scr'], ENT_QUOTES ) . "\r\n            </script>\r\n            <!--/noptimize-->";
        }
        return $output;
    }

    public function fupi_output_in_head() {
        echo $this->fupi_output_inline_scripts( 'fupi_head_scripts' );
    }

    public function fupi_output_in_footer() {
        echo $this->fupi_output_inline_scripts( 'fupi_footer_scripts' );
    }

    public function add_data_to_fp_object( $fp ) {
        $fp['tools'][] = 'cscr';
        $scr_names = [];
        if ( !empty( $this->settings['fupi_head_scripts'] ) ) {
            for ($i = 0; $i < count( $this->settings['fupi_head_scripts'] ); $i++) {
                $scr_names[$this->settings['fupi_head_scripts'][$i]['id']] = $this->settings['fupi_head_scripts'][$i]['title'];
            }
        }
        if ( !empty( $this->settings['fupi_footer_scripts'] ) ) {
            for ($i = 0; $i < count( $this->settings['fupi_footer_scripts'] ); $i++) {
                $scr_names[$this->settings['fupi_footer_scripts'][$i]['id']] = $this->settings['fupi_footer_scripts'][$i]['title'];
            }
        }
        $fp['cscr'] = $scr_names;
        return $fp;
    }

}
