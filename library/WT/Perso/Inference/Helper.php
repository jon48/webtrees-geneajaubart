<?php
/**
 * Helper for Inference module
 *
 * @package webtrees
 * @subpackage PersoLbrary
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Inference_Helper {
	
	private static $_isModuleOperational = -1;
	
	/**
	 * Return whether the Inference module is active. 
	 *
	 * @return bool True if module active, false otherwise
	 */
	public static function isModuleOperational(){
		if(self::$_isModuleOperational == -1){
			self::$_isModuleOperational = array_key_exists('perso_inferences', WT_Module::getActiveModules());
		}
		return self::$_isModuleOperational;
	}
		
	/**
	 * Get the singleton instance of the engine specified
	 * 
	 * @param string $enginename Inference engine to retrieve
	 * @param int $gedid Gedcom ID for the engine
	 * @return WT_Perso_Inference_EngineInterface|NULL Inference engine instance
	 */
	public static function getInferenceEngineInstance($enginename, $gedid = WT_GED_ID) {
		$engineclass = 'WT_Perso_Inference_'.$enginename;
		if(class_exists($engineclass) && in_array('getInstance', get_class_methods($engineclass))) {
			$engine = $engineclass::getInstance($gedid);
			if($engine instanceof WT_Perso_Inference_EngineInterface) return $engine;
		}
		return null;
	}
	
	/**
	 * Returns a list of available Inference Engines
	 * 
	 * @return array List of ineference engines
	 */
	public static function getInferenceEngines(){
		$engines=array();
		$dir=opendir(realpath(dirname(__FILE__)));
		while (($file=readdir($dir))!==false) {
			if (preg_match('/^([a-zA-Z0-9_]+Engine)\.php$/', $file, $matches)) {
				$enginename = $matches[1];
				if($engine = self::getInferenceEngineInstance($enginename)) {
					$engines[$enginename] = $engine->getTitle();
				}				
			}
		}
		uasort($engines, create_function('$x,$y', 'return utf8_strcasecmp((string)$x, (string)$y);'));
		return $engines;
	}
}

?>