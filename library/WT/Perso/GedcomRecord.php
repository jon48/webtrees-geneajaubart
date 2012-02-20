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
	protected $gedrec;

	// Cached results from various functions.
	protected $_issourced=null;

	/**
	 * Contructor for the decorator
	 *
	 * @param WT_GedcomRecord $gedcomrecord_in The GedcomRecord to extend
	 */
	public function __construct(WT_GedcomRecord $gedcomrecord_in){
		$this->gedcomrecord = $gedcomrecord_in;
		$this->gedrec = $gedcomrecord_in->getGedcomRecord();
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
		foreach ($this->gedcomrecord->getAllFactsByType(explode('|', $facts)) as $event) {
			// Only display if it has a date or place (or both)
			if (($event->getDate() || $event->getPlace()) && $event->canShow()) {
				switch ($style) {
					case 10:
						return '<i>'.$event->getLabel(true).' '.WT_Perso_Functions_Print::formatFactDateShort($event).'&nbsp;'.WT_Perso_Functions_Print::formatFactPlaceShort($event, '%1').'</i>';
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

		if(!$this->gedcomrecord->canDisplayDetails($access_level)) return false;

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
		$nbSources = preg_match("/1 SOUR (.*)/", $this->gedrec);
		for($i=1;$i<=$nbSources;$i++){
			$this->_issourced=max($this->_issourced, 1);
			$source = get_sub_record(1, '1 SOUR',  $this->gedrec, $i);
			if(preg_match('/2 _ACT (.*)/', $source) ){
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
	public function isEventSourced($eventslist){
		$isSourced=0;
		foreach (explode('|', $eventslist) as $event) {
			if($isSourced<WT_Perso_Event::MAX_IS_SOURCED_LEVEL){
				foreach($this->gedcomrecord->getAllFactsByType($event) as $fact){
					if($isSourced<WT_Perso_Event::MAX_IS_SOURCED_LEVEL){
						$fact = new WT_Perso_Event($fact);
						$tmpIsSourced = $fact->isSourced();
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
			}
		}
		return $isSourced;
	}


}


?>