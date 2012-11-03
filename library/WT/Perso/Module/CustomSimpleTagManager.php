<?php
/**
 * Interface for WT_Module for modules implementing custom tags.
 * Support hook <strong>h_get_simpletag_display</strong>,<strong>h_get_simpletag_editor</strong>,
 * <strong>h_add_simple_tag</strong>, <strong>h_get_help_text_tag</strong> and <strong>h_get_expected_tags</strong>.
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

interface WT_Perso_Module_CustomSimpleTagManager {

	/**
	 * Get the list of Simple Tags supported by the module
	 * 
	 * @return array List of supported custom simple tags
	 */
	public function getCustomTags();
	
	/**
	 * Return the HTML code to be display for this tag.
	 * 
	 * @param string $tag Tag
	 * @param string $value Value of the tag
	 * @param string $context Context of the tag
	 * @param string $contextid Context ID of the tag, if it exists
	 * @return string HTML code to display
	 */
	public function h_get_simpletag_display($tag, $value, $context = null, $contextid = null);
	
	/**
	 * Returns HTML code for editing the custom tag.
	 * 
	 * @param string $tag Tag
	 * @param string $value Value of the tag
	 * @param string $element_id Element id from the edit interface, used fr jQuery
	 * @param string $element_name Element name from the edit interface, used to POST values for update
	 * @param string $context Tag context
	 * @param string $contextid Id of tag context
	 */
	public function h_get_simpletag_editor($tag, $value = null, $element_id = '', $element_name = '', $context = null, $contextid = null);
	
	/**
	 * Print all tags edit field for the context specified.
	 * 
	 * @param string $context Context of the edition
	 * @param int $level Level to which add the tags
	 */
	public function h_add_simple_tag($context, $level);

	/**
	 * Returns whether the tag has any help text
	 * 
	 * @param string $tag Tag
	 * @return bool True is help text, False otherwise 
	 */
	public function h_has_help_text_tag($tag);
	
	/**
	 * Returns $title and $text to display help text for the specified tag.
	 * 
	 * @param string $tag Tag
	 * @return array Help title and text
	 */
	public function h_get_help_text_tag($tag);
	
	/**
	 * Returns the list of expected tags, classified by type of records.
	 * 
	 * @return array List of expected tags
	 */
	public function h_get_expected_tags();
	
}




?>