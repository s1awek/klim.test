<?php

$option_arr_id = 'fupi_cscr';
$footer_scr_fields = array();
$scr_fields = array(
    array(
        'placeholder' => esc_html__( 'Name', 'full-picture-analytics-cookie-notice' ),
        'type'        => 'text',
        'el_class'    => 'fupi_internal_title',
        'field_id'    => 'title',
        'class'       => 'fupi_col_100',
        'required'    => true,
        'under field' => esc_html__( '(Used on the GDPR setup helper page, data saved with visitors consents and by the "Privacy Policy Extras" module)', 'full-picture-analytics-cookie-notice' ),
    ),
    array(
        'type'     => 'hidden',
        'field_id' => 'id',
        'required' => true,
        'class'    => 'fupi_col_20',
    ),
    array(
        'label'       => esc_html__( '(Required) Script only - no HTML', 'full-picture-analytics-cookie-notice' ),
        'type'        => 'textarea',
        'field_id'    => 'scr',
        'el_class'    => 'fupi_textarea_with_code',
        'required'    => true,
        'format'      => 'htmlentities',
        'under field' => sprintf(
            esc_html__( '%1$s%5$s tags will be automatically removed (or replaced) from the code above so that WP Full Picture can manage it. %6$sLearn more%7$s.%2$s', 'full-picture-analytics-cookie-notice' ),
            '<p>',
            '</p>',
            '<strong>',
            '</strong>',
            '&lt;script>',
            '<a href="https://wpfullpicture.com/support/documentation/how-to-add-custom-scripts-in-a-privacy-respecting-way/?utm_source=fp_admin&utm_medium=referral&utm_campaign=documentation_link" target="_blank">',
            '</a>'
        ),
    ),
    array(
        'type'              => 'toggle',
        'label'             => esc_html__( 'This script does not install a tracking tool', 'full-picture-analytics-cookie-notice' ),
        'field_id'          => 'not_installer',
        'class'             => 'fupi_col_100 fupi_inline_label',
        'start_sub_section' => true,
    ),
    array(
        'label'           => esc_html__( 'Script\'s privacy policy URL (Used by the "Privacy Policy Extras" module if the script installs a tracking tool)', 'full-picture-analytics-cookie-notice' ),
        'type'            => 'url',
        'field_id'        => 'pp_url',
        'class'           => 'fupi_col_100',
        'end_sub_section' => true,
    )
);
$html_field = array(array(
    'label'       => esc_html__( '(Optional) HTML', 'full-picture-analytics-cookie-notice' ),
    'type'        => 'textarea',
    'field_id'    => 'html',
    'el_class'    => 'fupi_textarea_with_html',
    'class'       => 'fupi_col_100',
    'format'      => 'htmlentities',
    'under field' => esc_html__( 'HTML added in this field will be loaded on the page before the script. Attention! Make sure you enter HTML without errors. Buggy HTML may break the page.', 'full-picture-analytics-cookie-notice' ),
));
$footer_scr_fields = array_merge( $scr_fields, $html_field );
if ( isset( $this->tools['cook'] ) ) {
    $cook_scr_fields = array(
        array(
            'label'             => esc_html__( 'Load when visitors agree to using their data for these purposes (leave blank to load instantly)', 'full-picture-analytics-cookie-notice' ),
            'type'              => 'label',
            'field_id'          => 'types_label',
            'start_sub_section' => true,
            'class'             => 'fupi_col_40',
        ),
        array(
            'label'    => esc_html__( 'Statistics', 'full-picture-analytics-cookie-notice' ),
            'type'     => 'toggle',
            'field_id' => 'stats',
            'class'    => 'fupi_col_20 fupi_inline_label',
        ),
        array(
            'label'    => esc_html__( 'Marketing', 'full-picture-analytics-cookie-notice' ),
            'type'     => 'toggle',
            'field_id' => 'market',
            'class'    => 'fupi_col_20 fupi_inline_label',
        ),
        array(
            'label'           => esc_html__( 'Personalisation', 'full-picture-analytics-cookie-notice' ),
            'type'            => 'toggle',
            'field_id'        => 'pers',
            'class'           => 'fupi_col_20 fupi_inline_label',
            'end_sub_section' => true,
        )
    );
    $scr_fields = array_merge( $scr_fields, $cook_scr_fields );
    $footer_scr_fields = array_merge( $footer_scr_fields, $cook_scr_fields );
}
// $atrig_fields = array(
// 	array(
// 		'type'	 			=> 'r3',
// 		'is_repeater'		=> false,
// 		'field_id' 			=> 'adv_trigger',
// 		'must_have'			=> 'pro atrig',
// 		'class'				=> 'fupi_r3_atrig_section',
// 		'fields'			=> array(
// 			array(
// 				'label'				=> esc_html__( '(Beta) After the script loads, trigger when', 'full-picture-analytics-cookie-notice' ),
// 				'type' 				=> 'atrig_select',
// 				'field_id'			=> 'atrig_id',
// 				'class'				=> 'fupi_col_50',
// 				'default_option_text'	=> esc_html__( 'Trigger instantly', 'full-picture-analytics-cookie-notice' ),
// 			),
// 			array(
// 				'type'	 			=> 'select',
// 				'label' 			=> esc_html__( '...for...', 'full-picture-analytics-cookie-notice' ),
// 				'field_id' 			=> 'repeat',
// 				'class'				=> 'fupi_col_20',
// 				'options'			=> array(
// 					'no'				=> esc_html__( 'The first time', 'full-picture-analytics-cookie-notice' ),
// 					'yes'				=> esc_html__( 'Every time', 'full-picture-analytics-cookie-notice' ),
// 				),
// 			),
// 		),
// 	),
// );
// $scr_fields = array_merge( $scr_fields, $atrig_fields );
// $footer_scr_fields = array_merge( $footer_scr_fields, $atrig_fields );
$load_fields = array(array(
    'label'    => esc_html__( 'Force load', 'full-picture-analytics-cookie-notice' ),
    'type'     => 'toggle',
    'field_id' => 'force_load',
    'class'    => 'fupi_col_20 fupi_inline_label',
), array(
    'label'    => esc_html__( 'Disable script', 'full-picture-analytics-cookie-notice' ),
    'type'     => 'toggle',
    'field_id' => 'disable',
    'class'    => 'fupi_col_20 fupi_inline_label',
));
$scr_fields = array_merge( $scr_fields, $load_fields );
$footer_scr_fields = array_merge( $footer_scr_fields, $load_fields );
$sections = array(
    // HEAD SCRIPTS
    array(
        'section_id'    => 'fupi_cscr_head',
        'section_title' => esc_html__( 'Head scripts', 'full-picture-analytics-cookie-notice' ),
        'fields'        => array(array(
            'type'          => 'r3',
            'label'         => esc_html__( 'Head scripts', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'fupi_head_scripts',
            'option_arr_id' => $option_arr_id,
            'class'         => 'fupi_fullwidth_tr',
            'el_class'      => 'fupi_r3_scr',
            'is_repeater'   => true,
            'fields'        => $scr_fields,
        )),
    ),
    // FOOTER SCRIPTS
    array(
        'section_id'    => 'fupi_cscr_footer',
        'section_title' => esc_html__( 'Footer scripts', 'full-picture-analytics-cookie-notice' ),
        'fields'        => array(array(
            'type'          => 'r3',
            'label'         => esc_html__( 'Footer scripts', 'full-picture-analytics-cookie-notice' ),
            'field_id'      => 'fupi_footer_scripts',
            'option_arr_id' => $option_arr_id,
            'class'         => 'fupi_fullwidth_tr',
            'el_class'      => 'fupi_r3_scr',
            'is_repeater'   => true,
            'fields'        => $footer_scr_fields,
        )),
    ),
);