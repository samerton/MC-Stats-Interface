<?php
/*
 *  Web interface made by Samerton
 *  Statistics plugin made by PickNChew
 */
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Web interface for the Statistics Minecraft server plugin">
    <meta name="author" content="Samerton, PickNChew">

    <title>Statistics Web Interface - Install</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="assets/css/custom.css" rel="stylesheet">

  </head>

  <body>

    <div class="container">
	  <ul class="nav nav-pills nav-justified">
	    <li class="<?php if(!isset($_GET["step"])) { ?>active<?php } else { ?>disabled<?php } ?>"><a>Welcome</a></li>
	    <li class="<?php if(isset($_GET["step"]) && $_GET["step"] == "configuration") { ?>active<?php } else { ?>disabled<?php } ?>"><a>Configuration</a></li>
	    <li class="<?php if(isset($_GET["step"]) && $_GET["step"] == "user") { ?>active<?php } else { ?>disabled<?php } ?>"><a>Admin Account</a></li>
		<li class="<?php if(isset($_GET["step"]) && $_GET["step"] == "finish") { ?>active<?php } else { ?>disabled<?php } ?>"><a>Finish</a></li>
	  </ul>
	  <?php
	  if(!isset($_GET["step"])){
	  ?>
	  <h3>Welcome</h3>
	  <strong>Thanks for choosing the Statistics plugin!</strong><br /><br />
	  This installer will guide you through the process of installing the web interface. Please ensure you have:<br /><br />
	  <ul>
	  	<li>At least one Statistics plugin installation</li>
	  	<li>
	  	PHP 5.3+
	  	<?php
	  	if(version_compare(phpversion(), '5.3', '<')){
	  		$error = true;
	  	?>
	  	<p style="display: inline;" class="text-danger"><span class="glyphicon glyphicon-remove-sign"></span></p>
	  	<?php
	  	} else {
	  	?>
	  	<p style="display: inline;" class="text-success"><span class="glyphicon glyphicon-ok-sign"></span></p>
	  	<?php
	  	}
	  	?>
	  	</li>
	  	<li>
	  	PHP MySQLi Extension
	  	<?php 
	  	if(!function_exists('mysqli_connect')){
	  		$error = true;
	  	?>
	  	<p style="display: inline;" class="text-danger"><span class="glyphicon glyphicon-remove-sign"></span></p>
	  	<?php
	  	} else {
	  	?>
	  	<p style="display: inline;" class="text-success"><span class="glyphicon glyphicon-ok-sign"></span></p>
	  	<?php

	  	}
	  	?>
	  	</li>

	  </ul>
	  <?php
	  if(isset($error)){
	  ?>
	   <div class="alert alert-danger">You must be running at least PHP version 5.3 with the MySQLi extension enabled in order to proceed with installation.</div>
	  <?php
	  } else {
	  ?>
 		<button type="button" onclick="location.href='install.php?step=configuration'" class="btn btn-primary">Proceed &raquo;</button>
	  <?php
	  }
	  } else if(isset($_GET["step"]) && $_GET["step"] == "configuration") {
	  	if(!empty($_POST['server_name'])){
	  		$message = "<div class=\"alert alert-danger\">";
	  		if(empty($_POST['db_address']) || empty($_POST['db_username']) || empty($_POST['db_name'])){
	  			$message .= "Please input a database address, database username and database name.";
	  		} else {
	  			if(!empty($_POST['db_password'])){
	  				$password = $_POST['db_password'];
	  			} else {
	  				$password = "";
	  			}
				
				/* 
				 *  Test MySQL connection
				 */
				$mysqli = new mysqli($_POST['db_address'], $_POST['db_username'], $password, $_POST['db_name']);
				if($mysqli->connect_errno) {
					$message .= $mysqli->connect_errno . ' - ' . $mysqli->connect_error;
				} else {
					// Can connect to database, proceed
					if(is_writable("inc/conf.php")){
						// Can write to config, proceed
						$insert = '';
						if(!empty($_POST['project_name'])){
							$insert .= '$GLOBALS[\'project_name\'] = \'' . $_POST['project_name'] . '\'; // Project name' . PHP_EOL;
							// Default stats to display
							$insert .= '$GLOBALS[\'statistics\'] = array("first_joined", "last_online", "time_online", "blocks_placed", "blocks_broken", "deaths", "kd_ratio", "kills", "balance");' . PHP_EOL;
							$insert .= '$GLOBALS[\'extra_statistics\'] = array();' . PHP_EOL;
						}
						
						if(strpos(file_get_contents('inc/conf.php'), '$GLOBALS[\'servers\']') == false){
							// First server we're adding
							$insert .=  
							'$GLOBALS[\'servers\'] = array(' . PHP_EOL . 
							'	\'' . $_POST['server_name'] . '\' => array(' . PHP_EOL .
							'		"mc_ip" => "' . $_POST['server_ip'] . '", // Minecraft server IP' . PHP_EOL .
							'		"mc_port" => "' . $_POST['server_port'] . '", // Minecraft server port' . PHP_EOL . 
							'		"host" => "' . $_POST['db_address'] . '", // Database IP' . PHP_EOL .
							'		"username" => "' . $_POST['db_username'] . '", // Database username' . PHP_EOL .
							'		"password" => "' . $password . '", // Database password' . PHP_EOL .
							'		"db" => "' . $_POST['db_name'] . '" // Database name' . PHP_EOL .
							'	)' . PHP_EOL . 
							');';
							
							$append = 'a';
						} else {
							// Adding another server so we need to do this a bit differently
							$insert = file('inc/conf.php'); 
							$last = sizeof($insert) - 1; 
							unset($insert[$last]);
							unset($insert[$last - 1]);
							
							$explode =
							'	),' . PHP_EOL . '`' . 
							'	\'' . $_POST['server_name'] . '\' => array(' . PHP_EOL . '`' .
							'		"mc_ip" => "' . $_POST['server_ip'] . '", // Minecraft server IP' . PHP_EOL . '`' .
							'		"mc_port" => "' . $_POST['server_port'] . '", // Minecraft server port' . PHP_EOL . '`' . 
							'		"host" => "' . $_POST['db_address'] . '", // Database IP' . PHP_EOL . '`' .
							'		"username" => "' . $_POST['db_username'] . '", // Database username' . PHP_EOL . '`' .
							'		"password" => "' . $password . '", // Database password' . PHP_EOL . '`' .
							'		"db" => "' . $_POST['db_name'] . '" // Database name' . PHP_EOL . '`' .
							'	)' . PHP_EOL . '`' .
							');';
							
							$explode = explode('`', $explode);
							$insert = array_merge($insert, $explode);
							
							// String to insert
							$to_insert = '';
							foreach($insert as $item){
								$to_insert .= $item;
							}
							
							$insert = $to_insert;
							$to_insert = null;
							
							$append = 'w';
						}

						if(!$handle = fopen('inc/conf.php', $append)) {
							 echo "Error opening inc/conf.php. Check file permissions.";
							 die();
						}

						if(fwrite($handle, $insert) === false) {
							echo "Error writing to inc/conf.php. Check file permissions";
							die();
						}

						fclose($handle);
						
						$message .= "Success! If you would like to add another server, please input it now. If not, <button type=\"button\" onclick=\"location.href='install.php?step=user'\" class=\"btn btn-primary\">Proceed &raquo;</button>";
					} else {
						$message .= "Your <strong>inc/conf.php</strong> is not writable. Please check the file permissions.";
					}
				}

	  		}

	  		$message .= "</div>";
	  	}
		require('inc/conf.php');
		if(isset($GLOBALS['project_name'])){
			// Project name has been defined, don't display that input field again
			$project = true;
		}
		
	  ?>
	  	<h3>Configuration</h3>
	 	<?php
	 	if(isset($message)){
	 		echo $message;
	 	}
	 	?>
	  	<p>Please input the database details for the Statistics plugin. You can repeat this step if you would like to add another server.</p>
	    <form action="" method="post">
		  <?php
		  if(!isset($project)){
		  ?>
		  <div class="form-group">
			<label for="InputProject">Project Name <small>- This will be the name of the website, for example the network name</small></label>
			<input type="text" class="form-control" name="project_name" id="InputProject" placeholder="Project Name">
		  </div>
		  <hr>
		  <?php
		  }
		  ?>
		  <div class="form-group">
			<label for="InputName">Server Name <small>- This will be the name of the individual server, for example 'Survival'</small></label>
			<input type="text" class="form-control" name="server_name" id="InputName" placeholder="Server Name">
		  </div>
		  <div class="form-group">
			<label for="InputIP">Server IP <small>- This will be the IP to connect to your server, excluding the port</small></label>
			<input type="text" class="form-control" name="server_ip" id="InputIP" placeholder="Server IP">
		  </div>
		  <div class="form-group">
			<label for="InputPort">Server Port <small>- This will be the port the server is running on, NOT the Bungee port</small></label>
			<input type="text" class="form-control" name="server_port" id="InputPort" placeholder="Server Port">
		  </div>
		  <hr>
		  <div class="form-group">
			<label for="InputDBIP">Database Address</label>
			<input type="text" class="form-control" name="db_address" id="InputDBIP" placeholder="Database Address">
		  </div>
	  	  <div class="form-group">
			<label for="InputDBUser">Database Username</label>
			<input type="text" class="form-control" name="db_username" id="InputDBUser" placeholder="Database Username">
		  </div>
		  <div class="form-group">
			<label for="InputDBPass">Database Password</label>
			<input type="password" class="form-control" name="db_password" id="InputDBPass" placeholder="Database Password">
		  </div>
		  <div class="form-group">
			<label for="InputDBName">Database Name</label>
			<input type="text" class="form-control" name="db_name" id="InputDBName" placeholder="Database Name">
		  </div>
		  <input type="submit" class="btn btn-primary" value="Submit">
	    </form>
	  <?php
	  } else if(isset($_GET["step"]) && $_GET["step"] == "user") {
	  	if(!empty($_POST['username'])){
	  		$message = "<div class=\"alert alert-danger\">";
	  		if(empty($_POST['username']) || empty($_POST['password'])){
	  			$message .= "Please input a username and password.";
	  		} else {
				if(is_writable("inc/conf.php")){
					// Encrypt password
					require('inc/includes/password.php'); // Require password compat for PHP versions under 5.5
					$password = password_hash($_POST['password'], PASSWORD_BCRYPT, array("cost" => 13));
					
					// Can write to config, proceed
					$insert = 
					'$GLOBALS[\'admin\'] = array(' . PHP_EOL . 
					'	\'username\' => \'' . $_POST['username'] . '\', // Admin username' . PHP_EOL .
					'	\'password\' => \'' . $password . '\', // Admin password - encrypted. Don\'t change it here!' . PHP_EOL .
					');' . PHP_EOL;

					if(!$handle = fopen('inc/conf.php', 'r')) {
						echo "Error opening inc/conf.php. Check file permissions.";
						die();
					} else {
						$content = '';
						while(!feof($handle)){
							$line = fgets($handle);
							if(strpos($line, '// Project name') !== false){
								$content .= $line;
								$content .= $insert;
							} else {
								$content .= $line;
							}
						}
					}
					
					fclose($handle);
					
					if(!$handle = fopen('inc/conf.php', 'w')) {
						echo "Error opening inc/conf.php. Check file permissions.";
						die();
					}

					if(fwrite($handle, $content) === false) {
						echo "Error writing to inc/conf.php. Check file permissions";
						die();
					}

					fclose($handle);

					echo '<script>window.location.replace("install.php?step=finish");</script>';
					die();
				} else {
					$message .= "Your <strong>inc/conf.php</strong> is not writable. Please check the file permissions.";
				}

	  		}

	  		$message .= "</div>";
	  	}
	  ?>
	  <h3>Admin account details</h3>
	 	<?php
	 	if(isset($message)){
	 		echo $message;
	 	}
	 	?>
	  <p>The admin interface is where you can add your own statistics, add/remove servers and view performance graphs.</p>
	  <p>Please create an administrator account below.</p>
	    <form action="" method="post">
		  <div class="form-group">
			<label for="InputUser">Administrator Username</label>
			<input type="text" class="form-control" name="username" id="InputUser" placeholder="Username">
		  </div>
		  <div class="form-group">
			<label for="InputPassword">Administrator Password</label>
			<input type="password" class="form-control" name="password" id="InputPassword" placeholder="Password">
		  </div>
		  <input type="submit" class="btn btn-primary" value="Submit">
	    </form>
	  <?php
	  } else if(isset($_GET["step"]) && $_GET["step"] == "finish") {
	  ?>
	  <h3>Finish</h3>
	  <p>Installation complete. Please <strong>delete</strong> the install.php file before you use the interface.</p>
	  <p>Please ensure that the contents of <strong>inc/conf.php</strong> are not viewable in the browser, and that they are secure.</p>
	  <button type="button" onclick="location.href='index.php'" class="btn btn-primary">Finish</button>
	  <?php 
	  }
	  ?>
    </div>
  </body>
</html>