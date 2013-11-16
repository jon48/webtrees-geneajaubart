<?php
/**
 * Abstract Class for Admin Tasks and a sub class Configurable Admin Task
 *
 * @package webtrees
 * @subpackage subpackage
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

abstract class WT_Perso_Admin_ConfigurableTask extends WT_Perso_Admin_Task {	
	
	/**
	 * Returns HTML code for the task configuration tab in the Administration panel 
	 *
	 * @return string HTML code for the configuration tab
	 */
	abstract public function getConfigTabContent();
}

abstract class WT_Perso_Admin_Task{
	
	const TASK_TIME_OUT = 300; // Time out for runnign tasks, in seconds. Default 5 min
	
	private static $_isModuleOperational = -1;
	
	//private variables
	protected $_name;
	protected $_status;
	protected $_lastupdated;
	protected $_lastresult;
	protected $_frequency;
	protected $_nboccur;
	protected $_isrunning;
	

	/**
	 * Constructor for the Admin task class
	 *
	 */
	public function __construct(){
		$this->_name = str_replace('_WT_Perso_Admin_Task', '', get_class($this));
	}
	
	/**
	 * Set parameters of the Task
	 *
	 * @param string $status Status of the task, 'enabled' or 'disabled'
	 * @param datetime $lastupdated Time of the last task run
	 * @param bool $lastresult Result of the last run, true for success, false for failure
	 * @param int $frequency Frequency of execution in minutes
	 * @param int $nboccur Number of remaining occurrences, 0 for tasks not limited
	 * @param bool $isrunning Indicates if the task is currently running
	 */
	public function setParameters($status, $lastupdated, $lastresult, $frequency, $nboccur, $isrunning){
		$this->_status = $status;
		$this->_lastupdated = $lastupdated;
		$this->_lastresult = $lastresult;
		$this->_frequency = $frequency;
		$this->_nboccur = $nboccur;
		$this->_isrunning = $isrunning;
	}
	
	/**
	 * Return the name of the task
	 *
	 * @return string
	 */
	public function getName(){
		return $this->_name;
	}
	
	/**
	 * Return the status of the task in a boolean way
	 *
	 * @return boolean True if enabled
	 */
	public function isEnabled(){
		return $this->_status == 'enabled' ? true : false;	
	}
		
	/**
	 * Return the name to display for the task
	 *
	 * @return string Display name for the task
	 */
	abstract public function getPrettyName();
	
	/**
	 * Return the default frequency for the execution of the task
	 *
	 * @return int Frequency for the execution of the task
	 */
	abstract public function getDefaultFrequency();
	
	/**
	 * Execute the task's actions
	 */
	abstract protected function executeSteps();
		
	/**
	 * Execute the task, default skeleton
	 *
	 */
	public function execute(){		
		
		if($this->_lastupdated->add(new DateInterval('PT'.self::TASK_TIME_OUT.'S')) < new DateTime()) $this->_isrunning = false;
		
		if(!$this->_isrunning){  //TODO put in place a time_out for running...
			//TODO Log the executions in the logs
			$this->_lastresult = false;
			$this->_isrunning = true;
			$this->updateTask();

			AddToLog('Start execution of Perso Admin task: '.$this->getPrettyName(), 'debug');
			$this->_lastresult = $this->executeSteps();
			if($this->_lastresult){
				$this->_lastupdated = new DateTime();
				if($this->_nboccur > 0){
					$this->_nboccur--;
					if($this->_nboccur == 0) $this->_status = 'disabled';
				}
			}
			$this->_isrunning = false;
			$this->updateTask();
			AddToLog(
				'Execution completed for Perso Admin task: '.$this->getPrettyName().' - '.($this->_lastresult ? 'Success' : 'Failure'),
				'debug');
		}
	}
	
	/**
	 * Validate tasks settings, depending on the setting
	 *
	 * @param string $setting The setting to validate
	 * @param mixed $value The value of the setting, to validate against rules
	 * @return mixed The value of the settings, after validation and transformation
	 */
	public function validateConfigSettings($setting, $value){
		return $value;
	}
	
	/**
	 * Update the task details in the database
	 *
	 */
	private function updateTask(){
		WT_DB::prepare(
			'UPDATE ##padmintasks'.
			' SET pat_status = ?, pat_last_run = ?, pat_last_result = ?, pat_frequency = ?, pat_nb_occur = ?, pat_running =?'.
			' WHERE pat_name = ?'
		)->execute(array(
			$this->_status,
			$this->_lastupdated->format('Y-m-d H:i:s'),
			$this->_lastresult ? 1 : 0,
			$this->_frequency,
			$this->_nboccur,
			$this->_isrunning ? 1 : 0,
			$this->_name
		));
	}
	 	
	/*
	 * Static functions
	 */
	
	/**
	 * Return whether the AdminTasks module is active and the table has been created.
	 *
	 * @return bool True if module active and table created, false otherwise
	 */
	public static function isModuleOperational(){
		if(self::$_isModuleOperational == -1){
			self::$_isModuleOperational = array_key_exists('perso_admintasks', WT_Module::getActiveModules());
			if(self::$_isModuleOperational){
				self::$_isModuleOperational = WT_Perso_Functions::doesTableExist('##padmintasks');
			}
		}
		return self::$_isModuleOperational;
	}
	
	/**
	 * Gets and inserts in the DB the tasks for which a file exists in the tasks folder.
	 *
	 * @return array List of tasks in the folder:
	 */
	public static function getInstalledTasks() {
		$tasks=array();
		$dir=opendir(WT_ROOT.WT_P_ADMINTASKS_DIR);
		while (($file=readdir($dir))!==false){ 
			if(preg_match('/^([a-zA-Z0-9_]+)\.php$/', $file, $taskname)) {
				require_once WT_ROOT.WT_P_ADMINTASKS_DIR.$file;
				$class=$taskname[1].'_WT_Perso_Admin_Task';
				$task=new $class();
				WT_DB::prepare('INSERT IGNORE INTO `##padmintasks` (pat_name, pat_status, pat_frequency) VALUES (?, ?, ?)')
					->execute(array($task->getName(), 'disabled', $task->getDefaultFrequency()));
			}
		}
		return $tasks;
	}		
	
	/**
	 * Returns the list of active tasks
	 *
	 * @param bool $sort Should the list be sorted
	 * @return array List of active tasks
	 */
	public static function getActiveTasks($sort=true) {		
		$tasks_enabled=WT_DB::prepare(
			'SELECT SQL_CACHE pat_name, pat_status, pat_last_run, pat_last_result, pat_frequency, pat_nb_occur, pat_running
			 FROM `##padmintasks` WHERE pat_status=?'
		)->execute(array('enabled'))->fetchAll(PDO::FETCH_ASSOC);
		$tasks=array();
		foreach ($tasks_enabled as $taskrow) {
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
				$tasks[$taskrow['pat_name']]=$task;
			} else {
				// Task has been deleted from disk?  Delete it.
				self::deleteTask($taskrow['pat_name']);
			}
		}
		if ($sort) {
			uasort($tasks, create_function('$x,$y', 'return utf8_strcasecmp((string)$x, (string)$y);'));
		}
		return $tasks;
	}
	
	/**
	 * Delete the task from the DB
	 *
	 * @param string $task_name Task to delete
	 */
	public static function deleteTask($task_name){
		AddToLog('Perso Admin Task '.$task_name.' has been deleted from disk - deleting it from DB', 'config');
		WT_DB::prepare('DELETE FROM  `##padmintasks` WHERE pat_name=?')
		->execute(array($task_name));
		WT_DB::prepare("DELETE FROM  `##gedcom_setting` WHERE setting_name LIKE 'PAT_?%'")
		->execute(array($task_name));
	}
	
}

?>