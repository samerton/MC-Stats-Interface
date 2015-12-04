<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */

$path = "../";
$page = "admin-index";

// Require config
require($path . 'inc/conf.php');

// Initialise
require($path . 'inc/init.php');

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


/*
 *  Is it a single server setup?
 */
if(count($GLOBALS['servers']) == 1){
	// Single server
	foreach($GLOBALS['servers'] as $key => $item){
		$server_name = $key;
	}
	
	/*
	 *  Connect to the database
	 */
	$mysqli = new mysqli($GLOBALS['servers'][$server_name]['host'], $GLOBALS['servers'][$server_name]['username'], $GLOBALS['servers'][$server_name]['password'], $GLOBALS['servers'][$server_name]['db']);
	if($mysqli->connect_errno) {
		echo '<div class="alert alert-warning">' .  $mysqli->connect_errno . ' - ' . $mysqli->connect_error . '</div>';
		die();
	}
	
	/*
	 *  Get performance information
	 */
	
	if(!isset($_GET['period'])){
		// Last 2 days only
		$two_days_ago = strtotime('-2 days');
		$query = $mysqli->query("SELECT time, tps, free_ram, amount_online FROM statistics_performance WHERE time > $two_days_ago");
	} else {
		// All time
		$query = $mysqli->query("SELECT time, tps, free_ram, amount_online FROM statistics_performance");
	}
	
	// output strings for charts
	$tps_output_string = '[';
	$players_output_string = '[';
	$ram_output_string = '[';
	
	$results_number = $query->num_rows;
	$i = 1;
	
	// loop through and add data to output string
	while($row = $query->fetch_assoc()){
		if($i == $results_number){
			// Get current performance;
			$current_tps = $row['tps'];
			$current_ram = round($row["free_ram"] / 1024, 2);
			$current_players = $row['amount_online'];
		}
		
		$tps_output_string .= '[' . $row["time"] * 1000 . ', ' . $row["tps"] . '],';
		$ram_output_string .= '[' . $row["time"] * 1000 . ', ' . round($row["free_ram"] / 1024, 2) . '],';
		$players_output_string .= '[' . $row["time"] * 1000 . ', ' . $row["amount_online"] . '],';
		
		$i++;
	}
	
	$tps_output_string .= ']';
	$players_output_string .= ']';
	$ram_output_string .= ']';
	 
	// Close connection
	$query->close();
	$mysqli->close();
	

	/*
	 *  Ping the Minecraft server
	 */
	require($path . 'inc/includes/MinecraftServerPing.php');
	require($path . 'inc/includes/MinecraftPingException.php');
	
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
	
	if(isset($_GET['server'])){
		// Check the server exists
		$server_name = htmlspecialchars($_GET['server']);
		if(!isset($GLOBALS['servers'][$server_name])){
			// The server doesn't exist!
			echo '<script>window.location.replace("./");</script>';
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
		
		/*
		 *  Get performance information
		 */
		
		if(!isset($_GET['period'])){
			// Last 2 days only
			$two_days_ago = strtotime('-2 days');
			$query = $mysqli->query("SELECT time, tps, free_ram, amount_online FROM statistics_performance WHERE time > $two_days_ago");
		} else {
			// All time
			$query = $mysqli->query("SELECT time, tps, free_ram, amount_online FROM statistics_performance");
		}
		
		// output strings for charts
		$tps_output_string = '[';
		$players_output_string = '[';
		$ram_output_string = '[';
		
		$results_number = $query->num_rows;
		$i = 1;
		
		// loop through and add data to output string
		while($row = $query->fetch_assoc()){
			if($i == $results_number){
				// Get current performance;
				$current_tps = $row['tps'];
				$current_ram = round($row["free_ram"] / 1024, 2);
				$current_players = $row['amount_online'];
			}
			
			$tps_output_string .= '[' . $row["time"] * 1000 . ', ' . $row["tps"] . '],';
			$ram_output_string .= '[' . $row["time"] * 1000 . ', ' . round($row["free_ram"] / 1024, 2) . '],';
			$players_output_string .= '[' . $row["time"] * 1000 . ', ' . $row["amount_online"] . '],';
			
			$i++;
		}
		
		$tps_output_string .= ']';
		$players_output_string .= ']';
		$ram_output_string .= ']';
		 
		// Close connection
		$query->close();
		$mysqli->close();
	}
	
	/*
	 *  Ping the Minecraft servers to get a total player count
	 */
	require($path . 'inc/includes/MinecraftServerPing.php');
	require($path . 'inc/includes/MinecraftPingException.php');

	$online_players = 0;
	
	foreach($GLOBALS['servers'] as $key => $server){
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
			$exception_server = htmlspecialchars($key);
		}
		if(isset($Query) && $Query !== null){
			$Query->Close();
		}
	}
}
?>
  <body>
	<?php require($path . 'inc/templates/navbar.php'); ?>
	
	<div class="container">
	  <?php if(isset($exception)){ ?>
	  <div class="alert alert-warning">
	    <p><strong>Exception pinging the server '<?php echo $exception_server; ?>'.</strong></p>
		<p><?php echo $exception; ?></p>
	  </div>
	  <?php } ?>
	  
	<?php 
	// Single server
	if(count($GLOBALS['servers']) == 1){
	?>
	
	  <h2><?php echo $title; ?> Admin Interface</h2>
	  
	  <hr>
	  
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
			<br />
		    <h3 style="display: inline;">Performance <?php if(!isset($_GET['period'])){ ?>(Last 2 days)<?php } else { ?>(All time)<?php } ?></h3>
			<?php if($tps_output_string !== '[]'){ ?>
			<span class="pull-right">
			  <select id="graph_dropdown" multiple="multiple">
				<option value="tps" selected>TPS</option>
				<option value="ram" selected>Free RAM</option>
				<option value="players" selected>Players</option>
			  </select>
			  <?php if(!isset($_GET['period'])){ ?><a href="./?sid=<?php echo $sid; ?>&amp;period=all" class="btn btn-primary">All time</a><?php } else { ?><a href="./?sid=<?php echo $sid; ?>" class="btn btn-primary">Last 2 days</a><?php } ?>
			</span>
			<br /><br />
			<p>
			<span class="label label-info">Current Free RAM: <?php echo $current_ram; ?>GB</span> 
			<span class="label label-info">Current Players: <?php echo $current_players; ?></span>
			<span class="label label-<?php
			// which colour label?
			if($current_tps <= 14) echo "danger"; elseif($current_tps > 14 && $current_tps < 19) echo "warning"; else echo "success";
			?>">Current TPS: <?php echo $current_tps; ?></span>
			</p>
			
			<div class="graph-container">
				<div id="placeholder" class="graph-placeholder"></div>
			</div>
			<?php if(isset($_GET['period'])){ ?>
			<div class="graph-container" style="height:150px;">
				<div id="overview" class="graph-placeholder"></div>
			</div>
			<?php } ?>
			<?php 
			} else { 
				if(!isset($_GET['period'])){
			?>
			<br /><br />No information from the last 2 days. <a href="./?sid=<?php echo $sid; ?>&amp;period=all">View all time</a>.
			<?php 
				} else {
			?>
			<br /><br />No performance information in the database.
			<?php
				}
			} 
			?>
		  </div>
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
	    <span rel="tooltip" data-trigger="hover" data-original-title="<?php echo $player['name']; ?>"><a href="/players/?p=<?php echo $player['name']; ?>"><img src="https://cravatar.eu/avatar/<?php echo $player['name']; ?>/50.png" style="width: 40px; height: 40px; margin-bottom: 5px; margin-left: 5px; border-radius: 3px;" /></a></span>
	  <?php 
			}
		} 
	  } else {
	  ?>
	  <div class="alert alert-danger">Unable to query server</div>
	  <?php } ?>
	  
	<?php } else { // Multiple servers ?>
		<?php if(!isset($_GET['server'])){ ?>
		<h3>Select a server</h3>
		<?php foreach($GLOBALS['servers'] as $key => $server){ ?>
		<a href="./?sid=<?php echo $sid; ?>&server=<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($key); ?></a><br />
		<?php } ?>
		<?php } else { ?>
		
		<h3 style="display: inline;">Viewing server <?php echo htmlspecialchars($_GET['server']); ?></h3>
		<span class="pull-right"><a href="./?sid=<?php echo $sid; ?>">Change server</a></span>
		
		  <div class="row">
			<div class="col-md-3">
			  <div class="well well-sm">
				<ul class="nav nav-pills nav-stacked">
				  <li<?php if($page === "admin-index"){ ?> class="active"<?php } ?>><a href="<?php echo $path; ?>admin/?sid=<?php echo $sid; ?>&server=<?php echo htmlspecialchars($_GET['server']); ?>">Overview</a></li>
				  <li<?php if($page === "admin-settings"){ ?> class="active"<?php } ?>><a href="<?php echo $path; ?>admin/settings.php?sid=<?php echo $sid; ?>">Settings</a></li>
				</ul>
			  </div>
			</div>
			<div class="col-md-9">
			  <div class="well well-sm">
				<br />
				<h3 style="display: inline;">Performance <?php if(!isset($_GET['period'])){ ?>(Last 2 days)<?php } else { ?>(All time)<?php } ?></h3>
				<?php if($tps_output_string !== '[]'){ ?>
				<span class="pull-right">
				  <select id="graph_dropdown" multiple="multiple">
					<option value="tps" selected>TPS</option>
					<option value="ram" selected>Free RAM</option>
					<option value="players" selected>Players</option>
				  </select>
				  <?php if(!isset($_GET['period'])){ ?><a href="./?sid=<?php echo $sid; ?>&amp;server=<?php echo htmlspecialchars($_GET['server']); ?>&amp;period=all" class="btn btn-primary">All time</a><?php } else { ?><a href="./?sid=<?php echo $sid; ?>&server=<?php echo htmlspecialchars($_GET['server']); ?>" class="btn btn-primary">Last 2 days</a><?php } ?>
				</span>
				<br /><br />
				<p>
				<span class="label label-info">Current Free RAM: <?php echo $current_ram; ?>GB</span> 
				<span class="label label-info">Current Players: <?php echo $current_players; ?></span>
				<span class="label label-<?php
				// which colour label?
				if($current_tps <= 14) echo "danger"; elseif($current_tps > 14 && $current_tps < 19) echo "warning"; else echo "success";
				?>">Current TPS: <?php echo $current_tps; ?></span>
				</p>
				
				<div class="graph-container">
					<div id="placeholder" class="graph-placeholder"></div>
				</div>
				<?php if(isset($_GET['period'])){ ?>
				<div class="graph-container" style="height:150px;">
					<div id="overview" class="graph-placeholder"></div>
				</div>
				<?php } ?>
				<?php 
				} else { 
					if(!isset($_GET['period'])){
				?>
				<br /><br />No information from the last 2 days. <a href="./?sid=<?php echo $sid; ?>&server=<?php echo htmlspecialchars($_GET['server']); ?>&period=all">View all time</a>.
				<?php 
					} else {
				?>
				<br /><br />No performance information in the database.
				<?php
					}
				} 
				?>
			  </div>
			</div>
		  </div>
		
		<?php } ?>
	<?php } ?>
	  <hr>
	  <?php require($path . 'inc/templates/footer.php'); ?>
	</div>
	<?php require($path . 'inc/templates/scripts.php'); ?>
	<script>
	$(document).ready(function(){
		$("[rel=tooltip]").tooltip({ placement: 'top'});
	});
	</script>
	<script src="<?php echo $path; ?>assets/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
	<!-- Charts -->
	<script src="<?php echo $path; ?>assets/js/charts/jquery.flot.min.js"></script>
	<script src="<?php echo $path; ?>assets/js/charts/jquery.flot.resize.min.js"></script>
	<?php if(isset($_GET['period'])){ ?>
	<script src="<?php echo $path; ?>assets/js/charts/jquery.flot.selection.min.js"></script>
	<?php } ?>
	<script src="<?php echo $path; ?>assets/js/charts/jquery.flot.time.min.js"></script>
	<script src="<?php echo $path; ?>assets/js/charts/jquery.flot.tooltip.min.js"></script>
	
	<script type="text/javascript">
	var tps = { data: <?php echo $tps_output_string; ?>, label: "TPS"};
	var ram = { data: <?php echo $ram_output_string; ?>, label: "RAM (GB)", yaxis: 2 };
	var players = { data: <?php echo $players_output_string; ?>, label: "Players" };
	var display = [tps, ram, players];
	
    $(document).ready(function() {
		// Dropdown selection
		$('#graph_dropdown').multiselect({
			onChange: function(option, checked, select) {
				var items = $('#graph_dropdown option:selected');
				var display = [];
				$(items).each(function(index, item){
					var value = $(this).val();
					display.push(window[value]);
					
				});
				doPlot(display);
			}
		});
		
		
		function doPlot(display) {
			console.log(JSON.stringify(display));
			var plot = $.plot("#placeholder", display, {
				xaxes: [ { mode: "time" } ],
				yaxes: [ { min: 0 }, {
					// align if we are to the right
					alignTicksWithAxis: 1,
					position: 'right'
				} ],
				selection: {
					mode: "x"
				},
				legend: { position: "sw" },
				grid: {
					hoverable: true 
				},
				tooltip: true,
				tooltipOpts: {
					content: "%s : %y",
					onHover: function(flotItem, $tooltipEl) {} 
				}
			});

			<?php if(isset($_GET['period'])){ ?>
			// Overview chart underneath
			var overview = $.plot("#overview", display, {
				series: {
					lines: {
						show: true,
						lineWidth: 1
					},
					shadowSize: 0
				},
				xaxis: {
					ticks: [],
					mode: "time"
				},
				yaxis: {
					ticks: [],
					min: 0,
					autoscaleMargin: 0.1
				},
				selection: {
					mode: "x"
				},
				legend: { position: "sw" }
			});

			$("#placeholder").bind("plotselected", function (event, ranges) {

				// do the zooming
				$.each(plot.getXAxes(), function(_, axis) {
					var opts = axis.options;
					opts.min = ranges.xaxis.from;
					opts.max = ranges.xaxis.to;
				});
				plot.setupGrid();
				plot.draw();
				plot.clearSelection();

				overview.setSelection(ranges, true);
			});

			$("#overview").bind("plotselected", function (event, ranges) {
				plot.setSelection(ranges);
			});
			<?php } ?>
			
		}
		
		doPlot(display);
		
    });
	
	</script>
	
  </body>
</html>
