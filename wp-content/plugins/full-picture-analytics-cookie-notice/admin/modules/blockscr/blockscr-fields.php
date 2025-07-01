<?php

$option_arr_id = 'fupi_blockscr';

$scr_fields = array(
	array(
		'type'				=> 'text',
		'field_id'			=> 'title',
		'el_class'			=> 'fupi_internal_title',
		'placeholder'		=> esc_html__('Name', 'full-picture-analytics-cookie-notice' ),
		'class'		=> 'fupi_col_100',
		'required'			=> true, // made required in v8. When empty, the value will be filled in with script ID
		'under field'		=> esc_html__('(Used on the GDPR setup helper page, data saved with visitors consents and by the "Privacy Policy Extras" module)', 'full-picture-analytics-cookie-notice' ),
	),
	array(
		'label'				=> esc_html__('Control loading of', 'full-picture-analytics-cookie-notice' ),
		'type'				=> 'select',
		'field_id'			=> 'block_by',
		'options'			=> array(
			'content'			=> esc_html__('Script with unique text content','full-picture-analytics-cookie-notice'),
			'src'				=> esc_html__('Script with a specific filename or URL','full-picture-analytics-cookie-notice'),
			'link_href'			=> esc_html__('HTML <link> tag with a specific filename or URL','full-picture-analytics-cookie-notice'),
			'img_src'			=> esc_html__('Image with a specific filename or URL','full-picture-analytics-cookie-notice'),
		),
		'class'		=> 'fupi_col_30',
	),
	array(
		'type'				=> 'text',
		'field_id'			=> 'url_part',
		'label'				=> esc_html__('Filename, full URL, URL part or unique text content', 'full-picture-analytics-cookie-notice' ),
		'required'			=> true,
		'class'		=> 'fupi_col_50',
	),
	array(
		'label'				=> esc_html__('Script ID', 'full-picture-analytics-cookie-notice' ),
		'type'				=> 'text',
		'field_id'			=> 'id',
		'required'			=> true,
		'class'		=> 'fupi_col_20',
	),
);

if ( isset($this->tools['cook'] ) ) {
	$scr_fields = array_merge( $scr_fields, array(
		array(
			'label'				=> esc_html__('What is visitor\'s data used for?', 'full-picture-analytics-cookie-notice' ),
			'type'				=> 'label',
			'field_id'			=> 'types_label',
			'start_sub_section' =>  true,
			'class'		=> 'fupi_col_40',
		),
		array(
			'type'				=> 'checkbox',
			'field_id'			=> 'stats',
			'label'             => esc_html__('Statistics', 'full-picture-analytics-cookie-notice' ),
			'class'		=> 'fupi_col_20',
		),
		array(
			'type'				=> 'checkbox',
			'field_id'			=> 'market',
			'label'             => esc_html__('Marketing', 'full-picture-analytics-cookie-notice' ),
			'class'		=> 'fupi_col_20',
		),
		array(
			'type'				=> 'checkbox',
			'field_id'			=> 'pers',
			'label'             => esc_html__('Personalisation', 'full-picture-analytics-cookie-notice' ),
			'class'		=> 'fupi_col_20',
			'end_sub_section' 	=>  true,
		),
	) );
}


$scr_fields = array_merge( $scr_fields, array(
	array(
		'label'				=> esc_html__('Tool\'s privacy policy URL', 'full-picture-analytics-cookie-notice' ),
		'type'				=> 'url',
		'field_id'			=> 'pp_url',
		'class'		=> 'fupi_col_50_grow',
		'under field'		=> esc_html__('(Used by the "Privacy Policy Extras" module)', 'full-picture-analytics-cookie-notice' ),
	),
) );


if ( ! empty( $this->tools ) && isset ( $this->tools['geo'] ) ){

	$geo_scr_fields = array(
		array(
			'label'				=> esc_html__('Load only in specific countries (leave blank to use everywhere)', 'full-picture-analytics-cookie-notice' ),
			'type'				=> 'label',
			'field_id'			=> 'countries_label',
			'start_sub_section' =>  true,
			'class'		=> 'fupi_col_100',
		),
		array(
			'type'				=> 'select',
			'field_id'			=> 'method',
			'options'			=> array(
				'excl'				=> esc_html__('All except','full-picture-analytics-cookie-notice'),
				'incl'				=> esc_html__('Only in','full-picture-analytics-cookie-notice'),
			),
			'class'		=> 'fupi_col_20',
		),
		array(
			'type'				=> 'text',
			'field_id'			=> 'countries',
			'placeholder'		=> esc_html__('e.g. GB, DE, FR, AU, etc.', 'full-picture-analytics-cookie-notice' ),
			'end_sub_section' =>  true,
			'class'		=> 'fupi_col_50',
		),
	);

	$scr_fields = array_merge( $scr_fields, $geo_scr_fields );
}

$scr_fields = array_merge( $scr_fields, array(
	array(
		'label'				=> esc_html__('Temporarily stop WP Full Picture from managing this script (blocking, conditional-loading, etc.)', 'full-picture-analytics-cookie-notice' ),
		'type'				=> 'checkbox',
		'field_id'			=> 'force_load',
		'class'		=> 'fupi_col_66_grow',
	),
));

// ALL TOGETHER

$sections = array(

	array(
		'section_id' => 'fupi_blockscr_main',
		'section_title' => esc_html__( 'Control tracking tools installed without WP Full Picture', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'multi checkbox',
				'label' 			=> esc_html__( 'Automatically manage tracking tools loaded by', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'auto_rules',
				'option_arr_id'		=> $option_arr_id,
				'options' 			=> array(
					'woo_sbjs'				=> 'WooCommerce (SourceBuster.js)',
					'exact_metrics'			=> 'ExactMetrics',
					'ga_jeff_star'			=> 'GA Google Analytics (by Jeff Starr)',
					'jetpack'				=> 'Jetpack (*)',
					'monster_insights'		=> 'MonsterInsights',
					'pixel_caffeine' 		=> 'Pixel Caffeine',
					'rank_math'				=> 'Rank Math',
					'site_kit'				=> 'Site Kit by Google',
				),
				'under field'		=> esc_html__( '* WP Full Picture can make Jetpack\'s Stats module comply with GDPR and other privacy laws. Compliance of other Jetpack modules has not been tested.', 'full-picture-analytics-cookie-notice' ),
				'popup2'			=> '<h3>' . esc_html__( 'What does it do?', 'full-picture-analytics-cookie-notice' ) . '</h3>
				<p>' . esc_html__( 'Tracking tools managed by WP Full Picture:', 'full-picture-analytics-cookie-notice' ) . '</p>
				<ol>
					<li>' . esc_html__( 'load according to the settings in the Consent Banner module (if you have it enabled)', 'full-picture-analytics-cookie-notice' ) . '</li>
					<li>' . esc_html__( 'support consent mode v2', 'full-picture-analytics-cookie-notice' ) . '</li>
					<li>' . esc_html__( 'do not load for users excluded from tracking', 'full-picture-analytics-cookie-notice' ) . ' <button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_track_excl_popup">' . esc_html__("Learn more" ,'full-picture-analytics-cookie-notice' ) . '</button></li>
					<li>' . esc_html__( 'not load on pages that are not viewed (e.g. opened in tabs that were never opened)', 'full-picture-analytics-cookie-notice' ) . '</li>
				</ol>
				<h3>' . esc_html__( 'What you need to know', 'full-picture-analytics-cookie-notice' ) . '</h3>
				<p>' . esc_html__( 'If you want to make any of these tracking scripts / tools load only in specific countries, please enable the geolocation module and set them up using the fields for the manual setup method', 'full-picture-analytics-cookie-notice' ) . ' <a href="https://wpfullpicture.com/support/documentation/manual-setup-guide-for-the-tracking-tools-manager-module/">' . esc_html__("View the guide" ,'full-picture-analytics-cookie-notice' ) . '</a></p>'
			),
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__('Manually set up what tracking scripts should be managed', 'full-picture-analytics-cookie-notice'),
				'field_id' 			=> 'blocked_scripts',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_fullwidth_tr',
				'is_repeater'		=> true,
				'fields'			=> $scr_fields,
			),
		),
	),
);

?>
