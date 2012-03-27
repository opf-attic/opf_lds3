<?php
require_once('connect.inc.php');
require_once('functions.inc.php');

function getKeyPairs($user_id) {
	$query = "SELECT id,access_key,secret,descrip,enabled from Access_Keys where user_id=$user_id;";
	$res = mysql_query($query);
	while ($array = mysql_fetch_array($res)) {
		$out[] = $array;
	}
	return $out;
}

function getSecretKey($user_key) {
	$query = "SELECT secret from Access_Keys where access_key='$user_key' and enabled=1;";
	$res = mysql_query($query);
	$row = mysql_fetch_row($res);
	return $row[0];
}

function createNewKeyPair($user_id,$description) {
	$key = generateRandomKey();
	$secret = generateRandomSecret();
	$description = addslashes($description);
	
	$query = "INSERT into Access_Keys set user_id=$user_id,descrip='$description',access_key='$key',secret='$secret',enabled=1";
	$res = mysql_query($query) or die($query);
	if ($res) {
		return $key;
	}
	return null;
}

function generateRandomKey() {
	$guid = GenerateGUID();
	$guid = str_replace("-","",$guid);
	$key = substr($guid,0,20);
	return $key;	
}

function generateRandomSecret() {
	$guid1 = GenerateGUID();
	$guid2 = strtolower(GenerateGUID());
	$guid1 = str_replace("-","",$guid1);
	$guid2 = str_replace("-","",$guid2);
	$guid = splice($guid1,$guid2);

	$secret = substr($guid,0,40);
	return $secret;
}

function splice($string1,$string2) {
        for($i=0;$i<strlen($string1);$i++) {
                $out .= substr($string1,$i,1);
                $out .= substr($string2,$i,1);
        }
        return $out;
}

?>
