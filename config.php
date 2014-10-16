<?php

// Add your local development IP to the array
$local_ips = array( '192.168.0.70', '192.168.1.50', '192.168.1.60', '192.168.1.70' );
$local_environment = ( in_array( $_SERVER[ 'SERVER_ADDR' ], $local_ips ) ) ? true : false;
$site_root = dirname( __FILE__ ) . '/';

$db_host_address = ( $local_environment == true ) ? $_SERVER[ 'SERVER_ADDR' ] : 'PRODUCTION_IP';

// Raw database connection
$mysqli = new mysqli( $db_host_address, "demo_app_user", "demoapp", "demo_app" );

// Check connection
if ( mysqli_connect_errno() ) {
    printf( "Connect failed: %s\n", mysqli_connect_error() );
    exit();
}

// Define an array to store all sanitized variables
$params = array();

// Store the query variables in an array
$query_type = ( $_GET ) ? $_GET : $_POST;

// Run foreach and store the values in an array
foreach ( $query_type as $key => $value ) {
    $params[ $key ] = mysqli_real_escape_string( $mysqli, $value );
}