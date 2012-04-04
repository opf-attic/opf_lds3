<?php

function getDatasetCamel($dataset) {
	$dataset_parts = explode("_",$dataset);
	for ($i=0;$i<count($dataset_parts);$i++) {
		$dataset_camel .= strtoupper(substr($dataset_parts[$i],0,1)) . substr($dataset_parts[$i],1,strlen($dataset_parts[$i])) . "_";
	}
	$dataset_camel = substr($dataset_camel,0,strlen($dataset_camel)-1);
	return $dataset_camel;
}

function write_puelia_config($dataset) {

	if ($dataset == "example") {
		write_example_people();
		return;
	}
	
	if ($dataset == "documents") {
		write_document_template();
		return;
	}

	include('config.php');

	$dataset_singular = $dataset;
	if (substr($dataset_singular,strlen($dataset_singular)-1,strlen($dataset_singular)) == "s") {
		$dataset_singular = substr($dataset_singular,0,strlen($dataset_singular)-1);
	}

	$dataset_template = $dataset . "/{NodeID}" ;


$config = '
@prefix spec: <http://'.$base_domain.'/api_'.$dataset.'#> .
@prefix lds: <http://schema.lds3.org/> .
@prefix contact: <http://www.w3.org/2000/10/swap/pim/contact#> .

@prefix api: <http://purl.org/linked-data/api/vocab#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix dc: <http://purl.org/dc/elements/1.1/> .
@prefix dct: <http://purl.org/dc/terms/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix skos: <http://www.w3.org/2004/02/skos/core#> .
@prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> .
@prefix org: <http://www.w3.org/ns/org#> .
@prefix qb: <http://purl.org/linked-data/cube#> .

############################################################################################
#
# API DESCRIPTION
#
############################################################################################

spec:api
	a api:API ;
	rdfs:label "LDS3 '. str_replace("_"," ",getDatasetCamel($dataset)) .'"@en;
	dct:description "A list of the '.str_replace("_"," ",getDatasetCamel($dataset)).' currently indexed in this LDS3 endpoint";
	api:maxPageSize "300";
	api:defaultPageSize "20" ;
	api:sparqlEndpoint <http://'.$base_domain.'/sparql/index.php> ;
	api:lang "en";
	api:defaultViewer api:labelledDescribeViewer ;
	api:viewer 
		api:describeViewer ,
		api:labelledDescribeViewer ,
		spec:minimalViewer ;
	api:defaultFormatter spec:htmlFormatter ;
	api:formatter spec:csvFormatter ;
	api:endpoint spec:exampleData, spec:exampleDataItem .

#
# List all example data 
#
# /exampleData
#
# SELECT DISTINCT ?item WHERE { 
#        ?item rdf:type lds3:ExampleData
# }
#

spec:exampleData
	a api:ListEndpoint ;
	api:uriTemplate "/id/'.$dataset.'" ;
	dct:description "Listing of all '. str_replace("_"," ",getDatasetCamel($dataset)) .'." ;
	api:selector spec:exampleDataSelector ;
	.

spec:exampleDataSelector
  a api:Selector ;
  api:where "?item rdf:type lds:'.getDatasetCamel($dataset_singular).'" ;
  .

spec:exampleDataItem
	a api:ItemEndpoint ;
	api:uriTemplate "/id/'.$dataset.'/{NodeID}" ;
	api:itemTemplate "http://'.$base_domain.'/id/'.$dataset_template.'" ;
	dct:description "Individual '. str_replace("_"," ",getDatasetCamel($dataset)) .'." ;
	.

spec:minimalViewer
  a api:Viewer ;
  api:name "minimal" ;
  api:properties "prefLabel,altLabel,name,label"
  .

rdf:type api:label "type" .
rdf:about api:label "about" .
lds:TestData api:label "'. str_replace("_"," ",getDatasetCamel($dataset)) .'" .

spec:htmlFormatter
  a api:XsltFormatter ;
  api:name "html" ;
  api:mimeType "text/html" , "application/xhtml+xml" ;
  api:stylesheet "xslt/org.xsl" ;
  .

spec:csvFormatter
  a api:XsltFormatter ;
  api:name "csv" ;
  api:mimeType "text/csv" ;
  api:stylesheet "xslt/org-csv.xsl" .';

	$file_name = $dataset . "." . $base_domain . ".ttl";
	$file_path = $puelia_api_config_files_dir . $file_name;

	$handle = fopen($file_path,"w");
	if ($handle) {
		fwrite($handle,$config);
	}
	fclose($handle);
}

function write_example_people() {
	include ('config.php');

	$dataset = "example";

$config = '@prefix spec: <http://'.$base_domain.'/api_people#> .
@prefix lds: <http://schema.lds3.org/> .
@prefix contact: <http://www.w3.org/2000/10/swap/pim/contact#> .

@prefix api: <http://purl.org/linked-data/api/vocab#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix dc: <http://purl.org/dc/elements/1.1/> .
@prefix dct: <http://purl.org/dc/terms/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix skos: <http://www.w3.org/2004/02/skos/core#> .
@prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> .
@prefix org: <http://www.w3.org/ns/org#> .
@prefix qb: <http://purl.org/linked-data/cube#> .

############################################################################################
#
# API DESCRIPTION
#
############################################################################################

spec:api
	a api:API ;
	rdfs:label "LDS3 Example"@en;
	dct:description "The example people data for LDS3";
	api:maxPageSize "300";
	api:defaultPageSize "20" ;
	api:sparqlEndpoint <http://'.$base_domain.'/sparql/index.php> ;
	api:lang "en";
	api:defaultViewer api:labelledDescribeViewer ;
	api:viewer 
		api:describeViewer ,
		api:labelledDescribeViewer ,
		spec:minimalViewer ;
	api:defaultFormatter spec:htmlFormatter ;
	api:formatter spec:csvFormatter ;
	api:endpoint spec:exampleData, spec:exampleDataItem .

#
# List all example data 
#
# /exampleData
#
# SELECT DISTINCT ?item WHERE {
#        ?item rdf:type lds:ExampleData
# }
#

spec:exampleData
	a api:ListEndpoint ;
	api:uriTemplate "/id/person" ;
	dct:description "Listing of all example data." ;
	api:selector spec:exampleDataSelector ;
	.

spec:exampleDataSelector
  a api:Selector ;
  api:where "?item rdf:type contact:Person" ;
  .

spec:exampleDataItem
	a api:ItemEndpoint ;
	api:uriTemplate "/id/person/{NodeID}" ;
	api:itemTemplate "http://'.$base_domain.'/id/person/{NodeID}" ;
	dct:description "Individual Entries of Example Data." ;
	.

spec:minimalViewer
  a api:Viewer ;
  api:name "minimal" ;
  api:properties "prefLabel,altLabel,name,label"
  .

rdf:type api:label "type" .
rdf:about api:label "about" .
lds:TestData api:label "Test Data" .

spec:htmlFormatter
  a api:XsltFormatter ;
  api:name "html" ;
  api:mimeType "text/html" , "application/xhtml+xml" ;
  api:stylesheet "xslt/org.xsl" ;
  .

spec:csvFormatter
  a api:XsltFormatter ;
  api:name "csv" ;
  api:mimeType "text/csv" ;
  api:stylesheet "xslt/org-csv.xsl" .';


	$file_name = $dataset . "." . $base_domain . ".ttl";
	$file_path = $puelia_api_config_files_dir . $file_name;

	$handle = fopen($file_path,"w");
	if ($handle) {
		fwrite($handle,$config);
	}
	fclose($handle);
}


function write_document_template() {
	include ('config.php');

	$dataset = "documents";

$config = '@prefix spec: <http://'.$base_domain.'/api_documents#> .
@prefix lds: <http://schema.lds3.org/> .
@prefix contact: <http://www.w3.org/2000/10/swap/pim/contact#> .

@prefix api: <http://purl.org/linked-data/api/vocab#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix dc: <http://purl.org/dc/elements/1.1/> .
@prefix dct: <http://purl.org/dc/terms/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix skos: <http://www.w3.org/2004/02/skos/core#> .
@prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> .
@prefix org: <http://www.w3.org/ns/org#> .
@prefix qb: <http://purl.org/linked-data/cube#> .

############################################################################################
#
# API DESCRIPTION
#
############################################################################################

spec:api
	a api:API ;
	rdfs:label "LDS3 Documents"@en;
	dct:description "The documents dataset for LDS3";
	api:maxPageSize "300";
	api:defaultPageSize "20" ;
	api:sparqlEndpoint <http://'.$base_domain.'/sparql/index.php> ;
	api:lang "en";
	api:defaultViewer api:labelledDescribeViewer ;
	api:viewer 
		api:describeViewer ,
		api:labelledDescribeViewer ,
		spec:minimalViewer ;
	api:defaultFormatter spec:htmlFormatter ;
	api:formatter spec:csvFormatter ;
	api:endpoint spec:exampleData, spec:exampleDataItem .

#
# List all example data 
#
# /exampleData
#
# SELECT DISTINCT ?item WHERE {
#        ?item rdf:type lds:ExampleData
# }
#

spec:exampleData
	a api:ListEndpoint ;
	api:uriTemplate "/doc" ;
	dct:description "Listing of all Documents data." ;
	api:selector spec:exampleDataSelector ;
	.

spec:exampleDataSelector
  a api:Selector ;
  api:where "?item rdf:type lds:Graph" ;
  .

spec:exampleDataItem
	a api:ItemEndpoint ;
	api:uriTemplate "/doc/{NodeID}" ;
	api:itemTemplate "http://'.$base_domain.'/doc/{NodeID}" ;
	dct:description "Individual Entries of Document/Graph Data." ;
	.

spec:minimalViewer
  a api:Viewer ;
  api:name "minimal" ;
  api:properties "prefLabel,altLabel,name,label"
  .

rdf:type api:label "type" .
rdf:about api:label "about" .
lds:TestData api:label "Document Data" .

spec:htmlFormatter
  a api:XsltFormatter ;
  api:name "html" ;
  api:mimeType "text/html" , "application/xhtml+xml" ;
  api:stylesheet "xslt/org.xsl" ;
  .

spec:csvFormatter
  a api:XsltFormatter ;
  api:name "csv" ;
  api:mimeType "text/csv" ;
  api:stylesheet "xslt/org-csv.xsl" .';


	$file_name = $dataset . "." . $base_domain . ".ttl";
	$file_path = $puelia_api_config_files_dir . $file_name;

	$handle = fopen($file_path,"w");
	if ($handle) {
		fwrite($handle,$config);
	}
	fclose($handle);
}
