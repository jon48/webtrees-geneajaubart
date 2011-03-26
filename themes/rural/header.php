<?php
/**
 * Header for Rural theme
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2010 webtrees development team.
 *
 * Derived from PhpGedView
 * Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package webtrees
 * @subpackage Themes
 * @author Jonathan Jaubart ($Author$)
 * @version p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

echo
	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	'<html xmlns="http://www.w3.org/1999/xhtml" ', WT_I18N::html_markup(), '>',
	'<head>',
	'<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />',
	'<title>', htmlspecialchars($title), '</title>',
	'<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />',
	'<link rel="stylesheet" href="', $stylesheet, '" type="text/css" media="all" />';


if (isset($_GET["mod_action"]) && $_GET["mod_action"]=="places_edit") {
	echo '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';
}

if (file_exists(WT_THEME_DIR.$BROWSERTYPE.'.css')) {
	echo '<link rel="stylesheet" href="', WT_THEME_DIR.$BROWSERTYPE, '.css" type="text/css" media="all" />';
}
// Additional css files required (Only if Lightbox installed)
if (WT_USE_LIGHTBOX) {
	if ($TEXT_DIRECTION=='rtl') {
		echo '<link rel="stylesheet" href="', WT_MODULES_DIR, 'lightbox/css/clearbox_music_RTL.css" type="text/css" />';
		echo '<link rel="stylesheet" href="', WT_MODULES_DIR, 'lightbox/css/album_page_RTL_ff.css" type="text/css" media="screen" />';
	} else {
		echo '<link rel="stylesheet" href="', WT_MODULES_DIR, 'lightbox/css/clearbox_music.css" type="text/css" />';
		echo '<link rel="stylesheet" href="', WT_MODULES_DIR, 'lightbox/css/album_page.css" type="text/css" media="screen" />';
	}
}

if (!empty($LINK_CANONICAL)) {
	echo '<link rel="canonical" href="', $LINK_CANONICAL, '" />';
}
if (!empty($META_DESCRIPTION)) {
	echo '<meta name="description" content="', htmlspecialchars($META_DESCRIPTION), '" />';
}
echo '<meta name="robots" content="', $META_ROBOTS, '" />';
if (!empty($META_GENERATOR)) {
	echo '<meta name="generator" content="', $META_GENERATOR, '" />';
}

echo
	$javascript,
	'<link type="text/css" href="js/jquery/css/jquery-ui.custom.css" rel="Stylesheet" />',
	'<link type="text/css" href="', WT_THEME_DIR, 'jquery/jquery-ui_theme.css" rel="Stylesheet" />';
if ($TEXT_DIRECTION=='rtl') {
	echo '<link type="text/css" href="', WT_THEME_DIR, 'jquery/jquery-ui_theme_rtl.css" rel="Stylesheet" />';
}
echo
	'<link type="text/css" href="', WT_THEME_DIR, 'modules.css" rel="Stylesheet" />',
	'</head>',
	'<body id="body">';

// begin header section
if ($view=='simple') {
	
	echo '<div id="header_simple" > </div>';
	echo '<div id="main_content">';
	echo '<div class="top_center_box"/>';
	echo '<div class="top_center_box_left" ></div><div class="top_center_box_right" ></div><div class="top_center_box_center"></div>';
	echo '</div>';
	echo '<div class="content_box">';
	echo '<div id="content">';
	
}
else {
	echo '<div id="main_content">';
	echo '<div id="header" class="', $TEXT_DIRECTION, '">';

	if (!$SEARCH_SPIDER) {
		echo  '<div id="htopright">';
		//echo '<div class="header_search">',
		echo 	'<form action="search.php" method="post">',
			'<input type="hidden" name="action" value="general" />',
			'<input type="hidden" name="topsearch" value="yes" />',
			'<input type="text" name="query" size="25" value="', WT_I18N::translate('Search'), '"',
				'onfocus="if (this.value==\'', WT_I18N::translate('Search'), '\') this.value=\'\'; focusHandler();"',
				'onblur="if (this.value==\'\') this.value=\'', WT_I18N::translate('Search'), '\';" />',
			'<input type="image" class="image" src="', $WT_IMAGES['search'], '" alt="', WT_I18N::translate('Search'), '" title="', WT_I18N::translate('Search'), '" />',
			'</form>';
		echo '</div>';
		echo  '<div id="hcenterright">';
		echo '<ul class="makeMenu">';
		echo '<li>';
		if (WT_USER_ID) {
			echo '<a href="edituser.php">', WT_I18N::translate('Logged in as '), ' (', WT_USER_NAME, ')</a> | ', logout_link();
		} elseif (empty($SEARCH_SPIDER)) {
			echo login_link();
		}
		echo '</li>';
		echo '</ul>';
		echo '</div>';
		global $ALLOW_THEME_DROPDOWN;
		echo  '<div id="hbottomright">';
		echo '<ul class="makeMenu">';
		$favmenu = WT_MenuBar::getFavoritesMenu();
		if (count($favmenu->submenus)>1) {
			echo '<li>';
			echo $favmenu->getMenuAsList();
			echo '</li> | ';			
		}
		if ($ALLOW_THEME_DROPDOWN && get_site_setting('ALLOW_USER_THEMES')) {
			echo '<li>';
			echo WT_MenuBar::getThemeMenu()->getMenuAsList();
			echo '</li> | ';
		}
		$language_menu=WT_MenuBar::getLanguageMenu();
		if ($language_menu) {
			echo '<li>';
			echo $language_menu->getMenuAsList();
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
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
		'<table align="center" class="menu">',
			'<tr>';
				$menu=WT_MenuBar::getGedcomMenu();
				if ($menu) {
					echo '<td valign="top">', $menu->getMenu(), '</td>';

				}
				$menu=WT_MenuBar::getMyPageMenu();
				if ($menu) {
					echo '<td valign="top">', $menu->getMenu(), '</td>';

				}
				$menu=WT_MenuBar::getChartsMenu();
				if ($menu) {
					echo '<td valign="top">', $menu->getMenu(), '</td>';

				}
				$menu=WT_MenuBar::getListsMenu();
				if ($menu) {
					echo '<td valign="top">', $menu->getMenu(), '</td>';

				}
				$menu=WT_MenuBar::getCalendarMenu();
				if ($menu) {
					echo '<td valign="top">', $menu->getMenu(), '</td>';

				}
				$menu=WT_MenuBar::getReportsMenu();
				if ($menu) {
					echo '<td valign="top">', $menu->getMenu(), '</td>';

				}
				$menu=WT_MenuBar::getSearchMenu();
				if ($menu) {
					echo '<td valign="top">', $menu->getMenu(), '</td>';

				}
				$menus=WT_MenuBar::getModuleMenus();
				foreach ($menus as $m=>$menu) {
					if ($menu) {
						echo '<td valign="top">', $menu->getMenu(), '</td>';
	
					}
				}
				$menu=WT_MenuBar::getHelpMenu();
				if ($menu) {
					echo '<td class="topmenu_last" valign="top">', $menu->getMenu(), '</td>';
				}
			echo '</tr>',
		'</table>',
		'</div>',
	'</div>', // close menu
// begin content section -->
'<div id="content">';
}
