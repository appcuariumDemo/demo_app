<?php
session_start();
global $site_root, $redis;


class Color_Fetcher {

    public function db_connect() {

        global $local_environment;

        $db_host_address = ( $local_environment == true ) ? $_SERVER[ 'SERVER_ADDR' ] : 'PRODUCTION_IP';

        $mysqli = new mysqli( $db_host_address, "demo_app_user", "demoapp", "demo_app" );

        /* check connection */
        if ( $mysqli->connect_errno ) {
            printf( "Connect failed: %s\n", $mysqli->connect_error );
            exit();
        }

        return $mysqli;

    }

    //  Step 1.
    //  Get a random key from cache
    public function get_random_key( $threshold, $prefix, $new_size ) {

        global $redis;

        // First of all exclude all locked keys from selection
        $lock_prefix = 'lock_';

        // Get currently locked keys from backend
        $locked_keys = $redis->keys( '*' . $lock_prefix . $prefix . '*' );

        // Get all available keys from cache
        $available_keys = $redis->keys( $prefix . '*' );

        // If for some obscure reason cache was not generated or it is empty or if it's smaller than the threshold, generate new keys until the total size
        if ( count( $available_keys ) < $threshold || count( $available_keys ) == 0 ) {

            $this->refresh_keys( 0, 255, 3, $new_size - count( $available_keys ) );

            // Get currently locked keys from backend, even it is freshly generated, just in case
            $locked_keys = $redis->keys( '*' . $lock_prefix . $prefix . '*' );

            // Get all available keys from cache
            $available_keys = $redis->keys( $prefix . '*' );
        }

        // Get the corresponding value from the keys cache
        $random_value = '';

        // Loop through the values to find a non-locked item
        for( $i = 0; $i < count( $available_keys ) - 1; $i++ ){

            // If the item value is not in the locked items array, use it
            if ( !in_array( $lock_prefix . $available_keys[$i], $locked_keys ) ) {

                // Set the valid value
                $random_value = $available_keys[ $i ];

                // Lock current key so it is not used for other queries, until it expires
                $this->lock_key( $random_value, 15, $lock_prefix );


                break;
            }
        }

        $response = [
          'key' => $random_value,
          'values' => json_decode( $redis->get( $random_value ) )
        ];

        return $response;

    }


    //  Step 2.
    //  When the user fetches a key, mark it as reserved until the time expires. This function is triggered in step 1
    public function lock_key( $key, $timeout, $prefix ) {
        global $redis;

        $value = 1;

        // Save the entry in Redis cache, prepending an unique name and appending the index and color values for uniqueness
        return $redis->setex( $prefix . $key, $timeout, json_encode( $value ) );
    }


    //  Step 3.
    //  When the user fetches a key and uses it, erase it from the keys cache
    public function remove_key( $key ) {

        global $redis;

        return $redis->delete( [ $key, 'lock_' . $key ] );

    }

    public function set_key( $key, $index, $prefix ) {

        global $redis;

        $colors = [
            'red' => $key[ 0 ],
            'green' => $key[ 1 ],
            'blue' => $key[ 2 ]
        ];

        // Save the entry in Redis cache, prepending an unique name and appending the index and color values for uniqueness
        return $redis->set( $prefix . $index . $key[ 0 ] . $key[ 1 ] . $key[ 2 ], json_encode( $colors ) );

    }

    // UTILITIES

    // This function builds one array with RGB values. This function is triggered
    public function randomizer( $min, $max, $size ) {

        // Get the ranges
        $values = range( $min, $max );

        // Shuffle the values
        shuffle( $values );

        // Return the array
        return array_slice( $values, 0, $size );

    }

    //  If the number of available keys is below the specified threshold, build a new set of keys
    public function refresh_keys( $min, $max, $values_size, $queue_size ) {

        for ( $i = 0; $i < $queue_size; $i++ ) {

            $key = $this->randomizer( $min, $max, $values_size );

            $this->set_key( $key, $i, 'colorset_' );
        }

    }

}