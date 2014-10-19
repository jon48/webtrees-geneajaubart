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
	 * Get the singleton instance configured for a gedcom file
	 *
	 * @param string $enginename Inference engine to retrieve
	 * @param int $gedid Gedcom ID for the engine
	 * @return WT_Perso_Inference_EngineInterface|NULL Inference engine instance
	 */
	public static function getCurrentInferenceEngineInstance($gedid = WT_GED_ID) {
		$engine = get_gedcom_setting($gedid, 'PERSO_PI_INF_ENGINE');
		if($engine && strlen($engine) > 0) {
			return self::getInferenceEngineInstance($engine, $gedid);
		}
		return null;
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
		if(self::isModuleOperational() && class_exists($engineclass) && in_array('getInstance', get_class_methods($engineclass))) {
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
	
	/**
	 * Return HTML code to display an inferred place.
	 * The place can take a short (prefixed by (i)) or long form (display the confidence level.
	 * 
	 * @param WT_Fact $event Event to infer place for
	 * @param boolean $longform Should the long form be displayed.
	 * @return string HTML code for inferred place
	 */
	public static function printInferredPlace(WT_Fact $event, $longform=false) {
		global $SEARCH_SPIDER;
		
		$html = '';		
		$engine = self::getCurrentInferenceEngineInstance($event->getParent()->getGedcomId());
		if($engine) {
			$inferredvalue = $engine->getInferredValue($event->getParent(), $event->getTag().':PLAC');
			if($inferredvalue && is_array($inferredvalue) && count($inferredvalue) == 2 && $inferredvalue[0] instanceof WT_Place) {
				$eventinferredplace = $inferredvalue[0];
				if($longform) {
					$html = '<span class="label">'.WT_I18N::translate('Inferred:').'</span> ';
					if($SEARCH_SPIDER) {
						$html .= '<a href="' . $eventinferredplace->getURL() . '">' . $eventinferredplace->getFullName() . '</a>';
					}
					else {
						$html .= $eventinferredplace->getFullName();
					}
					$html .= ' <span class="inferredconfidence">'.WT_I18N::translate('[%.1f %%]', WT_Perso_Functions::getPercentage($inferredvalue[1], 1)).'</span>';
				}
				else {
					$html = WT_I18N::translate('(i) ').$eventinferredplace->getShortName();
				}
			}
		}
		return $html;
	}
	
}

?>