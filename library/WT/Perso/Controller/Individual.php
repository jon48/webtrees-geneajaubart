<?php
/**
 * Decorator class to extend native Individual controller.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Controller_Individual {

	protected $ctrlIndividual;

	/**
	 * Constructor for the decorator
	 *
	 * @param WT_Controller_Individual $ctrlIndividual_in The Individual Controller to extend
	 */
	public function __construct(WT_Controller_Individual  $ctrlIndividual_in){
		$this->ctrlIndividual = $ctrlIndividual_in;
	}

	/**
	 * Print the different titles of an individual in a smart way
	 *
	 */
	function print_titles(){
		$pindi = new WT_Perso_Person($this->ctrlIndividual->indi);
		$tab=$pindi->getTitles();
		$countTitles = count($tab);
		if($countTitles>0){
			echo '<div id="indi_titles"><dl><dt class="label">'.WT_I18N::translate('Titles').'</dt>';
			foreach($tab as $title=>$props){
				echo '<dd class="field">'.$title. ' ';
				echo WT_I18N::make_list($props);
				echo '</dd>';
			}
			echo  '</dl></div>';
		}
	}

}

?>