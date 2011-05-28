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
	protected $dindi;

	/**
	 * Constructor for the decorator
	 *
	 * @param WT_Controller_Individual $ctrlIndividual_in The Individual Controller to extend
	 */
	public function __construct(WT_Controller_Individual  $ctrlIndividual_in){
		$this->ctrlIndividual = $ctrlIndividual_in;
		$this->dindi = new WT_Perso_Person($this->ctrlIndividual->indi);
	}

	/**
	 * Print the different titles of an individual in a smart way
	 *
	 */
	public function print_titles(){
		$tab=$this->dindi->getTitles();
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
	
	/**
	 * Print individual header extensions.
	 * Use hooks h_extend_top_center and h_extend_top_right
	 *
	 */
	public function print_extensions_header(){
		$hook_extend_top_center = new WT_Perso_Hook('h_extend_top_center');
		$hook_extend_top_right = new WT_Perso_Hook('h_extend_top_right');
		$hook_extend_top_center = $hook_extend_top_center->execute($this->ctrlIndividual);
		$hook_extend_top_right = $hook_extend_top_right->execute($this->ctrlIndividual);
		
		echo '<td id="indi_top_center">';
		if(count($hook_extend_top_center)>0){
			echo implode('', $hook_extend_top_center);
		}
		else{
			echo '&nbsp;';
		}
		echo '</td>';
		echo '<td id="indi_top_right">';
		if(count($hook_extend_top_right)>0){
			echo implode('', $hook_extend_top_right);
		}
		else{
			echo '&nbsp;';
		}
		echo '</td>';
	}

}

?>