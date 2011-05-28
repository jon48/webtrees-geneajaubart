<?php
/**
 * Update the perso_sosa module database schema from version 0 to version 1
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

define('WT_PSOSA_DB_SCHEMA_0_1', '');

WT_DB::exec(
	"CREATE TABLE IF NOT EXISTS `##psosa` (".
	" ps_file      		INTEGER 	 		NOT NULL,".
	" ps_sosa      		BIGINT UNSIGNED 	NOT NULL,".		// Allow to calculate sosa on 64 generations
	" ps_i_id      		VARCHAR(20)		 	NOT NULL,".	
	" ps_gen			TINYINT				NULL,".
	" ps_birth_year		SMALLINT			NULL,".
	" ps_death_year		SMALLINT			NULL,".
	" PRIMARY KEY (ps_file, ps_sosa),".
	" FOREIGN KEY ph_fk1 (ps_i_id) REFERENCES `##individuals` (i_id) ON DELETE CASCADE ON UPDATE CASCADE".
	") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
set_site_setting($schema_name, $next_version);
