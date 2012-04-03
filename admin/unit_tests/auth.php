<?php
ini_set('include_path','.:../:../include/:../library/');
require_once('rest_processor.inc.php');
require_once('key_management.inc.php');
require_once('user_functions.inc.php');
require_once('connect.inc.php');

$request_headers = apache_request_headers();
$authorized = isRequestAuthorized($request_headers);

if ( $authorized ) {
	header("HTTP/1.0 200 OK");
	echo "Success";
	exit();
} else {
	header("HTTP/1.0 403 Forbidden");
	echo "Access Denied";
	exit();
} 

?>
