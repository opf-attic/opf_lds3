<?php

function addDocumentToIndex($file_path,$subject) {
	require('config.php');

	$cmd = 'curl -T ' . $file_path . ' ' . $index_edit_path . $subject;
	exec($cmd);
}

function removeDocumentFromIndex($document) {
	require('config.php');

	$cmd = 'curl -X DELETE "' . $index_edit_path . $document. '"';
	exec($cmd);
}

?>
