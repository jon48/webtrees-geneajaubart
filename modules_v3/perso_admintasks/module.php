<?php
/**
 * Class for Perso Admin Tasks module.
 * This module is used for managing and running nearly-scheduled tasks.
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
	WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.'perso_admintasks/db_schema/', 'PADMINTASKS_SCHEMA_VERSION', 1);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class perso_admintasks_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Block {
	
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Administration Tasks (Perso)');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Manage and run nearly-scheduled administration tasks.');
	}
	
	// Extend class WT_Module
	public function modAction($mod_action) {
		define('WT_P_ADMINTASKS_DIR', WT_MODULES_DIR.$this->getName().'/tasks/');
		
		switch($mod_action) {
			case 'admin_config':
				$this->config();
				break;
			case 'admin_gentoken':
				$this->generatetoken();
				break;
			case 'admin_update_setting':
				$this->editsetting();
				break;
			case 'ajaxtaskslist':
				$this->ajaxtaskslist();
				break;
			case 'trigger':
				$this->trigger();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}
	
	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&mod_action=admin_config';
	}
	
	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $controller;
		
		$controller->addInlineJavascript('
			$(document).ready(function(){
				$.ajax({
					url: "module.php",
					data : {
						mod: "'.$this->getName().'",
						mod_action: "trigger",
					},
				});	
			});	
		');
		return '';
	}
	
	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}
	
	// Implement class WT_Module_Block
	public function isUserBlock() {
		return false;
	}
	
	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return true;
	}
	
	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
	}
	
	
	/**
	 * Display the configuration items for the Admin Tasks module
	 * 
	 */
	private function config(){
		global $controller;
		
		$controller=new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addExternalJavascript(WT_JQUERY_JEDITABLE_URL)
			->addExternalJavascript(WT_STATIC_URL.'js/jquery.datatables.fnReloadAjax.js')
			->addInlineJavascript('jQuery("#tabs").tabs();')
			->setPageTitle($this->getTitle())
			->pageHeader();

		if(WT_Perso_Admin_Task::isModuleOperational()){
		$token = get_module_setting($this->getName(), 'PAT_FORCE_EXEC_TOKEN');
		if(is_null($token)) {
			$token = WT_Perso_Functions::generateRandomToken();
			set_module_setting($this->getName(), 'PAT_FORCE_EXEC_TOKEN', $token);
		}
		
		$controller->addInlineJavascript('
			function generateForceToken() {
				jQuery("#bt_genforcetoken").attr("disabled", "disabled");
				jQuery("#bt_tokentext").empty().html("<i class=\"icon-loading-small\"></i>");
				jQuery("#token_url").load(
					"module.php?mod='.$this->getName().'&mod_action=admin_gentoken",
					function() {
						jQuery("#bt_genforcetoken").removeAttr("disabled");
						jQuery("#bt_tokentext").empty().html("'.WT_I18N::translate('Regenerate token').'");
					}
				);
			}
				
			function runAdminTask(taskname){
				jQuery("#bt_runtask_" + taskname).attr("disabled", "disabled");
				jQuery("#bt_runtasktext_" + taskname).empty().html("<i class=\"icon-loading-small\"></i>");
				jQuery("#bt_runtasktext_" + taskname).load(
					"module.php?mod='.$this->getName().'&mod_action=trigger&force='.$token.'&task=" + taskname,
					function() {
						jQuery("#bt_runtasktext_" + taskname).empty().html("'.WT_I18N::translate('Done').'");
						oTable.fnReloadAjax();
					}
				);
			}
		');
		
		$controller->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			var oTable = jQuery("#admintasks_list").dataTable({
				"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
				"sAjaxSource": "'.WT_SERVER_NAME.WT_SCRIPT_PATH.WT_SCRIPT_NAME.'?mod='.$this->getName().'&mod_action=ajaxtaskslist",
				"bServerSide":true,
				'.WT_I18N::datatablesI18N().',
				"bJQueryUI": true,
				"bAutoWidth":false,
				"bProcessing": true,
				"sPaginationType": "full_numbers",
				"aoColumns": [
					/* 0 Enabled        */ 		{"sClass": "center"},
					/* 1 Task name */			{"sClass": "center"},
					/* 2 Last run */			{"sClass": "center"},
					/* 3 Last result */			{"sClass": "center"},
					/* 4 Frequency */			{"sType": "numeric", "sClass": "center"},
					/* 5 Number occurrence */	{"sClass": "center"},
					/* 6 IsRunning */			{"sClass": "center"},
					/* 7 ForceExecution */		{"sClass": "center"},
				],
				"fnDrawCallback": function() {
					// Our JSON responses include Javascript as well as HTML.  This does not get executed automatically…
					jQuery("#admintasks_list script").each(function() {
						eval(this.text);
					});
				}
			});
		');
		
				
		WT_Perso_Admin_Task::getInstalledTasks();
		
		$tasks = WT_Perso_Admin_Task::getActiveTasks();
		
		echo '<div id="site-config">',
			'<div id="tabs">',
				'<ul>',
					'<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('General'), '</span></a></li>';
		foreach($tasks as $task_name => $task){
			if($task instanceof WT_Perso_Admin_ConfigurableTask && $task->isEnabled()){
				echo '<li><a href="#'.$task->getName().'"><span>', $task->getPrettyName(), '</span></a></li>';
			}
		}
		echo 	'</ul>',
				'<div id="'.$this->getName().'">',
					'<div class="center">',
						'<h2>'.$this->getTitle().'</h2>',
						'<p>'.WT_I18N::translate('The administration tasks are meant to be run at a regular interval - or as regularly as possible.').'</p>',
						'<p>'.WT_I18N::translate('It is sometimes necessary to force the execution of a task.').'<br />'.WT_I18N::translate('In order to do so, use the following URL, with the optional parameter <em>%s</em> if you only want to force the execution of one task: ', 'task').'</p>',
						'<p><code>'.WT_SERVER_NAME.WT_SCRIPT_PATH.'module.php?mod='.$this->getName().'&mod_action=trigger&force=<span id="token_url">'.$token.'</span>[&task='.WT_I18N::translate('task_name').']</code></p>',
						'<p><button id="bt_genforcetoken" class="progressbutton" onClick="generateForceToken();"><div id="bt_tokentext">'.WT_I18N::translate('Regenerate token').'</div></button></p>',
					'</div>',
					'<table id="admintasks_list" style="width:100%;">',
						'<thead><tr>',
							'<th>',WT_I18N::translate('Enabled'),'</th>',	
							'<th>',WT_I18N::translate('Task name'),'</th>',
							'<th>',WT_I18N::translate('Last success'),'</th>',
							'<th>',WT_I18N::translate('Last result'),'</th>',
							'<th>',WT_I18N::translate('Frequency (in min.)'),'</th>',
							'<th>',WT_I18N::translate('Remaining occurrences'),'</th>',
							'<th>',WT_I18N::translate('Is running?'),'</th>',
							'<th>',WT_I18N::translate('Run task'),'</th>',
						'</tr></thead>',						
					'</table>',
				'</div>';
		foreach($tasks as $task_name => $task){
			if($task instanceof WT_Perso_Admin_ConfigurableTask && $task->isEnabled()){
				echo '<div id="'.$task->getName().'">'.
					'<div class="center"><h2>'.WT_I18N::translate('%s Settings', $task->getPrettyName()).'</h2></div>'.
					$task->getConfigTabContent().
				'</div>';
			}
		}
		echo	'</div>',  // closing tabs
		'</div>'; //closing site_config
		}
		else{
			echo '<p class="warning">'.WT_I18N::translate('The Administration Tasks module must be installed and enabled to display this page.').'<p>';
		}
	}
	
	/**
	 * Ajax call to generate a new token. Display the token, if generated.
	 * Tokens call only be generated by a site administrator.
	 *
	 */
	private function generatetoken(){
		$controller = new WT_Controller_Ajax();
		$html = WT_I18N::translate('no_token_defined');
		
		if(WT_Perso_Admin_Task::isModuleOperational() &&  WT_USER_IS_ADMIN){
			$token = WT_Perso_Functions::generateRandomToken();
			set_module_setting($this->getName(), 'PAT_FORCE_EXEC_TOKEN', $token);
			AddToLog($this->getTitle().' : New token generated.', 'config');
			$html = $token;
		}
		else{
			AddToLog($this->getTitle().' : Unauthorised attempt to change the token.', 'error');
		}
		
		$controller->pageHeader();
		echo $html;
	}
	
	/**
	 * Trigger the executions of the tasks that are meant to be executed.
	 * Do not display anything. 
	 *
	 */
	private function trigger(){
		$controller = new WT_Controller_Ajax();
		$controller->pageHeader();
		if(WT_Perso_Admin_Task::isModuleOperational()){
			$taskname = WT_Filter::get('task', WT_REGEX_ALPHANUM);
			$token_submitted = WT_Filter::get('force', WT_REGEX_ALPHANUM);
			$token = get_module_setting($this->getName(), 'PAT_FORCE_EXEC_TOKEN');	
					
			$sql = 
				'SELECT pat_name, pat_status, pat_last_run, pat_last_result, pat_frequency, pat_nb_occur, pat_running
					FROM ##padmintasks
					WHERE pat_status = ?
					AND (pat_running = ? OR DATE_ADD(pat_last_run, INTERVAL '.WT_Perso_Admin_Task::TASK_TIME_OUT.' SECOND) <= NOW())';
			$params = array('enabled', 0);
			if(is_null($token) || is_null($token_submitted) || $token != $token_submitted )
				$sql .= ' AND (DATE_ADD(pat_last_run, INTERVAL pat_frequency MINUTE) <= NOW() OR pat_last_result = 0)';
			if(!is_null($taskname)){
				$sql .= ' AND pat_name = ?';
				$params[] = $taskname;
			}
			$tasks = WT_DB::prepare($sql)->execute($params)->fetchAll(PDO::FETCH_ASSOC);
			foreach($tasks as $taskrow){
				if (file_exists(WT_ROOT.WT_P_ADMINTASKS_DIR.$taskrow['pat_name'].'.php')) {
					require_once WT_ROOT.WT_P_ADMINTASKS_DIR.$taskrow['pat_name'].'.php';
					$class=$taskrow['pat_name'].'_WT_Perso_Admin_Task';
					$task = new $class();
					$task->setParameters(
						$taskrow['pat_status'],
						new DateTime($taskrow['pat_last_run']),
						$taskrow['pat_last_result'] > 0 ? true : false,
						$taskrow['pat_frequency'],
						$taskrow['pat_nb_occur'],
						$taskrow['pat_running'] > 0 ? true : false
					);
					$task->execute();
				} else {
					WT_Perso_Admin_Task::deleteTask($taskrow['pat_name']);
				}
			}
			
		}
	}
	
	/**
	 *  Save Perso tasks settings.
	 * The id to be sent is under the format <strong><em>type_setting</em>-<em>task_name</em>-<em>setting</em>-<em>gedcom_id</em>-validate<strong>, with :
	 * 	- type_setting: <strong>task</strong>, <strong>module_setting</strong> or <strong>gedcom_setting</strong>
	 *  - task_name : related administration task	
	 *  - setting: setting to be change
	 *  - gedcom_id: if present, ID of the gedcom_id
	 *  - validate: if present, will validate the entry value, according to rule defined within the module.
	 */
	private function editsetting(){
	
		if(WT_Perso_Admin_Task::isModuleOperational()){
			$id=WT_Filter::post('id', '[a-zA-Z0-9_-]+');
			list($table, $id1, $id2, $id3, $id4)=explode('-', $id.'----');
				
			// The replacement value.
			$value=WT_Filter::post('value');
		
			// Validate the replacement value
			if($id4 == 'validate' && !is_null($id1)){
				switch($table){
					case 'task':
						$value = $this->validateConfigSettings($id2, $value);
						break;
					default:
						require_once WT_P_ADMINTASKS_DIR.$id1.'.php';
						$class=$id1.'_WT_Perso_Admin_Task';
						$task=new $class();
						$value = $task->validateConfigSettings($id2, $value);
				}
			}
				
			if($value === 'ERROR_VALIDATION') WT_Perso_Functions_Edit::fail();
				
			switch($table){
				case 'task':
					// Verify if the user has enough rights to modify the setting
					if(!WT_USER_IS_ADMIN) WT_Perso_Functions_Edit::fail();
					
					// Verify if a task has been specified;
					if(is_null($id1)) WT_Perso_Functions_Edit::fail();
					// Verify if a setting name has been specified;
					if(is_null($id2)) WT_Perso_Functions_Edit::fail();
					
					WT_DB::prepare('UPDATE `##padmintasks` SET '.$id2.' = ? WHERE pat_name = ?')
						->execute(array($value, $id1));
					
					$value = $this->formatConfigSettings($id2, $value);					
					WT_Perso_Functions_Edit::ok($value);
					break;
				case 'module_setting':
					// Verify if the user has enough rights to modify the setting
					if(!WT_USER_IS_ADMIN) WT_Perso_Functions_Edit::fail();
						
					// Verify if a task has been specified;
					if(is_null($id1)) WT_Perso_Functions_Edit::fail();
					// Verify if a setting name has been specified;
					if(is_null($id2)) WT_Perso_Functions_Edit::fail();
						
					// Authorised and valid - make update
					set_module_setting($this->getName(), 'PAT_'.$id1.'_'.$id2, $value);
					WT_Perso_Functions_Edit::ok($value);
					break;
				case 'gedcom_setting':
					// Verify if the user has enough rights to modify the setting
					if(!WT_USER_GEDCOM_ADMIN) WT_Perso_Functions_Edit::fail();
						
					// Verify if a task has been specified;
					if(is_null($id1)) WT_Perso_Functions_Edit::fail();
					// Verify if a setting name has been specified;
					if(is_null($id2)) WT_Perso_Functions_Edit::fail();
					// Verify if a gedcom ID has been specified;
					if(is_null($id3)) WT_Perso_Functions_Edit::fail();
						
					// Authorised and valid - make update
					set_gedcom_setting($id3, 'PAT_'.$id1.'_'.$id2, $value);
					WT_Perso_Functions_Edit::ok($value);
					break;
				default:
					WT_Perso_Functions_Edit::fail();
			}
		}
		WT_Perso_Functions_Edit::fail();
	}
	
	/**
	 * Validate tasks settings, depending on the setting
	 *
	 * @param string $setting The setting to validate
	 * @param mixed $value The value of the setting, to validate against rules
	 * @return mixed The value of the settings, after validation and transformation
	 */
	private function validateConfigSettings($setting, $value){
		switch($setting){
			case 'pat_frequency':
				if (!is_numeric(trim($value))) $value = 'ERROR_VALIDATION';
				break;
			case 'pat_nb_occur':				
				if (!is_numeric(trim($value))){
					if (strtolower(trim($value)) == strtolower(trim(WT_I18N::translate('Unlimited')))){
						$value = 0;
					}
					else{
						$value = 'ERROR_VALIDATION';
					}
				}
				break;
			default:
				break;
		}
		return $value;
	}
	
	/**
	 * Format config settings to be suitable for display
	 *
	 * @param string $setting The setting to validate
	 * @param mixed $value The value of the setting, to format
	 * @return mixed The value of the settings, after formatting
	 */
	public function formatConfigSettings($setting, $value){
		switch($setting){
			case 'pat_nb_occur':
				if($value == 0) $value = WT_I18N::translate('Unlimited');
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Ajax call to get the list of tasks (active or not) in the system.
	 * In case a task does not exist anymore, it is deleted.
	 * 
	 * Input parameters - GET :
	 *  - sEcho : datatable server-side processing parameter, must be returned as it
	 *  - iDisplayStart : datatable server-side processing parameter for paging, define the starting index of the list to be returned
	 *  - iDisplayLength : datatable server-side processing parameter for paging, define the length of the list to be returned
	 *  - sSearch : datatable server-side processing parameter for filtering, defines the filter 
	 * 
	 * JSON format
	 * {
	 * 		iTotalRecords : int - Total number of records,
	 * 		iTotalDisplayRecords : int - Total display number of records,
	 * 		sEcho : string - Parameter received from the request, to be returned,
	 * 		aaData : array
	 * 		{
	 * 			0 : string - Editable combobox for the task status,
	 * 			1 : string - Task name,
	 * 			2 : datetime - Last run time,
	 * 			3 : string - Result of last run,
	 * 			4 : string - Editable text field for task frequency in minute,
	 * 			5 : string - Editable text field for remaining number of occurences,
	 * 			6 : string - Is the task running?,
	 * 			7 : string - Button to launch a a forced execution of the task
	 * 		}
	 *
	 */
	private function ajaxtaskslist(){
		$controller = new WT_Perso_Controller_Json();
		
		if(WT_USER_IS_ADMIN && WT_Perso_Admin_Task::isModuleOperational()){		
			// AJAX callback for datatables
			$sql=
			'SELECT SQL_CALC_FOUND_ROWS'.
			' pat_status, pat_name, pat_last_run, pat_last_result, pat_frequency, pat_nb_occur, pat_running'.
			' FROM `##padmintasks`';
			$args=array();
			
			$sSearch=WT_Filter::get('sSearch');
			if ($sSearch) {
				$sql.=
				' WHERE pat_name LIKE CONCAT("%", ?, "%")';
				$args[]=$sSearch;
			}
			
			$iSortingCols=WT_Filter::get('iSortingCols');
			if ($iSortingCols) {
				$sql.=" ORDER BY ";
				for ($i=0; $i<$iSortingCols; ++$i) {
					// Datatables numbers columns 0, 1, 2, ...
					// MySQL numbers columns 1, 2, 3, ...
					switch (WT_Filter::get('sSortDir_'.$i)) {
						case 'asc':
							$sql.=(1+(int)WT_Filter::getInteger('iSortCol_'.$i)).' ASC ';
							break;
						case 'desc':
							$sql.=(1+(int)WT_Filter::getInteger('iSortCol_'.$i)).' DESC ';
							break;
					}
					if ($i<$iSortingCols-1) {
						$sql.=',';
					}
				}
			} else {
				$sql.=' ORDER BY pat_name ASC';
			}
			
			$iDisplayStart =(int)WT_Filter::getInteger('iDisplayStart');
			$iDisplayLength=(int)WT_Filter::getInteger('iDisplayLength');
			if ($iDisplayLength>0) {
				$sql.=" LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
			}
			
			// This becomes a JSON list, not a JSON array, so we need numeric keys.
			$aaData=WT_DB::prepare($sql)->execute($args)->fetchAll(PDO::FETCH_NUM);
			// Reformat the data for display
			foreach ($aaData as $k => &$row) {
				$task_name = $row[1];
				if (file_exists(WT_ROOT.WT_P_ADMINTASKS_DIR.$task_name.'.php')) {
					require_once WT_ROOT.WT_P_ADMINTASKS_DIR.$task_name.'.php';
					$class=$task_name.'_WT_Perso_Admin_Task';
					$task = new $class();
					$is_enabled = $row[0] === 'enabled' ? true : false;
					$is_running = $row[6];
					$row[0]=WT_Perso_Functions_Edit::select_edit_control_inline(
						'task-'.$task_name.'-pat_status',
						array('enabled' => WT_I18N::translate('Enabled'), 'disabled' => WT_I18N::translate('Disabled')),
						null,
						$row[0],
						null,
						$this->getName());
					$row[1] = $task->getPrettyName();
					$row[2] = $row[2];
					$row[3] = $row[3] ? WT_I18N::translate('Success') : WT_I18N::translate('Failure');
					$row[4] = WT_Perso_Functions_Edit::edit_module_field_inline('task-'.$task_name.'-pat_frequency--validate', $row[4], null, $this->getName());
					$row[5] = WT_Perso_Functions_Edit::edit_module_field_inline('task-'.$task_name.'-pat_nb_occur--validate', $row[5] > 0 ? $row[5] : WT_I18N::translate('Unlimited'), null, $this->getName());
					$row[6] = $row[6] ? WT_I18N::translate('Running') : WT_I18N::translate('Not running');
					if($is_enabled && !$is_running){
						$row[7] = '<button id="bt_runtask_'.$task_name.'" class="progressbutton" onClick="runAdminTask(\''.$task_name.'\');"><div id="bt_runtasktext_'.$task_name.'">'.WT_I18N::translate('Run').'</div></button>';
					}
					else{
						$row[7] = '';
					}
				}
				else{
					unset($aaData[$k]);
					WT_Perso_Admin_Task::deleteTask($task_name);
				}
			}
			
			// Total filtered rows
			$iTotalDisplayRecords= count($aaData);
			// Total unfiltered rows
			$iTotalRecords=WT_DB::prepare('SELECT COUNT(*) FROM `##padmintasks`')->fetchColumn();
		}
		else{
			$iTotalDisplayRecords = 0;
			$iTotalRecords = 0;
			$aaData = null;
		}
		
		$controller->pageHeader();
		echo json_encode(array( // See http://www.datatables.net/usage/server-side
				'sEcho'               =>(int)WT_Filter::getInteger('sEcho'),
				'iTotalRecords'       =>$iTotalRecords,
				'iTotalDisplayRecords'=>$iTotalDisplayRecords,
				'aaData'              =>$aaData
		));
	}
	
}

?>