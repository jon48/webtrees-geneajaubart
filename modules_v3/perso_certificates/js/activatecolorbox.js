/*
 * Javascript to include to activate Colorbox on certificates
 * 
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

var isCertifColorboxActive = 0;

function activatecertifcolorbox(){

	jQuery("body").on("click", "a.certgallery", function(event) {
		// Enable colorbox for images
		jQuery("a[type^=image].certgallery").colorbox({
			photo:         true,
			maxWidth:      "95%",
			maxHeight:     "95%",
			rel:           "certgallery", // Turn all images on the page into a slideshow
			slideshow:     false,
			// Add wheelzoom to the displayed image
			onComplete:    function() {
				jQuery(".cboxPhoto").wheelzoom();
			},
			title:		function(){
				var url = jQuery(this).data("obje-url");
				var img_title = jQuery(this).data("title");
				return "<a href=\"" + url + "\">" + img_title + "</a>";
			}
		});		
	});
}
