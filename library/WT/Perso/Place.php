<?php
/**
 * Decorator class to extend native Place class.
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


class WT_Perso_Place {

	protected $_place;

	/**
	 * Contructor for the decorator
	 *
	 * @param WT_Place $place_in The Place to extend
	 */
	public function __construct(WT_Place $place_in){
		$this->_place = $place_in;
	}

	/**
	 * 
	 * Returns an instance of WT_Perso_Place, based on the string provided.
	 *
	 * @param string $place_str
	 * @param int $gedcom_id
	 * @return WT_Perso_Place|null Instance of WT_Perso_Place, if relevant
	 */
	public static function getIntance($place_str, $gedcom_id = WT_GED_ID){
		$dplace = null;
		if(strlen($place_str) > 0){
			$dplace = new WT_Perso_Place(new WT_Place($place_str, $gedcom_id));
		}
		return $dplace;
	}
	
	/**
	 * Return the native place record embedded within the decorator
	 *
	 * @return WT_Place Embedded place record
	 */
	public function getDerivedPlace(){
		return $this->_place;
	}
	
	/**
	 * Return HTML code for the place formatted as requested.
	 * The format string should used %n with n to describe the level of division to be printed (in the order of the GEDCOM place).
	 * For instance "%1 (%2)" will display "Subdivision (Town)".
	 *
	 * @param string $format Format for the place
	 * @param bool $anchor Option to print a link to placelist
	 * @return string HTML code for formatted place
	 */
	public function getFormattedName($format, $anchor = false){
		global $SEARCH_SPIDER;
		
		$html='';
		
		$levels = explode(', ', $this->_place->getGedcomName());
		$nbLevels = count($levels);
		$displayPlace = $format;
		preg_match_all('/%[^%]/', $displayPlace, $matches);
		foreach ($matches[0] as $match2) {
			$index = str_replace('%', '', $match2);
			if(is_numeric($index) && $index >0 && $index <= $nbLevels){
				$displayPlace = str_replace($match2, $levels[$index-1] , $displayPlace);
			}
			else{
				$displayPlace = str_replace($match2, '' , $displayPlace);
			}
		}
		if ($anchor && !$SEARCH_SPIDER) {
			$html .='<a href="' . $this->_place->getURL() . '">' . $displayPlace . '</a>';
		} else {
			$html .= $displayPlace;
		}
		
		return $html;
		
	}

}


?>