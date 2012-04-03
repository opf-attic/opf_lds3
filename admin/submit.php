<?php

if ($_SERVER["REQUEST_METHOD"] != "POST" && $_SERVER["REQUEST_METHOD"] != "DELETE") {
	header('HTTP/1.0 400 Bad Request');
	echo "Bad Request";
	exit();
}

ini_set('include_path','.:include/:panels/:library/');
require_once('connect.inc.php');
require_once('rest_processor.inc.php');
require_once('key_management.inc.php');
require_once('user_functions.inc.php');
require_once('http_functions.inc.php');
require_once('document_functions.inc.php');
require_once('expiry_functions.inc.php');

// Remove expired documents which were previously created

removeAllExpiredGUIDs();


// Authorisation Section 
$request_headers = apache_request_headers();
$authorized = isRequestAuthorized($request_headers);
$authentication_header = getAuthenticationHeader($request_headers);
$user_key = getUserKeyFromHeader($authentication_header);

if ( !$authorized ) {
        header("HTTP/1.0 403 Forbidden");
        echo "Access Denied";
        exit();
}

// Handle POST requests for existing documents 
if (strpos($_SERVER["REQUEST_URI"],"?") > 0) {
	$base_request_uri = substr($_SERVER["REQUEST_URI"],0,strpos($_SERVER["REQUEST_URI"],"?"));
} else {
	$base_request_uri = $_SERVER["REQUEST_URI"];
}

// Check request for valid and existing guid that can be updated
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

// TODO: Auth the request against the document to see if this user can edit/delete it. 

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {

	$file_path = getBlankDocumentRef();

} else {
	//Assume POST

	$stuff = file_get_contents("php://input");

	if ($stuff == "") {
		$file_path = getBlankDocumentRef();
		$expiry_date = mktime() + 86400;
	} else {
		$file_path = writeDocumentToFile($stuff);
	}

	$doc_uri = $_GET["Doc-URI"];

} 

// OPERATIONS SECTION 
if ($guid_uri) {
	list($error,$message) = updateDocument($file_path,$guid_uri,$user_key,$doc_uri,null);
	$location_uri = $guid_uri;
	if (!$error) {
		$error = 200;
	}
	if ($error > 199 && $error < 300) {
		$content_uri = $message;
	}
} else {
	list($error,$message) = addNewDocument($file_path,$user_key,$doc_uri,$expiry_date);
	if (!$error) {
		$error = 201;
	}
	if ($error > 199 && $error < 300) {
		$uris = explode(" : ",$message);
		$location_uri = $uris[0];
		$content_uri = $uris[1];
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

// OUTPUT SECTION 
ob_start();

outputHTTPHeader($error);

header("Cache-Control: no-cache, must-revalidate", true); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT", true);

if ($error < 200 || $error > 299) {
	echo $message;
	exit();
}

header("Link: <$location_uri>; rel=\"edit-iri\";");
header("Content-Location: $content_uri",true);
header("Content-Type: application/rdf+xml",true);
header("Content-Length: $content_length",true);
print_r($content);

ob_end_flush();

?>
