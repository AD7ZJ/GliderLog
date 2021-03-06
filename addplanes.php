<h1>Edit Planes</h1>

<?php
include("SoaringLogBase.php");

// name of this file - used in links
$thisFile = "index.php?addplanes";

session_start();
$loggedIn = False;
if ( isset( $_SESSION['loggedin'] ) ) {
    $loggedIn = True;
    print("<h2><a href=\"auth.php?logout=1&origin=addplanes\">Logout</a></h2>");
}
else {
    print("<h2><a href=\"auth.php?origin=addplanes\">Login</a></h2>");
}


// Initialize variable we'll be using from the database
$logbase = SoaringLogBase::GetInstance();
$database = $logbase->dbObj;
$tableName = $logbase->GetAircraftTable(); 
$aircraftList = $logbase->GetAircraft(true); 

// using the _REQUEST array allows input via HTTP POST or URL tags
$available = isset($_REQUEST["available"]) ? $_REQUEST["available"] : null;
$lastAnnualed = strtotime(isset($_REQUEST["lastAnnualed"]) ? $_REQUEST["lastAnnualed"] : null);
$aircraftID = isset($_REQUEST["aircraftID"]) ? $_REQUEST["aircraftID"] : null;
$aircraftName = isset($_REQUEST["aircraftName"]) ? $_REQUEST["aircraftName"] : null;
$modified = isset($_REQUEST["modified"]) ? $_REQUEST["modified"] : null;

if($loggedIn) {
    // update an existing record
    if($aircraftID && !$modified) {
        $query = "UPDATE $tableName SET ";
        if($lastAnnualed)
            $query .= "LastAnnualed='$lastAnnualed',";

        if($available) 
            $query .= "IsAvailable='$available',";
        else
            $query .= "IsAvailable=0";

        // trim any trailing commas off the string
        $query = rtrim($query, ",");

        $query .= " WHERE ID='$aircraftID';";

        if(!$result = $database->query($query)) {
            print("uh oh.... failed to update record :( ");
            print $query;
        }
    }
    else if($aircraftName) {
        // Add a new airplane to the database
        $query = "INSERT INTO $tableName (ID, Name, LastAnnualed, IsAvailable) VALUES (NULL, '$aircraftName', NULL, 'available');";

        if(!$result = $database->query($query))
            print("uh oh.... query failed :( $result");

        // refresh the member list
        $aircraftList = $logbase->GetAircraft(true);
    }
}

// print out the list of existing aircraft
$query = "SELECT * FROM $tableName;";
if($result = $database->query($query)) {
    echo("<table id=\"aircraftTable\" >");
    echo("<tr class=\"Head\"><td>Aircraft Name</td><td>Last Annualed</td><td>IsAvailable</td><td></td></tr>\n");

    // skip the first row (it should be null)
    $row = $result->fetch(PDO::FETCH_BOTH);
    while($row = $result->fetch(PDO::FETCH_BOTH)) {
        $editMe = 0;
        // do we need to modify this row?
        if($aircraftID == $row['ID'] && $modified) {
            $editMe = 1;
        }

        if($editMe) {
            echo("<tr id=\"row{$row['flightIndex']}\" class=\"IncompleteEntry\"><form id=\"form{$row['ID']}\" name=\"aircraftUpdate\" action=\"{$thisFile}\" method=\"POST\"> ");
            $entryComplete = false;
        }
        else {
            echo("<tr class=\"CompleteEntry\">");
            $entryComplete = true;
        }

        // Airplane
        echo("<td>{$aircraftList[$row['ID']]}</td>");

        // Last annual
        $storedLastAnnual = date("M j, Y", $row['LastAnnualed']);
        if($editMe) {
            echo("<td>");
            echo("<input type=\"text\" name=\"lastAnnualed\" value=\"$storedLastAnnual\" id=\"lastAnnual{$row['ID']}\"/>");
            echo "</td>";
        }
        else
            echo("<td>$storedLastAnnual</td>");

        // Inactive status
        $isAvailable = $row['IsAvailable'];
        if($editMe) {
            if($isAvailable)
                echo("<td><input type=\"checkbox\" checked=\"yes\" name=\"available\" value=\"available\"></td>");
            else
                echo("<td><input type=\"checkbox\" name=\"available\" value=\"available\"></td>");

        }
        else {
            if($isAvailable)
                echo("<td class=\"Inactive\">Available</td>");
            else
                echo("<td>Not Available</td>");

        }

        // Submit button and hidden field containing the unique flight index
        if($editMe) {
            echo "<td><input type=\"hidden\" name=\"aircraftID\" value=\"{$row['ID']}\"/><input type=\"submit\" value=\"Update...\" /></form>";
        }
        else {
            if($loggedIn) {
                echo "<td><center><button name=\"modify\" class=\"modify\" onClick=\"window.location.href='{$thisFile}&aircraftID={$row['ID']}&modified=1'; \" />";
                echo "</center></td></tr>\n";
            }
            else {
                echo "<td></td></tr>\n";
            }
        }

    }

    if($loggedIn) {
        // Row for new entries...
        echo "<tr><form name=\"aircraftList\" action=\"{$thisFile}\" method=\"POST\">\n";
        echo "<td><input type=\"text\" name=\"aircraftName\" /></td>\n";
        echo "<td><input type=\"text\" name=\"lastAnnualed\" /></td>\n";
        echo "<td>N/A</td>";
        echo "<td><input type=\"submit\" value=\"Add new...\" /></td>";
        echo "</form></tr>";
    }
    echo("</table><br><br><br>");
}
else
    print("Failed to execute query!!!");

?>
