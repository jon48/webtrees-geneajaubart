<?php
/**
 * Interface for WT_Module for modules extending header.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Module_HeaderExtender {

	/**
	 * Print additional header.
	 */
	public function h_print_header();
	
}

?>