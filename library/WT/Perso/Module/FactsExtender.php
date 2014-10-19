<?php
/**
 * Interface for WT_Module for modules altering facts.
 * Support hook <strong>h_add_facts</strong>
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Module_FactsExtender {

	/**
	 * Add additional facts to a gedcom record
	 * 
	 * @param WT_GedcomRecord $record Gedcom record to add facts to
	 * @param array $factarray List of existing facts of the record
	 * @return array Facts to be added
	 */
	public function h_add_facts(WT_GedcomRecord $record, $factarray);
	
}
