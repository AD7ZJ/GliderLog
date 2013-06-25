<html>
<head>

<script LANGUAGE="JavaScript" type="text/javascript" src="clientScript.js"></script>
<script type="text/javascript" src="jsDatePick.min.1.3.js"></script>
<script type="text/javascript">
    window.onload = function(){
        new JsDatePick({
            useMode:2,
            target:"startDatePilot",
            dateFormat:"%d-%M-%Y"
        });
        new JsDatePick({
            useMode:2,
            target:"endDatePilot",
            dateFormat:"%d-%M-%Y"
        });

        new JsDatePick({
            useMode:2,
            target:"startDateA",
            dateFormat:"%d-%M-%Y"
        });
        new JsDatePick({
            useMode:2,
            target:"endDateA",
            dateFormat:"%d-%M-%Y"
        });
        new JsDatePick({
            useMode:2,
            target:"flyingDay",
            dateFormat:"%d-%M-%Y"
        });

    };
</script>

<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>Prescott Soaring Flight Log</title>
<link rel="stylesheet" type="text/css" href="style.css" />
<link rel="stylesheet" type="text/css" media="all" href="jsDatePick_ltr.min.css" />

</head>
    
<body>  
<div id="container">
    <div id="navbar">
        <ul>
        <li><a href="index.php">Logging Home</a></li>
        <li><a href="index.php?reports">Reports Page</a></li>
        <li><a href="index.php?addpilots">Add Pilots/Aircraft</a></li>
        <li><a href="http://prescottsoaring.com">Back to PSS Homepage</a></li>
        </ul>
    </div>

    <div id="main">
        <?php
            if(isset($_GET['reports'])) {
                // include page interests
                include('reports.php');
                // in all other cases include the home page
            } 
            else {
                include('home.php');
            }
        ?>
        <div class="spacer" />
    </div>
</div>
</body>
</html>
