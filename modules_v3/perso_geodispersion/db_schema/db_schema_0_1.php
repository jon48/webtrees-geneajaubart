<?php
/**
 * Update the perso_geodispersion module database schema from version 0 to version 1
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
	'CREATE TABLE IF NOT EXISTS `##pgeodispersion` ('.
	' pg_id       		INTEGER AUTO_INCREMENT NOT NULL,'.
	' pg_file      		INTEGER 	 		NOT NULL,'.
	' pg_descr			VARCHAR(70)			NOT NULL,'.
	' pg_sublevel		TINYINT				NOT NULL,'.
	' pg_map			VARCHAR(70)			NULL,'.
	' pg_toplevel		TINYINT				NULL,'.
	' pg_status      	ENUM(\'enabled\', \'disabled\') NOT NULL DEFAULT \'enabled\','.
	' pg_useflagsgen	ENUM(\'yes\', \'no\') NOT NULL DEFAULT \'no\','.
	' pg_detailsgen		TINYINT				NOT NULL DEFAULT 0,'.
	' PRIMARY KEY (pg_id)'.
	") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
