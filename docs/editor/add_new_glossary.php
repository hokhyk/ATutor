<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2003 by Greg Gay & Joel Kronenberg        */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/


	define('AT_INCLUDE_PATH', '../include/');
	require(AT_INCLUDE_PATH.'vitals.inc.php');
	require(AT_INCLUDE_PATH.'lib/format_content.inc.php');

	if ($_POST['cancel']) {
		if ($_POST['pcid'] != '') {
			Header('Location: ../index.php?cid='.$_POST['pcid'].SEP.'f='.AT_FEEDBACK_CANCELLED);
			exit;
		}

		Header('Location: ../glossary/index.php?f='.AT_FEEDBACK_CANCELLED);
		exit;
	}

	if ($_POST['submit']) {
		$num_terms = intval($_POST['num_terms']);

		for ($i=0; $i<$num_terms; $i++) {
			$_POST['word'][$i]			= str_replace('<', '&lt;', trim($_POST['word'][$i]));
			$_POST['definition'][$i]	= str_replace('<', '&lt;', trim($_POST['definition'][$i]));
			$_POST['ignore'][$i]		= intval($_POST['ignore'][$i]);

			if ($_POST['ignore'][$i] == '') {
				if ($_POST['word'][$i] == '') {
					$errors[]=AT_ERROR_TERM_EMPTY;
				}

				if ($_POST['definition'][$i] == '') {
					$errors[]=AT_ERROR_DEFINITION_EMPTY;
				}

				if ($terms_sql != '') {
					$terms_sql .= ', ';
				}

				$_POST['related_term'][$i] = intval($_POST['related_term'][$i]);

				/* for each item check if it exists: */

				if ($glossary[$_POST[word][$i]] != '' ) {
					$errors[] = array(AT_ERROR_TERM_EXISTS, $_POST[word][$i]);
				}

				$terms_sql .= "(0, $_SESSION[course_id], '{$_POST[word][$i]}', '{$_POST[definition][$i]}', {$_POST[related_term][$i]})";
			}
		}

		if ($errors == '') {
			$sql = "INSERT INTO ".TABLE_PREFIX."glossary VALUES $terms_sql";
			$result = mysql_query($sql);

			if ($_POST['pcid'] != '') {
				Header('Location: ../index.php?cid='.$_POST['pcid'].SEP.'f='.urlencode_feedback(AT_FEEDBACK_CONTENT_UPDATED));
				exit;
			}
			Header('Location: ../glossary/?f='.urlencode_feedback(AT_FEEDBACK_GLOS_UPDATED));
			exit;
		}
		$_GET['pcid'] = $_POST['pcid'];
	}

	$_section[0][0] = _AT('tools');
	$_section[0][1] = 'tools/';
	$_section[1][0] = _AT('glossary');
	$_section[1][1] = 'glossary/';
	$_section[2][0] = _AT('add_glossary');

	$onload = 'onload="document.form.title0.focus()"';

	unset($word);

	if (isset($_GET['pid'])) {
		$pid = intval($_GET['pid']);
	} else {
		$pid = intval($_POST['pid']);
	}
	$top_level = $contentManager->getContent($pid);


	if ($_GET['pcid'] != '') {
		/* we're entering terms from a content page */
		$result =& $contentManager->getContentPage($_GET['pcid']);

		if ($row =& mysql_fetch_array($result)) {
			$matches = find_terms(&$row['text']);
			$num_terms = count($matches[0]);
			$matches = $matches[0];
			$word = str_replace(array('[?]', '[/?]'), '', $matches);
			$word = str_replace("\n", ' ', $word);
		
			$found = true;
			for ($i=0; $i<$num_terms; $i++) {
				$word[$i] = trim($word[$i]);
				if ($glossary[$word[$i]] == '') {
					$found = false;
				}
			}

			if ($found) {
				/* there are no new terms found. redirect back to the content */
				Header('Location: ../index.php?cid='.$_GET['pcid'].SEP.'f='.urlencode_feedback(AT_FEEDBACK_CONTENT_UPDATED));
				exit;

			}
		}
	} else {
		$num_terms = 1;
	}

	require(AT_INCLUDE_PATH.'header.inc.php');
	
		echo '<h2>';
	if ($_SESSION['prefs'][PREF_CONTENT_ICONS] != 2) {
		echo '<a href="tools/?g=11"><img src="images/icons/default/square-large-tools.gif" class="menuimageh2" border="0" vspace="2" width="41" height="40" alt="" /></a>';
	}
	if ($_SESSION['prefs'][PREF_CONTENT_ICONS] != 1) {
		echo ' <a href="tools/?g=11">'._AT('tools').'</a>';
	}
	echo '</h2>';

	echo '<h3>';
	if ($_SESSION['prefs'][PREF_CONTENT_ICONS] != 2) {
		echo '&nbsp;<img src="images/icons/default/glossary-large.gif" class="menuimageh3" width="42" height="38" alt="" /> ';
	}
	if ($_SESSION['prefs'][PREF_CONTENT_ICONS] != 1) {
		echo _AT('add_glossary');
	}
	echo '</h3>';
?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form">
	<input type="hidden" name="num_terms" value="<?php echo $num_terms; ?>" />
	<input type="hidden" name="pcid" value="<?php echo $_GET['pcid']; ?>" />
	<table cellspacing="1" cellpadding="0" border="0" class="bodyline" summary="" align="center">
<?php
	for ($i=0;$i<$num_terms;$i++) {
		if ($glossary[$word[$i]] != '') {
			echo '<input type="hidden" name="ignore['.$i.']" value="1" />';
			continue;
		}
		for ($j=0;$j<$i;$j++) {
			if ($word[$j] == $word[$i]) {
				echo '<input type="hidden" name="ignore['.$i.']" value="1" />';
				continue 2;
			}
		}

		if ($word[$i] == '') {
			$word[$i] = ContentManager::cleanOutput($_POST['word'][$i]);
		}
?>
		<tr>
			<th colspan="2" class="left"><img src="images/pen2.gif" border="0" class="menuimage12" alt="<?php echo _AT('editor_on'); ?>" title="<?php echo _AT('editor_on'); ?>" height="14" width="16" /> <?php echo _AT('add_glossary');  ?></th>
		</tr>
		<tr>
			<td align="right" class="row1"><?php print_popup_help(AT_HELP_GLOSSARY_MINI);?><b><label for="title<?php echo $i; ?>"><?php echo _AT('glossary_term');  ?>:</label></b></td>
			<td class="row1"><input type="text" name="word[<?php echo $i; ?>]" size="30" class="formfield" value="<?php echo trim($word[$i]); ?>" id="title<?php echo $i; ?>" /><?php
			
			if ($_GET['pcid'] != '') { 
				echo '<input type="checkbox" name="ignore['.$i.']" value="1" id="ig'.$i.'" /><label for="ig'.$i.'">Ignore this term</label>.';	
			}

			?></td>
		</tr>
		<tr><td height="1" class="row2" colspan="2"></td></tr>
		<tr>
			<td valign="top" align="right" class="row1"><b><label for="body<?php echo $i; ?>"><?php echo _AT('glossary_definition');  ?>:</label></b></td>
			<td class="row1">
				<textarea name="definition[<?php echo $i; ?>]" class="formfield" cols="55" rows="7" id="body<?php echo $i; ?>"><?php echo ContentManager::cleanOutput($_POST['definition'][$i]); ?></textarea><br /><br /></td>
		</tr>
		<tr><td height="1" class="row2" colspan="2"></td></tr>
		<tr>
			<td valign="top" align="right" class="row1"><b><?php echo _AT('glossary_related');  ?>:</b></td>
			<td class="row1"><?php
				
					$sql = "SELECT * FROM ".TABLE_PREFIX."glossary WHERE course_id=$_SESSION[course_id] ORDER BY word";
					$result = mysql_query($sql);
					if ($row_g = mysql_fetch_array($result)) {
						echo '<select name="related_term['.$i.']">';
						echo '<option value="0"></option>';
						do {
							echo '<option value="'.$row_g[word_id].'">'.$row_g[word].'</option>';
						} while ($row_g = mysql_fetch_array($result));
						echo '</select>';
					} else {
						echo _AT('none_available');
					}

				?><br /><br /></td>
		</tr>
		<tr><td height="1" class="row2" colspan="2"></td></tr>
		<tr><td height="1" class="row2" colspan="2"></td></tr>

	<?php } ?>
		<tr>
			<td colspan="2" align="center" class="row1"><br /><input type="submit" name="submit" value="<?php echo _AT('add_term'); ?><?php
			if ($num_terms > 1) {
				echo 's';
			}
			?>[Alt-s]" class="button" accesskey="s" /> - <input type="submit" name="cancel" class="button" value="<?php echo _AT('cancel'); ?>" /></td>
		</tr>
		</table>
	</form>
<?php
	require(AT_INCLUDE_PATH.'footer.inc.php');
?>
