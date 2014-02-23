<?php
 /**
 * Base controller for Plain text Ajax pages
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Controller_PlainAjax extends WT_Controller_Ajax {

	// Extend class WT_Controller_Ajax
	public function pageHeader() {
		// We have finished writing session data, so release the lock
		Zend_Session::writeClose();
		// Ajax responses are always UTF8
		header('Content-Type: text/plain; charset=UTF-8');
		$this->page_header=true;
		return $this;
	}
	
	// Extend class WT_Controller_Ajax
	public function pageFooter() {
		return $this;
	}
}
