<?php
/**
 * Update the WT_Perso_Inference_SimpleComparisonEngine classe database schema from version 0 to version 1
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
	'CREATE TABLE IF NOT EXISTS `##pinferences_simplecomp` ('.
	' pisc_id    		INTEGER AUTO_INCREMENT NOT NULL,'.
	' pisc_file    		INTEGER 	 		NOT NULL,'.
	' pisc_record_type	ENUM(\'INDI\', \'FAM\', \'SOUR\', \'REPO\', \'NOTE\', \'OBJE\') NOT NULL DEFAULT \'INDI\','.
	' pisc_record_value VARCHAR(100) NOT NULL,'.
	' pisc_rela_value   VARCHAR(100) NOT NULL,'.
	' pisc_matches		INTEGER		 NOT NULL DEFAULT 0,'.
	' pisc_count		INTEGER		 NOT NULL DEFAULT 0,'.
	' PRIMARY KEY (pisc_id),'.
	' UNIQUE KEY uk (pisc_file, pisc_record_type, pisc_record_value, pisc_rela_value)'.
	') COLLATE utf8_unicode_ci ENGINE=InnoDB'
);

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
