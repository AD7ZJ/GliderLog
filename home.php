<?php
include("SoaringLogBase.php");
date_default_timezone_set('America/Phoenix');

//error_reporting(E_ALL);
//ini_set('display_errors', '1');

session_start();
$loggedIn = False;
if ( isset( $_SESSION['loggedin'] ) ) {
    $loggedIn = True;
    print("<h2>To edit a past day, enter the full date below. <a href=\"auth.php?logout=1&origin=home\">Logout</a></h2>");

    echo "<form name=\"setDate\" action=\"index.php\" method=\"POST\">";
    echo "Day: <input type=\"text\" name=\"day\" value=\"$day\"/>";
    echo "Month: <input type=\"text\" name=\"month\" value=\"$month\"/>";
    echo "Year: <input type=\"text\" name=\"year\" value=\"$year\"/>";
    echo "<input type=\"submit\" value=\"Modify flights for this day\" />";
    echo "</form>";
    echo "<br>";
}

// Initialize variable we'll be using from the database
$logbase = SoaringLogBase::GetInstance();
$database = $logbase->dbObj;
$tableName = $logbase->GetFlightLogTable(); 
$aircraftList = $logbase->GetAircraft(); 
$memberList = $logbase->GetMembers(); 
$instructorList = $logbase->GetInstructors(); 

// using the _REQUEST array allows input via HTTP POST or URL tags
$dateOverride = False;
if(isset($_REQUEST["day"]) && $loggedIn) {
    $day = $_REQUEST["day"];
    $dateOverride = True;
}
else
    $day = date("j");

if(isset($_REQUEST["month"]) && $loggedIn) {
    $month = $_REQUEST["month"];
    $dateOverride = True;
}
else
    $month = date("n");

if(isset($_REQUEST["year"]) && $loggedIn) {
    $year = $_REQUEST["year"];
    $dateOverride = True;
}
else
    $year = date("Y");

// Convert the various takeoff/landing time formats to a unix timestamp
$landing = null;
if (isset($_REQUEST["landing"])) {
    $landingTimeString = $_REQUEST["landing"];
    preg_match('/^\d+\:\d+$|^\d+:\d+:\d+$/', $landingTimeString, $matches);
    if(isset($matches[0]))
        $landing = strtotime($matches[0] . " $day-$month-$year");
    preg_match('/^now$/', $landingTimeString, $matches);
    if(isset($matches[0]))
        $landing = strtotime($matches[0]);
}

$takeoff = null;
if (isset($_REQUEST["takeoff"])) {
    $takeoffTimeString = $_REQUEST["takeoff"];
    preg_match('/^\d+\:\d+$|^\d+:\d+:\d+$/', $takeoffTimeString, $matches);
    if(isset($matches[0]))
        $takeoff = strtotime($matches[0] . " $day-$month-$year");
    preg_match('/^now$/', $takeoffTimeString, $matches);
    if(isset($matches[0]))
        $takeoff = strtotime($matches[0]);
}


$dayOfYear = date("z", $takeoff);
$towHeight = isset($_REQUEST["towHeight"]) ? $_REQUEST["towHeight"] : null;
$billTo = isset($_REQUEST["billTo"]) ? $_REQUEST["billTo"] : null;
$instructor = isset($_REQUEST["instructor"]) ? $_REQUEST["instructor"] : null;
$notes = isset($_REQUEST["notes"]) ? $_REQUEST["notes"] : null;
$aircraft = isset($_REQUEST["aircraft"]) ? $_REQUEST["aircraft"] : null;
$flightIndex = isset($_REQUEST["flightIndex"]) ? $_REQUEST["flightIndex"] : null;
$modified = isset($_REQUEST["modified"]) ? $_REQUEST["modified"] : null;
$token = isset($_REQUEST["token"]) ? $_REQUEST["token"] : null;

print "<center><h1>Flights for $month/$day/$year</h1></center>";

// force the time to assume PM if the hour is between 00:00 and 06:00
if($takeoff) {
    if(date("G", $takeoff) < 6)
        $takeoff += 43200;
}
if($landing) {
    if(date("G", $landing) < 6)
        $landing += 43200;
}

// update an existing record
if($flightIndex && !$modified) {
    // Get existing properties for this flight
    $query = "SELECT * FROM $tableName WHERE flightIndex='$flightIndex';";
    if($result = $database->query($query)) {
        $row = $result->fetch(PDO::FETCH_BOTH); 
    }

    // If token matches the one in the database
    if (strcmp($token, $row['token']) == 0)
    {
        // get the pilot's name for later use
        $pilotName = $row['billTo'];

        $query = "UPDATE $tableName SET ";

        // create new token
        $newToken = uniqid();
        $query .= "token='$newToken',";

        // aircraft set to 0 or 1 indicates null
        if($aircraft > 1)
            $query .= "aircraft='$aircraft',";

        if($takeoff) 
            $query .= "takeoffTime='$takeoff',";

        if($landing) {
            // update landing time only if landing time is after takeoff time
            if($row['takeoffTime'] < $landing)
                $query .= "landingTime='$landing',";
        }

        if($towHeight)
            $query .= "towHeight='$towHeight',";

        if($instructor) {
            if($instructor == 1) // 'none' was selected so clear it
                $instructor = 0;
            $query .= "instructor='$instructor',";
        }

        if($notes)
            $query .= "notes='$notes',";

        if($takeoff || $landing) {
            if($takeoff && $landing)
                $totalTime = $landing - $takeoff;
            else {
                if($row['takeoffTime'])
                    $totalTime = $landing - $row['takeoffTime'];
                else if ($row['landingTime'])
                    $totalTime = $row['landingTime'] - $takeoff;
                else
                    $totalTime = 0;
            }
            if ($totalTime > 0 && $totalTime < 86400) {
                $query .= "totalTime='$totalTime',"; 
            }
        }

        // trim any trailing commas off the string so far
        $query = rtrim($query, ",");

        $query .= " WHERE flightIndex='$flightIndex';";

        if(!$result = $database->query($query)) {
            print("uh oh.... failed to update record :( ");
            print $query;
        }

        // is this entry complete?
        if($logbase->EntryIsComplete($flightIndex)) {
            // Add the person to the bottom of the list
            AddEntry($pilotName);
        }
    }
    else {
        print("Someone else edited the log, page refreshed. Try again.");
    }
}
else if($billTo) {
    // Add a pilot to the list
    AddEntry($billTo);
}


$query = "SELECT * FROM $tableName WHERE day='$day' AND month='$month' AND year='$year';";
if($result = $database->query($query)) {
    echo("<table id=\"flightLogTable\" >");
    echo("<tr class=\"Head\"><td></td><td>Bill To</td><td>Instructor</td><td>Aircraft</td><td>Takeoff Time</td><td>Landing Time</td><td>Flight Time</td>");
    echo("<td></td></tr>\n");

    while($row = $result->fetch(PDO::FETCH_BOTH)) {
        $editMe = 0;
        // do we need to modify this row?
        if($flightIndex == $row['flightIndex'] && $modified) {
            $editMe = 1;
        }

        // is this an incomplete entry?
        if(!$row['aircraft'] || !$row['takeoffTime'] || !$row['landingTime'] || $editMe) {
            echo("<tr id=\"row{$row['flightIndex']}\" class=\"IncompleteEntry\">");
            $entryComplete = false;
        }
        else {
            echo("<tr class=\"CompleteEntry\">");
            $entryComplete = true;
        }

        echo "<td><center>";
        echo "<button name=\"modify\" class=\"modify\" onClick=\"window.location.href='index.php?flightIndex={$row['flightIndex']}&modified=1";
        if($dateOverride)
            echo "&day=$day&month=$month&year=$year";
        echo "'; \" />";
        echo "</center></td>\n";

        // start the update form if the entry is incomplete
        if (!$entryComplete) {
            echo "<form id=\"form{$row['flightIndex']}\" name=\"loggingUpdate\" action=\"index.php\" method=\"POST\">";

        }

        // Pilot
        echo("<td>{$memberList[$row['billTo']]}</td>");

        // instructor
        if(!$row['instructor'] && !$row['takeoffTime'] || $editMe) {
            echo("<td>");
            echo $logbase->PrintInstructors($row['instructor']);
            echo "</td>";
        }
        else if($row['instructor']) {
            echo("<td>{$instructorList[$row['instructor']]}</td>");
        }
        else {
            echo("<td></td>");
        }

        // Glider
        if($row['aircraft'] == 0 ||  $row['aircraft'] == 1 || $editMe) {
            echo "<td>";
            echo $logbase->PrintAircraft($row['aircraft']);
            echo "</td>";
        }
        else
            echo("<td>{$aircraftList[$row['aircraft']]}</td>");

        // Takeoff time
        if($row['takeoffTime'])
            $storedTakeoffTime = date("G:i:s", $row['takeoffTime']);

        if($row['takeoffTime'] && !$editMe)
            echo "<td>$storedTakeoffTime</td>";
        else {
            echo "<td><input type=\"text\" name=\"takeoff\" value=\"$storedTakeoffTime\" class=\"takeoffInput\" id=\"takeoff{$row['flightIndex']}\"/>";
            if(!$dateOverride)
                echo "<a href='#' onclick='startTimer({$row['flightIndex']});return false;'><img Title='Click to start the timer' src='img/clock.png' border='0'></a>";
            echo "</td>";
        }
        $storedTakeoffTime = "";

        // Landing time
        if($row['landingTime'])
            $storedLandingTime = date("G:i:s", $row['landingTime']);

        if($row['landingTime'] && !$editMe)
            echo "<td>$storedLandingTime</td>";
        else {
            echo "<td><input type=\"text\" name=\"landing\" value=\"$storedLandingTime\" class=\"landingInput\" id=\"landing{$row['flightIndex']}\" />";
            if(!$dateOverride)
                echo "<a href='#' onclick='endTimer({$row['flightIndex']});return false;'><img Title='Click to stop the timer' src='img/clock.png' border='0'></a>";
            echo "</td>";
        }
        $storedLandingTime = "";

        // Flight Time
        $flightMins = round($row['totalTime'] / 60);
        echo "<td>$flightMins Mins</td>";

        // Tow altitude
        /*if($row['towHeight'] && !$editMe)
            echo("<td>{$row['towHeight']}</td>");
        else
            echo "<td><input type=\"number\" name=\"towHeight\" value=\"{$row['towHeight']}\" class=\"towInput\" /></td>"; */

        // notes
        if(($row['notes'] && !$editMe) || $entryComplete) {
            //echo "<td style=\"width: 200px\">{$row['notes']}</td>";
        }
        else {
            //echo "<td><input type=\"text\" name=\"notes\" value=\"{$row['notes']}\"/></td>";
        }

        // Submit button and hidden field containing the unique flight index
        echo "<td>";
        if(!$entryComplete) {
            echo "<input type=\"hidden\" name=\"flightIndex\" value=\"{$row['flightIndex']}\"/><input type=\"hidden\" name=\"token\" value=\"{$row['token']}\"/>";
            if ($dateOverride) {
                echo "<input type=\"hidden\" name=\"day\" value=\"$day\"/>";
                echo "<input type=\"hidden\" name=\"month\" value=\"$month\"/>";
                echo "<input type=\"hidden\" name=\"year\" value=\"$year\"/>";
            }
            echo "<input type=\"submit\" name=\"updateBtn\" value=\"Update...\" /></form>";
        }

        echo "<button name=\"delete\" class=\"delete\" onClick=\"if(confirm('Are you sure you want to delete this entry for {$memberList[$row['billTo']]}?')) window.location.href='deleteEntry.php?flightIndex={$row['flightIndex']}'; \" />";

        echo "</td>";
        // end of the row
        echo "</tr>\n";

    }

    // Row for new entries...
    echo "</table><br><table id=\"addPilotForm\">";
    echo "<tr><form name=\"logging\" action=\"index.php\" method=\"POST\">\n";
    echo "<td></td><td>";
    echo $logbase->PrintPilots() . "</td>\n";
    if ($dateOverride) {
        echo "<input type=\"hidden\" name=\"day\" value=\"$day\"/>";
        echo "<input type=\"hidden\" name=\"month\" value=\"$month\"/>";
        echo "<input type=\"hidden\" name=\"year\" value=\"$year\"/>";
    }
    echo "<td><input type=\"submit\" value=\"<-- Add name to list\" /></td>";
    echo "<td colspan=\"4\"></td>";
    echo "</form></tr>";
    echo("</table><br><br><br>");
}
else
print("Failed to execute query!!!");


function AddEntry($billTo) {
    global $tableName, $day, $month, $year, $dayOfYear, $database;
    
    $newToken = uniqid();

    $query = "INSERT INTO $tableName (flightIndex,day,month,year,dayOfYear,aircraft,takeoffTime,landingTime,totalTime,towHeight,billTo,"
        . "instructor,notes,token) VALUES (NULL, '$day', '$month', '$year', '$dayOfYear', NULL, NULL, NULL, "
        . "NULL, NULL, '$billTo', NULL, NULL, '$newToken');";

    if(CheckDupes($year, $day, $month, $billTo)) {
        if(!$result = $database->query($query)) {
            print("uh oh.... query failed :( $result");
            print var_export($database->errorinfo());
        }
    }
}

function CheckDupes($year, $day, $month, $billTo) {
    global $tableName, $database;

    $query = "SELECT * FROM $tableName WHERE year='$year' AND day='$day' AND month='$month' AND billTo='$billTo';";

    if(!$result = $database->query($query))
        print("uh oh.... query failed :( $result");

    while($row = $result->fetch(PDO::FETCH_BOTH)) {
        if($row['takeoffTime'] && $row['landingTime']) {
            continue;
        }
        else
            return false;
    }

    return true;
}


?>

To begin the log for the day, only the pilot's name need be entered to begin with.  Rows will display with a red background until all data is complete.  When new data is entered, click the "Update..." button for the changes to take effect and be saved to the database.  The only real limitation is only a single row can be updated at a time, so don't go making a bunch of updates to 3 different people and then click update, as only the row whose update button was clicked will take effect.<br>
<br>
The format for entering times is very flexible - anything like "11:00", "1:00 PM", and "14:30" is acceptable.  "now" will automatically enter the current time in.  To delete an entry, click the red 'X'.  When a row is complete, the background will turn green.  Data is stored in a sQlite database, which offers the generic SQL interface for running reports on the data.  <br>
<br>
