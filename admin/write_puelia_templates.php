<?php

ini_set('include_path','.:include/:panels/:library/');

require_once('write_puelia_templete.php');

write_puelia_config("users");
write_puelia_config("access_keys");
write_puelia_config("documents");
write_puelia_config("example");

?>
