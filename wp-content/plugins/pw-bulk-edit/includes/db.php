<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'PWBE_DB' ) ) :

final class PWBE_DB {

    public static function query( $sql ) {
        global $wpdb;

        return mysqli_query( $wpdb->dbh, $sql );
    }

    public static function fetch_object( $result ) {
        global $wpdb;

        return mysqli_fetch_object( $result );
    }

    public static function free_result( $result ) {
        global $wpdb;

        mysqli_free_result( $result );
        $result = null;

        // Sanity check before using the handle
        if ( empty( $wpdb->dbh ) || !( $wpdb->dbh instanceof mysqli ) ) {
            return;
        }

        // Clear out any results from a multi-query
        while ( mysqli_more_results( $wpdb->dbh ) ) {
            mysqli_next_result( $wpdb->dbh );
        }
    }

    public static function num_rows( $result ) {
        global $wpdb;

        return mysqli_num_rows( $result );
    }

    public static function error() {
        global $wpdb;

        return mysqli_error( $wpdb->dbh );
    }
}

endif;
