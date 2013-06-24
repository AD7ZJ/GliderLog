<?php
    include("SoaringLogBase.php");

    $flightIndex = $_REQUEST["flightIndex"];
   
    $logbase = SoaringLogBase::GetInstance();

    if($logbase->DeleteEntry($flightIndex))
        echo "Success!";
    else        
        echo "Failed to delete... :-(";
?>
<html>
<head>
<title>Prescott Soaring Flight Log</title>
<meta http-equiv="refresh" content="1; URL='index.php'" />
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<p><a href="index.php">Redirecting to the logging page</a></p>

</body>
</html>
