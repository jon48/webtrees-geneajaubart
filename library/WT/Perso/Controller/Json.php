<?php
 /**
 * Base controller for all Json pages
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
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
	
	// Restrict access
	public function requireAdminLogin() {
		if (!WT_USER_IS_ADMIN) {			
			header('HTTP/1.0 403 Access Denied');
			exit;
		}
		return $this;
	}
	
	// Restrict access
	public function requireManagerLogin($ged_id=WT_GED_ID) {		
		if (
		$ged_id==WT_GED_ID && !WT_USER_GEDCOM_ADMIN ||
		$ged_id!=WT_GED_ID && !userGedcomAdmin(WT_USER_ID, $ged_id)
		) {
			header('HTTP/1.0 403 Access Denied');
			exit;
		}
		return $this;
	}
	
}
