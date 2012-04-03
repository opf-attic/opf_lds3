<?php

include_once('library/rdf_functions.inc.php');
include_once('library/functions.inc.php');
include_once('library/user_functions.inc.php');
include_once('library/graph_functions.inc.php');
include_once('library/4store_functions.inc.php');

function addNewDocument($file_path,$user_key,$incoming_subject,$expiry_time) {
	
	$graph = getGraph($file_path);
	if (!$graph) {
		return array ("400","Input File invalid");
	}

	list ($error,$guid_uri) = getExistingGUIDFromFile($file_path);

	if ($error) {
		return array ($error,$guid_uri);
	}

	if (!$guid) {
		$guid = getGUID();
	}
	
	$local_dir = getLocalFromGUID($guid);
	$guid_uri = getURIFromGUID($guid);
	
	if(is_dir($local_dir)) {
		return array ("400","GUID " . $guid . " already in use to update it please POST this document to " . $guid_uri);
	}
	
	list ($error,$guid_date_uri) = updateDocument($file_path,$guid_uri,$user_key,$incoming_subject,$expiry_time);

	if (!$error) {
		addExpiryToDatabaseIndex($guid,$expiry_time);
		$error = 202;	
	}

	return array ($error,"$guid_uri : $guid_date_uri");

}


function updateDocument($file_path,$guid_uri,$user_key,$incoming_subject,$expiry_time) {

	// Handle the upload need to sort out which URIs to re-write and rename
	
	$date = date("c");

	$guid = getGUIDFromURI($guid_uri);

	// Add the date to a pre-defined URI
	$guid_date_uri = getGUIDDateURI($guid_uri,$date);

	//Replace any defined nodes with the new guid_uri
	$subject = updateSubjectNodes($file_path,$subject,$guid_date_uri);

	//Update the Subject
	$subject = getDateSubject($subject,$guid_date_uri,$date);
	
	// Get all the paths
	$local_dir = getLocalFromURI($guid_uri);

	// Translate the incoming subject (or guid_uri) into our subject in the file
	$file_path = reWriteGUIDDateURI($file_path,$incoming_subject,$guid_date_uri);
	$file_path = reWriteGUIDDateURI($file_path,$guid_uri,$guid_date_uri);

	if (isReservedGUID($guid)) {
		removeExpiredDocumentsForGUID($guid);
	}

	// Get the RDF Grap
	$graph = getGraph($file_path);
	
	$graph = addUserDataToGraph($graph,$subject,$date,$user_key);
	
	$provenance_info = getProvenanceInfo($guid_uri,$subject,$local_dir);	
	$graph = addProvenanceInfoToGraph($graph,$subject,$provenance_info);

	$graph = addExpiryTimeToGraph($graph,$subject,$expiry_time);

	$file_path = writeGraphToFile($graph,$file_path);

	if ($provenance_info["previous"]) {
		$previous = $provenance_info["previous"];

		// 4s-remove the old document
		removeDocumentFromIndex($previous);
	}
	
	$result = instantiateNewDocument($file_path,$local_dir,$date,$subject);

	@unlink($file_path);
	
	return array (null,$guid_date_uri);

}

function updateSubjectNodes($file_path,$subject,$guid_date_uri) {
	
	if ($subject == "" or $subject == $guid_date_uri) {
		return $subject;
	}
	
	$data = file_get_contents($file_path);
	$data = str_replace($subject,$guid_date_uri,$data);
	$handle = fopen($file_path,"w");
	fwrite($handle,$data);
	fclose($handle);	

	return $guid_date_uri;
	
}

function getDateSubject($subject,$guid_date_uri,$date) {
	if ($subject == "" and $guid_date_uri != "") {
		return $guid_date_uri;
	} 
	if (strpos($subject,$date) < 1) {
		if (substr($subject,-1) == "/") {
			$subject .= $date;
		} else {
			$subject .= "/" . $date;
		}
	}
	return $subject;
	
}

function instantiateNewDocument($file_path,$local_dir,$date,$subject) {

	$latest_link = $local_dir . "/latest";
	$final_file_path = $local_dir . "/" . $date . ".rdf";

	if (is_dir($local_dir)) {
		copy($file_path,$final_file_path);
		unlink($latest_link);
		symlink($final_file_path,$latest_link);
	} else {
		// Make the directory
		mkdir($local_dir,0775,true);

		// Copy the new document into place and link it to latest
		copy($file_path,$final_file_path);
		symlink($final_file_path,$latest_link);
	}

	addDocumentToIndex($final_file_path,$subject);

	return true;
}	

function getProvenanceInfo($guid_uri,$subject,$local_dir) {

	if ($_POST["restored_from"]) {
		$info["restored_from"] = $_POST["restored_from"];
	}
	
	if (is_dir($local_dir)) {
		$previous = getCurrentDocumentURL($guid_uri,$local_dir);
		if ($previous) {
			$info["previous"] = $previous;
		}
	}
	return $info;	

}	

function getCurrentDocumentURL($subject,$local_dir) {
	$latest_link = $local_dir . "/latest";
	$graph = getGraph($latest_link);
	$subjects = $graph->allSubjects()->join(", ");
        
	$subject_array = explode(", ", $subjects);
	foreach($subject_array as $num => $local_subject) {
		if (strpos($local_subject,$subject) !== false) {
			return $local_subject;
		}
	}
	return $null;
}

function getBlankDocumentRef() {
	
	$data = getBlankDocumentData();

	return writeDocumentToFile($data);
	
}

function writeDocumentToFile($data) {

	$file_path = tempnam(sys_get_temp_dir(),'RDF_Admin');
	$handle = fopen($file_path,"w");
	fwrite($handle,$data);
	fclose($handle);
	
	return $file_path;
}

function getBlankDocumentData() {
	$data = '<?xml version="1.0"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	 xmlns:foaf="http://xmlns.com/foaf/0.1/"
	 xmlns:dcterms="http://purl.org/dc/terms/">

</rdf:RDF>';

	return $data;
}


?>
