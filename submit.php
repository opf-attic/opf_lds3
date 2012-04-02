<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
	header('HTTP/1.0 400 Bad Request');
	echo "Bad Request";
	exit();
}

ini_set('include_path','.:include/:panels/:library/');
require_once('rest_processor.inc.php');
require_once('key_management.inc.php');
require_once('user_functions.inc.php');
require_once('http_functions.inc.php');
require_once('document_functions.inc.php');
require_once('connect.inc.php');

$request_headers = apache_request_headers();
$authorized = isRequestAuthorized($request_headers);
$authentication_header = getAuthenticationHeader($request_headers);
$user_key = getUserKeyFromHeader($authentication_header);

if ( !$authorized ) {
        header("HTTP/1.0 403 Forbidden");
        echo "Access Denied";
        exit();
}

#Handle POST requests for existing documents 
if (strpos($_SERVER["REQUEST_URI"],"?") > 0) {
	$base_request_uri = substr($_SERVER["REQUEST_URI"],0,strpos($_SERVER["REQUEST_URI"],"?"));
} else {
	$base_request_uri = $_SERVER["REQUEST_URI"];
}

if ($base_request_uri != $_SERVER["PHP_SELF"]) {
	include('config.php');
	$requested_document = "http://" . $_SERVER["HTTP_HOST"] . $base_request_uri;
	if (substr($requested_document,strlen($requested_document)-1,strlen($requested_document)) == "/") {
		$requested_document = substr($requested_document,0,strlen($requested_document)-1);
	}
	if (substr($requested_document,0,strlen($http_doc_prefix)) != $http_doc_prefix) {
		outputHTTPHeader(400);
		echo "Bad Request, the requested URI cannot be edited";
		exit();
	}
	$input_guid = substr($requested_document,strlen($http_doc_prefix),strlen($requested_document));
	require_once('functions.inc.php');
	if (!isValidGUID($input_guid)) {
		outputHTTPHeader(400);
		echo "Bad Request, the requested URI cannot be edited, invalid GUID";
		exit();
	}
	$local_path = getLocalFromGUID($input_guid);
	if (!is_dir($local_path)) {
		outputHTTPHeader(404);
		echo "The requested URI, although valid, doesn't exist, thus can't be edited.";
		exit();
	}
	$guid_uri = $requested_document;
}

$stuff = file_get_contents("php://input");
$file_path = tempnam(sys_get_temp_dir(),'RDF_Admin');
$handle = fopen($file_path,"w");
fwrite($handle,file_get_contents("php://input"));
fclose($handle);

$doc_uri = $_GET["Doc-URI"];

if ($guid_uri) {
	list($error,$message) = updateDocument($file_path,$guid_uri,$user_key,$doc_uri);
	$location_uri = $guid_uri;
	if (!$error) {
		$content_uri = $message;
		$error = 200;
	}
} else {
	list($error,$message) = addNewDocument($file_path,$user_key,$doc_uri);
	if (!$error) {
		$uris = explode(" : ",$message);
		$location_uri = $uris[0];
		$content_uri = $uris[1];
		$error = 201;
	}
}

$content = "";
$handle = fopen($content_uri.".rdf","r");
if (!$handle) {
	$error = 500;	
	$message = "Error while retrieving content. Please contact system administrator";
} else {
	while(!feof($handle)) {
		$content .= fgets($handle);
	}
	fclose($handle);
	$content_length = strlen($content);
}

ob_start();

outputHTTPHeader($error);

header("Cache-Control: no-cache, must-revalidate", true); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT", true);

if ($error != 200 && $error != 201) {
	echo $message;
	exit();
}

#header("Location: $content_uri",true,$error);
header("Link: <$location_uri>; rel=\"edit-iri\";");
header("Content-Location: $content_uri",true);
header("Content-Type: application/rdf+xml",true);
header("Content-Length: $content_length",true);
print_r($content);

ob_end_flush();

?>
