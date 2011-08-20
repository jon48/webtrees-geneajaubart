<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: module.php 11892 2011-06-24 08:05:24Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class logged_in_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. (A list of users who are online now) */ WT_I18N::translate('Who is online');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Who is online" module */ WT_I18N::translate('A list of users and visitors who are currently online.');
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		// List active users
		$NumAnonymous = 0;
		$loggedusers = array ();
		foreach (get_logged_in_users() as $user_id=>$user_name) {
			if (WT_USER_IS_ADMIN || get_user_setting($user_id, 'visibleonline')) {
				$loggedusers[$user_id]=$user_name;
			} else {
				$NumAnonymous++;
			}
		}

		$id=$this->getName().$block_id;
		$title=$this->getTitle();
		$content='<table>';
		$LoginUsers=count($loggedusers);
		if ($LoginUsers==0 && $NumAnonymous==0) {
			$content.='<tr><td><b>' . WT_I18N::translate('No logged-in and no anonymous users') . '</b></td></tr>';
		}
		if ($NumAnonymous>0) {
			$content.='<tr><td><b>' . WT_I18N::plural('%d anonymous logged-in user', '%d anonymous logged-in users', $NumAnonymous, $NumAnonymous) . '</b></td></tr>';
		}
		if ($LoginUsers>0) {
			$content.='<tr><td><b>' . WT_I18N::plural('%d logged-in user', '%d logged-in users', $LoginUsers, $LoginUsers) . '</b></td></tr>';
		}
		if (WT_USER_ID) {
			foreach ($loggedusers as $user_id=>$user_name) {
				$content .= "<tr><td><br />".PrintReady(getUserFullName($user_id))." - ".$user_name;
				if (WT_USER_ID!=$user_id && get_user_setting($user_id, 'contactmethod')!="none") {
					$content .= "<br /><a href=\"javascript:;\" onclick=\"return message('" . $user_name . "');\">" . WT_I18N::translate('Send Message') . "</a>";
				}
				$content .= "</td></tr>";
			}
		}
		$content .= "</table>";

		if ($template) {
			require WT_THEME_DIR.'templates/block_main_temp.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isUserBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
	}
}
