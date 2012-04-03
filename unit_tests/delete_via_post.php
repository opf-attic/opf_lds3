<?php
error_reporting(E_ALL & ~E_NOTICE);

$authentication_service = "LDS";
$http_method = "POST";
$show_content = false;

if ($_POST["show_content"]) {
	$show_content = true;
}
$editiri = $_POST["editiri"];
$iriPrefix = $_POST["iriPrefix"];
$accessKey = $_POST["accessKey"];
$secretKey = $_POST["secretKey"];

$return["authed"] = "";
$return["uploaded"] = "";
$return["location"] = false;
$return["editiri"] = false;
$return["stuff"] = "";
$return["stuffisrdf"] = "";
$return["docurlannotated"] = "";
#$return["docurldatapreserved"] = "";
$return["editiris_match"] = false;
$return["linked_to_old"] = "";

if (!$show_content) {
	echo '<html><head><title>';
	echo 'Delete via POST Test';
	echo '</title></head>';
	echo '<body>';
	echo '<div align="center" style="margin: 4em;">';
}

if ($accessKey != "" and $secretKey != "" and $editiri != "" and $iriPrefix != "") {
	$base_url = substr($editiri,7,strlen($editiri));
	$base_url = substr($base_url,0,strpos($base_url,"/"));
	$thing = substr($editiri,strlen($base_url)+7,strlen($editiri));
	$url = $editiri;

	$content = getContent($editiri,$iriPrefix);
	$content_length = strlen($content);
	$thingtosign = $thing;

	if (strpos($base_url,"amazon") != false) {
		$authentication_service = "AWS";
		$http_method = "PUT";

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
$debug =0;
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

	if (strpos($base_url,"amazon") != false) {
		$stuff = getFakeReturnContent($editiri,$iriPrefix);
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
                        $editiri_ret = trim($parts[1]);
                        if (strpos($editiri_ret,"edit-iri") > 0) {
                                  $editiri_ret = substr($editiri_ret,strpos($editiri_ret,'<')+1,strlen($editiri_ret));
                                  $editiri_ret = substr($editiri_ret,0,strpos($editiri_ret,'>'));
                                  $return["editiri"] = true;
                        }
                        if ($editiri_ret == $editiri || $editiri_ret."/" == $editiri) {
                                $return["editiris_match"] = true;
                        }
                        $editiri = $editiri_ret;
                }
		if (strtolower(substr($header,0,strlen("content-type"))) == "content-type") {
			$parts = explode(":",$header);
			$content_type = trim($parts[1]);
			if (strpos($content_type,"rdf") !== false) {
				$return["stuff"] = "warning";
			}
		}
	}
	if ($location_url != "") {
		$return["location"] = true;
	}

	if ($stuff == "") {
		$return["stuff"] = false;
	} else {
		$return["stuff"] = true;
		$return["stuffisrdf"] = is_rdf($stuff);
		if ($return["location"]) {
			$return["docurlannotated"] = is_docurl_annotated($stuff,$location_url);
#			$return["docurldatapreserved"] = is_docurl_data_preserved($stuff,$location_url);
			$return["linked_to_old"] = is_doc_linked_to_existing($stuff,$location_url);
		}
	}

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
		$return["uploaded"] = true;
	}
	if ($show_content) {
		for ($i=0;$i<count($http_response_header);$i++) {
			$header = $http_response_header[$i];
			if (strtolower(substr($header,0,strlen("content-type"))) == "content-type") {
				$parts = explode(":",$header);
				header('Content-Type: ' . trim($parts[1]));
			}
		}
		print($stuff);
	} else {
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
		if ($return["uploaded"] === "") {
			echo ' color: grey;';
		}
		echo '">Document Submitted</td><td width="10%">';
		draw_result($return["uploaded"]);
		echo '</td></tr>';

		echo '<tr><td width="90%" style="height: 52px;';
		if ($return["location"] === "") {
			echo ' color: grey;';
		}
		echo '">Received Document Location (Doc-URL)?</td><td width="10%">';
		draw_result($return["location"]);
		echo '</td></tr>';

		echo '<tr><td width="90%" style="height: 52px;';
		if ($return["editiri"] === "") {
			echo ' color: grey;';
		}
		echo '">Received Edit-IRI?</td><td width="10%">';
		draw_result($return["editiri"]);
		echo '</td></tr>';

		echo '<tr><td width="90%" style="height: 52px;';
		if ($return["stuff"] === "") {
			echo ' color: grey;';
		}
		echo '">Received Formatted Content in Return?</td><td width="10%">';
		draw_result($return["stuff"]);
		echo '</td></tr>';

		echo '<tr><td width="90%" style="height: 52px;';
		if ($return["stuffisrdf"] === "") {
			echo ' color: grey;';
		}
		echo '">&nbsp;&nbsp;&nbsp;&nbsp;Received Content is Valid RDF?</td><td width="10%">';
		draw_result($return["stuffisrdf"]);
		echo '</td></tr>';

		echo '<tr><td width="90%" style="height: 52px;';
		if ($return["docurlannotated"] === "") {
			echo ' color: grey;';
		}
		echo '">&nbsp;&nbsp;&nbsp;&nbsp;DocURL has been annotated by server in document?</td><td width="10%">';
		draw_result($return["docurlannotated"]);
		echo '</td></tr>';
/*
		echo '<tr><td width="90%" style="height: 52px;';
		if ($return["docurldatapreserved"] === "") {
			echo ' color: grey;';
		}
		echo '">&nbsp;&nbsp;&nbsp;&nbsp;Has input data about the DocURL been preserved by the server?</td><td width="10%">';
		draw_result($return["docurldatapreserved"]);
		echo '</td></tr>';
*/		
		echo '<tr><td width="90%" style="height: 52px;';
		if ($return["linked_to_old"] === "") {
			echo ' color: grey;';
		}
		echo '">&nbsp;&nbsp;&nbsp;&nbsp;Has the document been linked to the old version?</td><td width="10%">';
		draw_result($return["linked_to_old"]);
		echo '</td></tr>';
		
		echo '<tr><td width="90%" style="height: 52px;';
		if ($return["editiris_match"] === "") {
			echo ' color: grey;';
		}
		echo '">Does the input Edit-IRI match that returned by the Server?</td><td width="10%">';
		draw_result($return["editiris_match"]);
		echo '</td></tr>';

		echo '</table>';
		echo '<br/><br/>';
		echo '<span style="font-size: 2em">';
                if ($return["location"]) {
                        echo 'Document Location <a href="'.$location_url.'" style="font-size: 0.5em;">'.$location_url.'</a><br/>';
                }
                if ($return["editiri"]) {
                        echo 'Edit-IRI <a href="'.$editiri.'" style="font-size: 0.5em;">'.$editiri.'</a><br/>';
                }
		echo '</span>';
	}
} else {
	echo '<form name="myform" action="" method="POST">';
	echo '<table width="790px" style="font-size: 1.5em;">';
	echo '<tr><td width="40%">Edit-IRI</td><td width="60%"><input style="padding: 0.2em; font-size: 0.9em; color: grey;" type="text" name="editiri" size="55" value="'.$editiri.'"/></td></tr>';
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

function getContent($editiri,$iriPrefix) {
	if (substr($iriPrefix,-1) == "/") {
		$iriPrefix = substr($iriPrefix,0,strlen($iriPrefix)-1);
	}
	$content = '<?xml version="1.0"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:contact="http://www.w3.org/2000/10/swap/pim/contact#"
	 xmlns:foaf="http://xmlns.com/foaf/0.1/"
	 xmlns:dcterms="http://purl.org/dc/terms/">

</rdf:RDF>';

	return $content;
}

function getFakeReturnContent($editiri,$iriPrefix) {
	if (substr($iriPrefix,-1) == "/") {
		$iriPrefix = substr($iriPrefix,0,strlen($iriPrefix)-1);
	}
	$content = '<?xml version="1.0"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:contact="http://www.w3.org/2000/10/swap/pim/contact#"
	 xmlns:foaf="http://xmlns.com/foaf/0.1/"
	 xmlns:dcterms="http://purl.org/dc/terms/">
  
  <rdf:Description rdf:about="'.$editiri.'">
    <dcterms:publisher>Dave Tarrant</dcterms:publisher>
    <dcterms:dateSubmitted>2012-01-03T14:45:00+00:00</dcterms:dateSubmitted>
    <dcterms:replaces rdf:resource="'.$iriPrefix.'/doc/4E5E357B-69A3-4C84-BCBA-572693FBCEA5/2011-12-31T23:20:00+00:00"/>
  </rdf:Description>

</rdf:RDF>';
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

	$creator = $resource->all("dct:creator")->join(", ");
	$creators = explode(", ",$creator);
	foreach ($creators as $name) {
		if ($name == "Richard Philips") {
			$pass++;
		}
		if ($name == "Brenda Rodgers") {
			$pass++;
		}
	}

	$ispartof = $resource->get("dct:isPartOf");
	if ($ispartof == "http://id.my-realy-cool-project.org/") {
		$pass++;
	}
	if ($pass == 3) {
		return true;
	} 

	return false;
}

function is_doc_linked_to_existing($stuff,$docurl) {
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
	if ($resource->get("dct:replaces")->isNull()) {
		return false;
	} else {
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
