<?php
/**
 * Class for Perso Inferences module.
 * This module is used for infering missing values.
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class perso_inferences_WT_Module 
	extends WT_Module 
	implements WT_Perso_Module_HookSubscriber, WT_Perso_Module_Configurable, WT_Perso_Module_FactsExtender
{
	
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Perso Inferences');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Compute and infer data about Gedcom records.');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'admin_update_setting': //standard method for inline edit
				$this->editsetting();
				break;
			case 'engineaction':
			case 'ajaxadmingedsettings':
			case 'ajaxadminenginesettings':
				$this->$mod_action();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Perso_Module_HookSubscriber
	public function getSubscribedHooks() {
		return array(
				'h_config_tab_name' => 35,
				'h_config_tab_content' => 35,
				'h_add_facts'	=> 20
		);
	}

	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_name(){
		echo '<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('Inferences'), '</span></a></li>';
	}

	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_content(){
		global $controller;

		echo '<div id="'.$this->getName().'"><table class="gm_edit_config"><tr><td><dl>';
		
		if(WT_Perso_Inference_Helper::isModuleOperational()){
			$tab_id = 'ID-PI-'.floor(microtime()*1000000);
			
			$controller
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addExternalJavascript(WT_JQUERY_JEDITABLE_URL)
			->addExternalJavascript(WT_STATIC_URL.'js/jquery.datatables.fnReloadAjax.js')
			->addExternalJavascript(WT_STATIC_URL.'js/jquery.form-3.32.0.js')
			->addInlineJavascript('
					function updateInferenceGedSettings(){
						// Add gedcom-settings for inferences
						// Clear config
						inferenceengine = null;
						jQuery("#ddGedSettingsInfEng_'.$tab_id.'").html("&nbsp;");
					
						// Get gedcom inline settings
						jQuery.get(
							"module.php",
							{ "mod" : "'.$this->getName().'",  "mod_action": "ajaxadmingedsettings", "gedid": gedid },
							function(data){
								if(data){
									if(data.inferenceengine) {
										inferenceengine = data.inferenceengine;
									}
									if(data.inferenceenginehtml) {
										jQuery("#ddGedSettingsInfEng_'.$tab_id.'").html(data.inferenceenginehtml);
									}
							    }
								updateInferenceInfSettings();
							},
							"json"
						);
					}
					
					function updateInferenceInfSettings(infengine) {
						//Clear
						jQuery("#dInferenceSettings_'.$tab_id.'").html("&nbsp;");
						
						inferenceengine = (typeof infengine == "undefined" ? inferenceengine : infengine);
						if( inferenceengine ) {
							// Get inference engine inline settings
							jQuery.get(
								"module.php",
								{ "mod" : "'.$this->getName().'",  "mod_action": "ajaxadminenginesettings", "gedid": gedid, "engine": inferenceengine },
								function(data){ jQuery("#dInferenceSettings_'.$tab_id.'").html(data); },
								"html"
							);
						}
					}
			
					jQuery(document).ready(function() {
						gedid = "'.WT_GED_ID.'";
						inferenceengine = null;
			
						updateInferenceGedSettings();
			
						//Change behaviour on Gedcom dropdown list
						jQuery("#ddlGedcoms_'.$tab_id.'").change(function() {
							gedid = $("#ddlGedcoms_'.$tab_id.' option:selected").val();

							// Update place hierarchy
							updateInferenceGedSettings();
				
						});
					});
		
				');
			
			echo '<tr><td><div class="center">';
			echo WT_I18N::translate('Choose tree: ');
			echo '<select id="ddlGedcoms_'.$tab_id.'">';
			foreach (WT_Tree::getAll() as $tree) {
				echo '<option value='.$tree->tree_id;
				if($tree->tree_id == WT_GED_ID) echo ' selected=true';
				echo '>'.$tree->tree_name.'</option>';
			}
			echo '</select>';
			echo '</div></td></tr>';
			
			echo '<tr><th>'.WT_I18N::translate('Gedcom settings').'</th><tr>';
			echo '<tr><td><div id="dGedcomSettings_'.$tab_id.'">',
					'<dt>', WT_I18N::translate('Inference engine to use'), help_link('config_inference_engine', $this->getName()), '</dt>',
					'<dd id="ddGedSettingsInfEng_'.$tab_id.'">&nbsp;</dd>',					
				'</div></td></tr>';
			echo '<tr><th>'.WT_I18N::translate('Inference engine settings').'</th><tr>';
			echo '<tr><td><div id="dInferenceSettings_'.$tab_id.'">&nbsp;</div></td></tr>';
		}
		else{
			echo '<p class="center">'.WT_I18N::translate('The Perso Inferences module is required for this module to run. Please activate it.').'</p>';
		}
		
		echo '</dl></td></tr></table></div>';
	}

	// Implement WT_Perso_Module_Configurable
	public function validate_config_settings($setting, $value){
		if(is_null($setting)) return 'ERROR_VALIDATION';
		$value = trim($value);
		switch($setting){
			case 'PERSO_PI_INF_ENGINE':
				if(strlen($value) > 0 && !WT_Perso_Inference_Helper::getInferenceEngineInstance($value)) {
					$value = 'ERROR_VALIDATION';
				}
				break;
			default:
				break;
		}
		return $value;
	}
	
	// Implement WT_Perso_Module_FactsExtender	
	public function h_add_facts(WT_GedcomRecord $record, $factsarray) {
		$result = array();
		
		if($record instanceof WT_Individual) {
			if($record->canShow() && count($record->getFacts(WT_EVENTS_BIRT)) == 0) {
				$fact = new WT_Fact('1 BIRT Y', $record, md5($this->getName().'_birth'));
				$fact->setEditable(false);
				$result[] = $fact;
			}
			if($record->canShow() && $record->isDead() && count($record->getFacts(WT_EVENTS_DEAT)) == 0) {
				$fact = new WT_Fact('1 DEAT Y', $record, md5($this->getName().'_death'));
				$fact->setEditable(false);
				$result[] = $fact;
			}	
		}
		
		return $result;
	}	
	
	/**
	 * Manages pages actions on the required inference engine.
	 */
	private function engineaction() {
		$gedid = WT_Filter::getInteger('gedid');
		$enginename = WT_Filter::get('engine');
		$engineaction = WT_Filter::get('engineaction');
		
		if(WT_Perso_Inference_Helper::isModuleOperational()
		&& $gedid && array_key_exists($gedid, WT_Tree::getIdList())
		&& ($engine = WT_Perso_Inference_Helper::getInferenceEngineInstance($enginename, $gedid))
		&& $engineaction
		){
			$engine->engineAction($engineaction);
		} else {
			header('HTTP/1.0 404 Not Found');
		}
	}
	
	/**
	 * Returns gedcom-specific settings for the inferences module for display in administration.
	 * 
	 * Input parameters - GET :
	 * 	- gedid : gedcom ID to set up
	 * 
	 * JSON format
	 * {
	 * 		inferenceengine : string|null - Inference engine to use,
	 * 		inferenceenginehtml : string - Html code to display for inference engine dropdown,
	 * }
	 * 
	 */
	private function ajaxadmingedsettings() {		
		$gedid = WT_Filter::getInteger('gedid');

		$jsonArray = array(
				'inferenceengine' => '',
				'inferenceenginehtml' => '&nbsp'
		);
		
		$controller = new WT_Perso_Controller_Json();
		if(WT_Perso_Inference_Helper::isModuleOperational() 
			&& $gedid && array_key_exists($gedid, WT_Tree::getIdList())) {
			$controller->requireManagerLogin($gedid);
			$jsonArray['inferenceengine'] = get_gedcom_setting($gedid, 'PERSO_PI_INF_ENGINE');
			$jsonArray['inferenceenginehtml'] = WT_Perso_Functions_Edit::select_edit_control_inline(
					'gedcom_setting-PERSO_PI_INF_ENGINE-'.$gedid, 
					WT_Perso_Inference_Helper::getInferenceEngines(), 
					WT_I18N::translate('No inference engine'), 
					$jsonArray['inferenceengine'],
					null,
					'perso_config',
					'',
					'updateInferenceInfSettings(value);'
				);
		
		}
		$controller->pageHeader();
		echo Zend_Json::encode($jsonArray);
	}
	
	/**
	 * Returns HTML code for engine-specific settings for display in administration.
	 * 
	 * Input parameters - GET :
	 * 	- gedid : gedcom ID to set up
	 *  - engine : inference engine to set up
	 * 
	 */
	private function ajaxadminenginesettings() {
		$gedid = WT_Filter::getInteger('gedid');
		$enginename = WT_Filter::get('engine');

		$html = '<div class="center">'.WT_I18N::translate('This inference engine does not contain any configurable settings.').'</div>';
		
		$controller = new WT_Controller_Ajax();
		if(WT_Perso_Inference_Helper::isModuleOperational()
			&& $gedid && array_key_exists($gedid, WT_Tree::getIdList())
			&& $engine = WT_Perso_Inference_Helper::getInferenceEngineInstance($enginename, $gedid)
		){
			$html = $engine->getConfigDisplay();
		}
		
		$controller->pageHeader();
		echo $html;
	}

	/**
	 *  Save Geodispersion analysis settings.
	 *  
	 *  The id to be sent is under the format <strong><em>type_setting</em>-<em>inference_engine</em>-<em>setting</em>-<em>gedcom_id</em>-validate<strong>, with :
	 * 	- type_setting: <strong>gedcom_setting</strong>
	 *  - inference_engine : inference engine to be used
	 *  - setting: setting to be changed
	 *  - gedcom_id: Id of the related gedcom
	 *  - validate: use validation rule
	 */
	private function editsetting() {
		if(WT_Filter::checkCsrf() && WT_Perso_Inference_Helper::isModuleOperational()){
			$id=WT_Filter::post('id', '[a-zA-Z0-9_-]+');
			// $table 	- type of setting
			// $id1		- inference engine
			// $id2		- setting name
			// $id3		- gedcom ID
			// $id4		- validate settings
			list($table, $id1, $id2, $id3, $id4)=explode('-', $id.'----');
		
			// The replacement value.
			$value=WT_Filter::post('value');
			
			if($id1 && $id3 && $engine = WT_Perso_Inference_Helper::getInferenceEngineInstance($id1, $id3)) {
				// Validate the replacement value
				if($id4 == 'validate' && !is_null($id2)){
					$value = $engine->validateConfigSettings($id2, $value);					
				}
				
				if($value === 'ERROR_VALIDATION') WT_Perso_Functions_Edit::fail();
												
				switch($table){
					case 'gedcom_setting':
						// Verify if the user has enough rights to modify the setting
						if(!WT_USER_GEDCOM_ADMIN) WT_Perso_Functions_Edit::fail();
					
						// Verify if a setting name has been specified;
						if(is_null($id2)) WT_Perso_Functions_Edit::fail();
					
						// Authorised and valid - make update
						set_gedcom_setting($id3, $id2, $value);
						WT_Perso_Functions_Edit::ok($value);
						break;
					default:
						WT_Perso_Functions_Edit::fail();
				}
			}
			else{
				WT_Perso_Functions_Edit::fail();
			}
		}
		WT_Perso_Functions_Edit::fail();
	}

}

?>