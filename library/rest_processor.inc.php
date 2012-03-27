<?php

function isRequestAuthorized($headers) {
	$authentication_header = getAuthenticationHeader($headers);
	$user_key = getUserKeyFromHeader($authentication_header);
	$request_signature = getSignatureFromHeader($authentication_header);
	$calculated_signature = getCalculatedSignature($user_key,$headers);

	if ($request_signature == $calculated_signature) {
		return true;
	}
	
	return false;
}

function getAuthenticationHeader($headers) {
	foreach ($headers as $header=> $value) {
		if (trim($header) == "Authorization") {
			return $value;
		}
	}
	header("HTTP/1.0 401 Unathorized");
	echo("Unathorized");
	exit();
}

function getArrayHeaders($headers) {
	foreach ($headers as $header=> $value) {
		$out[strtolower(trim($header))] = $value;
	}
	return $out;
}

function getUserKeyFromHeader($header) {
	$parts = explode(" ",$header);
	$auth_part = $parts[1];
	$user_sig = explode(":",$auth_part);
	return $user_sig[0];
}

function getSignatureFromHeader($header) {
	$parts = explode(" ",$header);
	$auth_part = $parts[1];
	$user_sig = explode(":",$auth_part);
	return $user_sig[1];
}

function getCanonicalizedHeaders($headers) {
	$base_host = getBaseDomain();
	$headers = getArrayHeaders($headers);
	
	$requested_path = $_SERVER["SCRIPT_NAME"];

	$requested_host = $headers["host"];
	$remainder = str_replace($base_host,"",$requested_host);
	
	if (strlen($remainder) < 2) {
		return $requested_path;
	} 
	
	$remainder = "/" . str_replace(".","/",$remainder);
	if (substr($remainder,-1) == "/") {
		$remainder = substr($remainder,0,strlen($remainder)-1);
	}
	return $remainder . $requested_path;
}

function getCanonicalizedResource($headers) {
	return str_replace($_SERVER["SCRIPT_NAME"],"",$_SERVER["REQUEST_URI"]);	
}

function getCalculatedSignature($user_key,$headers) {

	$canonicalized_headers = getCanonicalizedHeaders($headers);
	$canonicalized_resource = getCanonicalizedResource($headers);

	$secret_key = getSecretKey($user_key);
	$headers = getArrayHeaders($headers);

	$string_to_sign = $_SERVER['REQUEST_METHOD'] . "\n" .
	 		@$headers["content-md5"] . "\n" .
			@$headers["content-type"] . "\n" .
			@$headers["date"] . "\n" .
			$canonicalized_headers .
			$canonicalized_resource;

	
	return(base64_encode(hash_hmac("sha1", utf8_encode($string_to_sign), utf8_encode($secret_key), true)));

}

function getBaseDomain() {
	require('config.php');
	return $base_domain;
}

?>
