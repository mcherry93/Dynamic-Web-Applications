<?php
	
	// include configuration file
	include('config.php');

	// connect to the database
	$db = mysqli_connect ($db_host, $db_user, $db_password, $db_name) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

	// continue the session
	session_start();

	// is the user allowed here
	if(!$_SESSION['user_id'])
	{
		header("Location: signup.php");
	}

?>

<html>
	<head>
		<title>Shoutbox</title>
	</head>
	<body>
		
		<!-- top navigation -->
		<a href="activity.php">Activity</a> | 
		<a href="profile.php">Profile</a> | 
		<a href="signout.php">Sign out</a>
		<hr />
		
		<h1>Welcome <?php echo $_SESSION['firstname']; ?></h1>
		
		<?php
			
			// check for shout deletion
			if($_GET['action'] == 'remove')
			{
				
				$sql = "SELECT user_id FROM shouts2 WHERE shout_id = '{$_GET['id']}' LIMIT 1";
				$result = mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
				$row = mysqli_fetch_assoc($result);
				
				// check ownership
				if($row['user_id'] == $_SESSION['user_id'])
				{
					// delete shout
					$sql = "DELETE FROM shouts2 WHERE shout_id = '{$_GET['id']}' LIMIT 1";
					$result = mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
				
					// display confirmation
					echo "<p>Your shout has been removed</p>";
				}
			}
			
			// check for shout submission
			if(isset($_POST['submit']))
			{
				// if the shout is not empty
				if(!empty($_POST['shout']))
				{
					// clean shout
					$shout = mysqli_real_escape_string($db, $_POST['shout']);
					
					// insert shout
					$sql = "INSERT INTO shouts2 (
								shout_id, 
								user_id, 
								shout, 
								shout_date
							) VALUES (
								null, 
								'{$_SESSION['user_id']}', 
								'$shout', 
								NOW()
							)";
					$result = mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
					
					// display confirmation
					echo "<p>Your shout has been added</p>";
					
				} 
			}

			
			?>
			
			<!-- shoutbox form -->
			<form method="post" action="activity.php">
				<lable>What do you want to say?</lable><br />
				<textarea name="shout" rows="5" cols="30"></textarea><br /><br />
				<input name="submit" type="submit" value="Shout" />
			</form>
			
			<?php
				
			// select all shouts from the database		
			$sql = "SELECT 
						shout_id, 
						user_id, 
						shout, 
						DATE_FORMAT(shout_date,'%M %d, %Y') AS formatted_date 
					FROM 
						shouts2 
					ORDER BY 
						shout_date DESC";
			$result = mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
			while ($row = mysqli_fetch_assoc($result)) 
			{
				// get user information
				$sql2 = "SELECT user_id, firstname, lastname FROM users WHERE user_id = '{$row['user_id']}'";
				$result2 = mysqli_query($db, $sql2);
				$row2 = mysqli_fetch_assoc($result2);
				
				// check for a profile image
				if(file_exists('photos/' . $row['user_id'] . '.jpg'))
				{
					// assign time to prevent image caching
					$timestamp = time();
					
					// If the user has a profile image on file, display the user's profile image
					echo "<img src=\"photos/{$row['user_id']}.jpg?time={$timestamp}\" />";
					
				} else {
				
					// If the user does not have a profile image on file, display a default profile image
					echo "<img src=\"photos/noimage.png\" />";
					
				}
				
				// check ownership
				if($row['user_id'] == $_SESSION['user_id'])
				{	
					echo "<p><a href=\"activity.php?action=remove&id={$row['shout_id']}\">Delete Shout</a></p>";
				}
				
				// display name and shout
				echo "<p><strong>{$row2['firstname']} {$row2['lastname']} writes:</strong></p>";
				echo "<p>{$row['shout']}</p>";
				echo "<p>{$row['formatted_date']}</p>";
				echo "<hr />";
			}
		?>
		
	</body>
</html>