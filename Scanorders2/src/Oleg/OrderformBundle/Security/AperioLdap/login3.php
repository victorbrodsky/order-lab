<?php
    session_start();
    include_once '/DatabaseRoutines.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C/DTD HTML 4.0 Transitional//EN">

<html>

<head>

<title>Login Test</title>

</head>

<body>

<h1>  Login Here</h1>

<form method="POST" action="login3.php">

<p>

User Name:

<input type="text" name="user" >

<br />

Password:

<input type="password" name="pass" >

<br />

<input type="submit" name="submit" value="Submit">

</p>

</form>

</body>

</html>


<?php

if($_POST) {

    //$ldap['user'] = "order\".$_POST['user'];
    $LoginName = $_POST['user'];
    $Password = $_POST['pass'];
    $AuthResult = ADB_Authenticate($LoginName, $Password);
    
    if( isset($AuthResult['UserId']) ) {
        echo "<br>user name=".$LoginName."<br>";               
        //echo "user id=".$AuthResult['UserId']."<br>";
        //print_r($AuthResult);   
        header('Location: /order/scanorder/Scanorders2/web/app_dev.php/orderinfo/');
    } else {
        unset($_POST);
        echo "Login failed";
    }
    
}    
    
?>
