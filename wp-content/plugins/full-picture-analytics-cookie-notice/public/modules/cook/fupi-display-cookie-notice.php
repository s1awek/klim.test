<?php

function fupi_modify_cons_banner_text(  $text  ) {
    $open_tag_pos = strpos( $text, '{{' );
    $close_tag_pos = strpos( $text, '}}' );
    if ( $open_tag_pos && $close_tag_pos ) {
        // get the content between {{ }}
        $regex = '/\\{\\{(.*?)\\}\\}/';
        // Replace matches with anchor tags using preg_replace
        $text = preg_replace_callback( $regex, function ( $match ) {
            $innerText = $match[1];
            // Capture inner text
            $url = get_privacy_policy_url();
            // get URL and create a link
            if ( strpos( $innerText, '|' ) > 0 ) {
                $innerText_a = explode( '|', $innerText );
                if ( !empty( $innerText_a[1] ) ) {
                    $url = $innerText_a[1];
                    $innerText = $innerText_a[0];
                }
            }
            return "<a href=\"{$url}\">{$innerText}</a>";
        }, $text );
    }
    return do_shortcode( $text );
}

// THX: a11y: https://a11y-solutions.stevenwoodson.com/solutions/focus/modals/
$notice_opts = get_option( 'fupi_cookie_notice' );
$hidden_elements = ( isset( $notice_opts['hide'] ) && is_array( $notice_opts['hide'] ) ? $notice_opts['hide'] : false );
$shown_elements = ( isset( $notice_opts['show'] ) && is_array( $notice_opts['show'] ) ? $notice_opts['show'] : false );
$overlay_class = ( !empty( $notice_opts['overlay'] ) ? 'fupi_notice_gradient_overlay' : '' );
$fupi_version = '';
$fupi_campaignName = 'free_link';
// DEFAULT TEXTS
$default_texts = [
    'notif_h'           => '',
    'notif_descr'       => esc_html__( 'We use cookies to provide you with the best browsing experience, personalize content of our site, analyse its traffic and show you relevant ads. See our {{privacy policy}} for more information.', 'full-picture-analytics-cookie-notice' ),
    'stats_only'        => esc_html__( 'I only agree to statistics', 'full-picture-analytics-cookie-notice' ),
    'agree'             => esc_html__( 'Agree', 'full-picture-analytics-cookie-notice' ),
    'ok'                => esc_html__( 'I understand', 'full-picture-analytics-cookie-notice' ),
    'decline'           => esc_html__( 'Decline', 'full-picture-analytics-cookie-notice' ),
    'cookie_settings'   => esc_html__( 'Settings', 'full-picture-analytics-cookie-notice' ),
    'agree_to_selected' => esc_html__( 'Agree to selected', 'full-picture-analytics-cookie-notice' ),
    'return'            => esc_html__( 'Return', 'full-picture-analytics-cookie-notice' ),
    'close'             => esc_html__( 'Close', 'full-picture-analytics-cookie-notice' ),
    'necess_h'          => '',
    'necess_descr'      => '',
    'stats_h'           => esc_html__( 'Statistics', 'full-picture-analytics-cookie-notice' ),
    'stats_descr'       => esc_html__( 'I want to help you make this site better so I will provide you with data about my use of this site.', 'full-picture-analytics-cookie-notice' ),
    'pers_h'            => esc_html__( 'Personalisation', 'full-picture-analytics-cookie-notice' ),
    'pers_descr'        => esc_html__( 'I want to have the best experience on this site so I agree to saving my choices, recommending things I may like and modifying the site to my liking', 'full-picture-analytics-cookie-notice' ),
    'market_h'          => esc_html__( 'Marketing', 'full-picture-analytics-cookie-notice' ),
    'market_descr'      => esc_html__( 'I want to see ads with your offers, coupons and exclusive deals rather than random ads from other advertisers.', 'full-picture-analytics-cookie-notice' ),
];
// CURRENT TEXTS
$current_texts = [
    'notif_h'           => ( !empty( $notice_opts['notif_headline_text'] ) ? esc_html( $notice_opts['notif_headline_text'] ) : $default_texts['notif_h'] ),
    'agree'             => ( !empty( $notice_opts['agree_text'] ) ? esc_html( $notice_opts['agree_text'] ) : $default_texts['agree'] ),
    'stats_only'        => ( !empty( $notice_opts['stats_only_text'] ) ? esc_html( $notice_opts['stats_only_text'] ) : $default_texts['stats_only'] ),
    'ok'                => ( !empty( $notice_opts['ok_text'] ) ? esc_html( $notice_opts['ok_text'] ) : $default_texts['ok'] ),
    'decline'           => ( !empty( $notice_opts['decline_text'] ) ? esc_html( $notice_opts['decline_text'] ) : $default_texts['decline'] ),
    'close'             => ( !empty( $notice_opts['close_text'] ) ? esc_html( $notice_opts['close_text'] ) : $default_texts['close'] ),
    'cookie_settings'   => ( !empty( $notice_opts['cookie_settings_text'] ) ? esc_html( $notice_opts['cookie_settings_text'] ) : $default_texts['cookie_settings'] ),
    'agree_to_selected' => ( !empty( $notice_opts['agree_to_selected_text'] ) ? esc_html( $notice_opts['agree_to_selected_text'] ) : $default_texts['agree_to_selected'] ),
    'return'            => ( !empty( $notice_opts['return_text'] ) ? esc_html( $notice_opts['return_text'] ) : $default_texts['return'] ),
    'necess_h'          => ( !empty( $notice_opts['necess_headline_text'] ) ? esc_html( $notice_opts['necess_headline_text'] ) : '' ),
    'stats_h'           => ( !empty( $notice_opts['stats_headline_text'] ) ? esc_html( $notice_opts['stats_headline_text'] ) : $default_texts['stats_h'] ),
    'pers_h'            => ( !empty( $notice_opts['pers_headline_text'] ) ? esc_html( $notice_opts['pers_headline_text'] ) : $default_texts['pers_h'] ),
    'market_h'          => ( !empty( $notice_opts['marketing_headline_text'] ) ? esc_html( $notice_opts['marketing_headline_text'] ) : $default_texts['market_h'] ),
    'notif_descr'       => ( !empty( $notice_opts['notif_text'] ) ? fupi_modify_cons_banner_text( $notice_opts['notif_text'] ) : fupi_modify_cons_banner_text( $default_texts['notif_descr'] ) ),
    'necess_descr'      => ( !empty( $notice_opts['necess_text'] ) ? fupi_modify_cons_banner_text( $notice_opts['necess_text'] ) : '' ),
    'stats_descr'       => ( !empty( $notice_opts['stats_text'] ) ? fupi_modify_cons_banner_text( $notice_opts['stats_text'] ) : $default_texts['stats_descr'] ),
    'pers_descr'        => ( !empty( $notice_opts['pers_text'] ) ? fupi_modify_cons_banner_text( $notice_opts['pers_text'] ) : $default_texts['pers_descr'] ),
    'market_descr'      => ( !empty( $notice_opts['marketing_text'] ) ? fupi_modify_cons_banner_text( $notice_opts['marketing_text'] ) : $default_texts['market_descr'] ),
];
// CLASSES & DATA ATTR
$notice_position = ( !empty( $notice_opts['position'] ) ? esc_attr( $notice_opts['position'] ) : 'popup' );
$notice_position_inform = ( !empty( $notice_opts['position_inform'] ) ? esc_attr( $notice_opts['position_inform'] ) : 'bottom' );
$notice_paddings = ( !empty( $notice_opts['paddings'] ) ? esc_attr( $notice_opts['paddings'] ) : 'default' );
$btn_config = ( !empty( $notice_opts['btn_config'] ) ? esc_attr( $notice_opts['btn_config'] ) : 'config_1' );
$btn_class = ( !empty( $notice_opts['btn_class'] ) ? esc_attr( $notice_opts['btn_class'] ) : '' );
$cta_class = ( !empty( $notice_opts['cta_class'] ) ? esc_attr( $notice_opts['cta_class'] ) : '' );
$necess_headline_class = ( empty( $current_texts['necess_h'] ) ? 'fupi_hidden' : '' );
$necess_descr_class = ( empty( $current_texts['necess_descr'] ) ? 'fupi_hidden' : '' );
$necess_sect_class = ( empty( $current_texts['necess_h'] ) && empty( $current_texts['necess_descr'] ) ? 'fupi_hidden' : '' );
ob_start();
// GENERATE CSS
$border_style_val = get_theme_mod( 'fupi_cookie_notice_border' );
$border_style = ( !empty( $border_style_val ) ? esc_attr( $border_style_val ) : 'small_shadow' );
switch ( $border_style ) {
    case 'small_shadow':
        $panel_box_shadow = '2px 3px 7px rgba(0,0,0,.2)';
        $panel_border_width = '0px';
        break;
    case 'large_shadow':
        $panel_box_shadow = '5px 7px 17px rgba(0,0,0,.2)';
        $panel_border_width = '0px';
        break;
    case 'thin_border':
        $panel_box_shadow = 'none';
        $panel_border_width = '1px;';
        break;
    case 'wide_border':
        $panel_box_shadow = 'none';
        $panel_border_width = '4px;';
        break;
    default:
        $panel_border_width = '0px';
        $panel_box_shadow = 'none';
        break;
}
// Panel bg colors
$panel_bg_color_val = get_theme_mod( 'fupi_notice_bg_color', '#fff' );
$panel_bg_color = ( !empty( $panel_bg_color_val ) ? esc_attr( $panel_bg_color_val ) : '#fff' );
// Panel round corners
$panel_round_corners_val = get_theme_mod( 'fupi_notice_round_corners' );
if ( isset( $panel_round_corners_val ) ) {
    $panel_round_corners = ( empty( $panel_round_corners_val ) ? '16px' : esc_attr( $panel_round_corners_val ) . 'px' );
} else {
    $panel_round_corners = '16px';
}
// Panel border color
$panel_border_color_val = get_theme_mod( 'fupi_notice_border_color' );
$panel_border_color = ( !empty( $panel_border_color_val ) ? esc_attr( $panel_border_color_val ) : '#ccc' );
// H size
$h_size_val = get_theme_mod( 'fupi_cookie_notice_h_font_size' );
if ( isset( $h_size_val ) ) {
    $h_size = ( empty( $h_size_val ) ? '20px' : esc_attr( $h_size_val ) . 'px' );
} else {
    $h_size = '20px';
}
// H size - mobile
$h_size_mobile_val = get_theme_mod( 'fupi_cookie_notice_h_font_size_mobile' );
if ( isset( $h_size_mobile_val ) ) {
    $h_size_mobile = ( empty( $h_size_mobile_val ) ? '20px' : esc_attr( $h_size_mobile_val ) . 'px' );
} else {
    $h_size_mobile = '20px';
}
// H color
$h_color_val = get_theme_mod( 'fupi_notice_h_color' );
$h_color = ( !empty( $h_color_val ) ? esc_attr( $h_color_val ) : '#333' );
// P size
$p_size_val = get_theme_mod( 'fupi_cookie_notice_p_font_size' );
if ( isset( $p_size_val ) ) {
    $p_size = ( empty( $p_size_val ) ? '16px' : esc_attr( $p_size_val ) . 'px' );
} else {
    $p_size = '16px';
}
// P size - mobile
$p_size_val_mobile = get_theme_mod( 'fupi_cookie_notice_p_font_size_mobile' );
if ( isset( $p_size_val_mobile ) ) {
    $p_size_mobile = ( empty( $p_size_val_mobile ) ? '14px' : esc_attr( $p_size_val_mobile ) . 'px' );
} else {
    $p_size_mobile = '14px';
}
// P color
$p_color_val = get_theme_mod( 'fupi_notice_text_color' );
$p_color = ( !empty( $p_color_val ) ? esc_attr( $p_color_val ) : '#555' );
// BUTTON GAPS
$btn_gaps_theme_mod = get_theme_mod( 'fupi_cookie_notice_btns_gaps' );
$btn_gaps_val = ( !empty( $btn_gaps_theme_mod ) ? $btn_gaps_theme_mod : $notice_paddings );
$btn_gaps = ( !empty( $btn_gaps_val ) ? esc_attr( $btn_gaps_val ) : 'spacious' );
// POPUP MAX WIDTH
$popup_max_width_val = get_theme_mod( 'fupi_notice_popup_width' );
if ( isset( $popup_max_width_val ) ) {
    $popup_max_width = ( empty( $popup_max_width_val ) ? '700px' : esc_attr( $popup_max_width_val ) . 'px' );
} else {
    $popup_max_width = '700px';
}
// Btn round corners
$btn_round_corners_val = get_theme_mod( 'fupi_notice_btn_round_corners' );
if ( isset( $btn_round_corners_val ) ) {
    $btn_round_corners = ( empty( $btn_round_corners_val ) ? '8px' : esc_attr( $btn_round_corners_val ) . 'px' );
} else {
    $btn_round_corners = '8px';
}
// Btn size
$btn_size_val = get_theme_mod( 'fupi_cookie_notice_size' );
// "fupi_cookie_notice_size" is really the current name
$btn_size = ( !empty( $btn_size_val ) ? esc_attr( $btn_size_val ) : 'large' );
// Btn txt size
$btn_txt_size_val = get_theme_mod( 'fupi_cookie_notice_button_font_size' );
if ( isset( $btn_txt_size_val ) ) {
    $btn_txt_size = ( empty( $btn_txt_size_val ) ? '16px' : esc_attr( $btn_txt_size_val ) . 'px' );
} else {
    $btn_txt_size = '16px';
}
// Btn txt size - mobile
$btn_txt_size_val_mobile = get_theme_mod( 'fupi_cookie_notice_button_font_size' );
if ( isset( $btn_txt_size_val_mobile ) ) {
    $btn_txt_size_mobile = ( empty( $btn_txt_size_val_mobile ) ? '14px' : esc_attr( $btn_txt_size_val_mobile ) . 'px' );
} else {
    $btn_txt_size_mobile = '14px';
}
// Btn text color
$btn_txt_color_val = get_theme_mod( 'fupi_notice_btn_txt_color' );
$btn_txt_color = ( !empty( $btn_txt_color_val ) ? esc_attr( $btn_txt_color_val ) : '#111' );
// Btn text color (hover)
$btn_txt_color_hover_val = get_theme_mod( 'fupi_notice_btn_txt_color_hover' );
$btn_txt_color_hover = ( !empty( $btn_txt_color_hover_val ) ? esc_attr( $btn_txt_color_hover_val ) : '#111' );
// Btn bg color
$btn_bg_color_val = get_theme_mod( 'fupi_notice_btn_color' );
$btn_bg_color = ( !empty( $btn_bg_color_val ) ? esc_attr( $btn_bg_color_val ) : '#dfdfdf' );
// Btn bg color (hover)
$btn_hover_color_val = get_theme_mod( 'fupi_notice_btn_color_hover' );
$btn_hover_color = ( !empty( $btn_hover_color_val ) ? esc_attr( $btn_hover_color_val ) : '#e9e9e9' );
// CTA text color
$cta_txt_color_val = get_theme_mod( 'fupi_notice_cta_txt_color' );
$cta_txt_color = ( !empty( $cta_txt_color_val ) ? esc_attr( $cta_txt_color_val ) : '#fff' );
// CTA text color (hover)
$cta_txt_color_hover_val = get_theme_mod( 'fupi_notice_cta_txt_color_hover' );
$cta_txt_color_hover = ( !empty( $cta_txt_color_hover_val ) ? esc_attr( $cta_txt_color_hover_val ) : '#fff' );
// CTA bg color
$cta_bg_color_val = get_theme_mod( 'fupi_notice_cta_color' );
$cta_bg_color = ( !empty( $cta_bg_color_val ) ? esc_attr( $cta_bg_color_val ) : '#222' );
// CTA bg color (hover)
$cta_bg_color_hover_val = get_theme_mod( 'fupi_notice_cta_color_hover' );
$cta_bg_color_hover = ( !empty( $cta_bg_color_hover_val ) ? esc_attr( $cta_bg_color_hover_val ) : '#555' );
// Slider color
$slider_color_val = get_theme_mod( 'fupi_notice_switch_color' );
$slider_color = ( !empty( $slider_color_val ) ? esc_attr( $slider_color_val ) : '#249dc1' );
// Necessary slider color
$necessary_slider_color_val = get_theme_mod( 'fupi_notice_necessary_switch_color' );
$necessary_slider_color = ( !empty( $necessary_slider_color_val ) ? esc_attr( $necessary_slider_color_val ) : '#68909b' );
// TOGGLER BG COLOR
$toggler_bg_color_val = get_theme_mod( 'fupi_toggler_bg_color' );
$toggler_bg_color = ( !empty( $toggler_bg_color_val ) ? esc_attr( $toggler_bg_color_val ) : '#6190c6' );
echo "\n" . '<style id="fupi_cookie_css">
	body{
		--fupi-notice-panel-bg-color: ' . $panel_bg_color . ';
		--fupi-notice-panel-round-corners:  ' . $panel_round_corners . ';
		--fupi-notice-panel-box-shadow: ' . $panel_box_shadow . ';
		--fupi-notice-panel-border-width: ' . $panel_border_width . ';
		--fupi-notice-panel-border-color: ' . $panel_border_color . '; 
		--fupi-notice-txt-color: ' . $p_color . ';
		--fupi-notice-btn-gaps: ' . $btn_gaps . ';
		--fupi-notice-p-size: ' . $p_size . ';
		--fupi-notice-p-size-mobile: ' . $p_size_mobile . ';
		--fupi-notice-h-color: ' . $h_color . ';
		--fupi-notice-h-size: ' . $h_size . ';
		--fupi-notice-h-size-mobile: ' . $h_size_mobile . ';
		--fupi-notice-btn-round-corners:  ' . $btn_round_corners . ';
		--fupi-notice-btn-txt-size: ' . $btn_txt_size . ';
		--fupi-notice-btn-txt-size-mobile: ' . $btn_txt_size_mobile . ';
		--fupi-notice-btn-bg-color: ' . $btn_bg_color . ';
		--fupi-notice-btn-bg-color-hover: ' . $btn_hover_color . ';
		--fupi-notice-btn-text-color: ' . $btn_txt_color . ';
		--fupi-notice-btn-text-color-hover: ' . $btn_txt_color_hover . ';
		--fupi-notice-cta-bg-color: ' . $cta_bg_color . ';
		--fupi-notice-cta-bg-color-hover: ' . $cta_bg_color_hover . ';
		--fupi-notice-cta-txt-color: ' . $cta_txt_color . ';
		--fupi-notice-cta-txt-color-hover: ' . $cta_txt_color_hover . ';
		--fupi-notice-slider-color: ' . $slider_color . ';
		--fupi-notice-necessary-slider-color: ' . $necessary_slider_color . ';
		--fupi-notice-slider-focus-shadow: 0 0 4px ' . $slider_color . ';
		--fupi-notice-popup-panel-max-width: ' . $popup_max_width . ';
		--fupi-notice-toggler-bg-color: ' . $toggler_bg_color . ';
	}
</style>' . "\n";
if ( is_customize_preview() ) {
    echo '<style>
	.fupi_tooltip {
		position: relative;
	}
	.fupi_tooltiptext {
		position: absolute;
		z-index: 1;
		width: 180px;
		left: 0;
		top: -60px;
		white-space: break-spaces;
		visibility: hidden;
		background-color: #114e80;
		color: #e2f3ff;
		line-height: 1.3;
		font-weight: normal;
		font-size: 12px;
		text-align: left;
		padding: 5px 10px;
		border-radius: 6px;	
	}
	.fupi_tooltiptext:after {
		top: 100%;
		left: 26px;
		border: solid transparent;
		content: "";
		height: 0;
		width: 0;
		position: absolute;
		pointer-events: none;
		border-top-color: #114e80;
		border-width: 10px;
		margin-left: -10px;
	}
	.fupi_tooltip:hover .fupi_tooltiptext{
		visibility: visible;
	}
	</style>
	<script id="fupi_default_texts">
		const fupi_default_texts = ' . json_encode( $default_texts ) . ';
	</script>';
}
// get tag
$tag_val = get_theme_mod( 'fupi_cookie_notice_heading_tag' );
$tag = ( !empty( $tag_val ) ? esc_attr( $tag_val ) : 'p' );
$notif_headline = ( !empty( $current_texts['notif_h'] ) ? '<' . $tag . ' id="fupi_main_headline" class="fupi_headline">' . $current_texts['notif_h'] . '</' . $tag . '>' : '' );
// GENERATE HTML
echo '<!-- WP Full Picture - Consent Banner & Analytics - START -->
<aside id="fupi_cookie_notice" class="fupi_hidden ' . $overlay_class . ' fupi_notice_btn_' . $btn_size . '" style="display: none;" data-position="' . $notice_position . '" data-position_inform="' . $notice_position_inform . '" data-paddings="' . $notice_paddings . '" data-btn_gaps="' . $btn_gaps . '" data-btn_config="' . $btn_config . '" data-headlinetag="' . $tag . '">
	<div id="fupi_welcome_panel" class="fupi_panel fupi_hidden" role="dialog" aria-label="' . esc_attr__( 'Consent banner', 'full-picture-analytics-cookie-notice' ) . '" aria-modal="true" aria-describedby="fupi_main_descr">
		<div class="fupi_inner">
			<div class="fupi_content">' . $notif_headline . '
				<p id="fupi_main_descr" class="fupi_cookietype_descr">' . $current_texts['notif_descr'] . '</p>
			</div>
			<div class="fupi_buttons">
				<button type="button" id="fupi_agree_to_all_cookies_btn" data-classes="fupi_cta" class="fupi_cta ' . $cta_class . '"><span id="fupi_agree_text">' . $current_texts['agree'] . '</span><span id="fupi_ok_text">' . $current_texts['ok'] . '</span></button>
				<button type="button" id="fupi_stats_only_btn" data-classes="fupi_button" class="fupi_button ' . $btn_class . '">' . $current_texts['stats_only'] . '</button>
				<button type="button" id="fupi_cookie_settings_btn" data-classes="fupi_button" class="fupi_button ' . $btn_class . '">' . $current_texts['cookie_settings'] . '</button>
				<button type="button" id="fupi_decline_cookies_btn" data-classes="fupi_button" class="fupi_button ' . $btn_class . '">' . $current_texts['decline'] . '</button>
				<button type="button" data-classes="fupi_button" class="fupi_close_banner_btn fupi_button fupi_hidden ' . $btn_class . '">' . $current_texts['close'] . '</button>
			</div>
			<div class="fupi_consent_info fupi_hidden">
				<span class="fupi_consent_id"></span><br><span class="fupi_consent_date"></span>
			</div>
			<p class="fupi_poweredBy">Powered by <a class="fupi_poweredBy_link" href="https://wpfullpicture.com/?utm_source=usersite&utm_medium=poweredby&utm_campaign=' . $fupi_campaignName . '" rel="nofollow noopener">WP Full Picture ' . $fupi_version . '</a></p>
		</div>
	</div>';
if ( is_customize_preview() || (!$hidden_elements || $hidden_elements && !in_array( 'settings_btn', $hidden_elements )) ) {
    echo '<div id="fupi_settings_panel" class="fupi_panel fupi_fadeOutDown" role="dialog" aria-label="' . esc_attr__( 'Settings', 'full-picture-analytics-cookie-notice' ) . '" aria-modal="true" aria-describedby="fupi_notice_settings_content">
			<div class="fupi_inner">
				<div id="fupi_notice_settings_content" class="fupi_content">
					<div id="fupi_necess_section" class="fupi_section ' . $necess_sect_class . '">
						<' . $tag . ' id="fupi_necess_headline" class="fupi_headline ' . $necess_headline_class . '">' . $current_texts['necess_h'] . '</' . $tag . '>
						<label id="fupi_necess_switch" class="fupi_faux_switch ' . $necess_headline_class . '">
							<span class="fupi_faux_slider fupi_switch_slider_enabled" aria-hidden="true"></span>
						</label>
						<div id="fupi_necess_descr" class="fupi_cookietype_descr ' . $necess_descr_class . '">' . $current_texts['necess_descr'] . '</div>
					</div>
					<div id="fupi_stats_section" class="fupi_section">
						<' . $tag . ' id="fupi_stats_headline" class="fupi_headline">' . $current_texts['stats_h'] . '</' . $tag . '>
						<label class="fupi_switch">
							<span class="fupi_srt">' . $current_texts['stats_h'] . '</span>
							<input id="fupi_stats_agree" name="fupi_stats_agree" value="stats" type="checkbox" role="switch"/>
							<span class="fupi_switch_slider" aria-hidden="true"></span>
						</label>
						<div id="fupi_stats_descr" class="fupi_cookietype_descr">' . $current_texts['stats_descr'] . '</div>
					</div>
					<div id="fupi_pers_section" class="fupi_section">
						<' . $tag . ' id="fupi_pers_headline" class="fupi_headline">' . $current_texts['pers_h'] . '</' . $tag . '>
						<label class="fupi_switch">
							<span class="fupi_srt">' . $current_texts['pers_h'] . '</span>
							<input id="fupi_pers_agree" name="fupi_pers_agree" value="personalisation" type="checkbox" role="switch"/>
							<span class="fupi_switch_slider" aria-hidden="true"></span>
						</label>
						<div id="fupi_pers_descr" class="fupi_cookietype_descr">' . $current_texts['pers_descr'] . '</div>
					</div>
					<div id="fupi_market_section" class="fupi_section">
						<' . $tag . ' id="fupi_market_headline" class="fupi_headline">' . $current_texts['market_h'] . '</' . $tag . '>
						<label class="fupi_switch">
							<span class="fupi_srt">' . $current_texts['market_h'] . '</span>
							<input id="fupi_marketing_agree" name="fupi_marketing_agree" value="marketing" type="checkbox" role="switch"/>
							<span class="fupi_switch_slider" aria-hidden="true"></span>
						</label>
						<div id="fupi_market_descr" class="fupi_cookietype_descr">' . $current_texts['market_descr'] . '</div>
					</div>
				</div>
				<div class="fupi_buttons">
					<button type="button" id="fupi_agree_to_selected_cookies_btn" data-classes="fupi_cta" class="fupi_cta ' . $cta_class . '">' . $current_texts['agree_to_selected'] . '</button>
					<button type="button" id="fupi_return_btn" data-classes="fupi_button" class="fupi_button ' . $btn_class . '">' . $current_texts['return'] . '</button>
					<button type="button" data-classes="fupi_button" class="fupi_close_banner_btn fupi_button fupi_hidden ' . $btn_class . '">' . $current_texts['close'] . '</button>
				</div>
				<div class="fupi_consent_info fupi_hidden">
					<span class="fupi_consent_id"></span><br><span class="fupi_consent_date"></span>
				</div>
				<p class="fupi_poweredBy">Powered by <a class="fupi_poweredBy_link" href="https://wpfullpicture.com/?utm_source=usersite&utm_medium=poweredby&utm_campaign=' . $fupi_campaignName . '" rel="nofollow noopener">WP Full Picture ' . $fupi_version . '</a></p>
			</div>
		</div>';
}
echo '</aside>';
// TOGGLER
$is_mode_notify = false;
if ( isset( $this->tools['geo'] ) ) {
    if ( isset( $this->settings['mode'] ) && $this->settings['mode'] === 'notify' ) {
        $is_mode_notify = true;
    }
} else {
    if ( isset( $this->settings['enable_scripts_after'] ) && $this->settings['enable_scripts_after'] === 'notify' ) {
        $is_mode_notify = true;
    }
}
if ( !$is_mode_notify && (is_customize_preview() || isset( $notice_opts ) && !empty( $notice_opts['enable_toggle_btn'] )) ) {
    $image_file_id = get_theme_mod( 'fupi_custom_toggler_img' );
    $image_file_src = wp_get_attachment_image_url( $image_file_id );
    $img_src = ( !empty( $image_file_src ) ? $image_file_src : FUPI_URL . 'public/modules/cook/img/fupi_cookie_ico.png' );
    $toggler_class = ( empty( $notice_opts['enable_toggle_btn'] ) ? '' : 'fupi_animated fupi_fadeInUp' );
    $toggler_tooltip = '';
    if ( is_customize_preview() ) {
        $toggler_class = ( empty( $notice_opts['enable_toggle_btn'] ) ? '' : 'fupi_active fupi_animated fupi_fadeInUp ' );
        $toggler_tooltip = '<span class="fupi_tooltiptext">' . esc_html__( 'This button is only active on the live site', 'full-picture-analytics-cookie-notice' ) . '</span>';
    }
    echo '<aside><button id="fupi_notice_toggler" class="fp_show_cookie_notice fupi_tooltip ' . $toggler_class . '" style="display: none;"><span class="fupi_srt">' . esc_attr__( 'Change cookie preferences', 'full-picture-analytics-cookie-notice' ) . '</span><img src="' . $img_src . '">' . $toggler_tooltip . '</button></aside>';
}
echo '<!-- WP Full Picture - Consent Banner & Analytics - END -->';
ob_end_flush();