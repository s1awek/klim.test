<?php


class Fupi_Build_Side_Nav {

    private $tools;
    private $modules_data;
    private $modules_names;
    private $sorted_modules;
    private $menu_html = '';
    private $extra_html = '';
    private $active_slug;

    public function __construct( $modules, $active_slug ) {

        $this->tools            = get_option('fupi_tools');

        // Show GTAG module if google ads or analytics are enabled
		if ( ! empty( $this->tools['ga41'] ) || ! empty( $this->tools['gads'] ) ) {
			$this->tools['gtag'] = true;
		}

        $this->modules_data     = $modules;
        $this->active_slug      = $active_slug;
        
        $this->get_modules_names();
        $this->filter_modules();
        $this->sort_modules_by_type();
        // trigger_error( json_encode( $this->sorted_modules ) );
        $this->build_sections_html();
        // $this->add_account_link();
        $this->add_extra_html();
        $this->output_all_html();
    }

    private function get_modules_names(){
        include FUPI_PATH . '/includes/fupi_modules_names.php';
        $addons_names = apply_filters( 'fupi_set_module_name', [] ); // for addons
        $this->modules_names = array_merge( $fupi_modules_names, $addons_names );
    }

    private function filter_modules(){

        $filtered_modules = [];

        foreach( $this->modules_data as $module ){

            if ( ! $module['is_avail'] || ! $module['has_admin_page'] ) continue;
            
            // SKIP if module is not active
            if ( ! isset( $this->tools[$module['id']] ) && ! isset( $module['always_enabled'] ) ) continue;
            
            // SKIP Woo if the plugin is deactivated
            if ( $module['id'] == 'woo' && ! class_exists( 'woocommerce' ) ) continue;

            $filtered_modules[] = $module;
        }

        $this->modules_data = $filtered_modules;
    }

    private function sort_modules_by_type(){
        
        $sorted_modules = [];
        
        foreach( $this->modules_data as $module ){
            $sorted_modules[$module['type']][] = $module;
        }

        $this->sorted_modules = $sorted_modules;
    }

    private function build_sections_html(){
        $sections_html = [];
        foreach( $this->sorted_modules as $section_type => $modules_arr ){
            $sections_html[$section_type] = $this->build_section_html( $section_type, $modules_arr );
        }
        return $sections_html;
    }

    private function get_title( $module ){
        return '<span class="fupi_sidenav_title">'. $this->modules_names[$module['id']] . '</span>';
    }

    private function get_icon( $module ) {
        if ( isset( $module['icon'] ) ) {
            return '<img src="' . FUPI_URL . 'admin/assets/img/' . esc_attr( $module['icon'] ) . '.png" aria-hidden="true">';
        } else if ( isset( $module['dashicon'] ) ) {
            return '<span class="dashicons ' . esc_attr( $module['dashicon'] ) . ' "></span>';
        }
        return '';
    }

    private function build_section_html( $section_type, $modules_arr ){
        
        $module_url = get_admin_url() . 'admin.php?page=full_picture_';
        $module_nr = 1;
        $section_links = 0;
        $section_html = '<div class="fupi_sidenav_section">';
        
        foreach( $modules_arr as $module ){

            if ( $module_nr == 1 ) {
                
                $module_nr++;

                // HOME SECTION - ONLY LINKS, NO HEADLINE
                if ( $section_type == 'home' ) {

                    $section_links++;

                    if ( $this->active_slug == $module['id'] ) {
                        $section_html .= '<div class="fupi_sidenav_item fupi_sidenav_section_title fupi_current">' . $this->get_icon( $module ) . $this->get_title( $module ) . '</div>';
                    } else {
                        $section_html .= '<a class="fupi_sidenav_item fupi_sidenav_section_title" href="'. $module_url .'tools">' . $this->get_icon( $module ) . $this->get_title( $module ) . '</a>';
                    }
                    
                    continue; // go to the next module
                
                // INTEGRATIONS H3
                } else if ( $section_type == 'integr') {

                    $section_html .= '<h3 class="fupi_sidenav_item fupi_sidenav_section_title">' . esc_html__( 'Integrations', 'full-picture-analytics-cookie-notice' ) . '</h3>';
                
                // PRIVACY H3
                } else if ( $section_type == 'priv') {

                    $section_html .= '<h3 class="fupi_sidenav_item fupi_sidenav_section_title">' . esc_html__( 'Privacy', 'full-picture-analytics-cookie-notice' ) . '</h3>';
                
                // EXTENSIONS H3
                } else if ( $section_type == 'ext') {

                    $section_html .= '<h3 class="fupi_sidenav_item fupi_sidenav_section_title">' . esc_html__( 'Extensions', 'full-picture-analytics-cookie-notice' ) . '</h3>';

                }
            }

            // ADD MODULE LINK

            // if this page is currently displayed
            if ( $this->active_slug == $module['id'] ) {  

                if ( isset ( $module['sticky_link'] ) ){
                    $sticky_link_class = 'fupi_alt_style';
                } else {
                    $sticky_link_class = '';
                    $section_links++;
                }

                $section_html .= '<div class="fupi_sidenav_item fupi_current ' . $sticky_link_class . '">' . $this->get_icon( $module ) . $this->get_title( $module ) . '</div>';
            
            // if this page is not currently displayed
            } else {

                if ( isset ( $module['sticky_link'] ) ){
                    $sticky_link_class = 'fupi_alt_style';
                } else {
                    $sticky_link_class = '';
                    $section_links++;
                }

                $section_html .= '<a class="fupi_sidenav_item ' . $sticky_link_class . '" href="'. $module_url . $module['id'] . '">' . $this->get_icon( $module ) . $this->get_title( $module ) . '</a>';
            }

        } // END foreach

        // ADD LINKS TO STATIC PAGES

        if ( $section_type == 'priv') {
            
            if ( $this->active_slug == 'gdpr_setup_helper' ) {
                $section_html .= '<div class="fupi_sidenav_item fupi_current fupi_alt_style"><img src="' . FUPI_URL . '/admin/assets/img/info_ico2.png" aria-hidden="true"> <span class="fupi_sidenav_title">' . esc_html__('GDPR setup info', 'full-picture-analytics-cookie-notice') . '</span></div>';
            } else {
                $section_html .= '<a class="fupi_sidenav_item fupi_alt_style" href="' . admin_url('admin.php?page=full_picture_tools&tab=gdpr_setup_helper') . '"><img src="' . FUPI_URL . '/admin/assets/img/info_ico2.png" aria-hidden="true"> <span class="fupi_sidenav_title">' . esc_html__('GDPR setup info', 'full-picture-analytics-cookie-notice') . '</span></a>';
            }
        }
        
        if ( $section_links > 0 ) {
            $this->menu_html .= $section_html . '</div>';
        }
    }

    // private function add_account_link(){
    //     if ( fupi_fs()->can_use_premium_code__premium_only() ){
    //         // ADD ACCOUNT LINK
    //         $this->menu_html .= '<a class="fupi_sidenav_section_title__clickable" href="https://wpfullpicture.com/account/"><span class="dashicons dashicons-admin-users"></span> ' . esc_html__( 'Account', 'full-picture-analytics-cookie-notice' ) . '</a>';
    //     }
    // }

    private function add_extra_html(){

        if ( fupi_fs()->is_not_paying() ) {

            // Define date range (October 31, 2025 to November 30, 2025)
            $start_date = strtotime('2025-10-31 00:00:00');
            $end_date = strtotime('2025-12-07 23:59:59');
            $current_time = current_time('timestamp');

            $this->extra_html = '<div id="fupi_sidenav_banner">
                <div id="fupi_sidenav_banner_unlock_icon"><span class="dashicons dashicons-unlock"></span></div>
                <h3>' . esc_html__('Unlock all PRO features', 'full-picture-analytics-cookie-notice') . '</h3>
                <a href="https://wpfullpicture.com/pricing/" class="button-primary">' . esc_html__('Get PRO', 'full-picture-analytics-cookie-notice') . '<span class="dashicons dashicons-arrow-right"></span></a>
                <a href="https://wpfullpicture.com/free-vs-pro/" style="color: lightblue; text-align: center; display: block;">' . esc_html__('Compare Free and PRO', 'full-picture-analytics-cookie-notice') . '</a>
            </div>';
            
            // Show BF DEAL notification if current date is within the range
            if ( $current_time >= $start_date && $current_time <= $end_date ) {
                $this->extra_html = '<div id="fupi_sidenav_banner">
                    <div id="fupi_sidenav_banner_unlock_icon"><span class="dashicons dashicons-unlock"></span></div>
                    <h3>' . esc_html__('Black Friday deal!', 'full-picture-analytics-cookie-notice') . '</h3>
                    <p style="color: #efefef; font-size: 15px; text-align: center;">' . esc_html__('Last days of our Black Friday deal!', 'full-picture-analytics-cookie-notice') . '</p>
                    <a href="https://wpfullpicture.com/pricing/" class="button-primary">' . esc_html__('Check prices', 'full-picture-analytics-cookie-notice') . '<span class="dashicons dashicons-arrow-right"></span></a>
                    <a href="https://wpfullpicture.com/free-vs-pro/" style="color: lightblue; text-align: center; display: block;">' . esc_html__('Compare Free and PRO', 'full-picture-analytics-cookie-notice') . '</a>
                </div>';
            }
        }
    }

    private function output_all_html(){
        echo '<div id="fupi_nav_col"><div id="fupi_side_menu" role="link">' . $this->menu_html . '</div>' . $this->extra_html . '</div>';
    }
}
    
/*
    <div id="sidenav_buy_pro_banner">
        <h3>' . esc_html__('Do you want to...', 'full-picture-analytics-cookie-notice') . '</h3>
        <div id="fupi_feature_slider" class="fupi_slider">
            <div class="fupi_slides">
                <div class="fupi_slide">
                    <div class="fupi_slide_main_text">' . esc_html__('Measure how many visitors are really interested in your products', 'full-picture-analytics-cookie-notice') . '</div>
                    <p class="small" style="line-height: 1.2"><a href="https://wpfullpicture.com/support/documentation/how-to-set-up-advanced-triggers/" target="_blank">' . esc_html__('See how to do it with custom triggers', 'full-picture-analytics-cookie-notice') . '</a></p>
                </div>
                <div class="fupi_slide">
                    <div class="fupi_slide_main_text">' . esc_html__('Improve conersion tracking with server-side tracking and enhanced attribution', 'full-picture-analytics-cookie-notice') . '</div>
                    <p class="small" style="line-height: 1.2">' . esc_html__('Available in GA and Meta Pixel', 'full-picture-analytics-cookie-notice') . '</p>
                </div>
                <div class="fupi_slide">
                    <div class="fupi_slide_main_text">' . esc_html__('Learn which of your traffic sources and ad campaigns bring you the best traffic.', 'full-picture-analytics-cookie-notice') . '</div>
                    <p class="small" style="line-height: 1.2"><a href="https://wpfullpicture.com/support/documentation/how-to-use-lead-scoring/" target="_blank">' . esc_html__('See how to do it with Lead Scoring', 'full-picture-analytics-cookie-notice') . '</a></p>
                </div>
                <div class="fupi_slide">
                    <div class="fupi_slide_main_text">' . esc_html__('Monitor your site for JavaScript errors in Google Analytics.', 'full-picture-analytics-cookie-notice') . '</div>
                </div>
                <div class="fupi_slide">
                    <div class="fupi_slide_main_text">' . esc_html__('Track custom user and post data important to your business.', 'full-picture-analytics-cookie-notice') . '</div>
                    <p class="small" style="line-height: 1.2">' . esc_html__('With metadata tracking', 'full-picture-analytics-cookie-notice') . '</p>
                </div>
            </div>
            <ul class="fupi_slider_nav"></ul>
        </div>
        <a href="https://wpfullpicture.com/pricing" class="button-primary"><span class="dashicons dashicons-unlock"></span> ' . esc_html__('Get All Pro Features', 'full-picture-analytics-cookie-notice') . '</a>
    </div>
*/
?>