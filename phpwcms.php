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

// set page processiong start time
list($usec, $sec) = explode(' ', microtime());
$phpwcms_rendering_start = $usec + $sec;

session_start();

//define used var names
$body_onload				= '';
$forward_to_message_center	= false;
$wcsnav 					= array();
$indexpage 					= array();
$phpwcms 					= array();
$BL							= array();
$BE							= array('HTML' => '', 'BODY_OPEN' => array(), 'BODY_CLOSE' => array(), 'HEADER' => array(), 'LANG' => 'en');
$phpwcms_root				= str_replace('\\', '/', dirname(__FILE__));

if(!is_file($phpwcms_root.'/include/config/conf.inc.php')) {
	if(is_file($phpwcms_root.'/setup/index.php')) {
		header('Location: setup/index.php');
		exit();
	}
	die('Error: Config file missing. Check your setup!');
}

// check against user's language
if(!empty($_SESSION["wcs_user_lang"]) && preg_match('/[a-z]{2}/i', $_SESSION["wcs_user_lang"])) {
	$BE['LANG'] = $_SESSION["wcs_user_lang"];
}

require_once ($phpwcms_root.'/include/config/conf.inc.php');
require_once ($phpwcms_root.'/include/lib/default.inc.php');
require_once (PHPWCMS_ROOT.'/include/lib/dbcon.inc.php');
require_once (PHPWCMS_ROOT.'/include/lib/general.inc.php');
checkLogin();
require_once (PHPWCMS_ROOT.'/include/lib/backend.functions.inc.php');
require_once (PHPWCMS_ROOT.'/include/lib/default.backend.inc.php');

//load default language EN
require_once (PHPWCMS_ROOT.'/include/lang/backend/en/lang.inc.php');
$BL['modules'] = array();

if(!empty($_SESSION["wcs_user_lang_custom"])) {
	//use custom lang if available -> was set in login.php
	$BL['merge_lang_array'][0]		= $BL['be_admin_optgroup_label'];
	$BL['merge_lang_array'][1]		= $BL['be_cnt_field'];	
	include(PHPWCMS_ROOT.'/include/lang/backend/'. $BE['LANG'] .'/lang.inc.php');
	$BL['be_admin_optgroup_label']	= array_merge($BL['merge_lang_array'][0], $BL['be_admin_optgroup_label']);
	$BL['be_cnt_field']				= array_merge($BL['merge_lang_array'][1], $BL['be_cnt_field']);
	unset($BL['merge_lang_array']);
}

require_once (PHPWCMS_ROOT.'/include/lib/navi_text.inc.php');
require_once (PHPWCMS_ROOT.'/include/lib/checkmessage.inc.php');
require_once (PHPWCMS_ROOT.'/include/config/conf.template_default.inc.php');
require_once (PHPWCMS_ROOT.'/include/config/conf.indexpage.inc.php');
require_once (PHPWCMS_ROOT.'/include/lib/imagick.convert.inc.php');

// check modules 
require_once (PHPWCMS_ROOT.'/include/lib/modules.check.inc.php');	

$BL['be_admin_struct_index']		= html($indexpage['acat_name']);
$subnav								= ''; //Sub Navigation
$p									= isset($_GET["p"])  ? intval($_GET["p"]) : 0; //which page should be opened
$do									= isset($_GET["do"]) ? $_GET["do"] : 'default'; //which backend section and which $do action
$module								= isset($_GET['module'])  ? clean_slweg($_GET['module']) : ''; //which module
$phpwcms['be_parse_lang_process']	= false; // limit parsing for BBCode/BraceCode languages only to some sections
$phpwcms['be_current']				= $BL['be_nav_home'];

switch ($do) {

	case "articles":	//articles
						$phpwcms['be_current'] = $BL['be_nav_articles'];
						include(PHPWCMS_ROOT.'/include/lib/admin.functions.inc.php');
						$wcsnav["articles"] = '<li class="active"><a href="phpwcms.php?do=articles">'.$BL['be_nav_articles'].'</a></li>';
						include(PHPWCMS_ROOT.'/include/lib/article.contenttype.inc.php'); //load array with actual content types
						include(PHPWCMS_ROOT.'/include/lib/article.functions.inc.php'); //load article funtions
						$subnav .= subnavtext($BL['be_subnav_article_center'], "phpwcms.php?do=articles", $p, '');
						$subnav .= subnavtext($BL['be_subnav_article_new'], "phpwcms.php?do=articles&amp;p=1&amp;struct=0", $p, "1");
						$subnav .= subnavtext($BL['be_news'], "phpwcms.php?do=articles&amp;p=3", $p, "3");
						break;

	case "files":		//files
						$phpwcms['be_current'] = $BL['be_nav_files'];
						$wcsnav["files"] = '<li class="active"><a href="phpwcms.php?do=files">'.$BL['be_nav_files']."</a></li>";
						$subnav .= subnavtext($BL['be_subnav_file_center'], "phpwcms.php?do=files", $p, '');
						$subnav .= subnavtext($BL['be_subnav_file_ftptakeover'], "phpwcms.php?do=files&amp;p=8", $p, "8");
						$subnav .= subnavtext($BL['be_file_multiple_upload'], "phpwcms.php?do=files&amp;p=9", $p, "9");
						break;

	case "modules":		//modules
						$phpwcms['be_current'] = $BL['be_nav_modules'];
						$wcsnav["modules"] = '<li class="active"><a href="phpwcms.php?do=modules">'.$BL['be_nav_modules']."</a></li>";
						
						foreach($phpwcms['modules'] as $value) {
						
							$subnav .= subnavtext($BL['modules'][ $value['name'] ]['backend_menu'], 'phpwcms.php?do=modules&amp;module='.$value['name'], $module, $value['name']);
						
						}
						
						break;

	case "messages":	//messages
						$phpwcms['be_current'] = $BL['be_nav_messages'];
						$wcsnav["messages"] = '<li class="active"><a href="phpwcms.php?do=messages&amp;p=4">'.$BL['be_nav_messages']."</a></li>";
						if(isset($_SESSION["wcs_user_admin"]) && $_SESSION["wcs_user_admin"] == 1) {
							$subnav .= subnavtext($BL['be_subnav_msg_newslettersend'], "phpwcms.php?do=messages&amp;p=3", $p, "3");
							$subnav .= subnavtext($BL['be_subnav_msg_subscribers'], "phpwcms.php?do=messages&amp;p=4", $p, "4");
							$subnav .= subnavtext($BL['be_subnav_msg_newsletter'], "phpwcms.php?do=messages&amp;p=2", $p, "2");
						}
						if(!empty($phpwcms['enable_messages'])) {
							$subnav .= subnavtext($BL['be_subnav_msg_center'], "phpwcms.php?do=messages", $p, "");
							$subnav .= subnavtext($BL['be_subnav_msg_new'], "phpwcms.php?do=messages&amp;p=1", $p, "1");
						}
						break;

	case "discuss":		//discuss
						$phpwcms['be_current'] = $BL['be_nav_discuss'];
						$wcsnav["discuss"] = '<li class="active">'.$BL['be_nav_discuss']."</li>";
						break;

	case "chat":		//chat
						$wcsnav["chat"] = '<li class="active"><a href="phpwcms.php?do=chat">'.$BL['be_nav_chat']."</a></li>";
						$subnav .= subnavtext($BL['be_subnav_chat_main'], "phpwcms.php?do=chat", $p, "");
						$subnav .= subnavtext($BL['be_subnav_chat_internal'], "phpwcms.php?do=chat&amp;p=1", $p, "1");
						break;

	case "profile":		//profile
						$phpwcms['be_current'] = $BL['be_nav_profile'];
						if(!empty($_POST["form_aktion"])) {
							switch($_POST["form_aktion"]) { //Aktualisieren der wcs account & profile Daten
								case "update_account":	include(PHPWCMS_ROOT.'/include/lib/profile.updateaccount.inc.php');
														break;
								case "update_detail":	include(PHPWCMS_ROOT.'/include/lib/profile.update.inc.php'); 
														break;
								case "create_detail":	include(PHPWCMS_ROOT.'/include/lib/profile.create.inc.php'); 
														break;
							}
						}
						$subnav .= subnavtext($BL['be_subnav_profile_login'], "phpwcms.php?do=profile", $p, "");
						$subnav .= subnavtext($BL['be_subnav_profile_personal'], "phpwcms.php?do=profile&amp;p=1", $p, "1");
						break;

	case "logout":		//Logout
						$sql  = "UPDATE ".DB_PREPEND."phpwcms_userlog SET ";
						$sql .= "logged_change=".time().", logged_in=0 ";
						$sql .= "WHERE logged_user='".$_SESSION["wcs_user"]."' AND logged_in=1";
						@mysql_query($sql, $db);
						session_destroy();
						headerRedirect(PHPWCMS_URL.get_login_file());
						break;

	case "admin":		//Admin
						if(isset($_SESSION["wcs_user_admin"]) && $_SESSION["wcs_user_admin"] == 1) {
							$phpwcms['be_current'] = $BL['be_nav_admin'];
							include(PHPWCMS_ROOT.'/include/lib/admin.functions.inc.php');
							$subnav .= subnavtext($BL['be_subnav_admin_sitestructure'], "phpwcms.php?do=admin&amp;p=6", $p, "6");
							$subnav .= subnavtext($BL['be_subnav_admin_pagelayout'], "phpwcms.php?do=admin&amp;p=8", $p, "8");
							$subnav .= subnavtext($BL['be_subnav_admin_templates'], "phpwcms.php?do=admin&amp;p=11", $p, "11");
							$subnav .= subnavtext($BL['be_subnav_admin_css'], "phpwcms.php?do=admin&amp;p=10", $p, "10");
							$subnav .= subnavtext($BL['be_subnav_admin_users'], "phpwcms.php?do=admin", $p, "");
							//$subnav .= subnavtext($BL['be_subnav_admin_groups'], "phpwcms.php?do=admin&amp;p=1", $p, "1");
							//$subnav .= subnavtext($BL['be_admin_keywords'], "phpwcms.php?do=admin&amp;p=5", $p, "5");
							$subnav .= subnavtext($BL['be_subnav_admin_filecat'], "phpwcms.php?do=admin&amp;p=7", $p, "7");
							$subnav .= subnavtext($BL['be_subnav_admin_starttext'], "phpwcms.php?do=admin&amp;p=12", $p, "12");
							$subnav .= subnavtext($BL['be_article_urlalias'].' ('.$BL['be_ftptakeover_active'].')', 'phpwcms.php?do=admin&amp;p=13', $p, "13");
							$subnav .= subnavtext($BL['be_cnt_move_deleted'], 'include/actions/file.php?movedeletedfiles='. $_SESSION["wcs_user_id"], 1, 0, ' id="move-deleted" rel="'.$BL['be_cnt_move_deleted_msg'].'"');
							$subnav .= '<li><a href="include/actions/phpinfo.php" title="phpinfo()" class="blank">phpinfo()</a>';
						}
						break;
						
		default:		include(PHPWCMS_ROOT.'/include/lib/article.contenttype.inc.php'); //loading array with actual content types

} //Ende Auswahl Aktion

//Wenn der User kein Admin ist, anderenfalls
if(empty($_SESSION["wcs_user_admin"])) {
	unset($wcsnav["admin"]);
} elseif($do  == "admin") {
	$wcsnav["admin"] = '<li class="active"><a href="phpwcms.php?do=admin&amp;p=6">'.$BL['be_nav_admin'].'</a></li>';
}

//script chaching to allow header redirect
ob_start(); //without Compression

// set correct content type for backend
header('Content-Type: text/html; charset='.PHPWCMS_CHARSET);

?><!DOCTYPE html>
<html>
<head>
	<title><?php echo $BL['be_page_title'].' &mdash; '.PHPWCMS_HOST ?></title>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex, nofollow">
	<link rel="stylesheet" type="text/css" href="include/css/phpwcms-v2.css">
<?php

$BE['HEADER']['alias_slah_var'] = '	<script type="text/javascript"> ' . LF . '		var aliasAllowSlashes = ' . (PHPWCMS_ALIAS_WSLASH ? 'true' : 'false') . ';' . LF . '	</script>';
$BE['HEADER']['phpwcms.js'] = getJavaScriptSourceLink('include/js/phpwcms.js');


if($do == "messages" && $p == 1) {

	include(PHPWCMS_ROOT.'/include/lib/message.sendjs.inc.php');

} elseif($do == "articles") {

	if($p == 2 && isset($_GET["aktion"]) && intval($_GET["aktion"]) == 2) {
		initJsOptionSelect();
	}
	if(($p == 1) || ($p == 2 && isset($_GET["aktion"]) && intval($_GET["aktion"]) == 1)) {
		initJsCalendar();
	}

} elseif($do == 'admin' && ($p == 6 || $p == 11)) {

	// struct editor
	initJsOptionSelect();

}

if($BE['LANG'] == 'ar') {
	$BE['HEADER'][] = '<style type="text/css">' . LF . '<!--' . LF . '* {direction: rtl;}' . LF . '// -->' . LF . '</style>';
}

?>
<!-- phpwcms HEADER -->
</head>

<body<?php echo $body_onload ?> class="backend"><!-- phpwcms BODY_OPEN -->

	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid">
				<a class="brand" href="phpwcms.php">phpwcms</a>
				
				<div class="btn-group pull-right">
            		<a class="btn dropdown-toggle" data-toggle="dropdown" href="phpwcms.php?do=profile" title="<?php echo html($_SESSION["wcs_user_name"]) ?>">
              			<i class="icon-user"></i>
						<?php echo html($_SESSION["wcs_user"]) ?>
						<span class="caret"></span>
					</a>
            		<ul class="dropdown-menu">
              			<li<?php if($do == 'profile'): ?> class="active"<?php endif; ?>><a href="phpwcms.php?do=profile"><?php echo html($BL['be_nav_profile']) ?></a></li>
              			<li class="divider"></li>
             			<li><a href="phpwcms.php?do=logout"><?php echo html($BL['be_nav_logout']) ?></a></li>
            		</ul>
          		</div>
				
				<ul class="nav pull-right">
					<li><a href="<?php echo PHPWCMS_URL ?>"><?php echo PHPWCMS_HOST ?></a></li>
				</ul>
				
				<div class="nav-collapse">
					<ul class="nav">
						<li<?php if($do == 'default'): ?> class="active"<?php endif; ?>><a href="phpwcms.php"><?php echo html($BL['be_nav_home']) ?></a></li>
						<?php echo implode(LF.'						', $wcsnav); // create backend main navigation ?>
					</ul>
				</div>
				
			</div>
		</div>
	</div>
	
	<div class="container-fluid container-outer">
		<div class="row-fluid row-outer">
			<div class="sidebar pull-left">
				<ul class="nav nav-list">
<?php	if($subnav):	?>
					<li class="nav-header"><?php echo $phpwcms['be_current'] ?></li>
<?php 		echo $subnav;
		endif;			 ?>
					<li class="nav-header"><?php echo $BL['usr_online'] ?></li>
					<?php echo online_users($db, LF, "<li>|</li>");?>
      		</div>

	  		<div class="content fixed-fluid">
				{STATUS_MESSAGE}{BE_PARSE_LANG}
<!--BE_MAIN_CONTENT_START//-->
<?php
		 
      switch($do) {

      	case "profile":	//Profile
						include( PHPWCMS_ROOT.'/include/tmpl/' . ($p === 1 ? 'profile.data.tmpl.php' : 'profile.account.tmpl.php'));
						break;
      	
      	case 'filecenter':
      					include(PHPWCMS_ROOT.'/include/tmpl/filecenter.tmpl.php');
      					
      					break;

      	case "files":	//Hochladen sowie Downloaden und Verwalten von Dateien
      	switch($p) {
      		case 8:		//FTP File upload
						include(PHPWCMS_ROOT.'/include/lib/files.create.dirmenu.inc.php');
						include(PHPWCMS_ROOT.'/include/tmpl/files.ftptakeover.tmpl.php');
						break;
					
						// Multiple, queued file upload
			case 9:		include(PHPWCMS_ROOT.'/include/lib/files.create.dirmenu.inc.php');
						include(PHPWCMS_ROOT.'/include/lib/files.multipleupload.inc.php');
						include(PHPWCMS_ROOT.'/include/tmpl/files.multipleupload.tmpl.php');
						break;
						
      		default:	include(PHPWCMS_ROOT.'/include/tmpl/files.reiter.tmpl.php'); //Files Navigation/Reiter
      		switch($files_folder) {
      			case 0:	//Listing der Privaten Dateien
      			if(isset($_GET["mkdir"]) || (isset($_POST["dir_aktion"]) && intval($_POST["dir_aktion"]) == 1) ) {
					include(PHPWCMS_ROOT.'/include/tmpl/files.private.newdir.tmpl.php');
				}
      			if(isset($_GET["editdir"]) || (isset($_POST["dir_aktion"]) && intval($_POST["dir_aktion"]) == 2) ) {
					include(PHPWCMS_ROOT.'/include/tmpl/files.private.editdir.tmpl.php');
				}
      			if(isset($_GET["upload"]) || (isset($_POST["file_aktion"]) && intval($_POST["file_aktion"]) == 1) ) {
      				include(PHPWCMS_ROOT.'/include/lib/files.create.dirmenu.inc.php');
      				include(PHPWCMS_ROOT.'/include/tmpl/files.private.upload.tmpl.php');
      			}
      			if(isset($_GET["editfile"]) || (isset($_POST["file_aktion"]) && intval($_POST["file_aktion"]) == 2) ) {
      				include(PHPWCMS_ROOT.'/include/lib/files.create.dirmenu.inc.php');
      				include(PHPWCMS_ROOT.'/include/tmpl/files.private.editfile.tmpl.php');
      			}
      			include(PHPWCMS_ROOT.'/include/lib/files.private-functions.inc.php'); //Listing-Funktionen einfügen
      			include(PHPWCMS_ROOT.'/include/lib/files.private.additions.inc.php'); //Zusätzliche Private Funktionen
      			break;
      			case 1: //Funktionen zum Listen von Public Files
      			include(PHPWCMS_ROOT.'/include/lib/files.public-functions.inc.php'); //Public Listing-Funktionen einfügen
      			include(PHPWCMS_ROOT.'/include/tmpl/files.public.list.tmpl.php'); //Elemetares für Public Listing
      			break;
      			case 2:	//Dateien im Papierkorb
      			include(PHPWCMS_ROOT.'/include/tmpl/files.private.trash.tmpl.php');
      			break;
      			case 3:	//Dateisuche
      			include(PHPWCMS_ROOT.'/include/tmpl/files.search.tmpl.php');
      			break;
      		}
      		include(PHPWCMS_ROOT.'/include/tmpl/files.abschluss.tmpl.php'); //Abschließende Tabellenzeile = dicke Linie
      	}
      	break;

      	case "chat":	//Chat
      	switch($p) {
      		case 0: include(PHPWCMS_ROOT.'/include/tmpl/chat.main.tmpl.php'); break; //Chat Startseite
      		case 1: include(PHPWCMS_ROOT.'/include/tmpl/chat.list.tmpl.php'); break; //Chat/Listing
      	}
      	break;

		case "messages":	//Messages
      	switch($p) {
      		case 0: include(PHPWCMS_ROOT.'/include/tmpl/message.center.tmpl.php'); break; //Messages Overview
      		case 1: include(PHPWCMS_ROOT.'/include/tmpl/message.send.tmpl.php');   break;	//New Message
      		case 2: //Newsletter subscription
      		if($_SESSION["wcs_user_admin"] == 1) include(PHPWCMS_ROOT.'/include/tmpl/message.subscription.tmpl.php');
      		break;
      		case 3: //Newsletter
      		if($_SESSION["wcs_user_admin"] == 1) include(PHPWCMS_ROOT.'/include/tmpl/newsletter.list.tmpl.php');
      		break;
      		case 4: //Newsletter subscribers
      		if($_SESSION["wcs_user_admin"] == 1) {
				include(PHPWCMS_ROOT.'/include/tmpl/message.subscribers.tmpl.php');
			}
      		break;	
      	}
      	break;

      	case "modules":	//Modules
		
			// if a module is selected
			if(isset($phpwcms['modules'][$module])) {
			
				include($phpwcms['modules'][$module]['path'].'backend.default.php');
			
			}
			
			break;

      	case "admin":	//Administration
      	if($_SESSION["wcs_user_admin"] == 1) {
      		switch($p) {
      			case 0: //User Administration
      			switch(!empty($_GET['s']) ? intval($_GET["s"]) : 0) {
      				case 1: include(PHPWCMS_ROOT.'/include/tmpl/admin.newuser.tmpl.php');  break; //New User
      				case 2: include(PHPWCMS_ROOT.'/include/tmpl/admin.edituser.tmpl.php'); break; //Edit User
      			}
      			include(PHPWCMS_ROOT.'/include/tmpl/admin.listuser.tmpl.php');
      			break;
				
				case 1: //Users and Groups
				include(PHPWCMS_ROOT.'/include/lib/admin.groups.inc.php');
				include(PHPWCMS_ROOT.'/include/tmpl/admin.groups.'.$_entry['mode'].'.tmpl.php');				
				break;
				
				case 2: //Settings
				include(PHPWCMS_ROOT.'/include/tmpl/admin.settings.tmpl.php');				
				break;
				
				case 5: //Keywords
				include(PHPWCMS_ROOT.'/include/tmpl/admin.keyword.tmpl.php');	
				break;
				
      			case 6: //article structure
				
      			include(PHPWCMS_ROOT.'/include/lib/admin.structure.inc.php');
      			if(isset($_GET["struct"])) {
					include(PHPWCMS_ROOT.'/include/lib/article.contenttype.inc.php'); //loading array with actual content types
      				include(PHPWCMS_ROOT.'/include/tmpl/admin.structform.tmpl.php');
      			} else {
      				include(PHPWCMS_ROOT.'/include/tmpl/admin.structlist.tmpl.php');
					$phpwcms['be_parse_lang_process'] = true;
      			}
      			break;
      			
				case 7:	//File Categories
      			include(PHPWCMS_ROOT.'/include/tmpl/admin.filecat.tmpl.php');
      			break;
				
      			case 8:	//Page Layout
      			include(PHPWCMS_ROOT.'/include/tmpl/admin.pagelayout.tmpl.php');
      			break;
      			
				case 10:	//Frontend CSS
      			include(PHPWCMS_ROOT.'/include/tmpl/admin.frontendcss.tmpl.php');
      			break;
      			
				case 11:	//Templates
      			include(PHPWCMS_ROOT.'/include/tmpl/admin.templates.tmpl.php');
      			break;
				
      			case 12:	//Default backend starup HTML
      			include(PHPWCMS_ROOT.'/include/tmpl/admin.startup.tmpl.php');
      			break;
				
				//Default backend sitemap HTML
				case 13: 
				include(PHPWCMS_ROOT.'/include/tmpl/admin.aliaslist.tmpl.php');
        		break;

      		}
      	}
      	break;

		// articles
      	case "articles":
			$_SESSION['image_browser_article'] = 0; //set how image file browser should work
			switch($p) {
				
				// List articles
				case 0: 
					include(PHPWCMS_ROOT.'/include/tmpl/article.structlist.tmpl.php');
					$phpwcms['be_parse_lang_process'] = true;
					break;
				
				// Edit/create article
				case 1:
				case 2: 
					include(PHPWCMS_ROOT.'/include/lib/article.editcontent.inc.php');
					break;
				
				// News
				case 3:
					include(PHPWCMS_ROOT.'/include/lib/news.inc.php');
					include(PHPWCMS_ROOT.'/include/tmpl/news.tmpl.php');
					break;
			}
			break;
		
		// about phpwcms
		case "about":
			include(PHPWCMS_ROOT.'/include/tmpl/about.tmpl.php');
			break;
		
		// start
		default:
			include(PHPWCMS_ROOT.'/include/tmpl/be_start.tmpl.php');
			include(PHPWCMS_TEMPLATE.'inc_default/startup.php');
			$phpwcms['be_parse_lang_process'] = true;

	}

?>
<!--BE_MAIN_CONTENT_END//-->
			</div>
	 	</div>
		
		<footer class="fixed-fluid">
			<p class="pull-left">
				<a href="http://www.phpwcms.de" title="<?php echo $BL['be_aboutlink_title'] ?>" class="blank">phpwcms</a>
				&copy; 2002&#8212;<?php echo date('Y'); ?> <a href="http://www.linkedin.com/in/olivergeorgi" class="blank">Oliver Georgi</a>.
				<?php printf($BL['licensed_under'], '<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0" class="blank">GPLv2</a>'); ?>
			</p>
			<p class="pull-right">
				<?php echo $BL['version'] . ' ' . PHPWCMS_VERSION ?>
			</p>
		</footer>
	</div>

<?php

//Set Focus for chat insert filed
set_chat_focus($do, $p);

//If new message was sent -> automatic forwarding to message center
forward_to($forward_to_message_center, PHPWCMS_URL."phpwcms.php?do=messages", 2500);

$BE['BODY_CLOSE']['wz_tooltip.js'] = getJavaScriptSourceLink('include/js/wz_tooltip.js', '');

?>
<!-- phpwcms BODY_CLOSE -->
	
	<script src="include/js/jquery-1.7.2.js"></script>
	<script src="include/js/bootstrap-alert.js"></script>
	<script src="include/js/bootstrap-transition.js"></script>
	<script src="include/js/bootstrap-modal.js"></script>
    <script src="include/js/bootstrap-dropdown.js"></script>
    <script src="include/js/bootstrap-scrollspy.js"></script>
    <script src="include/js/bootstrap-tab.js"></script>
    <script src="include/js/bootstrap-tooltip.js"></script>
    <script src="include/js/bootstrap-popover.js"></script>
    <script src="include/js/bootstrap-button.js"></script>
	<script src="include/js/phpwcms-backend.js"></script>
	
</body>
</html>
<?php

// retrieve complete processing time
list($usec, $sec) = explode(' ', microtime());
header('X-phpwcms-Page-Processed-In: ' . number_format(getMicrotimeDiff($phpwcms_rendering_start, 's'), 3) .' s');

$BE['HTML'] = ob_get_clean();

//	parse for backend languages
backend_language_parser();

//	replace special backend sections -> good for additional code like custom JavaScript, CSS and so on
//	<!-- phpwcms BODY_CLOSE -->
//	<!-- phpwcms BODY_OPEN -->
//	<!-- phpwcms HEADER -->

// special body onload JavaScript
if($body_onload) {
	$BE['HTML'] = str_replace('<body>', '<body '.$body_onload.'>', $BE['HTML']);
}

// html head section
$BE['HTML'] = str_replace('<!-- phpwcms HEADER -->', implode(LF, $BE['HEADER']), $BE['HTML']);

// body open area
$BE['HTML'] = str_replace('<!-- phpwcms BODY_OPEN -->', implode(LF, $BE['BODY_OPEN']), $BE['HTML']);

// body close area
$BE['HTML'] = str_replace('<!-- phpwcms BODY_CLOSE -->', implode(LF, $BE['BODY_CLOSE']), $BE['HTML']);

// Show global system status message
$BE['HTML'] = str_replace('{STATUS_MESSAGE}', show_status_message(true), $BE['HTML']);

// return all
echo $BE['HTML'];

?>