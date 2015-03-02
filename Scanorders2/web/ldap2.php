<?php

error_reporting (E_ALL);

//$SearchFor ="oli2002";              				//What string do you want to find?
$SearchFor ="cap9083";
$SearchField="cn";	//"samaccountname";   			//In what Active Directory field do you want to search for the string?

 
  //$LDAPHost = "ldap://cumcdcp02.a.wcmc-ad.net";
  $LDAPHost = "cumcdcp02.a.wcmc-ad.net";        				//Your LDAP server DNS Name or IP Address
  
  $dn = "CN=Users,DC=a,DC=wcmc-ad,DC=net";      	//Put your Base DN here
  //$dn = "DC=a,DC=wcmc-ad,DC=net"; 
  
  $LDAPUserDomain = "@a.wcmc-ad.net";  			//Needs the @, but not always the same as the LDAP server domain
  
  $LDAPUserAdmin = "svc_aperio_spectrum";        			//A valid Active Directory login
  $LDAPUserPasswordAdmin = "Aperi0,123";
  
  //Try user credentials
  if( 1 ) {
	$LDAPUser = "oli2002";   
	$LDAPUserPassword = "Cinava1210";
	$LDAPUserDomain = "@a.wcmc-ad.net";
	$username = $LDAPUser.$LDAPUserDomain;
  } else {
	$LDAPUser = "cap9083";   
	$LDAPUserPassword = "Mesmer28";
	$LDAPUserDomain = "";
	$LDAPUserDomain = "@nyh.org";
	$LDAPUserDomain = "@a.wcmc-ad.net";
	$username = $LDAPUser.$LDAPUserDomain;
	$username = "nyp\\" . $LDAPUser;
	//$username = 'CN=cap9083,CN=Users,DC=a,DC=wcmc-ad,DC=net';
	//$username = 'CN=cap9083,DC=nyh,DC=org';
  }
  
  ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
  
  putenv('LDAPTLS_REQCERT=never'); 
  
  $ldapport = 389;
  //$cnx = ldap_connect($LDAPHost,$ldapport) or die("Could not connect to LDAP");
  $cnx = ldap_connect($LDAPHost) or die("Could not connect to LDAP");
  
  //ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
  if (!ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3)) exit('Could not set version 3');
  
  //ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
  if (!ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0)) exit('Could not disable referrals');
  
  
  //http://marc.info/?l=php-windows&m=116127873321748&w=2
  //ldap_start_tls($cnx);// or die("Error to start tls"); 
  
  //ldap_bind($cnx,$LDAPUserAdmin,$LDAPUserPasswordAdmin) or die("Could not bind to LDAP Admin");
  //print "Admin is logged in: $LDAPUserAdmin <br><br>";

	//$res = ldap_bind($cnx,$username,$LDAPUserPassword);
	//if( !$res ) {
	//	echo "Could not bind to LDAP: user=".$username."<br>";
	//} else {	
	//	echo "OK simple LDAP: user=".$username."<br>";
	//}  
  
  //ldap_bind($cnx,$LDAPUser.$LDAPUserDomain,$LDAPUserPassword) or die("Could not bind to LDAP");
  //ldap_bind($cnx,$username,$LDAPUserPassword) or die("Could not bind to LDAP: username=".$username);
  //ldap_bind($cnx,'CN=cap9083,CN=Users,DC=nyp,DC=org',$LDAPUserPassword) or die("Could not bind to LDAP");
  //ldap_bind($cnx,'nyh\cap9083@a.wcmc-ad.net',$LDAPUserPassword) or die("Could not bind to LDAP"); //or DOMAIN\username
  //ldap_bind($cnx,'CN=cap9083,CN=Users,DC=a,DC=wcmc-ad,DC=net',$LDAPUserPassword) or die("Could not bind to LDAP");
  
	//ldap_bind($cnx,'nyh\cap9083',$LDAPUserPassword) or die("Could not bind to LDAP");
	//ldap_bind($cnx,'CN='.$LDAPUser.',CN=Users,DC=a,DC=wcmc-ad,DC=net',$LDAPUserPassword) or die("Could not bind to LDAP");  
  
  
  //TODO: http://php.net/manual/en/function.ldap-start-tls.php
  //ldap_start_tls($cnx) or die("Error to start tls");
  //$cnx = ldap_connect("ldap://cumcdcp02.a.wcmc-ad.net",$ldapport) or die("Could not connect to LDAP");
  //ldap_sasl_bind( $cnx, $username, 'mysecret', 'DIGEST-MD5', NULL, $LDAPUserPassword) or die("Could not bind to LDAP by SASL");
  //ldap_sasl_bind($cnx, NULL, $LDAPUserPassword, 'GSS-SPNEGO', NULL, $LDAPUser.$LDAPUserDomain) or die("Could not bind to LDAP by SASL");
  //ldap_sasl_bind($cnx,NULL,$LDAPUserPassword,'GSS-SPNEGO',NULL,'cap9083@nyh.org',NULL) or die("Could not bind to LDAP by SASL");
  //ldap_sasl_bind($cnx,NULL,$LDAPUserPassword,NULL,NULL,'cap9083@nyh.org',NULL);
  //ldap_sasl_bind($cnx, NULL, "", "EXTERNAL");
  //ldap_sasl_bind($cnx, 'CN='.$LDAPUser.',CN=Users,DC=a,DC=wcmc-ad,DC=net', $LDAPUserPassword, 'GSSAPI') or die("Could not bind to LDAP by SASL");
  
  //bool ldap_sasl_bind ( 
  //resource $link [, 
  //string $binddn = NULL [, 
  //string $password = NULL [, 
  //string $sasl_mech = NULL [, 
  //string $sasl_realm = NULL [, 
  //string $sasl_authc_id = NULL [, 
  //string $sasl_authz_id = NULL [, 
  //string $props = NULL ]]]]]]] 
  //)
  /*
  ldap_sasl_bind(
	$cnx,
	NULL,	//'CN=Users,DC=a,DC=wcmc-ad,DC=net',
	$LDAPUserPassword,
	NULL,//'GSS-SPNEGO',
	NULL,
	'CN='.$LDAPUser.',CN=Users,DC=a,DC=wcmc-ad,DC=net',	//$username,	//'nyh\cap9083',
	NULL
  ) or die("Could not bind to LDAP by SASL");
  */
  
  //putenv("KRB5CCNAME=" . $_SERVER['KRB5CCNAME']);
  //ldap_sasl_bind($cnx, NULL, NULL, NULL);
  
  $rescount = 0;
  
  
  //ldap_simple_bind_s($cnx,'cap9083@nyh.org',$LDAPUserPassword) or die("Could not bind to LDAP");
  //$res = ldsap_sasl_bind_s($cnx,NULL,$LDAPUserPassword,'EXTERNAL',NULL,$username,NULL);
  //if( !$res ) {
//	echo "Could not bind to LDAP by SASL EXTERNAL <br>";
  //} else {
//	$rescount++;
  //}  
  
  //echo "server GSSAPI=".$_SERVER['GSSAPI']."<br>";
  //print_r($_SERVER);
	//export only path file name (remove "file:")
	//$krb5cache = substr($_SERVER['GSSAPI'],5,strlen($_SERVER['GSSAPI']));
	//export it again
	//putenv("GSSAPI=$krb5cache");
	//exoprt it also for apache2
	//apache_setenv("GSSAPI",null);

	//ldap_sasl_interactive_bind_s();
	
  //$mechs = array('EXTERNAL','ANONYMOUS','PLAIN','OTP','CRAM-MD5','DIGEST-MD5',"SCRAM","NTLM","GSSAPI",'GSS-SPNEGO',"BROWSERID-AES128","EAP-AES128");
  $mechs = array("","ANONYMOUS","PLAIN","GSSAPI","GSS-SPNEGO","SPNEGO");
  
  foreach( $mechs as $mech ) {
	  $res = ldap_sasl_bind($cnx,NULL,$LDAPUserPassword,$mech,NULL,$username,NULL);
	  if( !$res ) {
		echo $mech." - could not bind to LDAP by SASL<br>";
	  } else {
		$rescount++;
		exit("!!!!!!!!!!!! Logged in with ".$mech);
	  }  
  }
   
  $res = ldap_bind($cnx,$username,$LDAPUserPassword);
  if( !$res ) {
	echo "<br>Could not bind to LDAP: user=".$username."<br>";
  } else {	
	echo "<br>OK simple LDAP: user=".$username."<br>";
  }  
   
  ldap_error($cnx);
    
  ldap_unbind($cnx);
  
  if( $rescount > 0 ) {
	print "User is logged in: $username <br><br>";
  } else {
	exit("<br> User login failed: $username <br><br>");
  }
  
  //error_reporting (E_ALL ^ E_NOTICE);   //Suppress some unnecessary messages
  
  $filter="($SearchField=$SearchFor)"; //Wildcard is * Remove it if you want an exact match
  //$filter="(ObjectClass=Person)";
  
  $LDAPFieldsToFind = array("cn", "samaccountname", "mail");
  
  $sr=ldap_search($cnx, $dn, $filter, $LDAPFieldsToFind);
  $info = ldap_get_entries($cnx, $sr);
 
  for ($x=0; $x<$info["count"]; $x++) {
    $sam=$info[$x]['samaccountname'][0];
    $email=$info[$x]['mail'][0];
    $nam=$info[$x]['cn'][0];
	 
    print "\nActive Directory says that:<br />";
    print "CN is: $nam <br />";
	print "Email is: $email <br />";
    print "samaccountname is: $sam <br />";   
	
	print_r($info[$x]);

  } 
  if (
$x==0) { print "Oops, $SearchField $SearchFor was not found. Please try again.\n"; }

?>