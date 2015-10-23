<?php

	// this 
	include('config.php');

	// connect to the database
	$db = mysqli_connect ($db_host, $db_user, $db_password, $db_name) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

	// if the form has been submitted
	if(isset($_POST['submit']))
	{
		// create an empty error array
		$error = array();

		// check for a firstname
		if(empty($_POST['firstname']))
		{
			$error['firstname'] = 'Required field';
		} 

		// check for a lastname
		if(empty($_POST['lastname']))
		{
			$error['lastname'] = 'Required field';
		} 

		// check for a email
		if(empty($_POST['email']))
		{
			$error['email'] = 'Required field';
		} else {

			// check to see if email address is unique
			$sql = "SELECT user_id FROM users WHERE email = '{$_POST['email']}'";
			$result = mysqli_query($db, $sql);
			if(mysqli_num_rows($result) > 0)
			{
				$error['email'] = 'You already have an account';
			}
		}

		// check for a password
		if(empty($_POST['userpass']))
		{
			$error['userpass'] = 'Required field';
		} 

		// if there are no errors
		if(sizeof($error) == 0)
		{
			// add the user
			$firstname = mysqli_real_escape_string($db, $_POST['firstname']);
			$lastname = mysqli_real_escape_string($db, $_POST['lastname']);
			$email = mysqli_real_escape_string($db, $_POST['email']);
			$userpass = mysqli_real_escape_string($db, $_POST['userpass']);
			
			$sql = "INSERT INTO users (
						user_id,
						firstname,
						lastname,
						email,
						userpass,
						signupdate
					) VALUES (
						null,
						'$firstname',
						'$lastname',
						'$email',
						sha1('$userpass'),
						NOW()
					)";
			mysqli_query($db, $sql);
			
			// get user_id
			$user_id = mysqli_insert_id($db);
			
			// start or continue a session
			session_start();
			
			// added variables to our session
			$_SESSION['user_id'] = $user_id;
			$_SESSION['firstname'] = $firstname;
			
			// email the user
			$message = 'Welcome ' . $firstname . ' ' . $lastname . '!';
			mail($email, 'Welcome to my site', $message, "From: admin@atmysite.com");
			
			// go somewhere else
			header("Location: activity.php");
			
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
		
	</head>
	<body>
		
			<h2>Sign up</h2>

			<!-- signup form -->
			<form method="post" action="signup.php">
				
				<!-- first name -->
				<label>First Name</label><br />
				<input name="firstname" type="text" value="<?php echo $_POST['firstname']; ?>" />
				<span class="text-danger"><?php echo $error['firstname']; ?></span>
				<br /><br />

							
				<!-- last name -->
				<label>Last Name</label><br />
				<input name="lastname" type="text" value="<?php echo $_POST['lastname']; ?>" />
				<span class="text-danger"><?php echo $error['lastname']; ?></span>
				<br /><br />
				
				<!-- e-mail -->
				<label>E-mail</label><br />
				<input name="email" type="text" value="<?php echo $_POST['email']; ?>" />
				<span class="text-danger"><?php echo $error['email']; ?></span>
				<br /><br />

				
				<!-- password -->
				<label>Password</label><br />
				<input name="userpass" type="password" />
				<span class="text-danger"><?php echo $error['userpass']; ?></span>
				<br /><br />
				
				<!-- submit button -->
				<input name="submit" type="submit" value="Sign up" />
				
			</form>
			
			<!-- sign in link -->
			<p>Already have an account? <a href="index.php">Sign in</a>!</p>
			
		</div>
	
	</body>
</html>