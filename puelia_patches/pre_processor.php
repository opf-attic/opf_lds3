<?php
if ($_SERVER["REQUEST_URI"] == "/admin/" || $_SERVER["REQUEST_URI"] == "/admin") {
	header("Location: http://" . $_SERVER['HTTP_HOST'] . "/admin/index.php");
	exit();
}
if ($_SERVER["REQUEST_URI"] == "/sparql/" || $_SERVER["REQUEST_URI"] == "/sparql") {
	header("Location: http://" . $_SERVER['HTTP_HOST'] . "/sparql/index.php");
        exit();
}

ini_set('include_path','.:../admin/:../admin/include/:../admin/library/');
include_once('config.php');
include_once('functions.inc.php');

$length = strlen($http_doc_prefix . "CA/7D/A10B/FBA0-4A01-B62B-B4737B8AA180");
$requested_uri = "http://" . $base_domain . $_SERVER["REQUEST_URI"];
if (substr($requested_uri,strlen($requested_uri)-1,strlen($requested_uri)) == "/") {
	$requested_uri = substr($requested_uri,0,strlen($requested_uri)-1);
}

if (substr($requested_uri, 0, strlen($http_doc_prefix)) == $http_doc_prefix && strlen($requested_uri) == $length) {
	$uri = getAbsoluteLatestVersionURI($requested_uri . "/latest");
	$uri = substr($uri,0,-4);
	header("HTTP/1.1 303 See Other");
	header("Location: $uri");
        exit();
}
?>
