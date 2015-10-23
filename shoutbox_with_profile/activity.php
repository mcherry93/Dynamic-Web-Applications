<?php

// include configuration file
include('config.php');
	
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
		
		<!-- fontawesome -->
		<link href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
		
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

			<h2>Welcome <?php echo "{$_SESSION['firstname']} {$_SESSION['lastname']}"; ?>!</h2>
			
			<?php
			
			// check for shout removal
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
					echo "<div class=\"alert alert-success\">Your shout has been removed</div>";
				}
			}
			
			// check for shout submission
			if(isset($_POST['submit']))
			{
				// empty error array
				$error = array();
				
				// check for a shout
				if(empty($_POST['shout']))
				{
					$error[] = 'A shout is required';
				}
				
				// if there are no errors, insert shout into the database.
				// otherwise, display errors.
				if(sizeof($error) == 0)
				{
					// insert shout
					$sql = "INSERT INTO shouts2 (shout_id, user_id, shout, shout_date) VALUES (null, '{$_SESSION['user_id']}', '{$_POST['shout']}', NOW())";
					$result = mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
					
					// display confirmation
					echo "<div class=\"text-success\">Your shout has been added</div>";
					
				} else {
					
					// display error message
					foreach($error as $value)
					{
						echo "<div class=\"text-error\">{$value}</div>";
					}
					
				}
			}

			
			?>
			
			<!-- shoutbox form -->
			<form method="post" action="activity.php" style="margin-bottom: 25px">
				<div class="form-group">
					<textarea name="shout" placeholder="What do you want to say?" class="form-control" rows="5"></textarea>
				</div>
				<input name="submit" type="submit" value="Shout" class="btn btn-primary" />
			</form>
			
			<?php
			
					
			// select all shouts from the database		
			$sql = "SELECT shout_id, user_id, shout, DATE_FORMAT(shout_date,'%M %d, %Y') AS formatted_date FROM shouts2 ORDER BY shout_date DESC";
			$result = mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
			while ($row = mysqli_fetch_assoc($result)) 
			{
				// get user information
				$sql2 = "SELECT user_id, firstname, lastname FROM users WHERE user_id = '{$row['user_id']}'";
				$result2 = mysqli_query($db, $sql2);
				$row2 = mysqli_fetch_assoc($result2);
			
				// display shout (two columns - left column display the image; right column displays the text)
				echo "<div class=\"well\">";
				echo "<div class=\"row\">";

				echo "<div class=\"col-md-1\">";
				
				// check for a profile image
				if(file_exists('photos/' . $row['user_id'] . '.jpg'))
				{
					// assign time to prevent image caching
					$timestamp = time();
					
					// If the user has a profile image on file, display the user's profile image
					echo "<img src=\"photos/{$row['user_id']}.jpg?time={$timestamp}\" class=\"img-rounded profileimage\" />";
					
				} else {
				
					// If the user does not have a profile image on file, display a default profile image
					echo "<img src=\"photos/noimage.png\" class=\"img-rounded profileimage\" />";
					
				}
				
				echo "</div>";
				echo "<div class=\"col-md-11\">";
				
				// check ownership
				if($row['user_id'] == $_SESSION['user_id'])
				{	
					echo "<a href=\"activity.php?action=remove&id={$row['shout_id']}\" class=\"pull-right btn btn-danger\"><i class=\"fa fa-times\"></i>
</i></a>";
				}
				
				// display name and shout
				echo "<p><strong>{$row2['firstname']} {$row2['lastname']} writes:</strong></p>";
				echo "<p>{$row['shout']}</p>";
				echo "<span style=\"color: #666\">{$row['formatted_date']}<span>";
				echo "</div>";
				echo "</div>";
				echo "</div>";
			}
		?>

			
		</div>
	
	</body>
</html>