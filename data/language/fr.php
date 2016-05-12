<?php
/**
 * Extra French language file
 *
 * @package webtrees
 * @subpackage Language
 * @author Jonathan Jaubart <dev@jaubart.com>
 */
  
// WT_SCRIPT_NAME is defined in each script that the user is permitted to load.
if (!defined('WT_SCRIPT_NAME')) {
	http_response_code(403);

	return;
}

return array(
	
	//Overwriting main language files
	//'LANGUAGE_LIST_SEPARATOR'		=>	', ',
	//'LANGUAGE_LIST_SEPARATOR_LAST'	=>	' et ',
	' and '							=>	' et ',
	'My page'						=>	'Ma page',	
	'%j %F %Y'						=> 	'%D %j %F %Y', 
			
	//Perso Translation tool
	'Perso Translation Tool'		=>	'Outil de traduction Perso',
	'Manage webtrees translation.'	=>	'Gère les traductions de webtrees',
	'Translations status'			=>	'Statut des traductions',
	'Missing translations'			=>	'Traductions manquantes',
	'Message Id'					=>	'Id du message',
	'Removed personal translations'	=>	'Traductions personnelles supprimées',
	'Please make sure to <a href="%s">generate the PO template</a> beforehand.'
									=>	'Assurez-vous d\'avoir <a href="%s">généré le fichier POT</a> auparavant'
									
);

?>