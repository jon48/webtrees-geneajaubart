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
			
			if($ihooks!=null){
				foreach ($ihooks as $ihook => $params) {
					$array_hook = explode('#', $ihook);
					//Update status
					$new_status=safe_POST("status-{$params['id']}");
					if(in_array($array_hook[0], $module_names)) $new_status=0;
					$previous_status=$params['status'];	
					if ($new_status!==null) {
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
					$new_priority=safe_POST("moduleorder-{$params['id']}");
					$previous_priority=$params['priority'];	
					if ($new_priority!==null) {
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

		if(WT_USER_IS_ADMIN) {
		
			require WT_ROOT.'includes/functions/functions_edit.php';
			
			print_header($this->getTitle());
	
			$hooks= WT_Perso_Hook::getRawInstalledHooks();
	
			?>
			<script type="text/javascript">
			//<![CDATA[
			
			  function reindexMods(id) {
					jQuery('#'+id+' input').each(
						function (index, value) {
							value.value = index+1;
						});
			  }
			
			  jQuery(document).ready(function() {
	
				  var oTable = jQuery('#installed_table').dataTable( {
						"oLanguage": {
						"sLengthMenu": '<?php echo /* I18N: %s is a placeholder for listbox containing numeric options */ WT_I18N::translate('Display %s', '<select><option value="10">10</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="50">50</option><option value="-1">'.WT_I18N::translate('All').'</option></select>'); ?>',
						"sZeroRecords": '<?php echo WT_I18N::translate('No records to display');?>',
						"sInfo": '<?php echo /* I18N: %s are placeholders for numbers */ WT_I18N::translate('Showing %1$s to %2$s of %3$s', '_START_', '_END_', '_TOTAL_'); ?>',
						"sInfoEmpty": '<?php echo /* I18N: %s are placeholders for numbers */ WT_I18N::translate('Showing %1$s to %2$s of %3$s', '0', '0', '0'); ?>',
						"sInfoFiltered": '<?php echo /* I18N: %s is a placeholder for a number */ WT_I18N::translate('(filtered from %s total entries)', '_MAX_'); ?>',
						"sSearch": '<?php echo WT_I18N::translate('Search');?>',
						"oPaginate": {
							"sFirst": '<?php echo /* I18N: button label, first page    */ WT_I18N::translate('first'); ?>',
							"sLast": '<?php echo /* I18N: button label, last page     */ WT_I18N::translate('last'); ?>',
							"sNext": '<?php echo /* I18N: button label, next page     */ WT_I18N::translate('next'); ?>',
							"sPrevious": '<?php echo /* I18N: button label, previous page */ WT_I18N::translate('previous'); ?>'
						}
						},
						"sDom": '<"H"prf>t<"F"li>',
						"bJQueryUI": true,
						"bAutoWidth":false,
						"aaSorting": [[ 1, "asc" ], [ 2, "asc" ]],
						"iDisplayLength": 10,
						"sPaginationType": "full_numbers",
						"aoColumnDefs": [
							{ "bSortable": false, "aTargets": [ 0, 4 ] }
						]
				  });
	
			});
			//]]>
			</script>
			
			<?php
			
			echo '<div align="center">',
				'<div id="tabs">';
			echo WT_I18N::translate('Help').help_link('admin_config', $this->getName());
			echo '<form method="post" action="#">',
						'<table id="installed_table" class="tablesorter" border="0" cellpadding="0" cellspacing="1">',
							'<thead>',
								'<tr>',
								'<th>',WT_I18N::translate('Enabled'),'</th>',
								'<th>',WT_I18N::translate('Hook Function'),'</th>',
								'<th>',WT_I18N::translate('Hook Context'),'</th>',
								'<th>',WT_I18N::translate('Module Name'),'</th>',
								'<th>',WT_I18N::translate('Priority (1 is high)'),'</th>',
								'</tr>',
							'</thead>',
							'<tbody>';
								foreach ($hooks as $id => $hook) {
									echo '<tr><td>', two_state_checkbox('status-'.($hook->id), ($hook->status)=='enabled'), '</td>',
										'<td>',$hook->hook,'</td>',
										'<td>',$hook->context,'</td>',
										'<td>',$hook->module,'</td>',
										'<td><input type="text" class="center" size="2" value="',$hook->priority,'" name="moduleorder-',$hook->id,'" /></td>',
										'</tr>';
								}
			echo 			'</tbody>',
						'</table>',
						'<input type="submit" value="',WT_I18N::translate('Save'),'" />',
					'</form>',
				'</div>',
			'</div>';
	
			print_footer();
		
		} else {
			header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH);
			exit;
		}
	}

}


?>