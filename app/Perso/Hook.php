<?php
/**
 * Class to manage Hooks (subscription and execution
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Hook {

	//Private variables
	protected $hook_function; //Hook function
	protected $hook_context;

	//Constants
	private static $DEFAULT_PRIORITY = 99;
	private static $_isModuleOperational = -1;

	/**
	 * Constructor for Hook class
	 *
	 * @param string $hook_function_in Hook function to be subscribed or executed
	 * @param string $hook_context_in Hook context to be subscribed or executed
	 */
	public function __construct($hook_function_in, $hook_context_in = 'all'){
		$this->hook_function = $hook_function_in;
		$this->hook_context = $hook_context_in;
	}

	/**
	 * Methods for subscribing to Hooks
	 */


	/**
	 * Subscribe a class implementing WT_Perso_Module_HookSubscriber to the Hook
	 * The Hook is by default enabled.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function subscribe($hsubscriber){
		if(self::isModuleOperational()){
			$statement = WT_DB::prepare(
					"INSERT IGNORE INTO `##phooks` (ph_hook_function, ph_hook_context, ph_module_name)".
					" VALUES (?, ?, ?)"
			)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 *  Define the priority for execution of the Hook for the specific WT_Perso_Module_HookSubscriber
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 * @param int $priority Priority of execution
	 */
	public function setPriority($hsubscriber, $priority){
		if(self::isModuleOperational()){
			WT_DB::prepare(
			"UPDATE `##phooks`".
			" SET ph_module_priority=?".
			" WHERE ph_hook_function=?".
			" AND ph_hook_context=?".
			" AND ph_module_name=?"
					)->execute(array($priority, $this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Enable the hook for a specific WT_Perso_Module_HookSubscriber.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function enable($hsubscriber){
		if(self::isModuleOperational()){
		WT_DB::prepare(
		"UPDATE `##phooks`".
		" SET ph_status='enabled'".
		" WHERE ph_hook_function=?".
		" AND ph_hook_context=?".
		" AND ph_module_name=?"
				)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Disable the hook for a specific WT_Perso_Module_HookSubscriber.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function disable($hsubscriber){
		if(self::isModuleOperational()){
		WT_DB::prepare(
		"UPDATE `##phooks`".
		" SET ph_status='disabled'".
		" WHERE ph_hook_function=?".
		" AND ph_hook_context=?".
		" AND ph_module_name=?"
				)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Remove the hook for a specific WT_Perso_Module_HookSubscriber.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function remove($hsubscriber){
		if(self::isModuleOperational()){
		WT_DB::prepare(
		"DELETE FROM `##phooks`".
		" WHERE ph_hook_function=?".
		" AND ph_hook_context=?".
		" AND ph_module_name=?"
				)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}


	/**
	 * Methods for execution of the Hook
	 *
	 */

	/**
	 * Return the results of the execution of the hook function for all subscribed and enabled modules, in the order defined by their priority.
	 * Parameters can be passed if the hook requires them.
	 *
	 * @return array Results of the hook executions
	 */
	public function execute(){
		$result = array();
		if(self::isModuleOperational()){
			$params = func_get_args();
			$sqlquery = '';
			$sqlparams = array($this->hook_function);
			if($this->hook_context != 'all') {
				$sqlparams = array($this->hook_function, $this->hook_context);
				$sqlquery = " OR ph_hook_context=?";
			}
			$module_names=WT_DB::prepare(
					"SELECT ph_module_name AS module, ph_module_priority AS priority FROM `##phooks`".
					" WHERE ph_hook_function = ? AND (ph_hook_context='all'".$sqlquery.") AND ph_status='enabled'".
					" ORDER BY ph_module_priority ASC, module ASC"
			)->execute($sqlparams)->fetchAssoc();
			asort($module_names);
			foreach ($module_names as $module_name => $module_priority) {
				require_once WT_ROOT.WT_MODULES_DIR.$module_name.'/module.php';
				$class=$module_name.'_WT_Module';
				$hook_class=new $class();
				$result[] = call_user_func_array(array($hook_class, $this->hook_function), $params);
			}
		}
		return $result;
	}

	/*
	 * Returns the number of active modules linked to a hook
	*
	* @return int Number of active modules
	*/
	public function getNumberActiveModules(){
		if(self::isModuleOperational()){
			$sqlquery = '';
			$sqlparams = array($this->hook_function);
			if($this->hook_context != 'all') {
				$sqlparams = array($this->hook_function, $this->hook_context);
				$sqlquery = " OR ph_hook_context=?";
			}
			$module_names=WT_DB::prepare(
					"SELECT ph_module_name AS modules FROM `##phooks`".
					" WHERE ph_hook_function = ? AND (ph_hook_context='all'".$sqlquery.") AND ph_status='enabled'"
			)->execute($sqlparams)->fetchOneColumn();
			return count($module_names);
		}
		return 0;
	}

	/*
	 * Return whether any active module is linked to a hook
	*
	* @return bool True is active modules exist, false otherwise
	*/
	public function hasAnyActiveModule(){
		return ($this->getNumberActiveModules()>0);
	}

	/**
	 * Static functions for Hooks.
	 */

	/**
	 * Return whether the Hook module is active and the table has been created.
	 *
	 * @return bool True if module active and table created, false otherwise
	 */
	public static function isModuleOperational(){
		if(self::$_isModuleOperational == -1){
			self::$_isModuleOperational = array_key_exists('perso_hooks', WT_Module::getActiveModules());
			if(self::$_isModuleOperational){
				self::$_isModuleOperational = WT_Perso_Functions::doesTableExist('##phooks');
			}
		}
		return self::$_isModuleOperational;
	}


	/**
	 * Get the list of possible hooks in the list of modules files.
	 * A hook will be registered:
	 * 		- for all modules already registered in Webtrees
	 * 		- if the module implements WT_Perso_Module_HookSubscriber nterface
	 * 		- if the method exist within the module
	 *
	 * @return Array List of possible hooks, with the priority
	 */
	static public function getPossibleHooks() {
		static $hooks=null;
		if ($hooks===null) {
			$modules = $module_names=WT_DB::prepare("SELECT module_name FROM `##module`")->execute()->fetchOneColumn();
			$dir=opendir(WT_ROOT.WT_MODULES_DIR);
			while (($file=readdir($dir))!==false) {
				if (preg_match('/^[a-zA-Z0-9_]+$/', $file) && file_exists(WT_ROOT.WT_MODULES_DIR.$file.'/module.php')) {
					require_once WT_ROOT.WT_MODULES_DIR.$file.'/module.php';
					$class=$file.'_WT_Module';
					$hook_class=new $class();
					if( in_array($file, $modules) && $hook_class instanceof WT_Perso_Module_HookSubscriber){
						$subscribedhooks = $hook_class->getSubscribedHooks();
						if(is_array($subscribedhooks)){
							foreach($subscribedhooks as $key => $value){
								if(is_int($key)) {
									$hook_item = $value;
									$priority = WT_Perso_Hook::$DEFAULT_PRIORITY;
								}
								else{
									$hook_item = explode('#', $key, 2);
									$priority = $value;
								}
								if($hook_item && count($hook_item) == 2){
									$hook_func = $hook_item[0];
									$hook_cont = $hook_item[1];
								}
								else{
									$hook_func = $hook_item[0];
									$hook_cont = 'all';
								}
								if(method_exists($hook_class, $hook_func)){
									$hooks[$hook_class->getName().'#'.$hook_func.'#'.$hook_cont]=$priority;
								}
							}
						}
					}
				}
			}
		}
		return $hooks;
	}

	/**
	 * Get the list of hooks intalled in webtrees, with their id, status and priority.
	 *
	 * @return DBOStatement List of installed hooks
	 */
	static public function getRawInstalledHooks(){
		if(self::isModuleOperational()){
		return WT_DB::prepare(
				"SELECT ph_id AS id, ph_module_name AS module, ph_hook_function AS hook, ph_hook_context as context, ph_module_priority AS priority,  ph_status AS status".
				" FROM `##phooks`".
				" ORDER BY hook ASC, status ASC, priority ASC, module ASC"
		)->execute()->fetchAll();
		}
		return array();
	}

	/**
	 * Get the list of hooks intalled in webtrees, with their id, status and priority.
	 *
	 * @return Array List of installed hooks, with id, status and priority
	 */
	static public function getInstalledHooks(){
		static $installedhooks =null;
		if($installedhooks===null){
			$dbhooks=WT_Perso_Hook::getRawInstalledHooks();
			foreach($dbhooks as $dbhook){
				$installedhooks[($dbhook->module).'#'.($dbhook->hook).'#'.($dbhook->context)] = array('id' => $dbhook->id, 'status' => $dbhook->status, 'priority' => $dbhook->priority);
			}
		}
		return $installedhooks;
	}

}

?>