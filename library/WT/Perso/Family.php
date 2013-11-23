<?php
/**
 * Decorator class to extend native Family class.
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

class WT_Perso_Family extends WT_Perso_GedcomRecord {

	// Cached results from various functions.
	protected $_ismarriagesourced = null;
	
	/**
	 * Extend WT_Family getInstance, in order to retrieve directly a WT_Perso_Family object 
	 *
	 * @param unknown_type $data Data to identify the individual
	 * @return WT_Perso_Family|null WT_Perso_Family instance
	 */
	public static function getIntance($data){
		$dfam = null;
		$fam = WT_Family::getInstance($data);
		if($fam){
			$dfam = new WT_Perso_Family($fam);
		}
		return $dfam;
	}
	
	/**
	* Check if this family's marriages are sourced
	*
	* @return int Level of sources
	* */
	function isMarriageSourced(){
		if($this->_ismarriagesourced != null) return $this->_ismarriagesourced;
		$this->_ismarriagesourced = $this->isFactSourced(WT_EVENTS_MARR.'|MARC');
		return $this->_ismarriagesourced;
	}
		
}

?>