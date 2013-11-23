<?php
/**
 * Class for Perso Certificates module.
 * This module is used for displaying and editing the certificate feature.
 *
 * @package webtrees
 * @subpackage Perso
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class perso_certificates_WT_Module extends WT_Module implements WT_Perso_Module_HookSubscriber, WT_Perso_Module_Configurable, WT_Perso_Module_FactSourceTextExtender, WT_Perso_Module_CustomSimpleTagManager {
	
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Perso Certificates');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Display and edition of certificates linked to sources.');
	}
	
	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'certificatelist':
			case 'certificatefirewall':
			case 'autocomplete':
				require WT_ROOT.WT_MODULES_DIR.$this->getName().'/'.$mod_action.'.php';
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}
	
	// Implement WT_Perso_Module_HookSubscriber
	public function getSubscribedHooks() {
		return array(
			'h_config_tab_name' => 20,
			'h_config_tab_content' => 20,
			'h_fs_prepend' => 50,
			'h_fs_append' => 50,
			'h_get_simpletag_display#_ACT' => 50,
			'h_get_simpletag_editor#_ACT'	=> 50,
			'h_add_simple_tag#SOUR'	=> 50,
			'h_get_expected_tags' => 50,
			'h_get_help_text_tag#_ACT'	=> 50,
			'h_has_help_text_tag#_ACT'	=> 50
		);
	}
	
	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_name(){
		echo '<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('Certificates'), '</span></a></li>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_content(){
		global $controller;
		
		echo '<div id="'.$this->getName().'"><table class="gm_edit_config"><tr><td><dl>';
		if(WT_USER_IS_ADMIN){
			echo '<dt>', WT_I18N::translate('Certificates directory'), help_link('config_cert_rootdir', $this->getName()), '</dt>',
				'<dd>', WT_DATA_DIR , WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_CERT_ROOTDIR-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_CERT_ROOTDIR', 'certificates/'), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Show certificates'), help_link('config_show_cert', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_access_level_inline('module_setting-PC_SHOW_CERT-'.$this->getName(), get_module_setting($this->getName(), 'PC_SHOW_CERT', WT_PRIV_HIDE), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Show non-watermarked certificates'), help_link('config_show_no_watermark', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_access_level_inline('module_setting-PC_SHOW_NO_WATERMARK-'.$this->getName(), get_module_setting($this->getName(), 'PC_SHOW_NO_WATERMARK', WT_PRIV_HIDE), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Default watermark'), help_link('config_wm_default', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_WM_DEFAULT-'.$this->getName(), get_module_setting($this->getName(), 'PC_WM_DEFAULT', WT_I18N::translate('This image is protected under copyright law.')), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Watermark font color'), help_link('config_wm_font_color', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_WM_FONT_COLOR-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_WM_FONT_COLOR', '77,109,243'), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Watermark minimum font size'), help_link('config_wm_font_minsize', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_WM_FONT_MINSIZE-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_WM_FONT_MINSIZE', 8), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Watermark maximum font size'), help_link('config_wm_font_maxsize', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_WM_FONT_MAXSIZE-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_WM_FONT_MAXSIZE', 18), $controller), '</dd>';
		}
		echo '</dl></td></tr></table></div>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function validate_config_settings($setting, $value){
		switch($setting){
			case 'PC_CERT_ROOTDIR':
				$errors_cert_rootdir = false;
				$value = trim(str_replace('\\','/',$value)); // silently convert backslashes to forward slashes
				$value = str_replace('"','',$value); // silently remove quote marks
				$value = str_replace("'",'',$value); // silently remove quote marks
				$value = str_replace("//",'/',$value); // silently remove duplicate slashes
				if (substr ($value, -1) != '/') $value = $value . '/'; // silently add trailing slash
				if (substr($value, 0, 1)=='/') { $errors_cert_rootdir = true; } // don't allow absolute path
				if (preg_match("/.*[a-zA-Z]{1}:.*/", $value)>0) { $errors_cert_rootdir = true; } // don't allow drive letters
				if ($errors_cert_rootdir) $value = 'ERROR_VALIDATION';
				break;
			case 'PC_WM_FONT_COLOR':
				$error_font_color = false;
				$colors = explode(',', $value);
				if(count($colors)==3){
					foreach($colors as $component){
						if(!is_numeric(trim($component))) $error_font_color = true;
						if(!(trim($component) < 256)) $error_font_color = true;
					}
				}
				else{
					$error_font_color = true;
				}
				if ($error_font_color) $value = 'ERROR_VALIDATION';
				break;
			case 'PC_WM_FONT_MINSIZE':
			case 'PC_WM_FONT_MAXSIZE':
				if (!is_numeric(trim($value))) $value = 'ERROR_VALIDATION';
				break;
			default:
				break;
		}
		return $value;
	}
	
	//Implement WT_Perso_Module_FactSourceTextExtender
	public function h_fs_prepend($srec){		
		$html='';		
		$sid=null;
		
		if(get_module_setting($this->getName(), 'PC_SHOW_CERT', WT_PRIV_HIDE) >= WT_USER_ACCESS_LEVEL){	
			if (strlen($srec)==0) return $html;
			
			$certificate = null;
			$subrecords = explode("\n", $srec);
			$levelSOUR = substr($subrecords[0], 0, 1);
			if (preg_match('~^'.$levelSOUR.' SOUR @('.WT_REGEX_XREF.')@$~', $subrecords[0], $match)) {
				$sid=$match[1];
			};
			for ($i=0; $i<count($subrecords); $i++) {
				$subrecords[$i] = trim($subrecords[$i]);
				$level = substr($subrecords[$i], 0, 1);
				$tag = substr($subrecords[$i], 2, 4);
				$text = substr($subrecords[$i], 7);
				if($tag == '_ACT') $certificate= new WT_Perso_Certificate($text);
			}
			
			if($certificate && $certificate->canShow()) 
				$html = $this->getDisplay_ACT($certificate, $sid);
			
		}		
		return $html;
	}
	
	//Implement WT_Perso_Module_FactSourceTextExtender
	public function h_fs_append($srec){
	}
	
	//Implement WT_Perso_Module_CustomSimpleTagManager
	public function getCustomTags(){
		return array('_ACT');
	}

	//Implement WT_Perso_Module_CustomSimpleTagManager
	public function h_get_simpletag_display($tag, $value, $context = null, $contextid = null){
		$html = '';
		switch($tag){
			case '_ACT':
				if($context == 'SOUR') $html = $this->getDisplay_ACT($value, $contextid);
				break;
		}
		return $html;
	}
	
	//Implement WT_Perso_Module_CustomSimpleTagManager
	public function h_get_simpletag_editor($tag, $value = null, $element_id = '', $element_name = '', $context = null, $contextid = null){		
		global $controller;
		
		$html = '';
		
		switch($tag){
			case '_ACT':
				$element_id = $tag.floor(microtime()*1000000); //replace $element_id so that it is unique
				$controller
					->addExternalJavascript('js/autocomplete.js')
					->addExternalJavascript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/autocomplete.js')
					->addExternalJavascript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/updatecertificatevalues.js');
				$certificate = null;
				if($value){
					$certificate = new WT_Perso_Certificate($value);
				}
				$tabCities = WT_Perso_Functions_Certificates::getCitiesList();
				$html .= '<select id="certifCity'.$element_id.'" class="_CITY">';
				foreach ($tabCities as $cities){
					$selectedCity='';
					if($certificate && $cities== $certificate->getCity()) $selectedCity='selected="true"';
					$html .= '<option value="'.$cities.'" '.$selectedCity.' />'.$cities.'</option>';
				}
				$html .= '</select>';
				$html .= '<input id="certifFile'.$element_id.'" autocomplete="off" class="_ACT" value="'.
					($certificate ? basename($certificate->file) : '').
					'" size="35" />';
				$html .= '<input type="hidden" id="'.$element_id.'" name = "'.$element_name.'" value="'.$value.'" size="35"/>';
		}
		
		return $html;
	}
	
	//Implement WT_Perso_Module_CustomSimpleTagManager
	public function h_add_simple_tag($context, $level){
		switch($context){
			case 'SOUR':
				add_simple_tag($level.' _ACT');
				break;
		}
	}
	
	//Implement WT_Perso_Module_CustomSimpleTagManager
	public function h_get_expected_tags(){
		return array('SOUR' => '_ACT');
	}
	
	//Implement WT_Perso_Module_CustomSimpleTagManager
	public function h_has_help_text_tag($tag){
		switch($tag){
			case '_ACT':
				return true;
				break;
		}
		return false;
	}
	
	// Implement WT_Perso_Module_CustomSimpleTagManager
	public function h_get_help_text_tag($tag) {
		switch($tag){
			case '_ACT':
				return array(
					WT_I18N::translate('Certificate'),
					'<p>'.WT_I18N::translate('Path to a certificate linked to a source reference.').'</p>');
			default:
				return null;
		}
	}
	
	/**
	 * Return the HTML code for custom simple tag _ACT
	 * 
	 * @param string $certificatePath Path of the Certificate (as per the GEDCOM)
	 * @param string $sid ID of the linked source, if it exists
	 */
	private function getDisplay_ACT(WT_Perso_Certificate $certificate, $sid = null){
		global $controller;
				
		$html = '';
		if($certificate){
			$certificate->setSource($sid);
			$html = $certificate->displayImage('icon');
		}
		return $html;
	}	
	
}




?>