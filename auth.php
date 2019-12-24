<?php
include("SoaringLogBase.php");

$logbase = SoaringLogBase::GetInstance();

session_start();

if (isset($_REQUEST['logout'])) {
    session_destroy();
    // Redirect to the login page:
    //print("Logged out");
    header('Location: index.php?maintenance');
}
else if ( isset($_REQUEST['username'], $_REQUEST['password']) ) {

    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];

    if (strcmp($logbase->GetAdminUser(), $username) == 0) {
        // Passwords must be hashed with password_hash() for this to work 
        print ($logbase->GetAdminPass());
        print ("   {$logbase->GetAdminUser()}   ");
        if (password_verify($password, $logbase->GetAdminPass())) {
            // create session
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $username;
            //echo 'Welcome ' . $_SESSION['name'] . '!';
            header('Location: index.php?maintenance');
        } else {
            print("Incorrect password!");
        }
    } else {
        echo 'Incorrect username!';
    }
}
?>
