(function ($) {
	'use strict';

	jQuery( document ).ready(
		function ($) {
			// Check if we are in the post editor for posts or pages
			if ($( '.view' ).length > 0) {
				// Target all <a> tags within the post editor
				$( '.view' ).find( 'a' ).each(
					function () {
						// Check if the href attribute exists and is not empty
						if ($( this ).attr( 'href' ) && $( this ).attr( 'href' ).trim() !== '') {
							// Add target="_blank" attribute
							$( this ).attr( 'target', '_blank' );
						}
					}
				);
			}
		}
	);

}(jQuery));