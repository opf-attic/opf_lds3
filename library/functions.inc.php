<?php


function getGUID() {
	include_once('config.php');
	$exists = 1;
	while ($exists) {
		$guid = GenerateGUID();
		$path = $local_doc_prefix . $guid;
		if (!(is_dir($path))) {
			return $guid;
		}
	}
}

function GenerateGUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function isValidGUID($guid) {
	if (preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $guid)) {
		return $true;
	}
	return false;
}	

function getGUIDFromSubject($subject) {
	require('config.php');
		
	if (substr($subject,0,strlen($http_doc_prefix)) == $http_doc_prefix) {
		$id = substr($subject,(strpos($subject,$http_doc_prefix) + strlen($http_doc_prefix)),strlen($subject));
		$id = @substr($id,0,strlen("A98C5A1E-A742-4808-96FA-6F409E799937"));
		return $id;
	}
	return null;
}

function getGUIDDateURI($guid_uri,$date) {
	
	if ($guid_uri == "") {
		return $guid_uri;
	}

	if (substr($guid_uri,-1) == "/") {
                $guid_uri .= $date;
        } else {
                $guid_uri .= "/" . $date;
        }

	return $guid_uri;

}

function getLocalFromGUID($guid)  {
	require('config.php');

	return $local_doc_prefix . $guid;
}

function getURIFromGUID($guid) {
	require('config.php');

	return $http_doc_prefix . $guid;
}

function getLocalFromURI($uri) 
{
	require('config.php');
	
	return str_replace($http_doc_prefix, $local_doc_prefix, $uri);
}

function getGUIDURIFromLocal($local) 
{
	require('config.php');
	
	$uri = str_replace($local_doc_prefix, $http_doc_prefix, $local);
	if (substr($uri,-4) == ".rdf") {
		$uri = substr($uri,0,strrpos($uri,"/"));
	}
	return $uri;
}

function getURIFromLocal($local) 
{
	global $http_doc_prefix, $local_doc_prefix;
	
	return str_replace($local_doc_prefix, $http_doc_prefix, $local);
}

function latestVersionURI($local_path) 
{
	global $http_doc_prefix, $local_doc_prefix;

	$latest_link = substr($local_path,0,strrpos($local_path,"/")) . "/latest";

	return str_replace($local_doc_prefix,$http_doc_prefix,readlink($latest_link));
}


function getDocArray($path)
{
	$handle = opendir($path);
	while (false !== ($entry = readdir($handle))) {
		if ($entry == "latest" || substr($entry,0,1) == ".") {
		} else {
			$doc_array[] = $path . "/" . $entry;
		}
	}
	fclose($handle);
	arsort($doc_array);
	foreach($doc_array as $num=>$value) {
		$new_array[] = $value;
	}
	return $new_array;
}

function getDocDateName($doc_path)
{
	$date = substr($doc_path,strrpos($doc_path,"/")+1,strlen($doc_path));
	$date = str_replace(".rdf","",$date);

	$date = strtotime($date);
	return date("j/m/Y H:i",$date);
}
	
?>
