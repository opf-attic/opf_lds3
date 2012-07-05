<?php


function addDocumentToIndex($final_file_path,$subject,$count = 0) {
	
	include('config.php');

	$cmd = 'curl -T ' . $final_file_path . " " . $index_edit_url . $subject;

	exec($cmd,$ret);

	$line = $ret[0];
        $bits = explode(" ",$line);
        $code = $bits[0];

        if (($code < 200 || $code > 299) && $count < $retry_limit) {
		$count++;
                addDocumentToIndex($final_file_path,$subject,$count);
        } elseif (($code < 200 || $code > 299) && $count >= $retry_limit) {
		return false;
	}

	clearPueliaCache();

}

function removeDocumentFromIndex($previous,$count = 0) {
	
	include('config.php');

	$cmd = "curl -X DELETE '".$index_edit_url.$previous."'";

        exec($cmd,$ret);

	$line = $ret[0];
        $bits = explode(" ",$line);
        $code = $bits[0];

        if (($code < 200 || $code > 299) && $count < $retry_limit) {
		$count++;
		removeDocumentFromIndex($previous,$count);
        } elseif (($code < 200 || $code > 299) && $count >= $retry_limit) {
		return false;
	}

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
