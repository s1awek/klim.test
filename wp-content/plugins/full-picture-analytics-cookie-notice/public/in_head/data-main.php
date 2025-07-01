<?php

// these vars will be used in other files too
// check if current user role should be tracked
$this->track_current_user = true;
$disable_for_roles = [];
if ( !empty( $this->main ) && isset( $this->main['disable_for_roles'] ) ) {
    $disable_for_roles = $this->main['disable_for_roles'];
}
$user = wp_get_current_user();
if ( !empty( $user ) ) {
    foreach ( $user->roles as $role ) {
        if ( $role == 'administrator' || in_array( $role, $disable_for_roles ) ) {
            $this->track_current_user = false;
        }
    }
}
$fp['vars'] = [
    'bot_list'              => ( !empty( $this->main['bot_list'] ) ? esc_attr( $this->main['bot_list'] ) : 'none' ),
    'url'                   => FUPI_URL,
    'is_pro'                => fupi_fs()->can_use_premium_code(),
    'uploads_url'           => trailingslashit( wp_upload_dir()['baseurl'] ),
    'is_customizer'         => is_customize_preview(),
    'debug'                 => isset( $this->main['debug'] ),
    'intersections'         => ( !empty( $this->main['intersections'] ) ? esc_attr( $this->main['intersections'] ) : '-200px 0px -200px 0px' ),
    'track_current_user'    => $this->track_current_user,
    'dblclck_time'          => ( !empty( $this->main['notrack_dblclck'] ) ? esc_attr( $this->main['notrack_dblclck'] ) : 300 ),
    'track_scroll_min'      => ( !empty( $this->main['track_scroll_min'] ) ? esc_attr( $this->main['track_scroll_min'] ) : 200 ),
    'track_scroll_time'     => ( !empty( $this->main['track_scroll_time'] ) ? esc_attr( $this->main['track_scroll_time'] ) : 5 ),
    'formsubm_trackdelay'   => ( !empty( $this->main['formsubm_trackdelay'] ) ? esc_attr( $this->main['formsubm_trackdelay'] ) : 3 ),
    'link_click_delay'      => isset( $this->main['link_click_delay'] ),
    'reset_timer_on_anchor' => isset( $this->main['reset_timer_on_anchor'] ),
    'track404'              => isset( $this->tools['track404'] ),
    'redirect404_url'       => ( isset( $redirect404_url ) ? $redirect404_url : false ),
    'magic_keyword'         => ( !empty( $this->main ) && !empty( $this->main['magic_keyword'] ) ? esc_attr( $this->main['magic_keyword'] ) : 'tracking' ),
    'use_mutation_observer' => isset( $this->main['use_mutation_observer'] ),
    'server_method'         => ( !empty( $this->main['server_method'] ) ? esc_attr( $this->main['server_method'] ) : 'rest' ),
];