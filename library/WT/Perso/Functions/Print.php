<?php
/**
 * Additional functions for displaying information
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

class WT_Perso_Functions_Print {
	
	/**
	 * Return HTML code to include a flag icon in facts description
	 *
	 * @param string $factrec GEDCOM fact record
	 * @return string HTML code of the inserted flag
	 */
	public static function getFactPlaceIcon($factrec) {
		$html='';
		$ctpl = preg_match("/2 PLAC (.*)/", $factrec, $match);
		if($ctpl>0){
			$iconPlace=WT_Perso_Functions_Map::getPlaceIcon($match[1], 50);
			if(count($iconPlace) != 0){
				$html.='<div class="fact_flag">'.$iconPlace.'</div>';
			}
		}
		return $html;
	}
	
} 

?>