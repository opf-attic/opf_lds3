<?php
include_once('config.php');

function get_guid($subject) {
	global $http_doc_prefix,$local_doc_prefix;
	
	if (substr($subject,0,strlen($http_doc_prefix)) == $http_doc_prefix) {
		$id = substr($subject,(strpos($subject,$http_doc_prefix) + strlen($http_doc_prefix)),strlen($subject));
		$id = @substr($id,0,strlen("A98C5A1E-A742-4808-96FA-6F409E799937"));
		if (preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $id)) {
			return $id;
		}
	}
	return null;
}

/**
  *
  * WARNING - THIS IS NOT A NICE PIECE OF CODE, CAN'T HAVE IT ALL.
  *
  */
function selectSubjectDisplay($subject_tree,$guid) {
	global $http_doc_prefix,$local_doc_prefix;

	$path_count = 0;
	$import_disable = 0;
	foreach ($subject_tree as $subject => $data) {
		$id = get_guid($subject);
		if ($id) {
			$guid = $id;
			$path_count++;
		}
	}
	if ($path_count > 1) {
		$import_disable = 1;
		output_message("The imported document is masquerading as 2 documents, please remove one of the $http_doc_prefix/GUID references from this document","error");
	} else {
		$doc_url = $http_doc_prefix . $guid;
		$doc_path = $local_doc_prefix . $guid;
		if (is_dir($doc_path)) {
			$replace = 1;
		}
	}

	if ($debug) {
		echo "Path Count " . $path_count . "<br/>";
		echo "doc_url = $doc_url <br/>";
		echo "guid = $guid <br/>";
	}

	echo '<script type="text/javascript">' . "\n";
	echo 'function handleClick() {
		chosen = ""
			len = document.mainform.subject.length

			for (i = 0; i <len; i++) {
				if (document.mainform.subject[i].checked) {
					chosen = document.mainform.subject[i].value
				}
			}

		if (chosen == "") {
		}
		else {
			hideall();
			show(chosen); 
		}
	}';
	echo 'function hideall() {' . "\n";
	echo "\t" . 'document.getElementById("'.$doc_url.'").style.display = "none";' . "\n";
	foreach ($subject_tree as $subject => $data) {
		echo "\t" . 'document.getElementById("'.$subject.'").style.display = "none";' . "\n";
	}
	echo '}';
	echo 'function hidealldata() {' . "\n";
	echo "\t" . 'document.getElementById("'.$doc_url.'_data").style.display = "none";' . "\n";
	echo "\t" . 'document.getElementById("'.$doc_url.'_minus").style.display = "none";' . "\n";
	echo "\t" . 'document.getElementById("'.$doc_url.'_plus").style.display = "block";' . "\n";
	foreach ($subject_tree as $subject => $data) {
		echo "\t" . 'document.getElementById("'.$subject.'_data").style.display = "none";' . "\n";
		echo "\t" . 'document.getElementById("'.$subject.'_minus").style.display = "none";' . "\n";
		echo "\t" . 'document.getElementById("'.$subject.'_plus").style.display = "block";' . "\n";
	}
	echo '}';
	echo 'function show(id) {' . "\n";
	echo "\t" . 'document.getElementById(id).style.display = "block";' . "\n";
	echo '}' . "\n";
	echo 'function show_data(id) {' . "\n";
	echo "\t" . 'data = id + "_data";' . "\n";
	echo "\t" . 'plus = id + "_plus";' . "\n";
	echo "\t" . 'minus = id + "_minus";' . "\n";
	echo "\t" . 'document.getElementById(data).style.display = "block";' . "\n";
	echo "\t" . 'document.getElementById(plus).style.display = "none";' . "\n";
	echo "\t" . 'document.getElementById(minus).style.display = "block";' . "\n";
	echo '}' . "\n";
	echo 'function hide_data(id) {' . "\n";
	echo "\t" . 'data = id + "_data";' . "\n";
	echo "\t" . 'plus = id + "_plus";' . "\n";
	echo "\t" . 'minus = id + "_minus";' . "\n";
	echo "\t" . 'document.getElementById(data).style.display = "none";' . "\n";
	echo "\t" . 'document.getElementById(minus).style.display = "none";' . "\n";
	echo "\t" . 'document.getElementById(plus).style.display = "block";' . "\n";
	echo '}' . "\n";
	echo '</script>';
	echo '<div class="panel"><header>';
	echo '<h1>Please select the document namespace</h1>';
	echo '</header>';
	echo '<section style="width: 95%">';
	echo '<table class="rdftable">';
	if ($path_count < 1) {
		echo '<tr>';
		echo '<td class="radiotd"><input type="radio" CHECKED name="subject" value="'.$doc_url.'" onclick="handleClick()"/></td>';
		echo '<td class="dataplusminustd">';
		echo '<div id="'.$doc_url.'_plus" class="plusminus"></div>';
		echo '<div id="'.$doc_url.'_minus" class="plusminus" style="display: none;"></div>';
		echo '</td>';
		echo '<td class="subjecttd">'.$doc_url;
		echo '<input type="hidden" name="guid_uri" value="'.$doc_url.'"/>';
		echo '<div id="'.$doc_url.'" style="display: block;">';
		output_message("This is a URL created by the admin system and will be populated with provenence information on import","");
		echo '</div>';
		echo '<div id="'.$doc_url . '_data" style="display: none;"/>';
		echo '</td>';
		echo '</tr>';
	}
	if ($path_count < 2) {			
		echo '<input type="hidden" name="guid_uri" value="'.$doc_url.'"/>';
	}
	foreach ($subject_tree as $subject => $data) {
		echo '<tr';
		echo '>';
		echo '<td class="radiotd">';
		if (substr($subject,0,strlen($doc_url)) == $doc_url) {	
			echo '<input type="hidden" name="subject" value="'.$subject.'"/>';
			echo '<input type="radio" name="subject" value="'.$subject.'" CHECKED disabled"/>';
		} else if ($path_count == 1) {
			//echo '<input type="radio" name="subject" value="'.$subject.'" disabled"/>';
		} else {
			echo '<input type="radio" name="subject" value="'.$subject.'" onclick="handleClick()"/>';
		}
		echo '</td>';
		echo '<td class="dataplusminustd">';
		echo '<div id="'.$subject.'_plus" class="plusminus" onclick="show_data(\''.$subject.'\');">+</div>';
		echo '<div id="'.$subject.'_minus" class="plusminus" style="display: none;" onclick="hide_data(\''.$subject.'\');">-</div>';
		echo '</td>';
		echo '<td class="subjecttd">'.$subject;
		if ($path_count == 1 && (substr($subject,0,strlen($doc_url)) == $doc_url) && $replace)  {
			output_message("This document will replace one which already exists at this URL, if you do not wish this to happen please remove this URI and related data from the input document.","warning");
		} elseif ($path_count == 1 && substr($subject,0,strlen($doc_url)) == $doc_url) {
			output_message("Auto selected and valid document namespace","");
		} elseif ($path_count > 1 && get_guid($subject)) {
			output_message("This node conflicts with another highlighted node in this document","error");
		} else {
			echo '<div id="'.$subject.'" style="display: none;">';
			output_message("In order to import this document this URL will be changed to " . $doc_url . ", all related data will be retained.","warning");
		}
		echo '</div>';
		echo '<div id="'.$subject . '_data" style="display: none;">';
		echo $data["dump"];
		echo '</div>';
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</section>';
	echo '</div>';
	if (!$import_disable) {
		echo '<div style="padding-left: 10px; padding-right: 10px; padding-bottom: 5px; padding-top: 5px; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;width: 8%; float: right; height: 24px; font-size: 1.7em; background: none repeat scroll 0 0 lightgreen; cursor: pointer; margin-bottom: 10px;" onclick="document.forms[\'mainform\'].submit();">
	                <div style="margin-top: 6px;">Submit</div>
        	</div>';
	}
	
}

Function output_message($message, $class) {
        echo '<div class="message '.$class.'">';
        echo $message;
        echo '</div>';
}


?>
