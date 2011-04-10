<?php
/**
 * Extra French language file
 *
 * @package webtrees
 * @subpackage Language
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

return array(
	
	//Overwriting main language files
	'LANGUAGE_LIST_SEPARATOR'		=>	', ',
	'LANGUAGE_LIST_SEPARATOR_LAST'	=>	' et ',
	'My Page'						=>	'Ma page',	
	
	//Hooks management
	'Hooks'							=>	'<em>Hooks</em>',
	'Implements hooks management.'	=>	'Implémente la gestion des Hooks',
	'Hook Function'					=>	'Fonction Hook',
	'Priority (1 is high)'			=>	'Priorité (élevée : 1)',
	'<p>Hooks are predefined functions that can be called and executed from the core code, on a subscription base.</p><p>This page allows you to manage the different identified hooks within the module directory. You can enable (resp. disable) each of them by ticking (resp. unticking) the checkbox in front of each of them. A custom priority can also be defined, in order to execute the different hooks in a specific order (1 being the highest priority).</p><p>By default, the hooks are enabled when first created. If a module is removed, linked hooks will be removed. If a module is disabled, linked hooks will also be. However, if the module is enabled again, hook need to be enabled manually (if required).</p>'
									=>	'<p>Les '.WT_I18N::translate('Hooks').' sont des fonctions prédéfinies qui peuvent être appelées depuis le code principal, sur le principe de l\'abonnement</p><p>Cette page vous permet de gérer les différents '.WT_I18N::translate('Hooks').' identifiés dans le répertoire des modules. Vous pouvez les activer (respectivement désactiver) en cochant (respectivement décochant) la case appropriée. Une priorité personnalisée peut être définie afin d\'exécuter les '.WT_I18N::translate('Hooks').' dans une ordre déterminé (1 pour une priorité élevée).</p><p>Par défaut, les '.WT_I18N::translate('Hooks').' sont activés. Si un module est supprimé, les '.WT_I18N::translate('Hooks').' associés le seront également. Si un module est désactivé, ils le seront aussi. Par contre, activer à nouveau un module n\'activera pas les '.WT_I18N::translate('Hooks').' associés, qui devront l\'être manuellement (si nécessaire).</p>',

	//Central config management
	'Central Perso configuration'	=>	'Configuration des modules Perso',
	'Allows central configuration for Perso modules configuration.'
									=>	'Permet une gestion centralisée de la configuration des modules Perso.',
									
	//Titles
	'Titles'						=>	'Titres',
	'Title prefixes'				=>	'Particules nobiliaires',
	'<p>Set possible aristocratic particules to separate titles from the land they refer to (e.g. Earl <strong>of</strong> Essex). Variants must be separated by the character |.</p><p>An example for this setting is : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (covering some of French, English, German, Dutch, Italian, Spanish, Portuguese, Swedish common particules).</p>'
									=>	'Définit les particule nobiliaires à utiliser pour séparer le titre de la terre associée (par exemple Comte <strong>de</strong> Toulouse). Les variantes doivent être séparées par le caractère |.</p><p>Une valeur possible pour ce paramètre est : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (couvrant les principales particules françaises, anglaises, allemandes, hollandaises, italiennes, espagnoles, portugaises et suédoises).</p>'
	
);

?>