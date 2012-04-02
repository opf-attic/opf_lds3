<?php

	require_once('connect.inc.php');
	
	function addExpiryToDatabaseIndex($guid,$expiry_time) {
		$query = "INSERT INTO Expires_Index values('$guid','$expiry_time');";
		$res = mysql_query($query);
	}
	
	function removeExpiredIndex($guid) {
		$query = "DELETE FROM Expires_Index where guid='$guid';";
		$res = mysql_query($query);
	}

	function isReservedGUID($guid) {
		$query = "SELECT * from Expires_Index where guid='$guid';";
                $res = mysql_query($query);
		if (mysql_num_rows($res) > 0) {
			return true;
		}
		return false;
	}

	function removeAllExpiredGUIDs() {

		$query = "SELECT guid from Expires_Index where expiry_time<" . time() . ";";
		$res = mysql_query($query);
		while ($row = mysql_fetch_row($res)) {
			removeExpiredDocumentsForGUID($row[0]);
		}

	}

	function removeExpiredDocumentsForGUID($guid) {

		removeExpiredIndex($guid);

		removeExpiredFiles($guid);		
		
	}

	function removeExpiredFiles($guid) {
		
		include('config.php');
	
		$dir = $local_doc_prefix . $guid;

		if ($dir != $local_doc_prefix) {
			deleteDir($dir);
		}

	}

	function deleteDir($dir) { 

		if (substr($dir, strlen($dir)-1, 1) != '/') {
			$dir .= '/'; 
		}

		if ($handle = opendir($dir)) { 
			while ($obj = readdir($handle)) { 
				if ($obj != '.' && $obj != '..') { 
					if (is_dir($dir.$obj)) { 
						if (!deleteDir($dir.$obj)) 
							return false; 
					} 
					elseif (is_file($dir.$obj)) { 
						if (!unlink($dir.$obj)) 
							return false; 
					} 
				} 
			} 

			closedir($handle); 
	
			if (!@rmdir($dir)) 
				return false; 
			return true; 
		} 
		return false; 
	
	}  
	
?>
