<?php
/**
 * TreeView module abstraction layer for webtrees > rev 10974
 *
 * Copyright (C) 2011 Daniel Faivre
 * 
 * These abstraction layer define in one file ALL that is needed elsewhere.
 * Technically, that is just a kind of pointers, and therefore there's no impact on performances
 * but doing so is a great asset for maintainability.
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
 */

// Parameters
define('TV_USE_LIGHTBOX', WT_USE_LIGHTBOX);
if (TV_USE_LIGHTBOX)
	define('TV_LIGHTBOX_JS_CALL', WT_ROOT.WT_MODULES_DIR.'lightbox/functions/lb_call_js.php'); // the lightbox file to embed required javascripts and css calls
define ('TV_SCRIPT_NAME', WT_SCRIPT_NAME);
define('TV_SCRIPT_FULL_PATH', WT_SERVER_NAME.WT_SCRIPT_PATH);
define('TV_GEDURL', WT_GEDURL);

// define ALL images path here. These paths are used in several places but are all declared one time in one place : here
define('TV_IMAGES_PATH', TV_SCRIPT_FULL_PATH.TV_TREEVIEW_ROOT.'images/');
global $WT_IMAGES;

define('TV_ZOOMIN', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['zoomin']) ? $WT_IMAGES['zoomin'] : ''));
define('TV_ZOOMOUT', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['zoomout']) ? $WT_IMAGES['zoomout'] : ''));
define('TV_NOZOOM', TV_IMAGES_PATH.'zoom0.gif');
define('TV_LEFT', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['ldarrow']) ? $WT_IMAGES['ldarrow'] : TV_IMAGES_PATH.'alignLeft.png'));
define('TV_CENTER', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['patriarch']) ? $WT_IMAGES['patriarch'] : TV_IMAGES_PATH.'center.gif'));
define('TV_RIGHT', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['rdarrow']) ? $WT_IMAGES['rdarrow'] : TV_IMAGES_PATH.'alignRight.png'));
define('TV_DATES', TV_IMAGES_PATH.'dates.gif');
define('TV_COMPACT', TV_IMAGES_PATH.'compact.gif');
define('TV_SFAMILY', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES["sfamily"]) ? $WT_IMAGES["sfamily"] : ''));
define('TV_TREE', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES["tree"]) ? $WT_IMAGES["tree"] : ''));
define('TV_OPEN_BOXES', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES["media"]) ? $WT_IMAGES["media"] : ''));
define('TV_CLOSE_BOXES', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES["fambook"]) ? $WT_IMAGES["fambook"] : ''));
define('TV_PRINT', TV_IMAGES_PATH.'print.png');
define('TV_LOADING', TV_SCRIPT_FULL_PATH.'images/loading.gif');
define('TV_BUTTON_FAMILY', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['button_family']) ? $WT_IMAGES['button_family'] : ''));
define('TV_DEFAULT_IMAGE_U', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['default_image_U']) ? $WT_IMAGES['default_image_U'] : ''));
define('TV_DEFAULT_IMAGE_M', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['default_image_M']) ? $WT_IMAGES['default_image_M'] : ''));
define('TV_DEFAULT_IMAGE_F', TV_SCRIPT_FULL_PATH.(isset($WT_IMAGES['default_image_F']) ? $WT_IMAGES['default_image_F'] : ''));

// Embed ALL external classes here. This simplify many things and at first maintainability.
// WT classes could be extended or tuned here without interferences elsewhere.
// In the future, that will be greatly helpful to ensure compliance with new webtrees versions. 
abstract class tvModule extends WT_Module {}
interface tvModule_Tab extends WT_Module_Tab {}
interface tvModule_Report extends WT_Module_Report {}
class tvPerson extends WT_Person {}
class tvFamily extends WT_Family {}
class tvI18N extends WT_I18N {}
class tvMedia extends WT_Media {}
class tvMenu extends WT_Menu {}
class tvGedcomTag extends WT_Gedcom_Tag {}

?>