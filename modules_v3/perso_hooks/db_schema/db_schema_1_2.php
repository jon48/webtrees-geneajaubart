<?php
/**
 * Update the perso_hooks module database schema from version 1 to version 2
 *
 * Version 0: empty database
 * Version 1: create the table
 * Version 1: add hook context feature
 *
 * @package webtrees
 * @subpackage SubPackage
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

define('WT_PHOOKS_DB_SCHEMA_1_2', '');

// Remove unique key on hookfunction and module name
WT_DB::exec(
	"ALTER TABLE `##phooks` DROP KEY uk"
);

//Create a new column for index
//This will also automatically fill the new column with the value all
WT_DB::exec(
	"ALTER TABLE `##phooks` ADD COLUMN ph_hook_context VARCHAR(32) NOT NULL DEFAULT 'all'"
);

//Create a new unique key including the new hook context
//Create a new column for index
//This will also automatically fill the new column with the value all
WT_DB::exec(
	"ALTER TABLE `##phooks` ADD UNIQUE KEY uk (ph_hook_function, ph_hook_context, ph_module_name)"
);

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);

?>