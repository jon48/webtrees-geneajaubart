<?php

/**
 * MyArtJaub Patronymic Lineages module
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

use Fisharebest\Localization\Translation;

return array (

    'Patronymic Lineages'           =>  'Lignées Patronymiques',
    'Display lineages of people holding the same surname.'
                                    =>  'Affiche les lignées des individus portant le même nom de famille.',
    'Individuals in %s lineages'    =>  'Individus des lignées %s',
    'Go to the list of individuals with surname %s'
                                    =>  'Voir la liste des individus portant le nom %s',
    'No individuals with surname %s has been found. Please try another name.'
                                    =>  'Aucun individu portant le nom %s n’a été trouvé. Veuillez essayer un autre patronyme.',
    '%s lineage found' . Translation::PLURAL_SEPARATOR . '%s lineages found'
                                    =>  '%s lignée trouvée' . Translation::PLURAL_SEPARATOR . '%s lignées trouvées',
    'Go to %s lineages'             =>  'Voir les lignées %s',
    'The attached module could not be found.'
                                    =>  'Le module associé n’a pas été trouvé',
    'There is no module to handle individual lists.'
                                    =>  'Aucun module n’a été trouvé pour afficher les listes d’individus.'
);
