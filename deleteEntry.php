<html>
<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Prescott Soaring Flight Log</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<?php
    try {
        //create or open the database
        $dir = 'sqlite:myDatabase.sqlite';
        $database = new PDO($dir) or die("cannot open the database");
        // this crap with the PHP Data Objects is necessary because of a bug in the way PHP interfaces with SQLite
    }
    catch(Exception $e) {
        die("Crap!! Couldn't open database :( $e");
    }

    // name of the table we'll be using
    $tableName = "flightLog";

    $flightIndex = $_REQUEST["flightIndex"];
    $areYouSure = $_REQUEST["sure"];

    if(!$areYouSure) {
	$query = "SELECT * FROM $tableName WHERE flightIndex='$flightIndex';";
    
	if($result = $database->query($query, SQLITE_BOTH, $error)) {
	    $row = $result->fetch(PDO::FETCH_BOTH);
	    echo "<h1>Delete entry for {$row['billTo']}?";
	    echo "<a href=\"deleteEntry.php?flightIndex=$flightIndex&sure=true\">Yes</a>\n";
	    echo "<a href=\"index.php\">No</a></h1>\n";

        }
	else
	    print("Failed to execute query!!! $error");
    }
    else {
	// delete selected entry
	$query = "DELETE FROM $tableName WHERE flightIndex='$flightIndex';";
	if($result = $database->query($query, SQLITE_BOTH, $error)) {
	   echo "<h1>Deleted entry.  <a href=\"index.php\">Return</a> to logging page</h1>";
	}
	else
            print("Failed to execute query!!! $error");
    }

?>

</body>
</html>
