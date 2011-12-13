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

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $WT_IMAGES, $ctype;

		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		$title = '';
		if ($ctype=='gedcom' && WT_USER_GEDCOM_ADMIN) {
			$title.='<a href="javascript: configure block" onclick="window.open(\'index_edit.php?action=configure&amp;ctype='.$ctype.'&amp;block_id='.$block_id.'\', \'_blank\', \'top=50,left=50,width=600,height=350,scrollbars=1,resizable=1\'); return false;">';
			$title.='<img class="adminicon" src="'.$WT_IMAGES["admin"].'" width="15" height="15" border="0" alt="'.WT_I18N::translate('Configure').'" /></a>';
		}
		$title.=get_gedcom_setting(WT_GED_ID, 'title');
		
		$piwik_enabled=get_block_setting($block_id, 'piwik_enabled', false);
		
		$content = '<div class="center">';
		$content .= '<br />'.format_timestamp(client_time()).'<br />';
		if ($piwik_enabled){
			$visitCountYear = $this->getNumberOfVisitsPiwik($block_id);
			if($visitCountYear){
				$visitCountToday = max(0, $this->getNumberOfVisitsPiwik($block_id, 'day'));
				$visitCountYear = max( 0, $visitCountYear);
				$currentYear = date('Y');
				$content .=  WT_I18N::translate('<span class="hit-counter">%1$s</span> visits since the beginning of %2$s<br/>(<span class="hit-counter">%3$s</span> today)', $visitCountYear + $visitCountToday, $currentYear, $visitCountToday);
			}
		}
		$content .=  '</div>';
		
		$content .= '<hr />';
		
		if (WT_USER_ID) {
			$content .= '<div class="center"><form method="post" action="index.php?logout=1" name="logoutform" onsubmit="return true;">';
			$content .= '<br /><a href="edituser.php" class="name2">'.WT_I18N::translate('Logged in as ').' ('.WT_USER_NAME.')</a><br /><br />';

			$content .= "<input type=\"submit\" value=\"".WT_I18N::translate('Logout')."\" />";

			$content .= "<br /><br /></form></div>";
		} else {
			if (get_site_setting('USE_REGISTRATION_MODULE')) {
				$title.=help_link('index_login_register');
			} else {
				$title.=help_link('index_login');
			}
			$LOGIN_URL=get_site_setting('LOGIN_URL');
			$content .= "<div class=\"center\"><form method=\"post\" action=\"$LOGIN_URL\" name=\"loginform\" onsubmit=\"t = new Date(); document.loginform.usertime.value=t.getFullYear()+'-'+(t.getMonth()+1)+'-'+t.getDate()+' '+t.getHours()+':'+t.getMinutes()+':'+t.getSeconds(); return true;\">";
			$content .= "<input type=\"hidden\" name=\"url\" value=\"index.php\" />";
			$content .= "<input type=\"hidden\" name=\"ged\" value=\"";
			$content .= WT_GEDCOM;
			$content .= "\" />";
			$content .= "<input type=\"hidden\" name=\"pid\" value=\"";
			if (isset($pid)) $content .= $pid;
			$content .= "\" />";
			$content .= "<input type=\"hidden\" name=\"usertime\" value=\"\" />";
			$content .= "<input type=\"hidden\" name=\"action\" value=\"login\" />";
			$content .= "<table class=\"center\">";

			// Row 1: Userid
			$content .= "<tr><td>";
			$content .= WT_I18N::translate('User name');
			$content .= help_link('username');
			$content .= "</td><td><input type=\"text\" name=\"username\"  size=\"20\" class=\"formField\" />";
			$content .= "</td></tr>";

			// Row 2: Password
			$content .= "<tr><td>";
			$content .= WT_I18N::translate('Password');
			$content .= help_link('password');
			$content .= "</td><td><input type=\"password\" name=\"password\"  size=\"20\" class=\"formField\" />";
			$content .= "</td></tr>";

			// Row 3: "Login" link
			$content .= "<tr><td colspan=\"2\" class=\"center\">";
			$content .= "<input type=\"submit\" value=\"".WT_I18N::translate('Login')."\" />&nbsp;";
			$content .= "</td></tr>";
			$content .= "</table><table class=\"center\">";

			if (get_site_setting('USE_REGISTRATION_MODULE')) {

				// Row 4: "Request Account" link
				$content .= "<tr><td><br />";
				$content .= WT_I18N::translate('No account?');
				$content .= help_link('new_user');
				$content .= "</td><td><br />";
				$content .= "<a href=\"login_register.php?action=register\">";
				$content .= WT_I18N::translate('Request new user account');
				$content .= "</a>";
				$content .= "</td></tr>";

				// Row 5: "Lost Password" link
				$content .= "<tr><td>";
				$content .= WT_I18N::translate('Lost your password?');
				$content .= help_link('new_password');
				$content .= "</td><td>";
				$content .= "<a href=\"login_register.php?action=pwlost\">";
				$content .= WT_I18N::translate('Request new password');
				$content .= "</a>";
				$content .= "</td></tr>";
			}

			$content .= "</table>";
			$content .= "</form></div>";
		}

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
		return true;
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
		if(WT_Perso_Functions::isUrlAlive($piwik_url)){
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
		}
		
		return null;
	
	}

	
}

?>