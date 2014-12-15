<?php
/**
 * Update the perso_sosa module database schema from version 0 to version 1
 *
 * Version 0: empty database
 * Version 1: create the table
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
header('HTTP/1.0 403 Forbidden');
exit;
}

WT_DB::exec(
	"CREATE TABLE IF NOT EXISTS `##psosa` (".
	" ps_file      		INTEGER 	 		NOT NULL,".
	" ps_sosa      		BIGINT UNSIGNED 	NOT NULL,".		// Allow to calculate sosa on 64 generations
	" ps_i_id      		VARCHAR(20)		 	NOT NULL,".	
	" ps_gen			TINYINT				NULL,".
	" ps_birth_year		SMALLINT			NULL,".
	" ps_death_year		SMALLINT			NULL,".
	" PRIMARY KEY (ps_file, ps_sosa)".
	") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
WT_Site::setPreference($schema_name, $next_version);
