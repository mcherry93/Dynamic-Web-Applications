<?php

// include configuration file
include('config.php');

// Include SimpleImage library (https://github.com/claviska/SimpleImage)
include('abeautifulsite/SimpleImage.php');
	
// connect to the database
$db = mysqli_connect ($db_host, $db_user, $db_password, $db_name) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

// continue session
session_start();
	
// check for a user_id
if(!$_SESSION['user_id'])
{
	// redirect user to homepage if they are not signed in
	header("Location: index.php");	
}

// If the form has been submitted, update user information
if(isset($_POST['submit']))
{
	// Create an error array
	$error = array();
	
	// Check for a firstname
	if(empty($_POST['firstname']))
	{
		$error['firstname'] = 'Required field';
	} else {
		$firstname = $_POST['firstname'];
	}
	
	// Check for a lastname
	if(empty($_POST['lastname']))
	{
		$error['lastname'] = 'Required field';
	} else {
		$lastname = $_POST['lastname'];
	}
	
	// Check for a email
	if(empty($_POST['email']))
	{
		$error['email'] = 'Required field';
	} else {
	
		// Check to see if email address is unique
		$query = "select user_id from users where email = '{$_POST['email']}'";
		$result = mysqli_query($db, $query);
		$row = mysqli_fetch_assoc($result);
		if(mysqli_num_rows($result) > 0)
		{
			// Check to see if this email address is owned by this user
			if($row['user_id'] != $_SESSION['user_id'])
			{
				$error['email'] = 'This email address is already taken';
			}
		}
		
		$email = $_POST['email'];
	}
	
	// check for an image
	if($_FILES['file']['tmp_name'])
	{
		// check for a general error
		if ($_FILES['file']['error'] > 0)
		{
			$error['file'] = 'An error has occurred';
		}
	
		// check for valid file type
		if (($_FILES["file"]["type"] != "image/gif")   &&
			($_FILES["file"]["type"] != "image/jpeg")  &&
			($_FILES["file"]["type"] != "image/pjpeg") &&
			($_FILES["file"]["type"] != "image/png"))
		{
			$error['file'] = 'Invalid file type';
		}
	}
	
	// if there are no errors
	if(sizeof($error) == 0)
	{
		// edit user information in the users table
		$query = "update users set 
						firstname = '{$_POST['firstname']}', 
						lastname = '{$_POST['lastname']}',  
						email = '{$_POST['email']}'
				 	where
				 		user_id = '{$_SESSION['user_id']}'";
		$result = mysqli_query($db, $query);
		
		// update the photo is applicable
		if($_FILES['file']['tmp_name'])
		{
			// upload photo (https://github.com/claviska/SimpleImage)
			try
			{
				// initialize simpleImage
				$img = new abeautifulsite\SimpleImage($_FILES['file']['tmp_name']);

				// create a small photo
				$img->fit_to_width(250)->save('photos/' . $_SESSION['user_id'] . '.jpg');
				
				// create a large photo
				$img->fit_to_width(800)->save('photos/large_' . $_SESSION['user_id'] . '.jpg');    
			
			} catch(Exception $e) {
				echo 'Error: ' . $e->getMessage();
			}
		}
		
		// Redirect user to profile page (with a confirmation)
		header("Location: profile.php?confirmation=profile");
		exit();
				
	} 

} else {

	// If the form has not been submitted, get user information so that we can fill in the default form values
	$query = "SELECT firstname, lastname, email FROM users WHERE user_id = '{$_SESSION['user_id']}'";
	$result = mysqli_query($db, $query);
	$row = mysqli_fetch_assoc($result);
	
	// Assign user information to template
	$firstname = $row['firstname'];
	$lastname = $row['lastname'];
	$email = $row['email'];
	$biography = $row['biography'];
	
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
		
		<!-- top navigation -->
		<a href="activity.php">Activity</a> | 
		<a href="profile.php">Profile</a> | 
		<a href="signout.php">Sign out</a>
		<hr />

		<h1><?php echo "{$_SESSION['firstname']} {$_SESSION['lastname']}"; ?></h1>
			
			<?php
				
				// display a confirmation message if applicable
				if($_GET['confirmation'] == 'profile')
				{
					echo "<p>Your profile has been updated</p>";
				}
	
				// Check if the user has a profile image on file 
				if(file_exists('photos/' . $_SESSION['user_id'] . '.jpg'))
				{
					// Assign time to prevent image caching
					$timestamp = time();

					// If the user has a profile image on file, display the user's profile image
					echo "<img src=\"photos/large_{$_SESSION['user_id']}.jpg?time={$timestamp}\" style=\"width: 200px\" />";

				} else {

					// If the user does not have a profile image on file, display a default profile image
					echo "<img src=\"photos/large_noimage.png\"  />";

				}
			?>

			<!-- edit profile form -->
			<form method="post" enctype="multipart/form-data" action="profile.php">

				<!-- first name -->
				<label>First Name</label><br />
				<input name="firstname" type="text" value="<?php echo $firstname; ?>" />
				<span ><?php echo $error['firstname']; ?></span><br /><br />

				<!-- last name -->
				<label>Last Name</label><br />
				<input name="lastname" type="text" value="<?php echo $lastname; ?>" />
				<?php echo $error['lastname']; ?><br /><br />

				<!-- email -->
				<label>Email</label><br />
				<input name="email" type="text" value="<?php echo $email; ?>" />
				<?php echo $error['email']; ?><br /><br />

				<!-- upload image -->
				<label>Profile Image</label><br />
				<input name="file" type="file" />
				<?php echo $error['file']; ?><br /><br />

				<!-- submit button -->
				<input name="submit" type="submit" value="Save" />

			</form>
	</body>
</html>