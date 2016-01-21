<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */

if(file_exists('install.php')){
	echo 'Please run the installer (install.php) and ensure it is deleted afterwards.';
	die();
}
 
 
$path = "./";
$page = "home";

// Require config
require('inc/conf.php');

// Initialise
require('inc/init.php');

// Get some variables from the config file
$title = htmlspecialchars($GLOBALS['project_name']);

// Start initialising the page - display header
require('inc/templates/header.php');


/*
 *  Is it a single server setup?
 */
if(count($GLOBALS['servers']) == 1){
	// Single server
	foreach($GLOBALS['servers'] as $key => $item){
		$server_name = htmlspecialchars($key);
	}
	
	/*
	 *  Connect to the database
	 */
	$mysqli = new mysqli($GLOBALS['servers'][$server_name]['host'], $GLOBALS['servers'][$server_name]['username'], $GLOBALS['servers'][$server_name]['password'], $GLOBALS['servers'][$server_name]['db']);
	if($mysqli->connect_errno) {
		echo '<div class="alert alert-warning">' .  $mysqli->connect_errno . ' - ' . $mysqli->connect_error . '</div>';
		die();
	}
	
	// Get total players tracked
	$query = $mysqli->query("SELECT time_online, kills FROM statistics_players");
	$total_players = $query->num_rows;
	
	// Get total time online and kills
	$time_online = 0;
	$kills = 0;
	
	while($row = $query->fetch_assoc()){
		$time_online = $time_online + $row['time_online'];
		$kills = $kills + $row['kills'];
	}

	// Close connection
	$query->close();
	$mysqli->close();
	
	// Convert time online to days, minutes and seconds
	$dtF = new DateTime("@0");
    $dtT = new DateTime("@$time_online");
    $time_online = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes, %s seconds');
	

	/*
	 *  Ping the Minecraft server
	 */
	require('inc/includes/MinecraftServerPing.php');
	require('inc/includes/MinecraftPingException.php');
	
	foreach($GLOBALS['servers'] as $server){
		try {
			$Query = new MinecraftPing($server['mc_ip'], $server['mc_port'], 1);
			
			$Info = $Query->Query();
			
			if($Info === false){
				$Query->Close();
				$Query->Connect();
				
				$Info = $Query->QueryOldPre17();
			}
			
		} catch(MinecraftPingException $e){
			$exception = $e;
		}
		if(isset($Query) && $Query !== null){
			$Query->Close();
		}
	}
	
} else {
	// Multiple servers
	
	if(!isset($_GET['server'])){
		// Add up all statistics from all servers
		
		$total_players = 0;
		$time_online = 0;
		$kills = 0;
		
		foreach($GLOBALS['servers'] as $key => $item){
			$server_name = htmlspecialchars($key);
			/*
			 *  Connect to the database
			 */
			$mysqli = new mysqli($GLOBALS['servers'][$server_name]['host'], $GLOBALS['servers'][$server_name]['username'], $GLOBALS['servers'][$server_name]['password'], $GLOBALS['servers'][$server_name]['db']);
			if($mysqli->connect_errno) {
				echo '<div class="alert alert-warning">' .  $mysqli->connect_errno . ' - ' . $mysqli->connect_error . '</div>';
				die();
			}
			
			// Get total players tracked
			$query = $mysqli->query("SELECT time_online, kills FROM statistics_players");
			$total_players = $total_players + $query->num_rows;
			
			// Get total time online and kills
			while($row = $query->fetch_assoc()){
				$time_online = $time_online + $row['time_online'];
				$kills = $kills + $row['kills'];
			}

			// Close connection
			$query->close();
			$mysqli->close();
		
		}
		
		// Convert time online to days, minutes and seconds
		$dtF = new DateTime("@0");
		$dtT = new DateTime("@$time_online");
		$time_online = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes, %s seconds');
		
		/*
		 *  Ping the Minecraft servers to get a total player count
		 */
		require('inc/includes/MinecraftServerPing.php');
		require('inc/includes/MinecraftPingException.php');

		$online_players = 0;
		
		foreach($GLOBALS['servers'] as $server){
			try {
				$Query = new MinecraftPing($server['mc_ip'], $server['mc_port'], 1);
				
				$Info = $Query->Query();
				
				if($Info === false){
					$Query->Close();
					$Query->Connect();
					
					$Info = $Query->QueryOldPre17();
				}
				
				$online_players = $online_players + $Info['players']['online'];
				
			} catch(MinecraftPingException $e){
				$exception = $e;
			}
			if(isset($Query) && $Query !== null){
				$Query->Close();
			}
		}
	
	} else {
		// Server has been selected
		$server_name = htmlspecialchars($_GET['server']);
		if(!isset($GLOBALS['servers'][$server_name])){
			// The server doesn't exist!
			echo '<script>window.location.replace("./?error=exists");</script>';
			die();
		}
		
		/*
		 *  Connect to the database
		 */
		$mysqli = new mysqli($GLOBALS['servers'][$server_name]['host'], $GLOBALS['servers'][$server_name]['username'], $GLOBALS['servers'][$server_name]['password'], $GLOBALS['servers'][$server_name]['db']);
		if($mysqli->connect_errno) {
			echo '<div class="alert alert-warning">' .  $mysqli->connect_errno . ' - ' . $mysqli->connect_error . '</div>';
			die();
		}
		
		// Get total players tracked
		$query = $mysqli->query("SELECT time_online, kills FROM statistics_players");
		$total_players = $query->num_rows;
		
		$time_online = 0;
		$kills = 0;
		
		// Get total time online and kills
		while($row = $query->fetch_assoc()){
			$time_online = $time_online + $row['time_online'];
			$kills = $kills + $row['kills'];
		}

		// Close connection
		$query->close();
		$mysqli->close();
		
		// Convert time online to days, minutes and seconds
		$dtF = new DateTime("@0");
		$dtT = new DateTime("@$time_online");
		$time_online = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes, %s seconds');
		
		/*
		 *  Ping the Minecraft server
		 */
		require('inc/includes/MinecraftServerPing.php');
		require('inc/includes/MinecraftPingException.php');
		
		try {
			$Query = new MinecraftPing($GLOBALS['servers'][$server_name]['mc_ip'], $GLOBALS['servers'][$server_name]['mc_port'], 1);
			
			$Info = $Query->Query();
			
			if($Info === false){
				$Query->Close();
				$Query->Connect();
				
				$Info = $Query->QueryOldPre17();
			}
			
		} catch(MinecraftPingException $e){
			$exception = $e;
		}
		if(isset($Query) && $Query !== null){
			$Query->Close();
		}
	}
}
?>
  <body>
	<?php require('inc/templates/navbar.php'); ?>
	
	<div class="container">
	<?php 
	if(isset($_GET['error'])){
		if($_GET['error'] == 'exists'){
	?>
		<div class="alert alert-danger">
		That server doesn't exist!
		</div>
	<?php
		}
	}

	// Single server
	if(count($GLOBALS['servers']) == 1){
	?>
	
	  <div class="jumbotron">
	    <h1><?php echo $title; ?></h1>
		<p>Welcome to the <?php echo $title; ?> statistics interface</p>
		<p><a href="./players" class="btn btn-primary btn-lg">View Players</a></p>
	  </div>
	  
	  <hr>
	  
	  <div class="row">
	    <div class="col-md-3">
		  <center>
		  <h2>Players Online</h2>
		  <h4><?php if(isset($Info)) { echo $Info["players"]["online"]; ?>/<?php echo $Info["players"]["max"]; } else { ?>Unknown<?php } ?></h4>
		  </center>
		</div>
		<div class="col-md-3">
	      <center>
		  <h2>Players Tracked</h2>
		  <h4><?php echo $total_players; ?></h4>
		  </center>
		</div>
		<div class="col-md-3">
		  <center>
		  <h2>Total Playtime</h2>
		  <h4><?php echo $time_online; ?></h4>
		  </center>
		</div>
		<div class="col-md-3">
		  <center>
		  <h2>Total Kills</h2>
		  <h4><?php echo $kills; ?></h4>
		  </center>
		</div>
	  </div>
	  
	  <hr>
	  
	  <h3>Players online</h3>
	  <?php 
	  if(isset($Info)){
		  if($Info["players"]["online"] == 0){ 
		  ?>
	  <div class="alert alert-warning">No players online</div>
		  <?php 
		  } else {  
			foreach($Info['players']['sample'] as $player){
		  ?>
	    <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo $player['name']; ?>"><a href="players/?p=<?php echo $player['name']; ?>"><img src="https://cravatar.eu/avatar/<?php echo $player['name']; ?>/50.png" style="width: 40px; height: 40px; margin-bottom: 5px; margin-left: 5px; border-radius: 3px;" /></a></span>
		  <?php 
			}
		  } 
	  } else {
	  ?>
	  <div class="alert alert-danger">Unable to query server</div>
	  <?php
	  }
	  ?>
	  
	  <hr>
	  
	  <?php require('inc/templates/footer.php'); ?>
	  
	<?php 
	} else { // Multiple servers 
		if(!isset($_GET['server'])){
	?>
	
	  <div class="jumbotron">
	    <h1><?php echo $title; ?></h1>
		<p>Welcome to the <?php echo $title; ?> statistics interface</p>
		<p>Select a server to view statistics:</p>
		<strong>
		<?php foreach($GLOBALS['servers'] as $key => $item){ ?>
		<a href="./?server=<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($key); ?></a><br />
		<?php } ?>
		</strong>
	  </div>
		<?php 
		} else {
		?>
	  <div class="jumbotron">
		<h1><?php echo $server_name; ?></h1>
		<p><a href="./players/?server=<?php echo $server_name; ?>" class="btn btn-primary btn-lg">View Players</a></p>
	  </div>
		<?php
		}
		?>
	  
	  <hr>
	  
	  <div class="row">
		<div class="col-md-3">
		  <center>
		  <h2>Players Online</h2>
		  <?php if(!isset($_GET['server'])){ ?>
		  <h4><?php echo $online_players; ?></h4>
		  <?php } else { ?>
		  <h4><?php if(isset($Info)) { echo $Info["players"]["online"]; ?>/<?php echo $Info["players"]["max"]; } else { ?>Unknown<?php } ?></h4>
		  <?php } ?>
		  </center>
		</div>
		<div class="col-md-3">
	      <center>
		  <h2>Players Tracked</h2>
		  <h4><?php echo $total_players; ?></h4>
		  </center>
		</div>
		<div class="col-md-3">
		  <center>
		  <h2>Total Playtime</h2>
		  <h4><?php echo $time_online; ?></h4>
		  </center>
		</div>
		<div class="col-md-3">
		  <center>
		  <h2>Total Kills</h2>
		  <h4><?php echo $kills; ?></h4>
		  </center>
		</div>
	  </div>
	  
	  <?php if(isset($_GET['server'])){ ?>
	  <hr>
	  
	  <h3>Players online</h3>
	  <?php 
	  if(isset($Info)){
		  if($Info["players"]["online"] == 0){ 
		  ?>
	  <div class="alert alert-warning">No players online</div>
		  <?php 
		  } else {  
			foreach($Info['players']['sample'] as $player){
		  ?>
	    <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo $player['name']; ?>"><a href="players/?p=<?php echo $player['name']; ?>"><img src="https://cravatar.eu/avatar/<?php echo $player['name']; ?>/50.png" style="width: 40px; height: 40px; margin-bottom: 5px; margin-left: 5px; border-radius: 3px;" /></a></span>
		  <?php 
			}
		  } 
	  } else {
	  ?>
	  <div class="alert alert-danger">Unable to query server</div>
	  <?php
	  }
	  ?>
	  <?php } ?>
	  
	  <hr>
	  
	  <?php require('inc/templates/footer.php'); ?>
	<?php } ?>
	</div>

	<?php require('inc/templates/scripts.php'); ?>
	<script>
	$(document).ready(function(){
		$("[rel=tooltip]").tooltip({ placement: 'top'});
	});
	</script>
  </body>
</html>
