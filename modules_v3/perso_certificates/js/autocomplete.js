/*
 * Javascript to include in the Edit interface to autocomplete the available certificates
 * 
 * @package webtrees
 * @subpackage Perso
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date: 2011-08-20 21:38:01 +0100 (sam., 20 août 2011) 
 * $ $HeadURL$
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
						mod: 'perso_certificates',
						mod_action: 'autocomplete',
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
