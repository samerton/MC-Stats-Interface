<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */

$path = "../";
$page = "leaderboards";

// Require config
require($path . 'inc/conf.php');

// Get some variables from the config file
$title = htmlspecialchars($GLOBALS['project_name']);

// Start initialising the page - display header
require($path . 'inc/templates/header.php');


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
	
	// Get all players tracked
	$query = $mysqli->query("SELECT player_name, time_online, kills, deaths, blocks_placed, blocks_broken, balance FROM statistics_players ORDER BY time_online DESC");
	
	$output = array();
	
	// Convert results from query into array
	while($row = $query->fetch_assoc()){
		$output[] = $row;
	}

	// Close connection
	$query->close();
	$mysqli->close();
	
} else {
	// Multiple servers
	if(isset($_GET['server'])){
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
		
		// Get all players tracked
		$query = $mysqli->query("SELECT player_name, time_online, kills, deaths, blocks_placed, blocks_broken, balance FROM statistics_players ORDER BY time_online DESC");
		
		$output = array();
		
		// Convert results from query into array
		while($row = $query->fetch_assoc()){
			$output[] = $row;
		}

		// Close connection
		$query->close();
		$mysqli->close();

	} else {
		// No server selected
		
	}
}
?>
  <body>
	<?php require($path . 'inc/templates/navbar.php'); ?>
	
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
	  <h2>Leaderboards</h2>
	  <table id="leaderboards" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<thead>
		  <tr>
			<th>Username</th>
			<th>Time Online</th>
			<th>Blocks Placed</th>
			<th>Blocks Broken</th>
			<th>Kills</th>
			<th>Deaths</th>
			<th>Balance</th>
		  </tr>
		</thead>
	 
		<tbody>
		  <?php
		  foreach($output as $item){
			// Convert time online to days, minutes and seconds
			$time_online = $item['time_online'];
			$dtF = new DateTime("@0");
			$dtT = new DateTime("@$time_online");
			$time_online = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes, %s seconds');
		  ?>
		  <tr>
			<td><a href="../players/?p=<?php echo htmlspecialchars($item['player_name']); ?>"><?php echo htmlspecialchars($item['player_name']); ?></a></td>
			<td><?php echo $time_online; ?></td>
			<td><?php echo htmlspecialchars($item['blocks_placed']); ?></td>
			<td><?php echo htmlspecialchars($item['blocks_broken']); ?></td>
			<td><?php echo htmlspecialchars($item['kills']); ?></td>
			<td><?php echo htmlspecialchars($item['deaths']); ?></td>
			<td><?php echo htmlspecialchars($item['balance']); ?></td>
		  </tr>
		  <?php } ?>
		</tbody>
	  </table>
	  <hr>
	  
	  <?php require($path . 'inc/templates/footer.php'); ?>
	  
	<?php 
	} else { // Multiple servers 
		if(isset($_GET['server'])){
	?>
	  <h2><?php echo htmlspecialchars($_GET['server']); ?> Leaderboards</h2>
	  <table id="leaderboards" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<thead>
		  <tr>
			<th>Username</th>
			<th>Time Online</th>
			<th>Blocks Placed</th>
			<th>Blocks Broken</th>
			<th>Kills</th>
			<th>Deaths</th>
			<th>Balance</th>
		  </tr>
		</thead>
	 
		<tbody>
		  <?php
		  foreach($output as $item){
			// Convert time online to days, minutes and seconds
			$time_online = $item['time_online'];
			$dtF = new DateTime("@0");
			$dtT = new DateTime("@$time_online");
			$time_online = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes, %s seconds');
		  ?>
		  <tr>
			<td><a href="../players/?p=<?php echo htmlspecialchars($item['player_name']); ?>"><?php echo htmlspecialchars($item['player_name']); ?></a></td>
			<td><?php echo $time_online; ?></td>
			<td><?php echo htmlspecialchars($item['blocks_placed']); ?></td>
			<td><?php echo htmlspecialchars($item['blocks_broken']); ?></td>
			<td><?php echo htmlspecialchars($item['kills']); ?></td>
			<td><?php echo htmlspecialchars($item['deaths']); ?></td>
			<td><?php echo htmlspecialchars($item['balance']); ?></td>
		  </tr>
		  <?php } ?>
		</tbody>
	  </table>
	  
	  <hr>
	  
	  <?php require($path . 'inc/templates/footer.php'); ?>
	<?php 
		} else {
			// need to select a server
	?>
		<h2>Select a server</h2>
		<?php foreach($GLOBALS['servers'] as $key => $item){ ?>
		<a href="./?server=<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($key); ?></a><br />
		<?php } ?>
	    <hr>
	  
	    <?php require($path . 'inc/templates/footer.php'); ?>
	<?php
		}
	} 
	?>
	</div>

	<?php require($path . 'inc/templates/scripts.php'); ?>
	<script>
	$(document).ready(function(){
		$("[rel=tooltip]").tooltip({ placement: 'top'});
	});
	</script>
	<!-- DataTables -->
	<script src="../assets/js/jquery.dataTables.min.js"></script>
	<script src="../assets/js/dataTables.bootstrap.js"></script>
	
	<script>
	$(document).ready(function() {
		$('#leaderboards').dataTable({
			"order": [[ 1, "desc" ]]
		});
	} );
	</script>
  </body>
</html>
