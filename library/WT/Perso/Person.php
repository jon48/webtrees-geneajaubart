<?php
/**
 * Decorator class to extend native Person class.
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

class WT_Perso_Person extends WT_Perso_GedcomRecord {

	// Cached results from various functions.
	private $_titles=null;

	/**
	 * Get an array of the different titles (tag TITL) of an individual
	 *
	 * @return array Array of titles
	 */
	public function getTitles(){
		if(is_null($this->_titles)){
			$this->_titles=array();
			$ct = preg_match_all('/1 TITL (.*)/', $this->gedrec, $match);
			if($ct>0){
				$titles=$match[1];
				foreach($titles as $title){
					$ct2 = preg_match_all('/(.*) (('.get_module_setting('perso_general', 'PG_TITLE_PREFIX', '').')(.*))/', $title, $match2);
					if($ct2>0){
						$this->_titles[$match2[1][0]][]= trim($match2[2][0]);
					}
					else{
						$this->_titles[$title][]="";
					}
				}
			}
		}
		return $this->_titles;
	}

}

?>