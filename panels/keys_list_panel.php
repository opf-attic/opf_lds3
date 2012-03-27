<?php
	require_once('key_management.inc.php');
	$user_id = getUserID();
	if ($user_id) {
		$keys = getKeyPairs($user_id);	
	}
?>
<div class="panel">
	<header>
		<h1>Your Key-Pairs</h1>
	</header>
	<section style="width: 95%;">
		<table class="keystable">
			<tr>
				<th style="width: 35%;">Key</th>
				<th style="width: 65%;">Secret</th>
			</tr>
			<?php 
				for ($i=0;$i<count($keys);$i++) {
					$key = $keys[$i]["access_key"];
					$secret = $keys[$i]["secret"];
					$description = $keys[$i]["descrip"];
					
					echo '<tr class="keyrow"><td>' . $key . '</td><td>' . $secret . '</td></tr>';
					echo '<tr class="descrow"><td colspan="2"><b>Description: </b>' . $description . '</td></tr>';
					echo '<tr class="blankrow"><td colspan="2">&nbsp;</td></tr>';
				}
			?>	
		</table>
	</section>
</div>
