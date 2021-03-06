<h1>Edit Pilots</h1>

<?php
include("SoaringLogBase.php");

// name of this file - used in links
$thisFile = "index.php?addpilots";

session_start();
$loggedIn = False;
if ( isset( $_SESSION['loggedin'] ) ) {
    $loggedIn = True;
    print("<h2><a href=\"auth.php?logout=1&origin=addpilots\">Logout</a></h2>");
}
else {
    print("<h2><a href=\"auth.php?origin=addpilots\">Login</a></h2>");
}


// Initialize variable we'll be using from the database
$logbase = SoaringLogBase::GetInstance();
$database = $logbase->dbObj;
$tableName = $logbase->GetPilotTable(); 
$memberList = $logbase->GetMembers(true); 

// using the _REQUEST array allows input via HTTP POST or URL tags
$inactive = isset($_REQUEST["inactive"]) ? $_REQUEST["inactive"] : null;
$lastBiannual = strtotime(isset($_REQUEST["lastBiannual"]) ? $_REQUEST["lastBiannual"] : null);
$pilotID = isset($_REQUEST["pilotID"]) ? $_REQUEST["pilotID"] : null;
$pilotName = isset($_REQUEST["pilotName"]) ? $_REQUEST["pilotName"] : null;
$modified = isset($_REQUEST["modified"]) ? $_REQUEST["modified"] : null;

if($loggedIn) {
    // update an existing record
    if($pilotID && !$modified) {
        $query = "UPDATE $tableName SET ";

        if($pilotName)
            $query .= "Name='$pilotName',";

        if($lastBiannual)
            $query .= "LastBiAnnual='$lastBiannual',";

        if($inactive) 
            $query .= "Inactive='$inactive',";
        else
            $query .= "Inactive=0";

        // trim any trailing commas off the string
        $query = rtrim($query, ",");

        $query .= " WHERE ID='$pilotID';";

        if(!$result = $database->query($query)) {
            print("uh oh.... failed to update record :(");
            print $query;
        }

        // refresh the member list
        $memberList = $logbase->GetMembers(true);
    }
    else if($pilotName) {
        // Add a new pilot to the database
        $query = "INSERT INTO $tableName (ID, Name, LastBiAnnual, Inactive) VALUES (NULL, '$pilotName', NULL, 0);";

        if(!$result = $database->query($query))
            print("uh oh.... query failed :( $result");

        // refresh the member list
        $memberList = $logbase->GetMembers(true);
    }
}

// print out the list of existing pilots
$query = "SELECT * FROM $tableName ORDER BY Inactive, ID";
if($result = $database->query($query)) {
    echo("<table id=\"flightLogTable\" >");
    echo("<tr class=\"Head\"><td>Member Name</td><td>Last Flew</td><td>Last Bi-annual</td><td>Inactive</td><td></td></tr>\n");

    // skip the first row (it should be null)
    $row = $result->fetch(PDO::FETCH_BOTH);
    while($row = $result->fetch(PDO::FETCH_BOTH)) {
        $editMe = 0;
        // do we need to modify this row?
        if($pilotID == $row['ID'] && $modified) {
            $editMe = 1;
        }

        if($editMe) {
            echo("<tr id=\"row{$row['flightIndex']}\" class=\"IncompleteEntry\"><form id=\"form{$row['flightIndex']}\" name=\"loggingUpdate\" action=\"{$thisFile}\" method=\"POST\"> ");
            $entryComplete = false;
        }
        else {
            if ($row['Inactive']) {
                echo("<tr class=\"InactivePilot\">");
            }
            else {
                echo("<tr class=\"CompleteEntry\">");
            }
            $entryComplete = true;
        }

        // Pilot
        if($editMe) {
            echo("<td>");
            echo("<input type=\"text\" name=\"pilotName\" value=\"{$memberList[$row['ID']]}\" id=\"pilotName{$row['ID']}\"/>");
            echo "</td>";
        }
        else 
            echo("<td>{$memberList[$row['ID']]}</td>");

        // Last flew
        $lastFlew = $logbase->GetLastFlew($row['ID']);
        if($lastFlew > 1)
            echo("<td>" . date("M j, Y", $lastFlew) . "</td>");
        else
            echo("<td>N/A</td>");

        // Last bi-annual
        $storedLastBiannual = date("M j, Y", $row['LastBiAnnual']);
        if($editMe) {
            echo("<td>");
            echo("<input type=\"text\" name=\"lastBiannual\" value=\"$storedLastBiannual\" id=\"lastBiannual{$row['ID']}\"/>");
            echo "</td>";
        }
        else
            echo("<td>$storedLastBiannual</td>");

        // Inactive status
        $isInactive = $row['Inactive'];
        if($editMe) {
            if($isInactive)
                echo("<td><input type=\"checkbox\" checked=\"yes\" name=\"inactive\" value=\"inactive\"></td>");
            else
                echo("<td><input type=\"checkbox\" name=\"inactive\" value=\"inactive\"></td>");

        }
        else {
            if($isInactive)
                echo("<td class=\"Inactive\">Inactive</td>");
            else
                echo("<td>Active</td>");

        }

        // Submit button and hidden field containing the unique flight index
        if($editMe) {
            echo "<td><input type=\"hidden\" name=\"pilotID\" value=\"{$row['ID']}\"/><input type=\"submit\" value=\"Update...\" /></form>";
        }
        else {
            if($loggedIn) {
                echo "<td><center><button name=\"modify\" class=\"modify\" onClick=\"window.location.href='{$thisFile}&pilotID={$row['ID']}&modified=1'; \" />";
                echo "</center></td></tr>\n";
            }
            else {
                echo "<td></td></tr>\n";
            }
        }

    }

    if($loggedIn) {
        // Row for new entries...
        echo "<tr><form name=\"pilotList\" action=\"{$thisFile}\" method=\"POST\">\n";
        echo "<td><input type=\"text\" name=\"pilotName\" /></td>\n";
        echo "<td>N/A</td>\n";
        echo "<td><input type=\"text\" name=\"lastBiannual\" /></td>\n";
        echo "<td>N/A</td>";
        echo "<td><input type=\"submit\" value=\"Add new...\" /></td>";
        echo "</form></tr>";
    }
    echo("</table><br><br><br>");
}
else
    print("Failed to execute query!!!");

?>
