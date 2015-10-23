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

// If the form has not been submitted, get user information so that we can fill in the default form values
} else {

	// Get user information
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

		<!-- jQuery -->
		<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>

		<!-- bootstrap -->
		<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
		<link href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">	
		
		<style type="text/css">
			.profileimage {
				border: 1px solid #ccc; 
				width: 100%;
			}
		</style>			
	</head>
	<body>
		
		<!-- top navigation -->
		<?php include('topnavigation.php'); ?>
		
		<!-- content -->	
		<div class="container" style="margin-top: 65px">

			<h2><?php echo "{$_SESSION['firstname']} {$_SESSION['lastname']}"; ?></h2>
			
			<?php
				
				// display a confirmation message if applicable
				if($_GET['confirmation'] == 'profile')
				{
					echo "<div class=\"alert alert-success\">Your profile has been updated</div>";
				}
			
			?>
			
			<!-- bootstrap row -->
			<div class="row">
			
				<!-- left column -->
				<div class="col-md-3">
				
					<?php
					
						// Check if the user has a profile image on file 
						if(file_exists('photos/' . $_SESSION['user_id'] . '.jpg'))
						{
							// Assign time to prevent image caching
							$timestamp = time();
							
							// If the user has a profile image on file, display the user's profile image
							echo "<img src=\"photos/large_{$_SESSION['user_id']}.jpg?time={$timestamp}\" class=\"img-rounded profileimage\" style=\"width: 200px\" />";
							
						} else {
						
							// If the user does not have a profile image on file, display a default profile image
							echo "<img src=\"photos/large_noimage.png\" class=\"img-rounded profileimage\" />";
							
						}
					?>
				
				</div>
				
				<!-- right column -->
				<div class="col-md-9">
					
					<!-- edit profile form -->
					<form method="post" enctype="multipart/form-data" action="profile.php">
						
						<!-- first name -->
						<div class="form-group">
							<label>First Name</label>
							<input name="firstname" type="text" value="<?php echo $firstname; ?>" autocomplete="off" class="form-control" />
							<span class="text-danger"><?php echo $error['firstname']; ?></span>
						</div>
						
						<!-- last name -->
						<div class="form-group">
							<label>Last Name</label>
							<input name="lastname" type="text" value="<?php echo $lastname; ?>" autocomplete="off" class="form-control" />
							<span class="text-danger"><?php echo $error['lastname']; ?></span>
						</div>
						
						<!-- email -->
						<div class="form-group">
							<label>Email</label>
							<input name="email" type="text" value="<?php echo $email; ?>" autocomplete="off" class="form-control" />
							<span class="text-danger"><?php echo $error['email']; ?></span>
						</div>
						
						<!-- profile photo -->
						<div class="form-group">
							<label for="file">Profile Image</label>
							<input id="file" name="file" type="file" />
							<span class="text-danger"><?php echo $error['file']; ?></span>
						</div>
						
						<!-- submit button -->
						<input name="submit" type="submit" value="Save" class="btn btn-primary" />
						
					</form>
	
				</div>
			
			</div>
						
		</div>
	
	</body>
</html>