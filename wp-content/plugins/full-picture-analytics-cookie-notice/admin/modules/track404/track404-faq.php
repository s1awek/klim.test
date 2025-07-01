<?php

    $questions = [
        [
            'id' => 'learn',
            'title' => esc_html__('How does it work?', 'full-picture-analytics-cookie-notice' ),
        ],
        [
            'id' => 'redirect404',
            'title' => esc_html__('How to redirect traffic from a broken page to an existing page', 'full-picture-analytics-cookie-notice' ),
        ],
    ];

    // Do not use IDs below!
    // The code will be copied to a popup and IDs will double
    
    $answers = '<div id="fupi_learn_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'When someone clicks a link to a non-existent page on your website, WP Full Picture adds special parameters to this link so that it looks like this:','full-picture-analytics-cookie-notice') . '</p>
        <p style="font-family: courier; background: #efefef; padding: 5px; word-wrap: break-word;">https://example.com/my_404/?broken_link_location=facebook.com&broken_link=abot_us</p>
        <p>' . esc_html__( 'The parameters specify the location of the link and the address of the original broken link.','full-picture-analytics-cookie-notice') . '</p>
        <p>' . esc_html__( 'You can find links with these parameters in Google Analytics, Matomo or other web analytics tools.','full-picture-analytics-cookie-notice') . '</p>
    </div>
    
    <div id="fupi_redirect404_popup" class="fupi_popup_content">
        <p>' . esc_html__('To redirect traffic from 404 pages to a page of your choice:','full-picture-analytics-cookie-notice') . '</p>
        <ol>
            <li>' . esc_html__('Create a new page or use an existing one (e.g. the home page).','full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__('Enter its URL in the field on this page (available only in WP FP PRO)','full-picture-analytics-cookie-notice') . '</li>
            <li>' . esc_html__('(Optionally) If you redirect traffic to a page that should not be seen in Google search results, you should "noindex" it using your SEO plugin.','full-picture-analytics-cookie-notice') . '</li>
        </ol>
    </div>';
?>