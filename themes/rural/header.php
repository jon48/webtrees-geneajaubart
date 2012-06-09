<?php
// Header for Rural theme
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
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
// @package webtrees
// @subpackage Themes
// @author Jonathan Jaubart ($Author$)
// @version p_$Revision$ $Date$
// $HeadURL$

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

echo
	'<!DOCTYPE html>',
	'<html ', WT_I18N::html_markup(), '>',
	'<head>',
	'<meta charset="UTF-8">',
	'<title>', htmlspecialchars($title), '</title>',
	header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL),
	'<link rel="icon" href="', WT_THEME_URL, 'favicon.png" type="image/png">',
	'<link rel="stylesheet" type="text/css" href="', WT_STATIC_URL, 'js/jquery/css/jquery-ui.custom.css">',
	'<link rel="stylesheet" type="text/css" href="', $stylesheet, '">';

//PERSO Add extra style sheet for personal additions
$extrastylesheet= str_replace('.css', '.extra.css', $stylesheet);
echo '<link rel="stylesheet" type="text/css" href="', $extrastylesheet, '">';
//END PERSO

switch ($BROWSERTYPE) {
//case 'chrome': uncomment when chrome.css file needs to be added, or add others as needed
case 'msie':
	echo '<link type="text/css" rel="stylesheet" href="', WT_THEME_URL, $BROWSERTYPE, '.css">';
	break;
}

// Additional css files required (Only if Lightbox installed)
if (WT_USE_LIGHTBOX) {
		echo '<link rel="stylesheet" type="text/css" href="', WT_STATIC_URL, WT_MODULES_DIR, 'lightbox/css/album_page.css" media="screen">';
}

echo
	'</head>',
	'<body id="body">';

// begin header section
if ($view=='simple') {

	echo '<div id="header_simple" > </div>';
	echo '<div id="main_content">';
	echo '<div class="top_center_box">';
	echo '<div class="top_center_box_left" ></div><div class="top_center_box_right" ></div><div class="top_center_box_center"></div>';
	echo '</div>';
	echo '<div class="content_box">';
	echo  $javascript, '<div id="content">';

}
else {
	echo '<div id="main_content">';
	echo '<div id="header">';
	echo  '<div id="htopright">';
	global $WT_IMAGES;
	//echo '<div class="header_search">',
	echo 	'<form action="search.php" method="post">',
			'<input type="hidden" name="action" value="general">',
			'<input type="hidden" name="topsearch" value="yes">',
			'<input type="search" name="query" size="25" placeholder="', WT_I18N::translate('Search'), '" dir="auto">',
			'<input type="image" class="image" src="', $WT_IMAGES['search'], '" alt="', WT_I18N::translate('Search'), '" title="', WT_I18N::translate('Search'), '">',
			'</form>';
	echo '</div>';
	echo  '<div id="hcenterright">';
	echo '<ul class="makeMenu">';
	if (WT_USER_ID) {
		echo '<li><a href="edituser.php">', WT_I18N::translate('Logged in as '), ' ', getUserFullName(WT_USER_ID), '</a></li> <li>', logout_link(), '</li>';
	} else {
		echo '<li>', login_link(), '</li> ';
	}
	echo '</li>';
	echo '</ul>';
	echo '<div class="gedtitle" dir="auto">',htmlspecialchars($GEDCOM_TITLE),'</div>';
	echo '</div>';
	echo  '<div id="hbottomright">';
	//PERSO Extend header
	$hook_print_header = new WT_Perso_Hook('h_print_header');
	$hook_print_header->execute();
	//END PERSO
	echo '<ul class="makeMenu">';
	$menu=WT_MenuBar::getFavoritesMenu();
	if ($menu) {
		echo $menu->GetMenuAsList();
	}
	$menu=WT_MenuBar::getThemeMenu();
	if ($menu) {
		echo $menu->GetMenuAsList();
	}
	$menu=WT_MenuBar::getLanguageMenu();
	if ($menu) {
		echo $menu->GetMenuAsList();
	}
	echo '</ul>';
	echo '</div>';
	//Prepare menu bar
	$menu_items=array(
	WT_MenuBar::getGedcomMenu(),
	WT_MenuBar::getMyPageMenu(),
	WT_MenuBar::getChartsMenu(),
	WT_MenuBar::getListsMenu(),
	WT_MenuBar::getCalendarMenu(),
	WT_MenuBar::getReportsMenu(),
	WT_MenuBar::getSearchMenu(),
	);
	foreach (WT_MenuBar::getModuleMenus() as $menu) {
		$menu_items[]=$menu;
	}
	echo '</div>'; // close header
	//end header section
	echo '<div class="top_center_box"/>';
	echo '<div class="top_center_box_left" ></div>';
	echo '<div class="top_center_box_right" ></div>';
	echo '<div class="top_center_box_center"></div>';
	echo '</div>';
	echo '<div class="content_box">';
	echo '<div id="topMenu">',
		'<div class="topMenu_left"></div>',
		'<div class="topMenu_right"></div>',
		'<div class="topMenu_center">',
		'<table align="center" id="main-menu">',
			'<tr>';
	$nbMenus = count($menu_items);
	for ($i = 0; $i < $nbMenus -1 ; $i++) {
		$menu = $menu_items[$i];
		if ($menu) {
			echo '<td valign="top"><ul class="main-menu-item">', $menu->getMenuAsList(), '</ul></td>';
		}
	}
	$menu = $menu_items[$nbMenus - 1];
	if ($menu) {
		echo '<td class="topmenu_last" valign="top"><ul class="main-menu-item">', $menu->getMenuAsList(), '</ul></td>';
	}
	unset($menu_items, $menu);
	echo '</tr>',
		'</table>',
		'</div>', // close topMenu_center
	'</div>'; // close topmenu
	// Display feedback from asynchronous actions
	echo '<div id="flash-messages">';
	foreach (Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->getMessages() as $message) {
		echo '<p class="ui-state-highlight">', $message, '</p>';
	}
	echo '</div>'; // <div id="flash-messages">
	// begin content section -->
	echo  $javascript, '<div id="content">';
}
?>