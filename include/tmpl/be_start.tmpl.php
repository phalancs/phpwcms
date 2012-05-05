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

// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
   die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------


// set backend listing values 
$_phpwcms_home['homeMaxArticles'] = empty($_COOKIE['homeMaxArticles']) ? 5 : intval($_COOKIE['homeMaxArticles']);
$_phpwcms_home['homeMaxCntParts'] = empty($_COOKIE['homeMaxCntParts']) ? 5 : intval($_COOKIE['homeMaxCntParts']);

if(isset($_POST['homeMaxArticles'])) {
	if($_phpwcms_home['homeMaxArticles'] = intval($_POST['homeMaxArticles'])) {
		@setcookie('homeMaxArticles', strval($_phpwcms_home['homeMaxArticles']) , time()+31536000); // store cookie for 1 year
	}
}
if(isset($_POST['homeMaxCntParts'])) {
	if($_phpwcms_home['homeMaxCntParts'] = intval($_POST['homeMaxCntParts'])) {
		@setcookie('homeMaxCntParts', strval($_phpwcms_home['homeMaxCntParts']) , time()+31536000); // store cookie for 1 year
	}
}
// set default if necessary
if(!$_phpwcms_home['homeMaxArticles']) $_phpwcms_home['homeMaxArticles'] = 5;
if(!$_phpwcms_home['homeMaxCntParts']) $_phpwcms_home['homeMaxCntParts'] = 5;

// set if user has admin rights
$_usql = $_SESSION["wcs_user_admin"] ? '' : 'AND article_uid='.intval($_SESSION["wcs_user_id"]).' ';

// Get articles
$sql  = "SELECT article_id, article_cid, article_title, article_public, article_aktiv, article_uid, ";
$sql .= "UNIX_TIMESTAMP(article_tstamp) AS article_unixtime ";
$sql .= "FROM ".DB_PREPEND."phpwcms_article ";
$sql .= 'WHERE article_deleted=0 ';
$sql .= $_usql;
$sql .= 'ORDER BY article_tstamp DESC LIMIT '.$_phpwcms_home['homeMaxArticles'];
$result = _dbQuery($sql);

// Get content parts
$sql  = "SELECT *, UNIX_TIMESTAMP(acontent_tstamp) AS acontent_unixtime FROM ".DB_PREPEND."phpwcms_articlecontent ";
$sql .= "LEFT JOIN ".DB_PREPEND."phpwcms_article ON ";
$sql .= DB_PREPEND."phpwcms_articlecontent.acontent_aid = ".DB_PREPEND."phpwcms_article.article_id "; 
$sql .= 'WHERE acontent_trash=0 AND article_deleted=0 ';
$sql .= $_usql;
$sql .= 'ORDER BY acontent_tstamp DESC LIMIT '.$_phpwcms_home['homeMaxCntParts'];

?>
<div class="row-fluid">
	
	<form action="phpwcms.php" method="post" class="pull-right form-inline">
		<label><?php echo $BL['be_cnt_rssfeed_max'] ?></label>
		<input type="text" name="homeMaxArticles" id="homeMaxArticles" value="<?php echo $_phpwcms_home['homeMaxArticles'] ?>" class="input-mini" onblur="this.form.submit();" />
	</form>
	
	<h2><?php echo $BL['be_cnt_articles'] .' <small>('.$BL['be_last_edited'].')</small>' ?></h2>

	<table class="table table-bordered-bottom table-condensed">
	
		<thead>
			<tr>
				<th><?php echo $BL['be_article_atitle'] ?></th>
				<th class="nowrap"><?php echo $BL['be_cnt_last_edited'] ?></th>
				<th>&nbsp;</th>
			</tr>	
		</thead>
	
		<tbody>
<?php	foreach($result as $value):
				
				$value["button_type"] = $value["article_aktiv"] ? 'success' : 'danger';
?>
			
			<tr>
				<td style="width:85%;"><i class="icon-file"></i> <?php echo html($value['article_title']) ?></td>
				<td class="nowrap"><?php echo date($BL['be_longdatetime'], $value['article_unixtime']) ?></td>
				<td class="nowrap">
					<a href="index.php?aid=<?php echo $value['article_id'] ?>" class="btn btn-mini target-blank" title="<?php echo $BL['be_cnt_sitemap_display'] ?>">
						<i class="icon-zoom-in"></i>&nbsp;<?php echo $BL['be_cnt_sitemap_display'] ?>
					</a>
					<a href="phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;id=<?php echo $value['article_id'] ?>" class="btn btn-mini btn-<?php echo $value["button_type"] ?>" title="<?php echo $BL['be_func_struct_edit'] ?>">
						<i class="icon-edit icon-white"></i>&nbsp;<?php echo $BL['be_cnt_guestbook_edit'] ?>
					</a>
				</td>
			</tr>
	
<?php	endforeach;	?>
		
		</tbody>
	</table>
</div>


<div class="row-fluid btn-group">
	<a href="phpwcms.php?do=articles" class="btn" title="<?php echo $BL['be_subnav_article_center'] ?>"><?php echo $BL['be_subnav_article_center'] ?></a>
	<a href="phpwcms.php?do=articles&amp;p=1&amp;struct=0" class="btn" <?php echo $BL['be_subnav_article_new'] ?>><?php echo $BL['be_subnav_article_new'] ?></a>
</div>

<hr />

<div class="row-fluid">
	<form action="phpwcms.php" method="post" class="pull-right form-inline">
		<label><?php echo $BL['be_cnt_rssfeed_max'] ?></label>
		<input type="text" name="homeMaxCntParts" id="homeMaxCntParts" value="<?php echo $_phpwcms_home['homeMaxCntParts'] ?>" class="input-mini" onblur="this.form.submit();" />
	</form>
	<h2><?php echo $BL['be_ctype'] .' <small>('.$BL['be_last_edited'].')</small>' ?></h2>

	<table class="table table-bordered-bottom table-condensed">
	
		<thead>
			<tr>
				<th><?php echo $BL['be_cnt_type'] ?></th>
				<th><?php echo $BL['be_article_cnt_ctitle'] ?></th>
				<th class="nowrap"><?php echo $BL['be_cnt_last_edited'] ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		
		<tbody>

<?php	$result = _dbQuery($sql);

		foreach($result as $value):
	
			if(($value["acontent_type"] == 30 && !isset($phpwcms['modules'][$value["acontent_module"] ])) || !isset($wcs_content_type[$value["acontent_type"]])) {
				continue;
			}
			
			$value["button_type"]	= $value["article_aktiv"] ? 'success' : 'danger';
			$value['separator']		= $value['acontent_title'] && $value['acontent_subtitle'] ? ' / ' : '';
?>	
			<tr>
				<td class="nowrap" style="width:25%;"><?php echo $wcs_content_type[$value["acontent_type"]]; if($value["acontent_type"] == 30) echo ': '.$BL['modules'][$value["acontent_module"]]['listing_title']; ?></td>
				<td style="width:70%;"><?php echo html(getCleanSubString($value['acontent_title'].$value['separator'].$value['acontent_subtitle'], 27, '&#8230;')) ?></td>
				<td class="nowrap"><?php echo date($BL['be_longdatetime'], $value['acontent_unixtime']) ?></td>
				<td class="nowrap">
					<a href="phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id=<?php echo $value['acontent_aid'] ?>&amp;acid=<?php echo $value['acontent_id'] ?>" class="btn btn-mini btn-<?php echo $value["button_type"] ?>" title="<?php echo $BL['be_func_content_edit'] ?>">
						<i class="icon-edit icon-white"></i>&nbsp;<?php echo $BL['be_cnt_guestbook_edit'] ?>
					</a>			
				</td>
			</tr>

<?php	endforeach;	?>
		</tbody>
	</table>
</div>

<?php	echo phpwcmsversionCheck();	?>