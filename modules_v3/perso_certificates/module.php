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
			'h_get_help_text_tag#_ACT'	=> 50
		);
	}
	
	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_name(){
		echo '<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('Certificates'), '</span></a></li>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_content(){
		echo '<div id="'.$this->getName().'"><table class="gm_edit_config"><tr><td><dl>';
		if(WT_USER_IS_ADMIN){
			echo '<dt>', WT_I18N::translate('Certificates directory'), help_link('config_cert_rootdir', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_CERT_ROOTDIR-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_CERT_ROOTDIR', 'certificates/')), '</dd>',
				'<dt>', WT_I18N::translate('Show certificates'), help_link('config_show_cert', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_access_level_inline('module_setting-PC_SHOW_CERT-'.$this->getName(), get_module_setting($this->getName(), 'PC_SHOW_CERT', WT_PRIV_HIDE)), '</dd>',
				'<dt>', WT_I18N::translate('Certificates firewall root directory'), help_link('config_cert_fw_rootdir', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_CERT_FW_ROOTDIR-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_CERT_FW_ROOTDIR', 'data/')), '</dd>',
				'<dt>', WT_I18N::translate('Show non-watermarked certificates'), help_link('config_show_no_watermark', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_access_level_inline('module_setting-PC_SHOW_NO_WATERMARK-'.$this->getName(), get_module_setting($this->getName(), 'PC_SHOW_NO_WATERMARK', WT_PRIV_HIDE)), '</dd>',
				'<dt>', WT_I18N::translate('Default watermark'), help_link('config_wm_default', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_WM_DEFAULT-'.$this->getName(), get_module_setting($this->getName(), 'PC_WM_DEFAULT', WT_I18N::translate('This image is protected under copyright law.'))), '</dd>',
				'<dt>', WT_I18N::translate('Watermark font color'), help_link('config_wm_font_color', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_WM_FONT_COLOR-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_WM_FONT_COLOR', '77,109,243')), '</dd>',
				'<dt>', WT_I18N::translate('Watermark minimum font size'), help_link('config_wm_font_minsize', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_WM_FONT_MINSIZE-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_WM_FONT_MINSIZE', 8)), '</dd>',
				'<dt>', WT_I18N::translate('Watermark maximum font size'), help_link('config_wm_font_maxsize', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PC_WM_FONT_MAXSIZE-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PC_WM_FONT_MAXSIZE', 18)), '</dd>';
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
				if (preg_match('/([\.]?[\.][\/])+/', $value)>0) { $errors_cert_rootdir = true; } // don't allow ./ or ../ 
				$errors_cert_rootdir = $errors_cert_rootdir || $this->fix_certif_htaccess($value);
				if ($errors_cert_rootdir) $value = 'ERROR_VALIDATION';
				break;
			case 'PC_CERT_FW_ROOTDIR':
				$errors_cert_fw_rootdir = false;
				$cert_rootdir = get_module_setting($this->getName(), 'PC_CERT_ROOTDIR', 'certificates/');
				$value = trim(str_replace('\\','/',$value)); // silently convert backslashes to forward slashes
				if (substr ($value, -1) != "/") $value = $value . "/"; // silently add trailing slash
				if (!is_dir($value)) $errors_cert_fw_rootdir = true;
				if (!$errors_cert_fw_rootdir) {
					// Since the certificates firewall is always enabled, need to verify that the protected certificates dir exists
					if (!is_dir($value.$cert_rootdir)) {
						@mkdir($value.$cert_rootdir, WT_PERM_EXE);
						if (!is_dir($value.$cert_rootdir)) $errors_cert_fw_rootdir = true;
					}
				}	
				if (!$errors_cert_fw_rootdir) {
					// copy the .htaccess file from INDEX_DIRECTORY to the certificates firewall root directory in case it is still in a web-accessible area
					if ((file_exists(WT_DATA_DIR.".htaccess")) && (is_dir($value.$cert_rootdir)) && (!file_exists($value.$cert_rootdir.".htaccess")) ) {
						@copy(WT_DATA_DIR.".htaccess", $value.$cert_rootdir.".htaccess");
						if (!file_exists($value.$cert_rootdir.".htaccess"))	$errors_cert_fw_rootdir = true;
					}
				}
				if ($errors_cert_fw_rootdir) $value = 'ERROR_VALIDATION';
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
			
			$certifFile='';
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
				if($tag == '_ACT') $certifFile=$text;
			}
			
			if($certifFile != '') $html = $this->getDisplay_ACT($certifFile, $sid);
			
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
					->addExternalJavaScript('js/autocomplete.js')
					->addExternalJavaScript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/autocomplete.js')
					->addExternalJavaScript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/updatecertificatevalues.js');
				$city='';
				$certif='';
				if($value){
					$tabExplode = explode('/', $value, 2);
					if(count($tabExplode)==2){
						$city=$tabExplode[0];
						$certif=$tabExplode[1];
					}
				}
				$tabCities = WT_Perso_Functions_Certificates::getCitiesList();
				$html .= '<select id="certifCity'.$element_id.'" class="_CITY">';
				foreach ($tabCities as $cities){
					$selectedCity='';
					if($cities==$city) $selectedCity='selected="true"';
					$html .= '<option value="'.$cities.'" '.$selectedCity.' />'.$cities.'</option>';
				}
				$html .= '</select>';
				$html .= '<input id="certifFile'.$element_id.'" autocomplete="off" class="_ACT" value="'.$certif.'" size="35" />';		
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
	
	// Implement WT_Perso_Module_HookSubscriber
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
	private function getDisplay_ACT($certificatePath, $sid = null){		
		$certdetails = explode('/',$certificatePath,2);
		$html = '';
		if(count($certdetails)==2){
			$pathCertif= WT_MODULES_DIR.$this->getName().'/'.get_module_setting($this->getName(), 'PC_CERT_ROOTDIR', 'certificates/').$certificatePath;
			if($sid) $pathCertif .= '?sid='.$sid;
			$requestedCity = $certdetails[0];
			$requestedCertif = $certdetails[1];
			$html= '<a href="'.$pathCertif.'" title="'.$requestedCertif.'"'.
					' rel="clearbox[certificate]"'.
					' rev="PC::'.$requestedCity.'::'.$requestedCertif.'::">'.
					'<i class="icon-perso-certificate margin-h-2"></i></a>';
		}
		return $html;
	}	
	
	/**
	 * Creates a .htaccess file within the Certificates directory in order to redirect public URL requests to the certificates firewall
	 * 
	 * @param string $cert_rootdir Certificates public directory
	 */
	private function fix_certif_htaccess($cert_rootdir) {
		$whichFile = WT_MODULES_DIR.$this->getName().'/'.$cert_rootdir.".htaccess";
		$httext = "";
		if (file_exists($whichFile)) {
			$httext = implode('', file($whichFile));
			if ($httext && strpos('RewriteRule .* '.WT_SCRIPT_PATH.'module.php?mod='.$this->getName().'&mod_action=certificatefirewall [L]', $httext) !== false) {
				return; // don't mess with the file if it already refers to the certificatesfirewall
			} else {
				// remove all WT certificates firewall sections from the .htaccess
				$httext = preg_replace('/\n?^[#]*\s*BEGIN WT CERTIFICATES FIREWALL SECTION(.*\n){10}[#]*\s*END WT MEDIA FIREWALL SECTION\s*[#]*\n?/m', "", $httext);
				// comment out any existing lines that set ErrorDocument 404
				$httext = preg_replace('/^(ErrorDocument\s*404(.*))\n?/', "#$1\n", $httext);
				$httext = preg_replace('/[^#](ErrorDocument\s*404(.*))\n?/', "\n#$1\n", $httext);
			}
		}
		// add new WT certificates firewall section to the end of the file
		$httext .= "\n##### BEGIN WT CERTIFICATES FIREWALL SECTION #######";
		$httext .= "\n################## DO NOT MODIFY ###################";
		$httext .= "\n## THERE MUST BE EXACTLY 11 LINES IN THIS SECTION ##";
		$httext .= "\n<IfModule mod_rewrite.c>";
		$httext .= "\n\tRewriteEngine On";
		$httext .= "\n\tRewriteCond %{REQUEST_FILENAME} !-f";
		$httext .= "\n\tRewriteCond %{REQUEST_FILENAME} !-d";
		$httext .= "\n\tRewriteRule .* ".WT_SCRIPT_PATH."module.php?mod=".$this->getName()."&mod_action=certificatefirewall"." [L]";
		$httext .= "\n</IfModule>";
		$httext .= "\nErrorDocument\t404\t".WT_SCRIPT_PATH."module.php?mod=".$this->getName()."&mod_action=certificatefirewall";
		$httext .= "\n####### END WT CERTIFICATES FIREWALL SECTION #######";
	
		$fp = @fopen($whichFile, "wb");
		if (!$fp) {
			return true;
		} else {
			fwrite($fp, $httext);
			fclose($fp);
			@chmod($whichFile, WT_PERM_FILE); // Make sure apache can read this file
		}
		return false;
	}
	
}




?>