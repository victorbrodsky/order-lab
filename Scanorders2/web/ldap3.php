<?php

$username = 'oli2002';
$password = 'Cinava1210';
$account_suffix = '@a.wcmc-ad.net';

if( 1 ) {
	$username = 'cap9083';
	//$username = "nyh\cap9083";
	$password = 'Mesmer28';
	//$account_suffix = '@nyp';
	//$account_suffix = "";
}

$hostnameSSL = 'ldaps://cumcdcp02.a.wcmc-ad.net:636';
$hostnameTLS = 'cumcdcp02.a.wcmc-ad.net';
$portTLS = 389;

ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);

// Attempting fix from http://www.php.net/manual/en/ref.ldap.php#77553
putenv('LDAPTLS_REQCERT=never');

if( 1 ) {
####################
# SSL bind attempt #
####################
// Attempting syntax from http://www.php.net/manual/en/function.ldap-bind.php#101445
$con =  ldap_connect($hostnameSSL);
if (!is_resource($con)) trigger_error("Unable to connect to $hostnameSSL",E_USER_WARNING);

// Options from http://www.php.net/manual/en/ref.ldap.php#73191
if (!ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3))
{
    trigger_error("Failed to set LDAP Protocol version to 3, TLS not supported",E_USER_WARNING);
}
ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

if (ldap_bind($con,$username . $account_suffix, $password)) die('All went well using SSL');
ldap_close($con);
}

if( 1 ) {
####################
# TLS bind attempt #
####################
$con =  ldap_connect($hostnameTLS,$portTLS);
ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($con, LDAP_OPT_REFERRALS, 0);
$encrypted = (ldap_start_tls($con));
if ($encrypted) ldap_bind($con,$username . $account_suffix, $password); // Unecrypted works, but don't want logins sent in cleartext
ldap_close($con);
}

//GSSAPI
#####################
# SASL bind attempt #
#####################
$con =  ldap_connect($hostnameTLS,$portTLS);
ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($con, LDAP_OPT_REFERRALS, 0);
ldap_sasl_bind($con, NULL, $password, 'GSSAPI', NULL, $username. $account_suffix);
ldap_close($con);


?>