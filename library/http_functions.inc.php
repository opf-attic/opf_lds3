<?php

	function outputHTTPHeader($code) {
		if ($code == 200) {
			header("HTTP/1.1 200 OK",true);
		}
		if ($code = 201) {
			header("HTTP/1.1 201 Created",true);
		}
		if ($code = 202) {
			header("HTTP/1.1 200 Accepted",true);
		}
		if ($code = 204) {
			header("HTTP/1.1 204 No Content",true);
		}
		if ($code = 400) {
			header("HTTP/1.1 400 Bad Request",true);
		}
		if ($code = 401) {
			header("HTTP/1.1 401 Unauthorized",true);
		}
		if ($code = 403) {
			header("HTTP/1.1 403 Forbidden",true);
		}
		if ($code = 404) {
			header("HTTP/1.1 404 Not Found",true);
		}
		if ($code = 405) {
			header("HTTP/1.1 405 Method Not Allowed",true);
		}
		if ($code = 409) {
			header("HTTP/1.1 409 Conflict",true);
		}
		if ($code = 410) {
			header("HTTP/1.1 410 Gone",true);
		}
		if ($code = 500) {
			header("HTTP/1.1 500 Internal Server Error",true);
		}
		if ($code = 501) {
			header("HTTP/1.1 501 Not Implemented",true);
		}
	}

?>
