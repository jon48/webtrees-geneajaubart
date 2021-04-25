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

    'Geographical dispersion'       =>  'Répartition géographique',
    'Perform and display geographical dispersion analyses.'
                                    =>  'Calcule et affiche les analyses de répartition géographique.',
    'Global data'                   =>  'Données globales',
    'Detailed data'                 =>  'Données détaillées',
    'Choose a geographical dispersion analysis:'
                                    =>  'Choisir une analyse de répartition géographique :',
    'Places found'                  =>  'Lieux trouvés',
    'Other places'                  =>  'Autres lieux',
    'Places not found'              =>  'Lieux non trouvés',
    'event' . I18N::PLURAL . 'events'
                                    =>  'événement' . I18N::PLURAL . 'événements',
    'Interpretation help:'          =>  'Aide à l’interprétation',
    '<strong><em>Category</em> X (yy %%)</strong>: The percentage indicates the number of found places compared to the total number of %s in this category.'
                                    =>  '<strong><em>Génération</em> X (yy %%)</strong> : le pourcentage indique le nombre de lieux trouvés comparé au nombre total de/d’ %s dans cette génération.',
    '<strong><em>Place</em> [aa - bb %%]</strong>: The first number indicates the total number of %s in this place, the percentage compares this count to the total number of found places.'
                                    =>  '<strong><em>Lieu</em> [aa - bb %%]</strong>: le premier nombre indique le nombre total de/d’ %s en ce lieu, le pourcentage compare ce compte au nombre total de lieux trouvés.',
    '<strong><em>Place</em> or <em>Flag</em> aa (bb %%)</strong>: The first number indicates the total number of %s in this place, the percentage relates this count to the total number of found places. No percentage means it is less than 10%%.'
                                    =>  '<strong><em>Lieu</em> ou <em>Drapeau</em> aa (bb %%)</strong>: le premier nombre indique le nombre total de/d’ %s en ce lieu, le pourcentage compare ce compte au nombre total de lieux trouvés.  Pas de pourcentage signifie qu’il est inférieur à 10%%.',
    'If any, the darker area indicates the number of unknown places within the category or places outside the analysed area, and its percentage compared to the number of known places. No percentage means it is less than 10%%.'
                                    =>  'Si présente, la zone foncée indique le nombre de lieux inconnus dans la catégorie ou les lieux en dehors de la zone étudiée, et son pourcentage comparé au nombre d’ancêtres. Pas de pourcentage signifie qu’il est inférieur à 10%%.',
    'Only the %s most frequent places for each category are displayed.'
                                    =>  'Seuls les %s lieux les plus fréquents de chaque catégorie sont affichés.',
    'Add a geographical dispersion analysis view'
                                    =>  'Ajouter une visualisation d’analyse de répartition géographique',

    'Add a view'                    =>  'Ajouter une visualisation',
    'Add a map'                     =>  'Ajouter une carte',
    'Add a map configuration'       =>  'Ajouter une configuration de carte',
    'Edit the view'                 =>  'Modifier la visualisation',
    'Edit the geographical dispersion analysis view - %s'
                                    =>  'Modifier la visualisation d’analyse de répartition géographique - %s',
    'Edit the map configuration'    =>  'Modifier la configuration de la carte',
    'Enable'                        =>  'Activer',
    'Disable'                       =>  'Désactiver',
    'Global view'                   =>  'Vue globale',
    'Detailed view'                 =>  'Vue détaillée',
     'GEODISPERSION' . I18N::CONTEXT . 'View'
                                    =>  'Visualisation',
    'GEODISPERSION' . I18N::CONTEXT . 'Map'
                                    =>  'Carte',
    'GEODISPERSION' . I18N::CONTEXT . 'Table'
                                    =>  'Tableau',
    'Analysis'                      =>  'Analyse',
    'Depth'                         =>  'Profondeur',
    'Place level to analyze'        =>  'Niveau de la subdivision d’analyse',
    'Top places to display'         =>  'Nombre de lieux principaux à afficher',
    'Mapper'                        =>  'Moteur de correspondance',
    'Place mapper'                  =>  'Moteur de correspondance des lieux',
    'Mapping configuration'         =>  'Configuration de la correspondance',
    'Mapping property'              =>  'Propriété de correspondance',
    'Map shape mapping property'    =>  'Propriété de correspondance pour les formes de la carte',
    '%s, for instance'              =>  '%s, par exemple',
    'Color'                         =>  'Couleur',
    'Default shape color'           =>  'Couleur par défaut des formes',
    'Shape stroke color'            =>  'Couleur des contours des formes',
    'Maximum value shape color'     =>  'Couleur pour le total maximal',
    'Shape hover color'             =>  'Couleur de surlignage des formes',
    'Parent places'                 =>  'Lieux d’ordre supérieur',
    'The type of geographical dispersion analysis view to be added.'
                                    =>  'Le type de visualisation d’analyse de répartition géographique à ajouter',
    'Description to be given to the geographical dispersion analysis view. It will be used as the page title for it.'
                                    =>  'Description de la visualisation d’analyse de répartition géographique. Elle sera utilisée comme titre de la page de résultats.',
    'The geographical dispersion analysis to be used for this view.'
                                    =>  'Analyse de répartition géographique à utiliser pour cette visualisation.',
    'The map to use for the view.'  =>  'La carte à utiliser pour la visualisation',
    'The depth level within the place hierarchy to be used by the analysis, 1 being the top level.'
                                    =>  'Le niveau de profondeur dans la hièrarchie des lieux à utiliser par l’analyse, 1 étant le niveau le plus élevé',
    'The depth of a geographical dispersion analysis view defines the level within the place hierarchy used by the analysis, starting with the top level.'
                                    =>  'La profondeur d’une analyse de répartition géographique indique le niveau de la hiérarchie des lieux à utiliser pour l’analyse, en commençant par le niveau supérieur.',
    'In this tree, the available levels are for instance: '
                                    =>  'Dans cet arbre, les niveaux disponibles sont par exemple : ',
    'The number of places to display in the detailed view, by descending count.'
                                    =>  'Le nombre de lieux principaux à afficher dans la vue détaillée, par total décroissant',
    'The engine to use to map places in the tree to shapes on the map.'
                                    =>  'Le moteur à utiliser pour faire correspondre les lieux de l’arbre avec les formes de la carte',
    'The shape property to be used by the place mapper to identify places. For the best results, the property value should be unique.'
                                    =>  'La propriété de la forme à utiliser par le moteur de correspondance pour identifier le lieu. Pour de meilleurs résultats, la valeur de la propriété devrait être unique.',
    'Default color to fill map shapes.'
                                    =>  'La couleur par défaut pour les formes de la carte',
    'Color for map shapes’ stroke.'  =>  'La couleur pour les contours des formes de la carte',
    'Color to fill map shapes with the highest count.'
                                    =>  'La couleur pour les formes ayant la valeur totale la plus importante',
    'Color to fill map shapes when hovering it.'
                                    =>  'La couleur pour surligner les formes lors d’un survol',
    'Filter on places belonging to those parent places.'
                                    =>  'Filtrer sur les lieux appartenant à ces lieux d’ordre supérieur.',
    'The geographical dispersion analysis view has been successfully added.'
                                    =>  'La visualisation d’analyse de répartition géographique a été ajoutée avec succès',
    'The geographical dispersion analysis view has been successfully updated.'
                                    =>  'La visualisation d’analyse de répartition géographique a été mise à jour avec succès',
    'The geographical dispersion analysis view has been successfully deleted.'
                                    =>  'La visualisation d’analyse de répartition géographique a été supprimée avec succès',
    'The map configuration has been successfully added.'
                                    =>  'La configuration de carte a été ajoutée avec succès',
    'The map configuration has been successfully updated.'
                                    =>  'La configuration de carte a été mise à jour avec succès',
    'The map configuration has been successfully deleted.'
                                    =>  'La configuration de carte a été supprimée avec succès',
    'The requested dispersion analysis does not exist.'
                                    =>  'L’analyse de répartition géographique demandée n’existe pas.',
    'The view with ID “%s” does not exist.'
                                    =>  'La visualisation avec l’identifiant « %s » n’existe pas.',
    'The geographical dispersion analysis view could not be found.'
                                    =>  'La visualisation d’analyse de répartition géographique n’existe pas.',
    'The map configuration with ID “%d” does not exist.'
                                    =>  'La configuration de carte avec l’identifiant « %s » n’existe pas.',
    'The parameters for the new view are not valid.'
                                    =>  'Les paramètres de la nouvelle visualisation ne sont pas valides.',
    'The map configuration could not be found.'
                                    =>  'La configuration de carte n’existe pas.',
    'The configuration for the place mapper could not be found.'
                                    =>  'La configuration du moteur de correspondance n’existe pas.',
    'The map could not be found.'   =>  'La carte n’existe pas.',
    'The parameters for view with ID “%s” are not valid.'
                                    =>  'Les paramètres de la visualisation « %s » ne sont pas valides.',
    'The parameters for the map configuration are not valid.'
                                    =>  'Les paramètres de configuration de la carte ne sont pas valides.',
    'An error occured while adding the geographical dispersion analysis view.'
                                    =>  'Une erreur s’est produire lors de l’ajout de la visualisation d’analyse de répartition géographique.',
    'An error occured while updating the geographical dispersion analysis view.'
                                    =>  'Une erreur s’est produire lors de la mise à jour de la visualisation d’analyse de répartition géographique.',
    'An error occured while deleting the geographical dispersion analysis view.'
                                    =>  'Une erreur s’est produire lors de la suppression de la visualisation d’analyse de répartition géographique.',
    'An error occured while adding a new map configuration.'
                                    =>  'Une erreur s’est produire lors de l’ajout de la configuration de carte.',
    'An error occured while updating the map configuration.'
                                    =>  'Une erreur s’est produire lors de la mise à jour de la configuration de carte.',
    'An error occured while deleting the map configuration.'
                                    =>  'Une erreur s’est produire lors de la suppression de la configuration de carte.',
    'Change tree'                   =>  'Changer d’arbre généalogique',
    'All events places by century'  =>  'Lieux de tous les événements par siècle',
    'All events places by event type'
                                    =>  'Lieux de tous les événements par type d’événement',
    '%s century'                    =>  '%s siècle',
    'Mapping on place coordinates'  =>  'Correspondance sur les coordonnées des lieux',
    'Mapping on place name'         =>  'Correspondance sur les noms des lieux',
    'Mapping on place name with filter'
                                    =>  'Correspondance sur les noms des lieux, avec filtre',
    'The attached module could not be found.'
                                    =>  'Le module associé n\'a pas été trouvé',
    'There is no geographical dispersion analysis to display.'
                                    =>  'Il n’y a aucune analyse de répartition géographique à afficher.',
    'An error occurred while retrieving data.'
                                    =>  'Une erreur s\'est produite en récupérant les données.',
    'The tab could not be loaded.'  =>  'L’onglet n’a pas pu être chargé.',
    'The map could not be loaded.'  =>  'La carte n’a pas pu être chargée.',
    '<strong title="%1$s">%2$s</strong> [%3$s - %4$s]'
                                    =>  '<strong title="%1$s">%2$s</strong> [%3$s - %4$s]',
    '%1$d (%2$s)'                   =>  '%1$d (%2$s)',

);
