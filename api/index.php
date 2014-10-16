<?php
header( 'Content-type: application/json' );

// This acts as controller. This is the main communication path between frontend and backend.
require_once '../config.php';
require_once 'model.php';

// Store the Redis object
$redis = new Redis();
$redis->connect( '127.0.0.1', 6379 );

// Main access point
$api = new Color_Fetcher;

// Store the database object
$db = $api->db_connect();

$return = null;

// If the action is passed to the controller
if ( isset( $params[ 'action' ] ) ) {

    if ( $params['action'] == 'get_key' ) {
        $return = json_encode( $api->get_random_key(  10, 'colorset_', 100 ) );
    }

    if ( $params['action'] == 'remove_key' ) {
        $return = json_encode( $api->remove_key( $params['key_to_remove'] ) );
    }

    // If front end asks for a new key, get it and send it to the caller
    if ( $params[ 'action' ] == 'refresh_keys' ) {

        $min = ( isset( $params['min_value'] ) ) ? $params['min_value'] : 0;
        $max = ( isset( $params['max_value'] ) ) ? $params['max_value'] : 255;
        $size = ( isset( $params['max_size'] ) ) ? $params['max_size'] : 3;

        $return = json_encode( $api->refresh_keys( $min, $max, $size, 100 ) );
    }
}

echo $return;