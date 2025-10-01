<?php

class Fupi_Deactivator {

	public static function deactivate() {
		
		// CLEAN DB

		$fupi_main = get_option('fupi_main');

		if ( ! empty( $fupi_main ) ) {

			if ( ! empty( $fupi_main['clean_all'] ) ) {

				$options_ids = [
					'fupi_versions',
					'cookie_notice',
					'versions',
					'reports',
					'tools', 
					'main', 
					'track', 
					'cegg', 
					'gads', 
					'ga41', 
					'ga42', 
					'gopt', 
					'hotj', 
					'insp', 
					'linkd', 
					'mato', 
					'fbp1', 
					'fbp2', 
					'mads', 
					'clar', 
					'pin', 
					'pla', 
					'posthog', 
					'simpl', 
					'sbee', 
					'tik',
					'twit', 
					'gtm', 
					'cscr', 
					'cook', 
					'iframeblock', 
					'proofrec',
					'privex', 
					'woo', 
					'trackmeta', 
					'blockscr', 
					'geo', 
					'labelpages', 
					'track404'
				];

				foreach ( $options_ids as $id ){ 
					delete_option( 'fupi_' . $id );
				}

				// customizer
				remove_theme_mod( 'fupi_notice_bg_color' );
				remove_theme_mod( 'fupi_cookie_notice_btns_gaps' );
				remove_theme_mod( 'fupi_notice_h_color' );
				remove_theme_mod( 'fupi_notice_text_color' );
				remove_theme_mod( 'fupi_notice_cta_color' );
				remove_theme_mod( 'fupi_notice_cta_txt_color' );
				remove_theme_mod( 'fupi_notice_cta_color_hover' );
				remove_theme_mod( 'fupi_notice_cta_txt_color_hover' );
				remove_theme_mod( 'fupi_notice_btn_color' );
				remove_theme_mod( 'fupi_notice_btn_txt_color' );
				remove_theme_mod( 'fupi_notice_btn_color_hover' );
				remove_theme_mod( 'fupi_notice_btn_txt_color_hover' );
				remove_theme_mod( 'fupi_notice_switch_color' );
				remove_theme_mod( 'fupi_cookie_notice_border' );
				remove_theme_mod( 'fupi_notice_border_color' );
				remove_theme_mod( 'fupi_cookie_notice' );
				remove_theme_mod( 'fupi_cookie_notice_size' );
				remove_theme_mod( 'fupi_notice_round_corners' );
				remove_theme_mod( 'fupi_notice_btn_round_corners' );
				remove_theme_mod( 'fupi_cookie_notice_heading_tag' );
				remove_theme_mod( 'fupi_cookie_notice_h_font_size' );
				remove_theme_mod( 'fupi_cookie_notice_h_font_size_mobile' );
				remove_theme_mod( 'fupi_cookie_notice_p_font_size' );
				remove_theme_mod( 'fupi_cookie_notice_p_font_size_mobile' );
				remove_theme_mod( 'fupi_cookie_notice_button_font_size' );
				remove_theme_mod( 'fupi_cookie_notice_button_font_size_mobile' );
				remove_theme_mod( 'fupi_notice_necessary_switch_color' );
				remove_theme_mod( 'fupi_toggler_bg_color' );
				remove_theme_mod( 'fupi_custom_toggler_img' );

			}

			if ( ! empty( $fupi_main['deactiv_email'] ) ) {

				$to_email = esc_html( $fupi_main['deactiv_email'] );
				$subject = sprintf( esc_attr__( 'WP Full Picture plugin has been deactivated on %1$s', 'full-picture-analytics-cookie-notice' ), get_bloginfo('name') );
				$content = esc_attr__( 'WP Full Picture plugin has been deactivated. You can re-activate it from your WordPress admin plugin management page', 'full-picture-analytics-cookie-notice' );

				wp_mail( $to_email, $subject, $content );
			}
		}
	}
}
