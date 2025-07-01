<?php

$option_arr_id = 'fupi_reports';

$sections = array(

	array(
		'section_id' => 'fupi_reports_main',
		'section_title' => esc_html__( 'Settings', 'full-picture-analytics-cookie-notice' ),
		'fields' => array(
	
			array(
				'type'	 			=> 'r3',
				'label' 			=> esc_html__( 'Reports', 'full-picture-analytics-cookie-notice' ),
				'field_id' 			=> 'dashboards',
				'option_arr_id'		=> $option_arr_id,
				'class'				=> 'fupi_fullwidth_tr',
				'el_class'			=> 'fupi_reports_fields',
				'is_repeater'		=> true,
				'fields'			=> array(
					array(
						'type'				=> 'text',
						'placeholder'		=> esc_html__( 'Report name', 'full-picture-analytics-cookie-notice' ),
						'el_class'			=> 'fupi_internal_title',
						'class'		=> 'fupi_col_100',
						'field_id'			=> 'title',
						'required'			=> true,
					),
					array(
						'type'				=> 'textarea',
						'label'				=> esc_html__( '<iframe> code', 'full-picture-analytics-cookie-notice' ),
						'class'		=> 'fupi_col_100',
						'field_id'			=> 'iframe',
						'format'			=> 'htmlentities',
						'required'			=> true,
					),
					array(
						'type' 				=> 'user_search',
						'label' 			=> esc_html__( 'Let non-admin users view this report', 'full-picture-analytics-cookie-notice' ),
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
				'label' 			=> esc_html__( 'Let non-admin users view all reports', 'full-picture-analytics-cookie-notice' ),
				'option_arr_id' 	=> $option_arr_id,
				'must_have'			=> 'admin',
				'under field'		=> '<p>' . esc_html__( 'Users assigned to specific reports will overwrite these values.', 'full-picture-analytics-cookie-notice') . '</p>',
				'popup2'			=> '<p style="color: #e47d00">' . esc_html__( 'Attention! This setting will let users access the Reports page but NOT view it! Users must have the rights to access the reports in the service that you use to generate them, e.g. Looker Studio or Databox.', 'full-picture-analytics-cookie-notice' ) . '</p>
				<p style="color: #e47d00">' . esc_html__( 'Attention. As a security measure, the "Reports" page won\'t be accessible to users without the right to edit posts.', 'full-picture-analytics-cookie-notice' ) . '</p>',
			),
		),
	),
);

?>
