<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <oliver@phpwcms.de>
 * @copyright Copyright (c) 2002-2012, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.de
 *
 **/


// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
   die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------


// Content Type Guestbook
$content["sitemap"]["before"]		= slweg($_POST["csitemap_before"]);
$content["sitemap"]["after"] 		= slweg($_POST["csitemap_after"]);
$content["sitemap"]["catimg"]		= clean_slweg($_POST["csitemap_catimg"]);
$content["sitemap"]["articleimg"]	= clean_slweg($_POST["csitemap_articleimg"]);
$content["sitemap"]["display"]		= intval($_POST["csitemap_display"]);
$content["sitemap"]["catclass"]		= clean_slweg($_POST["csitemap_catclass"]);
$content["sitemap"]["articleclass"]	= clean_slweg($_POST["csitemap_articleclass"]);
$content["sitemap"]["classcount"]	= intval($_POST["csitemap_classcount"]);
$content["sitemap"]["startid"]		= intval($_POST["csitemap_startid"]);

?>