<?php
session_start();
//echo "start: <br>";

//print_r($_COOKIE);
//echo "<br><br>session:<br>";

if( isset($_SESSION["FullName"]) ) {
echo "Welcome!<br>";    
echo "You are user=".$_SESSION["FullName"]."<br>";
echo "Your email=".$_SESSION["E_Mail"]."<br>";
} else {
    echo "You are not logged in to Aperio<br>";
}
 

//exit();
//print_r($_SESSION);

if($_POST) {

//$ldap['user'] = "order\".$_POST['user'];
$ldap['user'] = $_POST['user'];

$ldap['pass'] = $_POST['pass'];

$ldap['host']   = 'a.wcmc-ad.net';

$ldap['port']   = 389;


//$AuthResult = ADB_Authenticate($ldap['user'], $ldap['pass']);

//echo "before";

$ldap['conn'] = ldap_connect( $ldap['host'], $ldap['port'] ) or die("Could not conenct to {$ldap['host']}" );

echo "after";

$ldap['bind'] = ldap_bind($ldap['conn'], $ldap['user'], $ldap['pass']);

 
if( !$ldap['bind'] )

{

echo ldap_error( $ldap['conn'] );

exit;

}

 

echo "<p>";

echo ($ldap['bind'])? "Valid Login": "Login Failed";

echo "</p><br />";

ldap_close( $ldap['conn'] );

}

else

{

echo '

<!DOCTYPE HTML PUBLIC "-//W3C/DTD HTML 4.0 Transitional//EN">

<html>

<head>

<title>LDAP Login Test</title>

</head>

<body>

<h1>  Login Here</h1>

<form method="POST" action="login.php">

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

';

}

?>