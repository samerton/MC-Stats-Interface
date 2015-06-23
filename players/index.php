<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */

$path = "../";
$page = "players";

// Require config
require($path . 'inc/conf.php');

// Get some variables from the config file
$title = htmlspecialchars($GLOBALS['project_name']);

// Start initialising the page - display header
require($path . 'inc/templates/header.php');

?>
  <body>
	<?php require($path . 'inc/templates/navbar.php'); ?>
	
	<div class="container">
	  <?php if(!isset($_GET['p'])){ ?>
	  <h2>Search for a player</h2>
	  <?php
	  if(isset($_GET['error']) && $_GET['error'] == 'not_exist'){
	  ?>
	  <div class="alert alert-danger">That player does not exist in our database.</div>
	  <?php
	  }
	  if(!empty($_POST['username'])){
		echo '<script>window.location.replace(\'' . $path . 'players/?p=' . htmlspecialchars($_POST['username']) . '\');</script>';
		die();
	  }
	  // Generate token for form
	  $token = md5(uniqid());
	  $_SESSION['stats_token'] = $token;
	  ?>
	  <form action="" method="post">
		<input type="text" name="username" id="username" autocomplete="off" class="form-control input-lg" placeholder="Username" tabindex="1">
		<input type="hidden" name="token" value="<?php echo $token; ?>">
		<br />
		<input type="submit" value="Search" class="btn btn-primary btn-lg" tabindex="2">
	  </form>
	  <?php 
	  } else { 
		$statistics_array = array(); // array to store data
		$player = htmlspecialchars($_GET['p']); // get player name from URL parameter
		$statistics_variables = array("first_joined", "last_online", "time_online", "blocks_placed", "blocks_broken", "deaths", "kills", "balance"); // array of variables to bind to
		
		foreach($GLOBALS['servers'] as $key => $server){
			/*
			 *  Connect to the database
			 */
			$mysqli = new mysqli($server['host'], $server['username'], $server['password'], $server['db']);
			if($mysqli->connect_errno) {
				echo '<div class="alert alert-warning">' .  $mysqli->connect_errno . ' - ' . $mysqli->connect_error . '</div>';
				die();
			}
			
			// Execute search query
			$stmt = $mysqli->prepare("SELECT first_joined, last_online, time_online, blocks_placed, blocks_broken, deaths, kills, balance FROM statistics_players WHERE player_name = ?");
			
			$stmt->bind_param("s", $player);
			
			$stmt->execute();
			
			$stmt->store_result();
			
			if($stmt->num_rows != 0){
				$stmt->bind_result($first_joined, $last_online, $time_online, $blocks_placed, $blocks_broken, $deaths, $kills, $balance);
				
				while($stmt->fetch()){
					$statistics_array[$key] = compact($first_joined, $last_online, $time_online, $blocks_placed, $blocks_broken, $deaths, $kills, $balance, $statistics_variables);
				}
				
				// user exists
				$exists = true;
			} else {
				// no results
			}
			$stmt->close();
			$mysqli->close();
		}
		
		if($exists == true){
			// exists
		?>
		<h2>Viewing player <?php echo $player; ?></h2>
		<?php
			if(count($GLOBALS['servers']) == 1){
				// display stats for just the one server
				foreach($GLOBALS['servers'] as $server => $item){
					// this just gets the server name
				}
				$item = null;
				
				// Convert time online to days, minutes and seconds
				$time_online = $statistics_array[$server]['time_online'];
				$dtF = new DateTime("@0");
				$dtT = new DateTime("@$time_online");
				$time_online = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes, %s seconds');
		?>
		<div class="row">
		  <div class="col-md-4">
		    <div class="panel panel-primary">
			  <div class="panel-heading">
			    <center><?php echo $player; ?></center>
			  </div>
			  <div class="panel-body">
			    <center><img src="https://cravatar.eu/avatar/<?php echo $player; ?>/150.png" class="img-rounded"></center>
				<hr>
				<center>
				  First joined: <strong><?php echo date('d M Y', $statistics_array[$server]['first_joined']); ?></strong><br />
				  Last online: <strong><?php echo date('d M Y', $statistics_array[$server]['last_online']); ?></strong><br />
			    </center>
			  </div>
			</div>
		  </div>
		  <div class="col-md-8">
		    <h3>Statistics</h3>
			Total playtime: <strong><?php echo $time_online; ?></strong><br />
			Blocks placed: <strong><?php echo $statistics_array[$server]['blocks_placed']; ?></strong><br />
			Blocks broken: <strong><?php echo $statistics_array[$server]['blocks_broken']; ?></strong><br />
			Deaths: <strong><?php echo $statistics_array[$server]['deaths']; ?></strong><br />
			Kills: <strong><?php echo $statistics_array[$server]['kills']; ?></strong><br />
			K/D ratio: <strong><?php if($statistics_array[$server]['deaths'] == 0){ echo $statistics_array[$server]['kills']; } else { echo round(($statistics_array[$server]['kills'] / $statistics_array[$server]['deaths']), 2); } ?></strong><br />
			Balance: <strong><?php echo $statistics_array[$server]['balance']; ?></strong>
		  </div>
		</div>
		<?php
			} else {
				// Display stats for all servers
				// First, get the time they were first online
				$first_joined = 0;
				foreach($statistics_array as $server){
					if($first_joined == 0 || $server['first_joined'] < $first_joined){
						$first_joined = $server['first_joined'];
					}
				}
				// Next, get the time they were last online
				foreach($statistics_array as $server){
					if(!isset($last_online) || $server['last_online'] > $last_online){
						$last_online = $server['last_online'];
					}
				}
		?>
		<div class="row">
		  <div class="col-md-4">
		    <div class="panel panel-primary">
			  <div class="panel-heading">
			    <center><?php echo $player; ?></center>
			  </div>
			  <div class="panel-body">
			    <center><img src="https://cravatar.eu/avatar/<?php echo $player; ?>/150.png" class="img-rounded"></center>
				<hr>
				<center>
				  First joined: <strong><?php echo date('d M Y', $first_joined); ?></strong><br />
				  Last online: <strong><?php echo date('d M Y', $last_online); ?></strong><br />
			    </center>
			  </div>
			</div>
		  </div>
		  <div class="col-md-8">
		    <?php
			foreach($GLOBALS['servers'] as $server => $item){
				echo '<h3>' . htmlspecialchars($server) . '</h3>';
				// Convert time online to days, minutes and seconds
				$time_online = $statistics_array[$server]['time_online'];
				$dtF = new DateTime("@0");
				$dtT = new DateTime("@$time_online");
				$time_online = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes, %s seconds');
			?>
			Total playtime: <strong><?php echo $time_online; ?></strong><br />
			Blocks placed: <strong><?php echo $statistics_array[$server]['blocks_placed']; ?></strong><br />
			Blocks broken: <strong><?php echo $statistics_array[$server]['blocks_broken']; ?></strong><br />
			Deaths: <strong><?php echo $statistics_array[$server]['deaths']; ?></strong><br />
			Kills: <strong><?php echo $statistics_array[$server]['kills']; ?></strong><br />
			K/D ratio: <strong><?php if($statistics_array[$server]['deaths'] == 0){ echo $statistics_array[$server]['kills']; } else { echo round(($statistics_array[$server]['kills'] / $statistics_array[$server]['deaths']), 2); } ?></strong><br />
			Balance: <strong><?php echo $statistics_array[$server]['balance']; ?></strong>
			<?php
			}
			$item = null;
			?>
		  </div>
		</div>
		<?php
			}
		} else {
			// doesn't exist
			echo '<script>window.location.replace(\'' . $path . 'players/?error=not_exist\');</script>';
			die();
		}
	  }
	  ?>
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
