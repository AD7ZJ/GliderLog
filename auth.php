<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Login</title>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
        <link href="loginstyle.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="login">
            <h1>Login</h1>
            <form action="auth.php" method="post">
                <label for="username">
                    <i class="fas fa-user"></i>
                </label>
                <input type="text" name="username" placeholder="Username" id="username" required>
                <label for="password">
                    <i class="fas fa-lock"></i>
                </label>
                <input type="password" name="password" placeholder="Password" id="password" required>
                <input type="hidden" name="origin" value="<?php echo $_REQUEST['origin'];?>">
                <input type="submit" value="Login">
            </form>
        </div>
    </body>
</html>

<?php
include("SoaringLogBase.php");

$logbase = SoaringLogBase::GetInstance();

session_start();

$pageToReturnTo = $_REQUEST['origin'];

if (isset($_REQUEST['logout'])) {
    session_destroy();
    header("Location: index.php?{$pageToReturnTo}");
}
else if ( isset($_REQUEST['username'], $_REQUEST['password']) ) {

    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];

    if (strcmp($logbase->GetAdminUser(), $username) == 0) {
        // Passwords must be hashed with password_hash() for this to work 
        if (password_verify($password, $logbase->GetAdminPass())) {
            // create session
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $username;
            header("Location: index.php?$pageToReturnTo");
        } else {
            print("Incorrect password!");
        }
    } else {
        echo 'Incorrect username!';
    }
}
?>
