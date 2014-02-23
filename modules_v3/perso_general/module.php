<?php
/**
 * Class for Perso General module.
 * This module is used for general and miscenalleous items of the Perso modules
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class perso_general_WT_Module extends WT_Module implements WT_Perso_Module_HookSubscriber, WT_Perso_Module_Configurable, WT_Perso_Module_HeaderExtender, WT_Perso_Module_FooterExtender, WT_Perso_Module_IndividualHeaderExtender{

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
			'h_config_tab_content' => 1,
			'h_print_header' => 20,
			'h_print_footer' => 20,
			'h_extend_indi_header_left' => 20
		);
	}

	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_name(){
		echo '<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('General'), '</span></a></li>';
	}

	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_content(){
		global $controller;
		
		echo '<div id="'.$this->getName().'"><table class="gm_edit_config"><tr><td><dl>';
		if(WT_USER_IS_ADMIN){
			echo '<dt>', WT_I18N::translate('Title prefixes'), help_link('config_title_prefix', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PG_TITLE_PREFIX-'.$this->getName(), get_module_setting($this->getName(), 'PG_TITLE_PREFIX', ''), $controller), '</dd>';
			echo '<dt>', WT_I18N::translate('Include additional HTML in header'), help_link('config_add_html_header', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_yes_no_inline('module_setting-PG_ADD_HTML_HEADER-'.$this->getName(), get_module_setting($this->getName(), 'PG_ADD_HTML_HEADER', false), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Hide additional header'), help_link('config_show_html_header', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_access_level_inline('module_setting-PG_SHOW_HTML_HEADER-'.$this->getName(), get_module_setting($this->getName(), 'PG_SHOW_HTML_HEADER', WT_PRIV_HIDE), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Additional HTML in header'), help_link('config_html_header', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_longfield_inline('module_setting-PG_HTML_HEADER-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PG_HTML_HEADER', ''), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Display French <em>CNIL</em> disclaimer'), help_link('config_display_CNIL', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_yes_no_inline('module_setting-PG_DISPLAY_CNIL-'.$this->getName(), get_module_setting($this->getName(), 'PG_DISPLAY_CNIL', false), $controller), '</dd>',
				'<dt>', WT_I18N::translate('<em>CNIL</em> reference'), help_link('config_cnil_ref', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('module_setting-PG_CNIL_REFERENCE-'.$this->getName(), get_module_setting($this->getName(), 'PG_CNIL_REFERENCE', ''), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Include additional HTML in footer'), help_link('config_add_html_footer', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_yes_no_inline('module_setting-PG_ADD_HTML_FOOTER-'.$this->getName(), get_module_setting($this->getName(), 'PG_ADD_HTML_FOOTER', false), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Hide additional footer'), help_link('config_show_html_footer', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_field_access_level_inline('module_setting-PG_SHOW_HTML_FOOTER-'.$this->getName(), get_module_setting($this->getName(), 'PG_SHOW_HTML_FOOTER', WT_PRIV_HIDE), $controller), '</dd>',
				'<dt>', WT_I18N::translate('Additional HTML in footer'), help_link('config_html_footer', $this->getName()), '</dt>',
				'<dd>', WT_Perso_Functions_Edit::edit_module_longfield_inline('module_setting-PG_HTML_FOOTER-'.$this->getName().'-validate', get_module_setting($this->getName(), 'PG_HTML_FOOTER', ''), $controller), '</dd>';
		}
		echo '</dl></td></tr></table></div>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function validate_config_settings($setting, $value){
		switch($setting){
			case 'PG_HTML_HEADER':
			case 'PG_HTML_FOOTER':
				$value =  htmlspecialchars_decode($value);
				break;
			default:
				break;
		}
		return $value;
	}
	
	// Implement WT_Perso_Module_HeaderExtender
	public function h_print_header(){
		global $WT_SESSION;
		
		if(get_module_setting($this->getName(), 'PG_ADD_HTML_HEADER', false)){
			if(WT_USER_ACCESS_LEVEL >= get_module_setting($this->getName(), 'PG_SHOW_HTML_HEADER', WT_PRIV_HIDE)  && !WT_Filter::getBool('noheader')){		
				echo htmlspecialchars_decode(get_module_setting($this->getName(), 'PG_HTML_HEADER', ''));
			}
		}
	}
	
	// Implement WT_Perso_Module_FooterExtender
	public function h_print_footer(){
		global $WT_SESSION;
		
		if(get_module_setting($this->getName(), 'PG_DISPLAY_CNIL', false)){
			echo '<br/>';
			echo '<div class="center">';
			$cnil_ref = get_module_setting($this->getName(), 'PG_CNIL_REFERENCE', '');
			if($cnil_ref != ''){
				echo WT_I18N::translate('This site has been notified to the French National Commission for Data protection (CNIL) and registered under number %s. ', $cnil_ref);
			}
			echo WT_I18N::translate('In accordance with the French Data protection Act (<em>Loi Informatique et Libertés</em>) of January 6th, 1978, you have the right to access, modify, rectify and delete personal information that pertains to you. To exercice this right, please contact %s, and provide your name, address and a proof of your identity.', user_contact_link(get_gedcom_setting(WT_GED_ID, 'WEBMASTER_USER_ID'))),
				'</div>';
		}
		if(get_module_setting($this->getName(), 'PG_ADD_HTML_FOOTER', false)){
			if(WT_USER_ACCESS_LEVEL >= get_module_setting($this->getName(), 'PG_SHOW_HTML_FOOTER', WT_PRIV_HIDE) && !WT_Filter::getBool('nofooter')){		
				echo htmlspecialchars_decode(get_module_setting($this->getName(), 'PG_HTML_FOOTER', ''));
			}
		}
	}

		//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_icons(WT_Controller_Individual $ctrlIndi) {
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_left(WT_Controller_Individual $ctrlIndi) {
		$res = '';
		if($ctrlIndi){
			$dindi = new WT_Perso_Individual($ctrlIndi->getSignificantIndividual());
			$tab=$dindi->getTitles();
			$countTitles = count($tab);
			if($countTitles>0){
				$res = '<dl><dt class="label">'.WT_I18N::translate('Titles').'</dt>';
				foreach($tab as $title=>$props){
					$res .= '<dd class="field">'.$title. ' ';
					$res .= WT_Perso_Functions_Print::getListFromArray($props);
					$res .= '</dd>';
				}
			$res .=  '</dl>';
			}
		}
		return array( 'indi-header-titles' , $res);
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_right(WT_Controller_Individual $ctrlIndi) {
	}
	
	
}

?>