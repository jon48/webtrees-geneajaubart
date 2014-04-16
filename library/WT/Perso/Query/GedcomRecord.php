<?php
/**
 * MySQL queries on gedcom records.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Query_GedcomRecord {
	
	/**
	 * Gets all records id and gedcom of a type, in an associate array.
	 * 
	 * @param string $type Gedcom record type
	 * @param int $gid Gedcom id
	 * @return (array|null) List of records
	 */
	public static function gedcomrecords($type, $gid = WT_GED_ID) {
		$param = null;
		switch($type) {
			case 'INDI':
				$sql = 'SELECT i_id ged_id, i_gedcom gedcom FROM ##individuals WHERE i_file = ?';
				break;
			case 'FAM':
				$sql = 'SELECT f_id ged_id, f_gedcom gedcom FROM ##families WHERE f_file = ?';
				break;
			case 'SOUR':
				$sql = 'SELECT s_id ged_id, s_gedcom gedcom FROM ##sources WHERE s_file = ?';
				break;
			case 'OBJE':
				$sql = 'SELECT m_id ged_id, m_gedcom gedcom FROM ##media WHERE m_file = ?';
				break;
			case 'NOTE' :
			case 'REPO' :
				$sql = 'SELECT o_id ged_id, o_gedcom gedcom FROM ##other WHERE o_file = ? AND o_type = ?';
				$param = $type;
				break;
			default :
				$sql = null;
				break;
		}
		if($sql) {
			return WT_DB::prepare($sql)
				->execute($param ? array($gid, $param) : array($gid))
				->fetchAssoc();
		}
		return null;
	}
	
	
}

?>
