<?php

$cook = get_option( 'fupi_cook' );
$tools = get_option( 'fupi_tools' );
$priv_policy_url = get_privacy_policy_url();
$priv_policy_url_text = ( empty( $priv_policy_url ) ? ' <span style="color: red;">' . esc_html__( 'Privacy policy page is not set! Please set it in Settings > Privacy.', 'full-picture-analytics-cookie-notice' ) . '</span>' : '' );
// CHECK IF IS PREMIUM
$is_premium = false;
// CHECK IF BANNER ONLY NOTIFIES
$banner_only_notifies = false;
$hide_when_banner_only_notifies = '__return_true';
if ( isset( $tools['geo'] ) && $is_premium ) {
    if ( !empty( $cook ) && isset( $cook['mode'] ) && $cook['mode'] === 'notify' ) {
        $hide_when_banner_only_notifies = '__return_false';
        // triggers a function which returns false which hides the field
        $banner_only_notifies = true;
    }
} else {
    if ( !empty( $cook ) && isset( $cook['enable_scripts_after'] ) && $cook['enable_scripts_after'] === 'notify' ) {
        $hide_when_banner_only_notifies = '__return_false';
        // triggers a function which returns false which hides the field
        $banner_only_notifies = true;
    }
}
// CUSTOM CONTROLS
if ( class_exists( 'WP_Customize_Control' ) ) {
    // HTML ( no control )
    class FUPI_Customize_Pure_HTML extends WP_Customize_Control {
        public $type = 'faux_preview_selector';

        public function render_content() {
            $cook = get_option( 'fupi_cook' );
            $tools = get_option( 'fupi_tools' );
            $preview_ver = "opt_in_out";
            // default
            if ( !empty( $cook ) && isset( $cook['enable_scripts_after'] ) && $cook['enable_scripts_after'] === 'notify' ) {
                $preview_ver = "notify";
            }
            echo '<span class="fupi_notice_mode" data-preview_ver="' . $preview_ver . '"></span>
			<span class="customize-control-title">' . esc_html__( 'Preview and style the notice which:', 'full-picture-analytics-cookie-notice' ) . '</span>
			<span class="description customize-control-description">' . esc_html__( 'Depending on the location of your visitors, some of them will see the first and some the second type of the notice.', 'full-picture-analytics-cookie-notice' ) . '<br><br>' . esc_html__( 'To make customisation easier, settings that do not apply to the currently chosen type will be disabled.', 'full-picture-analytics-cookie-notice' ) . '
			</span>
			<span class="customize-inside-control-row">
				<img class="fupi_preview_img fupi_opt_in_out_preview fupi_active" src="' . FUPI_URL . 'admin/modules/cook/customizer/imgs/notice-optin.png"/>
				<span class="fupi_faux_label">' . esc_html__( 'allows visitors to decline cookies / tracking', 'full-picture-analytics-cookie-notice' ) . '</span>
			</span>
			<span class="customize-inside-control-row">
				<img class="fupi_preview_img fupi_notify_preview" src="' . FUPI_URL . 'admin/modules/cook/customizer/imgs/notice-inform.png"/>
				<span class="fupi_faux_label">' . esc_html__( 'doesn\'t let visitors decline cookies / tracking', 'full-picture-analytics-cookie-notice' ) . '</span>
			</span>';
        }

    }

    // MULTI CHECKBOX
    class FUPI_Customize_Multi_Checkbox extends WP_Customize_Control {
        public $type = 'multi_checkbox';

        public function render_content() {
            if ( empty( $this->choices ) ) {
                return;
            }
            ?>

			<?php 
            if ( !empty( $this->label ) ) {
                ?>
				<span class="customize-control-title"><?php 
                echo esc_html( $this->label );
                ?></span>
			<?php 
            }
            ?>

			<?php 
            if ( !empty( $this->description ) ) {
                ?>
				<span class="description customize-control-description"><?php 
                echo $this->description;
                ?></span>
			<?php 
            }
            ?>

			<?php 
            $multi_values = ( !is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value() );
            ?>

			<ul>
				<?php 
            foreach ( $this->choices as $value => $label ) {
                ?>

					<li>
						<label>
							<input type="checkbox" value="<?php 
                echo esc_attr( $value );
                ?>" <?php 
                checked( in_array( $value, $multi_values ) );
                ?> />
							<?php 
                echo esc_attr( $label );
                ?>
						</label>
					</li>

				<?php 
            }
            ?>
			</ul>

			<input type="hidden" class="fupi_control_multi_checkbox" <?php 
            $this->link();
            ?> value="<?php 
            echo esc_attr( implode( ',', $multi_values ) );
            ?>" />
		<?php 
        }

    }

    // IMAGE SELECT
    class FUPI_Customize_Image_Select extends WP_Customize_Control {
        public $type = 'image_select';

        public function render_content() {
            if ( empty( $this->choices ) ) {
                return;
            }
            ?>

			<?php 
            if ( !empty( $this->label ) ) {
                ?>
				<span class="customize-control-title"><?php 
                echo esc_html( $this->label );
                ?></span>
			<?php 
            }
            ?>

			<?php 
            if ( !empty( $this->description ) ) {
                ?>
				<span class="description customize-control-description"><?php 
                echo $this->description;
                ?></span>
			<?php 
            }
            ?>

			<?php 
            $multi_values = ( !is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value() );
            ?>

			<ul>
				<?php 
            foreach ( $this->choices as $value => $label ) {
                ?>

					<li>
						<label>
							<input type="checkbox" value="<?php 
                echo esc_attr( $value );
                ?>" <?php 
                checked( in_array( $value, $multi_values ) );
                ?> />
							<?php 
                echo esc_attr( $label );
                ?>
						</label>
					</li>

				<?php 
            }
            ?>
			</ul>

			<input type="hidden" class="fupi_control_multi_checkbox" <?php 
            $this->link();
            ?> value="<?php 
            echo esc_attr( implode( ',', $multi_values ) );
            ?>" />
		<?php 
        }

    }

}
// PANEL
$wp_customize->add_panel( 'fupi_notice', array(
    'priority'   => 160,
    'capability' => 'edit_theme_options',
    'title'      => esc_html__( 'Consent Banner', 'full-picture-analytics-cookie-notice' ),
) );
// SECTIONS
$wp_customize->add_section( 'fupi_notice_place', array(
    'title'           => esc_html__( 'Layout & Behaviour (start here)', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => '',
    'panel'           => 'fupi_notice',
) );
$wp_customize->add_section( 'fupi_notice_design', array(
    'title'           => esc_html__( 'Styling', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => '',
    'panel'           => 'fupi_notice',
) );
$wp_customize->add_section( 'fupi_notice_typogr', array(
    'title'           => esc_html__( 'Typography', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => '',
    'panel'           => 'fupi_notice',
) );
$wp_customize->add_section( 'fupi_notice_texts', array(
    'title'           => esc_html__( 'Text content', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => '',
    'panel'           => 'fupi_notice',
) );
$wp_customize->add_section( 'fupi_notice_toggler', array(
    'title'           => esc_html__( 'Button opening the consent banner', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => '',
    'panel'           => 'fupi_notice',
) );
//
// PLACE PANEL
//
/*
IMPORTANT INSTRUCTIONS 
	- ACTIVE CALLBACKS set the initial visibility of the field when the customizer is loaded. After load, the visibility od fields is only controlled by fupi-customizer-controls.js.
	- elements of the consent banner that should not change after switching themes are saved as options. Elements which should change (changing the looks) are saved as theme mods.
*/
// PREVIEW
$wp_customize->add_setting( 'fupi_cookie_notice[active_preview]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 'opt_in_out',
) );
$wp_customize->add_control( 'fupi_cookie_notice[active_preview]', array(
    'label'           => esc_html__( 'Preview and style the notice which:', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_place',
    'type'            => 'radio',
    'choices'         => array(
        'opt_in_out' => esc_html__( 'allows visitors to decline cookies / tracking', 'full-picture-analytics-cookie-notice' ),
        'notify'     => esc_html__( 'doesn\'t let visitors decline cookies / tracking', 'full-picture-analytics-cookie-notice' ),
    ),
    'default'         => 'opt_in_out',
    'description'     => esc_html__( 'Depending on the location of your visitors, some of them will see the first and some the second type of the notice.', 'full-picture-analytics-cookie-notice' ) . '<br><br>' . esc_html__( 'To make customisation easier, settings that do not apply to the currently chosen type will be disabled.', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => function ( $control ) use($tools, $cook, $is_premium) {
        if ( !$is_premium ) {
            return false;
        }
        // This field shows only when the geo is enabled and the mode is either "auto_strict", "auto_lax", "manual" or not set (default to "auto" on front-end)
        return isset( $tools['geo'] ) && (empty( $cook ) || (!isset( $cook['mode'] ) || ($cook['mode'] === 'auto_strict' || $cook['mode'] === 'auto_lax' || $cook['mode'] === 'manual')));
    },
) );
// POSITION (ALL BUTTONS)
$wp_customize->add_setting( 'fupi_cookie_notice[position]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 'popup',
) );
$wp_customize->add_control( 'fupi_cookie_notice[position]', array(
    'label'           => esc_html__( 'Position', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_place',
    'type'            => 'radio',
    'choices'         => array(
        'popup'       => esc_html__( 'Center (recommended)', 'full-picture-analytics-cookie-notice' ),
        'bottom'      => esc_html__( 'Bottom - Narrow', 'full-picture-analytics-cookie-notice' ),
        'bottom_wide' => esc_html__( 'Bottom - Wide', 'full-picture-analytics-cookie-notice' ),
        'bottom_full' => esc_html__( 'Bottom - Very Wide', 'full-picture-analytics-cookie-notice' ),
        'leftcorner'  => esc_html__( 'Bottom Left', 'full-picture-analytics-cookie-notice' ),
    ),
    'description'     => esc_html__( 'Central position is highly recommended to increase the number of consents. On small screens, the notice will show as a narrow box at the bottom of the screen.', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => $hide_when_banner_only_notifies,
) );
// POSITION (ONLY NOTIFY)
$wp_customize->add_setting( 'fupi_cookie_notice[position_inform]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 'bottom',
) );
$wp_customize->add_control( 'fupi_cookie_notice[position_inform]', array(
    'label'           => esc_html__( 'Position', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_place',
    'type'            => 'radio',
    'choices'         => array(
        'bottom'      => esc_html__( 'Bottom - Narrow', 'full-picture-analytics-cookie-notice' ),
        'bottom_wide' => esc_html__( 'Bottom - Wide', 'full-picture-analytics-cookie-notice' ),
        'bottom_full' => esc_html__( 'Bottom - Very Wide', 'full-picture-analytics-cookie-notice' ),
        'leftcorner'  => esc_html__( 'Bottom Left', 'full-picture-analytics-cookie-notice' ),
    ),
    'description'     => esc_html__( 'On small screens, the notice will show as a narrow box at the bottom of the screen.', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => function ( $control ) use($tools, $cook, $is_premium) {
        $has_cook_opts = !empty( $cook );
        $geo_enabled = isset( $tools['geo'] );
        if ( $geo_enabled && $is_premium ) {
            if ( $has_cook_opts ) {
                if ( isset( $cook['mode'] ) ) {
                    return !($cook['mode'] === 'optin' || $cook['mode'] === 'optout');
                }
            }
            return true;
            // defaults to auto_strict
        } else {
            if ( $has_cook_opts ) {
                if ( isset( $cook['enable_scripts_after'] ) ) {
                    return !($cook['enable_scripts_after'] === 'optin' || $cook['enable_scripts_after'] === 'optout');
                }
            }
            return false;
            // defaults to optin
        }
    },
) );
// OVERLAY
$wp_customize->add_setting( 'fupi_cookie_notice[overlay]', array(
    'type'              => 'option',
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[overlay]', array(
    'label'           => esc_html__( 'Add a background overlay (highly recommended)', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_place',
    'type'            => 'checkbox',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Hide when the notice only notifies
        if ( $banner_only_notifies ) {
            return false;
        }
        // when it doesn't notify, hide it when the value of position is not "popup"
        $position_value = $control->manager->get_setting( 'fupi_cookie_notice[position]' )->value();
        return $position_value == 'popup';
    },
) );
// SCROLL LOCK
$wp_customize->add_setting( 'fupi_cookie_notice[scroll_lock]', array(
    'type'              => 'option',
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[scroll_lock]', array(
    'label'           => esc_html__( 'Lock page scroll until visitors make a choice', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_place',
    'type'            => 'checkbox',
    'active_callback' => $hide_when_banner_only_notifies,
) );
// PAGE BLUR
$wp_customize->add_setting( 'fupi_cookie_notice[blur_page]', array(
    'type'              => 'option',
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[blur_page]', array(
    'label'           => esc_html__( 'Blur page until visitors make a choice', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'To prevent some elements from bluring add to them the CSS class "fupi_noblur". It works only on elements that are direct descendants of <body> tag.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_place',
    'type'            => 'checkbox',
    'active_callback' => $hide_when_banner_only_notifies,
) );
// HIDE ELEMENTS
$hide_descr = sprintf( esc_html__( 'Automatic setup of the consent banner is enabled. It will show / hide the "Decline" button depending on the visitor\'s location. %1$sLearn more%2$s', 'full-picture-analytics-cookie-notice' ), '<a href="https://wpfullpicture.com/support/documentation/setting-up-the-cookie-notice/?utm_source=fp_admin&utm_medium=fp_link" target="_blank">', '</a>' );
$hide_options = array(
    'settings_btn' => esc_html__( 'Hide "Settings" button & panel', 'full-picture-analytics-cookie-notice' ),
    'stats'        => esc_html__( 'Hide "statistics" section (in the "Settings" panel)', 'full-picture-analytics-cookie-notice' ),
    'market'       => esc_html__( 'Hide "marketing" section (in the "Settings" panel)', 'full-picture-analytics-cookie-notice' ),
    'pers'         => esc_html__( 'Hide "personalisation" section (in the "Settings" panel)', 'full-picture-analytics-cookie-notice' ),
);
if ( !isset( $tools['geo'] ) || !isset( $cook['auto_mode'] ) || $cook['auto_mode'] == 'off' ) {
    $hide_descr = esc_html__( 'You can\'t hide both "Decline" and "Settings" buttons. If you don\'t want to give your visitors an option to decline cookies / tracking then, please go to the Consent Banner settings in the admin panel and change the notice mode to "only inform".', 'full-picture-analytics-cookie-notice' );
    $decline_option = array(
        'decline_btn' => esc_html__( 'Hide "decline" button (does not comply with GDPR)', 'full-picture-analytics-cookie-notice' ),
    );
    $hide_options = array_merge( $decline_option, $hide_options );
}
$wp_customize->add_setting( 'fupi_cookie_notice[hide]', array(
    'type'              => 'option',
    'default'           => array(),
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( new FUPI_Customize_Multi_Checkbox($wp_customize, 'fupi_cookie_notice[hide]', array(
    'label'           => esc_html__( 'Hide elements', 'full-picture-analytics-cookie-notice' ),
    'description'     => $hide_descr,
    'section'         => 'fupi_notice_place',
    'choices'         => $hide_options,
    'active_callback' => $hide_when_banner_only_notifies,
)) );
// SHOW ELEMENTS
$wp_customize->add_setting( 'fupi_cookie_notice[show]', array(
    'type'              => 'option',
    'default'           => array(),
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$show_elements = array();
// Hide btn option when the notice only notifies
if ( !$banner_only_notifies ) {
    $show_elements['stats_only_btn'] = esc_html__( 'Show "Statistics only" button', 'full-picture-analytics-cookie-notice' );
}
$show_elements['powered_by'] = esc_html__( 'Show "Powered by" link', 'full-picture-analytics-cookie-notice' );
$wp_customize->add_control( new FUPI_Customize_Multi_Checkbox($wp_customize, 'fupi_cookie_notice[show]', array(
    'label'   => esc_html__( 'Show elements', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_place',
    'choices' => $show_elements,
)) );
// THE DEFAULT STATE OF SWITCHES
$wp_customize->add_setting( 'fupi_cookie_notice[switches_on]', array(
    'type'              => 'option',
    'default'           => array(),
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( new FUPI_Customize_Multi_Checkbox($wp_customize, 'fupi_cookie_notice[switches_on]', array(
    'label'           => esc_html__( 'Which switches should be pre-selected?', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_place',
    'choices'         => array(
        'stats'  => esc_html__( 'Statistics', 'full-picture-analytics-cookie-notice' ),
        'pers'   => esc_html__( 'Personalisation', 'full-picture-analytics-cookie-notice' ),
        'market' => esc_html__( 'Marketing', 'full-picture-analytics-cookie-notice' ),
    ),
    'description'     => esc_html__( 'Switches are shown after clicking the "Settings" button. These choices will apply for visitors who are tracked from the moment they enter the website.', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val );
        // Should be visible?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
)) );
// SET DEFAULT SWITCHES ALSO FOR OPT-IN
$wp_customize->add_setting( 'fupi_cookie_notice[optin_switches]', array(
    'type'              => 'option',
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[optin_switches]', array(
    'label'           => esc_html__( 'Pre-select switches also for visitors who have to agree to tracking. (Attention! It does not comply with the EU\'s privacy regulations)', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_place',
    'type'            => 'checkbox',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val );
        // Should be visible
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
//
// DESIGN PANEL
//
$wp_customize->add_setting( 'fupi_cookie_notice_faux_preview_selector1', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( new FUPI_Customize_Pure_HTML($wp_customize, 'fupi_cookie_notice_faux_preview_selector1', array(
    'type'            => 'faux_preview_selector',
    'section'         => 'fupi_notice_design',
    'content'         => '',
    'active_callback' => function ( $control ) use($tools, $cook, $is_premium) {
        if ( !$is_premium ) {
            return false;
        }
        // The same display rules as for all previewers
        return isset( $tools['geo'] ) && (empty( $cook ) || (!isset( $cook['mode'] ) || ($cook['mode'] === 'auto_strict' || $cook['mode'] === 'auto_lax' || $cook['mode'] === 'manual')));
    },
)) );
// POPUP WIDTH
$wp_customize->add_setting( 'fupi_notice_popup_width', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 700,
) );
$wp_customize->add_control( 'fupi_notice_popup_width', array(
    'label'       => esc_html__( 'Max width of the popup panel (in px)', 'full-picture-analytics-cookie-notice' ),
    'description' => esc_html__( 'Only applies to centrally-positioned popup', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_design',
    'type'        => 'number',
    'input_attrs' => array(
        'min' => 520,
    ),
) );
// PADDINGS
$wp_customize->add_setting( 'fupi_cookie_notice[paddings]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 'default',
) );
$wp_customize->add_control( 'fupi_cookie_notice[paddings]', array(
    'label'   => esc_html__( 'Paddings', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
    'type'    => 'select',
    'choices' => array(
        'default'      => esc_html__( 'Spacious (recommended)', 'full-picture-analytics-cookie-notice' ),
        'medium'       => esc_html__( 'Medium', 'full-picture-analytics-cookie-notice' ),
        'compact'      => esc_html__( 'Compact', 'full-picture-analytics-cookie-notice' ),
        'supercompact' => esc_html__( 'Super compact', 'full-picture-analytics-cookie-notice' ),
    ),
) );
// BTN ORDER
$wp_customize->add_setting( 'fupi_cookie_notice[btn_config]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 'config_1',
) );
$wp_customize->add_control( 'fupi_cookie_notice[btn_config]', array(
    'label'           => esc_html__( 'Button placement & configuration', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_design',
    'type'            => 'select',
    'choices'         => array(
        'config_1' => esc_html__( 'Inline (default)', 'full-picture-analytics-cookie-notice' ),
        'config_2' => esc_html__( 'Inline, reversed', 'full-picture-analytics-cookie-notice' ),
        'config_3' => esc_html__( 'Inline, reversed on mobile', 'full-picture-analytics-cookie-notice' ),
        'default'  => esc_html__( 'Multi-line', 'full-picture-analytics-cookie-notice' ),
    ),
    'active_callback' => $hide_when_banner_only_notifies,
) );
// BTN SIZE
$wp_customize->add_setting( 'fupi_cookie_notice_size', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 'large',
) );
$wp_customize->add_control( 'fupi_cookie_notice_size', array(
    'label'   => esc_html__( 'Button size', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
    'type'    => 'select',
    'choices' => array(
        'large'   => esc_html__( 'Large (recommended)', 'full-picture-analytics-cookie-notice' ),
        'default' => esc_html__( 'Set by your theme', 'full-picture-analytics-cookie-notice' ),
        'small'   => esc_html__( 'Small', 'full-picture-analytics-cookie-notice' ),
        'medium'  => esc_html__( 'Medium', 'full-picture-analytics-cookie-notice' ),
    ),
) );
// BTN CLASS
$wp_customize->add_setting( 'fupi_cookie_notice[btn_class]', array(
    'type'              => 'option',
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[btn_class]', array(
    'label'       => esc_html__( 'Button class (Advanced)', 'full-picture-analytics-cookie-notice' ),
    'description' => esc_html__( 'Do not add a full stop "." before the class name', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_design',
    'type'        => 'text',
) );
// CTA CLASS
$wp_customize->add_setting( 'fupi_cookie_notice[cta_class]', array(
    'type'              => 'option',
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[cta_class]', array(
    'label'       => esc_html__( 'Call-to-action button class (Advanced)', 'full-picture-analytics-cookie-notice' ),
    'description' => esc_html__( 'Do not add a full stop "." before the class name', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_design',
    'type'        => 'text',
) );
// ROUNDED CORNERS IN NOTICE
$wp_customize->add_setting( 'fupi_notice_round_corners', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_notice_round_corners', array(
    'label'       => esc_html__( 'Rounded corners of notice pannels (in px)', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_design',
    'type'        => 'number',
    'input_attrs' => array(
        'min' => 0,
    ),
) );
// ROUNDED CORNERS IN BUTTONS
$wp_customize->add_setting( 'fupi_notice_btn_round_corners', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_notice_btn_round_corners', array(
    'label'       => esc_html__( 'Rounded corners of buttons (in px)', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_design',
    'type'        => 'number',
    'input_attrs' => array(
        'min' => 0,
    ),
) );
// BANNER BG COLOR
$wp_customize->add_setting( 'fupi_notice_bg_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#fff',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_bg_color', array(
    'label'   => esc_html__( 'Background Color', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
)) );
// HEADLINE COLOR
$wp_customize->add_setting( 'fupi_notice_h_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => "#333",
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_h_color', array(
    'label'   => esc_html__( 'Headline Color', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
)) );
// TEXT COLOR
$wp_customize->add_setting( 'fupi_notice_text_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#555',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_text_color', array(
    'label'   => esc_html__( 'Text Color', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
)) );
// CTA BG COLOR
$wp_customize->add_setting( 'fupi_notice_cta_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#249dc1',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_cta_color', array(
    'label'   => esc_html__( 'Call-to-action Button Color', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
)) );
// CTA BG HOVER COLOR
$wp_customize->add_setting( 'fupi_notice_cta_color_hover', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#3ca9d8',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_cta_color_hover', array(
    'label'   => esc_html__( 'Call-to-action Button Color (Hover)', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
)) );
// CTA TEXT COLOR
$wp_customize->add_setting( 'fupi_notice_cta_txt_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#fff',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_cta_txt_color', array(
    'label'   => esc_html__( 'Call-to-action Button Text Color', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
)) );
// CTA HOVER TEXT COLOR
$wp_customize->add_setting( 'fupi_notice_cta_txt_color_hover', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#fff',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_cta_txt_color_hover', array(
    'label'   => esc_html__( 'Call-to-action Button Text Color (Hover)', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
)) );
// BTN BG COLOR
$wp_customize->add_setting( 'fupi_notice_btn_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#dfdfdf',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_btn_color', array(
    'label'           => esc_html__( 'Button Color', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_design',
    'active_callback' => $hide_when_banner_only_notifies,
)) );
// BTN HOVER BG COLOR
$wp_customize->add_setting( 'fupi_notice_btn_color_hover', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#e9e9e9',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_btn_color_hover', array(
    'label'           => esc_html__( 'Button Color (Hover)', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_design',
    'active_callback' => $hide_when_banner_only_notifies,
)) );
// BTN TEXT COLOR
$wp_customize->add_setting( 'fupi_notice_btn_txt_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#111',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_btn_txt_color', array(
    'label'           => esc_html__( 'Button Text Color', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_design',
    'active_callback' => $hide_when_banner_only_notifies,
)) );
// BTN HOVER TEXT COLOR
$wp_customize->add_setting( 'fupi_notice_btn_txt_color_hover', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#111',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_btn_txt_color_hover', array(
    'label'           => esc_html__( 'Button Text Color (Hover)', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_design',
    'active_callback' => $hide_when_banner_only_notifies,
)) );
// ENABLED SWITCHES COLOR
$wp_customize->add_setting( 'fupi_notice_switch_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#249dc1',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_switch_color', array(
    'label'           => esc_html__( 'Color of enabled switches', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Click the "Settings" button in the consent banner to see the switches.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_design',
    'active_callback' => $hide_when_banner_only_notifies,
)) );
// NECESSARY COOKIES SWITCH COLOR
$wp_customize->add_setting( 'fupi_notice_necessary_switch_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#68909b',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_necessary_switch_color', array(
    'label'           => esc_html__( 'Color of the switch for the necessary cookies', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Can\'t see the switch? Please, go to the "Text content" section add the title for the "Necessary cookies".', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_design',
    'active_callback' => $hide_when_banner_only_notifies,
)) );
// BORDER STYLE
$wp_customize->add_setting( 'fupi_cookie_notice_border', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 'small_shadow',
) );
$wp_customize->add_control( 'fupi_cookie_notice_border', array(
    'label'   => esc_html__( 'Border style', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_design',
    'type'    => 'select',
    'choices' => array(
        'none'         => esc_html__( 'None', 'full-picture-analytics-cookie-notice' ),
        'small_shadow' => esc_html__( 'Small shadow (default)', 'full-picture-analytics-cookie-notice' ),
        'large_shadow' => esc_html__( 'Large shadow', 'full-picture-analytics-cookie-notice' ),
        'thin_border'  => esc_html__( 'Thin Border', 'full-picture-analytics-cookie-notice' ),
        'wide_border'  => esc_html__( 'Wide Border', 'full-picture-analytics-cookie-notice' ),
    ),
) );
// BORDER COLOR
$wp_customize->add_setting( 'fupi_notice_border_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#ccc',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_notice_border_color', array(
    'label'           => esc_html__( 'Border color', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_design',
    'active_callback' => function ( $control ) {
        $border_type_value = $control->manager->get_setting( 'fupi_cookie_notice_border' )->value();
        return $border_type_value == 'thin_border' || $border_type_value == 'wide_border';
    },
)) );
// HEADINGS TAG
$wp_customize->add_setting( 'fupi_cookie_notice_heading_tag', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 'p',
) );
$wp_customize->add_control( 'fupi_cookie_notice_heading_tag', array(
    'label'   => esc_html__( 'Heading HTML tag', 'full-picture-analytics-cookie-notice' ),
    'section' => 'fupi_notice_typogr',
    'type'    => 'select',
    'choices' => array(
        'p'   => esc_html__( 'Paragraph (default)', 'full-picture-analytics-cookie-notice' ),
        'h3'  => esc_html__( 'H3', 'full-picture-analytics-cookie-notice' ),
        'h2'  => esc_html__( 'H2', 'full-picture-analytics-cookie-notice' ),
        'div' => esc_html__( 'div', 'full-picture-analytics-cookie-notice' ),
    ),
) );
// H FONT SIZE
$wp_customize->add_setting( 'fupi_cookie_notice_h_font_size', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 20,
) );
$wp_customize->add_control( 'fupi_cookie_notice_h_font_size', array(
    'label'       => esc_html__( 'Heading font size (in px)', 'full-picture-analytics-cookie-notice' ),
    'description' => esc_html__( 'Default: 20px', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_typogr',
    'type'        => 'number',
    'input_attrs' => array(
        'min' => 0,
    ),
) );
// P FONT SIZE
$wp_customize->add_setting( 'fupi_cookie_notice_p_font_size', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 16,
) );
$wp_customize->add_control( 'fupi_cookie_notice_p_font_size', array(
    'label'       => esc_html__( 'Paragraph font size (in px)', 'full-picture-analytics-cookie-notice' ),
    'description' => esc_html__( 'Default: 16px', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_typogr',
    'type'        => 'number',
    'input_attrs' => array(
        'min' => 0,
    ),
) );
// BUTTON FONT SIZE
$wp_customize->add_setting( 'fupi_cookie_notice_button_font_size', array(
    'sanitize_callback' => 'sanitize_key',
    'transport'         => 'postMessage',
    'default'           => 16,
) );
$wp_customize->add_control( 'fupi_cookie_notice_button_font_size', array(
    'label'       => esc_html__( 'Button font size (in px)', 'full-picture-analytics-cookie-notice' ),
    'description' => esc_html__( 'Default: 16px', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_typogr',
    'type'        => 'number',
    'input_attrs' => array(
        'min' => 0,
    ),
) );
// OPTIONAL HEADLINE TEXT IN THE MAIN PANEL
$wp_customize->add_setting( 'fupi_cookie_notice[notif_headline_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[notif_headline_text]', array(
    'label'       => esc_html__( 'Notification headline (optional)', 'full-picture-analytics-cookie-notice' ),
    'description' => esc_html__( 'This text will show up at the top of the consent\'s panel.', 'full-picture-analytics-cookie-notice' ),
    'section'     => 'fupi_notice_texts',
    'type'        => 'text',
) );
// MAIN PANEL DESCRIPTION TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[notif_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'We use cookies to provide you with the best browsing experience, personalize content of our site, analyse its traffic and show you relevant ads. See our {{privacy policy}} for more information.', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[notif_text]', array(
    'label'       => esc_html__( 'Main notification', 'full-picture-analytics-cookie-notice' ),
    'description' => esc_html__( 'Words wrapped with {{ }} will turn into a link to your privacy policy page. You can provide your own URL after "|" character, like this {{my link|https://example.com}}. You can also add shortcodes.', 'full-picture-analytics-cookie-notice' ) . $priv_policy_url_text,
    'section'     => 'fupi_notice_texts',
    'type'        => 'textarea',
) );
// "ACCEPT" BTN TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[agree_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Agree', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[agree_text]', array(
    'label'           => esc_html__( '"Agree" button', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => $hide_when_banner_only_notifies,
) );
// "STATISTICS ONLY" BTN TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[stats_only_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'I only agree to statistics', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[stats_only_text]', array(
    'label'           => esc_html__( '"Statistics only" button', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $show_val = $control->manager->get_setting( 'fupi_cookie_notice[show]' )->value();
        $show_by_user = in_array( 'stats_only_btn', $show_val );
        // Show?
        return ( $banner_only_notifies ? false : $show_by_user );
    },
) );
// "I UNDERSTAND" BTN TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[ok_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'I understand', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[ok_text]', array(
    'label'           => esc_html__( '"I understand" button', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($tools, $cook, $is_premium) {
        $has_cook_opts = !empty( $cook );
        $geo_enabled = isset( $tools['geo'] );
        // show if "auto", "notify" or "manual" mode is selected
        if ( $geo_enabled && $is_premium ) {
            if ( $has_cook_opts ) {
                if ( isset( $cook['mode'] ) ) {
                    return !($cook['mode'] === 'optin' || $cook['mode'] === 'optout');
                }
            }
            return true;
            // defaults to auto_strict
            // show if notify mode is selected
        } else {
            if ( $has_cook_opts ) {
                if ( isset( $cook['enable_scripts_after'] ) ) {
                    return !($cook['enable_scripts_after'] === 'optin' || $cook['enable_scripts_after'] === 'optout');
                }
            }
            return false;
            // defaults to optin
        }
    },
) );
// "CLOSE" BTN TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[close_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Close', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[close_text]', array(
    'label'           => esc_html__( '"Close" button', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Button with this text is displayed in the consent banner only to users who made their choice in the past but now they want to change their tracking preferences.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => $hide_when_banner_only_notifies,
) );
// "AGREE TO SELECTED" BTN TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[agree_to_selected_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Agree to selected', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[agree_to_selected_text]', array(
    'label'           => esc_html__( '"Agree to selected" button', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val );
        // Show?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// "DEACLINE" BTN TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[decline_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Decline', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[decline_text]', array(
    'label'           => esc_html__( '"Decline" button', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'decline_btn', $hidden_val );
        // Show?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// "SETTINGS" BTN TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[cookie_settings_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Settings', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[cookie_settings_text]', array(
    'label'           => esc_html__( '"Settings" button', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val );
        // Show?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// "RETURN" BTN TEXT
$wp_customize->add_setting( 'fupi_cookie_notice[return_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Return', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[return_text]', array(
    'label'           => esc_html__( '"Return" button', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// (OPTIONAL) NECESSARY TEXT HEADLINE
$wp_customize->add_setting( 'fupi_cookie_notice[necess_headline_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[necess_headline_text]', array(
    'label'           => esc_html__( '"Necessary cookies" section title (optional)', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'This text will show up at the top of the "Settings" panel along with an always-enabled switch.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// (OPTIONAL) NECESSARY TEXT DESCRIPTION
$wp_customize->add_setting( 'fupi_cookie_notice[necess_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
) );
$wp_customize->add_control( 'fupi_cookie_notice[necess_text]', array(
    'label'           => esc_html__( '"Necessary cookies" section description (optional)', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'This text will show up at the top of the "Settings" panel.', 'full-picture-analytics-cookie-notice' ) . ' ' . esc_html__( 'Words wrapped with {{ }} will turn into links. You can provide the address after "|" character, like this {{my link|https://example.com}}. You can also add shortcodes.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'textarea',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// STATISTICS TEXT HEADLINE
$wp_customize->add_setting( 'fupi_cookie_notice[stats_headline_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Statistics', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[stats_headline_text]', array(
    'label'           => esc_html__( '"Statistics" section title', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val ) || in_array( 'stats', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// STATISTICS TEXT DESCRIPTION
$wp_customize->add_setting( 'fupi_cookie_notice[stats_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'I want to help you make this site better so I will provide you with data about my use of this site.', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[stats_text]', array(
    'label'           => esc_html__( '"Statistics" section description', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default.', 'full-picture-analytics-cookie-notice' ) . ' ' . esc_html__( 'Words wrapped with {{ }} will turn into links. You can provide the address after "|" character, like this {{my link|https://example.com}}. You can also add shortcodes.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'textarea',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val ) || in_array( 'stats', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// PERSONALISATION TEXT HEADLINE
$wp_customize->add_setting( 'fupi_cookie_notice[pers_headline_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Personalisation', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[pers_headline_text]', array(
    'label'           => esc_html__( '"Personalisation" section title', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val ) || in_array( 'pers', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// PERSONALISATION TEXT DESCRIPTION
$wp_customize->add_setting( 'fupi_cookie_notice[pers_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'I want to have the best experience on this site so I agree to saving my choices, recommending things I may like and modifying the site to my liking', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[pers_text]', array(
    'label'           => esc_html__( '"Personalisation" section description', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default.', 'full-picture-analytics-cookie-notice' ) . ' ' . esc_html__( 'Words wrapped with {{ }} will turn into links. You can provide the address after "|" character, like this {{my link|https://example.com}}. You can also add shortcodes.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'textarea',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val ) || in_array( 'pers', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// MARKETING TEXT HEADLINE
$wp_customize->add_setting( 'fupi_cookie_notice[marketing_headline_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'Marketing', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[marketing_headline_text]', array(
    'label'           => esc_html__( '"Marketing" section title', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'text',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val ) || in_array( 'market', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// MARKETING TEXT DESCR
$wp_customize->add_setting( 'fupi_cookie_notice[marketing_text]', array(
    'type'              => 'option',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
    'default'           => esc_html__( 'I want to see ads with your offers, coupons and exclusive deals rather than random ads from other advertisers.', 'full-picture-analytics-cookie-notice' ),
) );
$wp_customize->add_control( 'fupi_cookie_notice[marketing_text]', array(
    'label'           => esc_html__( '"Marketing" section description', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'Leave empty to use default.', 'full-picture-analytics-cookie-notice' ) . ' ' . esc_html__( 'Words wrapped with {{ }} will turn into links. You can provide the address after "|" character, like this {{my link|https://example.com}}. You can also add shortcodes.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_texts',
    'type'            => 'textarea',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        // Check if the field was hidden by the user with the "Show/Hide elements" control
        $hidden_val = $control->manager->get_setting( 'fupi_cookie_notice[hide]' )->value();
        $hidden_by_user = in_array( 'settings_btn', $hidden_val ) || in_array( 'market', $hidden_val );
        // Display?
        return ( $banner_only_notifies ? false : !$hidden_by_user );
    },
) );
// TOGGLER
// ENABLE
$wp_customize->add_setting( 'fupi_cookie_notice[enable_toggle_btn]', array(
    'type'              => 'option',
    'default'           => false,
    'transport'         => 'postMessage',
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
) );
$wp_customize->add_control( 'fupi_cookie_notice[enable_toggle_btn]', array(
    'label'           => esc_html__( 'Enable the button which opens the consent banner', 'full-picture-analytics-cookie-notice' ),
    'description'     => esc_html__( 'The button is not shown when the banner only notifies about tracking.', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_toggler',
    'type'            => 'checkbox',
    'active_callback' => $hide_when_banner_only_notifies,
) );
// CUSTOM IMAGE
$wp_customize->add_setting( 'fupi_custom_toggler_img', array(
    'sanitize_callback' => array($this, 'fupi_customizer_sanitize'),
) );
$wp_customize->add_control( new WP_Customize_Media_Control($wp_customize, 'fupi_custom_toggler_img', array(
    'label'           => esc_html__( 'Custom image', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_toggler',
    'description'     => esc_html__( 'Allowed file types: jpg, png, gif, webp, avif, ico.', 'full-picture-analytics-cookie-notice' ),
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        $enable_btn = $control->manager->get_setting( 'fupi_cookie_notice[enable_toggle_btn]' );
        // Hide when banner only notifies or when enable button is unchecked
        return ( $banner_only_notifies ? false : $enable_btn->value() );
    },
)) );
// TOGGLER BG COLOR
$wp_customize->add_setting( 'fupi_toggler_bg_color', array(
    'sanitize_callback' => 'sanitize_hex_color',
    'transport'         => 'postMessage',
    'default'           => '#6190c6',
) );
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'fupi_toggler_bg_color', array(
    'label'           => esc_html__( 'Background Color', 'full-picture-analytics-cookie-notice' ),
    'section'         => 'fupi_notice_toggler',
    'active_callback' => function ( $control ) use($banner_only_notifies) {
        $enable_btn = $control->manager->get_setting( 'fupi_cookie_notice[enable_toggle_btn]' );
        // Hide when banner only notifies or when enable button is unchecked
        return ( $banner_only_notifies ? false : $enable_btn->value() );
    },
)) );