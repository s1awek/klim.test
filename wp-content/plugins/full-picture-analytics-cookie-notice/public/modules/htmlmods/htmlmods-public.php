<?php

class Fupi_HTMLMODS_public {

    private $tools;
    private $main;

    private $iframes_enabled;
    private $blockscr_enabled;
    private $safefonts_enabled;

    private $iframes_settings;
    private $blockscr_settings;
    private $safefonts_settings;

    public function __construct(){

        $this->tools = get_option('fupi_tools');
        $this->main = get_option('fupi_main');

        $this->iframes_enabled = isset( $this->tools['iframeblock'] );
        $this->blockscr_enabled = isset( $this->tools['blockscr'] );
        $this->safefonts_enabled = isset( $this->tools['safefonts'] );
        
        if ( $this->iframes_enabled ) $this->iframes_settings = get_option( 'fupi_iframeblock' );
        if ( $this->blockscr_enabled ) $this->blockscr_settings = get_option( 'fupi_blockscr' );
        if ( $this->safefonts_enabled ) $this->safefonts_settings = get_option( 'fupi_safefonts' );

        if ( empty ( $this->iframes_settings ) && empty ( $this->blockscr_settings ) && empty ( $this->safefonts_settings ) ) return;

        $this->add_actions_and_filters();
    }

    private function add_actions_and_filters(){

        add_action( 'template_redirect', array( $this, 'fupi_maybe_buffer_output' ), 2); // https://stackoverflow.com/a/71548452

        if ( ! empty ( $this->iframes_settings ) ) {
            add_action( 'init', array($this, 'register_iframes_shortcodes' ) );
            add_filter( 'fupi_modify_fp_object', array($this, 'add_data_to_fp_object'), 10, 1 );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }
    }

    //
    // IFRAME BLOCKING
    //

    public function add_data_to_fp_object( $fp ){
        
        $iframeblock = $this->iframes_settings;
        if ( empty ( $iframeblock ) ) $iframeblock = [];
        $iframeblock['privacy_url'] = get_privacy_policy_url();
        if ( empty ( $iframeblock['btn_text'] ) ) $iframeblock['btn_text'] = esc_html__( 'Load content', 'full-picture-analytics-cookie-notice' );
        if ( empty ( $iframeblock['caption_txt'] ) ) $iframeblock['caption_txt'] = esc_html__( 'This content is hosted by [[an external source]]. By loading it, you accept its {{privacy terms}}.', 'full-picture-analytics-cookie-notice' );
        $fp['iframeblock'] = $iframeblock;
        
        return $fp;
    }

    public function enqueue_scripts(){
        // Load JS only when we are NOT in the bricks builder editor
        if ( ! ( function_exists('bricks_is_builder') && bricks_is_builder() ) ) {
            /* ^ */ wp_enqueue_script( 'fupi-iframes-js', FUPI_URL . 'public/modules/htmlmods/fupi-iframes.js', array('fupi-helpers-js'), FUPI_VERSION, [ 'in_footer' => false ] );
        }
    }

    // FUPI SHORTCODE FOR BLOCKING IFRAME
    public function register_iframes_shortcodes(){
        add_shortcode( 'fp_block', array($this, 'fupi_block') );
		add_shortcode( 'fp_block_iframe', array($this, 'fupi_block') );
    }

    public function fupi_block($atts, $content = null){
		
		if ( isset( $this->tools['cook'] ) ) {

			$a = shortcode_atts( array(
				'stats' => '',
				'market' => '',
				'pers' => '',
				'name' => '',
				'image' => false,
				'privacy' => ''
			), $atts );

			if ( empty( $content ) ){
				return '';
			} else {

				// get the data
				
				$stats 		= ! empty( $a['stats'] ) && $a['stats'] == '1' ? '1' : '0';
				$market 	= ! empty( $a['market'] ) && $a['market'] == '1' ? '1' : '0';
				$pers 		= ! empty( $a['pers'] ) && $a['pers'] == '1' ? '1' : '0';
				$name 		= ! empty( $a['name'] ) ? ' data-name="' . esc_attr( $a['name'] ) . '"': '';
				$placeholder = ! empty( $a['image'] ) ? ' data-placeholder="' . esc_url( $a['image'] ) . '"' : '';
				$privacy 	 = ! empty( $a['privacy'] ) ? ' data-privacy="' . esc_url( $a['privacy'] ) . '"' : '';
				
				// replace iframe

				$new_content = str_replace( '<iframe', '<div class="fupi_blocked_iframe" data-stats="' . $stats . '" data-market="' . $market . '" data-pers="' . $pers . '" ' . $placeholder . $name . $privacy . '><div class="fupi_iframe_data"', $content );
				$output = str_replace( '/iframe>', '/div></div>', $new_content ) . '<!--noptimize--><script data-no-optimize="1" nowprocket>FP.manageIframes();</script><!--/noptimize-->';

				return $output;
			};
		}
		
		return $content; // this returns only iframes - shortcodes are always invisible ( it saves user time removing them if the iframe blocking module was disabled )
		
	}

    //
    // HTML PARSER
    //

    public function fupi_maybe_buffer_output(){
		ob_start( array($this, 'fupi_return_buffer') );
	}

	public function fupi_return_buffer($html) {
		
        if ( ! $html ) { return $html; }

        // Copy HTML
		$orig_html = $html;

		// SCRIPTS BLOCKER
		if ( ! empty( $this->blockscr_settings ) ) {
			include_once dirname(__FILE__) . '/blockscr_parser.php';
		}

		// IFRAMES BLOCKER
		if ( ! empty ( $this->iframes_settings ) ) {
			
			// make sure we do not try to manage iframes while in bricks builder (it breaks)
			$can_load_iframe_parser = ! ( function_exists('bricks_is_builder') && bricks_is_builder() );

			if ( $can_load_iframe_parser ) include_once dirname(__FILE__) . '/iframeblock_parser.php';
		}

		// SAFE FONTS
		if ( isset ( $this->tools['safefonts'] ) ) {
			include_once dirname(__FILE__) . '/safefonts_parser.php';
		}
		
		if ( ! empty( $html ) ) return $html;

		return $orig_html;
	}
}