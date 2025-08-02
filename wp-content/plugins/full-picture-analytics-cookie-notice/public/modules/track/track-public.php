<?php

class Fupi_TRACK_public {
    private $settings;

    private $tools;

    private $main;

    public function __construct() {
        $this->settings = get_option( 'fupi_track' );
        if ( empty( $this->settings ) ) {
            return;
        }
        $this->tools = get_option( 'fupi_tools' );
        $this->main = get_option( 'fupi_main' );
        $this->add_filters_and_actions();
    }

    private function add_filters_and_actions() {
    }

}
