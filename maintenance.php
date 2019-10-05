<h1>Maintenance Status</h1>

<?php
include("SoaringLogBase.php");

// name of this file - used in links
$thisFile = "index.php?maintenance";

// Initialize variable we'll be using from the database
$logbase = SoaringLogBase::GetInstance();
$database = $logbase->dbObj;
$tableName = $logbase->GetMaintTable(); 
$maintList = $logbase->GetMaint(true); 

// using the _REQUEST array allows input via HTTP POST or URL tags
$maintItem = $_REQUEST["maintItem"];
$startTime = strtotime($_REQUEST["startTime"]);
$endTime = strtotime($_REQUEST["endTime"]);
$logType = $_REQUEST["logType"];
$maintID = $_REQUEST["maintID"];
$modified = $_REQUEST["modified"];

echo $startTime;
echo "Maint ID:";
echo $maintID;
echo "Modified:";
echo $modified;

// update an existing record
if($maintID && !$modified) {
    $query = "UPDATE $tableName SET ";

    if($maintItem)
        $query .= "maintItem='$maintItem',";

    if($startTime)
        $query .= "startTime='$startTime',";

    if($endTime) 
        $query .= "endTime='$endTime',";

    if($logType) 
        $query .= "logType='$logType',";

    // trim any trailing commas off the string
    $query = rtrim($query, ",");

    $query .= " WHERE ID='$maintID';";

    if(!$result = $database->query($query)) {
        print("uh oh.... failed to update record :(");
        print $query;
    }

    // refresh the member list
    $memberList = $logbase->GetMembers(true);
}
else if($maintItem) {
    // Add a new maintenance item to the database
    $query = "INSERT INTO $tableName (ID, maintItem, startTime, endTime, logType) VALUES (NULL, '$maintItem', '$startTime', '$endTime', '$logType');";

    if(!$result = $database->query($query))
        print("uh oh.... query failed :( $result");

    // refresh the maint list
    $maintList = $logbase->GetMaint();
}

// print out the list of existing entries
$query = "SELECT * FROM $tableName ORDER BY ID";
if($result = $database->query($query)) {
    echo("<table id=\"maintLogTable\" >");
    echo("<tr class=\"Head\"><td>Maint Item</td><td>Start Date</td><td>End Date</td><td>Log Type</td><td></td></tr>\n");

    // skip the first row (it should be null)
    //$row = $result->fetch(PDO::FETCH_BOTH);
    while($row = $result->fetch(PDO::FETCH_BOTH)) {
        $editMe = 0;
        // do we need to modify this row?
        if($maintID == $row['ID'] && $modified) {
            $editMe = 1;
        }

        if($editMe) {
            echo("<tr id=\"row{$row['ID']}\" class=\"IncompleteEntry\"><form id=\"form{$row['ID']}\" name=\"loggingUpdate\" action=\"{$thisFile}\" method=\"POST\"> ");
            $entryComplete = false;
        }
        else {
            echo("<tr class=\"CompleteEntry\">");
            $entryComplete = true;
        }

        // Maint Item
        if($editMe) {
            echo("<td>");
            echo("<input type=\"text\" name=\"maintItem\" value=\"{$row['maintItem']}\" id=\"maintItem{$row['ID']}\"/>");
            echo "</td>";
        }
        else 
            echo("<td>{$row['maintItem']}</td>");

        // Start Date
        if($editMe) {
            echo("<td>");
            echo("<input type=\"text\" name=\"startTime\" value=\"{$row['startTime']}\" id=\"startTime{$row['ID']}\"/>");
            echo "</td>";
        }
        else {
            $formattedStartTime = date("F j, Y", $row['startTime']);
            echo("<td>{$formattedStartTime}</td>");
        }

        // End Date
        if($editMe) {
            echo("<td>");
            echo("<input type=\"text\" name=\"endTime\" value=\"{$row['endTime']}\" id=\"endTime{$row['ID']}\"/>");
            echo "</td>";
        }
        else {
            if ($row['endTime']) {
                $formattedStartTime = date("F j, Y", $row['endTime']);
                echo("<td>{$formattedStartTime}</td>");
            }
            else {
                echo("<td>Not yet complete</td>");
            }
        }

        // Log Type
        if($editMe) {
            echo("<td>");
            echo $logbase->PrintMaintLogTypes($row['logType']);
            echo "</td>";
        }
        else 
            echo("<td>{$row['logType']}</td>");



        // Submit button and hidden field containing the unique flight index
        if($editMe) {
            echo "<td><input type=\"hidden\" name=\"maintID\" value=\"{$row['ID']}\"/><input type=\"submit\" value=\"Update...\" /></form>";
        }
        else {
            echo "<td><center><button name=\"modify\" class=\"modify\" onClick=\"window.location.href='{$thisFile}&maintID={$row['ID']}&modified=1'; \" />";
            echo "</center></td></tr>\n";
        }

    }

    // Row for new entries...
    echo "<tr><form name=\"maintList\" action=\"{$thisFile}\" method=\"POST\">\n";
    echo "<td><input type=\"text\" name=\"maintItem\" /></td>\n";
    echo "<td><input type=\"text\" name=\"startDate\" /></td>\n";
    echo "<td><input type=\"text\" name=\"endDate\" /></td>\n";
    echo "<td>";
    echo $logbase->PrintMaintLogTypes();
    echo "</td>";
    echo "<td><input type=\"submit\" value=\"Add new...\" /></td>";
    echo "</form></tr>";
    echo("</table><br><br><br>");
}
else
    print("Failed to execute query!!!");

?>
