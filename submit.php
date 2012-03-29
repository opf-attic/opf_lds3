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

if ( !$authorized ) {
        header("HTTP/1.0 403 Forbidden");
        echo "Access Denied";
        exit();
}

$stuff = file_get_contents("php://input");
$file_path = tempnam(sys_get_temp_dir(),'RDF_Admin');
$handle = fopen($file_path,"w");
fwrite($handle,file_get_contents("php://input"));
fclose($handle);

list($error,$message) = addNewDocument($file_path);

if (!$error) {
	$uris = explode(" : ",$message);
	$location_uri = $uris[0];
	$content_uri = $uris[1];
	$error = 201;
}

$content = "";
$handle = fopen($content_uri.".rdf","r");
if (!$handle) {
	$error = 500;	
	$message = "Error while retrieving content. Please contact system administrator";
}
while(!feof($handle)) {
	$content .= fgets($handle);
}
fclose($handle);

$content_length = strlen($content);

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
