<?php
/**
 * Additional functions for editing.
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

class WT_Perso_Functions_Edit {

	/**
	 * Return HTML code to print an inline editable text box, referring to Perso Config saving process.
	 * 
	 * @param string $name Setting id (not setting name)
	 * @param string $value Setting value
	 * @return string HTML code for editable textbox
	 */
	static public function edit_module_field_inline($name, $value){
		return
			'<span class="editable" id="' . $name . '">' . htmlspecialchars($value) . '</span>' .
			WT_JS_START .
			'jQuery("#' . $name . '").editable("' . WT_SERVER_NAME . WT_SCRIPT_PATH . 'module.php?mod=perso_config&mod_action=admin_update_setting", {submit:"&nbsp;&nbsp;' . WT_I18N::translate('OK') . '&nbsp;&nbsp;", style:"inherit", placeholder: "'.WT_I18N::translate('click to edit').'"})' .
			WT_JS_END;
	}

}

?>