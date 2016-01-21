<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */

$path = "../";
$page = "players";

// Require config
require($path . 'inc/conf.php');

// Initialise
require($path . 'inc/init.php');

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
		if($_POST['token'] == $user_token){
			echo '<script>window.location.replace(\'' . $path . 'players/?p=' . htmlspecialchars($_POST['username']) . '\');</script>';
			die();
		} else {
			echo '<div class="alert alert-danger">Your form token has expired. Please try again.</div>';
		}
	  }

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
		$statistics_variables = array("uuid", "player_name", "first_joined", "last_online", "time_online", "blocks_placed", "blocks_broken", "deaths", "kills", "balance", "results"); // array of variables to bind to
		
		foreach($GLOBALS['servers'] as $key => $server){
			/*
			 *  Connect to the database
			 */
			$mysqli = new mysqli($server['host'], $server['username'], $server['password'], $server['db']);
			if($mysqli->connect_errno) {
				echo '<div class="alert alert-warning">' .  $mysqli->connect_errno . ' - ' . $mysqli->connect_error . '</div>';
				die();
			}
			
			// Execute search query - first, normal statistics
			$stmt = $mysqli->prepare("SELECT uuid, player_name, first_joined, last_online, time_online, blocks_placed, blocks_broken, deaths, kills, balance FROM statistics_players WHERE player_name = ?");
			
			$stmt->bind_param("s", $player);
			
			$stmt->execute();
			
			$stmt->store_result();
			
			if($stmt->num_rows != 0){
				$stmt->bind_result($uuid, $player_name, $first_joined, $last_online, $time_online, $blocks_placed, $blocks_broken, $deaths, $kills, $balance);
				
				while($stmt->fetch()){
					// Get extra statistics
					$stmt_extra = $mysqli->prepare("SELECT * FROM statistics_extra WHERE uuid = ?");
					
					$stmt_extra->bind_param("s", $uuid);
					
					$stmt_extra->execute();
					
					$results = $stmt_extra->get_result();
					
					$results = $results->fetch_array();
					
					$stmt_extra->close();
					
					$statistics_array[$key] = compact($uuid, $player_name, $first_joined, $last_online, $time_online, $blocks_placed, $blocks_broken, $deaths, $kills, $balance, $results, $statistics_variables);
				}
				
				// user exists
				$exists = true;
			} else {
				// no results
			}
			$stmt->close();
			$mysqli->close();
		}

		$player = htmlspecialchars($statistics_array[$key]['player_name']);
		
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
				  <?php if(in_array('first_joined', $GLOBALS['statistics'])){ ?>First joined: <strong><?php echo date('d M Y', $statistics_array[$server]['first_joined']); ?></strong><br /><?php } ?>
				  <?php if(in_array('last_online', $GLOBALS['statistics'])){ ?>Last online: <strong><?php echo date('d M Y', $statistics_array[$server]['last_online']); ?></strong><br /><?php } ?>
			    </center>
			  </div>
			</div>
		  </div>
		  <div class="col-md-8">
		    <h3>Statistics</h3>
			<?php if(in_array('time_online', $GLOBALS['statistics'])){ ?>Total playtime: <strong><?php echo $time_online; ?></strong><br /><?php } ?>
			<?php if(in_array('blocks_placed', $GLOBALS['statistics'])){ ?>Blocks placed: <strong><?php echo $statistics_array[$server]['blocks_placed']; ?></strong><br /><?php } ?>
			<?php if(in_array('blocks_broken', $GLOBALS['statistics'])){ ?>Blocks broken: <strong><?php echo $statistics_array[$server]['blocks_broken']; ?></strong><br /><?php } ?>
			<?php if(in_array('deaths', $GLOBALS['statistics'])){ ?>Deaths: <strong><?php echo $statistics_array[$server]['deaths']; ?></strong><br /><?php } ?>
			<?php if(in_array('kills', $GLOBALS['statistics'])){ ?>Kills: <strong><?php echo $statistics_array[$server]['kills']; ?></strong><br /><?php } ?>
			<?php if(in_array('kd_ratio', $GLOBALS['statistics'])){ ?>K/D ratio: <strong><?php if($statistics_array[$server]['deaths'] == 0){ echo $statistics_array[$server]['kills']; } else { echo round(($statistics_array[$server]['kills'] / $statistics_array[$server]['deaths']), 2); } ?></strong><br /><?php } ?>
			<?php if(in_array('balance', $GLOBALS['statistics'])){ ?>Balance: <strong><?php echo $statistics_array[$server]['balance']; ?></strong><br /><?php } ?>
			<?php
			// Extra stats
			if(count($statistics_array[$server]['results'])){
				foreach($statistics_array[$server]['results'] as $key => $extra){
					if(!is_numeric($key) && $key !== 'uuid'){
						echo ucfirst(htmlspecialchars($key)) . ': <strong>' . htmlspecialchars($extra) . '</strong><br />';
					}
				}
			}
			?>
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
				  <?php if(in_array('first_joined', $GLOBALS['statistics'])){ ?>First joined: <strong><?php echo date('d M Y', $first_joined); ?></strong><br /><?php } ?>
				  <?php if(in_array('last_online', $GLOBALS['statistics'])){ ?>Last online: <strong><?php echo date('d M Y', $last_online); ?></strong><br /><?php } ?>
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
			<?php if(in_array('time_online', $GLOBALS['statistics'])){ ?>Total playtime: <strong><?php echo $time_online; ?></strong><br /><?php } ?>
			<?php if(in_array('blocks_placed', $GLOBALS['statistics'])){ ?>Blocks placed: <strong><?php echo $statistics_array[$server]['blocks_placed']; ?></strong><br /><?php } ?>
			<?php if(in_array('blocks_broken', $GLOBALS['statistics'])){ ?>Blocks broken: <strong><?php echo $statistics_array[$server]['blocks_broken']; ?></strong><br /><?php } ?>
			<?php if(in_array('deaths', $GLOBALS['statistics'])){ ?>Deaths: <strong><?php echo $statistics_array[$server]['deaths']; ?></strong><br /><?php } ?>
			<?php if(in_array('kills', $GLOBALS['statistics'])){ ?>Kills: <strong><?php echo $statistics_array[$server]['kills']; ?></strong><br /><?php } ?>
			<?php if(in_array('kd_ratio', $GLOBALS['statistics'])){ ?>K/D ratio: <strong><?php if($statistics_array[$server]['deaths'] == 0){ echo $statistics_array[$server]['kills']; } else { echo round(($statistics_array[$server]['kills'] / $statistics_array[$server]['deaths']), 2); } ?></strong><br /><?php } ?>
			<?php if(in_array('balance', $GLOBALS['statistics'])){ ?>Balance: <strong><?php echo $statistics_array[$server]['balance']; ?></strong><br /><?php } ?>
			<?php
			// Extra stats
			if(count($statistics_array[$server]['results'])){
				foreach($statistics_array[$server]['results'] as $key => $extra){
					if(!is_numeric($key) && $key !== 'uuid'){
						if(in_array($key, $GLOBALS['extra_statistics'])){
							echo ucfirst(htmlspecialchars($key)) . ': <strong>' . htmlspecialchars($extra) . '</strong><br />';
						}
					}
				}
			}

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
