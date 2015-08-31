<?php
/**
 * Perso queries for statistics.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Query_Stats {
		
	/**
	 * Return the number of individuals in a tree
	 * 
	 * @param WT_Tree $tree Tree to query against
	 * @return int Number of individuals
	 */
	public static function totalIndividuals(WT_Tree $tree) {
		$result = 
			WT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##individuals` WHERE i_file=?")
			->execute(array($tree->tree_id))
			->fetchOne();
		
		return $result ?: 0;
	}
	
}