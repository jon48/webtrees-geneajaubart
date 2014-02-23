/*
 * Javascript to include in the administration module pages to compute
 * asynchronously Sosas
 * 
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

function calculateSosa(ged_id) {
	jQuery('#bt_' + ged_id).attr('disabled', 'disabled');
	jQuery('#btsosa_' + ged_id).empty().html('<i class="icon-loading-small"></i>');
	jQuery('#btsosa_' + ged_id).load(
		'module.php?mod=perso_sosa&mod_action=computesosa&gid=' + ged_id,
		function() {
			jQuery('#bt_' + ged_id).removeAttr('disabled');
		});
}