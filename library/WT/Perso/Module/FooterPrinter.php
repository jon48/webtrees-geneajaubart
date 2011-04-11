<?php
/**
 * Interface for WT_Module for modules providing additional footer information.
 * Support hook <strong>h_config_tab_name</strong> and <strong>h_config_tab_content</strong>
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

interface WT_Perso_Module_FooterPrinter {

	/**
	 * Print additional footer.
	 */
	public function h_print_footer();
	
}


?>