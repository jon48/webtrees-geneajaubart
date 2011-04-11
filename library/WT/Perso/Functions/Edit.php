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
	 * Return HTML code to print an inline editable text box, used in Perso Config saving process.
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
	
	/**
	 * Return HTML code to print an inline editable text area, used in Perso Config saving process.
	 * 
	 * @param string $name Setting id (not setting name)
	 * @param string $value Setting value
	 */
	static public function edit_module_longfield_inline($name, $value){
		return
			'<span class="editable" id="' . $name . '">' . htmlspecialchars($value) . '</span>' .
			WT_JS_START .
			'jQuery("#' . $name . '").editable("' . WT_SERVER_NAME . WT_SCRIPT_PATH . 'module.php?mod=perso_config&mod_action=admin_update_setting", {type:"textarea", submit:"&nbsp;&nbsp;' . WT_I18N::translate('OK') . '&nbsp;&nbsp;", style:"inherit", rows: 5, placeholder: "'.WT_I18N::translate('click to edit').'"})' .
			WT_JS_END;
	}
	
	/**
	 * Return HTML code to print an inline editable combobox, used in Perso Config saving process.
	 * 
	 * @param string $name Setting id (not setting name)
	 * @param string $value Setting value
	 * @param string $empty Default value for empty item
	 * @param string $selected Selected item
	 * @param string $extra
	 */
	static public function select_edit_control_inline($name, $values, $empty, $selected, $extra='') {
		if (!is_null($empty)) {
			// Push ''=>$empty onto the front of the array, maintaining keys
			$tmp=array(''=>$empty);
			foreach ($values as $key=>$value) {
				$tmp[$key]=$value;
			}
			$values=$tmp;
		}
		$values['selected']=$selected;
		return
			'<span class="editable" id="' . $name . '">' .
			(array_key_exists($selected, $values) ? htmlspecialchars($values[$selected]) : '').
			'</span>' .
			WT_JS_START .
			'jQuery("#' . $name . '").editable("' . WT_SERVER_NAME . WT_SCRIPT_PATH . 'module.php?mod=perso_config&mod_action=admin_update_setting", {type:"select", data:' . json_encode($values) . ', submit:"&nbsp;&nbsp;' . WT_I18N::translate('OK') . '&nbsp;&nbsp;", style:"inherit", placeholder: "'.WT_I18N::translate('click to edit').'", callback:function(value, settings) {jQuery(this).html(settings.data[value]);} })' .
			WT_JS_END;
	}
	
	/**
	 * Return HTML code to print an inline editable combobox with values yes and no, used in Perso Config saving process.
	 * 
	 * @param string $name Setting id (not setting name)
	 * @param bool $selected Selected value
	 * @param string $extra
	 */
	static public function edit_field_yes_no_inline($name, $selected=false, $extra='') {
		return WT_Perso_Functions_Edit::select_edit_control_inline(
			$name, array(true=>WT_I18N::translate('yes'), false=>WT_I18N::translate('no')), null, (int)$selected, $extra
		);
	}
	

}

?>