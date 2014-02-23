/*
 * Javascript to include before the Sosa module menu to compute Sosas from an individual
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

function compute_sosa(sosa_pid){
	if($('#computesosadlg').length == 0) {
		$('body').append('<div id="computesosadlg" title="' + PS_Dialog_Title + '"><div id="sosaloadingarea"></div></div>');
	}
	$('#computesosadlg').dialog({
		modal: true,
		closeOnEscape: false,
		width: 300,
		open: function(event, ui) {
			$("button[title='close']").hide();
			$('#sosaloadingarea').empty().html('<i class="icon-loading-small"></i>');
			$('#sosaloadingarea').load('module.php?mod=perso_sosa&mod_action=computesosaindi&pid=' + sosa_pid, 
					function(){
						$("button[title='close']").show();
						setTimeout(function(){
							$('#computesosadlg').dialog('close');
						}, 2000);
			});		
		}
	});	
}