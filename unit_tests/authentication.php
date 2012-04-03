<?php
error_reporting(E_ALL & ~E_NOTICE);
$authentication_service = "LDS";
$show_content = false;

if ($_POST["show_content"]) {
        $show_content = true;
}
$accessKey = $_POST["accessKey"];
$secretKey = $_POST["secretKey"];
$authUrl = $_POST["url"];

if (!$show_content) {
        echo '<div align="center" style="margin: 4em;">';
}

if ($accessKey != "" and $secretKey != "" and $authUrl != "") {
        $base_url = substr($authUrl,7,strlen($authUrl));
        $base_url = substr($base_url,0,strpos($base_url,"/"));
        $thing = substr($authUrl,strlen($base_url)+7,strlen($authUrl));

        if (strpos($base_url,"amazon") != false) {
                $authentication_service = "AWS";
        }

        $date = time();
        $strtosign = "GET\n\n\n".date("r",$date)."\n$thing";

        $signature = base64_encode(hash_hmac("sha1", utf8_encode($strtosign), utf8_encode($secretKey), true));

        $url = $authUrl;
        $signed_url = $url . "?".$authentication_service."AccessKeyId=$accessKey&Expires=".$date."&Signature=".urlencode($signature);

        $auth_string = $authentication_service . " " . $accessKey . ":" . $signature;
        $date_header = "Date: " . date("r",$date);
        $auth_header = "Authorization: " . $auth_string;

        ini_set('user_agent', "PHP\r\n$date_header\r\n$auth_header");

        $stuff = @file_get_contents($url);
        $status = $http_response_header[0];
        $parts = explode(" ",$status,3);
        $code = trim($parts[1]);

        if ($code < 200 or $code > 299) {
                echo '<span style="font-size: 2em">';
                echo "Request Failed: " . $parts[1] . " " . $parts[2];
                echo '</span>';
        } elseif ($show_content) {
                for ($i=0;$i<count($http_response_header);$i++) {
                        $header = $http_response_header[$i];
                        if (strtolower(substr($header,0,strlen("content-type"))) == "content-type") {
                                $parts = explode(":",$header);
                                header('Content-Type: ' . trim($parts[1]));
                        }
                }
                print($stuff);
        } else {
                echo '<span style="font-size: 2em">Success</span>';
        }
} else {
        echo '<form name="myform" action="" method="POST">';
        echo '<table width="790px" style="font-size: 1.5em;">';
        echo '<tr><td width="40%">URL</td><td width="60%"><input style="padding: 0.2em; font-size: 0.9em; color: grey;" type="text" name="url" size="55" value="'.$authUrl.'"/></td></tr>';
        echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">The URL to test authenticate against</td></tr>';
        echo '<tr><td width="40%">Access Key</td><td width="60%"><input style="padding: 0.2em; font-size: 0.8em; color: grey;" type="text" size="30" name="accessKey" value="'.$accessKey.'"/></td></tr>';
        echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">Your Access Key provided by the service at the above URL</td></tr>';
        echo '<tr><td width="40%">Secret</td><td width="60%"><input style="padding: 0.2em; font-size: 0.8em; color: grey;" type="text" size="55" name="secretKey" value="'.$secretKey.'"/></td></tr>';
        echo '<tr><td width="40%">&nbsp;</td><td width="60%" style="font-size: 0.5em; color: grey;">The Secret Key linked to this Access Key</td></tr>';
        echo '<tr><td width="40%">Show Content</td><td width="60%"><input style="font-size: 0.8em; color: grey;" type="checkbox" name="show_content" ';
        if ($show_content) {
                echo "CHECKED";
        }
        echo '/><span style="font-size: 0.5em; color: grey;">Tick this box if you wish to display the returned content rather than just checking it can be retrieved.</span></td></tr>';
        echo '</table>';
        echo '<input style="font-size: 1em;" type="submit" name="submit" value="Submit"/>';
        echo '</form>';
}

if (!$show_content) {
        echo '</div>';
}
?>
