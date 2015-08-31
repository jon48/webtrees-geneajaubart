<?php
/**
 * Interface for Modules to indicate presence of hooks functions
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Module_HookSubscriber {
	
	/**
	 * Return the list of functions implementented in the class which needs to be registered as hooks.
	 * The format is either { function1, function 2,...} in which case the priority is the default one
	 * or { function1 => priority1, function2 => priority2, ...}
	 * 
	 * @return Array Array of hooks
	 */
	public function getSubscribedHooks();
	
}

?>