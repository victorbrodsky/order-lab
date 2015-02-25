<?php

$SearchFor ="oli2002";              	//What string do you want to find?
$SearchFor ="cap9083";
$SearchField="cn";	//"samaccountname";   		//In what Active Directory field do you want to search for the string?

 
  $LDAPHost = "cumcdcp02.a.wcmc-ad.net";        //Your LDAP server DNS Name or IP Address
  $dn = "CN=Users,DC=a,DC=wcmc-ad,DC=net";      //Put your Base DN here
  $LDAPUserDomain = "@a.wcmc-ad.net";  			//Needs the @, but not always the same as the LDAP server domain
  
  //$LDAPUser = "svc_aperio_spectrum";        			//A valid Active Directory login
  //$LDAPUserPassword = "Aperi0,123";
  
  //Try user credentials
  $LDAPUser = "svc_aperio_spectrum";        			
  $LDAPUserPassword = "Aperi0,123";
  $username = $LDAPUser.$LDAPUserDomain;
  
  $LDAPFieldsToFind = array("cn", "samaccountname", "mail");
  
  $cnx = ldap_connect($LDAPHost) or die("Could not connect to LDAP");
  
  
  ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
  ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
  
  
  $mechs = array("GSSAPI","GSS-SPNEGO","SPNEGO");
  
  foreach( $mechs as $mech ) {
	  $res = ldap_sasl_bind($cnx,NULL,$LDAPUserPassword,$mech,NULL,$username,NULL);
	  if( !$res ) {
		echo $mech." - could not bind to LDAP by SASL<br>";
	  } else {
		$rescount++;
		exit("!!!!!!!!!!!! Logged in with ".$mech);
	  }  
  }
  
  
  ldap_bind($cnx,$LDAPUser.$LDAPUserDomain,$LDAPUserPassword) or die("Could not bind to LDAP");
  
  error_reporting (E_ALL ^ E_NOTICE);   //Suppress some unnecessary messages
  
  $filter="($SearchField=$SearchFor)"; //Wildcard is * Remove it if you want an exact match
  //$filter="(ObjectClass=Person)";
  
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