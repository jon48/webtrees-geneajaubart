<?php

/**
 *  MyArtJaub Sosa module
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

use Fisharebest\Webtrees\I18N;

return array (

    'Calculate and display Sosa ancestors of the root person.'
                                    =>  'Calcule et affiche les ancêtres Sosa du de-cujus.',
    'Sosa'                          =>  'Sosa',
    'SOSA' . I18N::CONTEXT . 'S'    =>  'S',
    'Sosa Configuration'            =>  'Configuration module Sosa',
    'Root individual'               =>  'Individu de-cujus',
    'ancestor' . I18N::PLURAL . 'ancestors'
                                    =>  'ancêtre' . I18N::PLURAL . 'ancêtres',
    'For user'                      =>  'Pour l’utilisateur',
    'compute'                       =>  'calculer',
    'Number of generations to compute'
                                    =>  'Nombre de générations à calculer',
    'Computing...'                  =>  'En cours...',
    'Error'                         =>  'Erreur',
    'Success'                       =>  'Succès',
    'Generation %s'                 =>  'Génération %s',
    'Complete Sosas'                =>  'Compléter les Sosas',
    'Sosa Statistics'               =>  'Statistiques Sosa',
    'No Sosa root individual has been defined.'
                                    =>  'L’individu de-cujus n’a pas été défini.',
    'Sosa %1$s - Generation %2$s'   =>  'Sosa %1$s - Génération %2$s',
    'Sosa %1$s - Generation %2$s (%3$s)'
                                    =>  'Sosa %1$s - Génération %2$s (%3$s)',
    '+ another Sosa' . I18N::PLURAL . '+ %s other Sosas'
                                    =>  '+ un autre Sosa' . I18N::PLURAL . '+ %s autres Sosas',
    'This individual'               =>  'Cet individu',
    'the root individual'           =>  'l’individu de-cujus',
    '%s is the root individual'     =>  '%s est l’individu de-cujus',
    '%1$s is an ancestor of %2$s:'  =>  '%1$s est un ancêtre de %2$s :',
    '%1$s is %3$s times an ancestor of %2$s:'
                                    =>  '%1$s est %3$s fois un ancêtre de %2$s :',
    'Father’s side'                =>  'Côté paternel',
    'Mother’s side'                =>  'Côté maternel',
    'General statistics'            =>  'Statistiques générales',
    'Number of ancestors'           =>  'Nombre total d’ancêtres',
    'Number of different ancestors' =>  'Nombre d’ancêtres différents',
    '%% of ancestors in the base'   =>  'Proportion de Sosas dans la base',
    'Generation mean duration'      =>  'Durée moyenne d’une génération',
    'Statistics by generations'     =>  'Statistiques par générations',
    'Count'                         =>  'Nombre',
    'Theoretical'                   =>  'Théoriques',
    'Known'                         =>  'Connus',
    'Losses G-1'                    =>  'Pertes G-1',
    'Cumulative known ancestors'    =>  'Cumul ancêtres connus',
    'Different ancestors'           =>  'Ancêtres différents',
    'Cumulative'                    =>  'Cumul',
    'Pedigree collapse'             =>  'Implexe',
    'Minimum'                       =>  'Minimal',
    'Shrinkage'                     =>  'Contraction',
    '<strong>G%d</strong>'          =>  '<strong>G%d</strong>',
    'Sosa %s'                       =>  'Sosa %s',
    '%%'                            =>  '%%',
    '-'                             =>  '-',
    '%1$s <> %2$s'                  =>  '%1$s <> %2$s',
    '%s: %s'                        =>  '%s : %s',
    'Known Sosa ancestors’ family dispersion'
                                    =>  'Répartition familiale des ancêtres Sosas connus',
    'Shared'                        =>  'Partagés',
    'Hover the column headers to display some help on their meaning.'
                                    =>  'Passer le curseur sur les en-têtes de colonnes pour obtenir de l’aide sur leur signification.',
    'Theoretical number of ancestors in generation G.'
                                    =>  'Nombre théorique d’ancêtres à la génération G.',
    'Number of ancestors found in generation G. A same individual can be counted several times.'
                                    =>  'Nombre d’ancêtres connus à la génération G. Un même individu peut être compté plusieurs fois.',
    'Ratio of found ancestors in generation G compared to the theoretical number.'
                                    =>  'Proportion d’ancêtres connus à la génération G par rapport au nombre théorique.',
    'Number of ancestors not found in generation G, but whose children are known in generation G-1.'
                                    =>  'Nombre d’ancêtres non trouvés à la génération G, mais dont les enfants sont connus à la génération G-1.',
    'Ratio of not found ancestors in generation G amongst the theoretical ancestors in this generation whose children are known in generation G-1. This is an indicator of the completion of a generation relative to the completion of the previous generation.'
                                    =>  'Proportion d’ancêtres non trouvés à la génération G par rapport au nombre d’ancêtres potentiels dont les enfants sont connus à la génération G-1. Ce pourcentage est un indicateur de l’achèvement d’une génération relativement à celui de la génération précédente.',
    'Cumulative number of ancestors found up to generation G. A same individual can be counted  several times.'
                                    =>  'Nombre cumulé d’ancêtres trouvés jusqu’à la génération G. Un même individu peut être compté plusieurs fois.',
    'Ratio of cumulative found ancestors in generation G compared to the cumulative theoretical number.'
                                    =>  'Proportion d’ancêtres connus jusqu’à la génération G par rapport au nombre cumulé théorique.',
    'Number of distinct ancestors found in generation G. A same individual is counted only once.'
                                    =>  'Nombre d’ancêtres différents trouvés à la génération G. Un même individu n’est compté qu’une seule fois.',
    'Ratio of distinct individuals compared to the number of ancestors found in generation G.'
                                    =>  'Proportion d’individus distincts par rapport au nombre d’ancêtres trouvés à la génération G.',
    'Number of cumulative distinct ancestors found up to generation G. A same individual is counted only once in the total number, even if present in different generations.'
                                    =>  'Nombre cumulé d’ancêtres différents trouvés jusqu’à la génération G. Un même individu n’est compté qu’une seule fois, même s’il apparait dans plusieurs générations.',
    'Pedigree collapse at generation G. Pedigree collapse is a measure of the real number of ancestors of a person compared to its theorical number. The higher this number is, the more marriages between related persons have happened. Extreme examples of high pedigree collapse are royal families for which this number can be as high as nearly 90%% (Alfonso XII of Spain).'
                                    =>  'Implexe à la génération G. L’implexe est une mesure du nombre réel d’ancêtres d’une personne par rapport au nombre théorique. Plus ce pourcentage est élevé, plus il y a eu de mariages entre personnes apparentées. Les familles royales constituent des exemples extrêmes d’implexe, certains atteignant des niveaux de 90%%, comme le roi Alphonse XII d’Espagne.',
    'Multiple computation methods can be found; a non-conventional approach taking into account missing ancestors and cross-generation collapse is being used.'
                                    =>  'Plusieurs méthodes de calcul sont possibles; une approche non conventionnelle est utilisée, prenant en compte les ancêtres manquants et la contraction inter-générationnelle.',
    'Minimum pedigree collapse at generation G. The minimum pedigree collapse is a computation of the lowest possible value of the root ancestors collapse at generation G, based on the known ancestors, assuming all further missing ancestors are distinct, taking into account cross-generation collapse.'
                                    =>  'Implexe minimal à la génération G. L’implexe minimal est un calcul de la plus petite valeur possible de l’implexe des ancêtres racines à la génération G, basé sur les ancêtres connus, prenant en compte la contraction inter-générationnelle, et supposant que les ancêtres manquants ultérieurs sont différents.',
    'Pedigree cross-generation shrinkage at generation G. The shinkrage is a measure of the pedigree collapse due to cross-generation marriages. The higher this number is, the more marriages between related persons at different Sosa generations have happened.'
                                    =>  'Contraction inter-génerationnelle à la génération G. La contraction est une mesure de la la réduction de l’arbre due aux mariages inter-générationels. Plus ce pourcentage est élevé, plus il y a eu de mariages entre personnes apparentées appartenant à des générations Sosa différentes.',
    'Mean generation depth and standard deviation'
                                    =>  'Profondeur généalogique moyenne et écart-type',
    'Mean generation depth'         =>  'Profondeur généalogique moyenne',
    'Mean generation depth: %s'     =>  'Profondeur généalogique moyenne : %s',
    'Standard deviation: %s'        =>  'Écart-type : %s',
    '%s generation' . I18N::PLURAL . '%s generations'
                                    =>  '%s génération' . I18N::PLURAL . '%s générations',
    'Mean generation depth by grandparents'
                                    =>  'Profondeur généalogique moyenne par grand-parent',
    'Most duplicated root Sosa ancestors'
                                    =>  'Ancêtres Sosas racines les plus fréquents',
    '%s times'                      =>  '%s fois',
    'Missing Ancestors'             =>  'Ancêtres manquants',
    'Choose generation'             =>  'Choisir une génération',
    'Previous generation'           =>  'Génération précédente',
    'Next generation'               =>  'Génération suivante',
    'Number of different missing ancestors: %s'
                                    =>  'Nombre d’ancêtres manquants différents: %s',
    '%s hidden'                     =>  '%s cachés',
    'Generation complete at %s'     =>  'Génération complète à %s',
    'Potential %s'                  =>  'Potentiel %s',
    'No ancestors are missing for this generation. Generation complete at %s.'
                                    =>  'Il ne manque aucun ancêtre pour cette génération. Génération complète à %s.',
    'No ancestors could be found for this generation.'
                                    =>  'Aucun ancêtre n’a été trouvé pour cette génération.',
    'No ancestors can be displayed for this generation: %s hidden ancestors.'
                                    =>  'Aucun ancêtre ne peut être affiché pour cette génération : %s ancêtres cachés.',
    'No families have be found for this generation.'
                                    =>  'Aucune famille n’a été trouvée pour cette génération.',
    'No families can be displayed for this generation: %s hidden families.'
                                    =>  'Aucune famille ne peut être affichée pour cette génération : %s familles cachées.',
    'Sosa Ancestors'                =>  'Ancêtres Sosa',
    'Number of Sosa ancestors: %1$s known / %2$s theoretical (%3$s)'
                                    =>  'Nombre d’ancêtres Sosa : %1$s connus / %2$s théoriques (%3$s)',
    'Show missing only fathers.'    =>  'Afficher seulement les pères manquants.',
    'Show missing only mothers.'    =>  'Afficher seulement les mères manquantes.',
    'Show missing both parents.'    =>  'Afficher les deux parents manquants.',
    'Both'                          =>  'Tous les deux',
    'Show only known birth dates.'  =>  'Afficher seulement les dates de naissance connues.',
    'Show only known birth places.' =>  'Afficher seulement les lieux de naissance connus.',
    'Known birth date'              =>  'Date de naissance connue',
    'Known birth place'             =>  'Lieu de naissance connu',
    'Computing Sosa ancestors&hellip;'
                                    =>  'Calcul des ancêtres Sosa&hellip;',
    'Sosa ancestors computation completed successfully.'
                                    =>  'Le calcul des ancêtres Sosa s’est terminé avec succès.',
    'The root individual has been updated.'
                                    =>  'L’individu de-cujus a été mis à jour.',
    'The root individual could not be updated.'
                                    =>  'L’individu de-cujus n’a pu être mis à jour.',
    'Sosa ancestors places by generation'
                                    =>  'Lieux des ancêtres Sosas by génération',
    'The attached module could not be found.'
                                    =>  'Le module associé n’a pas été trouvé',
    'An error occurred while retrieving data.'
                                    =>  'Une erreur s’est produite en récupérant les données.',
    'An error occurred while computing Sosa ancestors.'
                                    =>  'Une erreur s’est produite lors du calcul des ancêtres Sosa.',
    'You do not have permission to modify the user.'
                                    =>  'Vous n’avez pas la permission de modifier l’utilisateur.',



);
