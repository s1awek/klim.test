<?php

// CHECK "track_current_user"

$this->track_current_user	 	= true;
$disable_for_roles 				= [];

if( ! empty( $this->main['disable_for_roles'] ) ) {
	$disable_for_roles = $this->main['disable_for_roles'];
}

$user = wp_get_current_user();

if ( ! empty( $user ) ) {
	foreach ( $user->roles as $role ) {
		if ( $role == 'administrator' || in_array( $role, $disable_for_roles ) ) {
			$this->track_current_user = false;
		}
	}
}

$fp['main'] = [
	'track_current_user' 		=> $this->track_current_user, // later also modified by URL parameter in head-js.php
	'is_pro'					=> fupi_fs()->can_use_premium_code(),
	'uploads_url'				=> trailingslashit( wp_upload_dir()['baseurl'] ),
	'is_customizer' 			=> is_customize_preview(),
	'debug' 					=> isset( $this->main['debug'] ),
	'url' 						=> FUPI_URL,
	'bot_list'					=> ! empty( $this->main['bot_list'] ) ? esc_attr( $this->main['bot_list'] ) : 'none',
	'server_method'				=> ! empty( $this->main['server_method'] ) ? esc_attr($this->main['server_method']) : 'rest',
	'magic_keyword' 			=> ! empty( $this->main['magic_keyword'] ) ? esc_attr($this->main['magic_keyword']) : 'tracking',
];

?>
