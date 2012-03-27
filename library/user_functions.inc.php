<?php

	require_once('connect.inc.php');

	function getUserStatus() {

		$user_name = $_SESSION["user_name"];
		$user_email = $_SESSION["user_email"];
		$user_pic_path = $_SESSION["user_pic"];
		
		if (!userExists($user_email)) {
			addUser($user_name,$user_email,$user_pic_path);
		} 
		
		return getUserAccountStatus($user_email);

	}

	function userExists($user_email) {
		$query = "select * from Users where Users.email='$user_email';";
		$res = mysql_query($query);
		$num_rows = mysql_num_rows($res);
		if ($num_rows > 0) {
			return true;
		}
		return false;
	}

	function getUserAccountStatus($user_email) {
		$query = "select enabled from Users where Users.email='$user_email';";
		$res = mysql_query($query);
		if (mysql_num_rows($res) < 1) {
			return "unregistered";
		}
		$row = mysql_fetch_row($res);
		$status = $row[0];
		switch ($status) {
			case -1:
			   return "banned";
			case 0:
			   return "inwaiting";
			case 1:
			   return "active";
		}
	}

	function addUser($user_name,$user_email,$user_pic_path) {
		$user_status = getDefaultUserStatus();

		$query = "INSERT into Users set name='$user_name', pic_path='$user_pic_path', email='$user_email', enabled=$user_status;";
		$res = mysql_query($query);
	}

	function getDefaultUserStatus() {
		require_once('config.php');
		if ($moderate_accounts) {
			return 0;
		} else {
			return 1;
		}
		return 1;
	}

?>
