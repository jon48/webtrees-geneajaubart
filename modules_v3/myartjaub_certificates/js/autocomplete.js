/*
 * Javascript to include in the Edit interface to autocomplete the available certificates
 * 
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

jQuery(document).ready(function($){
	
	$('._ACT').each(function(i, el){
		el = $(el);
		el.autocomplete({
			source: function(request, response) {
				$.ajax({
					url: 'module.php',
					datatype: 'json',
					data : {
						mod: 'myartjaub_certificates',
						mod_action: 'Certificate@autocomplete',
						ged: WT_GEDCOM,
						term : request.term,
						city : $('#certifCity' + el.attr('id').substring(10)).val()
					},
					success: function(data) {
						response(data);
					},
		            error: function(xhr, textStatus, errorThrown) {
		                alert('Error: ' + xhr.statusText);
		            }
				});
			},
			delay:500,
			select: function(event, ui){
				el.val(ui.item.value);
				el.change();
			}
		});
	});
	
}); 
