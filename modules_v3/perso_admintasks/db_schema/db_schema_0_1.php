<?php
/**
 * Update the perso_admintasks module database schema from version 0 to version 1
 *
 * Version 0: empty database
 * Version 1: create the table
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

WT_DB::exec(
	"CREATE TABLE IF NOT EXISTS `##padmintasks` (".
	" pat_name 			VARCHAR(32) 				NOT NULL,".
	" pat_status		ENUM('enabled','disabled') 	NOT NULL DEFAULT 'disabled',".		
	" pat_last_run 		DATETIME 					NOT NULL DEFAULT '2000-01-01 00:00:00',".	
	" pat_last_result 	TINYINT(1)					NOT NULL DEFAULT 1,".		// 0 for error, 1 for success
	" pat_frequency		INTEGER						NOT NULL DEFAULT 10080,".		// In min, Default every week
	" pat_nb_occur	 	SMALLINT					NOT NULL DEFAULT 0,".
	" pat_running 		TINYINT(1)					NOT NULL DEFAULT 0,".
	" PRIMARY KEY (pat_name)".
	") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
