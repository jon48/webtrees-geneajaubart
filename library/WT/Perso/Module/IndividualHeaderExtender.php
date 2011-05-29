<?php
/**
 * Interface for WT_Module for modules providing an extension feature for individual header.
 * Support hook <strong>h_extend_top_center</strong> and <strong>h_extend_top_right</strong>
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

interface WT_Perso_Module_IndividualHeaderExtender {
		
	/**
	 * Get HTML code for extending the center part of the individual header
	 *
	 * @param WT_Controller_Individual $ctrlIndi Individual page controller
	 * @return string HTML code extension
	 */
	public function h_extend_top_center(WT_Controller_Individual $ctrlIndi);
	
	/**
	 * Get HTML code for extending the right part of the individual header
	 *
	 * @param WT_Controller_Individual $ctrlIndi Individual page controller
	 * @return string HTML code extension
	 */
	public function h_extend_top_right(WT_Controller_Individual $ctrlIndi);
	
}

?>