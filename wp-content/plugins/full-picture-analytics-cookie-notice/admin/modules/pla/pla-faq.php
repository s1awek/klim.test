<?php

    $questions = [];

    // Do not use IDs inside the wrappers below!
    // The internal HTML will be copied to a popup and IDs will double
    
    $answers = '

    <div id="fupi_installation_modes_popup" class="fupi_popup_content">
        <p>' . esc_html__( 'If you choose to install Plausible with WP Full Picture, you will be able to use unique functions of WP FP and track more things, e.g. user actions and data types. However, some ad blockers may stop Plausible from tracking visitors (unless you use a self-hosted Plausible).', 'full-picture-analytics-cookie-notice' ) . '</p>
        <p>' . esc_html__( 'If you choose to extend Plausible\'s own plugin, most ad blockers will not block tracking, but you will no longer be able to track that many things.', 'full-picture-analytics-cookie-notice')  . '</p>
    </div>

    <div id="fupi_differences_popup" class="fupi_popup_content">

        <p>' . esc_html__('Plausible gives you two ways to track user actions - as events with properties and without them. Let\'s explain the difference on an example.', 'full-picture-analytics-cookie-notice' ) . '</p>

        <p>' . esc_html__('Imagine that you want to track click on three different buttons - A, B and C.', 'full-picture-analytics-cookie-notice' ) . '</p>

        <p>' . esc_html__('If you track them only with events, then you will see in Plausible three different events, like "Clicked button A", "Clicked button B" and "Clicked button C".', 'full-picture-analytics-cookie-notice' ) . '</p>

        <p>' . esc_html__('If you track them with events and properties, you will see in Plausible one event "Button Clicked" with properties "Button A", "Button B" and "Button C".', 'full-picture-analytics-cookie-notice' ) . '</p>

        <p class="fupi_warning_text">' . esc_html__('We recommend the first method, since it is easier and does not require a Business plan. Plus, you cannot use properties in funnels', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>

    <div id="fupi_setup_popup" class="fupi_popup_content">

        <p>' . esc_html__('To register events and their properties in Plausible:', 'full-picture-analytics-cookie-notice' ) . '</p>

        <ol>
            <li>' . esc_html__( 'give descriptive names to actions you want to track,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'enable Setup Helper in the top menu and start a test,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'do all the actions that you\'ve just set up,', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'in the Plausible Analytics panel go to the Site Settings page > Goals > Custom Goal (and later Custom Property) and click "Add new",', 'full-picture-analytics-cookie-notice' ) . '</li>
            <li>' . esc_html__( 'you should see a dropdown field with goal / property names that you entered in the "name" fields of this module.', 'full-picture-analytics-cookie-notice' ) . '</li>
        </ol>

        <p>' . esc_html__('P.S. If you are registering ecommerce actions, such as purchases, also enable the "revenue tracking" switch in the goal creation popup.', 'full-picture-analytics-cookie-notice' ) . '</p>
    </div>';
?>