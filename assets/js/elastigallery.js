/**
 * ElastiGallery - v0.1.0 - 2015-05-18
 * http://plugish.com
 *
 * Copyright (c) 2015;
 * Licensed GPLv2+
 */
/*jslint browser: true */
/*global jQuery:false */

window.Elastigallery = (function(window, document, $, undefined){
	'use strict';

	var app = {};

	app.init = function() {
        $( '.elastigallery' ).owlCarousel({
            items: 4,
            navigation: false,
        });
	};

	$(document).ready( app.init );

	return app;

})(window, document, jQuery);
