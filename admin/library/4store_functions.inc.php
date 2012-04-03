<?php


function addDocumentToIndex($final_file_path,$subject) {
	
	include('config.php');

	$cmd = 'curl -T ' . $final_file_path . " " . $index_edit_url . $subject;
	exec($cmd);

}

function removeDocumentFromIndex($previous) {
	
	include('config.php');

	$cmd = "curl -X DELETE '".$index_edit_url.$previous."'";

        exec($cmd);

	
}
	
?>
