(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	// Simple Accordion -- https://codepen.io/gecugamo/pen/xGLyXe

	$.fn.simpleAccordion = function() {

		this.on("click", ".accordion__control", function() {

				// Toggle the panel next to the item that was clicked
				$(this).toggleClass("accordion__control--active").next().slideToggle(250);

		});
	
		// Return jQuery object for method chaining
		return this;
	}
	
	// Count results in view
	
	function updateResultsCount() {
		var resultCount = $('.inspection-result:not(.result-is-hidden)').length;
		if ( resultCount == 0 ) {
			$('#results-count').html('');					
		} else {
			$('#results-count').html(resultCount);		
		}
	}

	// Set viewer for all results
	
	function setViewer( viewer ) {

		if ( viewer == 'var_export' ) {
			$('.item[data-tab="var_export"]').each( function() {
				$( this ).click();
			});
		} 
		
		if ( viewer == 'var_dump' ) {
			$('.item[data-tab="var_dump"]').each( function() {
				$( this ).click();
			});
		} 
		
		if ( viewer == 'print_r' ) {
			$('.item[data-tab="print_r"]').each( function() {
				$( this ).click();
			});
		} 
		
		

	}
		
	
	
	$(document).ready( function() {

		// Make page header sticky on scroll. Using https://github.com/AndrewHenderson/jSticky
		
		$('#vi-header').sticky({
			topSpacing: 0, // Space between element and top of the viewport (in pixels)
			zIndex: 100, // z-index
			stopper: '', // Id, class, or number value
			stickyClass: 'vi-sticky' // Class applied to element when it's stuck. Class name or false.
		})
		
		updateResultsCount();

		// Expand or collapse all individual results

		$('.toggle-results').click( function() {
			var text = $( this ).html();
			if ( text == 'Expand all' ) {
				$( this ).html( 'Collapse all' );
				$('.accordion__control').each( function() {
					if ( ! $( this ).hasClass( 'accordion__control--active' ) ) {
						$( this ).addClass( 'accordion__control--active' ).next().slideToggle(250);
					}
				});
			} else if ( text == 'Collapse all' ) {
				$( this ).html( 'Expand all' );
				$('.accordion__control').each( function() {
					if ( $( this ).hasClass( 'accordion__control--active' ) ) {
						$( this ).removeClass( 'accordion__control--active' ).next().slideToggle(250);
					}
				});
			} else {}
		});

		

		// Set the viewer from WP options via get_option -> wp_localize_script

		var viewer = viVars.viewer;
		// alert( 'Current viewer is ' + viewer );
		$('#results_viewer').val(viewer).change();
		setViewer( viewer );

		// Change viewer
		
		$('#results_viewer').change( function() {

			var viewer = $( this ).find("option:selected").attr('value');
			// alert( $( this ).find("option:selected").attr('value') );

			setViewer( viewer );

			$.ajax({
				url: ajaxurl,
				data: {
					'action': 'vi_set_viewer',
					'viewer': viewer
				},
				success:function(data) {
					// var data = data.slice(0,-1); // remove strange trailing zero in string returned by AJAX call
					// const response = JSON.parse(data); // create an object
					// alert( 'Viewer has been set to ' + response.viewer );
				},
				error:function(errorThrown) {
					console.log(errorThrown);
				}
			});

		});

        // Generate sample results

		$('#generate-sample-results').click( function( eventObject ) {

			eventObject.preventDefault();
			
			$('#results-spinner').show();

			var button = $( this );

			$.ajax({
				url: ajaxurl,
				data: {
					'action':'vi_generate_sample_results'
				},
				success:function(data) {

					var data = data.slice(0,-1); // remove strange trailing zero in string returned by AJAX call
					var response = JSON.parse(data);

					if ( response.success == true ) {

						AjaxManual('#inspection-results');

						viewer = $('#results_viewer').find("option:selected").attr('value');

						setTimeout(function() {
							setViewer( viewer );
							$('#results-spinner').hide();
							updateResultsCount();
						}, 2500);

					}

				},
				error:function(errorThrown) {
					console.log(errorThrown);
				}
			});

		});

		

        // Refresh results

		$('#refresh-results').click( function( eventObject ) {

			eventObject.preventDefault();

			$('#results-count').html('');
			$('#results-spinner').show();

			AjaxManual('#inspection-results');

			viewer = $('#results_viewer').find("option:selected").attr('value');

			setTimeout(function() {
				updateResultsCount();
				
				setViewer( viewer );
				$('#results-spinner').hide();
			}, 3000);

		});

        // Clear inspection results

		$('#clear-results').click( function( eventObject ) {

			eventObject.preventDefault();

			$('#results-spinner').show();

			var button = $( this );

			$.ajax({
				url: ajaxurl,
				data: {
					'action':'vi_clear_results'
				},
				success:function(response) {

					response = JSON.parse(response)

					if ( response.success == true ) {
						$('#inspection-results').empty();
						$('#inspection-results').prepend('<div class="no-results">There is no data in the inspection log.</div>');
						$('#results-spinner').hide();
						$('.color-filters').removeClass('color-chosen');
						$('.color-filter').removeClass('color-active');
						
						updateResultsCount();
					}

				},
				error:function(errorThrown) {
					console.log(errorThrown);
				}
			});

		});

		$("#auto_load").change(function() {
		    if(this.checked) {
		        autoReload = setInterval( function(){ AjaxAutoLoad("#inspection-results"); }, 2500);
		    } else {
				clearInterval(autoReload);
			}
		});

		// simpleAccordion init

		$(".accordion").simpleAccordion();

		// Fomantic UI accordion init
		
		$(".ui.accordion").accordion();
		
		

   		// Modal for sponsoring plugin dev and maintenance: https://stephanwagner.me/jBox

   		var sponsorModal = new jBox('Modal', {
   			attach: '#plugin-sponsor',
   			trigger: 'click', // or 'mouseenter'
   			// content: 'Test'
   			content: $('#vi-sponsor'),
   			width: 740, // pixels
   			closeButton: 'box',
   			addClass: 'plugin-sponsor-modal',
   			overlayClass: 'plugin-sponsor-modal-overlay',
   			target: '#wpwrap', // where to anchor the modal
   			position: {
   				x: 'center',
   				y: 'top'
   			},
   			// fade: 1000,
   			animation: {
   				open: 'slide:top',
   				close: 'slide:top'
   			}
   		});

   		$('#generate-sample-results, #generate-sample-results-premium, #clear-results').click( function() {
			$.ajax({
				url: 'https://bowo.io/vi-rf-btn',
				method: 'GET',
				dataType: 'jsonp',
				crossDomain: true
				// success: function(response) {
				// 	console.log(response);
				// }
			});
		});
   		
   		$('#plugin-sponsor').click( function() {
			$.ajax({
				url: 'https://bowo.io/vi-sp-btn',
				method: 'GET',
				dataType: 'jsonp',
				crossDomain: true
				// success: function(response) {
				// 	console.log(response);
				// }
			});
		});

   		$('#plugin-upgrade').click( function() {
			$.ajax({
				url: 'https://bowo.io/vi-up-btn-trk',
				method: 'GET',
				dataType: 'jsonp',
				crossDomain: true
				// success: function(response) {
				// 	console.log(response);
				// }
			});
		});

	}); // END OF $(document).ready();END

})( jQuery );

// Manually reload results

function AjaxManual(selector){(function($){
	$(selector).css({"opacity":"0.2","pointer-events":"none","cursor":"wait"});
	AjaxAutoLoad(selector);
})(jQuery);}

// Auto reload results

var autoReload = null;
var count = 0;
function AjaxAutoLoad(selector){(function($){
	$.ajax({
        type: "GET",
        url: window.location.href
    }).done(function(result){
    	var newResults = $(result).find(selector).html();
    	$(selector).html( newResults );
		
		$(".accordion__control").click(function() {
			$("#auto_load").prop( "checked", false );
			clearInterval(autoReload);
			openClose(this);
		});
		
		$(selector).removeAttr("style");
    });
})(jQuery);}

// Toggle inspection result accordion

function openClose(selector){(function($){
	$(selector).toggleClass("accordion__control--active");
	$(selector).parents(".accordion").find(".accordion__panel").slideToggle();
})(jQuery);}