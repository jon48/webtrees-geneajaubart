<?php
/**
 * Interface for WT_Module for modules providing additional footer information.
 * Support hook <strong>h_print_footer</strong>
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Module_FooterExtender {

	/**
	 * Print additional footer.
	 */
	public function h_print_footer();
	
}


?>