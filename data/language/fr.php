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
		
	//Central config management
	'Central Perso configuration'	=>	'Configuration des modules Perso',
	'Allows central configuration for Perso modules configuration.'
									=>	'Permet une gestion centralisée de la configuration des modules Perso.',
	
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