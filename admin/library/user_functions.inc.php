<?php

	require_once('connect.inc.php');

	function getUserStatus() {

		$user_name = $_SESSION["user_name"];
		$user_email = $_SESSION["user_email"];
		$user_pic_path = $_SESSION["user_pic"];
		
		if (!userExists($user_email)) {
			addUser($user_name,$user_email,$user_pic_path);
		} 
		
		return getUserAccountStatus($user_email);

	}

	function getUserID($user_email) {
		
		$query = "select id from Users where Users.email='$user_email';";
		$res = mysql_query($query);
		$row = mysql_fetch_row($res);
		return $row[0];
	}
	
	function getUserName($user_email) {
		$query = "select name from Users where Users.email='$user_email';";
		$res = mysql_query($query);
		$row = mysql_fetch_row($res);
		return $row[0];
	}

	function userExists($user_email) {
		$query = "select * from Users where Users.email='$user_email';";
		$res = mysql_query($query);
		$num_rows = mysql_num_rows($res);
		if ($num_rows > 0) {
			return true;
		}
		return false;
	}

	function getUserAccountStatus($user_email) {
		$query = "select enabled from Users where Users.email='$user_email';";
		$res = mysql_query($query);
		if (mysql_num_rows($res) < 1) {
			return "unregistered";
		}
		$row = mysql_fetch_row($res);
		$status = $row[0];
		switch ($status) {
			case -1:
			   return "banned";
			case 0:
			   return "inwaiting";
			case 1:
			   return "active";
		}
	}

	function addUser($user_name,$user_email,$user_pic_path) {
		$user_status = getDefaultUserStatus();

		$query = "INSERT into Users set name='$user_name', pic_path='$user_pic_path', email='$user_email', enabled=$user_status;";
		$res = mysql_query($query);
	}

	function getDefaultUserStatus() {
		require('config.php');
		if ($moderate_accounts) {
			return 0;
		} else {
			return 1;
		}
		return 1;
	}

	function getEmailFromAccessKey($access_key) {
		$query = 'select Users.email from Users INNER JOIN Access_Keys ON Users.id=Access_Keys.user_id where Access_Keys.access_key="'.$access_key.'"';
		$res = mysql_query($query);
		$row = mysql_fetch_row($res);
		return ($row[0]);
	}

	function addUserDataToGraph($graph,$subject,$date,$user_key) {
		require_once("library/graphite/arc/ARC2.php");
		require_once("library/graphite/Graphite.php");
		include("config.php");
		
		// Write all the import data in using graphite
	
		$user_email = getEmailFromAccessKey($user_key);
		$user_id = getUserID($user_email);
		$user_name = getUserName($user_email);
		$key_description = getAccessKeyDescription($user_key);

		$user_uri = $id_uri_prefix . "users/" . $user_id;
		$access_key_uri = $id_uri_prefix . "users/".$user_id . "/access_keys/" . $user_key;
		
		$to_add = '
			@prefix dct: <http://purl.org/dc/terms/> .
	
			<' . $subject .'>
			dct:publisher <'.$access_key_uri.'>;
			dct:dateSubmitted "'.$date.'".
		    ';

		$added = $graph->addTurtle($subject,$to_add);
		
		$to_add = '
			@prefix foaf: <http://xmlns.com/foaf/0.1/> .
			@prefix lds: <http://schema.lds3.org/> .
	
			<' . $user_uri .'>
			foaf:name "'.$user_name.'";
			foaf:account <'.$access_key_uri.'>;
			rdf:type <http://schema.lds3.org/User> .
		    ';
		
		$added = $graph->addTurtle($user_uri,$to_add);
		
		$to_add = '
			@prefix dct: <http://purl.org/dc/terms/> .
			@prefix lds: <http://schema.lds3.org/> .
	
			<' . $access_key_uri .'>
			dct:identifier "'.$user_key.'";
			dct:description "'.$key_description.'";
			dct:creator <'.$user_uri.'>;
			rdf:type <http://schema.lds3.org/Access_Key>.
		    ';

		$added = $graph->addTurtle($access_key_uri,$to_add);

	        return $graph;
	}

	function getUserKeyFromURI($user_key_uri) {
		#FIXME: This function is a bit stupid

		return substr($user_key_uri,strrpos($user_key_uri,"/")+1,strlen($user_key_uri));
	}

	function userCanEdit($user_key,$guid_uri) {
		$local_dir = getLocalFromURI($guid_uri);
		$current = getLocalFromURI(getCurrentDocumentURL($guid_uri,$local_dir));
		$graph = getGraph($file_path);
		$subject = getGUIDURIFromLocal($current);
		$graph = getGraph($current . ".rdf");

		$resource = $graph->resource($subject);

		foreach ($resource->all("dct:publisher") as $user_key_uri) {
			$user_key_in = getUserKeyFromURI($user_key_uri);
			
			// Strait match on the key
			if ($user_key_in == $user_key) {
				return true;
			}
		
			$query = "select user_id from Access_Keys where Access_Keys.access_key='$user_key_in';";
			$res = mysql_query($query);
			$row = @mysql_fetch_row($res);
			$user_check = @$row[0];
	
			$query = "select user_id from Access_Keys where Access_Keys.access_key='$user_key';";
			$res = mysql_query($query);
			$row = @mysql_fetch_row($res);
			$user_in = @$row[0];

			if ($user_check == $user_in) {
				return true;
			}
			
		}

		return false;
		
	}

?>
