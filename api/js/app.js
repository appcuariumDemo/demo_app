/*

 ============== DEMO APP ===============

 DEMO APP JS SDK

 ------------------------------------
 Copyright © 2013 - 2014 Appcuarium
 ------------------------------------

 sorin@appcuarium.com
 @author Sorin Gheata
 @version 1.0

 ====================================

 */

/**
 * Create wrapper for non-supporting browsers
 */
if ( typeof Object.create !== 'function' ) {

    Object.create = function ( obj ) {
        function F() {
        }

        F.prototype = obj;
        return new F();
    };
}

/**
 * Set self invoking script and the context
 */
(function ( $, window, document, undefined ) {

    // Load the framework
    /**
     *
     * @type {number}
     */
    var timer = setTimeout( function () {
        if ( demoInit ) {
            demoInit();
        }
    }, 0 );

    /**
     *
     * @type {{init: init, route: route, executeQuery: executeQuery, fetch: fetch, get_redis_key: get_redis_key, apply_key: apply_key, notify: notify}}
     */
    var DemoAPP = {
        /**
         *
         * @param options
         * @param elem
         */
        init: function ( options, elem ) {

            var me = this,
                $win = $( window );
            me.elem = elem;
            me.$elem = $( elem );
            me.options = $.extend( {}, $.fn.demoapp.options, options );
            me.api = '/api/';
            me.route( me.options );

        },
        /**
         *
         * @param options
         * @returns {*}
         */
        route: function ( options ) {
            var me = this,
                data = ( options.action  ) ? options.action : options;

            return me.executeQuery( data );
        },
        /**
         *
         * @param action
         */
        executeQuery: function ( action ) {

            var me = this;
            if ( typeof action === 'object' ) {

                $.each( action, function ( key, value ) {

                    if ( value.next ) {

                        var next = value.next;

                        $.when( me[key].call( me, value ) ).pipe(function ( data ) {

                            return me[next].call( me, data );

                        } ).then( function ( data ) {

                            console.log( key + ' Resolved -> Chained done for: ' + data );

                        }, function ( message ) {

                            console.log( key + ' Rejected: Reason -> ' + message );

                        } );

                    }
                    else {

                        return me[key].call( me, value );
                    }

                } );
            }
        },

        /**
         *
         * @param url
         * @param encoding
         * @param params
         * @param type
         * @param cache
         * @returns {*}
         */
        fetch: function ( url, encoding, params, type, cache ) {

            var me = this,
                ajaxEncoding = url.encoding || encoding,
                ajaxUrl = url.url || url,
                ajaxParams = url.params || params,
                query_type = ( type ) ? type : 'GET',
                query_cache = ( cache ) ? cache : 'false',
                dfd = $.Deferred();

            var result = $.ajax( {
                url: ajaxUrl,
                async: true,
                cache: query_cache,
                data: ajaxParams,
                dataType: ajaxEncoding,
                type: query_type

            } );

            $.when( result ).then( function ( response ) {

                dfd.resolve( response );

                if ( typeof me.options.onComplete === 'function' ) {
                    me.options.onComplete.apply( this, [response] );
                }

            } );

            return dfd.promise();
        },
        /**
         *
         * @param options
         */
        get_redis_key: function ( options ) {

            var me = this,
                params = {
                    action: 'get_key',
                    min_value: options.minimum,
                    max_value: options.maximum,
                    max_size: options.size
                };

            me.fetch( me.api, 'json', params, null, null );
        },
        /**
         *
         * @param options
         */
        apply_key: function ( options ) {

            var me = this,
                params = {
                    action: 'remove_key',
                    key_to_remove: options.key_name
                };

            me.fetch( me.api, 'json', params, null, null );
        },
        /**
         *
         * @param data
         */
        notify: function ( data ) {
            var me = this,
                target_container = $( data.target ),
                target_element = $( '<h5>', {
                    text: data.message
                } );

            if ( data.method == 'append' ) {
                target_container.append( target_element );
            }

            if ( data.animation !== undefined ) {
                target_container.fadeTo( data.speed, 1 ,function(){
                    if ( data.persist !== undefined ) {
                        target_container.delay( data.persist ).fadeTo( data.speed, 0, function(){
                            target_container.empty();
                        });
                    }
                });
            }
        }
    };

    $.fn.demoapp = function ( options ) {

        return this.each( function () {

            var demoapp = Object.create( DemoAPP );
            demoapp.init( options, this );
            $.data( this, 'demoapp', demoapp );

        } );
    };

    $.fn.demoapp.options = {
        onComplete: null
    };

})( jQuery, window, document );