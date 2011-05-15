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
	
}


?>