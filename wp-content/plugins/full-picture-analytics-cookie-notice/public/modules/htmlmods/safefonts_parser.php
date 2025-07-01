<?php

// THX to https://www.binarymoon.co.uk/2022/07/swapping-google-fonts-for-bunny-fonts/
$html = str_replace( '<link href="https://fonts.gstatic.com" crossorigin="" rel="preconnect">', '', $html );
$html = str_replace( 'fonts.googleapis.com/css2', 'fonts.bunny.net/css', $html );
$html = str_replace( 'fonts.googleapis.com/css', 'fonts.bunny.net/css', $html );
$html = str_replace( 'fonts.googleapis.com', 'fonts.bunny.net', $html );

?>