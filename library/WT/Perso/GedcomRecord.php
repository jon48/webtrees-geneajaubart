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

}


?>