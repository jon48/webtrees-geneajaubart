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
	'Hook Context'					=>	'Contexte Hook',
	'Priority (1 is high)'			=>	'Priorité (élevée : 1)',
	'<p>Hooks are predefined functions that can be called and executed from the core code, on a subscription base.</p><p>This page allows you to manage the different identified hooks within the module directory. You can enable (resp. disable) each of them by ticking (resp. unticking) the checkbox in front of each of them. A custom priority can also be defined, in order to execute the different hooks in a specific order (1 being the highest priority).</p><p>By default, the hooks are enabled when first created. If a module is removed, linked hooks will be removed. If a module is disabled, linked hooks will also be. However, if the module is enabled again, hook need to be enabled manually (if required).</p>'
									=>	'<p>Les '.WT_I18N::translate('Hooks').' sont des fonctions prédéfinies qui peuvent être appelées depuis le code principal, sur le principe de l\'abonnement</p><p>Cette page vous permet de gérer les différents '.WT_I18N::translate('Hooks').' identifiés dans le répertoire des modules. Vous pouvez les activer (respectivement désactiver) en cochant (respectivement décochant) la case appropriée. Une priorité personnalisée peut être définie afin d\'exécuter les '.WT_I18N::translate('Hooks').' dans une ordre déterminé (1 pour une priorité élevée).</p><p>Par défaut, les '.WT_I18N::translate('Hooks').' sont activés. Si un module est supprimé, les '.WT_I18N::translate('Hooks').' associés le seront également. Si un module est désactivé, ils le seront aussi. Par contre, activer à nouveau un module n\'activera pas les '.WT_I18N::translate('Hooks').' associés, qui devront l\'être manuellement (si nécessaire).</p>',

	//Central config management
	'Central Perso configuration'	=>	'Configuration des modules Perso',
	'Allows central configuration for Perso modules configuration.'
									=>	'Permet une gestion centralisée de la configuration des modules Perso.',
									
	// Extra footer
	'Display French <em>CNIL</em> message'
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
	'<p>Enable this option to include raw additional HTML in the footer of the page. This HTML will only be displayed to non-admin users.</p>'
									=>	'<p>Activer cette option pour inclure du code HTML additional dans le pied de page. Ce code sera visible seulement par les utilisateurs non-administrateurs.</p>',
	'Additional HTML in footer'		=>	'Code HTML additionnel pour le pied de page',
	'<p>If the option has been enabled, the saved HTML will be inserted in the footer, before the logo.</p><p>In edit mode, the HTML characters might have been transformed to their HTML equivalents (for instance &amp;gt; for &gt;), it is however possible to insert HTML characters, they will be automatically converted to their equivalent values.</p>'
									=>	'<p>Si l\'option est activée, le code HTML sauvegardée sera inséré dans le pied de page, au-dessus du logo.</p><p>En mode édition, les caractères HTML peuvent avoir été modifiés par leurs équivalents HTML (par exemple &amp;gt; pour &gt;), il est cependant possible d\'insérer directement des caractères HTML dans la zone de texte, ils seront convertis en leur equivalents.</p>',					

	//Perso certificates module
	'Perso Certificates'			=>	'Perso Actes',
	'Display and edition of certificates linked to sources.'
									=>	'Affichage et édition des actes liés aux sources d\'événements',
	'Certificate'					=>	'Acte',
	'Certificates'					=>	'Actes',
	'View details about this certificate'
									=>	'Voir les détails de cet acte',
	'Certificates directory'		=>	'Répertoire des actes',
	'The certificates directory is used to create URLs for your certificates. You will access the certificates by using URL of the form %2$s, if the certificate directory is %1$s.'
									=>	'Le Répertoire des actes est utilisée pour créer les adresses URL de vos actes. Vous accédez ainsi aux actes avec une URL du type %2$s, si le Répertoire des actes est %1$s',
	'The certificates firewall changes the location of the certificates directory from the public directory %1$s to a private directory such as %2$s.  This allows webtrees to apply privacy filtering to certificates.'
									=>	'Le pare-feu des actes transforme le Répertoire des actes public %1$s en un répertoire privé tel que %2$s. Cela permet d\appliquer des filtres d\'accès privés aux actes.',
	'The certificates directory %s must exist, and the webserver must have read and write access to it.'
									=>	'Le Répertoire des actes %s doit exister, et le serveur web doit y avoir les droits de lecture et d\'écriture.',
	'The certificates directory is shared by all family trees.'
									=>	'Le Répertoire des actes est partagé par tous les arbres.',
	'Show certificates'				=>	'Montrer les actes',
	'Define access level required to display certificates in facts sources. By default, nobody can see the certificates.'
									=>	'Définit le niveau d\'accès requis pour afficher les actes dans les sources des événements. Par défaut, personne ne peut voir les actes.',
	'Certificates firewall root directory'
									=>	'Répertoire racine du pare-feu des actes',
	'Directory in which the protected certificates directory can be created.  When this field is empty, the <b>%s</b> directory will be used.'
									=>	'Répertoire dans lequel le répertoire des actes peut être créé de manière protégée. Par défaut, le répertoire <b>%s</b> est utilisé.',
	'Show non-watermarked certificates'
									=>	'Montrer les actes non-filigranés',
	'Define access level required to see certificate images without any watermark. By default, everybody will see the watermark.'
									=>	'Définit le niveau d\'accès requis pour ne pas afficher de filigrane sur ls images d\'actes. Par défaut, tout le monde peut voir le filigrane.',
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
	'The certificate reference was not found.'
									=>	'La référence de l\'acte n\'a pas été trouvée.',
	'The certificate file does not exist.'
									=>	'Le fichier de l\'acte n\'existe pas.',	
	'You are not allowed to access this certificate.'
									=>	'Vous n\'êtes pas autorisé à afficher cet acte.',
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
	'Perso Welcome Block'					=>	'Bloc «Perso Accueil»',
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
	'<span class="hit-counter">%1$s</span> visits since the beginning of %2$s<br/>(<span class="hit-counter">%3$s</span> today)'
									=>	'<span class="hit-counter">%1$s</span> visiteurs depuis le début de l\'année %2$s<br/>dont <span class="hit-counter">%3$s</span> aujourd\'hui',
									
	//Titles
	'Titles'						=>	'Titres',
	'Title prefixes'				=>	'Particules nobiliaires',
	'<p>Set possible aristocratic particules to separate titles from the land they refer to (e.g. Earl <strong>of</strong> Essex). Variants must be separated by the character |.</p><p>An example for this setting is : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (covering some of French, English, German, Dutch, Italian, Spanish, Portuguese, Swedish common particules).</p>'
									=>	'Définit les particule nobiliaires à utiliser pour séparer le titre de la terre associée (par exemple Comte <strong>de</strong> Toulouse). Les variantes doivent être séparées par le caractère |.</p><p>Une valeur possible pour ce paramètre est : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (couvrant les principales particules françaises, anglaises, allemandes, hollandaises, italiennes, espagnoles, portugaises et suédoises).</p>'
									
									
);

?>