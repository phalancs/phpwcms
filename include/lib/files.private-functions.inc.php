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

//Funktionen zum Listen der privaten Dateien

function list_private($pid, $dbcon, $vor, $zieldatei, $userID, $cutID=0, $show_thumb=1, $phpwcms) {
	$cutID = intval($cutID);
	$klapp = $_SESSION["klapp"];
	$pid = intval($pid);
	$sql = "SELECT * FROM ".DB_PREPEND."phpwcms_file WHERE ".
		   "f_pid=".intval($pid)." AND ".
		   "f_uid=".intval($userID)." AND ".
		   "f_kid=0 AND f_trash=0 ORDER BY f_sort, f_name";
	$result = mysql_query($sql, $dbcon);
	while($row = mysql_fetch_array($result)) {
		
		$dirname = html_specialchars($row["f_name"]);
		
		//Ermitteln des Aufklappwertes
		$klapp_status = isset($klapp[$row["f_id"]]) ? true_false($klapp[$row["f_id"]]) : 1;
		
		//Ermitteln, ob �berhaupt abh�ngige Dateien/Ordner existieren
		$count_sql = "SELECT COUNT(f_id) FROM ".DB_PREPEND."phpwcms_file WHERE ".
					 "f_pid=".$row["f_id"]." AND ".
					 "f_uid=".intval($userID)." AND ".
					 "f_trash=0 LIMIT 1";
		if($count_result = mysql_query($count_sql, $dbcon)) {
			if($count_row = mysql_fetch_row($count_result)) {
				$count = '<img src="include/img/leer.gif" width="2" height="1">'.
						 '<a href="'.$zieldatei."&amp;klapp=".$row["f_id"].
						 '%7C'.$klapp_status.'">'.on_off($klapp_status, $dirname, 0)."</a>"; // | = %7C
				$count_wert = $count_row[0];
			}
			mysql_free_result($count_result);
		}
		
		//Aufbau der Zeile
		echo '<tr bgcolor="#EBF2F4"><td colspan="2"><img src="include/img/leer.gif" height="1" width="1" alt="" /></td></tr>'."\n"; //Abstand vor
		echo "<tr bgcolor=\"#EBF2F4\">\n"; //Einleitung Tabellenzeile
		echo "<td width=\"438\" class=\"msglist\">"; //Einleiten der Tabellenzelle
		echo $count."<img src=\"include/img/leer.gif\" height=\"1\" width=\"".($vor+6)."\" border=\"0\">";
		
		// Gallery status
		switch($row["f_gallerystatus"]) {
			
			case 2:		// gallery root dir
						echo '<img src="include/img/icons/folder_galleryroot.gif" border="0" alt="'.$GLOBALS['BL']['be_gallery_root'].'" title="'.$GLOBALS['BL']['be_gallery_root'].'" />';
						break;
			
			case 3:		// gallery subdir
						echo '<img src="include/img/icons/folder_gallerysub.gif" border="0" alt="'.$GLOBALS['BL']['be_gallery_directory'].'" title="'.$GLOBALS['BL']['be_gallery_directory'].'" />';
						break;
			
			default:	echo "<img src=\"include/img/icons/folder_zu.gif\" border=\"0\" alt=\"\" />";
		}
		
		echo "<img src=\"include/img/leer.gif\" height=\"1\" width=\"5\"><strong>".$dirname; //Zellinhalt 1. Spalte Fortsetzung
		echo "</strong></td>\n"; //Schlie�en Zelle 1. Spalte
		//Zelle 2. Spalte - vorgesehen f�r Buttons/Tasten Edit etc.
		echo "<td width=\"100\" align=\"right\" class=\"msglist\">";
		//Button zum Uploaden einer Datei in dieses Verzeichnisses
		echo "<a href=\"".$zieldatei."&amp;upload=".$row["f_id"]."\" title=\"".$GLOBALS['BL']['be_fprivfunc_upload'].": ".$dirname."\">";
		echo "<img src=\"include/img/button/upload_13x13.gif\" border=\"0\" alt=\"\" /></a>";		
		if(!$cutID) { //Button zum Erzeugen eines Neuen Unterverzeichnisses
			echo "<a href=\"".$zieldatei."&amp;mkdir=".$row["f_id"]."\" title=\"".$GLOBALS['BL']['be_fprivfunc_makenew'].": ".$dirname."\">";
			echo "<img src=\"include/img/button/add_13x13.gif\" border=\"0\" alt=\"\" /></a>";
		} else {  //Button zum Einf�gen der Clipboard-Datei in das Verzeichnis
			echo "<a href=\"include/actions/file.php?paste=".$cutID.'%7C'.$row["f_id"].
				 "\" title=\"".$GLOBALS['BL']['be_fprivfunc_paste'].": ".$dirname."\">";
			echo "<img src=\"include/img/button/paste_13x13.gif\" border=\"0\" alt=\"\" /></a>";
		}
		//Button zum Bearbeiten des Verzeichnisses
		echo "<a href=\"".$zieldatei."&amp;editdir=".$row["f_id"]."\" title=\"".$GLOBALS['BL']['be_fprivfunc_edit'].": ".$dirname."\">";
		echo "<img src=\"include/img/button/edit_22x13.gif\" border=\"0\" alt=\"\" /></a>";
		//Button zum Umschalten zwischen Aktiv/Inaktiv
		echo "<a href=\"include/actions/file.php?aktiv=".$row["f_id"].'%7C'.true_false($row["f_aktiv"]).
			 "\" title=\"".$GLOBALS['BL']['be_fprivfunc_cactive'].": ".$dirname."\">";
		echo "<img src=\"include/img/button/aktiv_12x13_".$row["f_aktiv"].".gif\" border=\"0\" alt=\"\" /></a>";
		//Button zum Umschalten zwischen Public/Non-Public
		echo "<a href=\"include/actions/file.php?public=".$row["f_id"].'%7C'.true_false($row["f_public"]).
			 "\" title=\"".$GLOBALS['BL']['be_fprivfunc_cpublic'].": ".$dirname."\">";
		echo "<img src=\"include/img/button/public_12x13_".$row["f_public"].".gif\" border=\"0\" alt=\"\" /></a>";
		echo "<img src=\"include/img/leer.gif\" width=\"5\" height=\"1\">"; //Spacer
		//Button zum L�schen des Verzeichnisses, wenn leer
		if(!$count_wert) {
			echo "<a href=\"include/actions/file.php?delete=".$row["f_id"].'%7C'."9".
				 "\" title=\"".$GLOBALS['BL']['be_fprivfunc_deldir'].": ".$dirname."\" onclick=\"return confirm('".
				 $GLOBALS['BL']['be_fprivfunc_jsdeldir'] ." \\n[".$dirname."]? ');\">";
			echo "<img src=\"include/img/button/trash_13x13_1.gif\" border=\"0\" alt=\"\" /></a>";
		} else {
			echo "<img src=\"include/img/button/trash_13x13_0.gif\" title=\"";
			echo str_replace('{VAL}', $dirname, $GLOBALS['BL']['be_fprivfunc_notempty']).'" border="0" alt="" />';
		}
		echo "<img src=\"include/img/leer.gif\" width=\"2\" height=\"1\" border=\"0\" alt=\"\" />"; //Spacer
		echo "</td>\n"; 
		echo "</tr>\n"; //Abschluss Tabellenzeile
		
		//Aufbau trennende Tabellen-Zeile
		echo "<tr bgcolor=\"#EBF2F4\"><td colspan=\"2\"><img src=\"include/img/leer.gif\" border=\"0\" alt=\"\" /></td></tr>\n"; //Abstand nach
		echo "<tr><td colspan=\"2\"><img src=\"include/img/leer.gif\" border=\"0\" alt=\"\" /></td></tr>\n"; //Trennlinie<img src='include/img/lines/line-lightgrey-dotted-538.gif'>
		
		//Weiter, wenn Unterstruktur
		if(!$klapp_status && $count_wert) { //$vor."<img src='include/img/leer.gif' height=1 width=18 border=0>"
			list_private($row["f_id"], $dbcon, $vor+18, $zieldatei, $userID, $cutID, $show_thumb, $phpwcms);
			
			//Listing eventuell im Verzeichnis enthaltener Dateien
			$file_sql = "SELECT * FROM ".DB_PREPEND."phpwcms_file WHERE f_pid=".$row["f_id"].
						" AND f_uid=".$userID." AND f_kid=1 AND f_trash=0 ORDER BY f_sort, f_name";
			if($file_result = mysql_query($file_sql, $dbcon) or die ("error while listing files")) {
				$file_durchlauf = 0;
				while($file_row = mysql_fetch_array($file_result)) {
					$filename = html_specialchars($file_row["f_name"]);
					if(!$file_durchlauf) { //Aufbau der Zeile zum Einflie�en der Filelisten-Tavbelle
						echo "<tr bgcolor=\"#F5F8F9\"><td colspan=\"2\"><table width=\"538\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n"; 
						echo "<!-- start file list: private-functions //-->\n";
					} else {
						echo "<tr bgcolor=\"#FFFFFF\"><td colspan=\"5\"><img src=\"include/img/leer.gif\" border=\"0\" alt=\"\" /></td></tr>\n";
					}
					echo "<tr>\n";
					echo "<td width=\"".($vor+37)."\" class=\"msglist\"><img src=\"include/img/leer.gif\" height=\"1\" width=\"".($vor+37)."\" border=\"0\" alt=\"\" /></td>\n";
					echo "<td width=\"13\" class=\"msglist\">";
					echo "<img src=\"include/img/icons/small_".extimg($file_row["f_ext"])."\" border=\"0\" ";
					echo 'onmouseover="Tip(\'ID: '.$file_row["f_id"].'&lt;br&gt;Sort: '.$file_row["f_sort"];
					echo '&lt;br&gt;Name: '.html_specialchars($file_row["f_name"]);
					if($file_row["f_copyright"]) {
						echo '&lt;br&gt;&copy;: '.html_specialchars($file_row["f_copyright"]);
					}
					echo '\');" onmouseout="UnTip()" alt=""';
					echo " /></td>\n";
					echo "<td width=\"".(388-$vor)."\" class=\"msglist\"><img src=\"include/img/leer.gif\" height=\"1\" width=\"5\" border=\"0\" alt=\"\" />";
					echo "<a href=\"fileinfo.php?fid=".$file_row["f_id"];
					echo "\" target=\"_blank\" onclick=\"flevPopupLink(this.href,'filedetail','scrollbars=yes,resizable=yes,width=500,height=400',1);return document.MM_returnValue;\">";
					echo $filename."</a></td>\n";
					//Aufbauen Buttonleiste f�r jeweilige Datei
					echo "<td width=\"100\" align=\"right\" class=\"msglist\">";
					//Button zum Downloaden der Datei
					echo "<a href=\"include/actions/download.php?dl=".$file_row["f_id"].
						 "\"  target=\"_blank\" title=\"".$GLOBALS['BL']['be_fprivfunc_dlfile'].": ".$filename."\">".
						 "<img src=\"include/img/button/download_disc.gif\" border=\"0\" alt=\"\" /></a>"; //target='_blank'
					//Button zum Erzeugen eines Neuen Unterverzeichnisses
					if($cutID == $file_row["f_id"]) {
						echo "<img src=\"include/img/button/cut_13x13_1.gif\" border=\"0\" title=\"".$GLOBALS['BL']['be_fprivfunc_clipfile'].": ".$filename."\" alt=\"\" />";
					} else {
						echo "<a href=\"".$zieldatei."&amp;cut=".$file_row["f_id"]."\" title=\"".$GLOBALS['BL']['be_fprivfunc_cutfile'].": ".$filename."\">";
						echo "<img src=\"include/img/button/cut_13x13_0.gif\" border=\"0\" alt=\"\" /></a>";
					}
					//Button zum Bearbeiten der Dateiinformationn
					echo "<a href=\"".$zieldatei."&amp;editfile=".$file_row["f_id"]."\" title=\"".$GLOBALS['BL']['be_fprivfunc_editfile'].": ".$filename."\">";
					echo "<img src=\"include/img/button/edit_22x13.gif\" border=\"0\" alt=\"\" /></a>";					
					//Button zum Umschalten zwischen Aktiv/Inaktiv
					echo "<a href=\"include/actions/file.php?aktiv=".$file_row["f_id"].'%7C'.true_false($file_row["f_aktiv"]).
			 			 "\" title=\"".$GLOBALS['BL']['be_fprivfunc_cactivefile'].": ".$filename."\">";
					echo "<img src=\"include/img/button/aktiv_12x13_".$file_row["f_aktiv"].".gif\" border=\"0\" alt=\"\" /></a>";
					//Button zum Umschalten zwischen Public/Non-Public
					echo "<a href=\"include/actions/file.php?public=".$file_row["f_id"].'%7C'.true_false($file_row["f_public"]).
			 			 "\" title=\"".$GLOBALS['BL']['be_fprivfunc_cpublicfile'].": ".$filename."\">";
					echo "<img src=\"include/img/button/public_12x13_".$file_row["f_public"].".gif\" border=\"0\" alt=\"\" /></a>";
					echo "<img src=\"include/img/leer.gif\" width=\"5\" height=\"1\">"; //Spacer					
					//Button zum L�schen der Datei
					echo "<a href=\"include/actions/file.php?trash=".$file_row["f_id"].'%7C'."1".
				 		 "\" title=\"".$GLOBALS['BL']['be_fprivfunc_movetrash'].": ".$filename."\" onclick=\"return confirm('".
						 $GLOBALS['BL']['be_fprivfunc_jsmovetrash1']."\\n[".$filename."]\\n".$GLOBALS['BL']['be_fprivfunc_jsmovetrash2'].
						 "');\">".
						 "<img src=\"include/img/button/trash_13x13_1.gif\" border=\"0\" alt=\"\" /></a>";
					echo "<img src=\"include/img/leer.gif\" width=\"2\" height=\"1\" border=\"0\" alt=\"\" />"; //Spacer
					echo "</td>\n";
					//Ende Aufbau
					echo "</tr>\n";
					
					
					if($_SESSION["wcs_user_thumb"]) {
		
						// now try to get existing thumbnails or if not exists 
						// build new based on default thumbnail listing sizes
			
						// build thumbnail image name
						$thumb_image = get_cached_image(
			 					array(	"target_ext"	=>	$file_row["f_ext"],
										"image_name"	=>	$file_row["f_hash"] . '.' . $file_row["f_ext"],
										"thumb_name"	=>	md5($file_row["f_hash"].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"])
        							  )
								);

						if($thumb_image != false) {
						
						
							echo "<tr>\n";
							echo "<td width=\"".($vor+37)."\"><img src=\"include/img/leer.gif\" height=\"1\" width=\"".($vor+37)."\" border=\"0\" alt=\"\" /></td>\n";
							echo "<td width=\"13\"><img src=\"include/img/leer.gif\" height=\"1\" width=\"1\" border=\"0\" alt=\"\" /></td>\n<td width=\"";
							echo (388-$vor)."\"><img src=\"include/img/leer.gif\" height=\"1\" width=\"6\" border=\"0\" alt=\"\" /><a href=\"fileinfo.php?fid=";
							echo $file_row["f_id"]."\" target=\"_blank\" onclick=\"flevPopupLink(this.href,'filedetail','scrollbars=";
							echo "yes,resizable=yes,width=500,height=400',1); return document.MM_returnValue;\">";
							echo '<img src="'.PHPWCMS_IMAGES . $thumb_image[0] .'" border="0" '.$thumb_image[3].' ';
							echo 'onmouseover="Tip(\'ID: '.$file_row["f_id"].'&lt;br&gt;Sort: '.$file_row["f_sort"];
							echo '&lt;br&gt;Name: '.html_specialchars($file_row["f_name"]);
							if($file_row["f_copyright"]) {
								echo '&lt;br&gt;&copy;: '.html_specialchars($file_row["f_copyright"]);
							}
							echo '\');" onmouseout="UnTip()" alt=""';
							echo " /></a></td>\n";
							echo "<td width=\"100\"><img src=\"include/img/leer.gif\" border=\"0\" alt=\"\" /></td>\n</tr>\n";
							echo "<tr><td colspan=\"4\"><img src=\"include/img/leer.gif\" height=\"2\" width=\"1\" border=\"0\" alt=\"\" /></td>\n</tr>\n";
				
						}
			
					}
					
			
					$file_durchlauf++;
				}
				if($file_durchlauf) { //Abschluss der Filelisten-Tabelle
					echo "</table>\n<!-- end file list: private-functions //-->\n";
					echo "<tr><td colspan=\"2\"><img src=\"include/img/leer.gif\" border=\"0\" alt=\"\" /></td></tr>\n";
				}
			} //Ende Liste Dateien
		}
		
		//Zaehler mitf�hren
		$_SESSION["list_zaehler"]++;
	}
	mysql_free_result($result);
	return $vor;
}

function true_false($wert) {
	//Wechselt den Wahr/Falsch wert zum Gegenteil: 1=>0 und 0=>1
	return (intval($wert)) ? 0 : 1;
}

function on_off($wert, $string, $art = 1) {
	//Erzeugt das Status-Zeichen f�r Klapp-Auf/Zu
	//Wenn Art = 1 dann als Zeichen, ansonsten als Bild
	if($wert) {
		return ($art == 1) ? "+" : "<img src=\"include/img/symbols/klapp_zu.gif\" title=\"".$GLOBALS['BL']['be_fprivfunc_opendir'].": ".$string."\" border=\"0\" alt=\"\" />";
	} else {
		return ($art == 1) ? "-" : "<img src=\"include/img/symbols/klapp_auf.gif\" title=\"".$GLOBALS['BL']['be_fprivfunc_closedir'].": ".$string."\" border=\"0\" alt=\"\" />";
	}
}
?>