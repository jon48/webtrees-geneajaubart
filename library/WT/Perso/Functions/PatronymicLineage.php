<?php
/**
 * Additional functions for Patronymic Lineages (based on patronymiclineage module)
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Functions_PatronymicLineage {

	private static $_tabPlaces = null;
	private static $_usedIndis = null;

	/**
	 * Print all the lineages for the surname $surname
	 * 
	 * @param string $surname Surname
	 * @param string $legend Legend to display at the top of the lineage
	 * @param int $gedcomid Gedcom ID
	 */
	public static function printLineages($surname, $legend, $gedcomid) {
		global $_tabPlaces, $_usedIndis;

		$indilist = WT_Query_Name::individuals(strtoupper($surname), null, null, false, false, $gedcomid);

		if(count($indilist)==0) {
			echo '<p class="center"><span class="warning">',
			WT_I18N::translate('No individuals with surname %s has been found. Please try another name.', check_NN($surname)),
				'</span></p>';
			return;
		}

		$nbLineages=0;
		if(!$_usedIndis) $_usedIndis = array();
		if(!$_tabPlaces) $_tabPlaces = array();

		//Initialise table and print title
		echo '<table class="list_table">',
			'<tr><td class="list_label">',
		$legend,
			'</td></tr>';

		echo '<tr><td class="list_value_wrap">';

		foreach($indilist as $indi){
			$pid=$indi->getXref();
			//Look if the individual has been already used in a previous lineage
			if(!isset($_usedIndis[$pid])){
				//Find the root of the lineage
				$indiFirst=self::getLineageRootIndividual($indi);
				if($indiFirst){
					$_usedIndis[$indiFirst->getXref()] = true;
					if($indiFirst->canShow()){
						if($nbLineages>0) echo '<hr/>';
						//Reinitialise the place table for the new lineage
						$_tabPlaces=array();
						//Print the lineage of the root individual
						print '<div class="patrolin_tree">';
						//Check if the root individual has brothers and sisters, without parents
						$indiChildFamily = $indiFirst->getPrimaryChildFamily();
						if($indiChildFamily != null){
							foreach($indiChildFamily->getChildren() as $indiFirstFam){
								self::printIndiLineage($indiFirstFam);
							}
						}
						else{
							self::printIndiLineage($indiFirst);
						}
						echo '</div>';
						//Print repartition of the places for this lineage
						if(count($_tabPlaces)>0){
							ksort($_tabPlaces);
							echo '<table class="patrolin_places"><tr><td>';
							echo WT_Perso_Functions_Print::getPlacesCloud($_tabPlaces, true);
							echo '</td></tr></table>';
						}
						$nbLineages+=1;
					}
				}
			}
		}

		echo '</td></tr>';

		echo '<tr><td class="list_label">',
		WT_I18N::translate('%s lineages found', $nbLineages),
			'</td></tr></table>';

		echo  '</table>';
	}

	/**
	 * Print the pratronymic lineage from the individual $indi_root.
	 * This function is used recursively.
	 * Rules that apply are :
	 * 	- If the root individual is a father, display his children except for those holding their mother's surname
	 * 	- If the root individual is a mother, display children only if they hold the same surname and they do not hold the same surname as their father (when known).
	 *  - If the root individual is a mother, indicate other children by ... 
	 * 
	 * @param WT_Individual $indi_root Root individual to start the lineage from
	 */
	private static function printIndiLineage(WT_Individual $indi_root){
		global $_usedIndis, $_tabPlaces;

		if($indi_root){
			$dindi = new WT_Perso_Individual($indi_root);
			$indi_surname=$dindi->getUnprotectedPrimarySurname();
		
			echo '<ul>';
			$spouseFams=$indi_root->getSpouseFamilies();
			//For married individuals
			if($spouseFams && count($spouseFams)>0){
				$numSpouseFamily=1;
				//Print the individual and his spouse if relevant
				foreach($spouseFams as $famId=>$fam){
				//Separate the way to display the individual depending if this is its first marriage or not
					echo '<li>';
					if($numSpouseFamily<2){
						echo WT_Perso_Functions_Print::getIndividualForList($indi_root);
					}
					else{
						echo WT_Perso_Functions_Print::getIndividualForList($indi_root, false);
					}
					//Get individual's spouse
					$spouse=$fam->getSpouse($indi_root);
					//Print the spouse if relevant
					if($spouse){
						$marrdate = WT_I18N::translate('yes');
						$marryear = '';
						echo '&nbsp;<a href="'.$fam->getHtmlUrl().'">';
						if ($fam->getMarriageYear()){
							$marrdate = strip_tags($fam->getMarriageDate()->Display());
							$marryear = $fam->getMarriageYear();
						}
						echo '<span class="details1" title="'.$marrdate.'"><i class="icon-rings"></i>'.$marryear.'</span></a>&nbsp;';
						echo WT_Perso_Functions_Print::getIndividualForList($spouse);
					}
					// Get the children to print
					$children=$fam->getChildren();
					//If the root individual is the mother
					if($indi_root->getSex()=='F'){
						if($children && count($children)>0){
							$nbChildren=count($children);
							$mother_surname=$indi_surname;
							$father_surname = false;
							if($spouse) {
								$dspouse = new WT_Perso_Individual($spouse);
								$father_surname=$dspouse->getUnprotectedPrimarySurname();
							}
							$nbNatural=0;
							foreach($children as $child){
								$dchild = new WT_Perso_Individual($child);
								$child_surname=$dchild->getUnprotectedPrimarySurname();
								//Print only lineages of children with the same surname as their mother (supposing they are natural children)
								if(!$spouse || ($father_surname && $child_surname!=$father_surname)){
									if($child_surname==$mother_surname){
										$nbNatural+=1;
										self::printIndiLineage($child);
									}
								}
							}
							//Do not print other children
							if(($nbChildren-$nbNatural)>0){
								echo '<ul><li><b>&hellip;</b></li></ul>';
							}
						}
					}
					//Else if the root individual is the father
					else{
						$father_surname=$indi_surname;
						$mother_surname = false;
						if($spouse) {
							$dspouse = new WT_Perso_Individual($spouse);
							$mother_surname=$dspouse->getUnprotectedPrimarySurname();
						}
						foreach($children as $child){
							$dchild = new WT_Perso_Individual($child);
							$child_surname=$dchild->getUnprotectedPrimarySurname();
							//Print the natural children of the mother, with a reference to the relevant lineage
							if($child_surname && $child_surname!=$father_surname && $mother_surname && $child_surname==$mother_surname){
								echo '<ul><li>';
								echo WT_Perso_Functions_Print::getIndividualForList($child);
								echo '&nbsp;<a href="module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&surname='.$child_surname.'&ged='.WT_GED_ID.'">('.WT_I18N::translate('Go to %s lineages', $child_surname).')</a></li></ul>';
							}
							//Print the children's lineage
							else{
								self::printIndiLineage($child);
							}
						}
					}
					echo '</li>';
					
					$numSpouseFamily+=1;
				}
			}
			//If no family, juste write the individual
			else{
				echo '<li>';
				echo WT_Perso_Functions_Print::getIndividualForList($indi_root);
				echo '</li>';
			}
			echo '</ul>';
	
			$pid= $indi_root->getXref();
	
			//Get the estimated birth place and put it in the place table
			$place=$dindi->getEstimatedBirthPlace(false);
			if($place && strlen($place) > 0){
				$place=trim($place);
				if(isset($_tabPlaces[$place])){
					$_tabPlaces[$place]+=1;
				}
				else{
					$_tabPlaces[$place]=1;
				}
			}
			
			//Tag the individual as used
			$_usedIndis[$pid]=true;			
		}
	}

	/**
	 * Returns the root individual of a lineage.
	 * Rules that apply are:
	 *  - If a father exists, he is the new root
	 *  - Else, if a mother exists and holds the same surname, she is the new root
	 *  - Else, we have reached the root of the lineage
	 *  - Also, if the root has already been used, do not return it (to avoid duplication of lineages in case of Private context).
	 * 
	 * @param WT_Individual $individual Root Individual, or null, if already used.
	 */
	private static function getLineageRootIndividual(WT_Individual $individual) {
		global $_usedIndis;
		
		$is_first=false;
		$dindi = new WT_Perso_Individual($individual);
		$indi_surname=$dindi->getUnprotectedPrimarySurname();
		while(!$is_first){
			//Get the individual parents family
			$fam=$individual->getPrimaryChildFamily();
			if($fam){
				$husb=$fam->getHusband();
				$wife=$fam->getWife();
				//If the father exists, take him
				if($husb){
					$individual=$husb;
				}
				//If only a mother exists
				else if($wife){
					$dwife = new WT_Perso_Individual($wife);
					$wife_surname=$dwife->getUnprotectedPrimarySurname();
					//Check if the child is a natural child of the mother (based on the surname - Warning : surname must be identical)
					if($wife_surname==$indi_surname){
						$individual=$wife;
					}
					else{
						$is_first=true;
					}
				}
				else{
					$is_first=true;
				}
			}
			else{
				$is_first=true;
			}
		}
		if($_usedIndis && isset($_usedIndis[$individual->getXref()])){
			return null;
		}
		else{
			return $individual;
		}
	}

}

?>