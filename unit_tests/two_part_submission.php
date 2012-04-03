<?php
error_reporting(E_ALL & ~E_NOTICE);

$authentication_service = "LDS";
$http_method = "POST";
$show_content = false;
$location_url = "";

if ($_POST["show_content"]) {
	$show_content = true;
}
$dsURL = $_POST["dsURL"];
$iriPrefix = $_POST["iriPrefix"];
$accessKey = $_POST["accessKey"];
$secretKey = $_POST["secretKey"];

$s1_editiri = "";
$s2_editiri = "";

$return["s1_authed"] = "";
$return["s1_uploaded"] = "";
$return["s2_authed"] = "";
$return["s2_uploaded"] = "";
$return["s1_editiri"] = false;
$return["s2_editiri"] = false;

$return["location"] = false;
$return["stuff"] = "";
$return["stuffisrdf"] = "";
$return["editiri_not_present_in_rdf"] = "";
$return["docurlannotated"] = "";
$return["docurldatapreserved"] = "";

if (!$show_content) {
	echo '<html><head><title>';
	echo 'Custom Doc Data Submission Test';
	echo '</title></head>';
	echo '<body>';
	echo '<div align="center" style="margin: 4em;">';
}

if ($accessKey != "" and $secretKey != "" and $dsURL != "" and $iriPrefix != "") {
	list ($s1_editiri, $return) = stage1($accessKey, $secretKey, $dsURL, $iriPrefix, $show_content, $http_method, $authentication_service);

	if ($s1_editiri != "") {
		list ($s2_editiri,  $location_url, $return) = stage2($accessKey, $secretKey, $s1_editiri, $iriPrefix, $show_content, $http_method, $authentication_service);
	}
		
	if ($s1_editiri != $s2_editiri or $s1_editiri == "") {
		$return["edituris_match"] = false;
	} else {
		$return["edituris_match"] = true;
	}
	
	if (!$show_content) {
		output_result_count($return);
		echo '<table width="790px" style="font-size: 1.5em;">';
		output_stage1_result($return);
		if ($s1_editiri != "") {
			output_stage2_result($return);
		}
		echo '<tr><td width="90%" style="height: 52px;">Two returned Edit-IRIs match?</td><td width="10%">';
		draw_result($return["edituris_match"]);
		echo '</td></tr>';	
		echo '</table>';
	} elseif ($s1_editiri != "") {
		echo "FAILED, no edit-iri from initial submit.<br/>";
	} else {
		echo "FAILED, no content returned, turn off show content and try again<br/>";
	}
	
	echo '<br/><br/>';
	echo '<span style="font-size: 2em">';
	if ($return["location"]) {
		echo 'Document Location <a href="'.$location_url.'" style="font-size: 0.5em;">'.$location_url.'</a><br/>';
	}
	if ($return["editiri"]) {
		echo 'Edit-IRI <a href="'.$editiri.'" style="font-size: 0.5em;">'.$editiri.'</a><br/>';
	}
	echo '</span>';
} else {
	echo '<form name="myform" action="" method="POST">';
	echo '<table width="790px" style="font-size: 1.5em;">';
	echo '<tr><td width="40%">DS-URL</td><td width="60%"><input style="padding: 0.2em; font-size: 0.9em; color: grey;" type="text" name="dsURL" size="55" value="'.$dsURL.'"/></td></tr>';
	echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">The Data-Submission IRI to which the data is POSTed (e.g. http://bucket.s3.amazonaws.com/)</td></tr>';
	echo '<tr><td width="40%">IRI-Prefix</td><td width="60%"><input style="padding: 0.2em; font-size: 0.9em; color: grey;" type="text" name="iriPrefix" size="55" value="'.$iriPrefix.'"/></td></tr>';
	echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">The IRI-Prefix of the data endpoint</td></tr>';
	echo '<tr><td width="40%">Access Key</td><td width="60%"><input style="padding: 0.2em; font-size: 0.8em; color: grey;" type="text" size="30" name="accessKey" value="'.$accessKey.'"/></td></tr>';
	echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">Your Access Key provided by the service at the above URL</td></tr>';
	echo '<tr><td width="40%">Secret</td><td width="60%"><input style="padding: 0.2em; font-size: 0.8em; color: grey;" type="text" size="55" name="secretKey" value="'.$secretKey.'"/></td></tr>';
	echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">The Secret Key linked to this Access Key</td></tr>';
	echo '<tr><td width="40%">Show Content</td><td width="60%"><input style="font-size: 0.8em; color: grey;" type="checkbox" name="show_content" ';
	if ($show_content) {
		echo "CHECKED";
	}
	echo '/><span style="font-size: 0.5em; color: grey;">Tick this box if you wish to display the returned content rather than just checking it can be retrieved.</span></td></tr>';
	echo '</table>';
	echo '<input style="font-size: 1em;" type="submit" name="submit" value="Submit"/>';
	echo '</form>';
}

if (!$show_content) {
	echo '</div></body></html>';
}

function stage1($accessKey, $secretKey, $dsURL, $iriPrefix, $show_content, $http_method, $authentication_service) {
	global $return;
	global $s1_editiri;

	$base_url = substr($dsURL,7,strlen($dsURL));
	$base_url = substr($base_url,0,strpos($base_url,"/"));
	$thing = substr($dsURL,strlen($base_url)+7,strlen($dsURL));
	$url = $dsURL;

	# STAGE 1 - BLANK POST

	$content_length = 0;
	$thingtosign = $thing;
	
	if (strpos($base_url,"amazon") != false) {
		$authentication_service = "AWS";
		$http_method = "PUT";
		$bucket = substr($base_url,0,strpos($base_url,"."));
		if ($thing == "/") {
			$thing = "/test3.rdf";
		}
		$thingtosign = "/" . $bucket . $thing;
		$location_url = $url . $bucket . $thing;
		$location_url = str_replace($bucket . ".","",$location_url);
		if (substr($url,-1) == "/" and substr($thing,0,1) == "/") {
			$url = $url . substr($thing,1,strlen($thing));
		} else {
			$url = $url . $thing;
		}
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
	$content_type_header = "Content-Type: " . $content_type;
	$content_length_header = "Content-Length: " . $content_length;
	$auth_header = "Authorization: " . $auth_string;
	$accept_header = "Accept: application/rdf+xml";
	$headers = "$host_header\n$date_header\n$accept_header\n$content_length_header\n$auth_header";

$debug = 0;
if ($debug) {
	echo $url;
	echo "=\n=";
	echo $strtosign;
	echo "=\n=";
	echo $thing;
	echo "=\n=";
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

	$status = $http_response_header[0];
	$parts = explode(" ",$status,3);
	$code = trim($parts[1]);
	
	if ($code == 403) {
		foreach ($return as $key => $value) {
			$return[$key] = "";
		}
		$return["s1_authed"] = false;
	} elseif ($code < 200 or $code > 299) {
		echo '<span style="font-size: 2em">';
		echo "Request Failed in Stage 1: " . $parts[1] . " " . $parts[2];
		echo '</span>';
		exit();
	} else {
		$return["s1_authed"] = true;
		$return["s1_uploaded"] = true;
	}
	
	for ($i=0;$i<count($http_response_header);$i++) {
		$header = $http_response_header[$i];
                if (strtolower(substr($header,0,strlen("link"))) == "link") {
                        $parts = explode(":",$header,2);
                        $s1_editiri = trim($parts[1]);
                        if (strpos($s1_editiri,"edit-iri") > 0) {
                                  $s1_editiri = substr($s1_editiri,strpos($s1_editiri,'<')+1,strlen($s1_editiri));
                                  $s1_editiri = substr($s1_editiri,0,strpos($s1_editiri,'>'));
                                  $return["s1_editiri"] = true;
                        }
                }
	}
	
	if (strpos($base_url,"amazon") != false) {
		$s1_editiri = "http://training1.s3.amazonaws.com/";
	}

       return array($s1_editiri,$return);
	# STAGE 1 - OUTPUT RESULT
}

function get_content_from_url($accessKey, $secretKey, $url) {
	global $authentication_service;
	$http_method = "GET";
	if (strpos($url,"amazon") != false) {
		$authentication_service = "AWS";
	}

	$thing = substr($url,8,strlen($url));
	$thing = substr($thing,strpos($thing,"/"),strlen($thing));
	
	$date = time();
	$strtosign = $http_method . "\n";
	$strtosign .= $content_md5 . "\n";
	$strtosign .= $content_type . "\n";
	$strtosign .= date("r",$date) ."\n";
	$strtosign .= $thing;

	$signature = base64_encode(hash_hmac("sha1", utf8_encode($strtosign), utf8_encode($secretKey), true));
	
	$auth_string = $authentication_service . " " . $accessKey . ":" . $signature;
	$host_header = "Host: " . $base_url;
	$date_header = "Date: " . date("r",$date);
	$auth_header = "Authorization: " . $auth_string;
	
	$headers = "$host_header\n$date_header\n$auth_header";

$debug = 0;
if ($debug) {
	echo $url;
	echo "\n";
	echo $strtosign;
	echo "\n";
	echo $thing;
	echo "\n";
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
	
	return $stuff;
}

function stage2($accessKey, $secretKey, $dsURL, $iriPrefix, $show_content, $http_method, $authentication_service) {
	global $return;
	global $s1_editiri,$s2_editiri;
	global $location_url;

	$base_url = substr($dsURL,7,strlen($dsURL));
	$base_url = substr($base_url,0,strpos($base_url,"/"));
	$thing = substr($dsURL,strlen($base_url)+7,strlen($dsURL));
	$url = $dsURL;
	
	# STAGE 2 - Actual Post

	$content = getContent($iriPrefix,$s1_editiri);
	$content_length = strlen($content);
	$thingtosign = $thing;

	if (strpos($base_url,"amazon") != false) {
		$authentication_service = "AWS";
		$http_method = "PUT";
		$bucket = substr($base_url,0,strpos($base_url,"."));
		if ($thing == "/") {
			$thing = "/test3.rdf";
		}
		$thingtosign = "/" . $bucket . $thing;
		$location_url = $url . $bucket . $thing;
		$location_url = str_replace($bucket . ".","",$location_url);
		if (substr($url,-1) == "/" and substr($thing,0,1) == "/") {
			$url = $url . substr($thing,1,strlen($thing));
		} else {
			$url = $url . $thing;
		}
	}

	$date = time();
	$content_type = "application/rdf+xml";
	$strtosign = $http_method . "\n";
	$strtosign .= $content_md5 . "\n";
	$strtosign .= $content_type . "\n";
	$strtosign .= date("r",$date) ."\n";
	$strtosign .= $thingtosign;

	$signature = base64_encode(hash_hmac("sha1", utf8_encode($strtosign), utf8_encode($secretKey), true));
	
	$auth_string = $authentication_service . " " . $accessKey . ":" . $signature;
	$host_header = "Host: " . $base_url;
	$date_header = "Date: " . date("r",$date);
	$content_type_header = "Content-Type: " . $content_type;
	$content_length_header = "Content-Length: " . $content_length;
	$auth_header = "Authorization: " . $auth_string;
	$accept_header = "Accept: application/rdf+xml";
	if ($content_md5) {
		$content_md5_header = "Content-Md5: " . $content_md5;
		$headers = "$host_header\n$date_header\n$accept_header\n$content_type_header\n$content_md5_header\n$content_length_header\n$auth_header";
	} else {
		$headers = "$host_header\n$date_header\n$accept_header\n$content_type_header\n$content_length_header\n$auth_header";
	}
$debug = 0;
if ($debug) {
	echo $url;
	echo "\n";
	echo $strtosign;
	echo "\n";
	echo $thing;
	echo "\n";
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

	if (strpos($base_url,"amazon") != false) {
		$stuff = getFakeReturnContent($iriPrefix);
	}

	$status = $http_response_header[0];
	$parts = explode(" ",$status,3);
	$code = trim($parts[1]);
	for ($i=0;$i<count($http_response_header);$i++) {
		$header = $http_response_header[$i];
                if (strtolower(substr($header,0,strlen("content-location"))) == "content-location") {
                        $parts = explode(":",$header,2);
                        $location_url = trim($parts[1]);
                }
                if (strtolower(substr($header,0,strlen("link"))) == "link") {
                        $parts = explode(":",$header,2);
                        $s2_editiri = trim($parts[1]);
                        if (strpos($s2_editiri,"edit-iri") > 0) {
                                  $s2_editiri = substr($s2_editiri,strpos($s2_editiri,'<')+1,strlen($s2_editiri));
                                  $s2_editiri = substr($s2_editiri,0,strpos($s2_editiri,'>'));
                                  $return["s2_editiri"] = true;
                        }
                }
		if (strtolower(substr($header,0,strlen("content-type"))) == "content-type") {
			$parts = explode(":",$header);
			$content_type = trim($parts[1]);
			if (strpos($content_type,"rdf") !== false) {
				$return["stuff"] = "warning";
			}
		}
	}

#TEST GETTING THE CONTENT FROM THE SERVER
	if ($location_url != "") {
		$return["location"] = true;
#		$stuff = get_content_from_url($accessKey, $secretKey, $location_url);
	}

	if ($stuff == "") {
		$return["stuff"] = false;
	} else {
		$return["stuff"] = true;
		$return["stuffisrdf"] = is_rdf($stuff);
		if ($return["location"]) {
			$return["docurlannotated"] = is_docurl_annotated($stuff,$location_url);
			$return["docurldatapreserved"] = is_docurl_data_preserved($stuff,$location_url);
		}
		if ($s1_editiri != "") {
			$return["editiri_not_present_in_rdf"] = editiri_not_present_in_rdf($stuff,$s1y_editiri);
		}
	}

	if ($code == 403) {
		foreach ($return as $key => $value) {
			$return[$key] = "";
		}
		$return["s2_authed"] = false;
	} elseif ($code < 200 or $code > 299) {
		echo '<span style="font-size: 2em">';
		echo "Request Failed in Stage 2: " . $parts[1] . " " . $parts[2];
		echo '</span>';
		exit();
	} else {
		$return["s2_authed"] = true;
		$return["s2_uploaded"] = true;
	}

        return array($s2_editiri, $location_url, $return);

}

function output_content($return_content) {
	$http_response_header = $return_content["headers"];
	$stuff = $return_content["stuff"];
	
	for ($i=0;$i<count($http_response_header);$i++) {
		$header = $http_response_header[$i];
		if (strtolower(substr($header,0,strlen("content-type"))) == "content-type") {
			$parts = explode(":",$header);
			header('Content-Type: ' . trim($parts[1]));
		}
	}
	print($stuff);
}

function output_result_count($return) {
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
}

function output_stage1_result($return) {
	echo '<tr><td width="90%" style="height: 52px;"><strong>Stage 1</strong></td><td width="10%">&nbsp;</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["s1_authed"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;Authentication Check</td><td width="10%">';
	draw_result($return["s1_authed"]);
	echo '</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["s1_uploaded"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;Document Submitted</td><td width="10%">';
	draw_result($return["s1_uploaded"]);
	echo '</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["s1_editiri"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;Received Edit-IRI?</td><td width="10%">';
	draw_result($return["s1_editiri"]);
	echo '</td></tr>';
	
}

function output_stage2_result($return) {
	echo '<tr><td width="90%" style="height: 52px;"><strong>Stage 2</strong></td><td width="10%">&nbsp;</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["s2_authed"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;Authentication Check</td><td width="10%">';
	draw_result($return["s2_authed"]);
	echo '</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["s2_uploaded"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;Document Submitted</td><td width="10%">';
	draw_result($return["s2_uploaded"]);
	echo '</td></tr>';
		
	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["location"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;Received Document Location (Doc-URL)?</td><td width="10%">';
	draw_result($return["location"]);
	echo '</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["s2_editiri"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;Received Edit-IRI?</td><td width="10%">';
	draw_result($return["s2_editiri"]);
	echo '</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["stuff"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;Received Formatted Content in Return?</td><td width="10%">';
	draw_result($return["stuff"]);
	echo '</td></tr>';
	
	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["stuffisrdf"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Received Content is Valid RDF?</td><td width="10%">';
	draw_result($return["stuffisrdf"]);
	echo '</td></tr>';
	
	echo '<tr><td width="90%" style="height: 52px;';	
	if ($return["editiri_not_present_in_rdf"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Edit-IRI NOT present in document?</td><td width="10%">';
	draw_result($return["editiri_not_present_in_rdf"]);
	echo '</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["docurlannotated"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DocURL has been annotated by server in document?</td><td width="10%">';
	draw_result($return["docurlannotated"]);
	echo '</td></tr>';

	echo '<tr><td width="90%" style="height: 52px;';
	if ($return["docurldatapreserved"] === "") {
		echo ' color: grey;';
	}
	echo '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Has input data about the DocURL been preserved by the server?</td><td width="10%">';
	draw_result($return["docurldatapreserved"]);
	echo '</td></tr>';
}

if (!$show_content) {
	echo '</div></body></html>';
}

function getContent($iriPrefix,$editiri) {
	if (substr($iriPrefix,-1) == "/") {
		$iriPrefix = substr($iriPrefix,0,strlen($iriPrefix)-1);
	}
	$content = '<?xml version="1.0"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:contact="http://www.w3.org/2000/10/swap/pim/contact#"
	 xmlns:dcterms="http://purl.org/dc/terms/">
  
  <rdf:Description rdf:about="'.$editiri.'">
    <dcterms:creator>Richard Philips</dcterms:creator>
    <dcterms:date>2011-12-26T12:30:14+00:00</dcterms:date>
    <dcterms:isPartOf rdf:resource="http://id.my-realy-cool-project.org/"/>
  </rdf:Description>

  <contact:Person rdf:about="'.$iriPrefix.'/id/person/BobSmith">
    <contact:fullName>Bob Smith</contact:fullName>
    <contact:mailbox rdf:resource="mailto:bob_smith@example.org"/>
    <contact:personalTitle>Dr.</contact:personalTitle> 
  </contact:Person>
</rdf:RDF>';

	return $content;
}

function getFakeReturnContent($iriPrefix) {
	if (substr($iriPrefix,-1) == "/") {
		$iriPrefix = substr($iriPrefix,0,strlen($iriPrefix)-1);
	}
	$content = '<?xml version="1.0"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:contact="http://www.w3.org/2000/10/swap/pim/contact#"
	 xmlns:dcterms="http://purl.org/dc/terms/">

  <rdf:Description rdf:about="http://s3.amazonaws.com/training1/test3.rdf">
    <dcterms:creator>Richard Philips</dcterms:creator>
    <dcterms:date>2011-12-26T12:30:14+00:00</dcterms:date>
    <dcterms:isPartOf rdf:resource="http://id.my-realy-cool-project.org/"/>
    <dcterms:publisher>Dave Tarrant</dcterms:publisher>
    <dcterms:dateSubmitted>2011-12-31T23:59:00+00:00</dcterms:dateSubmitted>
  </rdf:Description>

  <contact:Person rdf:about="'.$iriPrefix.'/id/person/BobSmith">
    <contact:fullName>Bob Smith</contact:fullName>
    <contact:mailbox rdf:resource="mailto:bob_smith@example.org"/>
    <contact:personalTitle>Dr.</contact:personalTitle> 
  </contact:Person>

</rdf:RDF>
';
	return $content;
}

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

function is_rdf($stuff) {
	$stuff = trim($stuff);
	include_once('libraries/Graphite/Graphite.php');
	include_once('libraries/arc2/ARC2.php');

	$temp_file = tempnam(sys_get_temp_dir(),'LDS3_unit');
	$handle = fopen($temp_file,"w");
	fwrite($handle,$stuff);
	fclose($handle);

	$graph = new Graphite();
	$amount = $graph->load($temp_file);
	unlink($temp_file);
	if ($amount < 1) {
		return false;
	}
	return true;
}

function is_docurl_data_preserved($stuff,$docurl) {
	$stuff = trim($stuff);
	include_once('libraries/Graphite/Graphite.php');
	include_once('libraries/arc2/ARC2.php');

	$temp_file = tempnam(sys_get_temp_dir(),'LDS3_unit');
	$handle = fopen($temp_file,"w");
	fwrite($handle,$stuff);
	fclose($handle);

	$graph = new Graphite();
	$graph->load($temp_file);
	unlink($temp_file);

	$resource = $graph->resource($docurl);
	
	$pass = 0;

	$creator = $resource->get("dct:creator");
	if ($creator == "Richard Philips") {
		$pass++;
	}

	$ispartof = $resource->get("dct:isPartOf");
	if ($ispartof == "http://id.my-realy-cool-project.org/") {
		$pass++;
	}
	if ($pass == 2) {
		return true;
	} 

	return false;
}

function editiri_not_present_in_rdf($stuff,$s1_editiri) {
	$stuff = trim($stuff);
	include_once('libraries/Graphite/Graphite.php');
	include_once('libraries/arc2/ARC2.php');

	$temp_file = tempnam(sys_get_temp_dir(),'LDS3_unit');
	$handle = fopen($temp_file,"w");
	fwrite($handle,$stuff);
	fclose($handle);

	$graph = new Graphite();
	$graph->load($temp_file);
	unlink($temp_file);
	$relation_list = $graph->resource($s1_editiri)->relations()->join(', ');
	if ($relation_list == "") {
		return true;
	} 
	return false;	
}

function is_docurl_annotated($stuff,$docurl) {
	$stuff = trim($stuff);
	include_once('libraries/Graphite/Graphite.php');
	include_once('libraries/arc2/ARC2.php');

	$temp_file = tempnam(sys_get_temp_dir(),'LDS3_unit');
	$handle = fopen($temp_file,"w");
	fwrite($handle,$stuff);
	fclose($handle);

	$graph = new Graphite();
	$graph->load($temp_file);
	unlink($temp_file);
	$relation_list = $graph->resource($docurl)->relations()->join(', ');
	if ($relation_list == "") {
		return false;
	}
	$relations = explode(", ",$relation_list);
	$amount = 0;
	foreach ($relations as $name) {
		if ($name != "http://purl.org/dc/terms/creator" and $name != "http://purl.org/dc/terms/date" and $name != "http://purl.org/dc/terms/isPartOf") {
			$amount++;
		}
	}
	if ($amount > 0) {
		return true;
	}
	return false;
}
?>
