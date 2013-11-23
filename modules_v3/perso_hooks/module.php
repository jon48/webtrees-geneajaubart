<?php
/**
 * Classes for Module Perso Hooks
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

// Create tables, if not already present
try {
	WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.'perso_hooks/db_schema/', 'PHOOKS_SCHEMA_VERSION', 2);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class perso_hooks_WT_Module extends WT_Module implements WT_Module_Config {
	
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Hooks (Perso)');
	}
	
	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Implements hooks management.');
	}
	
	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':
			$this->updateHooks();
			$this->updateParams();
			$this->config();
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
	 * Compare possible and installed hooks and subscribe/remove if required.
	 */
	private function updateHooks(){
		
		if(WT_USER_IS_ADMIN){
			$ihooks = WT_Perso_Hook::getInstalledHooks();
			$phooks = WT_Perso_Hook::getPossibleHooks();
			
			// Insert hooks not existing yet in the DB
			if($phooks!=null){
				foreach($phooks as $phook => $priority){
					$array_hook = explode('#', $phook);
					if($ihooks==null || !array_key_exists($phook, $ihooks)){
						$chook = new WT_Perso_Hook($array_hook[1], $array_hook[2]);
						$chook->subscribe($array_hook[0]);
						$chook->setPriority($array_hook[0], $priority);
					}
				}
			}
			
			//Remove hooks not existing any more in the file system
			if($ihooks!=null){
				foreach($ihooks as $ihook => $status){
					$array_hook = explode('#', $ihook);
					if($phooks==null || !array_key_exists($ihook, $phooks)){
						$chook = new WT_Perso_Hook($array_hook[1], $array_hook[2]);
						$chook->remove($array_hook[0]);
					}
				}
			}
		}
		
	}
	
	/**
	 * Update the status and the priority based on user choices or changes in underlying modules.
	 * By default, disabled modules involves disabled hooks.
	 * 
	 */
	private function updateParams(){
		
		if(WT_USER_IS_ADMIN){
			$ihooks = WT_Perso_Hook::getInstalledHooks();
			
			$module_names=WT_DB::prepare("SELECT module_name FROM `##module` WHERE status='disabled'")->fetchOneColumn();		
			
			$isposted = WT_Filter::postBool('ispost') && WT_Filter::checkCsrf();
			
			if($ihooks!=null){
				foreach ($ihooks as $ihook => $params) {
					$array_hook = explode('#', $ihook);
					//Update status
					$new_status=WT_Filter::post("status-{$params['id']}");
					if(in_array($array_hook[0], $module_names)) $new_status=0;
					$previous_status=$params['status'];	
					if ($isposted && $new_status!==null) {
						$new_status= $new_status ? 'enabled' : 'disabled';
						if($new_status != $previous_status){
							$chook = new WT_Perso_Hook($array_hook[1], $array_hook[2]);
							switch($new_status){
								case 'enabled':
									$chook->enable($array_hook[0]);
									break;
								case 'disabled':
									$chook->disable($array_hook[0]);
									break;
								default:
									break;
							}
						}
					}
					//Update priority
					$new_priority=WT_Filter::post("moduleorder-{$params['id']}");
					$previous_priority=$params['priority'];	
					if ($isposted && $new_priority!==null) {
						if($new_priority != $previous_priority){
							$chook = new WT_Perso_Hook($array_hook[1], $array_hook[2]);
							$chook->setPriority($array_hook[0], $new_priority);
						}
					}
				}
			}
		}
	}
	
	
	/**
	 * Display an editable list of installed hooks in order for the admin to configure statuses and priorities.
	 */
	private function config() {
		
		$controller=new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->setPageTitle($this->getTitle())
			->pageHeader();
		
		require WT_ROOT.'includes/functions/functions_edit.php';
	
		$hooks= WT_Perso_Hook::getRawInstalledHooks();
		
		echo '<div align="center">',
			'<div id="tabs">';
		echo WT_I18N::translate('Help').help_link('admin_config', $this->getName());
		echo '<form method="post" action="#">',
					WT_Filter::getCsrf(),
					'<input type="hidden" name="ispost" value="true">',
					'<table id="installed_table" class="tablesorter" border="0" cellpadding="0" cellspacing="1">',
						'<thead>',
							'<tr>',
							'<th>',WT_I18N::translate('Enabled'),'</th>',
							'<th>ENABLED_SORT</th>',
							'<th>',WT_I18N::translate('Hook Function'),'</th>',
							'<th>',WT_I18N::translate('Hook Context'),'</th>',
							'<th>',WT_I18N::translate('Module Name'),'</th>',
							'<th>',WT_I18N::translate('Priority (1 is high)'),'</th>',
							'<th>PRIORITY_SORT</th>',
							'</tr>',
						'</thead>',
						'<tbody>';
							foreach ($hooks as $id => $hook) {
								echo '<tr><td>', two_state_checkbox('status-'.($hook->id), ($hook->status)=='enabled'), '</td>',
									'<td>',(($hook->status)=='enabled'),'</td>',
									'<td>',$hook->hook,'</td>',
									'<td>',$hook->context,'</td>',
									'<td>',$hook->module,'</td>',
									'<td><input type="text" class="center" size="2" value="',$hook->priority,'" name="moduleorder-',$hook->id,'" /></td>',
									'<td>',$hook->priority,'</td>',
									'</tr>';
							}
		echo 			'</tbody>',
					'</table>',
					'<input type="submit" value="',WT_I18N::translate('Save'),'" />',
				'</form>',
			'</div>',
		'</div>';
		
		$controller
		->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
		->addInlineJavascript('
				  	jQuery(document).ready(function() {
		
					  var oTable = jQuery("#installed_table").dataTable( {
							"sDom": \'<"H"prf>t<"F"li>\',
							'.WT_I18N::datatablesI18N().',
							"bJQueryUI": true,
							"bAutoWidth":false,
							"aaSorting": [[ 2, "asc" ], [ 3, "asc" ]],
							"iDisplayLength": 10,
							"sPaginationType": "full_numbers",
							"aoColumns": [
								/* 0 Enabled 		*/	{ "iDataSort": 1, "sClass": "center" },
								/* 1 Enabled sort	*/	{ "bVisible": false},
								/* 2 Hook function	*/	null,
								/* 3 Hook context	*/	null,
								/* 4 Module name	*/	null,						
								/* 5 Priority		*/	{ "iDataSort": 6, "sClass": "center" },
								/* 6 Priority sort	*/	{ "sType": "numeric", "bVisible": false},
							]
					  });
					});
				');	
	}

}


?>