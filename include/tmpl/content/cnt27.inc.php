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


//FAQ

?>
<tr>
	<td align="right" class="chatlist"><?php echo $BL['be_admin_struct_template'] ?>:&nbsp;</td>
	<td><select name="faq_template" id="faq_template" class="f11b">
<?php

// templates for recipes
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/faq');
if(is_array($tmpllist) && count($tmpllist)) {
	foreach($tmpllist as $val) {
		if(isset($content['faq']['faq_template']) && $val == $content['faq']['faq_template']) {
			$selected_val = ' selected="selected"';
		} else {
			$selected_val = '';
		}
		$val = htmlspecialchars($val);
		echo '	<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
	}
}

?>				  
	</select></td>
</tr>
<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="8" /></td></tr>
<tr><td colspan="2"><img src="include/img/lines/l538_70.gif" alt="" /></td></tr>
<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="8" /></td></tr>
<tr><td colspan="2" class="chatlist">&nbsp;<?php echo $BL['be_cnt_question'] ?>:&nbsp;</td></tr>
<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="1" /></td>
</tr>
<tr><td colspan="2"><textarea name="faq_question" rows="4" class="msgtext" id="faq_question" style="width: 536px"><?php
	
	echo empty($content["faq_question"]) ? '' : $content["faq_question"];
	 
?></textarea></td></tr>
<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="8" /></td>
</tr>
<tr><td colspan="2" class="chatlist">&nbsp;<?php echo $BL['be_cnt_answer'] ?>:&nbsp;</td></tr>
<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="1" /></td>
</tr>
<tr><td colspan="2" align="center"><?php

$wysiwyg_editor = array(
	'value'		=> isset($content["faq_answer"]) ? $content["faq_answer"] : '',
	'field'		=> 'faq_answer',
	'height'	=> '300px',
	'width'		=> '536px',
	'rows'		=> '15',
	'editor'	=> $_SESSION["WYSIWYG_EDITOR"],
	'lang'		=> 'en'
);

include(PHPWCMS_ROOT.'/include/lib/wysiwyg.editor.inc.php');



?></td></tr>
<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="10" /></td>
</tr>
<tr><td colspan="2"><img src="include/img/lines/l538_70.gif" alt="" /></td>
</tr>
<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="10" /></td>
</tr>
<tr>
			  <td align="right" class="chatlist"><?php echo  $BL['be_cnt_image'] ?>:&nbsp;</td>
			  <td valign="top"><table border="0" cellpadding="0" cellspacing="0" summary="">
			    <tr>
			      <td><input name="cimage_name" type="text" id="cimage_name" class="f11b" style="width: 200px; color: #727889;" value="<?php echo  isset($content["image_name"]) ? html_specialchars($content["image_name"]) : '' ?>" size="40" maxlength="250" onfocus="this.blur()" /></td>
			      <td><img src="include/img/leer.gif" alt="" width="3" height="1" /><a href="javascript:;" title="<?php echo  $BL['be_cnt_openimagebrowser'] ?>" onclick="openFileBrowser('filebrowser.php?opt=0&amp;target=nolist')"><img src="include/img/button/open_image_button.gif" alt="" width="20" height="15" border="0" /></a></td>
			      <td><img src="include/img/leer.gif" alt="" width="3" height="1" /><a href="javascript:;" title="<?php echo  $BL['be_cnt_delimage'] ?>" onclick="document.articlecontent.cimage_name.value='';document.articlecontent.cimage_id.value='0';this.blur();return false;"><img src="include/img/button/del_image_button.gif" alt="" width="15" height="15" border="0" /></a>
			      	<input name="cimage_id" type="hidden" value="<?php echo  isset($content["image_id"]) ? $content["image_id"] : '' ?>" /></td>
		        </tr>
		      </table></td>
			  </tr>
		  <tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="5" /></td>
</tr>
			<tr>
			  <td align="right" class="chatlist"><?php echo $BL['be_cnt_maxw'] ?>:&nbsp;</td>
			  <td valign="top"><table border="0" cellpadding="0" cellspacing="0" summary="">
			    <tr>
			      <td><input name="cimage_width" type="text" class="f11b" id="cimage_width" style="width: 50px;" size="3" maxlength="4" onkeyup="if(!parseInt(this.value)) this.value='';" value="<?php echo  isset($content["image_width"]) ? $content["image_width"] : '' ?>" /></td>
			      <td class="chatlist">&nbsp;&nbsp;<?php echo $BL['be_cnt_maxh'] ?>:&nbsp; </td>
			      <td><input name="cimage_height" type="text" class="f11b" id="cimage_height" style="width: 50px;" size="3" maxlength="4" onkeyup="if(!parseInt(this.value)) this.value='';" value="<?php echo  isset($content["image_height"]) ? $content["image_height"] : '' ?>" /></td>
			      <td class="chatlist">&nbsp;px&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
				  <td bgcolor="#E7E8EB">&nbsp;</td>
				  <td bgcolor="#E7E8EB"><input name="cimage_zoom" type="checkbox" id="cimage_zoom" value="1" <?php is_checked(1, isset($content["image_zoom"]) ? $content["image_zoom"] : 0); ?> /></td>
				  <td bgcolor="#E7E8EB" class="v10">&nbsp;<label for="cimage_zoom"><?php echo $BL['be_cnt_enlarge'] ?></label>&nbsp;</td>
				  <td bgcolor="#E7E8EB"><img src="include/img/leer.gif" alt="" width="6" height="15" /></td>
		        </tr>
		      </table></td>
			  </tr>
			<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="6" /></td>
</tr>
			<tr>
			  <td align="right" valign="top" class="chatlist"><img src="include/img/leer.gif" alt="" width="1" height="13" /><?php echo $BL['be_cnt_caption'] ?>:&nbsp;</td>
			  <td valign="top"><table border="0" cellpadding="0" cellspacing="0" summary="">
			      <tr>
			        <td valign="top"><textarea name="cimage_caption" cols="30" rows="4" class="f11" id="cimage_caption" style="width: 300px;"><?php echo  isset($content["image_caption"]) ? html_specialchars($content["image_caption"]) : '' ?></textarea></td>
			        <td valign="top"><img src="include/img/leer.gif" alt="" width="15" height="1" /></td>
			        <td valign="top"><?php
	
if(isset($content["image_hash"])) {
	$thumb_image = get_cached_image(
						array(	"target_ext"	=>	$content["image_ext"],
								"image_name"	=>	$content["image_hash"] . '.' . $content["image_ext"],
								"thumb_name"	=>	md5($content["image_hash"].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"])
        					  )
							);

	if($thumb_image != false) {
		echo '<img src="'.PHPWCMS_IMAGES . $thumb_image[0] .'" border="0" '.$thumb_image[3].'>';
	}
}

?></td>
		          </tr>
	          </table></td>
</tr>
<tr><td colspan="2"><img src="include/img/leer.gif" alt="" width="1" height="8" /></td>
</tr>
<tr><td colspan="2"><img src="include/img/lines/l538_70.gif" alt="" /></td>
</tr>