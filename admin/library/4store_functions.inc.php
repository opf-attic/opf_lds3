<?php


function addDocumentToIndex($final_file_path,$subject) {
	
	include('config.php');

	$cmd = 'curl -T ' . $final_file_path . " " . $index_edit_url . $subject;

	exec($cmd);

	clearPueliaCache();

}

function removeDocumentFromIndex($previous) {
	
	include('config.php');

	$cmd = "curl -X DELETE '".$index_edit_url.$previous."'";

        exec($cmd);

	clearPueliaCache();

	
}

function clearPueliaCache() {

	include('config.php');
		
	$d = dir($puelia_cache_dir);
	
	while ($entry = $d->read()) {
		if ($entry!= "." && $entry != "..") {
			unlink($puelia_cache_dir . "/" . $entry);
		}
	}
	$d->close();

}
	
?>
