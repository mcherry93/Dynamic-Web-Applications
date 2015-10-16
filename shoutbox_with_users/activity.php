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

		<!-- bootstrap -->
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
						
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
						echo "<div class=\"text-success\">Your shout has been removed</div>";
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
					
					// if there are no errors, insert shout into the database
					if(sizeof($error) == 0)
					{
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
									'{$_POST['shout']}', 
									NOW()
								)";
						$result = mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
						
						// display confirmation
						echo "<div class=\"text-success\">Your shout has been added</div>";
						
					} 
				}

			
			?>
			
			<!-- shoutbox form -->
			<form method="post" action="activity.php">
				<div class="form-group">
					<textarea name="shout" placeholder="What do you want to say?" class="form-control"></textarea>
				</div>
				<div class="form-group">
					<input name="submit" type="submit" value="Shout" class="btn btn-primary" />
				</div>
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
				
					// shout panel
					echo "<div class=\"panel panel-default\">";
					echo "<div class=\"panel-body\">";
					
					// check ownership for delete button
					if($row['user_id'] == $_SESSION['user_id'])
					{	
						echo "<a href=\"activity.php?action=remove&id={$row['shout_id']}\" class=\"pull-right\">Delete</a>";
					}
					
					// display name
					echo "<p><strong>{$row2['firstname']} {$row2['lastname']} writes:</strong></p>";
					
					// display shout
					echo "<p>{$row['shout']}</p>";
					
					// display date
					echo "<span style=\"color: #ccc\">{$row['formatted_date']}<span>";
					
					echo "</div>";
					echo "</div>";
				}
			?>

			
		</div>
	
	</body>
</html>