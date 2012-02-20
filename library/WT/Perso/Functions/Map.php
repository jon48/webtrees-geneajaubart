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

require_once WT_ROOT.WT_MODULES_DIR.'googlemap/googlemap.php';

class WT_Perso_Functions_Map {
	
	private static $_maps = null;
	private static $_enabledmaps = null;
	
	/**
	 * Return HTML code for the flag icon of the requested place
	 *
	 * @param string $place The place to find
	 * @param int $height Height of the returned icon
	 * @return string HTML code of the flag icon
	 */
	public static function getPlaceIcon($place, $height){
		$latlongval = get_lati_long_placelocation($place);
		if ((count($latlongval) == 0) && (!empty($GM_DEFAULT_TOP_VALUE))) {
			$latlongval = get_lati_long_placelocation($place.", ".$GM_DEFAULT_TOP_VALUE);
		}
		if(count($latlongval) != 0){
			return '<img class="flag_gm_h'.$height.'" src="'.WT_MODULES_DIR.'googlemap/'.$latlongval['icon'].'" title="'.$place.'" alt="'.$place.'" />';
		}
		else{
			return '';
		}
	}
	
	/**
	 * Return an array of the place hierarchy, as defined in the GEDCOM header
	 * The places are reversed compared to normal GEDCOM structure.
	 *
	 * @param int $ged_id ID of the gedcom file
	 * @return NULL|array Place hierarchy array
	 */
	public static function getPlaceHierarchyHeader($ged_id = WT_GED_ID){
		$hierarchyarray = null;
		$head=find_other_record('HEAD', $ged_id);
		$ct=preg_match('/1 PLAC/', $head, $match);
		if ($ct > 0) {
			$softrec=get_sub_record(1, '1 PLAC', $head);
			$tt=preg_match('/2 FORM (.*)/', $softrec, $tmatch);
			if ($tt > 0) {
				$places=trim($tmatch[1]);
				$hierarchyarray = array_reverse(array_map('trim',explode(',', $places)));
			}
		}
		return $hierarchyarray;
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
		$nbLevels = ($nb = WT_DB::prepare("SELECT MAX(p_level) FROM `##places` WHERE p_file=?")
						->execute(array($ged_id))
						->fetchOne(0))? $nb + 1 : 0;
		if($nbLevels > 0){
			$sample = WT_DB::prepare(
				"SELECT pl_p_id AS place_id
				FROM `##placelinks`
				JOIN `##places` ON ##placelinks.pl_p_id = ##places.p_id
				WHERE pl_file= ? AND ##places.p_level = ? AND LENGTH(##places.p_place) > 0 
				ORDER BY RAND() LIMIT 1")
				->execute(array($ged_id, $nbLevels - 1))
				->fetchOne();
			if($sample){
				// Generate the SQL statement
				$select = "p1.p_place";
				$from = "`##places` p1";
				for($i = 2; $i <= $nbLevels ; $i++) {
					$select .= ", ', ', p".$i.".p_place";
					$from .= " JOIN `##places` p".$i." ON (p".($i-1).".p_parent_id=p".$i.".p_id AND p".($i-1).".p_file=p".$i.".p_file)";
				}
				$sql = 
					'SELECT CONCAT('.$select.')'.
					' FROM '.$from.
					' WHERE p1.p_id = ? AND p'.$nbLevels.'.p_parent_id=0 AND p1.p_file=?'.
					' LIMIT 1';
				
				$dbresult = WT_DB::prepare($sql)->execute(array($sample, $ged_id))->fetchOne();
				$randomPlace = array_reverse(array_map('trim',explode(',', $dbresult)));
			}
		}
		return array($nbLevels, $randomPlace);
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
				self::$_maps = array_merge(array('nomap' => WT_I18N::translate('No map')), self::$_maps);
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