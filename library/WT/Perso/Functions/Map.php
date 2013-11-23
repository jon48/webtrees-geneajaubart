<?php
/**
 * Additional functions for maps (based on GoogleMaps module)
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Functions_Map {
	
	private static $_maps = null;
	private static $_enabledmaps = null;
	private static $_isGeoDispersionOperational = -1;
	
	/**
	 * Return HTML code for the flag icon of the requested place
	 *
	 * @param WT_Place $place The place to find
	 * @param int $height Height of the returned icon
	 * @return string HTML code of the flag icon
	 */
	public static function getPlaceIcon(WT_Place $place, $height){
		require_once WT_ROOT.WT_MODULES_DIR.'googlemap/googlemap.php'; // Cannot be outside of the function, causing issues with placehierarchy.php
		
		if(!$place->isEmpty()){
			$place_gedcom = $place->getGedcomName();
			$latlongval = get_lati_long_placelocation($place_gedcom);
			if($latlongval){
				return '<img class="flag_gm_h'.$height.'" src="'.WT_MODULES_DIR.'googlemap/'.$latlongval->pl_icon.'" title="'.$place_gedcom.'" alt="'.$place_gedcom.'" />';
			}
		}
		return '';
	}
	
	/**
	 * Returns the infered place hierarchy, determined from the Gedcom data
	 *
	 * @param int $gedid ID of the gedcom file
	 * @return array Places hierarchy
	 */
	public static function getPlaceHierarchy($gedid = WT_GED_ID){
		$plHierarchy = array();
	
		$cacheId = 'placeHierarchyArray'.$gedid;
		if(WT_Perso_Cache::isCached($cacheId)) {
			$plHierarchy = WT_Perso_Cache::get($cacheId);
		}
		else{
			$plHierarchy['ged_id'] = WT_GED_ID;		// This is already taken care of in the session.php
			$plHierarchy['isdefined'] = false;
			$plHierarchy['nblevels'] = 0;
			$plHierarchy['hierarchy'] = null;
			if($placestructure = WT_Perso_Functions_Map::getPlaceHierarchyHeader(WT_GED_ID)){
				$plHierarchy['isdefined'] = true;
				$plHierarchy['nblevels'] = count($placestructure);
				$plHierarchy['hierarchy'] = $placestructure;
			}
			else{
				list($plHierarchy['nblevels'], $plHierarchy['hierarchy']) = WT_Perso_Functions_Map::getRandomPlaceExample(WT_GED_ID);
			}
			WT_Perso_Cache::save($cacheId, $plHierarchy);
		}
	
		return $plHierarchy;
	}
	
	/**
	 * Return an array of the place hierarchy, as defined in the GEDCOM header
	 * The places are reversed compared to normal GEDCOM structure.
	 *
	 * @param int $ged_id ID of the gedcom file
	 * @return NULL|array Place hierarchy array
	 */
	public static function getPlaceHierarchyHeader($ged_id = WT_GED_ID){
		$head= WT_GedcomRecord::getInstance('HEAD');
		$headplac = $head->getFirstFact('PLAC');
		if($headplac && $headplacvalue = $headplac->getAttribute('FORM')){
			return array_reverse(array_map('trim',explode(',', $headplacvalue)));
		}
		return null;
	}
	
	/**
	 * Return an array based on a random example of place within the GEDCOM.
	 * The two elements of the array are :
	 * 		- the maximum number of levels
	 * 		- an array of the place elements, reversed compared to normal GEDCOM structure	 * 
	 *
	 * @param int $ged_id ID of the gedcom file
	 * @return null|array Array of random place example
	 */
	public static function getRandomPlaceExample($ged_id = WT_GED_ID){
		$randomPlace = null;
		$nbLevels = 0;
		
		//Select all '2 PLAC ' tags in the file and create array
		$place_list=array();
		$ged_data=WT_DB::prepare("SELECT i_gedcom FROM `##individuals` WHERE i_gedcom LIKE ? AND i_file=?")
			->execute(array("%\n2 PLAC %", $ged_id))
			->fetchOneColumn();
		foreach ($ged_data as $ged_datum) {
			preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
			foreach ($matches[1] as $match) {
				$place_list[$match]=true;
			}
		}
		$ged_data=WT_DB::prepare("SELECT f_gedcom FROM `##families` WHERE f_gedcom LIKE ? AND f_file=?")
		->execute(array("%\n2 PLAC %", $ged_id))
		->fetchOneColumn();
		foreach ($ged_data as $ged_datum) {
			preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
			foreach ($matches[1] as $match) {
				$place_list[$match]=true;
			}
		}
		// Unique list of places
		$place_list=array_keys($place_list);		
		
		//sort the array, limit to unique values, and count them
		$place_parts=array();
		usort($place_list, "utf8_strcasecmp");
		$i=count($place_list);
		
		//calculate maximum no. of levels to display
		$x=0;
		$goodexamplefound = false;
		while ($x<$i) {
			$levels=explode(",", $place_list[$x]);
			$parts=count($levels);
			if ($parts>=$nbLevels){
				$nbLevels=$parts;
				if(!$goodexamplefound){
					$randomPlace = $place_list[$x];
					if(min(array_map('strlen', $levels)) > 0){
						$goodexamplefound = true;
					}
				}
			}
			$x++;
		}
		
		$randomPlace = array_reverse(array_map('trim',explode(',', $randomPlace)));
		
		return array($nbLevels, $randomPlace);
	}
	
	/**
	 * Return whether the GeoDispersion module is active and the table has been created.
	 *
	 * @return bool True if module active and table created, false otherwise
	 */
	public static function isGeoDispersionModuleOperational(){
		if(self::$_isGeoDispersionOperational == -1){
			self::$_isGeoDispersionOperational = array_key_exists('perso_geodispersion', WT_Module::getActiveModules());
			if(self::$_isGeoDispersionOperational){
				self::$_isGeoDispersionOperational = WT_Perso_Functions::doesTableExist('##pgeodispersion');
			}
		}
		return self::$_isGeoDispersionOperational;
	}
		
	/**
	 * Return the list of geodispersion maps available within the maps folder.
	 *
	 * @return array List of available maps
	 */
	public static function getAvailableGeoDispersionMaps(){
		if (self::$_maps===null) {
			$path = WT_ROOT.WT_MODULES_DIR.'perso_geodispersion/maps';
			if(is_dir($path)){
				$dir=opendir($path);
				while (($file=readdir($dir))!==false) {
					if (preg_match('/^[a-zA-Z0-9_]+.xml$/', $file)) {
						self::$_maps[$file]=$file;
					}
				}
				if(self::$_maps) uasort(self::$_maps, create_function('$x,$y', 'return utf8_strcasecmp((string)$x, (string)$y);'));
				//self::$_maps = array_merge(array('nomap' => WT_I18N::translate('No map')), self::$_maps);
			}
		}
		return self::$_maps;
	}
	
	/**
	 * Return the list of geodispersion maps recorded and enabled for a specific GEDCOM
	 *
	 * @param int $gedid ID of the gedcom file
	 * @return array List of enabled maps
	 */
	public static function getEnabledGeoDispersionMaps($gedid = WT_GED_ID){
		if (self::$_enabledmaps===null) {
			self::$_enabledmaps = WT_DB::prepare(
				'SELECT pg_id AS id, pg_descr AS title'.
				' FROM ##pgeodispersion'.
				' WHERE pg_file=? AND pg_status=?'.
				' ORDER BY pg_descr'
			)
			->execute(array($gedid, 'enabled'))
			->fetchAll(PDO::FETCH_ASSOC);
		}
		return self::$_enabledmaps;
	}
} 

?>