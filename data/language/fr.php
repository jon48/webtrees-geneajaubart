<?php
/**
 * Extra French language file
 *
 * @package webtrees
 * @subpackage Language
 * @author Jonathan Jaubart <dev@jaubart.com>
 */
  
use Fisharebest\Webtrees\I18N;

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
		
	//Central config management
	'Central Perso configuration'	=>	'Configuration des modules Perso',
	'Allows central configuration for Perso modules configuration.'
									=>	'Permet une gestion centralisée de la configuration des modules Perso.',

	//General Module
	'Perso General'					=>	'Perso Général',
	'General items about Perso modules.'
									=>	'Configurations diverses pour les Modules Perso',

	// Extra header
	'Include additional HTML in header'	
									=>	'Étendre l\'HTML dans l\'en-tête',
	'<p>Enable this option to include raw additional HTML in the header of the page.</p>'
									=>	'<p>Activer cette option pour inclure du code HTML additional dans l\'en-tête.</p>',
	'Hide additional header'		=>	'Masquer l\'en-tête additionnel',
	'<p>Select the access level until which the additional header should be displayed. The <em>Hide from everyone</em> should be used to show the header to everybody.</p>'
									=>	'<p>Sélectionner le niveau d\'accès jusqu\'auquel l\'en-tête additionnel doit être affiché. L\'option <em>Masquer à tout le monde</em> devrait être utilisée pour cacher l\'en-tête à tout le monde.</p>',
	'Additional HTML in header'		=>	'Code HTML additionnel pour l\'en-tête',
	'<p>If the option has been enabled, the saved HTML will be inserted in the header.</p><p>In edit mode, the HTML characters might have been transformed to their HTML equivalents (for instance &amp;gt; for &gt;), it is however possible to insert HTML characters, they will be automatically converted to their equivalent values.</p>'
									=>	'<p>Si l\'option est activée, le code HTML sauvegardée sera inséré dans l\'en-tête.</p><p>En mode édition, les caractères HTML peuvent avoir été modifiés par leurs équivalents HTML (par exemple &amp;gt; pour &gt;), il est cependant possible d\'insérer directement des caractères HTML dans la zone de texte, ils seront convertis en leur equivalents.</p>',					
																
	// Extra footer
	'Display French <em>CNIL</em> disclaimer'
									=>	'Afficher le message d\'information CNIL',
	'<em>CNIL</em> reference'		=>	'Numéro d\'autorisation CNIL',
	'This site has been notified to the French National Commission for Data protection (CNIL) and registered under number %s. '
									=>	'Ce site a fait l\'objet d\'une déclaration auprès de la Commission Nationale de l\'Informatique et des Libertés, sous le numéro %s. ',
	'In accordance with the French Data protection Act (<em>Loi Informatique et Libertés</em>) of January 6th, 1978, you have the right to access, modify, rectify and delete personal information that pertains to you. To exercice this right, please contact %s, and provide your name, address and a proof of your identity.'
									=>	'Conformément à la loi "Informatique et Libertés" du 6 janvier 1978, vous disposez du droit d\'accès, de modification, de rectification et de suppression des données personnelles vous concernant. Afin d\'exercer ce droit, veuillez contacter %s en précisant vos nom et adresse et en fournissant une preuve de votre identité.',
	'<p>Enable this option to display an information disclaimer in the footer required by the French <em>CNIL</em> for detaining personal information on users.</p>'
									=>	'<p>Activer cette option permet l\'affichage d\'un message d\'information requis par la Commission Nationale de l\'Informatique et des Libertés (CNIL) pour la détention d\'informations personnelles des utilisateurs.</p>',
	'<p>If the website has been notified to the French <em>CNIL</em>, an authorisation number may have been delivered. Providing this reference will display a message in the footer visible to all users.</p>'
									=>	'<p>Si votre site a été déclaré à la Commission Nationale de l\'Informatique et des Libertés (CNIL), un numéro d\'autorisation a dû vous être délivré. Fournir cette référence affichera un message dans le pied de page, visible par tous les utilisateurs.</p>',
	'Include additional HTML in footer'	
									=>	'Étendre l\'HTML du pied de page',
	'<p>Enable this option to include raw additional HTML in the footer of the page.</p>'
									=>	'<p>Activer cette option pour inclure du code HTML additional dans le pied de page.</p>',
	'Hide additional footer'		=>	'Masquer le pied de page additionnel',
	'<p>Select the access level until which the additional footer should be displayed. The <em>Hide from everyone</em> should be used to show the footer to everybody.</p>'
									=>	'<p>Sélectionner le niveau d\'accès jusqu\'auquel le pied de page additionnel doit être affiché. L\'option <em>Masquer à tout le monde</em> devrait être utilisée pour cacher pied de page à tout le monde.</p>',
	'Additional HTML in footer'		=>	'Code HTML additionnel pour le pied de page',
	'<p>If the option has been enabled, the saved HTML will be inserted in the footer, before the logo.</p><p>In edit mode, the HTML characters might have been transformed to their HTML equivalents (for instance &amp;gt; for &gt;), it is however possible to insert HTML characters, they will be automatically converted to their equivalent values.</p>'
									=>	'<p>Si l\'option est activée, le code HTML sauvegardée sera inséré dans le pied de page, au-dessus du logo.</p><p>En mode édition, les caractères HTML peuvent avoir été modifiés par leurs équivalents HTML (par exemple &amp;gt; pour &gt;), il est cependant possible d\'insérer directement des caractères HTML dans la zone de texte, ils seront convertis en leur equivalents.</p>',					

	
								
	//Perso GeoDispersion
	'Perso Geographical Dispersion' =>	'Perso Répartition Géographique',
	'Geographical Dispersion' 		=>	'Répartition Géographique',
	'Display the geographical dispersion of the root person’s Sosa ancestors.'
									=>	'Affiche la répartition géographique des ancêtres Sosa de l’individu de-cujus',
	'GeoDispersion'					=>	'GéoRépartition',
	'No map'						=>	'Pas de carte',
	'Sosa Geographical dispersion'	=>	'Répartition géographique des ancêtres Sosa',
	'General data'					=>	'Données générales',
	'Data by Generations'			=>	'Données par générations',
	'Generation %s'					=>	'Génération %s',
	'The required dispersion analysis does not exist.'
									=>	'L\'analyse de répartition géographique demandée n\'existe pas.',
	'The Perso Sosa module must be installed and enabled to display this page.'
									=>	'Le module Perso Sosa doit être installé et activé avant de pouvoir afficher cette page.',
	'Choose tree: '					=>	'Choisir un arbre généalogique : ',
	'No place or indication of place structure could be found in your data.'
									=>	'Aucun lieu ou indication de la structure des lieux n\'a pu être trouvé dans vos données.',
	'According to the GEDCOM header, the places within your file follows the structure:'
									=>	'D\'après votre en-tête GEDCOM, la structure des lieux dans votre fichier est la suivante :',
	'Here is an example of your place data:'
									=>	'Voici un exemple de lieu issu de vos données :',
	'Your GEDCOM header does not contain any indication of place structure.'
									=>	'L\'en-tête de votre fichier GEDCOM ne contient aucune indication sur la structure de vos lieux.',
	'Add...'						=>	'Ajouter...',
	'Add a new entry'				=>	'Ajouter une entrée',
	'Level of analysis'				=>	'Subdivision d\'analyse',
	'Map Top level'					=>	'Subdivision supérieure',
	'Use Flags'						=>	'Drapeaux ?',
	'Place Details'					=>	'Détail des lieux',
	'Choose a geographical dispersion analysis:'
									=>	'Choisir une analyse de répartition géographique :',
	'The Perso Sosa module is required for this module to run. Please activate it.'
									=>	'Le module Perso Sosa est requis pour ce module. Veuillez l\'activer.',
	'Other places'					=>	'Autres lieux',
	'Places not found'				=>	'Lieux non trouvés',
	'%d individuals'				=>	'%d individus',
	'The map could not be loaded.'	=>	'La carte n\'a pu être chargée.',
	'Unknown (%s)'					=>	'Inconnu (%s)',
	'No data is available for the general analysis.'
									=>	'Aucune donnée n\'est disponible pour l\'analyse générale.',
	'No data is available for the generations analysis.'
									=>	'Aucune donnée n\'est disponible pour l\'analyse générationnelle.',
	'Interpretation help:'			=> 	'Aide à l\'interprétation',
	'<strong>Generation X (yy %%)</strong>: The percentage indicates the number of found places compared to the total number of ancestors in this generation.'
									=>	'<strong>Génération X (yy %%)</strong> : le pourcentage indique le nombre de lieux trouvés comparé au nombre total d\'ancêtres dans cette génération.',
	'<strong><em>Place</em> or <em>Flag</em> aa (bb %%)</strong>: The first number indicates the total number of ancestors born in this place, the percentage relates this count to the total number of found places. No percentage means it is less than 10%%.'
									=>	'<strong><em>Lieu</em> or <em>Drapeau</em> aa (bb %%)</strong>: le premier nombre indique le nombre total d\'ancêtres nés en ce lieu, le pourcentage compare ce compte au nombre total de lieux trouvés. Pas de pourcentage signifie qu\'il est inférieur à 10%%.',
	'If any, the darker area indicates the number of unknown places within the generation or places outside the analysed area, and its percentage compared to the number of ancestors. No percentage means it is less than 10%%.'
									=>	'Si présente, la zone foncée indique le nombre de lieux inconnus dans la génération ou les lieux en dehors de la zone étudiée, et son pourcentage comparé au nombre d\'ancêtres. Pas de pourcentage signifie qu\'il est inférieur à 10%%.',
	'<strong><em>Place</em> [aa - bb %%]</strong>: The first number indicates the total number of ancestors born in this place, the percentage compares this count to the total number of found places.'
									=>	'<strong><em>Lieu</em> [aa - bb %%]</strong> : le premier nombre indique le nombre total d\'ancêtres nés en ce lieu, le pourcentage compare ce compte au nombre total de lieux trouvés.',
	'Only the %d more frequent places for each generation are displayed.'
									=>	'Seuls les %d lieux les plus fréquents de chaque génération sont affichés.',
	'The Geodispersion analysis entry could not be deleted.'
									=>	'L\'analyse de répartition géographique n\'a pas pu être supprimée.',
	'The Geodispersion analysis entry has been successfully deleted.'
									=>	'L\'analyse de répartition géographique a été supprimée.',
	'An error occured while adding new element.'
									=>	'Une erreur s\'est produite lors de l\'ajout d\'un élément',
	
	//Perso Admin Tasks
	'Administration Tasks (Perso)'	=>	'Tâches d\'administration (Perso)',
	'Manage and run nearly-scheduled administration tasks.'
									=>	'Gère et exécute des tâches d\'administration quasi-planifiées.',
	'The Administration Tasks module must be installed and enabled to display this page.'
									=>	'Le module Tâches d\'administration doit être installé et activé avant de pouvoir afficher cette page.',
	'The administration tasks are meant to be run at a regular interval - or as regularly as possible.'
									=>	'Les tâches d\'administration sont destinées à être exécutées à intervalles réguliers, ou aussi réguliers que possible.',
	'It is sometimes necessary to force the execution of a task.'
									=>	'Il est parfois nécessaire de forcer l\'exécution d\'une tâche.',
	'In order to do so, use the following URL, with the optional parameter <em>%s</em> if you only want to force the execution of one task: '
									=>	'Pour cela, utilisez l\'adresse suivante, avec le paramètre optionnel <em>%s</em>, si vous souhaitez forcer l\'exécution d\'une seule tâche',
	'task_name'						=>	'nom_de_la_tâche',
	'Regenerate token'				=>	'Regénérer le jeton',
	'Task name'						=>	'Nom de la tâche',
	'Last success'					=>	'Dernier succès',
	'Last result'					=>	'Dernier résultat',
	'Frequency (in min.)'			=>	'Fréquence (min.)',
	'Remaining occurrences'			=>	'Occurrences restantes',
	'Is running?'					=>	'En cours ?',
	'Run task'						=>	'Exécuter la tâche',
	'Disabled'						=>	'Désactivé',
	'Failure'						=>	'Échec',
	'Unlimited'						=>	'Permanent',
	'Running'						=>	'En cours...',
	'Not running'					=>	'Arrêté',
	'Run'							=>	'Exécuter',
	'Done'							=>	'Terminé',
	'%s Settings'					=>	'Configuration : %s',
	'no_token_defined'				=>	'aucun_jeton_défini',
	'Healthcheck Email'				=>	'État du système',
	'Health Check Report'			=>	'État du système',
	'Enable healthcheck emails for <em>%s</em>'
									=>	'Activer les rapports d\'état du système pour </em>%s</em>',
	'Health Check Report for the last %d days'
									=>	'État du système - Rapport pour les %d derniers jours',
	'Tree'							=>	'Arbre',
	'Tree %s'						=>	'Arbre %s',
	'A new version of *webtrees* is available: %s. Upgrade as soon as possible.'
									=>	'Une nouvelle version de *webtrees* est disponible : %s. Veuillez mettre à jour dès que possible.',
	'Download it here: %s.'			=>	'Pour la télécharger : %s',
	'Tree statistics'				=>	'Statistiques de l\'arbre',
	'Errors [%d]'					=>	'Erreurs [%d]',
	'Last occurrence'				=>	'Dernière occurrence',		
	'No errors'						=>	'Aucune erreur',
	
		
	//Titles
	'Titles'						=>	'Titres',
	'Title prefixes'				=>	'Particules nobiliaires',
	'<p>Set possible aristocratic particles to separate titles from the land they refer to (e.g. Earl <strong>of</strong> Essex). Variants must be separated by the character |.</p><p>An example for this setting is : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (covering some of French, English, German, Dutch, Italian, Spanish, Portuguese, Swedish common particles).</p>'
									=>	'Définit les particule nobiliaires à utiliser pour séparer le titre de la terre associée (par exemple Comte <strong>de</strong> Toulouse). Les variantes doivent être séparées par le caractère |.</p><p>Une valeur possible pour ce paramètre est : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (couvrant les principales particules françaises, anglaises, allemandes, hollandaises, italiennes, espagnoles, portugaises et suédoises).</p>',

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