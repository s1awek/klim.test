<?php

$option_arr_id = 'fupi_reports';

$sections = array(

	array(
		'section_id' => 'fupi_reports_main',
		'section_title' => esc_html__( 'Analytics dashboards in WP admin', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Dashboards settings', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'dashboards',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_fullwidth_tr',
				'el_class'			=> 'fupi_reports_fields',
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'label'				=> esc_html__( 'Dashboard\'s name', 'full-picture-analytics-cookie-notice' ),
						'type'				=> 'text',
						'el_class'			=> 'fupi_internal_title',
						'class'				=> 'fupi_col_100',
						'field_id'			=> 'title',
						'required'			=> true,
					),
					array(
						'type'				=> 'textarea',
						'label'				=> esc_html__( 'Dashboard\'s <iframe> code (embed code)', 'full-picture-analytics-cookie-notice' ),
						'class'				=> 'fupi_col_100',
						'el_class'			=> 'fupi_textarea_with_code',
						'field_id'			=> 'iframe',
						'format'			=> 'htmlentities',
						'required'			=> true,
					),
					array(
						'type' 				=> 'user_search',
						'label' 			=> esc_html__( 'Select non-admin users who can access this dashboard (optional)', 'full-picture-analytics-cookie-notice' ),
						'field_id' 			=> 'selected_users',
						'option_arr_id' 	=> $option_arr_id,
						'class'		=> 'fupi_col_100',
						'must_have'			=> 'pro admin',
					),
					array(
						'type'				=> 'hidden',
						'label'				=> esc_html__( 'ID', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'e.g. dashboard1', 'full-picture-analytics-cookie-notice' ),
						'class'		=> 'fupi_col_20',
						'field_id'			=> 'id',
						'required'			=> true,
					),
					array(
						'type'				=> 'number',
						'label'				=> esc_html__( 'Report width (px)', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'default: 1200', 'full-picture-analytics-cookie-notice' ),
						'class'		=> 'fupi_col_20',
						'field_id'			=> 'width',
					),
					array(
						'type'				=> 'number',
						'label'				=> esc_html__( 'Report height (px)', 'full-picture-analytics-cookie-notice' ),
						'placeholder'		=> esc_html__( 'default: 675', 'full-picture-analytics-cookie-notice' ),
						'class'		=> 'fupi_col_20',
						'field_id'			=> 'height',
					),
				),
			),
			array(
				'type'	 			=> 'number',
				'label' 			=> esc_html__( 'Menu position of the "Reports" page', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'menu_pos',
				'option_arr_id'		=> $option_arr_id,
				'label_for' 		=> $option_arr_id . '[menu_pos]',
				'placeholder'		=> '10',
				'popup'				=> esc_html__( 'Lower number = Higher position. Leave empty or enter 0 for the default position. Negative values will place the link on the first position in the menu.', 'full-picture-analytics-cookie-notice' ),
			),
			array(
				'type' 				=> 'user_search',
				'field_id' 			=> 'selected_users',
				'label' 			=> esc_html__( 'Select non-admin users who can access all dashboards', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id' 	=> $option_arr_id,
				'must_have'			=> 'admin',
				'under field'		=> '<p>' . esc_html__( 'Users assigned to specific reports will overwrite these values.', 'full-picture-analytics-cookie-notice') . '</p>',
			),
		),
	),
);

?>
