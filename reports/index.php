<script LANGUAGE="JavaScript" type="text/javascript" src="clientScript.js"></script>
<html>
<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Prescott Soaring Flight Reports</title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>


<?php
    try {
        //create or open the database
        $dir = 'sqlite:../myDatabase.sqlite';
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
    $instructorList = array( "", "None", "A.C. Goodwin" );

    // using the _REQUEST array allows input via HTTP POST or URL tags
    $day = date("j");
    $month = date("n");
    $year = date("Y");
    $dayOfYear = date("z");
    $billTo = $_REQUEST["billTo"];
    $instructor = $_REQUEST["instructor"];
    $aircraft = $_REQUEST["aircraft"];
    $flightIndex = $_REQUEST["flightIndex"];


    echo("Select Pilot's Name: ");
    echo("<form action=\"index.php\" method=\"POST\"> ");
    echo(listPilots());
    echo("<input type=\"submit\" value=\"Go!\" /></form>");


    if($billTo) {
	$query = "SELECT * FROM $tableName WHERE billTo='$billTo';";
	if($result = $database->query($query, SQLITE_BOTH, $error)) {
	    echo("<table id=\"flightLogTable\" border=\"1\">");
	    echo("<tr><td>Bill To</td><td>Instructor</td><td>Aircraft</td><td>Takeoff Time</td><td>Landing Time</td><td>Flight Time</td>");
	    echo("<td>Tow Height</td><td>Notes</td></tr>\n");

	    $currentDOY = 0;
	    $totalTime = 0; // flight time in seconds
            while($row = $result->fetch(PDO::FETCH_BOTH)) {
		// don't print if there's no takeoff time
		if($row['takeoffTime']) {
		if(date("z", $row['takeoffTime']) ==  $currentDOY) {
	    	    echo("<tr>");
		}
		else {
		    echo("<tr bgcolor=\"#6496FF\"><td colspan=\"8\">");
		    echo date("F j, Y", $row['takeoffTime']);
		    echo("</td></tr><tr>");
		}
	    	echo("<td>{$memberList[$row['billTo']]}</td>");
	    	echo("<td>{$instructorList[$row['instructor']]}</td>");
	    	echo("<td>{$aircraftList[$row['aircraft']]}</td>");
	    	if($row['takeoffTime'])
		    $storedTakeoffTime = date("G:i:s", $row['takeoffTime']);
		else
		    $storedTakeoffTime = "None Available";
	    	echo("<td>$storedTakeoffTime</td>");
	
	    	if($row['landingTime']) {
		    $totalTime += $row['landingTime'] - $row['takeoffTime'];
		    $flightMins = round(($row['landingTime'] - $row['takeoffTime']) / 60);
		    $storedLandingTime = date("G:i:s", $row['landingTime']);
		}
	    	else
		    $storedLandingTime = "None Available";
	    	echo("<td>$storedLandingTime</td>");

	    	echo("<td>$flightMins Mins</td>");
	    	echo("<td>{$row['towHeight']}</td>");
	    	echo("<td style=\"width: 200px\">{$row['notes']}</td>");

	    	$storedLandingTime = "";
	    	$storedTakeoffTime = "";
		// update the current day of year
		$currentDOY = date("z", $row['takeoffTime']);

	    	echo "</tr>";
		}
            }
	    echo "</table>";
	    echo "<br><b>Total flight time for selected period: " . round($totalTime / 3600, 1) . "</b>";
    	}
    	else
	    print("Failed to execute query: $query  Sucks to be you! $error");

    }

/**
 * Builds an HTML option list of aircraft from the hash $aircraftList
 */
function listAircraft($selected = 0) {
    global $aircraftList;
    echo "<select name=\"aircraft\">\n";
    foreach($aircraftList as $i => $value) {
	echo "<option value=\"$i\" ";
	if($i == $selected)
	    echo "selected=\"selected\"";
	echo ">$value</option>\n";
    }
    echo "</select>";
}


/**
 * Builds an HTML option list of members from the hash $memberList
 */
function listPilots($selected = 0) {
    global $memberList;
    echo "<select name=\"billTo\">\n";   
    foreach($memberList as $i => $value) {
        echo "<option value=\"$i\" ";
	if($i == $selected)
	    echo "selected=\"selected\"";
	echo ">$value</option>\n";
    }
    echo "</select>";
}

function listInstructors($selected = 0) {
    global $instructorList;
    echo "<select name=\"instructor\">\n";
    foreach($instructorList as $i => $value) {
        echo "<option value=\"$i\" ";
	if($i == $selected)
            echo "selected=\"selected\"";
	echo ">$value</option>\n";
    }
    echo "</select>";
}

?>

<br>
To Do Yet:<br>
<ul>
<li>Implement date range
<li>Source code can be seen <a href="index.phps">here</a>
</ul>  
</body>
</html>
