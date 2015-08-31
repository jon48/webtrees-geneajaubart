<?php
/**
 * Interface for WT_Module for modules providing an extension feature for individual header.
 * Support hooks <strong>h_extend_indi_header_icons</strong>, <strong>h_extend_indi_header_left</strong> and <strong>h_extend_indi_header_right</strong>
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Module_IndividualHeaderExtender {
		
	/**
	 * Get HTML code for extending the icons in the individual header
	 *
	 * @param WT_Controller_Individual $ctrlIndi Individual page controller
	 * @return string HTML code extension
	 */
	public function h_extend_indi_header_icons(WT_Controller_Individual $ctrlIndi);
	
	/**
	 * Get HTML code for extending the left part of the individual header
	 *
	 * @param WT_Controller_Individual $ctrlIndi Individual page controller
	 * @return string HTML code extension
	 */
	public function h_extend_indi_header_left(WT_Controller_Individual $ctrlIndi);
	
	/**
	 * Get HTML code for extending the right part of the individual header
	 *
	 * @param WT_Controller_Individual $ctrlIndi Individual page controller
	 * @return string HTML code extension
	 */
	public function h_extend_indi_header_right(WT_Controller_Individual $ctrlIndi);
	
}

?>