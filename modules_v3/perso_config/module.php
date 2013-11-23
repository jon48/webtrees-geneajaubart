<?php
/**
 * Class for Module Perso Config
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

class perso_config_WT_Module extends WT_Module implements WT_Module_Config {

	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Central Perso configuration');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Allows central configuration for Perso modules configuration.');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'admin_config':
				$this->config();
				break;
			case 'admin_update_setting':
				$this->editsetting();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&mod_action=admin_config';
	}

	/**
	 * Display the configuration items for each Perso modules, implementing the hooks h_config_tab_name and h_config_tab_content
	 * 
	 */
	private function config(){
		global $controller;
		
		$controller=new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->addExternalJavascript(WT_JQUERY_JEDITABLE_URL)
			->addInlineJavascript('jQuery("#tabs").tabs();')
			->setPageTitle($this->getTitle())
			->pageHeader();
		
		echo '<div id="site-config">',
				'<div id="tabs">',
					'<ul>';
		$h_config_tab_name = new WT_Perso_Hook('h_config_tab_name');
		$h_config_tab_name->execute();
		echo				'</ul>';
		$h_config_tab_content = new WT_Perso_Hook('h_config_tab_content');
		$h_config_tab_content->execute($this->getName());
		echo	'</div>',
			'</div>';
	}
	
	/**
	 * Save Perso module settings.
	 * The id to be sent is under the format <strong><em>type_setting</em>-<em>setting</em>-<em>module_name</em>-validate<strong>, with :
	 * 		- typ_setting: <strong>module_setting</strong> or <strong>gedcom_setting</strong>
	 * 		- setting: setting to be change
	 *  	- module_name: if present, name of the calling module. Is required for gedcom setting if validation necessary
	 *  	- validate: if present, will validate the entry value, according to rule defined within the module.
	 */
	private function editsetting(){
		
		// Do we have a valid CSRF token?
		if (!WT_Filter::checkCsrf()) {
			WT_Perso_Functions_Edit::fail();
		}
		
		$id=WT_Filter::post('id', '[a-zA-Z0-9_-]+');
		list($table, $id1, $id2, $id3)=explode('-', $id.'---');
			
		// The replacement value.
		$value=WT_Filter::post('value');

		// Validate the replacement value
		if($id3 == 'validate' && !is_null($id2)){
			require_once WT_ROOT.WT_MODULES_DIR.$id2.'/module.php';
			$class=$id2.'_WT_Module';
			$config_class=new $class();
			$value = $config_class->validate_config_settings($id1, $value);
		}
			
		if($value === 'ERROR_VALIDATION') WT_Perso_Functions_Edit::fail();
			
		switch($table){
		case 'module_setting':
			// Verify if the user has enough rights to modify the setting
			if(!WT_USER_IS_ADMIN) WT_Perso_Functions_Edit::fail();
			
			// Verify if a module has been specified;
			if(is_null($id2)) WT_Perso_Functions_Edit::fail();
					
			// Authorised and valid - make update
			set_module_setting($id2, $id1, $value);
			WT_Perso_Functions_Edit::ok($value);
		break;
		case 'gedcom_setting':
			// Verify if the user has enough rights to modify the setting
			if(!WT_USER_GEDCOM_ADMIN) WT_Perso_Functions_Edit::fail();
			
			// Verify if a gedcom ID has been specified;
			if(is_null($id2)) WT_Perso_Functions_Edit::fail();
			
			// Authorised and valid - make update
			set_gedcom_setting($id2, $id1, $value);
			WT_Perso_Functions_Edit::ok($value);
			break;
		default:
			WT_Perso_Functions_Edit::fail();				
		}
		WT_Perso_Functions_Edit::fail();	
	}
	

}


?>