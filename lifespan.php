<?php
// Display a timeline chart for a group of individuals
//
// Use the $pids array to set which individuals to show on the chart
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
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
//
// @version $Id: lifespan.php 10603 2011-01-25 09:28:33Z greg $

define('WT_SCRIPT_NAME', 'lifespan.php');
require './includes/session.php';

// GEDCOM elements that will be found but should not be displayed
$nonfacts = array("FAMS", "FAMC", "MAY", "BLOB", "OBJE", "SEX", "NAME", "SOUR", "NOTE", "BAPL", "ENDL", "SLGC", "SLGS", "_TODO", "_WT_OBJE_SORT", "CHAN", "HUSB", "WIFE", "CHIL", "BIRT", "DEAT", "BURI");// DEATH OF SIBLING:  DEATH OF HALF SIBLING DEATH OF MOTHER DEATH OF FATHER DEATH OF CHILD
$nonfamfacts = array("CHAN", "HUSB", "WIFE", "CHIL");

$controller = new WT_Controller_Lifespan();
$controller->init();

$zoomfactor = 10;
//if peeps !null then pass new array for zooming

print_header(WT_I18N::translate('Lifespan chart'));

if ($ENABLE_AUTOCOMPLETE) require WT_ROOT.'js/autocomplete.js.htm';
?>
	<script type="text/javascript">
	<!--
	var pastefield;
	function paste_id(value) {
		pastefield.value=value;
	}
	//-->
	</script>

<h2><?php echo WT_I18N::translate('Lifespan chart'), help_link('lifespan_chart'); ?></h2>
<table><tr><td>
<form name="people" action="lifespan.php">

<?php
//This is the box that adds one person at a time.  Not sure if we want to keep this functionality.
		if (!isset($col)) $col = 0;
		?>
	<table>
		<tr><td class="person<?php echo $col; ?>" style="padding: 5px" valign="top">
			<?php echo WT_I18N::translate('Add another person to chart'), help_link('add_person'), '<br />', WT_I18N::translate('Person ID'); ?>
			<input class="pedigree_form" type="text" size="5" id="newpid" name="newpid" />
			<?php print_findindi_link("newpid",""); ?>
			<br />
			<div style="text-align: center"><input type="checkbox" checked="checked" value="yes" name="addFamily"/><?php echo WT_I18N::translate('Include Immediate Family'); ?></div>
			<br />
			<div style="text-align: center"><input type="submit" value="<?php echo WT_I18N::translate('Show'); ?>" /></div>
		</td></tr>
	</table>
	<?php if (count($controller->pids)<11) { ?><br /><a href="timeline.php"><b><?php echo WT_I18N::translate('Show Timeline chart'); ?></b></a><br /><br /><?php } ?>

</form>
<script type="text/javascript">
<!--

var timer;
var offSetNum = 20; // amount timeline moves with each mouse click
var speed;
// method for scrolling timeline around in portal. takes in a string for the direction the timeline is moving "Left" "Right" "Top" "Down"
function startScroll(move)
{
	speed = parseInt(document.buttons.speedMenu.options[document.buttons.speedMenu.selectedIndex].value) * 25; //Sets the speed of the scroll feature
	timer = 1;
	scroll(move);
}
function scroll(move)
{
	if (timer==null) return;  // If timer is not set timeline doesn't scroll'
	timer = setTimeout("scroll('"+move+"')",speed); // Keeps the timeline moving as long as the user holds down the mouse button on one of the direction arrows
	topInnerDiv = document.getElementById("topInner");
	innerDiv = document.getElementById("inner");
	myouterDiv = document.getElementById("outerDiv");

	//compares the direction the timeline is moving and how far it can move in each direction.
	if (move == "left" && ((maxX+topInnerDiv.offsetLeft+350) > (myouterDiv.offsetLeft+myouterDiv.offsetWidth))) {
		left = (innerDiv.offsetLeft - offSetNum)+"px";
		innerDiv.style.left = left;
		topInnerDiv.style.left = left;
	}
	else if (move == "right" && topInnerDiv.offsetLeft < (-10)) {
		right = (innerDiv.offsetLeft + offSetNum)+"px";
		innerDiv.style.left = right;
		topInnerDiv.style.left = right;
	}
	else if (move == "up" && innerDiv.offsetTop > maxY) {
		up = (innerDiv.offsetTop - offSetNum)+"px";
		innerDiv.style.top = up;
	}
	else if (move == "down" && innerDiv.offsetTop < -60) {
		down = (innerDiv.offsetTop + offSetNum)+"px";
		innerDiv.style.top = down;
	}
}
//hopefully this will increase zoom at every press until five presses have been made
//after 5, the control will stop and the minimize will reverse the effects

var numOfIncrease = 0;
var numOfDecrease = 0;
var font = 12;
var zoomfactor = <?php echo $zoomfactor; ?>;

function startZoom(move)
{
	zoom(move);
}

function zoom(move) {
	if (move == "increase" && numOfIncrease < 5) {

		increase = zoomfactor + 10;
		numOfIncrease += 1;

		temp = document.getElementById("inner");

		for (i=0; i<temp.childNodes.length; i++) {

			if (temp.childNodes[i].tagName=="DIV") {
				width = temp.childNodes[i].offsetWidth;
				height = temp.childNodes[i].offsetHeight;
				left = temp.childNodes[i].offsetLeft;
				top = temp.childNodes[i].offsetTop;

				width = width * 1.1;
				height = height * 1.1;
				left = left * 1.1;
				font = font + 0.2;

				if (temp.childNodes[i].offsetTop <= 65) {
					top = top;
				}
				else {
					top = top * 1.2;
				}

				temp.childNodes[i].style.width = width+'px';
				temp.childNodes[i].style.height = height+'px';
				temp.childNodes[i].style.left = left+'px';
				temp.childNodes[i].style.fontSize = font+'pt';
				temp.childNodes[i].style.top = top+'px';
			}
		}
	}
	else if (move == "decrease" && numOfIncrease > 0) {
		decrease = zoomfactor - 10;
		numOfIncrease -= 1;

		for (i=0; i<temp.childNodes.length; i++) {
			if (temp.childNodes[i].tagName=="DIV") {
				width = temp.childNodes[i].offsetWidth;
				height = temp.childNodes[i].offsetHeight;
				left = temp.childNodes[i].offsetLeft;
				top = temp.childNodes[i].offsetTop;

				width = width * 0.9;
				height = height * 0.9;
				left = left * 0.9;
				font = font - 0.2;

				if (temp.childNodes[i].offsetTop <= 65) {
					top = top;
				}
				else {
					top = top * 0.95;
				}

				temp.childNodes[i].style.width = width+'px';
				temp.childNodes[i].style.height = height+'px';
				temp.childNodes[i].style.left = left+'px';
				temp.childNodes[i].style.fontSize = font+'pt';
				temp.childNodes[i].style.top = top+'px';
			}
		}
	}
}
function reset() {
	if (numOfIncrease >= 5) {
	temp = document.getElementById("inner");

		for (i=0; i<temp.childNodes.length; i++) {

			if (temp.childNodes[i].tagName=="DIV") {
				width = temp.childNodes[i].offsetWidth;
				height = temp.childNodes[i].offsetHeight;
				left = temp.childNodes[i].offsetLeft;
				top = temp.childNodes[i].offsetTop;
			}
		}
		numOfIncrease = 0;
		zoomfactor = 10;
	}
	else if (numOfDecrease >= 5) {
		temp = document.getElementById("inner");

		for (i=0; i<temp.childNodes.length; i++) {

			if (temp.childNodes[i].tagName=="DIV") {
				width = temp.childNodes[i].offsetWidth;
				height = temp.childNodes[i].offsetHeight;
				left = temp.childNodes[i].offsetLeft;
				top = temp.childNodes[i].offsetTop;
			}
		}
		numOfIncrease = 0;
		zoomfactor = 10;
	}
}

//method used to stop scrolling
function stopScroll()
{
	if (timer) clearTimeout(timer);
	timer=null;
}

var oldMx = 0;
	var oldMy = 0;
	var movei1 = "";
	var movei2 = "";
	function pandiv() {
		if (movei1=="") {
			oldMx = msX;
			oldMy = msY;
		}
		i = document.getElementById('topInner');
		//alert(i.style.top);
		movei1 = i;
		i = document.getElementById('inner');
		movei2 = i;
		return false;
	}
	function releaseimage() {
		movei1 = "";
		movei2 = "";
		return true;
	}
	// Main function to retrieve mouse x-y pos.s
	function getMouseXY(e) {
		if (IE) { // grab the x-y pos.s if browser is IE
			msX = event.clientX + document.documentElement.scrollLeft;
			msY = event.clientY + document.documentElement.scrollTop;
		} else {  // grab the x-y pos.s if browser is NS
			msX = e.pageX;
			msY = e.pageY;
		}
		// catch possible negative values in NS4
		if (msX < 0) {msX = 0;}
		if (msY < 0) {msY = 0;}
		if (movei1!="") {
		//ileft = parseInt(movei1.style.left);
		//itop = parseInt(movei2.style.top);
		var ileft = movei2.offsetLeft+1;
		var itop = movei2.offsetTop+1;
		ileft = ileft - (oldMx-msX);
		itop = itop - (oldMy-msY);
		movei1.style.left = ileft+"px";
		movei2.style.left = ileft+"px";
		movei2.style.top = itop+"px";
		oldMx = msX;
		oldMy = msY;
		return false;
		}
	}

	var IE = document.all?true:false;
	if (!IE) document.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP)
	document.onmousemove = getMouseXY;
	document.onmouseup = releaseimage;
//-->
</script>
</td><td>
<form name="buttons" action="lifespan.php" method="get">

	<table>
		<tr>
			<td rowspan="2"><?php echo help_link('timeline_control'); ?></td>
			<td align="center"><?php echo WT_I18N::translate('Speed'); ?></td>
				<td align="center"><?php echo WT_I18N::translate('Begin Year'); ?></td>
				<td align="center"><?php echo WT_I18N::translate('End Year'); ?></td>
				<td align="center"><?php echo translate_fact('PLAC'); ?></td>
		</tr>
		<tr>
			<td><select name="speedMenu" size="1">
				<option value="4">1</option>
				<option value="3">2</option>
				<option value="2">3</option>
				<option value="1">4</option>

				</select></td>
			<td><input type="text" name="beginYear" size="5" value="<?php if (isset($beginYear)) echo $beginYear; ?>" /></td>
			<td><input type="text" name="endYear" size="5" value="<?php if (isset($endYear)) echo $endYear; ?>" /></td>
			<td><input type="text" name="place" size="15" value="<?php if (isset($place)) echo $place; ?>"/></td>
			<td><input type="submit" name="search" value="<?php echo WT_I18N::translate('Search'); ?>" /></td>
		<td><input type="button" value="<?php echo WT_I18N::translate('Clear Chart'); ?>" onclick="window.location = 'lifespan.php?clear=1';" /></td>
		</tr>
	</table>
	<?php
	$people = count($controller->people);
	echo "<br /><b>".WT_I18N::plural('%d Individual', '%d Individuals', $people, $people)."</b>";
	?>
</form>
</td></tr></table>
<div dir="ltr" id="outerDiv" class="lifespan_outer">
	<div dir="ltr" id="topInner"  class="lifespan_timeline" onmousedown="pandiv(); return false;">
	<?php $controller->PrintTimeline($controller->timelineMinYear,$controller->timelineMaxYear); ?>
	</div>
		<div id="inner" class="lifespan_people" onmousedown="pandiv(); return false;">
		<?php $maxY = $controller->fillTL($controller->people,$controller->minYear,$controller->YrowLoc); ?>
	</div>
	<!--  Floating div controls START -->
<div dir="ltr" style="position:relative; z-index: 100; filter: alpha(opacity=67); -moz-opacity: 0.67;  opacity: 0.67; width:180px; top: 80px;">
		<table style="margin-left: 20px" dir="ltr" border="0" cellpadding="0">
		<tr>
			<td></td>
			<td colspan="2" align="center"><a href="#" onclick="return false;" onmousedown="startScroll('down')" onmouseup="stopScroll()"><img src="<?php echo $WT_IMAGES["lsuparrow"]; ?>" border="0" alt="" /></a></td>
			<td></td>
		</tr>
		<tr>
			<td><a href="#" onclick="return false;" onmousedown="startScroll('right')" onmouseup="stopScroll()"><img src="<?php echo $WT_IMAGES["lsltarrow"]; ?>" border="0" alt="" /></a></td>
			<td align="center"><!-- <a href="#" onclick="return false;" onmousedown="startZoom('increase')"><img src="<?php echo $WT_IMAGES["zoomin"]; ?>" border="0" alt="" /></a> --></td>
			<td align="center"><!-- <a href="#" onclick="return false;" onmousedown="startZoom('decrease')"><img src="<?php echo $WT_IMAGES["zoomout"]; ?>" border="0" alt="" /></a> --></td>
			<td><a href="#" onclick="return false;" onmousedown="startScroll('left')" onmouseup="stopScroll()"><img src="<?php echo $WT_IMAGES["lsrtarrow"]; ?>" border="0" alt="" /></a></td>
		</tr>
		<tr>
		<td> </td>
		<td colspan="2" align="center"><a href="#" onclick="return false;" onmousedown="startScroll('up')" onmouseup="stopScroll()"><img src="<?php echo $WT_IMAGES["lsdnarrow"]; ?>" border="0" alt="" /></a></td>
	<td> </td>
	</tr>
	</table>
</div>
	<!--  Floating div controls END-->
</div>
<script type="text/javascript">
<!--
var maxY = 80-<?php echo $maxY; ?>; // Sets the boundaries for how far the timeline can move in the up direction
var maxX = <?php if (!isset($maxX)) $maxX = 0; echo $maxX; ?>;  // Sets the boundaries for how far the timeline can move in the left direction

//-->
</script>

<?php print_footer();
