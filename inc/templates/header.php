<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Web interface for the Statistics plugin">
    <meta name="author" content="Samerton, PickNChew">
    <link rel="icon" href="<?php echo $path; ?>favicon.ico">

    <title><?php echo $title; ?> - Statistics Web Interface</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo $path; ?>assets/css/bootstrap.css" rel="stylesheet">
	<link href="<?php echo $path; ?>assets/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet">
	
	<style>
	body {
		padding-top: 70px;
	}
	.graph-container {
		box-sizing: border-box;
		width: 825px;
		height: 450px;
		padding: 20px 15px 15px 15px;
		margin: 15px auto 30px auto;
		border: 1px solid #ddd;
		background: #fff;
		background: linear-gradient(#f6f6f6 0, #fff 50px);
		background: -o-linear-gradient(#f6f6f6 0, #fff 50px);
		background: -ms-linear-gradient(#f6f6f6 0, #fff 50px);
		background: -moz-linear-gradient(#f6f6f6 0, #fff 50px);
		background: -webkit-linear-gradient(#f6f6f6 0, #fff 50px);
		box-shadow: 0 3px 10px rgba(0,0,0,0.15);
		-o-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
		-ms-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
		-moz-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
		-webkit-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
	}

	.graph-placeholder {
		width: 100%;
		height: 100%;
		font-size: 14px;
		line-height: 1.2em;
	}
	</style>
  </head>