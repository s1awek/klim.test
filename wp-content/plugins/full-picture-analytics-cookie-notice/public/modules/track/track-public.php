<?php

class Fupi_TRACK_public {
    private $settings;

    private $tools;

    private $main;

    private $ver;

    public function __construct() {
        $this->settings = get_option( 'fupi_track' );
        if ( empty( $this->settings ) ) {
            return;
        }
        $this->tools = get_option( 'fupi_tools' );
        $this->main = get_option( 'fupi_main' );
        $this->ver = get_option( 'fupi_versions' );
        $this->add_filters_and_actions();
    }

    private function add_filters_and_actions() {
    }

}
