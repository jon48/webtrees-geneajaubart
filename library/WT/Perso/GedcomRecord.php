<?php
/**
 * Decorator class to extend native GedcomRecord class.
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


class WT_Perso_GedcomRecord {

	protected $gedcomrecord;

	// Cached results from various functions.
	protected $_issourced=null;

	/**
	 * Contructor for the decorator
	 *
	 * @param WT_GedcomRecord $gedcomrecord_in The GedcomRecord to extend
	 */
	public function __construct(WT_GedcomRecord $gedcomrecord_in){
		$this->gedcomrecord = $gedcomrecord_in;
	}

	/**
	 * Return the native gedcom record embedded within the decorator
	 *
	 * @return WT_GedcomRecord Embedded gedcom record
	 */
	public function getDerivedRecord(){
		return $this->gedcomrecord;
	}
	
	/**
	 * Get an HTML link to this object, for use in sortable lists.
	 * 
	 * @return string HTML link
	 */
	public function getXrefLink() {
		global $SEARCH_SPIDER;
		if (empty($SEARCH_SPIDER)) {
			return '<a href="'.$this->gedcomrecord->getHtmlUrl().'#content" name="'.preg_replace('/\D/','',$this->gedcomrecord->getXref()).'">'.$this->gedcomrecord->getXref().'</a>';
		} else {
			return $this->gedcomrecord->getXref();
		}
	}
	
	/**
	 * Add additional options to the core format_first_major_facts function.
	 * If no option is suitable, it will try returning the core function.
	 *
	 * Option 10 : display <i>factLabel shortFactDate shortFactPlace</i>
	 *
	 * @param string $facts List of facts to find information from
	 * @param int $style Style to apply to the information. Number >= 10 should be used in this function, lower number will return the core function.
	 * @return string Formatted fact description
	 */
	public function format_first_major_fact($facts, $style) {
		foreach ($this->gedcomrecord->getFacts($facts) as $fact) {
			// Only display if it has a date or place (or both)
			if (($fact->getDate() || $fact->getPlace()) && $fact->canShow()) {
				switch ($style) {
					case 10:
						return '<i>'.$fact->getLabel().' '.WT_Perso_Functions_Print::formatFactDateShort($fact).'&nbsp;'.WT_Perso_Functions_Print::formatFactPlaceShort($fact, '%1').'</i>';
						break;
					default:
						return $this->gedcomrecord->format_first_major_fact($facts, $style);
				}
			}
		}
		return '';
	}

	/**
	 * Check if the IsSourced information can be displayed
	 *
	 * @param int $access_level
	 * @return boolean
	 */
	public function canDisplayIsSourced($access_level=WT_USER_ACCESS_LEVEL){
		global $global_facts;

		if(!$this->gedcomrecord->canShow($access_level)) return false;

		if (isset($global_facts['SOUR'])) {
			return $global_facts['SOUR']>=$access_level;
		}

		return true;
	}

	/**
	 * Check if a gedcom record is sourced
	 * Values:
	 * 		- -1, if the record has no sources attached
	 * 		- 1, if the record has a source attached
	 * 		- 2, if the record has a source, and a certificate supporting it
	 *
	 * @return int Level of sources
	 */
	public function isSourced(){
		if($this->_issourced != null) return $this->_issourced;
		$this->_issourced=-1;
		$sourcesfacts = $this->gedcomrecord->getFacts('SOUR');
		foreach($sourcesfacts as $sourcefact){
			$this->_issourced=max($this->_issourced, 1);
			if($sourcefact->getAttribute('_ACT')){
				$this->_issourced=max($this->_issourced, 2);
			}
		}
		return $this->_issourced;
	}

	/**
	 * Check is an event associated to this record is sourced
	 *
	 * @param string $eventslist
	 * @return int Level of sources
	 */
	public function isFactSourced($eventslist){
		$isSourced=0;
		$facts = $this->gedcomrecord->getFacts($eventslist);
		foreach($facts as $fact){
			if($isSourced<WT_Perso_Fact::MAX_IS_SOURCED_LEVEL){
				$dfact = new WT_Perso_Fact($fact);
				$tmpIsSourced = $dfact->isSourced();
				if($tmpIsSourced != 0) {
					if($isSourced==0) {
						$isSourced =  $tmpIsSourced;
					}
					else{
						$isSourced = max($isSourced, $tmpIsSourced);
					}
				}
			}
		}
		return $isSourced;
	}


}


?>