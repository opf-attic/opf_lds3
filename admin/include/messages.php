<?php

require_once('display_functions.inc.php');

if (isset($_GET["extension"])) {
	output_message("The uploaded file did not have a valid extension of RDF, please try again.","error");
} 

if (isset($_GET["no_subjects"])) {
	output_message("No data found in uploaded document. Failed to Process.","error");
}

if ($_SESSION["user_status"] == "banned") {
	output_message("Your user account is not allowed to use this system","error");
}
if ($_SESSION["user_status"] == "inwaiting") {
	output_message("Your account is yet to be enabled by a system administrator","error");
}
if ($_SESSION["user_status"] != "active" and $_SESSION["user_status"] != "") {
	output_message("An error occured while trying to log-in, please try again later","error");
}
/*
if ($_SESSION["user_status"] == "active") {
	output_message("Account Activated","success");
}
*/
?>
