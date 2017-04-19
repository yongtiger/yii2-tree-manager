jQuery(function () {
	$('.disclose').attr('title','Click to show/hide children');
	$('.disclose').on('click', function() {
	    $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
	    $(this).toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');
	});
});