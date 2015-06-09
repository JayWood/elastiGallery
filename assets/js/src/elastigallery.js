/**
 * ElastiGallery
 * http://plugish.com
 *
 * Copyright (c) 2015 Jay Wood
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */

window.Elastigallery = (function(window, document, $, undefined){
	'use strict';

	var app = {};

	app.init = function() {
        
        app.current_url =  app.urlObject( { 'url': window.location } );
        app.owl_settings = {
            items:             5,
            itemsDesktop:      [ 1199, 5 ],
            itemsDesktopSmall: [ 979, 3 ],
            itemsTablet:       [ 768, 5 ],
            itemsMobile:       [ 479, 3 ],
            navigation:        false,
        };

        //if ( window.console ) {
        //    window.console.log( url_object );
        //}

        $( '.elastigallery' ).each( app.initialize_oc );
        
        //$( '.elastigallery' ).owlCarousel();
	};

    app.initialize_oc = function(){
        var $that = $(this),
            carousel = $that.owlCarousel( app.owl_settings );

        if ( app.current_url && app.current_url.hash ) {
            var $element = $that.find( '#' + app.current_url.hash ); // try and find the element.
            // @TODO: for each $element use .index() I believe to get it's index then oc.jumpTo on system load.
        }
    };

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
