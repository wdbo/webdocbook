/**
Inspired by http://www.creativejuiz.fr/blog/tutoriels/mise-jour-jquery-effet-smoothscroll-au-chargement-page
*/

(function($) {
	$.fn.juizScrollTo = function( speed ) {
		if ( !speed ) var speed = 'slow';
 
		// coeur du plugin
		return this.each( function() {
			$(this).on('click', function() {
				var goscroll = false;
				var the_hash = $(this).attr("href");
				var regex = new RegExp("(.*)\#(.*)","gi");
				var the_element = '';
 
				if (the_hash.match("\#(.+)")) {
					the_hash = the_hash.replace(regex,"$2");
//					the_hash = escapeSelector(the_hash);
					if ($("[id=\"" + the_hash + "\"]").length>0) {
						the_element = "[id=\"" + the_hash + "\"]";
						goscroll = true;
					}
					else if ($("a[name=\"" + the_hash + "\"]").length>0) {
						the_element = "a[name=\"" + the_hash + "\"]";
						goscroll = true;
					}
					if (goscroll) {
						var container = 'html';
						if ( $.browser.safari || $.browser.chrome ) container = 'body';
						$(container).animate( {
							scrollTop: $(the_element).offset().top-60
						}, speed, function() {
							tab_n_focus(the_hash);
    						write_hash(the_hash);
						});
						return false;
					}
				}
			});
		});
 
		// fonctions
 
		// écriture du hash
		function write_hash( the_hash ) {
			document.location.hash =  "r:"+the_hash;
		}
 
		// accessibilité au clavier
		function tab_n_focus( the_hash ) {
			$(the_hash).attr('tabindex','0').focus().removeAttr('tabindex');
		}
 
	};
 
	// appel de la fonction sur toutes les ancres !
	$('a').juizScrollTo('slow');
 
	// fonction de slide au chargement
	function trigger_click_for_slide() {
		var the_hash = document.location.hash;
		if ( the_hash )
			$('a[href~="'+the_hash+'"]').trigger('click');
	}
	trigger_click_for_slide();
 
})(jQuery);
