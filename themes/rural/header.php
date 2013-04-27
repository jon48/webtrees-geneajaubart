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

// This theme uses the jQuery “colorbox” plugin to display images
$this
	->addExternalJavascript(WT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(WT_JQUERY_WHEELZOOM_URL)
	->addInlineJavascript('
		activate_colorbox();	
		
		jQuery("body").on("click", "a.gallery", function(event) {		
			// Add colorbox to pdf-files
			jQuery("a[type^=application].gallery").colorbox({
				innerWidth: "75%",
				innerHeight:"75%",
				rel:        "gallery",
				iframe:     true,
				photo:      false,
				slideshow:     true,
				slideshowAuto: false,
				title:		function(){
					var url = jQuery(this).attr("href");
					var img_title = jQuery(this).data("title");
					return "<a href=\"" + url + "\" target=\"_blank\">" + img_title + "</a>";
				}
			});
		});
		jQuery.extend(jQuery.colorbox.settings, {
			initialWidth: "20%", initialHeight: "20%", 
			slideshowStart: "<div id=\"cboxSlideshowStart\">&nbsp;</div>",
			slideshowStop: "<div id=\"cboxSlideshowStop\">&nbsp;</div>",
			transition: "none",
			current: "{current} '.WT_I18N::translate('of').' {total}",
			title: function(){
				var img_title = jQuery(this).data("title");
				return img_title;
			}
		});
	');

echo
	'<!DOCTYPE html>',
	'<html ', WT_I18N::html_markup(), '>',
	'<head>',
	'<meta charset="UTF-8">',
	'<title>', htmlspecialchars($title), '</title>',
	header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL),
	'<link rel="icon" href="', WT_THEME_URL, 'favicon.png" type="image/png">',
	'<link rel="stylesheet" type="text/css" href="', WT_THEME_URL, 'jquery-ui-1.10.0/jquery-ui-1.10.0.custom.css">',
	'<link rel="stylesheet" type="text/css" href="', WT_THEME_URL, 'style.css', '">';

//PERSO Add extra style sheet for personal additions
echo '<link rel="stylesheet" type="text/css" href="', WT_THEME_URL, 'style.extra.css', '">';
// and Java script for Certificate Module
$this->addExternalJavascript(WT_STATIC_URL.WT_MODULES_DIR.'perso_certificates/js/activatecolorbox.js');
//END PERSO

switch ($BROWSERTYPE) {
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
	echo '<div class="content_box simpleview">';
}
else {
	global $WT_IMAGES;
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
	echo '<div class="gedtitle" dir="auto">',WT_TREE_TITLE,'</div>';
	echo '</div>';
	echo  '<div id="hbottomright">';
	//PERSO Extend header
	echo '<div id="perso-header">';
	$hook_print_header = new WT_Perso_Hook('h_print_header');
	$hook_print_header->execute();
	echo '</div>';
	//END PERSO
	echo '<ul id="extra-menu" class="makeMenu">';
	echo
		WT_MenuBar::getFavoritesMenu(),
		WT_MenuBar::getThemeMenu(),
		WT_MenuBar::getLanguageMenu(),
		'</ul>',
		'</div>';
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
	// begin content section -->
}
echo
	$javascript,
	WT_FlashMessages::getHtmlMessages(), // Feedback from asynchronous actions
	'<div id="content">';
?>