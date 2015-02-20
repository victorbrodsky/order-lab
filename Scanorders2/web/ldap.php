<?php

//$ldap = ldap_connect("cumcdcp02.a.wcmc-ad.net");
$ldap = ldap_connect("a.wcmc-ad.net");

$username="svc_aperio_spectrum";
$password="Aperi0,123";

//$username="oli2002";
//$password="Cinava1210";

if( $bind = ldap_bind($ldap, $username,$password) ) {
	echo "logged in";
} else {
	echo "fail";
}

echo "<br/>done";

?>