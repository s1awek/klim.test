<?php

$redirect404_url = false;
$fp['track'] = [
    'intersections'         => ( !empty( $this->track['intersections'] ) ? esc_attr( $this->track['intersections'] ) : '-200px 0px -200px 0px' ),
    'dblclck_time'          => ( !empty( $this->track['notrack_dblclck'] ) ? esc_attr( $this->track['notrack_dblclck'] ) : 300 ),
    'track_scroll_min'      => ( !empty( $this->track['track_scroll_min'] ) ? esc_attr( $this->track['track_scroll_min'] ) : 200 ),
    'track_scroll_time'     => ( !empty( $this->track['track_scroll_time'] ) ? esc_attr( $this->track['track_scroll_time'] ) : 5 ),
    'formsubm_trackdelay'   => ( !empty( $this->track['formsubm_trackdelay'] ) ? esc_attr( $this->track['formsubm_trackdelay'] ) : 3 ),
    'link_click_delay'      => isset( $this->track['link_click_delay'] ),
    'reset_timer_on_anchor' => isset( $this->track['reset_timer_on_anchor'] ),
    'track404'              => isset( $this->track['track_404'] ),
    'redirect404_url'       => $redirect404_url,
    'use_mutation_observer' => isset( $this->track['use_mutation_observer'] ),
];