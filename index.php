<html>
<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Prescott Soaring Flight Log</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<center><h1>Flights for <?php echo date("F j, Y"); ?></h1></center>

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

    // hashes used to build option lists.  Eventually we should probably generate these from a database
    $aircraftList = array( "", "SGS 1-26", "SGS 2-33", "SGS 1-34", "Cirrus" );
    $memberList = array( "", "Max Denney", "Greg Berger", "Rod Clark", "Scott Boynton", "Elijah Brown", "Dana", "Fred" );

    // using the _REQUEST array allows input via HTTP POST or URL tags
    $day = date("j");
    $month = date("n");
    $year = date("Y");
    $dayOfYear = date("z");
    $takeoff = strtotime($_REQUEST["takeoff"]); // Times are stored in seconds after the unix epoch
    $landing = strtotime($_REQUEST["landing"]);
    $towHeight = $_REQUEST["towHeight"];
    $billTo = $_REQUEST["billTo"];
    $notes = $_REQUEST["notes"];
    $aircraft = $_REQUEST["aircraft"];
    $flightIndex = $_REQUEST["flightIndex"];
    
    // Calculate total flight time
    $totalTime = $landing - $takeoff;

    if(isset($flightIndex)) {
	// update an existing record

	// Get existing properties for this flight
	$query = "SELECT * FROM $tableName WHERE flightIndex='$flightIndex';";
    	if($result = $database->query($query, SQLITE_BOTH, $error)) {
            $row = $result->fetch(PDO::FETCH_BOTH); 
	}
	

	$query = "UPDATE $tableName SET ";
	if($aircraft)
	    $query .= "aircraft='$aircraft',";

	if($takeoff)
	    $query .= "takeoffTime='$takeoff',";

	if($landing) {
	    $query .= "landingTime='$landing',";
	    // update the flight time
	    $totalTime = $landing - $row['takeoffTime'];
	    $query .= "totalTime='$totalTime',";
	}

	if($towHeight)
	    $query .= "towHeight='$towHeight',";

	// trim any trailing commas off the string so far
	$query = rtrim($query, ",");
	
	$query .= " WHERE flightIndex='$flightIndex';";

	if(!$result = $database->query($query, SQLITE_BOTH, $error)) {
            print("uh oh.... failed to update record :( $error");
	}

    }
    else if(isset($billTo)) {
	// add a new entry
	if($takeoff && $landing) 
	    $totalTime = $landing - $takeoff;
	
        $query = "INSERT INTO $tableName (flightIndex,day,month,year,dayOfYear,aircraft,takeoffTime,landingTime,totalTime,towHeight,billTo) " 
                  . "VALUES (NULL, '$day', '$month', '$year', '$dayOfYear', '$aircraft', '$takeoff', '$landing', '$totalTime', '$towHeight', '$billTo');";

	// before executing the query, check for duplicates
	//FIXME:checkDuplicates($dayOfYear, $billTo, $takeoff, $landing);
        if($result = $database->query($query, SQLITE_BOTH, $error)) {
	    print("Executed INSERT...");
        }
    
        else
            print("uh oh.... :( $error $result");
    }


    $query = "SELECT * FROM $tableName WHERE dayOfYear='$dayOfYear';";
    if($result = $database->query($query, SQLITE_BOTH, $error)) {
	echo("<table border=\"1\">");
	echo("<tr><td>Pilot</td><td>Aircraft</td><td>Takeoff Time</td><td>Landing Time</td><td>Flight Time</td><td>Tow Height</td><td></td></tr>\n");

        while($row = $result->fetch(PDO::FETCH_BOTH)) {
	    if(!$row['aircraft'] || !$row['takeoffTime'] || !$row['landingTime'] || !$row['towHeight']) {
                echo("<tr bgcolor=\"#FF0000\"><form name=\"loggingUpdate\" action=\"index.php\" method=\"POST\"> ");
		$entryComplete = false;
	    }
	    else {
		echo("<tr bgcolor=\"#00FF00\">");
		$entryComplete = true;
	    }

	    // Pilot
            echo("<td>{$memberList[$row['billTo']]}</td>");

	    // Glider
	    if($row['aircraft'] == 0) {
		echo "<td>";
		echo listAircraft();
		echo "</td>";
	    }
	    else
                echo("<td>{$aircraftList[$row['aircraft']]}</td>");

	    // Takeoff time
	    if($row['takeoffTime'])
		echo "<td>" . date("G:i:s", $row['takeoffTime']) . "</td>";
	    else
		echo "<td><input type=\"text\" name=\"takeoff\" /></td>";

	    // Landing time
	    if($row['landingTime'])
	        echo "<td>" . date("G:i:s", $row['landingTime']) . "</td>";
	    else
		echo "<td><input type=\"text\" name=\"landing\" /></td>";

	    // Flight Time
	    $flightMins = round($row['totalTime'] / 60);
	    echo "<td>$flightMins Mins</td>";

	    // Tow altitude
	    if($row['towHeight'])
                echo("<td>{$row['towHeight']}</td>");
	    else
		echo "<td><input type=\"text\" name=\"towHeight\" /></td>";

	    // Submit button and hidden field containing the unique flight index
	    if(!$entryComplete) {
		echo "<td><input type=\"hidden\" name=\"flightIndex\" value=\"{$row['flightIndex']}\"/><input type=\"submit\" value=\"Update...\" />";
		echo "  <a href=deleteEntry.php?flightIndex={$row['flightIndex']}><img src=\"close_24.png\" /></a></td></form></tr>\n";
	    }
	    else
		echo "<td><center><a href=deleteEntry.php?flightIndex={$row['flightIndex']}><img src=\"close_24.png\" /></a></center></td></tr>\n";

        }

	// Row for new entries...
	echo "<tr><form name=\"logging\" action=\"index.php\" method=\"POST\">\n";
	echo "<td>";
	echo listPilots() . "</td>\n";
	echo "<td>";
	echo listAircraft() . "</td>\n";
	echo "<td><input type=\"text\" name=\"takeoff\" /></td>";
	echo "<td><input type=\"text\" name=\"landing\" /></td>";
	echo "<td>N/A</td>";
	echo "<td><input type=\"text\" name=\"towHeight\" /></td>";
	echo "<td><input type=\"submit\" value=\"Submit\" /></td>";
	echo "</form></tr>";
	echo("</table><br><br><br>");
    }
    else
	print("Failed to execute query!!!  Sucks to be you! $error");

/**
 * Builds an HTML option list of aircraft from the hash $aircraftList
 */
function listAircraft() {
//    <select name="aircraft">
//         <option value="0"></option>
//         <option value="1">SGS 1-26</option>
//         <option value="2">SGS 2-33</option>
//         <option value="3">SGS 1-34</option>
//         <option value="4">Cirrus</option>
//    </select>

    global $aircraftList;
    echo "<select name=\"aircraft\">\n";
    foreach($aircraftList as $i => $value) {
	echo "<option value=\"$i\">$value</option>\n";
    }
    echo "</select>";
}


/**
 * Builds an HTML option list of members from the hash $memberList
 */
function listPilots() {
    global $memberList;
    echo "<select name=\"billTo\">\n";   
    foreach($memberList as $i => $value) {
        echo "<option value=\"$i\">$value</option>\n";
    }
    echo "</select>";
}

?>

To begin the log for the day, only the pilot's name need be entered to begin with.  Rows will display with a red background until all data is complete.  When new data is entered, click the "Update..." button for the changes to take effect and be saved to the database.  The only real limitation is only a single row can be updated at a time, so don't go making a bunch of updates to 3 different people and then click update, as only the row whose update button was clicked will take effect.<br>
<br>
The format for entering times is very flexible - anything like "11:00", "1:00 PM", and "14:30" is acceptable.  "now" will automatically enter the current time in.  To delete an entry, click the red 'X'.  When a row is complete, the background will turn green.  Data is stored in a sQlite database, which offers the generic SQL interface for running reports on the data.  <br>
<br>
To Do Yet:<br>
<ul>
<li>Total Time not calculated correctly when row is initially entered. Times are stored internally in relation to the unix epoch, so a massively large negative value will initially be displayed.  
<li>Need to implement reports for billing, etc
<li>Source code can be seen <a href="index.phps">here</a>
<li>The sQlite database admin panel can be seen <a href="phpliteadmin.php">here</a>
</ul>  
</body>
</html>
