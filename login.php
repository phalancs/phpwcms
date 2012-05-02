<?php
/*************************************************************************************
   Copyright notice
   
   (c) 2002-2012 Oliver Georgi <oliver@phpwcms.de> // All rights reserved.
 
   This script is part of PHPWCMS. The PHPWCMS web content management system is
   free software; you can redistribute it and/or modify it under the terms of
   the GNU General Public License as published by the Free Software Foundation;
   either version 2 of the License, or (at your option) any later version.
  
   The GNU General Public License can be found at http://www.gnu.org/copyleft/gpl.html
   A copy is found in the textfile GPL.txt and important notices to the license 
   from the author is found in LICENSE.txt distributed with these scripts.
  
   This script is distributed in the hope that it will be useful, but WITHOUT ANY 
   WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
   PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 
   This copyright notice MUST APPEAR in all copies of the script!
*************************************************************************************/

session_start();

$phpwcms	= array();
$BL			= array();
$phpwcms_root = str_replace('\\', '/', dirname(__FILE__));
if(!is_file($phpwcms_root.'/include/config/conf.inc.php')) {
	if(is_file($phpwcms_root.'/setup/index.php')) {
		header('Location: setup/index.php');
		exit();
	}
	die('Error: Config file missing. Check your setup!');
}
require_once ($phpwcms_root.'/include/config/conf.inc.php');
require_once ($phpwcms_root.'/include/lib/default.inc.php');
require_once (PHPWCMS_ROOT.'/include/lib/dbcon.inc.php');
require_once (PHPWCMS_ROOT.'/include/lib/general.inc.php');
require_once (PHPWCMS_ROOT.'/include/lib/backend.functions.inc.php');
require_once (PHPWCMS_ROOT.'/include/lang/code.lang.inc.php');

$_SESSION['REFERER_URL'] = PHPWCMS_URL.get_login_file();

// make compatibility check
if(phpwcms_revision_check_temp($phpwcms["revision"]) !== true) {
	_dbQuery('SET storage_engine=MYISAM', 'SET');
	$revision_status = phpwcms_revision_check($phpwcms["revision"]);
}

// define vars
$err 		= 0;
$wcs_user 	= '';

// where user should be redirected too after login
if(!empty($_POST['ref_url'])) {
	$ref_url = xss_clean($_POST['ref_url']);
} elseif(!empty($_GET['ref'])) {
	$ref_url = xss_clean(rawurldecode($_GET['ref']));
} else {
	$ref_url = '';
}


// reset all inactive users
$sql  = "UPDATE ".DB_PREPEND."phpwcms_userlog SET ";
$sql .= "logged_in = 0, logged_change = '".time()."' ";
$sql .= "WHERE logged_in = 1 AND ( ".time()." - logged_change ) > ".intval($phpwcms["max_time"]);
mysql_query($sql, $db);


//load default language EN
require_once (PHPWCMS_ROOT.'/include/lang/backend/en/lang.inc.php');

//define language and check if language file is available
if(isset($_COOKIE['phpwcmsBELang'])) {
	$temp_lang = strtoupper( substr( trim( $_COOKIE['phpwcmsBELang'] ), 0, 2 ) );
	if( isset( $BL[ $temp_lang ] ) ) {
		$_SESSION["wcs_user_lang"] = strtolower($temp_lang);
	} else {
		setcookie('phpwcmsBELang', '', time()-3600 );
	}
}
if(isset($_POST['form_lang'])) {
	$_SESSION["wcs_user_lang"] = strtolower(substr(clean_slweg($_POST['form_lang']), 0, 2));
	set_language_cookie();
}
if(empty($_SESSION["wcs_user_lang"])) {
	$_SESSION["wcs_user_lang"] = strtolower( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 ) : $phpwcms["default_lang"] );
} else {
	$_SESSION["wcs_user_lang"] = strtolower( substr($_SESSION["wcs_user_lang"], 0, 2 ) );
}
if(isset($BL[strtoupper($_SESSION["wcs_user_lang"])]) && is_file(PHPWCMS_ROOT.'/include/lang/backend/'.$_SESSION["wcs_user_lang"].'/lang.inc.php')) {
	$_SESSION["wcs_user_lang_custom"] = 1;
} else {
	$_SESSION["wcs_user_lang"] 			= 'en'; //by ono
	$_SESSION["wcs_user_lang_custom"] 	= 0;
}
if(!empty($_SESSION["wcs_user_lang_custom"])) { 
	//use custom lang if available -> was set in login.php
	$BL['merge_lang_array'][0] = $BL['be_admin_optgroup_label'];
	$BL['merge_lang_array'][1] = $BL['be_cnt_field'];	
	include_once (PHPWCMS_ROOT.'/include/lang/backend/'.$_SESSION["wcs_user_lang"].'/lang.inc.php');
	$BL['be_admin_optgroup_label'] = array_merge($BL['merge_lang_array'][0], $BL['be_admin_optgroup_label']);
	$BL['be_cnt_field'] = array_merge($BL['merge_lang_array'][1], $BL['be_cnt_field']);
}

//WYSIWYG EDITOR:
//0 = no wysiwyg editor (default)
//1 = CKEditor
//2 = FCKeditor
$phpwcms["wysiwyg_editor"]		= abs(intval($phpwcms["wysiwyg_editor"]));
if($phpwcms["wysiwyg_editor"] > 2) {
	$phpwcms["wysiwyg_editor"] = 1;
}
$_SESSION["WYSIWYG_EDITOR"]		= $phpwcms["wysiwyg_editor"];
$wysiwyg_template				= '';

if($phpwcms["wysiwyg_editor"]) {
					
	if(!empty($phpwcms['wysiwyg_template']['FCKeditor'])) {
		$wysiwyg_template = convertStringToArray($phpwcms['wysiwyg_template']['FCKeditor']);
	} elseif(!empty($phpwcms['wysiwyg_template']['CKEditor'])) {
		$wysiwyg_template = convertStringToArray($phpwcms['wysiwyg_template']['CKEditor']);
	}
	
	if(empty($wysiwyg_template) || count($wysiwyg_template) == 0) {
		$wysiwyg_template = array('Basic');
	}

}

if(isset($_POST['form_aktion']) && $_POST['form_aktion'] == 'login' && isset($_POST['json']) && $_POST['json'] == '1') {

	$login_passed = 0;
	$wcs_user = slweg($_POST['form_loginname']);
	$wcs_pass = slweg($_POST['md5pass']);
	
	$sql_query =	"SELECT * FROM ".DB_PREPEND."phpwcms_user WHERE usr_login='".
					aporeplace($wcs_user)."' AND usr_pass='".
					aporeplace($wcs_pass)."' AND usr_aktiv=1 AND (usr_fe=1 OR usr_fe=2)";

	if($result = mysql_query($sql_query)) {
		if($row = mysql_fetch_assoc($result)) {
			$_SESSION["wcs_user"]			= $wcs_user;
			$_SESSION["wcs_user_name"] 		= ($row["usr_name"]) ? $row["usr_name"] : $wcs_user;
			$_SESSION["wcs_user_id"]		= $row["usr_id"];
			$_SESSION["wcs_user_aktiv"]		= $row["usr_aktiv"];
			$_SESSION["wcs_user_rechte"]	= $row["usr_rechte"];
			$_SESSION["wcs_user_email"]		= $row["usr_email"];
			$_SESSION["wcs_user_avatar"]	= $row["usr_avatar"];
			$_SESSION["wcs_user_logtime"]	= time();
			$_SESSION["wcs_user_admin"]		= intval($row["usr_admin"]);
			$_SESSION["wcs_user_thumb"]		= 1;
			if($row["usr_lang"]) {
				$_SESSION["wcs_user_lang"]	= $row["usr_lang"];
			}
			
			set_language_cookie();
						
			$_SESSION["structure"]			= @unserialize($row["usr_var_structure"]);
			$_SESSION["klapp"]				= @unserialize($row["usr_var_privatefile"]);
			$_SESSION["pklapp"]				= @unserialize($row["usr_var_publicfile"]);
			$row["usr_vars"]				= @unserialize($row["usr_vars"]);
			$_SESSION["WYSIWYG_TEMPLATE"]	= empty($row["usr_vars"]['template']) || !in_array($row["usr_vars"]['template'], $wysiwyg_template) ? $wysiwyg_template[0] : $row["usr_vars"]['template'];
			
			$row["usr_wysiwyg"]				= abs(intval($row["usr_wysiwyg"]));
			// Fallback to FCKeditor?
			$_SESSION["WYSIWYG_EDITOR"]		= $row["usr_wysiwyg"] > 2 ? 2 : $row["usr_wysiwyg"];
			
			$login_passed = 1;
		}
		mysql_free_result($result);
	}
	
	if($login_passed) {
		// Store login information in DB
		$check = mysql_query(	"SELECT COUNT(*) FROM ".DB_PREPEND."phpwcms_userlog WHERE logged_user='".
								aporeplace($wcs_user)."' AND logged_in=1", $db );
		if($row = mysql_fetch_row($check)) {
			if(!$row[0]) {
				// User not yet logged in, create new
				mysql_query("INSERT INTO ".DB_PREPEND."phpwcms_userlog ".
							"(logged_user, logged_username, logged_start, logged_change, ".
							"logged_in, logged_ip) VALUES ('".
							aporeplace($wcs_user)."', '".aporeplace($_SESSION["wcs_user_name"])."', ".time().", ".
							time().", 1, '".aporeplace(getRemoteIP())."')", $db );				
			}
		}
		mysql_free_result($check);
		$_SESSION['PHPWCMS_ROOT'] = PHPWCMS_ROOT;
		set_status_message('Welcome '.$wcs_user.'!');
		if($ref_url) {
			headerRedirect($ref_url.'&'.session_name().'='.session_id());
		} else {
			headerRedirect(PHPWCMS_URL."phpwcms.php?". session_name().'='.session_id());
		}

	} else {
		$err = 1;
	}

} elseif(isset($_POST['json']) && intval($_POST['json']) != 1) {

	$err = 1;

}

$error_msg = array();

if(isset($_POST['json']) && $_POST['json'] == 2) {
	$err = 0;
}

if($err) {
	$error_msg[] = '<h4>' . $BL["login_error"] . '</h4>';
}

if(file_exists(PHPWCMS_ROOT.'/setup')) {
	$error_msg[] = $BL["setup_dir_exists"];
}
if(file_exists(PHPWCMS_ROOT.'/phpwcms_code_snippets')) {
	$error_msg[] = $BL["phpwcms_code_snippets_dir_exists"];
}


?><!DOCTYPE html>
<html>
<head>
	<title><?php echo $BL['be_page_title'] . ' &mdash; ' . PHPWCMS_HOST ?></title>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex, nofollow">
	<link href="include/css/phpwcms-v2.css" rel="stylesheet" type="text/css">
	<script src="include/js/jquery-1.7.2.js"></script>
	<script src="include/js/bootstrap-alert.js"></script>
	<script src="include/js/bootstrap-transition.js"></script>
	<script src="include/js/jquery.md5.js"></script>
<?php

// get whole login form and keep in buffer
ob_start();

?>
<?php	if(count($error_msg)):	?>
	<div class="alert alert-block alert-error fade in">
		<button class="close" data-dismiss="alert">&times;</button>
		<p><?php echo implode('</p><p>', $error_msg); ?></p>
	</div>
<?php	endif;	?>


<form action="<?php echo PHPWCMS_URL.get_login_file() ?>" method="post" id="login" autocomplete="off" class="form-horizontal">

	<div class="control-group">
		<label class="control-label" for="form_loginname"><?php echo $BL["login_username"] ?></label>
		<div class="controls">
			<input name="form_loginname" type="text" id="form_loginname" class="span3" value="<?php echo html_specialchars($wcs_user); ?>">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="form_password"><?php echo $BL["login_userpass"] ?></label>
		<div class="controls">
			<input name="form_password" type="password" id="form_password" class="span3">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="form_lang"><?php echo $BL["login_lang"] ?></label>
		<div class="controls">
		<select name="form_lang" id="form_lang" class="span3">
            <?php
// check available languages installed and build language selector menu
$lang_dirs = opendir(PHPWCMS_ROOT.'/include/lang/backend');
$lang_code = array();
while($lang_codes = readdir( $lang_dirs )) {
	if( $lang_codes != "." && $lang_codes != ".." && is_file(PHPWCMS_ROOT.'/include/lang/backend/'.$lang_codes."/lang.inc.php")) {
		$lang_code[$lang_codes]  = '<option value="'.$lang_codes.'"';
		$lang_code[$lang_codes] .= ($lang_codes == $_SESSION["wcs_user_lang"]) ? ' selected="selected"' : '';
		$lang_code[$lang_codes] .= '>';
		$lang_code[$lang_codes] .= (isset($BL[strtoupper($lang_codes)])) ? $BL[strtoupper($lang_codes)] : strtoupper($lang_codes);
		$lang_code[$lang_codes] .= '</option>';
	}
}
closedir( $lang_dirs );
ksort($lang_code);

echo implode(LF, $lang_code);

?>
		</select>
		</div>
	</div>
		
	<div class="form-actions">		
		<button type="submit" class="btn btn-primary"><?php echo $BL["login_button"] ?></button>
		<input type="hidden" name="json" id="json" value="0">
		<input type="hidden" name="md5pass" id="md5pass" value="" autocomplete="off">
		<input type="hidden" name="ref_url" value="<?php echo html_entities($ref_url) ?>">
		<input name="form_aktion" type="hidden" id="form_aktion" value="login">
	</div>
</form>
<?php

$formAll = str_replace( array("'", "\r", "\n", '<', '> <'), array("\'", '', " ", "<'+'", '><'), preg_replace('/\s+/s', ' ', ob_get_clean()) );

?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			$('#loginSection').html('<?php echo $formAll ?>');
			var login	= $('#login');
			var lang	= $('#json');
			login.submit(function(event){
				if(lang.val()=='2') {
					return true;
				}
				event.preventDefault();
				lang.val('1');
				var password	= $('#form_password');
				$('#md5pass').val( $.md5(password.val()) );
				password.val('');
				
				(this).submit();
				
			});
			$('#form_lang').change(function(){
				lang.val('2');
				login.submit();
			})
		});
	</script>
<?php	if((isset($_SESSION["wcs_user_lang"]) && $_SESSION["wcs_user_lang"] == 'ar') || strtolower($phpwcms['default_lang']) == 'ar'):	?>
	<style type="text/css">
		* {direction: rtl;}
	</style>
<?php	endif;	?>

</head>
<body>
	
	<div class="container-fluid">
		<div class="box-login well">
			
			<div class="page-header">
				<h1>
					<strong>phpwcms</strong>
					
				</h1>
			</div>
			
			<div id="loginSection">
			
				<p class="alert alert-error">
					<strong><?php echo $BL['be_login_jsinfo']; ?></strong>
				</p>
			
			</div>
				
			<p>
				<strong><a href="http://www.phpwcms.de" target="_blank" style="text-decoration:none;">phpwcms</a></strong> 
				Copyright &copy; 2003&#8212;<?php echo date('Y'); ?>
				Oliver Georgi. Extensions are copyright of their respective owners.
				Visit <a href="http://www.phpwcms.de">www.phpwcms.de</a> for
				details. phpwcms is free software released under <a href="http://www.fsf.org/licensing/licenses/gpl.html" target="_blank">GPL</a> 
				and comes WITHOUT ANY WARRANTY. Obstructing the appearance of this notice is prohibited  by law. 
			</p>

		</div>
	</div>
</body>
</html>