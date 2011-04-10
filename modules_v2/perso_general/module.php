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

class perso_general_WT_Module extends WT_Module implements WT_Perso_Module_HookSubscriber, WT_Perso_Module_Configurable {

	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Perso General');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('General items about Perso modules.');
	}

	// Implement WT_Perso_Module_HookSubscriber
	public function getSubscribedHooks() {
		return array(
			'h_config_tab_name' => 1,
			'h_config_tab_content' => 1
		);
	}

	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_name(){
		echo '<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('General'), '</span></a></li>';
	}

	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_content(){
		echo '<div id="'.$this->getName().'"><table class="gm_edit_config"><tr><td><dl>';
		if(WT_USER_IS_ADMIN){
			echo '<dt>', WT_I18N::translate('Title prefixes'), help_link('config_title_prefix', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-TITLE_PREFIX-'.$this->getName(), get_module_setting($this->getName(), 'TITLE_PREFIX', '')), '</dd>';
		}
		echo '</dl></td></tr></table></div>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function validate_config_settings($setting, $value){
		return $value;
	}


}

?>