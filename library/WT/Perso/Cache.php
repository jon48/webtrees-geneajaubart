<?php
/**
 * Class to provide a Cache to Perso Modules
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

class WT_Perso_Cache{
	
	static private $_cache=null;
	static private $_isInit = false;
	
	/**
	 * Initialise the WT_Perso_Cache static class
	 *
	 */
	static private function init() {	
		// The translation libraries only work with a cache.
		$cache_options=array('automatic_serialization'=>true);
	
		if (ini_get('apc.enabled')) {
			self::$_cache=Zend_Cache::factory('Core', 'Apc', $cache_options, array());
		} else {
			if (!is_dir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache')) {
				// We may not have permission - especially during setup, before we instruct
				// the user to "chmod 777 /data"
				@mkdir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache');
			}
			if (is_dir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache')) {
				self::$_cache=Zend_Cache::factory('Core', 'File', $cache_options, array('cache_dir'=>WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache'));
			} else {
				// No cache available :-(
				self::$_cache=Zend_Cache::factory('Core', 'Zend_Cache_Backend_BlackHole', $cache_options, array(), false, true);
			}
		}
		
		self::$_isInit = true;
	}
	
	/**
	 * Initiliase the WT_Perso_Cache static class if not done.
	 *
	 */
	static private function checkInit(){
		if(!self::$_isInit) self::init();
	}
	
	/**
	 * Returns the name of the cached key, based on the value name and the calling module
	 *
	 * @param string $value Value name
	 * @param WT_Module $mod Calling module
	 * @return string Cached key name
	 */
	static private function getKeyName($value, WT_Module $mod = null){
		self::checkInit();
		$mod_name = 'perso';
		if($mod != null) $mod_name = $mod->getName();
		return $mod_name.'_'.$value;
	}

	/**
	 * Checks whether the value is already cached
	 *
	 * @param string $value Value name
	 * @param WT_Module $mod Calling module
	 * @return bool True is cached
	 */
	static public function isCached($value, WT_Module $mod = null) {
		self::checkInit();
		return self::$_cache->test(self::getKeyName($value, $mod));
	}
	
	/**
	 * Returns the cached value, if exists
	 *
	 * @param string $value Value name
	 * @param WT_Module $mod Calling module
	 * @return unknown_type Cached value
	 */
	static public function get($value, WT_Module $mod = null){
		self::checkInit();
		return self::$_cache->load(self::getKeyName($value, $mod));
	}
	
	
	/**
	 * Cache a value to the specified key
	 *
	 * @param string $value Value name
	 * @param unknown_type $data Value
	 * @param WT_Module $mod Calling module
	 * @return unknown_type Cached value
	 */
	static public function save($value, $data, WT_Module $mod = null){
		self::checkInit();
		self::$_cache->save($data, self::getKeyName($value, $mod));
		return self::get($value, $mod);
	}
	
	/**
	 * Clean the cache
	 *
	 */
	static public function clean(){
		self::checkInit();
		self::$_cache->clean();
	}
	
	
}

?>