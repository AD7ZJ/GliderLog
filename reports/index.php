<html>
<head>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Prescott Soaring Flight Reports</title>
<link rel="stylesheet" type="text/css" href="style.css" />
<link rel="stylesheet" type="text/css" media="all" href="jsDatePick_ltr.min.css" />
<script type="text/javascript" src="jsDatePick.min.1.3.js"></script>
<script type="text/javascript">
    window.onload = function(){
        new JsDatePick({
            useMode:2,
            target:"startDate",
            dateFormat:"%d-%M-%Y"
        });
        new JsDatePick({
            useMode:2,
            target:"endDate",
            dateFormat:"%d-%M-%Y"
        });


    };
</script>
</head>
<body>
	
<?php
/*
 * Reports page
 *
 */
include("../SoaringLogBase.php");

$logbase = SoaringLogBase::GetInstance();
$database = $logbase->dbObj;
$tableName = $logbase->GetFlightLogTable();
$aircraftList = $logbase->GetAircraft();
$memberList = $logbase->GetMembers();
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
$startDate = strtotime($_REQUEST["startDate"]); // Times are stored in seconds after the unix epoch
$endDate = strtotime($_REQUEST["endDate"]);


echo("Select Pilot's Name: ");
echo("<form action=\"index.php\" method=\"POST\"> ");
echo($logbase->PrintPilots());
echo("<input type=\"text\" size=\"12\" id=\"startDate\" name=\"startDate\" />");
echo("<input type=\"text\" size=\"12\" id=\"endDate\" name=\"endDate\" />");
echo("<input type=\"submit\" value=\"Go!\" /></form>");


if($billTo) {
    outputPilotTime();
}


function outputPilotTime() {
    global $database;
    global $tableName;
    global $billTo;
    global $aircraftList;
    global $memberList;
    global $instructorList;
    global $startDate;
    global $endDate;

    if($startDate) {
        $query = "SELECT * FROM $tableName WHERE billTo='$billTo' AND takeoffTime >= '$startDate' AND takeoffTime <= '$endDate';";
    }
    else 
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


?>

<br>
To Do Yet:<br>
<ul>
<li>Implement date range
<li>Source code can be seen <a href="index.phps">here</a>
</ul>  
</body>
</html>
