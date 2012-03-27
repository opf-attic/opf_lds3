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

function getLocalFromURI($uri) 
{
	global $http_doc_prefix, $local_doc_prefix;
	
	return str_replace($http_doc_prefix, $local_doc_prefix, $uri);
}

function getGUIDURIFromLocal($local) 
{
	global $http_doc_prefix, $local_doc_prefix;
	
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
