<?php
    session_start();
    include_once 'config.php';

    $db_host_address = ( $local_environment == true ) ? $_SERVER['SERVER_ADDR'] : 'PRODUCTION_IP';

    // Raw database connection to series
    $mysqli = new mysqli( $db_host_address, "demo_app_user", "demoapp", "demo_app" );

    $params = array();

    // Store the query variables in an array
    $query_type = ( $_GET ) ? $_GET : $_POST;

    // Run foreach and store the values in an array
    foreach ( $query_type as $key => $value ) {
        $params[$key] = mysqli_real_escape_string( $mysqli, $value );
    }

    $action = ( $params['action'] && $params['action'] !== '' ) ? $params['action'] : false;

    include_once 'view/header.php';
    include_once 'view/content.php';
    include_once 'view/scripts.php';
    include_once 'view/footer.php';