<form name="quick_search" method="post" action".">
<div class="panel" style="height: 114px;">
	<header>
		<h1>Existing Document Search</h1>
	</header>
	<section style="width: 82%; float: left;">
		<!-- Submit on enter -->
		<input type="text" name="quick_search" style="font-size: 2em; border: 0; width: 100%; color: #555; margin: -5px;" value="http://<?php echo $_SERVER["SERVER_NAME"];?>/doc/{GUID}/latest"/>
	</section>
	<section style="width: 8%; float: right; height: 24px; font-size: 1.7em; background: none repeat scroll 0 0 lightgreen; cursor: pointer;" onclick="document.forms['quick_search'].submit();">
		<div style="margin-top: 6px;">Submit</div>
	</section>
</div>
</form>
