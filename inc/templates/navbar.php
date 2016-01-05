<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */
?>    
    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo $path; ?>"><?php echo $title; ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li<?php if($page === "home"){ ?> class="active"<?php } ?>><a href="<?php echo $path; ?>">Home</a></li>
            <li<?php if($page === "leaderboards"){ ?> class="active"<?php } ?>><a href="<?php echo $path; ?>leaderboards<?php if(isset($_GET['server'])){ echo '/?server=' . htmlspecialchars($_GET['server']); }?>">Leaderboards</a></li>
			<li<?php if($page === "players"){ ?> class="active"<?php } ?>><a href="<?php echo $path; ?>players">Players</a></li>
          </ul>
		  <form class="navbar-form navbar-right" action="<?php echo $path; ?>players/" method="post">
		    <?php
			  // Generate token for form
			  if(isset($_SESSION['stats_token']){
				  $user_token = $_SESSION['stats_token'];
			  } else {
				  $user_token = '';
			  }
			  $token = md5(uniqid());
			  $_SESSION['stats_token'] = $token;
			?>
			<div class="form-group">
			  <input type="text" class="form-control" name="username" placeholder="Search for a user..">
			  <input type="hidden" name="token" value="<?php echo $token; ?>">
			</div>
			<button type="submit" class="btn btn-default">Submit</button>
		  </form>
        </div><!--/.nav-collapse -->
      </div>
    </nav>