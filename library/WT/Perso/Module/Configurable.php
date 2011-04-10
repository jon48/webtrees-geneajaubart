<?php
/**
 * {Description}
 *
 * @package webtrees
 * @subpackage SubPackage
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Module_Configurable {
	
	/**
	 * Print the text to display as title of the config tab for this module.
	 * 
	 * @return string Title of module config tab
	 */
	public function h_config_tab_name();
	
	/**
	 * Print the content of the config tab for this module.
	 * 
	 * @return string Title of module config tab
	 */
	public function h_config_tab_content();
	
	/**
	 * Validate the value sent for the setting against specific module rules.
	 * Can return either a modified value, or the error message 'ERROR_VALIDATION' is the validation fails.
	 * 
	 * @param string $setting Setting name
	 * @param string $value Replacement setting value to be validated.
	 * @return string Result of the validation (value or error message)
	 */
	public function validate_config_settings($setting, $value);
	
}

?>