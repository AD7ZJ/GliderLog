<h1>Edit Pilots</h1>

<?php
include("SoaringLogBase.php");

// name of this file - used in links
$thisFile = "index.php?addpilots";

// Initialize variable we'll be using from the database
$logbase = SoaringLogBase::GetInstance();
$database = $logbase->dbObj;
$tableName = $logbase->GetPilotTable(); 
$memberList = $logbase->GetMembers(true); 

// using the _REQUEST array allows input via HTTP POST or URL tags
$inactive = $_REQUEST["inactive"];
$lastBiannual = strtotime($_REQUEST["lastBiannual"]);
$pilotID = $_REQUEST["pilotID"];
$pilotName = $_REQUEST["pilotName"];
$modified = $_REQUEST["modified"];

// update an existing record
if($pilotID && !$modified) {
    $query = "UPDATE $tableName SET ";
    if($lastBiannual)
        $query .= "LastBiAnnual='$lastBiannual',";

    if($inactive) 
        $query .= "Inactive='$inactive',";
    else
        $query .= "Inactive=0";

    // trim any trailing commas off the string
    $query = rtrim($query, ",");

    $query .= " WHERE ID='$pilotID';";

    if(!$result = $database->query($query, SQLITE_BOTH, $error)) {
        print("uh oh.... failed to update record :( $error");
        print $query;
    }
}
else if($pilotName) {
    // Add a new pilot to the database
    print("Adding new pilot...");
}

// print out the list of existing pilots
$query = "SELECT * FROM $tableName;";
if($result = $database->query($query, SQLITE_BOTH, $error)) {
    echo("<table id=\"flightLogTable\" >");
    echo("<tr class=\"Head\"><td>Member Name</td><td>Last Flew</td><td>Last Bi-annual</td><td>Inactive</td><td></td></tr>\n");

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
            echo("<tr class=\"CompleteEntry\">");
            $entryComplete = true;
        }

        // Pilot
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
            echo "<td><center><button name=\"modify\" class=\"modify\" onClick=\"window.location.href='{$thisFile}&pilotID={$row['ID']}&modified=1'; \" />";
            echo "</center></td></tr>\n";
        }

    }

    // Row for new entries...
    echo "<tr><form name=\"pilotList\" action=\"{$thisFile}\" method=\"POST\">\n";
    echo "<td><input type=\"text\" name=\"pilotName\" /></td>\n";
    echo "<td>N/A</td>\n";
    echo "<td><input type=\"text\" name=\"lastBiannual\" /></td>\n";
    echo "<td>N/A</td>";
    echo "<td><input type=\"submit\" value=\"Add new...\" /></td>";
    echo "</form></tr>";
    echo("</table><br><br><br>");
}
else
    print("Failed to execute query!!!  Sucks to be you! $error");

?>
