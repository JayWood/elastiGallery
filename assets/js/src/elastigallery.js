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
