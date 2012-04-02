<?php

function addProvenanceInfoToGraph($graph,$subject,$info) {

	if (!$info) {
		return $graph;
	}

	if ($info["restored_from"]) {
		$restored_from = urlencode($info["restored_from"]);
		$to_add = '
			@prefix opf: <http://data.openplanetsfoundation.org/schema/#> .
	
			<' . $subject .'>
			opf:restored_from <'.$restored_from.'>;
		    ';

		$added = $graph->addTurtle($subject,$to_add);
	}

	if ($info["previous"]) {
		$to_add = '
			@prefix dct: <http://purl.org/dc/terms/> .

			<' . $subject .'>
			dct:replaces <'.$previous.'> .
			';
		$added = $graph->addTurtle($subject,$to_add);
	}

	return $graph;
}

function getExistingGUIDFromFile($file_path) {
	require('config.php');

	$subject_tree = getSubjects($file_path);

	$path_count = 0;
	foreach ($subject_tree as $subject => $data) {
		$guid = getGUIDFromSubject($subject);
		if (isValidGUID($guid)) {
			$path_count++;
		}
	}
	if ($path_count > 1) {
		return array ("400","The imported document is masquerading as 2 documents, please remove one of the $http_doc_prefix/GUID references from this document");
	}

	return array (null,$guid);
}

function writeGraphToFile($graph,$file_path) {

	$data = $graph->serialize();
	$handle = fopen($file_path,"w");
	fwrite($handle,$data);
	fclose($handle);

	return $file_path;

}

function reWriteGUIDDateURI($file_path,$incoming_subject,$guid_date_uri) {
	if ($incoming_subject == "") {
		return $file_path;
	}

	$data = file_get_contents($file_path);

	//Account for tailing /'s with quotes.
	if (strpos($data,$incoming_subject . '/"') > 0 || strpos($data,$incoming_subject . "/'") > 0) {
	        $data = str_replace($incoming_subject . '/"',$guid_date_uri . '"',$data);
        	$data = str_replace($incoming_subject . "/'",$guid_date_uri . "'",$data);
        } else {
		$data = str_replace($incoming_subject,$guid_date_uri,$data);
	}

        $handle = fopen($file_path,"w");
        fwrite($handle,$data);
        fclose($handle);
	return $file_path;
}


?>
