/*
 * {Description}
 *
 * @package webtrees
 * @subpackage Perso
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

jQuery(document).ready(function($){

	$('._CITY').change(function(){
		var id = this.id.substring(10);
		var certifFileEl = $('#certifFile' + id);
		certifFileEl.val('');
		certifFileEl.change();
	});
	
	$('._ACT').change(function(){
		var id = this.id.substring(10);
		var hidden = $('#' + id);
		if(hidden.length){
			if(this.value.length > 0){
				hidden.val($('#certifCity'+id).val() + '/' + this.value);
			}
			else{
				hidden.val('');
			}
		}
	});
	
});