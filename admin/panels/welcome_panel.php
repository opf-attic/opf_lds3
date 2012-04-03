		<div class="panel">
			<header>
			<h1>Welcome</h1>
			</header>
			<section>
			
			<p>
			This is the Linked Data admin interface for <a href="http://<?php echo $_SERVER["SERVER_NAME"];?>">http://<?php echo $_SERVER["SERVER_NAME"];?></a>.
			</p><br/>
			<p>
			<b>All Data</b> stored in this system must refers to URIs in the http://<?php echo $_SERVER["SERVER_NAME"];?> namespace following the URI schema outlined here:<br/>
			<ul style="margin-left: 2em">
				<li>/doc/<i>GUID</i> - An RDF graph containing data (the raw data)</li>
				<li>/id/<i>whatever</i> - An entity or object, physical or virtual</li>
				<li>/ref/<i>whatever</i> - Reference data, e.g. results of an experiment which uses entities</li>
				<li>/<i>other</i>/... - Another dataset or logical cool URI (if really necessary)</li> 
			</ul>
			</p><br/>
			<p>
			Using this interface it is possible to:<br/>
			<ul style="margin-left: 2em">
				<li>Add/Update/Remove Documents (Coming Soon)</li>
				<li>Manage Namespaces (Coming Soon)</li>
				<li>Configure the Peulia API (Coming Soon)</li>
			</ul> 
			</p><br/>
			<p>
			You can <a href="example.rdf">Download An Example</a> of an RDF document describing some data in this system which can be used as a template. When documents are upload provenance information is automatically added!
			</p><br/>
			</section>
		</div>
