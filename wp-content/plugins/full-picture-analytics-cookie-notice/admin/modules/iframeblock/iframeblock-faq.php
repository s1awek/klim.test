<?php

    $questions = [
        [
            'id' => 'about',
            'title' => esc_html__('How to use it', 'full-picture-analytics-cookie-notice' ),
            'url' => 'https://wpfullpicture.com/support/documentation/how-iframes-manager-works-and-how-to-set-it-up'
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '
    <div id="fupi_compatibility_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'This module has been tested with various page builders. Gutenberg is fully compatible with Iframes Manager. In order to control iframes in other page builders please insert them as HTML. In the Breakdance builder you can also choose iframes to be loaded in the "Fully Embedded" mode.', 'full-picture-analytics-cookie-notice') . '</p>
    </div>';
?>