<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

    <meta charset="utf-8">
    <title>SPARQL Endpoint</title>
    <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <link rel="stylesheet" href="stylesheets/base.css">
    <link rel="stylesheet" href="stylesheets/skeleton.css">
    <link rel="stylesheet" href="stylesheets/layout.css">
    <link rel="stylesheet" href="js/CodeMirror/lib/codemirror.css">
    <link rel="stylesheet" href="js/CodeMirror/theme/default.css">

    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">

    <style type="text/css">
        .section h1 {
            font-size: 150%;
            line-height: 100%;
            margin-bottom: 0.3em;
        }

        #sample_section p {
            margin-bottom: 0.5em;
        }

        #sample_queries {
            display: none;
        }

        #sample_queries ul li {
            margin:0;
        }

        #query_textarea {
            border: 1px solid black;
	    background: white;
        }

        #footer p {
            font-size: 80%;
            border-top: 1px solid #dddddd;
            margin-top: 0.7em;
            text-align: center;
        }

        p.subheading {
            font-size: 75%;
            color: #666666;
            padding: 0;
            margin: 0;
            margin-left: 0.5em;
        }

        .results {
            font-size: 85%;
        }

        .results table {
            border-collapse: separate;
            border-spacing: 5px;
        }

        .results th {
            border-bottom: 5px solid #dddddd;
            padding-bottom: 5px;
            font-weight: bold;
        }

        .results td {
            border-bottom: 1px solid #dddddd;
        }
    </style>

</head>
<body>
    <div class="container">
        <div class="sixteen columns">
	<div id="header">
<?php
	@include('../config.php');
	@include('config.php');
	
	echo '<h2><a href="http://'.$base_domain.'/">'.$base_domain.'</a></h2>
	<ul class="right"><li><a href="'.$company_website.'">home</a></li><li><a href="/sparql/">query</a></li><li><a href="'.$company_website.'/contact">contact</a></li></ul>';
?>
	<div style="clear: both;"></div>
	</div>
	<h4 style="padding-bottom: 0.5em; font-weight: bold;">SPARQL Query Interface</h4>
        </div>
        <?php echo $query_form_open; ?>
        <div class="sixteen columns" id="sample_section">
<?php
/*
            <p>
                <a id="toggle_sample_queries" style="cursor: pointer;">[+] show sample queries</a>
            </p>
            <div class="section" id="sample_queries">
                <h1>Sample Queries</h1>
                <ul class="square"><?php echo $sample_queries; ?></ul>
            </div>
*/ 
?>
            <p>
                <label for="output">Output format (if supported by query type):</label>
                <?php echo $query_options; ?>
<!--            </p>-->
	    <br/>
<!--            <fieldset>
                <legend>Show results inline:</legend>
                <?php //echo $show_inline_input; ?>-->
            </fieldset>
<!--            <p>-->
                <label for="jsonp">JSONP callback (for JSON results):</label>
                <?php echo $jsonp_input; ?>
            </p>
        </div>
        <div class="two-thirds column section">
            <h1>Query</h1>
            <div id="query_textarea"><?php echo $query_form; ?></div>
            <p class="subheading">
               Enabled operations: <?php echo $endpoint_features; ?>
               (Max. number of results: <?php echo $endpoint_max_results; ?>)
            </p>
        </div>
        <div class="sixteen columns">
            <input type="submit" value="Send Query" />
            <input type="button" value="Load Sample Query" id="load_namespaces" />
	    <span style="border: none; padding: 0.4em;">
                <b>HTTP method:</d>
                <?php echo $http_method_input; ?>
	    </span>
        </div>
        </form>
        <div class="sixteen columns section" id="results_table">
            <?php //echo $results; ?>
        </div>
        <div class="sixteen columns" id="footer">
            <p>SPARQL Endpoint created by Dave Challis, using <a href="https://github.com/semsol/arc2/wiki">ARC2</a>, <a href="http://codemirror.net/">CodeMirror</a> and <a href="http://www.getskeleton.com/">Skeleton</a>.</p>
        </div>
    </div><!-- container -->

    <script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
    <script src="js/CodeMirror/lib/codemirror.js"></script>
    <script src="js/CodeMirror/mode/sparql/sparql.js"></script>
    <script>
        $(document).ready(function() {
            $('.http_method_radio').click(function() {
                $('#sparql-form').attr('method', $(this).val());
            });

            $('#toggle_sample_queries').click(function() {
                if ($('#sample_queries').is(':visible')) {
                    $('#toggle_sample_queries').text('[+] show sample queries');
                }
                else {
                    $('#toggle_sample_queries').text('[-] hide sample queries');
                }
                $('#sample_queries').toggle('fast');
            });

            var myCodeMirror = CodeMirror.fromTextArea($('#query').get(0));

            $('#load_namespaces').click(function() {
                myCodeMirror.setValue(<?php echo $namespace_prefixes; ?>);
            });
        });
    </script>
</body>
</html>
