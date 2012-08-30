function testFunction() {
    var table = document.getElementById("flightLogTable");

    for(var i = 1, row; row = table.rows[i]; i++) {
        // run through the rows of the table
        var startTime = row.cells[2].innerHTML;
        var startTimeArray = startTime.split(":");
        var startHrs = startTimeArray[0];
        var startDate = new Date();
        var rightNow = new Date();
        startDate.setHours(parseInt(startTimeArray[0], 10), parseInt(startTimeArray[1], 10), parseInt(startTimeArray[2], 10));
        var totalTimeSeconds = Math.round((rightNow - startDate) / 1000);
        
        // set the background color of the cell
        if(totalTimeSeconds > 3600)
            row.cells[4].style.backgroundColor = "FF0000";
        else if(totalTimeSeconds > 3000)
            row.cells[4].style.backgroundColor = "FFFF00";
        else
            row.cells[4].style.backgroundColor = "008080";
            
        var flightTimeHours = Math.floor(totalTimeSeconds / 3600);
        totalTimeSeconds -= flightTimeHours * 3600;
        var flightTimeMinutes = Math.floor(totalTimeSeconds / 60);
        totalTimeSeconds -= flightTimeMinutes * 60;

        // build elapsed flight time string
        row.cells[4].innerHTML = "";
	if(flightTimeHours > 0)
            row.cells[4].innerHTML += flightTimeHours + ":";
        row.cells[4].innerHTML += pad(flightTimeMinutes, 2) + ":" + pad(totalTimeSeconds, 2);
    }
}

function pad(number, length) {
    var str = '' + number;
    while (str.length < length) {
        str = '0' + str;
    }
    return str;
}

setInterval(function(){testFunction()}, 500);
