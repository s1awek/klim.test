<?php

class Fupi_MAIN_admin {

    private $settings;
    private $tools;
    private $cook;
	private $proofrec;

    public function __construct(){
        $this->settings = get_option('fupi_main');
        $this->tools = get_option('fupi_tools');
        $this->cook = get_option('fupi_cook');	
		$this->proofrec = get_option('fupi_proofrec');
        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){
        add_filter( 'fupi_main_add_fields_settings', array( $this, 'add_fields_settings' ), 10, 1 );
        add_action( 'fupi_register_setting_main', array( $this, 'register_module_settings' ) );
        add_filter( 'fupi_main_get_page_descr', array( $this, 'get_page_descr' ), 10, 2 );

        // Settings Backup feature
		add_action('wp_ajax_fupi_ajax_make_new_backup', array( $this, 'fupi_ajax_make_new_backup') );
		add_action('wp_ajax_fupi_ajax_upload_settings_from_file', array( $this, 'fupi_ajax_upload_settings_from_file') );
		add_action('wp_ajax_fupi_ajax_restore_settings_backup', array( $this, 'fupi_ajax_restore_settings_backup') );
		add_action('wp_ajax_fupi_ajax_remove_settings_backup', array( $this, 'fupi_ajax_remove_settings_backup') );

		// Register the download endpoint
		add_action('admin_post_wpfp_download_backup', array( $this, 'fupi_download_settings_backup') );
    }

    public function add_fields_settings( $sections ){
        include_once 'main-fields.php';
        return $sections;
    }

    public function register_module_settings(){
        register_setting( 'fupi_main', 'fupi_main', array( 'sanitize_callback' => array( $this, 'sanitize_fields' ) ) );
    }

    public function sanitize_fields( $input ){

        include 'main-sanitize.php';

		if ( apply_filters( 'fupi_updating_many_options', false ) ) return $clean_data;


        // UPDATE CDB

        if ( ! empty ( $this->tools['cook'] ) && ! empty ( $this->tools['proofrec'] ) && ! empty ( get_privacy_policy_url() ) ) {

			include_once FUPI_PATH . '/includes/class-fupi-get-gdpr-status.php';
			$gdpr_checker = new Fupi_compliance_status_checker( 'main', $clean_data );
			$gdpr_checker->send_and_return_status();
		}

        // GENERATE FILES
		
		// Generate HEAD js

		$main_file_gen_is_enabled = ! empty( $clean_data['save_settings_file'] );
		$main_file_gen_was_enabled = ! empty( $this->settings['save_settings_file' ] );
		
		$generate_head_js = $main_file_gen_is_enabled && ! $main_file_gen_was_enabled;

		// Generate CSCR files
		
        $cscr_module_is_enabled = ! empty( $this->tools['cscr'] );
		$cscr_file_gen_is_enabled = ! empty ( $clean_data['save_cscr_file'] );
		$cscr_file_gen_was_enabled =  ! empty( $this->settings['save_cscr_file'] );

		$generate_cscr = $cscr_module_is_enabled && $cscr_file_gen_is_enabled && ! $cscr_file_gen_was_enabled;
		
        if ( $generate_head_js || $generate_cscr ) {

            include_once FUPI_PATH . '/admin/common/generate-files.php';
            $generator = new Fupi_Generate_Files();
			
            if ( $generate_head_js ) {

				$generation_status = $generator->make_head_js_file( 'main', $clean_data );

				if ( $generation_status == 'error' ) {
					if ( ! empty( $this->ver['debug'] ) ) trigger_error('[FP] Error saving main WP FP scripts in a file. The option has been turned off. Please check your server file permissions and try again.');
					unset( $clean_data['debug'] );
				}
			}

            if ( $generate_cscr ) $generator->make_cscr_js_files( false );
        }

        // CLEAR CACHE
		
		include FUPI_PATH . '/admin/common/fupi-clear-cache.php';
		return $clean_data;
    }

    public function get_page_descr( $section_id, $no_woo_descr_text ){
        include 'main-descr.php';
        return $ret_text;
    }

    //
	// SETTINGS BACKUP FEATURE
	//

	private function fupi_save_settings_to_file( $filename ) {
        
		$saved_options = array();
        require FUPI_PATH . '/includes/fupi_modules_data.php'; // sets a var $fupi_modules
        $addons_data = apply_filters( 'fupi_register_addon', [] ); // ! ADDON
        $fupi_modules = array_merge( $fupi_modules, $addons_data );
		
		// get modules options
		foreach( $fupi_modules as $module ){
			$option_id = 'fupi_' . $module['id'];
			$option_value = get_option( $option_id );
			if ( ! empty( $option_value ) ) {
				$saved_options[$option_id] = $option_value;
			} else {
				$saved_options[$option_id] = 'no_value';
			}
		}

		$saved_options['fupi_versions'] = get_option('fupi_versions');

		// get consent banner options
		$customizer_options = get_option( 'fupi_cookie_notice' );
		if ( ! empty( $customizer_options ) ) {
			$saved_options[ 'fupi_cookie_notice' ] = $customizer_options;
		} else {
			$saved_options[ 'fupi_cookie_notice' ] = 'no_value';
		}
		
		// get theme mods
		$banner_style_mods = array(
			'fupi_notice_bg_color',
			'fupi_notice_h_color',
			'fupi_cookie_notice_btns_gaps',
			'fupi_notice_text_color',
			'fupi_notice_cta_color',
			'fupi_notice_cta_txt_color',
			'fupi_notice_cta_color_hover',
			'fupi_notice_cta_txt_color_hover',
			'fupi_notice_btn_color',
			'fupi_notice_btn_txt_color',
			'fupi_notice_btn_color_hover',
			'fupi_notice_btn_txt_color_hover',
			'fupi_notice_switch_color',
			'fupi_cookie_notice_border',
			'fupi_notice_border_color',
			'fupi_cookie_notice_size',
			'fupi_notice_round_corners',
			'fupi_cookie_notice_heading_tag',
			'fupi_cookie_notice_h_font_size',
			'fupi_cookie_notice_h_font_size_mobile',
			'fupi_cookie_notice_p_font_size',
			'fupi_cookie_notice_p_font_size_mobile',
			'fupi_cookie_notice_button_font_size',
			'fupi_cookie_notice_button_font_size_mobile',
			'fupi_notice_necessary_switch_color',
			'fupi_toggler_bg_color',
			'fupi_custom_toggler_img',
		);

		$saved_options[ 'theme_mods' ] = [];

		foreach ( $banner_style_mods as $mod_id ) {
			
			$value = get_theme_mod( $mod_id );
			
			if ( empty( $value ) ) {
				$saved_options[ 'theme_mods' ][$mod_id] = 'no_value';
			} else {
				$saved_options[ 'theme_mods' ][$mod_id] = get_theme_mod( $mod_id );
			}
		}

		$json_data = json_encode( $saved_options, JSON_PRETTY_PRINT );

		$folder_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'wpfp/backups/';

		if ( ! file_exists( $folder_path ) ) {
			mkdir( $folder_path, 0755, true );
		}

		$file_path = $folder_path . '/' . $filename;
	
		$result = file_put_contents( $file_path, $json_data );

		// check if index.php file is in the same folder
		$index_file_path = $folder_path . '/index.php';

		if ( ! file_exists( $index_file_path ) ) {
				$index_file_content = '<?php
	header("HTTP/1.0 403 Forbidden");
	echo "Access denied.";
	exit;';
	
			file_put_contents( $index_file_path, $index_file_content );
		};
	
		return $result !== false;
	}

	function fupi_download_settings_backup() {
		
		// Ensure the user has the right permissions (optional)
		if ( ! current_user_can('manage_options') ) {
			wp_die('Unauthorized access');
		}
	
		// Check if the file parameter is present
		if ( ! isset($_GET['file']) ) {
			wp_die('No file specified.');
		}
	
		$file_name = sanitize_file_name($_GET['file']);
		$file_path = wp_upload_dir()['basedir'] . '/wpfp/backups/' . $file_name;
	
		// Verify the file exists
		if ( ! file_exists($file_path) ) {
			wp_die('File not found.');
		}
	
		// Set headers to force download
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
		header('Content-Length: ' . filesize($file_path));
	
		// Output the file content
		readfile($file_path);
		exit;
	}

	public function fupi_ajax_remove_settings_backup(){
		
		// check permissions
		$correct_nonce = check_ajax_referer('wpfullpicture_import_export_nonce', 'nonce');
		if ( ! current_user_can('manage_options') || ! $correct_nonce ) wp_send_json_error(array('message' => esc_html__('Permission denied', 'full-picture-analytics-cookie-notice' )));

		// get file_name
		$file_name = isset($_POST['file_name']) ? $_POST['file_name'] : false;
		if ( empty ( $file_name ) ) wp_send_json_error( array( 'message' => esc_html__('Backup file not found', 'full-picture-analytics-cookie-notice' ) ) );

		// remove file
		$file_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'wpfp/backups/' . $file_name;
		
		if ( file_exists( $file_path ) ) {
			unlink( $file_path ); // deletes the file
			wp_send_json_success( array( 'message' => esc_html__('File deleted', 'full-picture-analytics-cookie-notice' ) ) );
		} else {
			wp_send_json_error( array( 'message' => esc_html__('File not found', 'full-picture-analytics-cookie-notice' ) ) );
		}
	}

	public function fupi_ajax_make_new_backup() {
		
		// check permissions
		$correct_nonce = check_ajax_referer('wpfullpicture_import_export_nonce', 'nonce');
		if ( ! current_user_can('manage_options') || ! $correct_nonce ) wp_send_json_error(array('message' => esc_html__('Permission denied', 'full-picture-analytics-cookie-notice' )));

		// make filename
		$file_suffix = substr( str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789' ), 0, 24 );
		$filename = 'wpfp_backup_' . $file_suffix . '.json';

		// save data to file
		$file_created = $this->fupi_save_settings_to_file( $filename );
		
		if ( $file_created ) {
			$file_url = trailingslashit( wp_upload_dir()['baseurl'] ) . 'wpfp/backups/' . $filename;
			wp_send_json_success( array( 'file_url' => $file_url ));
		} else {
			wp_send_json_error( array( 'message' => esc_html__('There was an error saving the backup file', 'full-picture-analytics-cookie-notice' ) ) );
		}
	}

	public function fupi_ajax_upload_settings_from_file() {
		
		// check permissions
		$correct_nonce = check_ajax_referer('wpfullpicture_import_export_nonce', 'nonce');
		if ( ! current_user_can('manage_options') || ! $correct_nonce ) wp_send_json_error(array('message' => esc_html__('Permission denied', 'full-picture-analytics-cookie-notice' )));
		
		// Restore settings
		$uploaded_settings = isset($_POST['settings']) ? $_POST['settings'] : ''; // gets decoded JSON

		if ( ! is_array( $uploaded_settings ) ) {
			wp_send_json_error( array( 'message' => esc_html__('Error. Incorrect format of uploaded data', 'full-picture-analytics-cookie-notice' ) ) );
			return;
		}

		include_once FUPI_PATH . '/admin/common/fupi_updater.php';
		$updater = new Fupi_Updater();
		$update_ok = $updater->run( true, $uploaded_settings );

		if ( $update_ok ) {
			wp_send_json_success( array('message' => esc_html__('Settings restorred successfully', 'full-picture-analytics-cookie-notice' ) ) );
		} else {
			wp_send_json_error( array( 'message' => esc_html__('No settings data received', 'full-picture-analytics-cookie-notice' ) ) );
		}
	}

	public function fupi_ajax_restore_settings_backup(){
		
		// check permissions
		$correct_nonce = check_ajax_referer('wpfullpicture_import_export_nonce', 'nonce');
		if ( ! current_user_can('manage_options') || ! $correct_nonce ) wp_send_json_error(array('message' => esc_html__('Permission denied', 'full-picture-analytics-cookie-notice' )));

		// get file name
		$file_name = isset($_POST['file_name']) ? $_POST['file_name'] : false;
		if ( empty ( $file_name ) ) wp_send_json_error( array( 'message' => esc_html__('Backup file not found', 'full-picture-analytics-cookie-notice' ) ) );

		// get file
		$file_path = trailingslashit( wp_upload_dir()['basedir'] ) . 'wpfp/backups/' . $file_name;
		if ( ! file_exists( $file_path ) ) wp_send_json_error( array( 'message' => esc_html__('File not found', 'full-picture-analytics-cookie-notice' ) ) );

		// Restore settings
		$file_contents = json_decode( file_get_contents( $file_path ), true );
		
		if ( ! is_array( $file_contents ) ) {
			wp_send_json_error( array( 'message' => esc_html__('Error. Incorrect format of uploaded data', 'full-picture-analytics-cookie-notice' ) ) );
			return;
		}

		include_once FUPI_PATH . '/admin/common/fupi_updater.php';
		$updater = new Fupi_Updater();
		$update_ok = $updater->run( true, $file_contents );

		if ( $update_ok ) {
			wp_send_json_success( array('message' => esc_html__('Settings restorred successfully', 'full-picture-analytics-cookie-notice' ) ) );
		} else {
			wp_send_json_error( array( 'message' => esc_html__('No settings data received', 'full-picture-analytics-cookie-notice' ) ) );
		}
	}
}