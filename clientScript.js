function updateFlightTime() {
    var table = document.getElementById("flightLogTable");

    // run through the rows of the table
    for(var i = 1, row; row = table.rows[i]; i++) {
        // Skip rows that already have a landing time entered or that do not yet have a takeoff time
        // we're triggering on the <input> tag that will be present if the field is not filled out
        if(row.cells[5].innerHTML.match("input") && !row.cells[4].innerHTML.match("input")) {
            var startTime = row.cells[4].innerHTML;
            var startTimeArray = startTime.split(":");
            var startHrs = startTimeArray[0];
            var startDate = new Date();
            var rightNow = new Date();
            startDate.setHours(parseInt(startTimeArray[0], 10), parseInt(startTimeArray[1], 10), parseInt(startTimeArray[2], 10));
            var totalTimeSeconds = Math.round((rightNow - startDate) / 1000);
            
            // set the background color of the cell
            if(totalTimeSeconds > 3600)
                row.cells[6].style.backgroundColor = "FF0000";
            else if(totalTimeSeconds > 3000)
                row.cells[6].style.backgroundColor = "FFFF00";
            else
                row.cells[6].style.backgroundColor = "008080";
                
            var flightTimeHours = Math.floor(totalTimeSeconds / 3600);
            totalTimeSeconds -= flightTimeHours * 3600;
            var flightTimeMinutes = Math.floor(totalTimeSeconds / 60);
            totalTimeSeconds -= flightTimeMinutes * 60;

            // build elapsed flight time string
            row.cells[6].innerHTML = "";
            if(flightTimeHours > 0)
                row.cells[5].innerHTML += flightTimeHours + ":";
            row.cells[6].innerHTML += pad(flightTimeMinutes, 2) + ":" + pad(totalTimeSeconds, 2);
        }
    }
}

function pad(number, length) {
    var str = '' + number;
    while (str.length < length) {
        str = '0' + str;
    }
    return str;
}

function startTimer(flightIndex) {
    document.getElementById("takeoff"+flightIndex).value = 'now';
    document.getElementById("form"+flightIndex).submit();
}

function endTimer(flightIndex) {
    document.getElementById("landing"+flightIndex).value = 'now';
    document.getElementById("form"+flightIndex).submit();
}

// Run the update function every 500 ms
setInterval(function(){updateFlightTime()}, 500);
