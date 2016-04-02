/**
 * ElastiGallery - v0.1.0 - 2016-04-02
 * http://plugish.com
 *
 * Copyright (c) 2016;
 * Licensed GPLv2+
 */
/*jslint browser: true */
/*global jQuery:false */

window.Elastigallery = (function(window, document, $, undefined){
	'use strict';

	var app = {},
        elg_localized = window.elg_localized || {};

	app.init = function() {
        
        app.current_url =  app.urlObject( { 'url': window.location } );
        app.owl_settings = {
            items:             5,
            itemsDesktop:      [ 1199, 5 ],
            itemsDesktopSmall: [ 979, 3 ],
            itemsTablet:       [ 768, 5 ],
            itemsMobile:       [ 479, 3 ],
            navigation:        false
        };

        $( '.elastigallery_wrapper' ).each( app.initialize_oc );
        $( 'body' ).on( 'click', 'a.elastigallery-slide', app.show_image );
	};

    app.initialize_oc = function(){
        var $that = $( this ),
            carousel = $that.find( '.elastigallery_slides' ).owlCarousel( app.owl_settings );

        if ( app.current_url && app.current_url.hash ) {
            var hash = '#elastigallery-' + app.current_url.hash,
                $item = $that.find( hash ).parent( '.gallery-item' ),
                $index = $that.find( '.gallery-item' ).index( $item ),
                $image_divs = $that.find( '.entry-attachment' );

            if ( $index ) {
                carousel.trigger( 'owl.jumpTo', $index );
                $image_divs.each( function(){
                    var $el = $( this );
                    if ( app.current_url.hash === $el.attr('id') ) {
                        $el.show();
                    } else {
                        $el.hide();
                    }
                });
            }
        }
    };

    app.show_image = function( evt ) {
        evt.preventDefault();
        var $that = $( this ),
            $to_show = $( $that.attr( 'href' ) ),
            $container = $to_show.parent( '.elastigallery_wrapper' );

        $container.find( '.entry-attachment' ).hide();
        $to_show.show();

        // Use this to prevent jumping to the image, but still allow url hash changes
        history.replaceState( null, '', $that.attr('href') );
    };

    app.log = function( data ){
        if ( window.console && elg_localized.script_debug ) {
            window.console.log( data );
        }
    };

    /**
     * JS utility function
     * - Breaks down url to an object with accessible properties: protocol, parameters object, host, hash, etc...
     * - Converts url parameters to key/value pairs
     * - Convert parameter numeric values to their base types instead of strings
     * - Store multiple values of a parameter in an array
     * - Unescape parameter values
     *
     * @author: Aymanfarhat
     * @link: https://gist.github.com/aymanfarhat/5608517
     *
     * @param options
     * @returns object Similar to PHP's parse_url() method
     */
    app.urlObject = function ( options ) {

        var url_search_arr,
            option_key,
            i,
            urlObj,
            get_param,
            key,
            val,
            url_query,
            url_get_params = {},
            a = document.createElement( 'a' ),
            default_options = {
                'url':         window.location.href,
                'unescape':    true,
                'convert_num': true
            };

        if ( typeof options !== "object" ) {
            options = default_options;
        } else {
            for ( option_key in default_options ) {
                if ( default_options.hasOwnProperty( option_key ) ) {
                    if ( options[ option_key ] === undefined ) {
                        options[ option_key ] = default_options[ option_key ];
                    }
                }
            }
        }

        a.href = options.url;
        url_query = a.search.substring( 1 );
        url_search_arr = url_query.split( '&' );

        if ( url_search_arr[ 0 ].length > 1 ) {
            for ( i = 0; i < url_search_arr.length; i += 1 ) {
                get_param = url_search_arr[ i ].split( "=" );

                if ( options.unescape ) {
                    key = decodeURI( get_param[ 0 ] );
                    val = decodeURI( get_param[ 1 ] );
                } else {
                    key = get_param[ 0 ];
                    val = get_param[ 1 ];
                }

                if ( options.convert_num ) {
                    if ( val.match( /^\d+$/ ) ) {
                        val = parseInt( val, 10 );
                    } else if ( val.match( /^\d+\.\d+$/ ) ) {
                        val = parseFloat( val );
                    }
                }

                if ( url_get_params[ key ] === undefined ) {
                    url_get_params[ key ] = val;
                } else if ( typeof url_get_params[ key ] === "string" ) {
                    url_get_params[ key ] = [ url_get_params[ key ], val ];
                } else {
                    url_get_params[ key ].push( val );
                }

                get_param = [];
            }
        }

        urlObj = {
            protocol:   a.protocol,
            hostname:   a.hostname,
            host:       a.host,
            port:       a.port,
            hash:       a.hash.substr( 1 ),
            pathname:   a.pathname,
            search:     a.search,
            parameters: url_get_params
        };

        return urlObj;
    };

	$(document).ready( app.init );

	return app;

})(window, document, jQuery);
