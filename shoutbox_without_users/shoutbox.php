<!DOCTYPE html>
<html>

<head>
	<title>Shoutbox</title>
</head>

<body>

<?php

// suppress error notices
error_reporting(E_ALL & ~E_NOTICE);

// database credentials
$db_user = 'root';
$db_password = '';
$db_host = 'localhost';
$db_name = 'dw';

// connect to database
$db = mysqli_connect ($db_host, $db_user, $db_password, $db_name) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

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
		// Clean data
		$shout = mysqli_real_escape_string($db, $_POST['shout']);

		// Insert shout
		$sql = "INSERT INTO shouts (
					shout_id, 
					shout, 
					shout_date
				) VALUES (
					null, 
					'$shout', 
					NOW()
				)";
		mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
		
		// Display confirmation
		echo "<p>Your shout has been added</p>";
	
	} 
}
?>
	
<h2>Shoutbox</h2>

<!-- Shoutbox form -->
<form method="post" action="shouts.php">
	<textarea name="shout"></textarea>
	<?php echo $error['shout']; ?>
	<input name="submit" type="submit" value="Shout"  />
</form>

	
<?php

// select all shouts from the database
$sql = "SELECT 
			shout_id, 
			shout, 
			DATE_FORMAT(shout_date,'%M %d, %Y') AS formatted_date 
		FROM 
			shouts 
		ORDER BY 
			shout_date 
		DESC";
$result = mysqli_query($db, $sql) or die('Query failed: ' . mysqli_error($db));
while ($row = mysqli_fetch_assoc($result))
{
	// Display shout
	echo "<p>{$row['shout']}</p>";
	echo "<p>{$row['formatted_date']}</p>";
	echo "<hr />";
}

?>
	
</body>
</html>