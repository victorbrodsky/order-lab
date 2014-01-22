<? 
/**
* @package Login
*
* - vunger 080423	Authentication now always calls Disclaimer.php
* - vunger 080610	Authenticate now clears session data to ensure a clean session
* 					Now checks for license violations
*/

include_once '/DatabaseRoutines.php';
include_once '/Skeleton.php';

	$LoginName = trim(strtolower($_REQUEST['user']));
	$Password = trim($_REQUEST['password']);

	// InitializePage();	// Cannot call till we have authentication
	IniSettings();			//  but ensure socket timeouts are set

	if ($LoginName == 'guest')
	{
		$IniArray = my_parse_ini_file();
		$ClinicalGuest = (isset($IniArray['ClinicalGuest']) && $IniArray['ClinicalGuest'] == '1');
		if ((!$ClinicalGuest) && ($IniArray['InstallationType'] == 'Clinical'))
			$LoginName = '';	// No guest login in default clinical configuration
	}

	if (session_id() == '')
		session_start();

	$SaveSession = array();
	// Cache any needed information from the last SESSION
	if (isset($_SESSION['OverrideStartPage']))
		$SaveSession['OverrideStartPage'] = $_SESSION['OverrideStartPage'];

	// Destroy the last session (if any), create the new one
	$Id = strval(time()); // Set a unique (across tabs/browsers) session id
	while (true)
	{
		session_destroy();
		session_id($Id);
		session_start();
		// PHP will sometimes have a user log in under an existing session (a different user); prevent this
		if (empty($_SESSION))
			break;
		$Id++;
	}

	// Restore saved parameters
	foreach ($SaveSession as $Key => $Value)
		$_SESSION[$Key] = $Value;


	$AuthResult = ADB_Authenticate($LoginName, $Password);
	if ($AuthResult['ReturnCode'] != 0)
	{
		// authentication failed
		$Error = MapDataServerError($AuthResult['ReturnCode'], $AuthResult['ReturnText']);
		header("Location: /Login.php?error=$Error");
		exit();
	}
	$_SESSION['AuthToken'] = $AuthResult['Token_TODEL'];

	// If the user MUST change their password before proceeding
	if (isset ($AuthResult['UserMustChangePassword']) && $AuthResult['UserMustChangePassword'] == 'True')
	{
		$_SESSION['User']['Id'] = $AuthResult['UserId'];
		$_SESSION['User']['LoginName'] = $LoginName;
		$_SESSION['ErrorString'] = 'You must change your password before continuing.';
		header('Location: /ChangePassword.php');
		exit();
	}

	if (isset($_COOKIE['memory_limit']))
		ini_set('memory_limit', $_COOKIE['memory_limit']);

	// Error handling
	$_SESSION['ErrorType'] = NULL;
	// Log user off if an error occurs during the login process
	SetDefaultErrorPage('/Logoff.php');
	SetReturnPage('/Logoff.php');
	set_error_handler('SpectrumErrorHandler');

	$UsersTable = GetTableObj('Users');
	$User = $UsersTable->GetOneRecord($AuthResult['UserId']);
	// ??? Should reset StartPage to AllRecords.php if PAL user --especially if iPad users cannot login to Spectrum now
	if ((isset($User->StartPage) == false) || ($User->StartPage == '')) {
		//$User->StartPage = '/Welcome.php';
                $User->StartPage = '/order/scanorder/Scanorders2/web/app_dev.php/orderinfo/';
                echo "OK!!!!";
                //exit();
        }
	// Keep StartPage encoded during session so that it may be passed in URL
	$User->StartPage = urlencode($User->StartPage);
	$_SESSION['User'] = ObjectToArray($User);


	//
	// Set standard SESSION variables
	//

	// Limit masthead commands until login is completed
	$_SESSION['MastHead']['DisplayUserName'] = false;
	$_SESSION['MastHead']['DisplayNonAdminMenu'] = false;
	$_SESSION['MastHead']['DisplayAdminMenu'] = false;

	// Timezone offset (subtract this form all DateTime fields when displaying and saving)
	$_SESSION['TimezoneOffset'] = isset ($_REQUEST['TimezoneOffset']) ? $_REQUEST['TimezoneOffset'] : 0;


	// If the logged in user changed, get rid of the previous user's history
	if (isset ($_SESSION ['LoginName']) && $_SESSION ['LoginName'] != $LoginName)
	{
		unset ($_SESSION ['PageHistory']);
	}


	// Some code uses UserId instead of Id
	$_SESSION['User']['UserId'] = $User->Id;

	// XXX Code should migrate away from the following and use $_SESSION['User']
	$_SESSION['UserId'] = $User->Id;
	$_SESSION['LoginName'] = $User->LoginName;
	$_SESSION['FullName'] = $User->FullName;
	$_SESSION['Phone'] = $User->Phone;
	$_SESSION['E_Mail'] = $User->E_Mail;
   	$_SESSION['AcceptedTerms'] = $User->AcceptedTerms;
	$_SESSION['LastLoginTime'] = $User->LastLoginTime;
	$_SESSION['StartPage'] = $User->StartPage;
	$_SESSION['PasswordDaysLeft'] = $User->PasswordDaysLeft;
	$_SESSION['UserMustChangePassword'] = $User->UserMustChangePassword;
	$_SESSION['AutoView'] = $User->AutoView;
   	$_SESSION['ImageTransferNotificationEmail'] = $User->ImageTransferNotificationEmail;
	$_SESSION['DisableLicenseWarning'] = $User->DisableLicenseWarning;
	$_SESSION['ScanDataGroupId'] = $User->ScanDataGroupId;

	// Ensure the system is licensed (The DataServer ensures user is authenticated & does not have to change passwords)
	$Result = ADB_GetLicenses($_SESSION['AuthToken'], false);
	if ($Result['ASResult'] != 0)
	{
		if ($Result['ASResult'] != INVALID_LICENSE)
			trigger_error($_SESSION['DataServerError']);

		// System has not yet been licensed
		if (strcasecmp($LoginName, 'administrator') == 0)
		{
			header('Location: /SysAdmin.php');
		}
		else
		{
			header('Location: /order/Ldap//LogOff.php?error=System is unlicensed, please log in as administrator.');
		}
		exit();
	}

	// We now have a valid token, set the configuration for future pages.
	UpdateConfiguration();

	if (IsLicensed('SpectrumPlus') == false)
	{
		if (($LoginName != 'administrator') && ($LoginName != 'guest'))
		{
			header('Location: /order/Ldap/Login2.php?error=Invalid User');
			exit();
		}
	}

	//header('Location: /order/Ldap/Welcome.php');
        header('Location: /order/scanorder/Scanorders2/web/app_dev.php/orderinfo/');
?>
