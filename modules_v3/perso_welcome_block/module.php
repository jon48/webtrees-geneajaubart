<?php
/**
 * Class for Perso Welcome Block
 *
 * @package webtrees
 * @subpackage Perso
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class perso_welcome_block_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Perso Welcome Block');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('The Perso Welcome block welcomes the visitor to the site, allows a quick login to the site, and displays statistics on visits.');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'getpiwikstats':
				$this->getPiwikStats();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}
	
	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $ctype, $controller;
		
		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		$controller
			->addInlineJavaScript('
				  jQuery("#perso-new_passwd").hide();
				  jQuery("#perso-passwd_click").click(function()
				  {
					jQuery("#perso-new_passwd").slideToggle(100);
				  });
			');
		
		// Welcome section - gedcom title, date, statistics - based on gedcom_block
		$indi_xref=$controller->getSignificantIndividual()->getXref();
		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		$title='<span dir="auto">'.get_gedcom_setting(WT_GED_ID, 'title').'</span>';
		
		$piwik_enabled=get_block_setting($block_id, 'piwik_enabled', false);
		
		$content = '<table><tr>';
		$content .= '<td><a href="pedigree.php?rootid='.$indi_xref.'&amp;ged='.WT_GEDURL.'"><i class="icon-pedigree"></i><br>'.WT_I18N::translate('Default chart').'</a></td>';
		$content .= '<td><a href="individual.php?pid='.$indi_xref.'&amp;ged='.WT_GEDURL.'"><i class="icon-indis"></i><br>'.WT_I18N::translate('Default individual').'</a></td>';
		if (get_site_setting('USE_REGISTRATION_MODULE') && WT_USER_ID==false) {
			$content .= '<td><a href="'.WT_LOGIN_URL.'?action=register"><i class="icon-user_add"></i><br>'.WT_I18N::translate('Request new user account').'</a></td>';
		}
		$content .= '</tr>';
		$content .= '</table>';
		
		$content .= '<div class="center">';		
		if ($piwik_enabled){
			$controller->addInlineJavaScript('$("#piwik_stats").load("module.php?mod=perso_welcome_block&mod_action=getpiwikstats&block_id='.$block_id.'");');		
			$content .= '<div id="piwik_stats"><i class="icon-loading-small"></i>&nbsp;'.WT_I18N::translate('Retrieving Piwik statistics...').'</div>';
		}
		$content .=  '</div>';
		
		$content .= '<hr />';
		
		// Login section - based on login_block
		if (WT_USER_ID) {
			$content .= '<div class="center"><form method="post" action="index.php?logout=1" name="logoutform" onsubmit="return true;">';
			$content .= '<br><a href="edituser.php" class="name2">'.WT_I18N::translate('Logged in as ').' ('.WT_USER_NAME.')</a><br><br>';

			$content .= '<input type="submit" value="'.WT_I18N::translate('Logout').'">';

			$content .= '<br><br></form></div>';
		} else {
			$content .= '<div id="perso-login-box">
							<form id="perso-login-form" name="perso-login-form" method="post" action="'.WT_LOGIN_URL.'" onsubmit="t = new Date(); this.usertime.value=t.getFullYear()+\'-\'+(t.getMonth()+1)+\'-\'+t.getDate()+\' \'+t.getHours()+\':\'+t.getMinutes()+\':\'+t.getSeconds(); return true;">
							<input type="hidden" name="action" value="login">
							<input type="hidden" name="url" value="index.php">
							<input type="hidden" name="ged" value="'; if (isset($ged)) $content.= htmlspecialchars($ged); else $content.= htmlentities(WT_GEDCOM); $content.= '">
							<input type="hidden" name="pid" value="'; if (isset($pid)) $content.= htmlspecialchars($pid); $content.= '">
							<input type="hidden" name="usertime" value="">';
			$content.= '<div>
							<label for="perso-username">'. WT_I18N::translate('Username').
								'<input type="text" id="perso-username" name="username" class="formField">
							</label>
							</div>
							<div>
								<label for="perso-password">'. WT_I18N::translate('Password').
									'<input type="password" id="perso-password" name="password" class="formField">
								</label>
							</div>
							<div>
								<input type="submit" value="'. WT_I18N::translate('Login'). '">
							</div>
							<div>
								<a href="#" id="perso-passwd_click">'. WT_I18N::translate('Request new password').'</a>
							</div>';
			if (get_site_setting('USE_REGISTRATION_MODULE')) {
				$content.= '<div><a href="'.WT_LOGIN_URL.'?action=register">'. WT_I18N::translate('Request new user account').'</a></div>';
			}
			$content.= '</form>'; // close "login-form"
			
			// hidden New Password block
			$content.= '<div id="perso-new_passwd">
				<form id="perso-new_passwd_form" name="new_passwd_form" action="'.WT_LOGIN_URL.'" method="post" onsubmit="t = new Date(); document.new_passwd_form.time.value=t.toUTCString(); return checkform(this);">
				<input type="hidden" name="time" value="">
				<input type="hidden" name="action" value="requestpw">
				<h4>'. WT_I18N::translate('Lost password request').'</h4>
				<div>
					<label for="perso-username">'. WT_I18N::translate('Username or email address').
						'<input type="text" id="perso-username" name="username" value="">
					</label>
				</div>
				<div><input type="submit" value="'. WT_I18N::translate('Continue'). '"></div>
				</form>
			</div>'; //"new_passwd"
			$content.= '</div>';//"login-box"
		}

		// Reorganise blocks page
		if (WT_USER_GEDCOM_ADMIN) {
			$content .= '<div class="center">';
			$content .=  '<a href="#" onclick="window.open(\'index_edit.php?name='.WT_GEDURL.'&amp;ctype=gedcom\', \'_blank\', \'top=50,left=10,width=600,height=500,scrollbars=1,resizable=1\'); return false;">'.WT_I18N::translate('Customize this GEDCOM Home Page').'</a><br />';
			$content .=  '</div>';
		}

		if ($template) {
			require WT_THEME_DIR.'templates/block_main_temp.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isUserBlock() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
		if (safe_POST_bool('save')) {
			set_block_setting($block_id, 'piwik_enabled',  safe_POST_bool('piwik_enabled'));
			set_block_setting($block_id, 'piwik_url',  safe_POST('piwik_url'));
			set_block_setting($block_id, 'piwik_siteid',  safe_POST('piwik_siteid'));
			set_block_setting($block_id, 'piwik_token',  safe_POST('piwik_token'));
			echo WT_JS_START, 'window.opener.location.href=window.opener.location.href;window.close();', WT_JS_END;
			exit;
		}
		
		require_once WT_ROOT.'includes/functions/functions_edit.php';
		
		// Is Piwik Statistic Enabled ?
		$piwik_enabled=get_block_setting($block_id, 'piwik_enabled', false);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Enable Piwik Statistics'), help_link('piwik_enabled', $this->getName());
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('piwik_enabled', $piwik_enabled);
		echo '</td></tr>';
		
		// Piwik root URL
		$piwik_url=get_block_setting($block_id, 'piwik_url', '');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Piwik URL'), help_link('piwik_url', $this->getName());
		echo '</td><td class="optionbox"><input type="text" name="piwik_url" size="45" value="'.$piwik_url.'" /></td></tr>';
		
		// Piwik token
		$piwik_token=get_block_setting($block_id, 'piwik_token', '');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Piwik Token'), help_link('piwik_token', $this->getName());
		echo '</td><td class="optionbox"><input type="text" name="piwik_token" size="45" value="'.$piwik_token.'" /></td></tr>';
		
		
		// Piwik side id
		$piwik_siteid=get_block_setting($block_id, 'piwik_siteid', '');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Piwik Site ID'), help_link('piwik_siteid', $this->getName());
		echo '</td><td class="optionbox"><input type="text" name="piwik_siteid" size="4" value="'.$piwik_siteid.'" /></td></tr>';

	}
	
	/**
	 * Return the number of visits, according to a Piwik installation.
	 *
	 * @param string $block_id Block ID
	 * @param string $period Period on which to retrieve statistics. Default is year.
	 * @return int|NULL Number of visits, if defined, null otherwise
	 */
	private function getNumberOfVisitsPiwik($block_id, $period='year'){
		
		$piwik_url=get_block_setting($block_id, 'piwik_url', '');
		$piwik_siteid=get_block_setting($block_id, 'piwik_siteid', '');
		$piwik_token=get_block_setting($block_id, 'piwik_token', '');
		
		// calling Piwik REST API
		$url = $piwik_url;
		$url .= '?module=API&method=VisitsSummary.getVisits';
		$url .= '&idSite='.$piwik_siteid.'&period='.$period.'&date=today';
		$url .= '&format=PHP';
		$url .= '&token_auth='.$piwik_token;
		
		$fetched = file_get_contents($url);
		$content = unserialize($fetched);
		
		// case error
		if($content && is_numeric($content))
		{
			return $content;
		}
		
		return null;	
	}

	private function getPiwikStats(){			
		$controller=new WT_Controller_Ajax();
		
		$html = WT_I18N::translate('No statistics could be retrieved from Piwik.');
		$block_id = safe_GET('block_id');
		$piwik_url=get_block_setting($block_id, 'piwik_url', '');
		if($block_id && WT_Perso_Functions::isUrlAlive($piwik_url)){
			if(WT_Perso_Cache::isCached('piwikCountYear', $this)) {
				$visitCountYear = WT_Perso_Cache::get('piwikCountYear', $this);
			}
			else{
				$visitCountYear = $this->getNumberOfVisitsPiwik($block_id);
				WT_Perso_Cache::save('piwikCountYear', $visitCountYear, $this);
			}
			if($visitCountYear){
				$visitCountToday = max(0, $this->getNumberOfVisitsPiwik($block_id, 'day'));
				$visitCountYear = max( 0, $visitCountYear);
				$currentYear = date('Y');
				$html = WT_I18N::translate('<span class="hit-counter">%1$s</span> visits since the beginning of %2$s<br/>(<span class="hit-counter">%3$s</span> today)', $visitCountYear + $visitCountToday, $currentYear, $visitCountToday);
			}
		}
		
		$controller->pageHeader();
		echo $html;
	}
	
}

?>