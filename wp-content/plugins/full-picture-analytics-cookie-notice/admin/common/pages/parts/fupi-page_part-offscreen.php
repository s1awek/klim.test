<?php 
/* <button type="button" id="fupi_offscreen_open_btn"><span class="dashicons dashicons-arrow-left-alt2"></span><span class="fupi_srt"><?php esc_html_e('Show / hide popup', 'full-picture-analytics-cookie-notice'); ?></span></button> */
?>
<div id="fupi_offscreen">
    <div id="fupi_offscreen_overlay"></div>
    <div id="fupi_offscreen_content_wrap">
        <button type="button" id="fupi_offscreen_close_btn"><span class="dashicons dashicons-no-alt"></span></button>
        <div id="fupi_offscreen_nav">
            <button type="button" id="fupi_offscreen_prev_btn" class="button-secondary fupi_open_popup fupi_popup_nav_btn fupi_disabled" data-type="prev" data-popup=""><span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e('Return', 'full-picture-analytics-cookie-notice'); ?></button>
            <button type="button" id="fupi_offscreen_next_btn" class="button-secondary fupi_open_popup fupi_popup_nav_btn fupi_disabled" data-type="next" data-popup=""><?php esc_html_e('Next', 'full-picture-analytics-cookie-notice'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></button>
        </div>
        <?php // <button type="button" id="fupi_offscreen_maximize_btn"><span class="dashicons dashicons-fullscreen-alt"></span><span class="dashicons dashicons-fullscreen-exit-alt"></span></button> ?>
        <div id="fupi_offscreen_content"><?php esc_html_e('Nothing here yet. Click info icons in the main content area.', 'full-picture-analytics-cookie-notice'); ?></div>
        <div id="fupi_offscreen_feedback_link"><?php echo sprintf( esc_html__('Is this text outdated or unclear? %1$sLet us know, please.%2$s', 'full-picture-analytics-cookie-notice'), '<br><a href="https://wpfullpicture.com/contact/">', '</a>' ); ?></div>
    </div>
</div>