/**
 * ElastiGallery - v0.1.0 - 2015-06-01
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
            items:             5,
            itemsDesktop:      [ 1199, 5 ],
            itemsDesktopSmall: [ 979, 3 ],
            itemsTablet:       [ 768, 5 ],
            itemsMobile:       [ 479, 3 ],
            navigation:        false,
        });
	};

	$(document).ready( app.init );

	return app;

})(window, document, jQuery);
