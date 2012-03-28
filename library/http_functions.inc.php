<?php

	function outputHTTPHeader($code) {
		switch ($code) {
			case 200:
				header("HTTP/1.1 200 OK",true);
				break;
			case 201:
				header("HTTP/1.1 201 Created",true);
				break;
			case 202:
				header("HTTP/1.1 200 Accepted",true);
				break;
			case 204:
				header("HTTP/1.1 204 No Content",true);
				break;
			case 400:
				header("HTTP/1.1 400 Bad Request",true);
				break;
			case 401:
				header("HTTP/1.1 401 Unauthorized",true);
				break;
			case 403:
				header("HTTP/1.1 403 Forbidden",true);
				break;
			case 404:
				header("HTTP/1.1 404 Not Found",true);
				break;
			case 405:
				header("HTTP/1.1 405 Method Not Allowed",true);
				break;
			case 409:
				header("HTTP/1.1 409 Conflict",true);
				break;
			case 410:
				header("HTTP/1.1 410 Gone",true);
				break;
			case 500:
				header("HTTP/1.1 500 Internal Server Error",true);
				break;
			case 501:
				header("HTTP/1.1 501 Not Implemented",true);
				break;
				
		}
	}

?>
