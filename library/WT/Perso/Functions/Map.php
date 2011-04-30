<?php
/**
 * Additional functions for maps (based on GoogleMaps module)
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once WT_ROOT.WT_MODULES_DIR.'googlemap/googlemap.php';

class WT_Perso_Functions_Map {
	
	/**
	 * Return HTML code for the flag icon of the requested place
	 *
	 * @param string $place The place to find
	 * @param int $height Height of the returned icon
	 * @return string HTML code of the flag icon
	 */
	public static function getPlaceIcon($place, $height){
		$latlongval = get_lati_long_placelocation($place);
		if ((count($latlongval) == 0) && (!empty($GM_DEFAULT_TOP_VALUE))) {
			$latlongval = get_lati_long_placelocation($place.", ".$GM_DEFAULT_TOP_VALUE);
		}
		if(count($latlongval) != 0){
			return '<img class="flag_gm_h'.$height.'" src="'.WT_MODULES_DIR.'googlemap/'.$latlongval['icon'].'" />';
		}
		else{
			return '';
		}
	}
	
} 

?>