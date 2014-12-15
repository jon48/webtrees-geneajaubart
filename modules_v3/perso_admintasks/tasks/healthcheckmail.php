<?php
/**
 * Task to send an email summarising the healthcheck of the site
 *
 * @package webtrees
 * @subpackage subpackage
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

use WT\Auth;
use WT\User;

class healthcheckmail_WT_Perso_Admin_Task extends WT_Perso_Admin_ConfigurableTask {
	
	//Extend class WT_Perso_Admin_Task
	public function getPrettyName(){
		return WT_I18N::translate('Healthcheck Email');
	}
	
	//Extend class WT_Perso_Admin_Task
	public function getDefaultFrequency(){
		return 10080;  // = 1 week = 7 * 24 * 60 min
	}
	
	//Extend class WT_Perso_Admin_ConfigurableTask
	public function getConfigTabContent(){
		global $controller;
		
		$html = '<table class="gm_edit_config"><tr><td><dl>';
		foreach(WT_Tree::getAll() as $tree){
			if(Auth::isManager($tree)){
				$isTreeEnabled = $tree->getPreference('PAT_'.$this->getName().'_ENABLED');
				if(is_null($isTreeEnabled)) $isTreeEnabled = true;
				$html .= '<dt>'.WT_I18N::translate('Enable healthcheck emails for <em>%s</em>', $tree->tree_title).'</dt>'.
					'<dd>'.WT_Perso_Functions_Edit::edit_field_yes_no_inline('gedcom_setting-'.$this->getName().'-ENABLED-'.$tree->tree_id, $isTreeEnabled, $controller, 'perso_admintasks').'</dd>';
			}
		}
		$html .= '</dl></td></tr></table></div>';
		
		return $html;
	}
	
	//Extend class WT_Perso_Admin_Task
	protected function executeSteps(){		
		$res = false;		
		
		// Get the number of days to take into account, either last 7 days or since last check
		$interval_sincelast = 0;
		if($this->_lastupdated){
			$tmpInt = $this->_lastupdated->diff(new DateTime("now"), true);
			$interval_sincelast = ( $tmpInt->days * 24  + $tmpInt->h ) * 60 + $tmpInt->i;
		}
		
		$interval = max($this->_frequency, $interval_sincelast);
		$nbdays = ceil($interval / (24 * 60));

		// Check for updates
		$latest_version_txt=fetch_latest_version();
		if (preg_match('/^[0-9.]+\|[0-9.]+\|/', $latest_version_txt)) {
			list($latest_version, $earliest_version, $download_url)=explode('|', $latest_version_txt);
		}
				
		// Users statistics
		$warnusers = 0;
		$nverusers = 0;
		$applusers = 0;
		foreach(User::all() as $user) {
			if (((date("U") - (int)$user->getPreference('reg_timestamp')) > 604800) && !$user->getPreference('verified')) {
				$warnusers++;
			}
			if (!$user->getPreference('verified_by_admin') && $user->getPreference('verified')) {
				$nverusers++;
			}
			if (!$user->getPreference('verified')) {
				$applusers++;
			}
		}

		// Tree specifics checks
		$one_tree_done = false;
		foreach(WT_Tree::getAll() as $tree){
			$isTreeEnabled = $tree->getPreference('PAT_'.$this->getName().'_ENABLED');
			if((is_null($isTreeEnabled) || $isTreeEnabled) && $webmaster = User::find($tree->getPreference('WEBMASTER_USER_ID'))){
				WT_I18N::init($webmaster->getPreference('language'));
				
				$subject = WT_I18N::translate('Health Check Report').' - '.WT_I18N::translate('Tree %s', $tree->tree_title);
				$message = 
					WT_I18N::translate('Health Check Report for the last %d days', $nbdays).WT_Mail::EOL.WT_Mail::EOL.
					WT_I18N::translate('Tree %s', $tree->tree_title).WT_Mail::EOL.
					'=========================================='.WT_Mail::EOL.WT_Mail::EOL;
				
				// News
				$message_version = '';
				if($latest_version && version_compare(WT_VERSION, $latest_version)<0){
					$message_version = WT_I18N::translate('News').WT_Mail::EOL.
							'-------------'.WT_Mail::EOL.
							WT_I18N::translate('A new version of *webtrees* is available: %s. Upgrade as soon as possible.', $latest_version).WT_Mail::EOL.
							WT_I18N::translate('Download it here: %s.', $download_url).WT_Mail::EOL.WT_Mail::EOL;
				}
				$message .= $message_version;
				
				// Statistics users
				$message_users = WT_I18N::translate('Users').WT_Mail::EOL.
						'-------------'.WT_Mail::EOL.
						WT_SERVER_NAME.WT_SCRIPT_PATH.'admin_users.php'.WT_Mail::EOL.
						WT_I18N::translate('Total number of users')."\t\t".User::count().WT_Mail::EOL.
						WT_I18N::translate('Not verified by the user')."\t\t".$applusers.WT_Mail::EOL.
						WT_I18N::translate('Not approved by an administrator')."\t".$nverusers.WT_Mail::EOL.
						WT_Mail::EOL;
				$message  .= $message_users;
								
				// Statistics tree:				
				$stats = new WT_Stats($tree->tree_name);
				$sql = 'SELECT ged_type AS type, COUNT(change_id) AS chgcount FROM wt_change'.
					' JOIN ('.
						' SELECT "indi" AS ged_type, i_id AS ged_id, i_file AS ged_file FROM wt_individuals'.
						' UNION SELECT "fam" AS ged_type, f_id AS ged_id, f_file AS ged_file FROM wt_families'.
						' UNION SELECT "sour" AS ged_type, s_id AS ged_id, s_file AS ged_file FROM wt_sources'.
						' UNION SELECT "media" AS ged_type, m_id AS ged_id, m_file AS ged_file FROM wt_media'.
						' UNION SELECT LOWER(o_type) AS ged_type, o_id AS ged_id, o_file AS ged_file FROM wt_other'.
					') AS gedrecords ON (xref = ged_id AND gedcom_id = ged_file)'.
					' WHERE change_time >= DATE_ADD( NOW(), INTERVAL -'.$nbdays.' DAY)'.
					' AND status = ? AND gedcom_id = ?'.
					' GROUP BY ged_type';
				$changes = WT_DB::prepare($sql)->execute(array('accepted', $tree->tree_id))->fetchAssoc();
								
				$message_gedcom = WT_I18N::translate('Tree statistics').WT_Mail::EOL.
					'-------------'.WT_Mail::EOL.
					sprintf('%-25s', WT_I18N::translate('Records'))."\t".sprintf('%15s', WT_I18N::translate('Count'))."\t".sprintf('%15s', WT_I18N::translate('Changes')).WT_Mail::EOL.
					sprintf('%-25s', WT_I18N::translate('Individuals'))."\t".sprintf('%15s', $stats->totalIndividuals())."\t".sprintf('%15s', (isset($changes['indi']) ? $changes['indi'] : 0)).WT_Mail::EOL.
					sprintf('%-25s', WT_I18N::translate('Families'))."\t".sprintf('%15s', $stats->totalFamilies())."\t".sprintf('%15s', (isset($changes['fam']) ? $changes['fam'] : 0)).WT_Mail::EOL.
					sprintf('%-25s', WT_I18N::translate('Sources'))."\t".sprintf('%15s', $stats->totalSources())."\t".sprintf('%15s', (isset($changes['sour']) ? $changes['sour'] : 0)).WT_Mail::EOL.
					sprintf('%-25s', WT_I18N::translate('Repositories'))."\t".sprintf('%15s', $stats->totalRepositories())."\t".sprintf('%15s', (isset($changes['repo']) ? $changes['repo'] : 0)).WT_Mail::EOL.
					sprintf('%-25s', WT_I18N::translate('Media objects'))."\t".sprintf('%15s', $stats->totalMedia())."\t".sprintf('%15s', (isset($changes['media']) ? $changes['media'] : 0)).WT_Mail::EOL.
					sprintf('%-25s', WT_I18N::translate('Notes'))."\t".sprintf('%15s', $stats->totalNotes())."\t".sprintf('%15s', (isset($changes['note']) ? $changes['note'] : 0)).WT_Mail::EOL.
					WT_Mail::EOL;				
				$message .= $message_gedcom;
								
				//Errors
				$sql = 'SELECT SQL_CACHE log_message, gedcom_id, COUNT(log_id) as nblogs, MAX(log_time) as lastoccurred'.
							' FROM `##log`'.
							' WHERE log_type = ? AND (gedcom_id = ? OR ISNULL(gedcom_id))'.
							' AND log_time >= DATE_ADD( NOW(), INTERVAL -'.$nbdays.' DAY)'.
							' GROUP BY log_message, gedcom_id'.
							' ORDER BY lastoccurred DESC';
				$errors=WT_DB::prepare($sql)->execute(array('error', $tree->tree_id))->fetchAll();	
				$nb_errors = 0;		
				$tmp_message = '';
				$nb_char_count_title = strlen(WT_I18N::translate('Count'));
				$nb_char_type = max(strlen(WT_I18N::translate('Type')), strlen(WT_I18N::translate('Site')), strlen(WT_I18N::translate('Tree')));
				foreach ($errors as $error) {
					$tmp_message .= sprintf('%'.$nb_char_count_title.'d', $error->nblogs)."\t";
					$tmp_message .= sprintf('%'.$nb_char_type.'s', is_null($error->gedcom_id) ? WT_I18N::translate('Site') : WT_I18N::translate('Tree'));
					$tmp_message .= "\t".sprintf('%20s', $error->lastoccurred)."\t";
					$tmp_message .= str_replace("\n", "\n\t\t\t\t\t\t", $error->log_message).WT_Mail::EOL;
					$nb_errors += $error->nblogs;
				}
				if($nb_errors > 0){
					$message .= WT_I18N::translate('Errors [%d]', $nb_errors).WT_Mail::EOL.
						'-------------'.WT_Mail::EOL.
						WT_SERVER_NAME.WT_SCRIPT_PATH.'admin_site_logs.php'.WT_Mail::EOL.
						WT_I18N::translate('Count')."\t".
						sprintf('%-'.$nb_char_type.'s', WT_I18N::translate('Type'))."\t".
						sprintf('%-20s', WT_I18N::translate('Last occurrence'))."\t".
						WT_I18N::translate('Error').WT_Mail::EOL.
						str_repeat('-', $nb_char_count_title)."\t".str_repeat('-', $nb_char_type)."\t".str_repeat('-', 20)."\t".str_repeat('-', strlen(WT_I18N::translate('Error'))).WT_Mail::EOL.
						$tmp_message.WT_Mail::EOL;
				}
				else{
					$message .= WT_I18N::translate('No errors', $nb_errors).WT_Mail::EOL.WT_Mail::EOL;
				}
				
				$tmpres = true;
				if($webmaster->getPreference('contactmethod') !== 'messaging' 
						&& $webmaster->getPreference('contactmethod') !== 'none') {
					$tmpres = WT_Mail::systemMessage($tree, $webmaster, $subject, $message);
				}		
				$res = $tmpres && (!$one_tree_done || $one_tree_done && $res);
				$one_tree_done = true;
			}
		}
		
		return $res;
	}
	
}


?>