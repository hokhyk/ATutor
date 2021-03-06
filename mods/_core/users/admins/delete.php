<?php
/****************************************************************************/
/* ATutor																	*/
/****************************************************************************/
/* Copyright (c) 2002-2010                                                  */
/* Inclusive Design Institute                                               */
/* http://atutor.ca															*/
/*																			*/
/* This program is free software. You can redistribute it and/or			*/
/* modify it under the terms of the GNU General Public License				*/
/* as published by the Free Software Foundation.							*/
/****************************************************************************/
// $Id$

define('AT_INCLUDE_PATH', '../../../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
admin_authenticate(AT_ADMIN_PRIV_ADMIN);

$_GET['login'] = $addslashes($_GET['login']);

if (isset($_POST['submit_no'])) {
	$msg->addFeedback('CANCELLED');
	header('Location: index.php');
	exit;
} else if (isset($_POST['submit_yes'])) {
	check_csrf_token();

	$_POST['login'] = $addslashes($_POST['login']);

	$sql = "DELETE FROM %sadmins WHERE login='%s'";
	$result = queryDB($sql, array(TABLE_PREFIX, $_POST['login']));
    global $sqlout;
	write_to_log(AT_ADMIN_LOG_DELETE, 'admins', $result, $sqlout);

	$msg->addFeedback('ADMIN_DELETED');
	header('Location: index.php');
	exit;
}
?>
<?php require(AT_INCLUDE_PATH.'header.inc.php'); ?>
<?php

if (!strcasecmp($_GET['login'], $_SESSION['login'])) {
	$msg->addError('CANNOT_DELETE_OWN_ACCOUNT');
	$msg->printErrors();
	require(AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}

$sql = "SELECT * FROM %sadmins WHERE login='%s'";
$row_admins = queryDB($sql, array(TABLE_PREFIX, $_GET['login']), TRUE);

if(count($row_admins) == 0){
	echo _AT('no_user_found');
} else {
	$hidden_vars['login'] = $_GET['login'];
	$hidden_vars['csrftoken'] = $_SESSION['token'];
	$confirm = array('DELETE_ADMIN', $row_admins['login']);
	$msg->addConfirm($confirm, $hidden_vars);
	$msg->printConfirm();
}
?>
<?php require(AT_INCLUDE_PATH.'footer.inc.php'); ?>