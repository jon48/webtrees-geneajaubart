<?php
/**
 * Extra French language file
 *
 * @package webtrees
 * @subpackage Language
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

return array(
	
	//Overwriting main language files
	//'LANGUAGE_LIST_SEPARATOR'		=>	', ',
	//'LANGUAGE_LIST_SEPARATOR_LAST'	=>	' et ',
	' and '							=>	' et ',
	'My page'						=>	'Ma page',	
	'%j %F %Y'						=> 	'%D %j %F %Y', 

	//Hooks management
	'Hooks'							=>	'<em>Hooks</em>',
	'Implements hooks management.'	=>	'Implémente la gestion des Hooks',
	'Hook Function'					=>	'Fonction Hook',
	'Hook Context'					=>	'Contexte Hook',
	'Priority (1 is high)'			=>	'Priorité (élevée : 1)',
	'<p>Hooks are predefined functions that can be called and executed from the core code, on a subscription base.</p><p>This page allows you to manage the different identified hooks within the module directory. You can enable (resp. disable) each of them by ticking (resp. unticking) the checkbox in front of each of them. A custom priority can also be defined, in order to execute the different hooks in a specific order (1 being the highest priority).</p><p>By default, the hooks are enabled when first created. If a module is removed, linked hooks will be removed. If a module is disabled, linked hooks will also be. However, if the module is enabled again, hook need to be enabled manually (if required).</p>'
									=>	'<p>Les '.WT_I18N::translate('Hooks').' sont des fonctions prédéfinies qui peuvent être appelées depuis le code principal, sur le principe de l\'abonnement</p><p>Cette page vous permet de gérer les différents '.WT_I18N::translate('Hooks').' identifiés dans le répertoire des modules. Vous pouvez les activer (respectivement désactiver) en cochant (respectivement décochant) la case appropriée. Une priorité personnalisée peut être définie afin d\'exécuter les '.WT_I18N::translate('Hooks').' dans une ordre déterminé (1 pour une priorité élevée).</p><p>Par défaut, les '.WT_I18N::translate('Hooks').' sont activés. Si un module est supprimé, les '.WT_I18N::translate('Hooks').' associés le seront également. Si un module est désactivé, ils le seront aussi. Par contre, activer à nouveau un module n\'activera pas les '.WT_I18N::translate('Hooks').' associés, qui devront l\'être manuellement (si nécessaire).</p>',
	'Module Name'					=>	'Nom du module',
		
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

	//Perso certificates module
	'Perso Certificates'			=>	'Perso Actes',
	'Display and edition of certificates linked to sources.'
									=>	'Affichage et édition des actes liés aux sources d\'événements',
	'Certificate'					=>	'Acte',
	'Certificates'					=>	'Actes',
	'Certificates directory'		=>	'Répertoire des actes',
	'This folder will be used to store the certificate files.'
									=>	'Ce répertoire est utilisé pour stocker les images d\'actes.',
	'If you select a different folder, you must also move any certificate files from the existing folder to the new one.'
									=>	'Si vous désirez utiliser un autre répertoire, vous devez également déplacer les images d\'actes du répertoire existant vers le nouveau.',
	'Show certificates'				=>	'Montrer les actes',
	'Define access level required to display certificates in facts sources. By default, nobody can see the certificates.'
									=>	'Définit le niveau d\'accès requis pour afficher les actes dans les sources des événements. Par défaut, personne ne peut voir les actes.',
	'Show non-watermarked certificates'
									=>	'Montrer les actes non-filigranés',
	'Define access level required to see certificate images without any watermark. By default, everybody will see the watermark.'
									=>	'Définit le niveau d\'accès requis pour ne pas afficher de filigrane sur les images d\'actes. Par défaut, tout le monde peut voir le filigrane.',
	'When displayed, the watermark is generated from the name of the repository and of the sources, if they exist. Otherwise, a default text is displayed.'
									=>	'Quand affiché, le filigrane se compose des noms du dépôt d\'archive et de la source, s\'ils existent. Sinon, un texte par défaut est affiché.',
	'Default watermark'				=>	'Texte du filigrane par défaut',
	'Text to be displayed by default if no source has been associated with the certificate.'
									=>	'Texte à afficher par défaut dans le filigrane si aucune source n\'a pu être rattachée à l\'acte.',
	'Watermark font color'			=>	'Couleur de police du filigrane',
	'Font color for the watermark. By default, <span style="color:#4d6df3;">the color (77,109,243)</span> is used.' 
									=>	'Couleur de la police du filigrane. Par défault, <span style="color:#4d6df3;">la couleur (77,109,243)</span> est utilisée.',
	'This parameter must be entered with the format <strong>RR,GG,BB</strong> with <strong>RR</strong>, <strong>GG</strong> and <strong>BB</strong> the respective <span style="color:#ff0000;">red</span>, <span style="color:#00ff00;">green</span> and <span style="color:#0000ff;">blue</span> components as decimal integers (between 0 and 255).'
									=>	'Ce paramètre est de la forme <strong>RR,VV,BB</strong> avec <strong>RR</strong>, <strong>VV</strong> et <strong>BB</strong> les composantes respectivement <span style="color:#ff0000;">rouge</span>, <span style="color:#00ff00;">verte</span> et <span style="color:#0000ff;">bleu</span> sous leur forme décimale (comprises entre 0 et 255).',
	'Watermark minimum font size'	=>	'Taille de police minimale du filigrane',
	'Watermark maximum font size'	=>	'Taille de police maximale du filigrane',
	'This image is protected under copyright law.'
									=>	'Cette image est protégée par les lois sur le copyright.',
	'The certificate file was not found in this family tree'
									=>	'L\'acte n\'a pu être trouvé dans cet arbre.',
	'Missing or private certificate object.'
									=>	'L\'objet acte n\'existe pas ou est privé.',
	'The certificate file does not exist.'
									=>	'Le fichier de l\'acte n\'existe pas',
	'This certificate file is broken and cannot be watermarked.'
									=>	'Cet acte semble être corrompu et le filigrane n\'a pas pu être ajouté',							
	'Individuals linked to this certificate'
									=>	'Individus liés à cet acte',
	'Families linked to this certificate'
									=>	'Familles liés à cet acte',
	'Path to a certificate linked to a source reference.'		
									=>	'Chemin vers un acte lié à la source.',						
	
	//Patronymic Lineages
	'Perso Patronymic Lineages'		=>	'Perso Lignées Patronymiques',
	'Display lineages of people holding the same surname.'
									=>	'Affiche les lignées des individus portant le même nom de famille.',
	'Patronymic Lineages'			=>	'Lignées Patronymiques',
	'Individuals in %s lineages'	=>	'Indididus des lignées %s',
	'Go to the list of individuals with surname %s'	
									=>	'Voir la liste des individus portant le nom %s',
	'No individuals with surname %s has been found. Please try another name.'
									=>	'Aucun individu portant le nom %s n\'a été trouvé. Veuillez essayer un autre patronyme.',						
	'%s lineages found'				=>	'%s lignées trouvées',
	'Go to %s lineages'				=>	'Voir les lignées %s',
	'Informations for individual %s'=>	'Informations sur l\'individu %s',

	// Perso Welcome Block
	'Perso Welcome Block'			=>	'Bloc «Perso Accueil»',
	'The Perso Welcome block welcomes the visitor to the site, allows a quick login to the site, and displays statistics on visits.'
									=>	'Le bloc Perso Accueil acueille le visiteur sur le site, permet une connexion rapide et affiche des statistiques de visites.',
	'Enable Piwik Statistics'		=>	'Activer les statistiques Piwik',
	'Enable Piwik statistics, in order to display the number of visits on the related site.'
									=>	'Activer les statistiques afin d\'afficher le nombre de visites sur le site associé',
	'Piwik URL'						=>	'Adresse URL de Piwik',
	'URL of the Piwik API to request. This is usually the <b>index.php</b> at the root of the Piwik installation'
									=>	'Adresse URL de l\'API Piwik à requêter. Il s\'agit normalement du <b>index.php</b> à la racine de l\'installation Piwik.',
	'Piwik Token'					=>	'Token Piwik',
	'Token provided by the Piwik installation, under the API tab.'
									=>	'Token fourni par l\'installation Piwik, sous la section API.',
	'Piwik Site ID'					=>	'ID Piwik du site',		
	'Piwik Site ID of the website to follow.'	
									=>	'ID Piwik du site à suivre.',	
	'Retrieving Piwik statistics...'
									=>	'Récupération des statistiques Piwik...',
	'No statistics could be retrieved from Piwik.'
									=>	'Les statistiques Piwik ne peuvent être récupérées.',
	'<span class="hit-counter">%1$s</span> visits since the beginning of %2$s<br/>(<span class="hit-counter">%3$s</span> today)'
									=>	'<span class="hit-counter">%1$s</span> visiteurs depuis le début de l\'année %2$s<br/>dont <span class="hit-counter">%3$s</span> aujourd\'hui',
	'Continue'						=>	'Continuer',
	
	// Perso Sosa
	'Perso Sosa'					=>	'Perso Sosa',
	'Calculate and display Sosa ancestors of the root person.'
									=>	'Calcule et affiche les ancêtres Sosa du de-cujus.',
	'Sosa'							=>	'Sosa',
	'Root individual for <em>%s</em>'
									=>	'Individu de-cujus pour <em>%s</em>',
	'Root individual'				=>	'Individu de-cujus',
	'Define the Sosa root individual for the specified GEDCOM.'
									=>	'Définit l\'individu de-cujus pour le GEDCOM spécifié.',
	'Compute'						=>	'Calculer',
	'Computed'						=>	'Calculé',
	'Recompute'						=>	'Recalculer',
	'Compute all Sosas for <em>%s</em>'
									=>	'Calculer les Sosas pour <em>%s</em>',
	'Sosas computation'				=>	'Calcul des ancêtres Sosas',
	'Compute all Sosas'				=>	'Calculer tous les ancêtres Sosas',
	'Compute all Sosa ancestors for the specified GEDCOM, from the set Sosa root individual.'
									=>	'Calculer les ancêtres Sosa pour le GEDCOM spécifié à partir de l\'individu de-cujus défini.',									
	'Error'							=>	'Erreur',
	'Success'						=>	'Succès',
	'Individual is not a Sosa'		=>	'L\'individu n\'est pas un ancêtre Sosa.',
	'Non existing individual'		=>	'L\'individu n\'existe pas.',	
	'You are not allowed to perform this operation.'
									=>	'Vous n\'êtes pas autorisé à executer cette opération.',																
	'(G%s)'							=>	'(G%s)',
	'Complete Sosas'				=>	'Compléter les Sosas',
	'Sosa Statistics'				=>	'Statistiques Sosa',
	'Sosa statistics'				=>	'Statistiques Sosa',
	'No Sosa root individual has been defined.'
									=>	'Aucun individu n\'a été défini comme de-cujus.', 
	'%s\'s ancestors'				=>	'Ancêtres de %s',
	'General statistics'			=>	'Statistiques générales',
	'Number of ancestors'			=>	'Nombre total d\'ancêtres',
	'Number of different ancestors'	=>	'Nombre d\'ancêtres différents',
	'%% of ancestors in the base'	=>	'Proportion de Sosas dans la base',
	'Mean generation time'			=>	'Durée moyenne d\'une génération',
	'Statistics by generations'		=>	'Statistiques par générations',						
	'Theoretical'					=>	'Théoriques',
	'Known'							=>	'Connus',
	'Losses G-1'					=>	'Pertes G-1',
	'Total known'					=>	'Cumul Connus',
	'Different'						=>	'Différents',
	'Total Different'				=>	'Cumul Différents',
	'Pedigree collapse'				=>	'Implexe',
	'<strong>G%d</strong>'			=>	'<strong>G%d</strong>',
	'Theoretical number of ancestors in generation G.'
									=>	'Nombre théorique d\'ancêtres à la génération G.',							
	'Number of ancestors found in generation G. A same individual can be counted several times.'
									=>	'Nombre d\'ancêtres connus à la génération G. Un même individu peut être compté plusieurs fois.',	
	'The <strong>%%</strong> column is the ratio of found ancestors in generation G compared to the theoretical number.'
									=>	'La colonne <string>%%</strong> indique la proportion d\'ancêtres connus à la génération G par rapport au nombre théorique.',										
	'Number of ancestors not found in generation G, but whose children are known in generation G-1.'
									=>	'Nombre d\'ancêtres non trouvés à la génération G, mais dont les enfants sont connys à la génération G-1.',	
	'The <strong>%%</strong> column is the ratio of not found ancestors in generation G amongst the theoretical ancestors in this generation whose children are known in generation G-1. This is an indicator of the completion of a generation relative to the completion of the previous generation.'
									=>	'La colonne <string>%%</strong> indique la proportion d\'ancêtres non trouvés à la génération G par rapport au nombre d\'ancêtres potentiels dont les enfants sont connus à la génération G-1. Ce pourcentage est un indicateur de l\'achèvement d\'une génération relativement à celui de la génération précédente.',	
	'Cumulative number of ancestors found up to generation G. A same individual can be counted  several times.'
									=>	'Nombre cumulé d\'ancêtres trouvés jusqu\'à la génération G. Un même individu peut être compté plusieurs fois.',	
	'The <strong>%%</strong> column is the ratio of cumulative found ancestors in generation G compared to the cumulative theoretical number.'
									=>	'La colonne <string>%%</strong> indique la proportion d\'ancêtres connus jusqu\'à la génération G par rapport au nombre cumulé théorique.',
	'Number of distinct ancestors found in generation G. A same individual is counted only once.'
									=>	'Nombre d\'ancêtres différents trouvés à la génération G. Un même individu n\'est compté qu\'une seule fois.',									
	'The <strong>%%</strong> column displays the ratio of distinct individuals compared to the number of ancestors found in generation G.'
									=>	'La colonne <string>%%</strong> affiche la proportion d\'individus distincts par rapport au nombre d\'ancêtres trouvés à la génération G.',
	'Number of cumulative distinct ancestors found up to generation G. A same individual is counted only once in the total number, even if present in different generations.'
									=>	'Nombre cumulé d\'ancêtres différents trouvés jusqu\'à la génération G. Un même individu n\'est compté qu\'une seule fois, même s\'il apparait dans plusieurs générations.',
	'Pedigree collapse at generation G.'
									=>	'Implexe à la génération G.',										
	'Pedigree collapse is a measure of the real number of ancestors of a person compared to its theorical number. The higher this number is, the more marriages between related persons have happened. Extreme examples of high pedigree collapse are royal families for which this number can be as high as nearly 90%% (Alfonso XII of Spain).'
									=>	'L\'implexe est une mesure du nombre réel d\'ancêtres d\'une personne par rapport au nombre théorique. Plus ce pourcentage est grand, plus il y a eu de mariages entre personnes apparentées. Les familles royales constituent des exemples extrêmes d\'implexe, certains atteignant des niveaux de 90%%, comme le roi Alphonse XII d\'Espagne.',							
	'Generation-equivalent: %s generations'
									=>	'Équivalent-génération : %s générations',
	'Missing Ancestors'				=>	'Ancêtres manquants',
	'Choose generation'				=>	'Choisir une génération',
	'Generation %d'					=>	'Génération %d',
	'Previous generation'			=>	'Génération précédente',
	'Next generation'				=>	'Génération suivante',
	'Number of different missing ancestors: %s'
									=>	'Nombre d\'ancêtres manquants différents: %s',
	'%s hidden'						=>	'%s cachés',
	'Generation complete at %s'		=>	'Génération complète à %s',
	'Potential %s'					=>	'Potentiel %s',
	'No ancestors are missing for this generation. Generation complete at %s.'
									=>	'Il ne manque aucun ancêtre pour cette génération. Génération complète à %s.',
	'No ancestor has been found for generation %d'
									=>	'Aucun ancêtre n\'a été trouvé pour la génération %d',
	'No individual has been found for generation %d'
									=>	'Aucun individu n\'a été trouvé pour la génération %d',
	'No family has been found for generation %d'
									=>	'Aucune famille n\'a été trouvée pour la génération %d',
	'The list could not be displayed. Reasons might be:'
									=>	'La liste ne peut être affichée. La raison de ceci peut être :',
	'No Sosa root individual has been defined.'
									=>	'L\'individu de-cujus n\'a pas été défini.',
	'The Sosa ancestors have not been computed yet.'
									=>	'La liste des ancêtres Sosa n\'a pas été encore calculée.',
	'No generation were found.'		=>	'Le nombre de générations est insuffisant',	
	'Sosa Ancestors'				=>	'Ancêtres Sosa',	
	'Number of Sosa ancestors: %1$s known / %2$s theoretical (%3$s)'
									=>	'Nombre d\'ancêtres Sosa : %1$s connus / %2$s théoriques (%3$s)',	
	'An error occurred while retrieving data...'
									=>	'Une erreur s\'est produite en récupérant les données...',															

	//Perso IsSourced
	'Sourced events'				=>	'Événements documentés',
	'Sourced individual'			=>	'Individu documenté',
	'Sourced birth'					=>	'Naissance documentée',
	'Sourced death'					=>	'Décès documenté',
	'Sourced marriage'				=>	'Mariage documenté',
	'Indicate if events related to an record are sourced.'
									=>	'Indique si les événements liés à un enregistrement sont documentés',
	'%s not found'					=>	'%s non trouvé(e)',
	'%s not precise'				=>	'%s imprécis(e)',
	'%s not sourced'				=>	'%s non documenté(e)',
	'%s sourced'					=>	'%s documenté(e)',
	'%s sourced with a certificate'	=>	'%s documenté(e) avec un acte',
	'%s sourced with exact certificate'	
									=>	'%s documenté(e) avec l\'acte exact',	
									
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
	'webtrees Site Administrator'	=>	'Administrateur webtrees',
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