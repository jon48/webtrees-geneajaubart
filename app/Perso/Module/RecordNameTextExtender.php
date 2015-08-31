<?php
/**
 * Interface for WT_Module for modules providing an extension feature for texts describing records names.
 * Support hook <strong>h_rn_prepend</strong> and <strong>h_rn_append</strong>
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Module_RecordNameTextExtender {

	/**
	 * Insert some content before the record name text.
	 * 
	 * @param WT_GedcomRecord $grec Gedcom record
	 */
	public function h_rn_prepend(WT_GedcomRecord $grec);
	
	/**
	 * Insert some content after the record name text.
	 * 
	 * @param WT_GedcomRecord $grec Gedcom record
	 */
	public function h_rn_append(WT_GedcomRecord $grec);
	
}

?>