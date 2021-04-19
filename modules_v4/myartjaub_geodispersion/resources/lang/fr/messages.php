<?php

/**
 *  MyArtJaub Sosa module
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

use Fisharebest\Webtrees\I18N;

return array (

    'Geographical Dispersion'       =>  'Répartition Géographique',
    'Display the geographical dispersion of the root person’s Sosa ancestors.'
                                    =>  'Affiche la répartition géographique des ancêtres Sosa de l’individu de-cujus',
    'Sosa Geographical dispersion'  =>  'Répartition géographique des ancêtres Sosa',
    'General data'                  =>  'Données générales',
    'Data by Generations'           =>  'Données par générations',
    'Generation %s'                 =>  'Génération %s',
    'The required dispersion analysis does not exist.'
                                    =>  'L\'analyse de répartition géographique demandée n\'existe pas.',
    'According to the GEDCOM header, the places within your file follows the structure: '
                                    =>  'D\'après votre en-tête GEDCOM, la structure des lieux dans votre fichier est la suivante : ',
    'Here is an example of your place data: '
                                    =>  'Voici un exemple de lieu issu de vos données : ',
    'Your GEDCOM header does not contain any indication of place structure.'
                                    =>  'L\'en-tête de votre fichier GEDCOM ne contient aucune indication sur la structure de vos lieux.',
    'Level of analysis'             =>  'Subdivision d\'analyse',
    'Map parent level'              =>  'Subdivision supérieure',
    'Use flags'                     =>  'Drapeaux ?',
    'Top places'                    =>  'Lieux principaux',
    'Top places number'             =>  'Nombre de lieux principaux',
    'Choose a geographical dispersion analysis:'
                                    =>  'Choisir une analyse de répartition géographique :',
    'Other places'                  =>  'Autres lieux',
    'Places not found'              =>  'Lieux non trouvés',
    '%d individuals'                =>  '%d individus',
    'Unknown (%s)'                  =>  'Inconnu (%s)',
    'event' . I18N::PLURAL . 'events'
                                    =>  'événement' . I18N::PLURAL . 'événements',
    'No data is available for the general analysis.'
                                    =>  'Aucune donnée n\'est disponible pour l\'analyse générale.',
    'No data is available for the generations analysis.'
                                    =>  'Aucune donnée n\'est disponible pour l\'analyse générationnelle.',
    'Interpretation help:'          =>  'Aide à l\'interprétation',
    '<strong>Generation X (yy %%)</strong>: The percentage indicates the number of found places compared to the total number of ancestors in this generation.'
                                    =>  '<strong>Génération X (yy %%)</strong> : le pourcentage indique le nombre de lieux trouvés comparé au nombre total d\'ancêtres dans cette génération.',
    '<strong><em>Place</em> or <em>Flag</em> aa (bb %%)</strong>: The first number indicates the total number of ancestors born in this place, the percentage relates this count to the total number of found places. No percentage means it is less than 10%%.'
                                    =>  '<strong><em>Lieu</em> or <em>Drapeau</em> aa (bb %%)</strong>: le premier nombre indique le nombre total d\'ancêtres nés en ce lieu, le pourcentage compare ce compte au nombre total de lieux trouvés. Pas de pourcentage signifie qu\'il est inférieur à 10%%.',
    'If any, the darker area indicates the number of unknown places within the generation or places outside the analysed area, and its percentage compared to the number of ancestors. No percentage means it is less than 10%%.'
                                    =>  'Si présente, la zone foncée indique le nombre de lieux inconnus dans la génération ou les lieux en dehors de la zone étudiée, et son pourcentage comparé au nombre d\'ancêtres. Pas de pourcentage signifie qu\'il est inférieur à 10%%.',
    '<strong><em>Place</em> [aa - bb %%]</strong>: The first number indicates the total number of ancestors born in this place, the percentage compares this count to the total number of found places.'
                                    =>  '<strong><em>Lieu</em> [aa - bb %%]</strong> : le premier nombre indique le nombre total d\'ancêtres nés en ce lieu, le pourcentage compare ce compte au nombre total de lieux trouvés.',
    'Only the %d more frequent places for each generation are displayed.'
                                    =>  'Seuls les %d lieux les plus fréquents de chaque génération sont affichés.',
    'Displays the results on a map.' =>  'Affiche les résultats sur une carte',
    'Edit the geographical dispersion analysis'
                                    =>  'Modifier l\'analyse de répartition géographique',
    'Add a geographical dispersion analysis'
                                    =>  'Ajouter une analyse de répartition géographique',
    'For instance, if the map is intended to represent a country by county analysis, then the map parent level would be “Country”, and the analysis level would be “County”.'
                                    =>  'Par exemple, si la carte représente une analyse d\'un pays par départements, le niveau de carte parent serait “Pays”, et le niveau d\'analyse serait “Département”.',
    'Map outline to be used for the result display.'
                                    =>  'Fond de carte à utiliser pour l\'affichage des résultats',
    'An error occured while adding the geographical dispersion analysis “%s”'
                                    =>  'Une erreur s\'est produite lors de la création de l\'analyse de répartition géographique “%s”',
    'An error occured while editing this analysis:'
                                    =>  'Une erreur s\'est produite lors de la mise à jour de l\'analyse :',
    'An error occured while deleting this analysis:'
                                    =>  'Une erreur s\'est produite lors de la suppression de l\'analyse :',
    'An error occured while updating the geographical dispersion analysis “%s”'
                                    => 'Une erreur s\'est produite lors de la mise à jour de l\'analyse de répartition géographique “%s” :',
    'The geographical dispersion analysis “%s” has been successfully updated'
                                    =>  'L\'analyse de répartition géographique “%s” a été mise à jour avec succès',
    'The geographical dispersion analysis “%s” has been successfully added.'
                                    =>  'L\'analyse de répartition géographique “%s” a été ajoutée avec succès',
    'Change tree'                   =>  'Changer d\'arbre généalogique',
    'Subdivision level of the parent subdivision(s) represented by the map.'
                                    =>  'Niveau de la (des) subdivision(s) parente(s) représentées par la carte',
    'Display the place\'s flag, instead of or in addition to the place name.'
                                    =>  'Affiche le drapeau du lieu, au lieu ou en plus de son nom',
    'Set the number of top places to display in the generation breakdown view.'
                                    =>  'Définit le nombre de lieux principaux à afficher dans l\'analyse par générations.',
    'Description to be given to the geographical dispersion analysis. It will be used as the page title for it.'
                                    =>  'Description à donner à l\'analyse de répartition géographique. Elle sera utilisée comme titre de la page de résultats.',
    'Analysis level'                =>  'Subdivision d\'analyse',
    'Subdivision level used for the analysis.'
                                    =>  'Niveau de la subdivision d\'analyse à utiliser',
    'Display options'               =>  'Options d\'affichage',
    'Use map'                       =>  'Utiliser une carte',
    'Table'                         =>  'Tableau',
    'There is no geographical dispersion analysis to display.'
                                    =>  'Il n\'y a aucune analyse de répartition géographique à afficher.',
    'Error when loading map.'       =>  'Erreur lors du chargement de la carte.',
    'The map could not be loaded.'  =>  'La carte n\'a pas pu être chargée.',
    'No place structure could be determined. Please make sure that at least a place exists.'
                                    =>  'Aucune structure de lieux n\'a pu être déterminée. Assurez-vous qu\'au moins un lieu existe.'

);
