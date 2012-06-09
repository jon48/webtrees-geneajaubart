/*
 * Javascript to include before the Sosa module menu to compute Sosas from an individual
 *
 * @package webtrees
 * @subpackage Perso
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

jQuery(document).ready(function() {
   $('#loadingarea').empty().html('<i class="icon-loading-small"></i>');
   $('#loadingarea').load('module.php?mod=perso_sosa&mod_action=computesosaindi&pid=' + sosa_pid, 
		function(){
      		 var date = new Date();
      		 var curDate = null;
      		 
      		do { curDate = new Date(); } 
      		while(curDate-date < 2000);

	 		window.opener.location='individual.php?pid='+sosa_pid+'&show_changes=yes';
	 		window.close();
   		}	   
   );
 });