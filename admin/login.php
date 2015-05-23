<?php
/*
 *  Web interface made by Samerton (https://github.com/samerton)
 *  Statistics plugin by PickNChew (http://www.spigotmc.org/members/picknchew.12729/)
 */
 
$path = "../";
$page = "admin-login";

// Require config
require($path . 'inc/conf.php');

// Require password compat for PHP versions under 5.5
require($path . 'inc/includes/password.php'); 

// Get some variables from the config file
$title = htmlspecialchars($GLOBALS['project_name']);

// Start initialising the page - display header
require($path . 'inc/templates/header.php');

/*
 *  User needs to be logged in
 */
 
if(isset($_GET['sid'])){
	echo '<script>window.location.replace(\'../\');</script>';
	die();
}

// Check for input
if(isset($_POST['token'])){
	if($_POST['token'] !== $_SESSION['token']){
		// Invalid token
		echo 'Invalid Token';
		die();
	}
	
	// Valid username?
	if($_POST['username'] == $GLOBALS['admin']['username']){
		// Correct username
		
		if(password_verify($_POST['password'], $GLOBALS['admin']['password'])){
			// Correct password
			$sid = substr(str_shuffle(MD5(microtime())), 0, 32);
			$_SESSION['sid'] = $sid;
			echo '<script>window.location.replace(\'./?sid=' . $sid . '\');</script>';
			die();
		} else {
			// Incorrect password
			echo '<script>window.location.replace(\'./?error=true\');</script>';
			die();
		}
	} else {
		// Incorrect username
		echo '<script>window.location.replace(\'./?error=true\');</script>';
		die();
	}

}

?>
  <body>
	
	<div class="container">
	  <div class="row">
	    <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
		  <h1>Statistics Admin Interface</h1>
		  <?php if(isset($_GET['error'])){ ?>
		  <div class="alert alert-danger">Invalid username or password</div>
		  <?php } ?>
		  <h3>Log In</h3>
		  <form action="./" method="post">
			<div class="form-group">
				<input type="text" name="username" id="username" autocomplete="off" class="form-control input-lg" placeholder="Username" tabindex="1">
			</div>
			<div class="form-group">
				<input type="password" name="password" id="password" autocomplete="off" class="form-control input-lg" placeholder="Password" tabindex="2">
			</div>
			<?php
			// token
			$token = md5(uniqid());
			$_SESSION['token'] = $token;
			?>
			<input type="hidden" name="token" value="<?php echo $token; ?>">
			<input type="submit" value="Submit" class="btn btn-primary btn-block btn-lg" tabindex="3">
		  </form>
	    </div>
	  </div>
    </div>
	
  </body>
</html>
