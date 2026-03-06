<?php

$ret_text = '';
$common_info = '<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_custom_data_popup">' . esc_html__( 'How to use variables in the code', 'full-picture-analytics-cookie-notice') . ' <span class="fupi_open_popup_i">i</span></button>
<button type="button" class="fupi_faux_link fupi_open_popup" data-popup="fupi_testing_popup">' . esc_html__( 'How to test the code', 'full-picture-analytics-cookie-notice') . ' <span class="fupi_open_popup_i">i</span></button>

<p class="fupi_warning_text">' . esc_html__( 'Attention. Make sure your caching tool/plugin does not save inline JavaScript in a file. Otherwise, this function may not work correctly.', 'full-picture-analytics-cookie-notice' ) . '</p>

<p class="fupi_warning_text">' . esc_html__('Do not add meta tags to your website here. Go to the "General settings" page > "Meta tags" tab.', 'full-picture-analytics-cookie-notice' ) . '</p>';

switch( $section_id ){

     case 'fupi_cscr_head':
          $ret_text = '
               <p>' . esc_html__('Here you can install tracking tools in the &lt;head&gt; section of your website. Your installation scripts cannot contain any HTML. If you want to add a script with HTML, go to the "Footer Scripts" tab.', 'full-picture-analytics-cookie-notice' ) . '</p>' . $common_info . '
          
               <span id="fupi_cscr_missing_atrig_text" style="display: none !important">' . esc_html__('Script disabled. The script\'s trigger has been deleted.', 'full-picture-analytics-cookie-notice' ) . '</span>
               <span id="fupi_cscr_missing_atrig_select_text" style="display: none !important">' . esc_html__('Set a trigger', 'full-picture-analytics-cookie-notice' ) . '</span>'; // DO NOT REMOVE - this text is used in the fields
     break;

     case 'fupi_cscr_footer':
          $ret_text = '<p>' . esc_html__('Use these fields to install tracking tools before the end of the &lt;/body&gt; tag. They can contain HTML.', 'full-picture-analytics-cookie-notice' ) . '</p>' . $common_info;
     break;
};

?>
