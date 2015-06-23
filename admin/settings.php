<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */

session_start();
 
$path = "../";
$page = "admin-settings";

// Require config
require($path . 'inc/conf.php');

// Get some variables from the config file
$title = htmlspecialchars($GLOBALS['project_name']);

// Start initialising the page - display header
require($path . 'inc/templates/header.php');

/*
 *  User needs to be logged in
 */
 
if(!isset($_GET['sid'])){
	// No session ID, need to log in
	require('login.php');
	die();
} else {
	// Validate session ID
	if($_GET['sid'] == $_SESSION['sid']){
		// Okay, can continue
		$sid = htmlspecialchars($_GET['sid']);
	} else {
		// Not okay, need to login again
		echo '<script>window.location.replace(\'./\');</script>';
		die();
	}
}
?>
  <body>
	<?php require($path . 'inc/templates/navbar.php'); ?>
	
	<div class="container">
	  <div class="row">
	    <div class="col-md-3">
		  <div class="well well-sm">
			<ul class="nav nav-pills nav-stacked">
			  <li<?php if($page === "admin-index"){ ?> class="active"<?php } ?>><a href="<?php echo $path; ?>admin/?sid=<?php echo $sid; ?>">Overview</a></li>
			  <li<?php if($page === "admin-settings"){ ?> class="active"<?php } ?>><a href="<?php echo $path; ?>admin/settings.php?sid=<?php echo $sid; ?>">Settings</a></li>
			</ul>
		  </div>
		</div>
		<div class="col-md-9">
		  <div class="well well-sm">
		    <?php 
			if(!isset($_GET['action'])){ 
				if(!empty($_POST['sitename'])){ // deal with input
					// Check token
					if($_SESSION['stats_token'] !== $_POST['token']){
						echo '<script>window.location.replace(\'' . $path . 'admin/settings.php?sid=' . $sid . '&amp;error=token\');</script>';
						die();
					}
					// Valid token, edit configuration
					// Generate new config file
					$servers_string = '';
					foreach($GLOBALS['servers'] as $key => $item){
						$servers_string .= 	'	\'' . $key . '\' => array(' . PHP_EOL . 
											'		"mc_ip" => "' . $item['mc_ip'] . '", // Minecraft server IP' . PHP_EOL .
											'		"mc_port" => "' . $item['mc_port'] . '", // Minecraft server port' . PHP_EOL .
											'		"host" => "' . $item['host'] . '", // Database IP' . PHP_EOL .
											'		"username" => "' . $item['username'] . '", // Database username' . PHP_EOL . 
											'		"password" => "' . $item['password'] . '", // Database password' . PHP_EOL .
											'		"db" => "' . $item['db'] . '" // Database name' . PHP_EOL .
											'	),';
					}

					$insert = 	'<?php' . PHP_EOL .
								'/*' . PHP_EOL .
								' *  Web interface made by Samerton' . PHP_EOL .
								' *  Statistics plugin made by PickNChew' . PHP_EOL .
								' */' . PHP_EOL .
								'' . PHP_EOL . 
								'// Configuration file' . PHP_EOL .
								'$GLOBALS[\'project_name\'] = \'' . htmlspecialchars($_POST['sitename']) . '\'; // Project name' . PHP_EOL . 
								'$GLOBALS[\'admin\'] = array(' . PHP_EOL .
								'	\'username\' => \'' . $GLOBALS['admin']['username'] . '\', // Admin username' . PHP_EOL . 
								'	\'password\' => \'' . $GLOBALS['admin']['password'] . '\' // Admin password - encrypted. Don\'t change it here!' . PHP_EOL . 
								');' . PHP_EOL .
								'$GLOBALS[\'servers\'] = array(' . PHP_EOL .
								$servers_string . PHP_EOL . 
								');';
								
					// Write to config file			
					if(is_writable($path . 'inc/conf.php')){
						$file = fopen($path . 'inc/conf.php','w');
						fwrite($file, $insert);
						fclose($file);

						echo '<script>window.location.replace("' . $path . 'admin/settings.php?sid=' . $sid . '");</script>';
						die();
						
					} else {
						// unable to write to file
						echo 'Unable to write to <strong>inc/conf.php</strong>. Please ensure permissions are correctly set.';
					}
				}
				// Generate token for form
				$token = md5(uniqid());
				$_SESSION['stats_token'] = $token;
			?>
		    <h3>Settings</h3>
			<?php if(isset($_GET['error']) && $_GET['error'] == 'token'){ ?>
			<div class="alert alert-danger">Invalid token. Please try again.</div>
			<?php } ?>
			<form action="<?php echo $path; ?>admin/settings.php?sid=<?php echo $sid; ?>" method="post">
			  <div class="form-group">
			    <label for="sitename">Site Name</label>
				<input type="text" class="form-control" id="sitename" name="sitename" value="<?php echo $title; ?>">
			  </div>
			  <input type="hidden" name="token" value="<?php echo $token; ?>">
			  <input type="submit" class="btn btn-primary" value="Update">
			</form>
			<hr>
			<a href="<?php echo $path; ?>admin/settings.php?sid=<?php echo $sid; ?>&amp;action=add" class="btn btn-success">Add a server</a>
			<a href="<?php echo $path; ?>admin/settings.php?sid=<?php echo $sid; ?>&amp;action=remove" class="btn btn-danger">Remove a server</a>
			<?php 
			} else { 
				if($_GET['action'] == 'add'){
					if(!empty($_POST['server_name'])){
						// Check token
						if($_SESSION['stats_token'] !== $_POST['token']){
							echo '<script>window.location.replace(\'' . $path . 'admin/settings.php?sid=' . $sid . '&amp;action=add&amp;error=token\');</script>';
							die();
						}
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
								if(is_writable($path . "inc/conf.php")){
									// Can write to config, proceed
									$insert = '';
									if(!empty($_POST['project_name'])){
										$insert .= '$GLOBALS[\'project_name\'] = \'' . $_POST['project_name'] . '\'; // Project name' . PHP_EOL;
									}
									
									// Adding another server so we need to do this a bit differently
									$insert = file($path . 'inc/conf.php'); 
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

									if(!$handle = fopen($path . 'inc/conf.php', $append)) {
										 echo "Error opening inc/conf.php. Check file permissions.";
										 die();
									}

									if(fwrite($handle, $insert) === false) {
										echo "Error writing to inc/conf.php. Check file permissions";
										die();
									}

									fclose($handle);
									
									echo '<script>window.location.replace(\'' . $path . 'admin/settings.php?sid=' . $sid . '\');</script>';
									die();
								} else {
									echo "Your <strong>inc/conf.php</strong> is not writable. Please check the file permissions.";
									die();
								}
							}

						}

						$message .= "</div>";
					} else {
						// Generate token for form
						$token = md5(uniqid());
						$_SESSION['stats_token'] = $token;
					?>
					<h3>Add a server</h3>
					<form action="" method="post">
					  <?php if(isset($_GET['error']) && $_GET['error'] == 'token'){ ?>
					  <div class="alert alert-danger">Invalid token. Please try again.</div>
					  <?php } ?>
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
					  <input type="hidden" name="token" value="<?php echo $token; ?>">
					  <input type="submit" class="btn btn-primary" value="Submit">
					</form>
					<?php
					}
				} else if($_GET['action'] == 'remove'){
					if(!isset($_GET['server'])){
						// need to select a server
					?>
					    <h3>Remove a server</h3>
						<strong>Select a server to remove:</strong><br /><br />
						<?php foreach($GLOBALS['servers'] as $key => $item){ ?>
							<a onclick="return confirm('Are you sure you want to delete the server <?php echo htmlspecialchars($key); ?>?');" href="<?php echo $path; ?>admin/settings.php?sid=<?php echo $sid; ?>&amp;action=remove&amp;server=<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($key); ?></a><br />
						<?php } ?>
					<?php
					} else {
					    // Go ahead and remove it
						// Check the server exists
						$server_name = htmlspecialchars($_GET['server']);
						if(!isset($GLOBALS['servers'][$server_name])){
							// The server doesn't exist!
							echo '<script>window.location.replace("./");</script>';
							die();
						}

						// Generate new config file
						$servers_string = '';

						foreach($GLOBALS['servers'] as $key => $item){
							if($key != $server_name){
								$servers_string .= 	'	\'' . $key . '\' => array(' . PHP_EOL . 
													'		"mc_ip" => "' . $item['mc_ip'] . '", // Minecraft server IP' . PHP_EOL .
													'		"mc_port" => "' . $item['mc_port'] . '", // Minecraft server port' . PHP_EOL .
													'		"host" => "' . $item['host'] . '", // Database IP' . PHP_EOL .
													'		"username" => "' . $item['username'] . '", // Database username' . PHP_EOL . 
													'		"password" => "' . $item['password'] . '", // Database password' . PHP_EOL .
													'		"db" => "' . $item['db'] . '" // Database name' . PHP_EOL .
													'	),';
							}
						}

						$insert = 	'<?php' . PHP_EOL .
									'/*' . PHP_EOL .
									' *  Web interface made by Samerton' . PHP_EOL .
									' *  Statistics plugin made by PickNChew' . PHP_EOL .
									' */' . PHP_EOL .
									'' . PHP_EOL . 
									'// Configuration file' . PHP_EOL .
									'$GLOBALS[\'project_name\'] = \'' . $title . '\'; // Project name' . PHP_EOL . 
									'$GLOBALS[\'admin\'] = array(' . PHP_EOL .
									'	\'username\' => \'' . $GLOBALS['admin']['username'] . '\', // Admin username' . PHP_EOL . 
									'	\'password\' => \'' . $GLOBALS['admin']['password'] . '\' // Admin password - encrypted. Don\'t change it here!' . PHP_EOL . 
									');' . PHP_EOL .
									'$GLOBALS[\'servers\'] = array(' . PHP_EOL .
									$servers_string . PHP_EOL . 
									');';
									
						// Write to config file			
						if(is_writable($path . 'inc/conf.php')){
							$file = fopen($path . 'inc/conf.php','w');
							fwrite($file, $insert);
							fclose($file);

							echo '<script>window.location.replace("' . $path . 'admin/settings.php?sid=' . $sid . '");</script>';
							die();
							
						} else {
							// unable to write to file
							echo 'Unable to write to <strong>inc/conf.php</strong>. Please ensure permissions are correctly set.';
						}
					}
				}
			}
			?>
		  </div>
		</div>
	  </div>
	  
	  <hr>
	  <?php require($path . 'inc/templates/footer.php'); ?>
	</div>
	<?php require($path . 'inc/templates/scripts.php'); ?>
	<script>
	$(document).ready(function(){
		$("[rel=tooltip]").tooltip({ placement: 'top'});
	});
	</script>
  </body>
</html>
