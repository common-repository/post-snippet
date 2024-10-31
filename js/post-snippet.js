jQuery(document).ready(function($){

	// accordion onclick
	$('.post_snippet_anchor.accordion').each(function() {
        $(this).click(function(){
			$(this).next('.post_content_inner').slideToggle("slow");
			return false;
		});
    });
	// accordion "collapse" link  onclick
	$('.top_collapse').each(function() {
        $(this).click(function(){
			var elInner = $(this).parent();
			var elOffset = $(this).closest( ".post_content_inner" ).prev(".post_snippet_anchor.accordion").find('.post_snippet').offset().top;
			$('html, body').animate({
				scrollTop: elOffset
			}, 1000, function(){$(elInner).slideUp('fast');}); 
			
			return false;
			
		});
    });
		
	/* Responsive Wide Widget */
	
	function widgetWideResponsiveSettings() {
		$('.wide .post_snippet_title, .wide .post_snippet_content, .wide .post_snippet_image').each(function() {
			if($(document).width() > 500) {
				$(this).removeClass($(this).data('show-responsive'));
				$(this).addClass($(this).data('show'));
			} else {
				$(this).removeClass($(this).data('show'));
				$(this).addClass($(this).data('show-responsive'));	
			}
		})
		$('.wide .post_snippet_content').each(function() {
			if($(document).width() > 500) {
				$(this).html($(this).data('excerpt'));
			} else {
				$(this).html($(this).data('excerpt-responsive'));
			}
		})
	}
	widgetWideResponsiveSettings();
	
	$(window).resize(function() {
		widgetWideResponsiveSettings();
	})

});
