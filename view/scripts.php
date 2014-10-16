<script src="/js/libs/jquery.min.js"></script>
<script>

    $( function () {

        // Store body in a variable
        var $body = $( 'body' ),
            timeout = null;

        // Initialize the main app script
        demoInit = function () {

            $body.on( 'click', '.action.active', function () {

                // Store clicked element in a variable
                var $me = $( this ),
                    action_text = $me.data( 'action' ),
                    current_bg_color = $body.css('background-color' ),
                    $get_key = $('a[data-action="get_key"]' ),
                    $apply_key = $('a[data-action="apply_key"]');


                // If the button clicked is the one to get new background values
                if ( action_text == 'get_key' ) {

                    // If message is visible, stop the animation queue, hide it and empty the container
                    $('.notifications' ).stop().fadeTo( 300, 0, function() {
                        $(this ).empty();
                    });

                    timeout = setTimeout( function () {

                        // Restore class active so the button is clickable again
                        $me.toggleClass('active');
                        $me.toggleClass('label-success');
                        $me.toggleClass('label-default');

                        // Restore the apply button states
                        $apply_key.toggleClass('active');
                        $apply_key.toggleClass('label-success');
                        $apply_key.toggleClass('label-default');
                        $apply_key.data('key_name', '' );

                        // Restore original background color
                        $body.css({
                            'background-color': current_bg_color
                        });

                    }, 15000 );

                    // Call API with action
                    $me.demoapp( {
                        action: {
                            get_redis_key: {
                                minimum: 0,
                                maximum: 255,
                                size: 3
                            }
                        },
                        onComplete: function( response ){

                            // Change fetcher button states
                            $me.toggleClass('active');
                            $me.toggleClass('label-success');
                            $me.toggleClass('label-default');

                            // Change the apply button states
                            $apply_key.toggleClass('active');
                            $apply_key.toggleClass('label-success');
                            $apply_key.toggleClass('label-default');
                            $apply_key.data('key_name', response.key );

                            var bg_color = 'rgb(' + response.values.red + ',' + response.values.green + ',' + response.values.blue + ')';

                            // Change body background color
                            $body.css({ 'background-color': bg_color });

                        }
                    } );
                }

                // If the button clicked is the one for applying the background values
                else if ( action_text == 'apply_key' ) {

                    $me.demoapp({
                       action: {
                           apply_key: {
                               key_name: $apply_key.data('key_name')
                           }
                       },
                        onComplete: function( response ){

                            if ( response && response !== 0 ) {

                                $me.demoapp({
                                    action: {
                                        notify: {
                                            'message': 'Your new background is set up! Have a nice day!',
                                            'target': '.notifications',
                                            'method': 'append',
                                            'animation': 'fade',
                                            'persist': 5000,
                                            'speed': 300
                                        }
                                    }
                                });

                                clearInterval( timeout );

                                // Change apply button states
                                $me.toggleClass('active');
                                $me.toggleClass('label-success');
                                $me.toggleClass('label-default');
                                $me.data('key_name', response.key );

                                // Change the fetcher button states
                                $get_key.toggleClass('active');
                                $get_key.toggleClass('label-success');
                                $get_key.toggleClass('label-default');

                            }
                        }
                    });
                }

            } );
        };

    } );

    // Load the main script async
    (function ( d, s, id ) {
        var js, fjs = d.getElementsByTagName( s )[0];
        if ( d.getElementById( id ) ) return;
        js = d.createElement( s );
        js.id = id;
        js.src = "/api/js/app.js";
        fjs.parentNode.insertBefore( js, fjs );
    }( document, 'script', 'demoapp-js' ));
</script>