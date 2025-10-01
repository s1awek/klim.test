<?php

// include_once FUPI_PATH . '/includes/fupi_modules_data.php';
include_once FUPI_PATH . '/public/modules/main/fpinfo_generator.php';
$fupi_fpinfo_generator = new Fupi_fpinfo_generator( 'list' );
$fp_info_output = $fupi_fpinfo_generator->output();
$ret_text = '';

switch( $section_id ){

	// DO NOT TRACK

	case 'fupi_main_no_track':
		$ret_text = '<p>' . esc_html__( 'Here you can choose what users you do not want to track. This will work on all the tools installed with WP FP modules and those controlled by the Consent Banner. Tools installed with GTM need extra work (more info in that module)', 'full-picture-analytics-cookie-notice') . '</p>';
	break;

	// BASIC SETTINGS

	case 'fupi_main_basic':
		$ret_text = '<p>' . esc_html__( 'These settings change various aspects of WP Full Picture.' , 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	// ADVANCED SETTINGS

	case 'fupi_main_advanced':
		$ret_text = '<p>' . esc_html__( 'Settings for advanced users and developers.' , 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	// META TAGS

    case 'fupi_main_meta':
        $ret_text = '<p>' . esc_html__('Verify website ownership in various platforms.', 'full-picture-analytics-cookie-notice' ) . '</p>';
    break;

	// PERFORMANCE

	case 'fupi_main_perf':
		$ret_text = '<p>' . esc_html__('Improve page-speed and compatibility with caching tools.', 'full-picture-analytics-cookie-notice' ) . '</p>';
	break;

	// IMPORT / EXPORT

	case 'fupi_main_importexport':

		// Set JS variables

		$js_vars = [
			'import_export_nonce' => wp_create_nonce('wpfullpicture_import_export_nonce'),
			'reload_notice_text' => esc_html__( 'The page will reload after the process is complete. If you made any changes to the settings on this page, please make sure to save them before you continue. Do you wish to proceed?', 'full-picture-analytics-cookie-notice' ),
			'confirm_text' => esc_html__("This will overwrite all settings of WP Full Picture. Are you sure?", 'full-picture-analytics-cookie-notice'),
			'alert_success_text' => esc_html__( 'Settings uploaded successfully! The page will now reload.', 'full-picture-analytics-cookie-notice' ),
			'new_backup_text' => esc_html__( 'Backup created succesfully! The page will now reload.', 'full-picture-analytics-cookie-notice' ),
			'alert_error_text' => esc_html__( 'There was an error processing the file.', 'full-picture-analytics-cookie-notice' ),
		];

		$output = '<p style="text-align: center; max-width: 640px; margin-left: auto; margin-right: auto;">' . esc_html__( 'Back up WP Full Picture\'s settings to easily move them between installations or before making bigger changes.', 'full-picture-analytics-cookie-notice' ) . '</p>
		<script>
			let fupi_import_export_data = ' . json_encode( $js_vars ) . ';
		</script>';

		// Get all the files in the backup folder
		
		$folder_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'wpfp/backups/';
		// get all txt and json files in the folder
		$files = glob( $folder_path . '*.json' );

		// Add table with available backups

		$output .= '<div id="fupi_backups_table" class="fupi_pseudo_table">
		<div class="fupi_pseud_table_head fupi_pseudo_table_row">
			<div class="fupi_pseudo_th fupi_table_cell_50">' . esc_html__( 'Backup', 'full-picture-analytics-cookie-notice' ) . '</div>
			<div class="fupi_pseudo_th fupi_table_cell_20">' . esc_html__( 'Date', 'full-picture-analytics-cookie-notice' ) . '</div>
			<div class="fupi_pseudo_th fupi_table_cell_30">' . esc_html__( 'Actions', 'full-picture-analytics-cookie-notice' ) . '</div>
		</div>';
		
		// Check if there are any backups
		if ( empty( $files ) ) {
			$output .= '<div id="no_backups_info" class="fupi_pseudo_table_row">' . esc_html__( 'No backups found', 'full-picture-analytics-cookie-notice' ) . '</div>';
		} else {

			$backup_rows = [];

			foreach( $files as $file ){
				
				$file_name = basename( $file );
				
				// file creation date is in the format YYYY-MM-DD HH:MM:SS
				$file_timestamp = filemtime( $file );
				$file_creation_date = date( 'Y-m-d H:i:s', $file_timestamp );

				$backup_rows[$file_timestamp] = '<div class="fupi_pseudo_table_row fupi_backups_row" data-file="' . esc_attr( $file_name ) . '">
					<div class="fupi_pseudo_td fupi_table_cell_50">' . esc_attr( $file_name ) . '</div>
					<div class="fupi_pseudo_td fupi_table_cell_20">' . esc_attr( $file_creation_date ) . '</div>
					<div class="fupi_pseudo_td fupi_table_cell_30">
						<button class="fupi_backup_restore button-secondary">' . esc_html__( 'Restore', 'full-picture-analytics-cookie-notice' ) . '</button>
						<button class="fupi_backup_delete button-secondary">' . esc_html__( 'Delete', 'full-picture-analytics-cookie-notice' ) . '</button>
						<a href="' . esc_url(admin_url('admin-post.php?action=wpfp_download_backup&file=' . esc_attr($file_name) . '')) . '" class="fupi_backup_download button-secondary">' . esc_html__( 'Download', 'full-picture-analytics-cookie-notice' ) . '</a>
					</div>
				</div>';
			}

			// sort rows by the key value in descending order
			if ( count( $backup_rows ) > 0 ) {
				krsort( $backup_rows );
				$output .= implode( '', $backup_rows );
			} else {
				$output .= '<div id="no_backups_info" class="fupi_pseudo_table_row">' . esc_html__( 'No backups found', 'full-picture-analytics-cookie-notice' ) . '</div>';
			}
		}

		// Always show these buttons
		$output .= '</div>
		<input type="file" id="fupi_upload_settings_file" class="fupi_upload_settings_file fupi_hidden" accept=".json">
		<div id="fupi_new_import_export_buttons_wrap">
			<button type="button" class="button button-primary fupi_make_new_backup_btn"></span> ' . esc_html__( 'Create a new backup', 'full-picture-analytics-cookie-notice' ) . '</button>
			<button type="button" class="fupi_faux_link fupi_upload_backup_file_btn"><span class="dashicons dashicons-upload"></span> ' . esc_html__( 'Restore settings from a backup file', 'full-picture-analytics-cookie-notice' ) . '</button>
		</div>';

		$ret_text = [
			'content' => $output,
			'classes' => 'fupi_descr_standard_width'
		];

	break;

	// FUPI SHORTCODE

	case 'fupi_main_shortcode':

		$ret_text = [
			'classes' => 'fupi_descr_standard_width',
			'content' => '<div class="fupi_cols" style="text-align: left; margin-top: 20px;">
				<div class="fupi_col_50">
					<p>' . sprintf( esc_html__('Use the shortcode %1$s[fp_info]%2$s in your privacy policy. It will display a list of tracking tools which you installed with WP Full Picture\'s modules (except GTM) or control with the Consent Banner.', 'full-picture-analytics-cookie-notice' ), '<code>', '</code>' ) . '</p>
					<p>' . esc_html__('The list automatically updates when you make changes in your tools. Use the form below to provide information about tools that are missing from the list (e.g. installed with GTM).', 'full-picture-analytics-cookie-notice' ) . '</p>
				</div>
				<div class="fupi_col_50" style="border: 2px solid #ccc; padding: 0 20px 20px; box-sizing: border-box; margin-top: 1em;">
					<p><strong style="text-transform: uppercase; font-size: 13px; letter-spacing: 1px;">' . esc_html__('Preview', 'full-picture-analytics-cookie-notice' ) . '</strong></p>
					'. $fp_info_output . '
				</div>
			</div>'
		];
	break;
};

?>
