<?php

error_reporting(E_ALL & ~E_NOTICE);

$authentication_service = "LDS";
$http_method = "DELETE";

if ($_POST["show_content"]) {
	$show_content = true;
}
$editiri = $_POST["editiri"];
$accessKey = $_POST["accessKey"];
$secretKey = $_POST["secretKey"];

$return["authed"] = "";
$return["deleted"] = "";

echo '<html><head><title>';
echo 'Direct Delete Test';
echo '</title></head>';
echo '<body>';
echo '<div align="center" style="margin: 4em;">';

if ($accessKey != "" and $secretKey != "" and $editiri != "") {
	$base_url = substr($editiri,7,strlen($editiri));
	$base_url = substr($base_url,0,strpos($base_url,"/"));
	$thing = substr($editiri,strlen($base_url)+7,strlen($editiri));
	$url = $editiri;

	$thingtosign = $thing;

	if (strpos($base_url,"amazon") != false) {
		$authentication_service = "AWS";

		$thingtosign = substr($editiri,strpos($editiri,$base_url)+strlen($base_url),strlen($editiri));
	
		$bucket = substr($editiri,7,strlen($editiri));
		$bucket = substr($bucket,strpos($bucket,"/")+1,strlen($bucket));
		$bucket = substr($bucket,0,strpos($bucket,"/"));
	
		$thing = substr($editiri,strpos($editiri,$bucket)+strlen($bucket),strlen($editiri));

		if ($thing == "/") {
			$thing = "/test3.rdf";
		}
		
		$location_url = $editiri;
		$base_url = $bucket . "." . $base_url;
		
		$url = "http://" . $base_url . $thing;
	}

	$date = time();
	$strtosign = $http_method . "\n";
	$strtosign .= $content_md5 . "\n";
	$strtosign .= $content_type . "\n";
	$strtosign .= date("r",$date) ."\n";
	$strtosign .= $thingtosign;

	$signature = base64_encode(hash_hmac("sha1", utf8_encode($strtosign), utf8_encode($secretKey), true));
	
	$auth_string = $authentication_service . " " . $accessKey . ":" . $signature;
	$host_header = "Host: " . $base_url;
	$date_header = "Date: " . date("r",$date);
	$auth_header = "Authorization: " . $auth_string;
	$headers = "$host_header\n$date_header\n$auth_header";

$debug = 0;
if ($debug) {
	echo "\n".$url;
	echo "\n=====\n";
	echo $strtosign;
	echo "\n=====\n";
	echo $thing;
	echo "\n=====\n";
	echo $headers;	
	exit();
}

	$params = array(
			'http' => array(
				'method' => $http_method,
				'header' => $headers,
				'content' => $content
				)
		       );
	
	$ctx = stream_context_create($params);

	$stuff = @file_get_contents($url, false, $ctx);

#	if (strpos($base_url,"amazon") != false) {
#		$stuff = getFakeReturnContent($editiri,$iriPrefix);
#	}

	$status = $http_response_header[0];
	$parts = explode(" ",$status,3);
	$code = trim($parts[1]);

	if ($code == 403) {
		foreach ($return as $key => $value) {
			$return[$key] = "";
		}
		$return["authed"] = false;
	} elseif ($code < 200 or $code > 299) {
		echo '<span style="font-size: 2em">';
		echo "Request Failed: " . $parts[1] . " " . $parts[2];
		echo '</span>';
		exit();
	} else {
		$return["authed"] = true;
		$return["deleted"] = true;
	}
	$passed = 0;
	foreach ($return as $key => $value) {
		if ($value == true) {
			$passed++;
		}
	}
	echo '<div style="font-size: 2em">';
	if ($passed == 0) {
		echo '<span style="color: red;">'.$passed.'/'.count($return) . ' Tests Passed</span>';
	} else {
		echo $passed.'/'.count($return) . ' Tests Passed';
	}	
	echo '</div><br/><br/>';
	echo '<table width="790px" style="font-size: 1.5em;">';
	
	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["authed"] === "") {
		echo ' color: grey;';
	}	
	echo '">Authentication Check</td><td width="10%">';
	draw_result($return["authed"]);
	echo '</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["deleted"] === "") {
		echo ' color: grey;';
	}
	echo '">Document Deleted</td><td width="10%">';
	draw_result($return["deleted"]);
	echo '</td></tr>';
	
	echo '</table>';
	echo '<br/><br/>';
} else {
	echo '<form name="myform" action="" method="POST">';
	echo '<table width="790px" style="font-size: 1.5em;">';
	echo '<tr><td width="40%">Edit-IRI</td><td width="60%"><input style="padding: 0.2em; font-size: 0.9em; color: grey;" type="text" name="editiri" size="55" value="'.$editiri.'"/></td></tr>';
	echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">The Data-Submission IRI to which the data is POSTed (e.g. http://bucket.s3.amazonaws.com/)</td></tr>';
	echo '<tr><td width="40%">Access Key</td><td width="60%"><input style="padding: 0.2em; font-size: 0.8em; color: grey;" type="text" size="30" name="accessKey" value="'.$accessKey.'"/></td></tr>';
	echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">Your Access Key provided by the service at the above URL</td></tr>';
	echo '<tr><td width="40%">Secret</td><td width="60%"><input style="padding: 0.2em; font-size: 0.8em; color: grey;" type="text" size="55" name="secretKey" value="'.$secretKey.'"/></td></tr>';
	echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">The Secret Key linked to this Access Key</td></tr>';
	echo '</table>';
	echo '<input style="font-size: 1em;" type="submit" name="submit" value="Submit"/>';
	echo '</form>';
}

echo '</div></body></html>';

function draw_result($result) {
	if ($result === "") {
		return;
	}
	echo '<img width="48px" src="/files/';
	if ($result === false) {
		echo 'error.png" alt="Failed"';
	}	
	if ($return == "warning") {
		echo 'warning.png" alt="Warning"';
	}
	if ($result === true) {
		echo 'message.png" alt="Passed"';
	}	
	echo '/>';
}

?>
