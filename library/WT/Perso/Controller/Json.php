<?php
 /**
 * Base controller for all Json pages
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Controller_Json extends WT_Controller_Base {

	// Extend class WT_Controller_Base
	public function pageHeader() {
		// We have finished writing session data, so release the lock
		Zend_Session::writeClose();
		header('Content-Type: application/json');
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->page_header=true;
		return $this;
	}
		
	// Extend class WT_Controller_Base
	protected function pageFooter() {
		return $this;
	}
	
}
