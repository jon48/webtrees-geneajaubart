<?php
/**
 * Update the perso_hooks module database schema from version 0 to version 1
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

define('WT_PHOOKS_DB_SCHEMA_0_1', '');

WT_DB::exec(
	"CREATE TABLE IF NOT EXISTS `##phooks` (".
	" ph_id       			INTEGER AUTO_INCREMENT NOT NULL,".
	" ph_hook_function		VARCHAR(32)            NOT NULL,".
	" ph_module_name		VARCHAR(32)            NOT NULL,".
	" ph_module_priority	INTEGER            	   NOT NULL DEFAULT 99,".
	" ph_status      		ENUM('enabled', 'disabled') NOT NULL DEFAULT 'enabled',".
	" PRIMARY KEY (ph_id),".
	" UNIQUE KEY uk (ph_hook_function, ph_module_name),".
	" FOREIGN KEY ph_fk1 (ph_module_name) REFERENCES `##module` (module_name) ON DELETE CASCADE ON UPDATE CASCADE".
	") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
