// JavaScript Document

jQuery(document).ready(function($) {
	// Get the 'current' selector.
	var carousel = $('#carousel');
	var curVal = $('.current');
	var curr = carousel.children('li').index(curVal);
	
	if(curr == -1){curr = 0}
	
    carousel.elastislide({
		start: curr,
		minItems: 3
	});
	
	$('.fancybox').fancybox();
});