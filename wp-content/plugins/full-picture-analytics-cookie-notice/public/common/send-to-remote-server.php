<?php

// Needs to provide $requests_a, $return_reponse = false

// Initialize a multi-cURL handle
$multiCurl      = curl_multi_init();
$curlHandles    = [];
$logged_responses = [];

foreach ( $requests_a as $request ) {

    // $request = [
    //   'url' => 'https://example.com/api1',
    //   'header' => [],
    //   'payload' => ['key1' => 'value1', 'key2' => 'value2'],
    // 	 'return_response' => 'CDB', // (optional)
    //          can take 3 values?: false, debug.log and a string:
    //          - false - response will not be sent, default
    //          - debug.log - response will be logged in WP debug.log
    //          - string - response will be sent back to the browser with the string used to help identify the response in case there are multiple, from multiple tools
    // ];
    
    // trigger_error( 'Preparing to send request: ' . json_encode( $request ) );
    
    // Initialize a new cURL session
    $ch = curl_init();

    // Set cURL options for the POST request
    curl_setopt($ch, CURLOPT_URL, $request['url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request['headers']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $request['payload'] ) );
    
    if ( ! empty ( $request['return_response'] ) ) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    }

    curl_setopt($ch, CURLOPT_TIMEOUT, 50); // Set a timeout for the request

    // Add this cURL handle to the multi-cURL handle
    curl_multi_add_handle($multiCurl, $ch);

    // Keep track of this handle
    $curlHandles[] = [
        'handle' => $ch,
        'return_response' => empty( $request['return_response'] ) ? false : $request['return_response']
    ];
}

// Execute all requests asynchronously
$running = null;

do {
    curl_multi_exec( $multiCurl, $running );
} while ( $running > 0 );

// Collect responses from each handle

foreach ( $curlHandles as $index => $handle_data) {
    if ( ! empty( $handle_data['return_response'] ) ) {
        if ( $handle_data['return_response'] == 'debug.log' ) {
            $logged_responses[] = curl_multi_getcontent( $handle_data['handle'] );
        } else {
            $responses[ $handle_data['return_response'] ] = curl_multi_getcontent( $handle_data['handle'] );
        }
    };
    curl_multi_remove_handle($multiCurl, $handle_data['handle']);
    curl_close($handle_data['handle']);
}

// Close the multi-cURL handle
curl_multi_close( $multiCurl );

if ( count( $logged_responses ) > 0 ) {
    trigger_error( json_encode( $logged_responses ) );
}