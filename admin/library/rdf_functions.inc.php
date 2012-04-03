<?php

include_once("library/graphite/arc/ARC2.php");
include_once("library/graphite/Graphite.php");

function getSubjects($input_path) {

	$graph = new Graphite();
	$graph->load( $input_path );
	$subjects = $graph->allSubjects()->join(", ");

	$subject_array = explode(", ", $subjects);

	foreach($subject_array as $num => $subject) {
		$label = trim($graph->resource($subject)->get("rdfs:label") . "\n");
		$comment = trim($graph->resource($subject)->get("rdfs:comment") . "\n");
		$creator = trim($graph->resource($subject)->get("dct:creator") . "\n");
		$dump = $graph->resource($subject)->dump();
		if ($label != "[NULL]" and $label != "") {
			$subject_tree[$subject]["rdfs:label"] = $label; 
		}
		if ($comment != "[NULL]" and $comment != "") {
			$subject_tree[$subject]["rdfs:comment"] = $comment; 
		}
		if ($creator != "[NULL]" and $creator != "") {
			$subject_tree[$subject]["dct:creator"] = "<" . $creator . ">"; 
		}
		$dump = str_replace($subject,"",$dump);
		$dump = str_replace("padding-left: 3em","padding-left: 1em",$dump);
		$dump = str_replace("text-align:left;font-family: arial;padding:0.5em; background-color:lightgrey;border:dashed 1px grey;margin-bottom:2px;","text-align:left;font-family: arial;padding:0.5em; font-size: 0.8em; background-color:lightgrey;border:dashed 1px grey;margin-bottom:2px;margin-left:2em;margin-right:2em;",$dump);
		$subject_tree[$subject]["dump"] = $dump;
	}

	return $subject_tree;
	
}

function getGraph($input_path) {

	$graph = new Graphite();
	$graph->load( $input_path );

	return $graph;
}

?>
