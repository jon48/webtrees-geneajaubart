<?php
/**
 * Interface for WT_Module for modules providing an extension feature for texts describing Facts sources.
 * Support hook <strong>h_fs_prepend</strong> and <strong>h_fs_append</strong>
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Module_FactSourceTextExtender {

	/**
	 * Insert some content before the fact source text.
	 * 
	 * @param string $srec Source fact record
	 */
	public function h_fs_prepend($srec);
	
	/**
	 * Insert some content after the fact source text.
	 * 
	 * @param string $srec Source fact record
	 */
	public function h_fs_append($srec);
	
}

?>