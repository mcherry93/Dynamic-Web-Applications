<?php

// include configuration file
include('config.php');
	
// connect to the database
$db = mysqli_connect ($db_host, $db_user, $db_password, $db_name) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

// continue session
session_start();

// if the submit button has been pressed
if(isset($_POST['submit']))
{
	// create an empty error array
	$error = array();
	
	// check for a email
	if(empty($_POST['email']))
	{
		$error['email'] = 'Required field';
	} 
	
	// check for a password
	if(empty($_POST['userpass']))
	{
		$error['userpass'] = 'Required field';
	} 
	
	// check signin credentials
	if(!empty($_POST['email']) && !empty($_POST['userpass']))
	{
		// get user_id from the users table
		$sql = "SELECT 
					user_id, 
					firstname, 
					lastname 
				FROM 
					users 
				WHERE 
					email = '{$_POST['email']}' AND userpass = sha1('{$_POST['userpass']}') 
				LIMIT 1";
		$result = mysqli_query($db, $sql);
		$row = mysqli_fetch_assoc($result);
		
		// if the user is not found
		if(!$row['user_id'])
		{
			$error['user'] = 'Invalid username and/or password';
		}
	}
	
	// if there are no errors
	if(sizeof($error) == 0)
	{
		// append user variables to session
		$_SESSION['user_id'] = $row['user_id'];
		$_SESSION['firstname'] = $row['firstname'];
		$_SESSION['lastname'] = $row['lastname'];
		
		// redirect user to profile page
		header("Location: activity.php");
		exit();

	} 
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Shoutbox</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- bootstrap -->
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">	

	</head>
	<body>
		
		<!-- top navigation -->
		<?php include('topnavigation.php'); ?>
		
		<!-- content -->	
		<div class="container" style="margin-top: 65px">
		
			<h2>Sign in</h2>
			
			<?php
				// check for a user error
				if($error['user'])
				{
					echo "<div class=\"text-danger\">{$error['user']}</div>";
				}
			?>
						
			<!-- signin form -->
			<form method="post" action="index.php">
				
				<!-- email -->
				<div class="form-group">
					<label>Email</label><br />
					<input name="email" type="text" value="<?php echo $_POST['email']; ?>" class="form-control" />
					<span class="text-danger"><?php echo $error['email']; ?></span>
				</div>
				
				<!-- password -->
				<div class="form-group">
					<label for="password">Password</label><br />
					<input id="password" name="userpass" type="password" class="form-control" value="<?php echo $error['userpass']; ?>" />
					<span class="text-danger"><?php echo $error['userpass']; ?></span>
				</div>
				
				<!-- submit button -->
				<div class="form-group">
					<input name="submit" type="submit" value="Sign in" class="btn btn-primary" />
				</div>

			</form>
			
			<!-- sign up link -->
			<p>Don't already have an account? <a href="signup.php">Sign up</a>!</p>
			
		</div>
	
	</body>
</html>