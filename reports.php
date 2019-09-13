<?php
/*
 * Reports page
 *
 */
include("SoaringLogBase.php");

$logbase = SoaringLogBase::GetInstance();
$database = $logbase->dbObj;
$tableName = $logbase->GetFlightLogTable();
$aircraftList = $logbase->GetAircraft();
$memberList = $logbase->GetMembers(true);
$instructorList = $logbase->GetInstructors();


// using the _REQUEST array allows input via HTTP POST or URL tags
$day = date("j");
$month = date("n");
$year = date("Y");
$dayOfYear = date("z");
$billTo = $_REQUEST["billTo"];
$instructor = $_REQUEST["instructor"];
$aircraft = $_REQUEST["aircraft"];
$flightIndex = $_REQUEST["flightIndex"];
// only one of these can be set at a time
$startDate = strtotime($_REQUEST["startDateP"]); // Times are stored in seconds after the unix epoch
$endDate = strtotime($_REQUEST["endDateP"]);
$flyingDay = strtotime($_REQUEST["flyingDay"]);


if(!$startDate)
    $startDate = strtotime($_REQUEST["startDateA"]); // Times are stored in seconds after the unix epoch
if(!$endDate)
    $endDate = strtotime($_REQUEST["endDateA"]);

$startDateRange = strtotime($_REQUEST["startDateRange"]);
$endDateRange = strtotime($_REQUEST["endDateRange"]);

/************ Display flights by pilot ***********/
echo("<form action=\"index.php?reports\" method=\"POST\">");
echo("<table><tr>");
echo("<td class=\"Heading\" colspan=\"4\">Flight times by pilot<br><br></td>");
echo("</tr><tr>");
echo("<td>Select Pilot's Name:</td>");
echo("<td>Start date</td>");
echo("<td>End date</td>");
echo("<td></td>");
echo("</tr><tr>");
echo("<td>");
if($billTo)
    echo($logbase->PrintPilots($billTo));
else
    echo($logbase->PrintPilots());
echo("</td>");
echo("<td><input type=\"text\" size=\"12\" id=\"startDatePilot\" name=\"startDateP\" /></td>");
echo("<td><input type=\"text\" size=\"12\" id=\"endDatePilot\" name=\"endDateP\" /></td>");
echo("<td><input type=\"submit\" value=\"Go!\" /></td>");
echo("</tr></table>");
echo("</form>");

/************ Display flights by aircraft ***********/
echo("<form action=\"index.php?reports\" method=\"POST\"> ");
echo("<table>");
echo("<tr>");
echo("<td class=\"Heading\" colspan=\"4\">Flight times by aircraft<br><br></td>");
echo("</tr><tr>");
echo("<td>Select Aircraft:</td>");
echo("<td>Start date</td>");
echo("<td>End date</td>");
echo("<td></td>");
echo("</tr><tr>");
echo("<td>");
if($aircraft)
    echo($logbase->PrintAircraft($aircraft));
else
    echo($logbase->PrintAircraft());
echo("</td>");
echo("<td><input type=\"text\" size=\"12\" id=\"startDateA\" name=\"startDateA\" /></td>");
echo("<td><input type=\"text\" size=\"12\" id=\"endDateA\" name=\"endDateA\" /></td>");
echo("<td><input type=\"submit\" value=\"Go!\" /></td>");
echo("</tr></table>");
echo("</form>");

/************ Display flights by a single day ***********/
echo("<form action=\"index.php?reports\" method=\"POST\"> ");
echo("<table>");
echo("<tr>");
echo("<td class=\"Heading\" colspan=\"2\">Flights by day<br><br></td>");
echo("</tr><tr>");
echo("<td>Select flying day:</td>");
echo("</tr><tr>");
echo("<td><input type=\"text\" size=\"12\" id=\"flyingDay\" name=\"flyingDay\" /></td>");
echo("<td><input type=\"submit\" value=\"Go!\" /></td>");
echo("</tr></table>");
echo("</form>");

/************ Display flights by a range of days ***********/
echo("<form action=\"index.php?reports\" method=\"POST\"> ");
echo("<table>");
echo("<tr>");
echo("<td class=\"Heading\" colspan=\"3\">Flight Billing<br><br></td>");
echo("</tr><tr>");
echo("<td>Start date</td><td>End Date</td>");
echo("</tr><tr>");
echo("<td><input type=\"text\" size=\"12\" id=\"startDateRange\" name=\"startDateRange\" /></td>");
echo("<td><input type=\"text\" size=\"12\" id=\"endDateRange\" name=\"endDateRange\" /></td>");
echo("<td><input type=\"submit\" value=\"Go!\" /></td>");
echo("</tr></table>");
echo("</form>");


/************ Display flights by day ***********/


if($billTo) {
    if($startDate) {
        $query = "SELECT * FROM $tableName WHERE billTo='$billTo' AND takeoffTime >= '$startDate' AND takeoffTime <= '$endDate';";
    }
    else
        $query = "SELECT * FROM $tableName WHERE billTo='$billTo';";

    OutputQueryResults($query);
}

if($aircraft) {
    if($startDate) {
        $query = "SELECT * FROM $tableName WHERE aircraft='$aircraft' AND takeoffTime >= '$startDate' AND takeoffTime <= '$endDate';";
    }
    else
        $query = "SELECT * FROM $tableName WHERE aircraft='$aircraft';";

    OutputQueryResults($query);
}

if($flyingDay) {
    $endTime = $flyingDay + 86400;
    $query = "SELECT * FROM $tableName WHERE takeoffTime >= '$flyingDay' AND takeoffTime < '$endTime';";
    OutputQueryResults($query);
}

if($startDateRange) {
    if($endDateRange)
        $endDateRange = $endDateRange + 86400;
//    $query = "SELECT * FROM $tableName WHERE takeoffTime >= '$startDateRange' AND takeoffTime < '$endDateRange' ;";
//    OutputQueryResults($query);
    $query = "SELECT * FROM $tableName WHERE takeoffTime >= '$startDateRange' AND takeoffTime < '$endDateRange' ORDER BY billTo;";
    OutputBilling($query);
}


function OutputBilling($query = "") {
    global $database;
    global $aircraftList;
    global $memberList;
    global $instructorList;
    global $billTo;

	if($result = $database->query($query)) {
	    echo("<table id=\"flightLogTable\" border=\"1\">");
	    echo("<tr class=\"Head\">");
        echo("<td >Bill To</td>");
        echo("<td >Date</td>");
        echo("<td >Aircraft</td>");
        echo("<td >Flight Time</td>");
        echo("<td >Total</td>");
        echo("</tr>\n");

	    $currentMember = 0;
	    $totalTime = 0; // flight time in seconds
        $flightCount = 0;
        while($row = $result->fetch(PDO::FETCH_BOTH)) {
            // don't print if there's no takeoff time
            if($row['takeoffTime']) {
                if($row['billTo'] ==  $currentMember) {
                    echo("<tr class=\"Data\">");
                }
                else {
                    if($flightCount) {
                        echo "<tr class=\"SubHighlight\"><td></td><td></td><td></td><td></td><td>Total Flights: $flightCount</td></tr>";
                    }
                    $flightCount = 0;
                    echo("<tr class=\"Highlight\"><td colspan=\"5\">");
                    echo $memberList[$row['billTo']];
                    echo("</td></tr><tr class=\"Data\">");
                }

                $flightCount++;

                echo("<td></td>");
                $storedTakeoffTime = date("F j, Y", $row['takeoffTime']);
                echo("<td>$storedTakeoffTime</td>");
                echo("<td>{$aircraftList[$row['aircraft']]}</td>");

                if($row['landingTime']) {
                    $flightMins = round(($row['landingTime'] - $row['takeoffTime']) / 60);
                }

                echo("<td>$flightMins Mins</td>");
                echo("<td></td>");
                $currentMember = $row['billTo'];
                echo "</tr>";
            }
        }
        if($flightCount) {
            echo "<tr class=\"SubHighlight\"><td></td><td></td><td></td><td></td><td>Total Flights: $flightCount</td></tr>";
        }

	    echo "</table>";
	}
    else
	    print("Failed to execute query: $query  Sucks to be you! $error");

}

function OutputQueryResults($query = "") {
    global $database;
    global $aircraftList;
    global $memberList;
    global $instructorList;
    global $billTo;

	if($result = $database->query($query)) {
	    echo("<table id=\"flightLogTable\" border=\"1\">");
	    echo("<tr class=\"Head\">");
        echo("<td >Bill To</td>");
        echo("<td >Instructor</td>");
        echo("<td >Aircraft</td>");
        echo("<td >Takeoff Time</td>");
        echo("<td >Landing Time</td>");
        echo("<td >Flight Time</td>");
	    echo("<td >Tow Height</td>");
        echo("<td >Notes</td>");
        echo("</tr>\n");

	    $currentDOY = 0;
	    $totalTime = 0; // flight time in seconds
        $flightCount = 0;
        while($row = $result->fetch(PDO::FETCH_BOTH)) {
            // don't print if there's no takeoff time
            if($row['takeoffTime']) {
                $flightCount = $flightCount + 1;
                if(date("z", $row['takeoffTime']) ==  $currentDOY) {
                    echo("<tr class=\"Data\">");
                }
                else {
                    echo("<tr class=\"Highlight\"><td colspan=\"8\">");
                    echo date("F j, Y", $row['takeoffTime']);
                    echo("</td></tr><tr class=\"Data\">");
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
                //echo("<td>{$row['flightIndex']}</td>");
                echo("<td style=\"width: 200px\">{$row['notes']}</td>");

                $storedLandingTime = "";
                $storedTakeoffTime = "";
                $flightMins = 0;
                // update the current day of year
                $currentDOY = date("z", $row['takeoffTime']);

                    echo "</tr>";
            }
        }
	    echo "</table>";
    	echo "<br><b>Total flight time for {$memberList[$billTo]} over the displayed period: " . round($totalTime / 3600, 1) . " hours.";
        echo "<br>$flightCount flights </b>";	
	
	
	}
    else
	    print("Failed to execute query: $query  Sucks to be you! $error");
}


?>
