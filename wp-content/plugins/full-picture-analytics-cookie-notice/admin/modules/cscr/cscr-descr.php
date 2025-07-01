<?php

$ret_text = '';

switch( $section_id ){

    case 'fupi_cscr_head':
         $ret_text = '<p>' . esc_html__('Use these fields to add scripts to the document\'s &lt;head&gt;. These scripts cannot contain any HTML. If you want to add a script with HTML, do it in the "Footer Scripts" section.', 'full-picture-analytics-cookie-notice' ) . '</p>
         
         <div id="fupi_cscr_missing_atrig_text" style="display: none !important">' . esc_html__('Script disabled. The script\'s trigger has been deleted.', 'full-picture-analytics-cookie-notice' ) . '</div>
         <div id="fupi_cscr_missing_atrig_select_text" style="display: none !important">' . esc_html__('Set a trigger', 'full-picture-analytics-cookie-notice' ) . '</div>'
         ; // DO NOT REMOVE - this text is used in the fields
    break;

    case 'fupi_cscr_footer':
        $ret_text = '<p>' . esc_html__('Use these fields to add scripts and HTML (optional) before the end of the &lt;/body&gt; tag.', 'full-picture-analytics-cookie-notice' ) . '</p>';
   break;
};

?>
