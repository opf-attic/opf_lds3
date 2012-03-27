<?php
	ini_set('include_path','.:include/:panels/:library/');
	include('header.php');
	require_once('key_management.inc.php');

	if ($_POST["description"] != "") {
		#ASSUME THIS WORKS
		$user_id = getUserID();
		$description = $_POST["description"];
		if (!$user_id) {
			output_message("An error occured (user_id unknown) while trying to create a new key pair, please try again later","error");
		} else {
			$key = createNewKeyPair($user_id,$description);
		}
		if (!$key) {
			output_message("An error occured (no key returned) while trying to create a new key pair, please try again later","error");
		} else {
			output_message("A new key pair has been created with key: $key","success");
		}
	}
?>
	<div style="padding-left: 43px; padding-right: 30px;">
<?php
	include('keys_list_panel.php');
?>

	</div>
	<nav class="topnav">
<?php
	include('operations_panel.html');
?>
	</nav>
	<div id="result">
<?php
	include('keys_create_panel.php');
?>
	</div>
<?php
	include('footer.php');
?>
