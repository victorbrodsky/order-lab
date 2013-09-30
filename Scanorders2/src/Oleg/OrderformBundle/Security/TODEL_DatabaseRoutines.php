<?
/**
* DatabaseRoutines.php contains all routines used to access DataServer
* 
* @package Database
*
* - vunger	3/28/08	Added classes cFilter, cFilterField.
* 					Added method ReportDatabaseError
*					Added ADB_PrepareForAudit(), ADB_GetAuditTrail(), ADB_GetDate()
* - msmaga 4/8/08	Electronic Signature "ESIG" handling (Hierarchy StatusField-related)
* - vunger 080418	Added ADB_GetTable()
* - vunger 080425	Mapped some features in ADB_GetFeatures() for processing by IsConfigured().
* - vunger 080425	Added ADB_SetRoleForSession().
* - vunger 080501	Create cSortField class for ADB_GetTable().
* - msmaga 080507	Added Dependent-Records Error catching
* - msmaga 080513	Added AddUserDefinedField()
* - msmaga 080528	Added ADB_AssignScoreConfigToImage(), ADB_ListSsFieldConfigs(), ADB_ListScoreActions(), ADB_GetScoreConfig(), ADB_ValidateFormula()
* - vunger 080602	Added ADB_ListUserCounts()
* - msmaga 080610	Added ADB_ListCannedCommentsForImage() and ADB_GetAction()
* - msmaga 080618	Added SsAction data to ADB_GetScoreConfigResult()
* - msmaga 080619	Changed ADB_ListCannedComments() to accept $SsConfigId and/or $CommentType
* - msmaga 080624	Added ADB_GetCommentsFromSSData()
* - vunger 080626	Added ADB_GetDataHierarchy()
* - vunger 080711	Added ADB_GetDataHierarchyTypes()
* - msmaga 080821	Removed URI's outside of GetSOAPclient constructors for SOAP-compliant '#'method handling and PHP 5.2 update
* - msmaga 080825	DEBUGMODE defined for controlling development aids
* - msmaga 080827	SsFieldConfig 'ColumnName' column renamed 'FieldName'
* - thoare 080828	DEBUGMODE turned off by default
* - msmaga 080904	'UDF' fields renamed 'Column'
* - vunger 080909	Added ADB_PutScoreConfig(), ADB_DeleteScoreConfig()
* - msmaga 091809	Added ADB_EsigRequired()
* - vunger 081029	ReportDataServerError() now returns a NULL in case a client needs the call returned (AJAX methods)
* 					Conveted most trigger_error() calls to ReportDataServerError()
* - vunger 081030	Added special caching for php ini setting overrides
* - rellis 081105	Correct ADB_UpdateDataGroup to call ReportDataServerError only if there is an error 
* - ksalig 081113	Remove priority setting when adding new job
* - vunger 081117	Added ADB_GetDatabaseInfo()
* - vunger 081209	Added ADB_GetUserConfig() & ADB_SetUserConfig()
* - vunger 090527	Added cDatabaseReader
* - rellis 100319	Added functions for accessing externally authenticated users
* - rellis 100602	Added GetAuthTypes, use ExternalLoginName when referencing external users
* - vunger	100513	Removed ADB_ESigRequired()
* - rellis 100706	Add $DataHierarchyId parameter to ADB_SetRoleForSession
* - rellis 100723	Modify ADB_GetReportTemplates to order by Description Ascending
* - rellis 101015	Redisplay the ESig window when status is ESIG_NOT_VALID
* - rellis 101105	Modify ADB_ListUsers to sort by LoginName
* - rellis 110525	Add ADB_RecordExists function
* - rellis 110915	Replace ADB_GetImageFileReference with the more generic ADB_GetRecordRefCount,
* 					add ADB_GetCurrentUserRoles
* 					remove ADB_PutFilesByIds which is no longer used
* - rellis 111207	add ADB_GetWorkflowUsers and ADB_GetWorkflowUserGroups
* -	pkraft	120120	Mapped login failure message from DataServer to user-friendly message
* - leonid 120224   ADB_GetEffectiveDataGroups - exclude child datagrousp (case 16150)
* - rellis 120514	Add ADB_GetESigHistory
* - rellis 120530	Add DeleteFiles parameter to DeleteRecordData call. Hard coded value of '1' means delete files
* - rellis 120614	Modified ADB_GetLicenses to handle an empty <Licenses> node in the response. Occurs on clean install of Spectrum
*
*/
include_once '/cExtendedSoapClient.php';
include_once '/Skeleton.php';
include_once '/cTable.php';
include_once '/cAnnotation.php';
include_once '/Tables/cDataGroups.php';
include_once '/cDatabaseReader.php';

//	Constants representing anticipated DataServer errors:
define ('GENERIC_ERROR', '-1');				// Error in SESSION['DataServerError']
define ('FIELD_ERRORS', '-2');				// Error(s) set in FieldError array (see cDDBWriter)
define ('INVALID_LICENSE', '-3004');
define ('IMAGE_NOT_FOUND', '-4031');		// Image not found/does not exist
define ('INVALID_TOKEN', '-7002');			// Session has expired
define ('INVALID_LOGIN', '-7003');			// Password is incorrect
define ('LOGON_FAILED', '-7004');           // UserName is incorrect
define ('NO_ASSIGNABLE_ROLES', '-7013');
define ('NOT_AUTHORIZED', '-7020');			// User is not authorized for this operation
define ('INVALID_ARGUMENT', '-4036');		// Returned when operation is failed
define ('INVALID_PASSWORD', '-7023');
define ('DESCRIPTION_EXISTS', '-7026');		// Description duplicates another (SsConfig) record
define ('DEPENDENT_RECORDS', '-7027');		// Cannot delete: other records are dependent on this
define ('ESIG', '-7029');					// Electronic Signature Validation Required
define ('ESIG_NOT_VALID', '-7030');			// Electronic Signature not valid
define ('ESIG2', '-7030');					// XXX For compatibility
define ('EXCEEDED_IMAGES', '-7031');		// The number of images have been exceeded
define ('INVALID_SCORE_FORMULA', '-7032');	// Invalid (c-sharp) Score Formula
define ('EXCEEDED_RUNS', '-7033');			// The number of algorithm runs have been exceeded
define ('NOT_DATAGROUP_OWNER', '-7039');	// Cannot delete: not data group owner (secondslide)   
define ('REASON_FOR_CHANGE_REQUIRED', '-7042');
define ('ESIG_NO_FULLNAME', '-7043');		// ESignature requires a user to have a full name
define ('USER_MUST_HAVE_ONE_ROLE', '-7044');// User must have at least one visible role
define ('DATA_ACCESS_VIOLATION', '-7057');	// Datagroup permissions prohibit access
define ('ROLE_ACCESS_VIOLATION', '-7058');	// Role permissions prohibit access
define ('COPY_OUTGOING_CONSULT', '-7051');	// Role permissions prohibit access

// 	minimum column length that shoule be aliased for GetFilteredRecordList calls
define ('ALIASCOLUMNLENGTH', 64);

/**
* Caches results from GetFilteredRecordList and GetRecordData to prevent many excessive calls to DataServer
* 
* Format: $RecordDataCache [$TableName][$Id][$ColumnName] = $Value
*/
$RecordDataCache = array ();


class cFilter
{
	public	$TableName;
	public	$ColumnName;
	public	$Operator;
	public	$Value;
	public	$ValueDesc;	// useful for mapping a database Id to a string

	// ctor
	public function __construct ($TableName, $ColumnName, $Operator, $Value)
	{
		$this->TableName = $TableName;
		$this->ColumnName = $ColumnName;
		$this->Operator = $Operator;

		$Value = ($Operator == 'LIKE' || $Operator == 'NOTLIKE')  ? '%'.EscapeSQLchars($Value).'%' : $Value;
		// convert '-1 day', '-1 week', and '-1 month' to usable values:
		date_default_timezone_set('UTC');
		if ($Value == '-1 day')
			$Value = date ('Y-m-d', time() - (24*60*60));

		elseif ($Value == '-1 week')
			$Value = date ('Y-m-d', time() - (7*24*60*60));

		elseif ($Value == '-1 month')
			$Value =  date("Y-m-d", time() - (31*24*60*60));

		$this->Value = $Value;
		$this->ValueDesc = $Value;	// overridable
	}
}



/**
 * Wrapper class for both the sort column and the sort order for that column
 */
class cSortField
{
	/**
	 * The Sort Field,  SORT BY in SQL
	 * @var string 
	 */
	private	$SortField ='Id';
	/**
	 * The Order Field,  ORDER BY in SQL
	 * @var string 
	 */
	private $SortOrder = 'Ascending';
	/**
	 * CTOR
	 */
	public function __construct ($SortField, $SortOrder = 'Ascending')
	{
		$this->SortField = $SortField;
		$this->SortOrder = $SortOrder;
	}
	/**
	 * Return the sort field;
	 */
	public function GetSortField()
	{
	   return $this->SortField;
}
	/**
	 * Return the sort order
	 */
	public function GetSortOrder()
{
	   return $this->SortOrder;
	}
}


function ClearDBErrors()
{
	$_SESSION['DataServerErrorCode'] = 0;	
	$_SESSION['DataServerError'] = 0;
}

function CheckDBResult($res, $DoThrow=true)
{
	if (is_array($res))
	{
		$Result = 0;
		$Message = '';
		foreach ($res as $Tag => $Obj)
		{
			if ($Result != 0)
				break;

			if ($Tag == 'ValidationResults')
			{
				// ValidationResults are only returned when ValidateOnly is set
				if (isset($Obj->RequireReasonForChange) && ($Obj->RequireReasonForChange == 1))
				{
					$Result = REASON_FOR_CHANGE_REQUIRED;
					$Message = 'Reason for change required';
				}
				else if (isset($Obj->RequireESig) && ($Obj->RequireESig == 1))
				{
					$Result = ESIG;
					$Message = 'ESignature required';
				}
			}
			elseif (isset($Obj->ASResult))
			{
				$Result = $Obj->ASResult;
				$Message = $Obj->ASMessage;
			}
		}
	}
	elseif (is_object($res))
	{
		$Result = $res->ASResult;
		$Message = $res->ASMessage;
	}
	else
	{
		$Result = -1;
		$Message = 'Invalid DataServer response';
	}

	if ($Result == 0)
		return 0;

	// Translations
	if ($Result == ESIG_NOT_VALID)
	{
		// Note: ESig requirement can be disabled with roles: Compliance:Disable E-Signature
		$Message = 'ESignature is invalid';
		// redisplay the ESig window
		$Result = ESIG;
	}
	else
	{
		$Message = MapDataServerError($Result, $Message);
	}

	$_SESSION['DataServerErrorCode'] = $Result;	
	$_SESSION['DataServerError'] = $Message;	

	if ($DoThrow)
	{
		trigger_error($Message);
		// NOTE: trigger_error's error handler may choose to return
	}

	return $Result;
}

// Deprecated - use CheckDBResult()
function ReportDataServerError($res, $DoThrow=true)
{
	if (is_object($res))
	{
		if ($res->ASResult != 0)
		{
			$_SESSION['DataServerErrorCode'] = $res->ASResult;	
			$_SESSION['DataServerError'] = $res->ASMessage;	
			if ($DoThrow)
				trigger_error("DataServer Error: $res->ASResult: $res->ASMessage");
		}
	}
	else
	{
		$_SESSION['DataServerErrorCode'] = -1;
		$_SESSION['DataServerError'] = 'invalid response';
		if ($DoThrow)
			trigger_error('DataServer Error, invalid response');
	}

	if ($DoThrow)
	{
		// trigger_error's error handler may choose to return, ensure an error code is passed back.
		// This is especially true with AJAX induced methods.
		return NULL;
	}
}


function MapDataServerError($Code, $Message)
{
	switch($Code)
	{
	case ESIG:
		return 'ESignature required';
	case ESIG_NO_FULLNAME:
		return 'Note: You cannot sign this because your Spectrum account user does not have a Full Name - contact your system administrator';
	case DEPENDENT_RECORDS:
		return 'Record dependency';
	case NOT_AUTHORIZED:
		return 'Not authorized to perform operation';
	case DATA_ACCESS_VIOLATION:
		return 'DataGroup lacks permission to perform operation';
	case ROLE_ACCESS_VIOLATION:
		return 'Role lacks permission to perform operation';
	case COPY_OUTGOING_CONSULT:
		return 'Error copying prior cases - you do not have Read Permissions to these cases. Please contact your site administrator to enable these permissions.';
	case NO_ASSIGNABLE_ROLES:
		return "There are currently no assignable roles for this user.  Please contact your site administrator to enable a role for this user.";;
	case NOT_DATAGROUP_OWNER:
		return 'Only owner can delete';
	// Following errors the message is sufficient (exclude 'DataServer Error)
	case USER_MUST_HAVE_ONE_ROLE:
		return $Message;
	// DataServer returns too much detail, so make it simple
	case INVALID_LOGIN: 
	case LOGON_FAILED:
		return "Specified Username or Password is not correct.";
	case INVALID_PASSWORD || INVALID_ARGUMENT:
		return TidyUpDataServerErrorMessage($Message);
	}

	return "DataServer Error: $Code: $Message";
}

/**
* Remove leading and trailing log messages from DataServer returned error message
* Example 1 Input String: Failed to execute method DataServer.SecurityProxy2.Logon: Specified UserName or Password is not correct. Check DataServer.log file for more details
* Example 1 Return String: Specified UserName or Password is not correct.
*
* Example 2 Input String: Failed to execute method DataServer.SecurityProxy2.ChangeUserPassword: Password does not meet requirements: Password cannot contain spaces. Check DataServer.log file for more details
* Example 2 Return String: Password does not meet requirements: Password cannot contain spaces.
*
* @param string $dsErrorString		String containing DataServer log message
*
* @return string $output		Interior error message
*/
function TidyUpDataServerErrorMessage($dsErrorString = null)
{ 
	if (isset($dsErrorString) == false)
		return '';

	$CheckDSLogMsg = 'Check DataServer.log file for more details';
	$ErrString = str_replace($CheckDSLogMsg, '', $dsErrorString);
	$reg_ex = "#(Failed)(.*?)(: )#e";
	$output = trim(preg_replace($reg_ex, '', $ErrString));
	if (strlen($output) == 0)
	{
		// We've stripped the entire message - just return the original
		return $dsErrorString;
	}
	return $output;
}

// MakeArray - helper function which accepts a thing and returns an array
//   	if thing is already an array it returns thing.  If thing is not an
//		array it creates a new array and puts thing in it.
function MakeArray($Thing)
{		
	$ThingArray = array();
	
	if (isset($Thing))
	{
		if (is_array($Thing))
			$ThingArray = $Thing;
		else
			$ThingArray[] = $Thing;
	}
		
	return $ThingArray;
}


//------------------------------------------------------------------
// ADB_Authenticate - Verify that the login/password exist in the db
//	if a match is found, then return the authentication token and 
//	store the user status in the 
//	
//------------------------------------------------------------------
function ADB_Authenticate($UserName, $Password)
{
	$client = GetSOAPSecurityClient ();

	// since this is usually the first call to dataserver put it in a try/catch block
	// because it might fail if dataserver is not accessible.  If an exception occurs
	// we can display a decent error message.
	try
	{
		$res = $client->__soapCall(	'Logon',																	//SOAP Method Name
									array('soap_version'=>SOAP_1_2,new SoapParam($UserName, 'UserName'), 		//Parameters
									new SoapParam($Password, 'PassWord')));	
	}
	catch (Exception $e) 
	{
		$DataServerURL = GetDataServerURL();
		trigger_error("Spectrum SOAP Error:  Unable to communicate with DataServer at $DataServerURL", E_USER_ERROR);
	}


	if (is_array($res) && ($res['LogonResult']->ASResult == 0))
	{
		$ReturnArray['ReturnCode'] = 0;
		$ReturnArray['Token'] = $res['Token'];
		$ReturnArray['UserId'] = $res['UserData']->UserId;
		$ReturnArray['UserMustChangePassword'] = $res['UserData']->UserMustChangePassword;
	}
	elseif (is_object($res))
	{
		$ReturnArray['ReturnCode'] = $res->ASResult;
		$ReturnArray['ReturnText'] = $res->ASMessage;
	}
	else
	{
		$ReturnArray = array('ReturnCode'=>'-1','ReturnText'=>'');
	}

	return $ReturnArray;

}

//------------------------------------------------------------------
// ADB_Logoff - Disconnect and invalidate the existing Auth Token
//------------------------------------------------------------------
function ADB_Logoff()
{
	$client = GetSOAPSecurityClient ();
	$res = $client->__soapCall('Logoff', array(new SoapParam($_SESSION['AuthToken'], 'Token')));
}

//------------------------------------------------------------------
// ADB_IsValidToken - Validate an Auth Token
//	if $Renew is true, then the valid token is renewed
//------------------------------------------------------------------
function ADB_IsValidToken($DoNotRenewToken = false)
{
	$client = GetSOAPSecurityClient ();
	
	$res = $client->__soapCall('IsValidToken',
								array(new SoapParam($_SESSION['AuthToken'], 'Token'),
									  new SoapParam($DoNotRenewToken ? '1' : '0', 'DoNotRenewToken')));

	if(is_array($res))
	{
		if($res['IsValidTokenResult']->ASResult == 0)
		{
			if ($res['Valid'] == 'True')
				return true;
			else
				return false;
		}			
	}
	return false;
}


//------------------------------------------------------------------
// ADB_IsValidSignature - Verifies that the given password matches with
//	the current AuthToken
//------------------------------------------------------------------
function ADB_IsValidSignature($Password)
{
	$client = GetSOAPSecurityClient ();

	$res = $client->__soapCall(	'IsValidSignature',
								array(new SoapParam($_SESSION['AuthToken'], 'Token'),
								new SoapParam($Password, 'Password')));	 	  

	if(is_array($res))
	{
		if($res['IsValidSignatureResult']->ASResult == 0)
		{
			if ($res['Valid'] == 'True')
				return true;
			else
				return false;
		}			
	}

	return false;
}


//------------------------------------------------------------------
// Add a new user to the User table
// ret:	New User Id (or) result object (if error)
//------------------------------------------------------------------
function ADB_AddNewUser ($FullName, $PhoneNumber, $Email, $LoginName, $Password,
		$UserMustChangePassword, $StartPage, $DisableLicenseWarning, $ExpireDate, $ImageTransferNotificationEmail = 1, $AuthType = 'Spectrum', $ExternalId='', $ExternalLoginName = '', $ViewingMode, $DisableAutoSlideFlip = 0, $DisableWorkflowEmailNotification = 0 )
{
	$client = GetSOAPSecurityClient ();

	// create the UserData XML
	$dom = new DOMDocument();
	$ndUserData = $dom->CreateElement('UserData');
	$ndUserData->appendChild($dom->CreateElement('LoginName', xmlencode($LoginName)));
	$ndUserData->appendChild($dom->CreateElement('ExternalLoginName', xmlencode($ExternalLoginName)));
	$ndUserData->appendChild($dom->CreateElement('FullName', xmlencode($FullName)));
	$ndUserData->appendChild($dom->CreateElement('Phone', xmlencode($PhoneNumber)));
	$ndUserData->appendChild($dom->CreateElement('E_Mail', xmlencode($Email)));
	$ndUserData->appendChild($dom->CreateElement('Password', xmlencode($Password)));
	$ndUserData->appendChild($dom->CreateElement('StartPage', xmlencode($StartPage)));     
	$ndUserData->appendChild($dom->CreateElement('ExpireDate', xmlencode($ExpireDate)));
	$ndUserData->appendChild($dom->CreateElement('DisableLicenseWarning', $DisableLicenseWarning ? '1' : '0'));
	$ndUserData->appendChild($dom->createElement('AuthType', xmlencode($AuthType)));
	$ndUserData->appendChild($dom->CreateElement('ImageTransferNotificationEmail', $ImageTransferNotificationEmail ? '1' : '0'));
	$ndUserData->appendChild($dom->createElement('ExternalId', xmlencode($ExternalId)));
	$ndUserData->appendChild($dom->CreateElement('ViewingMode', ($ViewingMode ? '1' : '0')));
	$ndUserData->appendChild($dom->CreateElement('DisableAutoSlideFlip', ($DisableAutoSlideFlip ? '1' : '0')));
	$ndUserData->appendChild($dom->CreateElement('DisableWorkflowEmailNotification', ($DisableWorkflowEmailNotification ? '1' : '0')));

	$ndPrivileges = $dom->CreateElement('Privileges');
	$ndUserData->appendChild($ndPrivileges);
	$ExternalUser = $AuthType != 'Spectrum';
	if ($ExternalUser) {
		$UserMustChangePassword = false;
		$ViewingMode = false;
	}
	$ndAccountStatus = $dom->CreateElement('AccountStatus');
	$ndAccountStatus->appendChild($dom->CreateElement('LockOnInvalidPass',$ExternalUser ? 'False' : 'True'));
	$ndAccountStatus->appendChild($dom->CreateElement('LockIfUnused',$ExternalUser ? 'False' : 'True'));
	$ndAccountStatus->appendChild($dom->CreateElement('PasswordCanExpire',$ExternalUser ? 'False' : 'True'));
	$ndAccountStatus->appendChild($dom->CreateElement('UserMustChangePassword', ($UserMustChangePassword ? 'True' : 'False')));
	$ndUserData->appendChild($ndAccountStatus);

	$UserDataXML = $dom->saveXML($ndUserData);

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapVar ($UserDataXML, 147);

	$res = $client->__soapCall ('AddUser', $ParamsArray,
								NULL, NULL, $OutputHeaders, true);	// Don't make multiple attempts

	if ((is_array($res)) && ($res['AddUserResult']->ASResult == 0))
	{
		return $res['UserId'];
	}
	else if (is_object($res))
	{
		return $res;
	}
	return ReportDataServerError($res);
}

//------------------------------------------------------------------
// ADB_DeleteUser - Remove user from user table 
//------------------------------------------------------------------
function ADB_DeleteUser ($UserId)
{
	$client = GetSOAPSecurityClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($UserId, 'UserId');
	
	$res = $client->__soapCall ('DeleteUser',		//SOAP Method Name
								$ParamsArray);		//Parameters

	if($res->ASResult != 0)
	{
		trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		return ReportDataServerError($res);
	}
}


//------------------------------------------------------------------
// ADB_UpdateUser - Update name, login, password, and admin status of an existing user.
//------------------------------------------------------------------
function ADB_UpdateUser($UserId, $UserData = array (), $UserPrivileges = array (), $UserStatus = array (), $ThrowError = true)
{
	$client = GetSOAPSecurityClient ();

	// create the UserData XML
	$dom = new DOMDocument ();
	$ndUserData = $dom->CreateElement ('UserData');
	$ndUserData->appendChild ($dom->CreateElement ('UserId', xmlencode($UserId)));

	foreach ($UserData as $Name => $Value)
	{
		if (is_array ($Value))
		{
			$ndSub = $dom->createElement ($Name);

			foreach ($Value as $SubName => $SubValue)
			{
				$ndSub->appendChild($dom->CreateElement($SubName, xmlencode($SubValue)));
			}

			$ndUserData->appendChild($ndSub);
		}
		else
		{
			$ndUserData->appendChild($dom->CreateElement($Name, xmlencode($Value)));
		}
	}

	$UserDataXML = $dom->saveXML($ndUserData);

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapVar($UserDataXML, 147);

	$res = $client->__soapCall('UpdateUser', $ParamsArray);

	return CheckDBResult($res, $ThrowError);
}

//------------------------------------------------------------------
// ADB_ChangeUserPassword - Alters a single user's passwords
//------------------------------------------------------------------
function ADB_ChangeUserPassword ($UserId, $NewPassword)
{
	$client = GetSOAPSecurityClient ();

	// create the UserData XML
	$dom = new DOMDocument();
	$ndUserData = $dom->CreateElement('UserData');
	$ndUserData->appendChild($dom->CreateElement('UserId', xmlencode($UserId)));
	$ndUserData->appendChild($dom->CreateElement('Password', xmlencode($NewPassword)));
	$UserDataXML = $dom->saveXML($ndUserData);
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapVar($UserDataXML, 147);
	
	$res = $client->__soapCall('ChangeUserPassword', $ParamsArray);

	return CheckDBResult($res);
}

/**
* Returns a list of users
* 
* @return array		- List of users in the DB
*/
function ADB_ListUsers()
{
	static $Cache = null;

	if (!$Cache)
	{
		$client = GetSOAPSecurityClient ();

		$res = $client->__soapCall('ListUsers', GetAuthVars());

		$UserList = array ();
		if(is_array($res))
		{
			// if there is more than one user then return the array, otherwise
			// we need to create the array manually.
			if(is_array($res['UserDataArray']->UserData))
			{
				$UserList = $res['UserDataArray']->UserData;
			}
			else
			{
				$arr = array();
				$arr[] = $res['UserDataArray']->UserData;
				$UserList = $arr;
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}

		$Cache = $UserList;
	}

	return $Cache;
}

/**********************************************************
 * ADB_ListAccessByUser -  Returns the user's access level to each
 * 		of the datagroups.  If no user is specified, it lists the 
 *		current user's datagroups and access levels.
 * 04/16/08 msmaga	Added 'Forced' parameter to verify updates
 **********************************************************/
function ADB_ListAccessByUser($UserId=null, $Forced=false, $SortByName=false)
{
	static $Access = null;

	if (($Access === null) || ($Forced))
	{
		$client = GetSOAPSecurityClient ();

		$ParamsArray = GetAuthVars ();
		
		if ($UserId != null)
		{
			// pass the UserId if it was set
			$ParamsArray[] = new SoapParam($UserId, 'UserId');
		}
		// Sort by name if true, newest added first if set to false
		if ($SortByName)
		{
			$ParamsArray[] = new SoapParam(1, 'SortByName');
		}

		$res = $client->__soapCall(	'ListAccessByUser', $ParamsArray);

		if(is_array($res))
		{
			// if there is more than one user then return the array, otherwise
			// we need to create the array manually.
			$Access = array();
			if (isset($res['AccessDataByUserArray']->AccessDataByUser))
			{
				if(is_array($res['AccessDataByUserArray']->AccessDataByUser))
				{
					$Access = $res['AccessDataByUserArray']->AccessDataByUser;
				}
				elseif(is_object($res['AccessDataByUserArray']->AccessDataByUser))
				{
					
					$Access[] = $res['AccessDataByUserArray']->AccessDataByUser;
				}
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}

	return $Access;
}


/**********************************************************
 * ADB_ListAccessByDataGroup -  returns a list of users who
 *      have access to the specified datagroup along with
 *      their access level
 **********************************************************/
function ADB_ListAccessByDataGroup($DataGroupId, $PrivateOnly=0, $ExcludeSystemUsers=0)
{

	$client = GetSOAPSecurityClient ();
	
	$ParamsArray = GetAuthVars ();
	
	$ParamsArray[] = new SoapParam($DataGroupId, 'DataGroupId');
	$ParamsArray[] = new SoapParam($PrivateOnly, 'PrivateOnly');
	$ParamsArray[] = new SoapParam($ExcludeSystemUsers, 'ExcludeSystemUsers');         

	$res = $client->__soapCall( 'ListAccessByDataGroup', $ParamsArray);
	
	if(is_array($res))
	{
		// if there is more than one user then return the array, otherwise
		// we need to create the array manually.
		$Access = array();
		if (isset($res['AccessDataByDataGroupArray']->AccessDataByDataGroup))
		{
			if(is_array($res['AccessDataByDataGroupArray']->AccessDataByDataGroup))
			{
				$Access = $res['AccessDataByDataGroupArray']->AccessDataByDataGroup;
			}
			elseif(is_object($res['AccessDataByDataGroupArray']->AccessDataByDataGroup))
			{
				
				$Access[] = $res['AccessDataByDataGroupArray']->AccessDataByDataGroup;
			}    
		}
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}
  
	return $Access;
}


//------------------------------------------------------------------
// ADB_ListUserCounts -  Returns a list of the number of users
//------------------------------------------------------------------
function ADB_ListUserCounts()
{
	$client = GetSOAPSecurityClient ();

	$res = $client->__soapCall(	'ListCurrentUsers', GetAuthVars ());

	if (is_array($res))
	{
		if (isset($res['UserStatistics']))
		{
			return $res['UserStatistics'];
		}
		else
		{
			trigger_error('DataServer Error: Invalid response to ListUserCounts()', E_USER_ERROR);
		}
	}
	else
	{
		return ReportDataServerError($res);
	}	
}


//------------------------------------------------------------------
// ADB_GetDatabaseInfo -  Return infomation about the database
//------------------------------------------------------------------
function ADB_GetDatabaseInfo()
{
	$client = GetSOAPImageClient ();
	
	$res = $client->__soapCall(	'GetDatabaseInfo', GetAuthVars ());

	if (is_array($res))
	{
		if (isset($res['DatabaseInfo']))
		{
			return $res['DatabaseInfo'];
		}
		else
		{
			trigger_error('DataServer Error: Invalid response to GetDatabaseInfo()', E_USER_ERROR);
		}
	}
	else
	{
		return ReportDataServerError($res);
	}	
}

//------------------------------------------------------------------
// ADB_IsLoginTaken - see if the specified login is taken by a user
// 	other than the one specified in UserId
//------------------------------------------------------------------
function ADB_IsLoginTaken($LoginName, $UserId=-1)
{
	$UserList = ADB_ListUsers();
	foreach ($UserList as $User)
	{
		if (strtolower($User->LoginName) == strtolower($LoginName) && ($User->UserId != $UserId) )
			return true;
	}
	return false;
}

//------------------------------------------------------------------
// ADB_UpdateAccessByUser -  updates the user's access level to each
// 		of the the specified datagroups
//------------------------------------------------------------------
function ADB_UpdateAccessByUser($UserId, $AccessLevels)
{
	$client = GetSOAPSecurityClient ();

	$AccessByUserXML = "<AccessDataByUserArray><UserId>$UserId</UserId>";
	foreach ($AccessLevels as $DataGroupId => $AccessLevel)
	{
		$AccessByUserXML = $AccessByUserXML . "<AccessDataByUser><DataGroupId>$DataGroupId</DataGroupId><AccessFlags>$AccessLevel</AccessFlags></AccessDataByUser>";
	}

	$AccessByUserXML = $AccessByUserXML . '</AccessDataByUserArray>';

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapVar($AccessByUserXML, 147);

	$res = $client->__soapCall(	'UpdateAccessByUser', $ParamsArray);

	if ($res->ASResult != 0)
	{
		return ReportDataServerError($res);
	}

}

//------------------------------------------------------------------
// ADB_UpdateAccessByDataGroup -  updates the user's access level to each
//         of the the specified datagroups
//------------------------------------------------------------------
function ADB_UpdateAccessByDataGroup($DataGroupId, $UserIds, $AccessLevels, $PrivateOnly=0, $DenyMissingUsers=0)
{
	$client = GetSOAPSecurityClient ();
	
	
	$AccessByDataGroupXML = "<AccessDataByDataGroupArray><DataGroupId>$DataGroupId</DataGroupId>";
	for ($i=0; $i<count($UserIds); $i++)
	{
		$UserId = $UserIds[$i];
		$AccessLevel = $AccessLevels[$i];
		$AccessByDataGroupXML = $AccessByDataGroupXML . "<AccessDataByDataGroup><UserId>$UserId</UserId><AccessFlags>$AccessLevel</AccessFlags></AccessDataByDataGroup>";
	}
	
	$AccessByDataGroupXML .= '</AccessDataByDataGroupArray>';
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapVar($AccessByDataGroupXML, 147);
	$ParamsArray[] = new SoapParam($PrivateOnly,'PrivateOnly');
	$ParamsArray[] = new SoapParam($DenyMissingUsers,'DenyMissingUsers');     
	
	$res = $client->__soapCall(    'UpdateAccessByDataGroup', //SOAP Method Name
								$ParamsArray);                //Parameters
	
	if($res->ASResult != 0)
	{
		return ReportDataServerError($res);
	}

}
//------------------------------------------------------------------
// ADB_GetPinDrops -  returns XML list of pin drops for entire case
//------------------------------------------------------------------
function ADB_GetPinDrops($TableName, $CurrentId)
{
	$client = GetSOAPImageClient();
	$CaseIds = '<Ids><Id>' . $CurrentId . '</Id></Ids>';  // TODO Get prior case ids
	
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	$ParamsArray[] = new SoapVar($CaseIds, 147);

	$res = $client->__soapCall('GetPinDrops', $ParamsArray);
	CheckDBResult($res);
	
	if ($res['PinDrops'])
	{
		if (is_array($res['PinDrops']->PinDrop) == false)
		{
			$res['PinDrops']->PinDrop = array($res['PinDrops']->PinDrop);
		}
		return $res['PinDrops'];
	}
		
	return false;
}
//------------------------------------------------------------------
// ADB_PutPinDrops -  accepts list of pin drops to be added or deleted
//------------------------------------------------------------------
function ADB_PutPinDrops($TableName, $PinDrops)
{
	$client = GetSOAPImageClient();
	
	// Example PinDrops XML
	//$PinDrops = '<PinDrops><PinDrop><ImageId>85</ImageId><DisplayOrder>1</DisplayOrder><Region><Id>859</Id><Type>5</Type><Zoom>1</Zoom><Text></Text><Vertices><Vertex X="2334" Y="473" /></Vertices></Region></PinDrop></PinDrops>';
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	$ParamsArray[] = new SoapVar($PinDrops, 147);

	$res = $client->__soapCall('PutPinDrops', $ParamsArray);
	CheckDBResult($res);
}


//------------------------------------------------------------------
// ADB_ClearAccountLock - Removes the account lock from a user
//------------------------------------------------------------------
function ADB_ClearAccountLock($UserId)
{
	$client = GetSOAPSecurityClient ();
	
	$AccessByUserXML = "<UserData><UserId>$UserId</UserId></UserData>";
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapVar($AccessByUserXML, 147);
	
	$res = $client->__soapCall(	'ClearAccountLock',		//SOAP Method Name
								$ParamsArray); 			//Parameters
	
	if($res->ASResult != 0)
	{
		ReportDataServerError($res);
	}
}

//------------------------------------------------------------------
// ADB_AddDataGroup -  add a new data group giving it a name and
//		description.  The private option is used in SecondSlide where
//		users can create their own "private" datagroups without having
//		to have administrative priveliges.
//------------------------------------------------------------------
function ADB_AddDataGroup($AuthToken, $GroupName, $GroupDescription, $IsPrivate=0)
{
	$client = GetSOAPSecurityClient ();
	
	$EscapedGroupName = xmlencode($GroupName);
	$EscapedGroupDesc = xmlencode($GroupDescription);
	
	$DataGroupXML = "<DataGroup><Name>$EscapedGroupName</Name><Description>$EscapedGroupDesc</Description><IsPrivate>$IsPrivate</IsPrivate></DataGroup>";
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapVar($DataGroupXML, 147);
	
	$res = $client->__soapCall(	'AddDataGroup',				// SOAP Method Name
								$ParamsArray,				// Parameters
								NULL, NULL, $OutputHeaders, true);	// Force only one attempt
						
	if(is_array($res))
	{
		return $res['DataGroupId'];
	}
	else
	{
		return ReportDataServerError($res);
	}	
}

//------------------------------------------------------------------
// ADB_UpdateDataGroup - Update name and description of existing
// 		data group specified by GroupId.
//------------------------------------------------------------------
function ADB_UpdateDataGroup($GroupId, $GroupName, $GroupDescription)
{
	$client = GetSOAPSecurityClient ();
	
	// create the DataRow XML
	$dom = new DOMDocument();
	$ndDataGroup = $dom->CreateElement('DataGroup');
	$ndDataGroup->appendChild($dom->CreateElement('Id', $GroupId));
	$ndDataGroup->appendChild($dom->CreateElement('Name', xmlencode($GroupName)));
	$ndDataGroup->appendChild($dom->CreateElement('Description', xmlencode($GroupDescription)));
	$DataGroupXML = $dom->saveXML($ndDataGroup);
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapVar($DataGroupXML, XSD_ANYXML);   	
	
	$res = $client->__soapCall(	'UpdateDataGroup',		//SOAP Method Name
								$ParamsArray);			//Parameters
								
	if(is_object($res))
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}	
}


//------------------------------------------------------------------
// ADB_GetChildList -  Returns a list of child data records
//		that belong to the specified record parent record.  Since this 
//		could be a very large list, MaxRecords limits the count.
//------------------------------------------------------------------
function ADB_GetChildList($ParentId,  $ParentTableName , $ChildTableName)
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($ParentTableName,'ParentTableName');
	$ParamsArray[] = new SoapParam($ParentId,'ParentId');
	$ParamsArray[] = new SoapParam($ChildTableName,'ChildTableName');

	$res = $client->__soapCall(	'GetChildList',		//SOAP Method Name
								$ParamsArray); 		//Parameters

	if(is_array($res))
	{
		$DataRows = array();

		if (isset($res['GenericDataSet']->DataRow))
		{
			if(is_array($res['GenericDataSet']->DataRow))
			{
				foreach($res['GenericDataSet']->DataRow as $DataRow)
				{
					// the data row comes back as an object, but it's more useful
					// if we turn it into an associative array
					$RowFields = array();
					foreach($DataRow as $key => $value) 
					{
					   $RowFields[$key] = $value;
					}
		
					$DataRows[] = $RowFields;
				}
			}	
			elseif (is_object($res['GenericDataSet']->DataRow))
			{
				// the data row comes back as an object, but it's more useful
				// if we turn it into an associative array
				$RowFields = array();
				foreach($res['GenericDataSet']->DataRow as $key => $value) 
				{
				   $RowFields[$key] = $value;
				}
	
				$DataRows[] = $RowFields;
			}
		}
		return $DataRows;			
	}

	return ReportDataServerError($res);
}


//------------------------------------------------------------------
// ADB_GetRecordImages -  Returns a list of images that are
//		associated with the specified data record in the specified
//		table.
//------------------------------------------------------------------
function ADB_GetRecordImages($Id,  $TableName , $MaxImages = 200)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName,'TableName');
	$ParamsArray[] = new SoapParam($Id,'Id');

	$res = $client->__soapCall(	'GetRecordImages',		//SOAP Method Name
								$ParamsArray); 			//Parameters

	$ImageList = array();

	if(is_array($res))
	{
		if (isset($res['ImageDataArray']->ImageData))
		{
			if(is_array($res['ImageDataArray']->ImageData))
			{
				foreach($res['ImageDataArray']->ImageData as $ImageData)
				{
					// the data row comes back as an object, but it's more useful
					// if we turn it into an associative array
					$Arr = array();
					foreach($ImageData as $key => $value) 
					{
					   $Arr[$key] = $value;
					}

					$ImageList[] = $Arr;
				}
			}	
			elseif (is_object($res['ImageDataArray']->ImageData))
			{
				// the data row comes back as an object, but it's more useful
				// if we turn it into an associative array
				$Arr = array();
				foreach($res['ImageDataArray']->ImageData as $key => $value) 
				{
				   $Arr[$key] = $value;
				}
				$ImageList[] = $Arr;
			}
		}
	}

	if(is_object($res))
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}	

	return $ImageList;
}

/**
* Retrieve a list of Markup Image Ids belonging to a particular Image
* 
* @param int $ImageId	- Image Id to retreive markups for
* 
* @return array (int)	- List of Image Ids for the Markup Images
*/
function ADB_GetMarkupImages ($ImageId)
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($ImageId, 'ImageId');

	$res = $client->__soapCall ('GetMarkupImages', $ParamsArray);

	if (is_array($res))
	{
		if (is_object ($res ['Markups']) == false)
			return array ();  // no markups
		if (is_array ($res ['Markups']->Id))
			$ImageList = $res ['Markups']->Id;
		else
			$ImageList = array($res ['Markups']->Id);
		return $ImageList;
	}

	return ReportDataServerError($res);
}

//------------------------------------------------------------------
// Escape SQL chars '_', '%', '[', ']', '^', '~' with 
// leading '~' (tilde) as an escape character
//------------------------------------------------------------------
function EscapeSQLchars($inputString)
{
	$escaped = '';
	$iLen = strlen($inputString);
	for ($i=0; $i < $iLen; $i++)
	{
		switch ($inputString[$i])
		{
			case '_':
			case '%':
			case '[':
			case ']':
			case '^':
			case '~':
			{            
				$escaped = $escaped . '~';
			}
		}
		$escaped = $escaped . $inputString[$i];
	}
	// Replace ' character with ''
	$escaped = str_replace('\'', '\'\'', $escaped);
	return $escaped;
}

//------------------------------------------------------------------
// ADB_GetFilteredRecordList -  Returns a 2D array (i.e. list of records)
//		based on the specified filter.
//------------------------------------------------------------------
function ADB_GetFilteredRecordList($TableName='Slide', $RecordsPerPage=0, $PageIndex=0, $SelectColumns=array(), $FilterColumns=array(), $FilterOperators=array(), $FilterValues=array(), $FilterTables=array(), $SortByField='', $SortOrder='Descending', &$TotalCount = NULL, $Distinct = false)
{
	$FilterArray = array();
	// Build the filter arguments, Count how many tables for DISTINCT keyword
	for ($i=0; $i<count($FilterValues); $i++)
	{
		$FilterColumn = $FilterColumns[$i];
		$FilterOperator = $FilterOperators[$i];
		$FilterValue = ($FilterOperator == 'LIKE' || $FilterOperator == 'NOTLIKE') ? '%'.EscapeSQLchars($FilterValues[$i]).'%' : $FilterValues[$i];
		// convert '-1 day', '-1 week', and '-1 month' to usable values:
		date_default_timezone_set('UTC');
		if ($FilterValue == '-1 day')
			$FilterValue = date ('Y-m-d', time() - (24*60*60));

		elseif ($FilterValue == '-1 week')
			$FilterValue = date ('Y-m-d', time() - (7*24*60*60));

		elseif ($FilterValue == '-1 month')
			$FilterValue =  date("Y-m-d", time() - (31*24*60*60));

		$FilterTable = $FilterTables[$i];

		$dom = new DOMDocument(null, 'utf-8');
		$elFilterBy = $dom->CreateElement('FilterBy');
		$elFilterBy->setAttribute('Column',$FilterColumn);
		$elFilterBy->setAttribute('FilterOperator',$FilterOperator);
		$elFilterBy->setAttribute('FilterValue',$FilterValue);
		$elFilterBy->setAttribute('Table',$FilterTable);
		
		$FilterArray[] = $dom->saveXML($elFilterBy);

		if ($TableName != $FilterTables[$i] && !($TableName == 'Slide' && $FilterTables[$i] == 'Image'))
			$Distinct = true;
	}
	return ADB_GetRecordListWithFilterArray($TableName, $RecordsPerPage, $PageIndex, $SelectColumns, $FilterArray, $SortByField, $SortOrder, $TotalCount, $Distinct);
}

//------------------------------------------------------------------
// ADB_GetRecordListWithFilterArray - Returns a 2D array (i.e. list of records)
//		based on the specified filter. Accepts FilterArray parameter that may contain <Parenthesis> nodes
//
//	a simple FilterArray element look like:
//	<FilterBy Column="ParentTable" FilterOperator="=" FilterValue="Case" Table="Specimen"/>'
//
//	if using parentheses, FilterArray elements look like this:
//	'<Parenthesis Connector="Or"><FilterBy Column="ParentTable" FilterOperator="IsNull" Table="Specimen"/><FilterBy Column="ParentTable" FilterOperator="=" FilterValue="Case" Table="Specimen"/></Parenthesis>'
//------------------------------------------------------------------
function ADB_GetRecordListWithFilterArray($TableName='Slide', $RecordsPerPage=0, $PageIndex=0, $SelectColumns=array(), $FilterArray=array(), $SortByField='', $SortOrder='Descending', &$TotalCount = NULL, $Distinct = false)
{
	global $RecordDataCache;

	$OutTotalCount = 0;

	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars ();

	$ParamsArray[] = new SoapParam ($TableName, 'TableName');

	if ($TotalCount === NULL && $PageIndex == 1)
	{
		// The client does not need totalRecords.
		// Since he is requesting the first page, the DataServer is more efficient by not processing pagination.
		$ParamsArray[] = new SoapParam ($RecordsPerPage, 'MaxCount');
	}
	else
	{
		// Pagination
		$ParamsArray[] = new SoapParam ($PageIndex, 'PageIndex');
		if ($RecordsPerPage > 0) $ParamsArray[] = new SoapParam($RecordsPerPage, 'RecordsPerPage');
	}

	foreach ($FilterArray as $Filter)
	{
		$ParamsArray[] = new SoapVar($Filter, 147);
	}

	// If the user didn't pass a select array, grab the default
	if (count ($SelectColumns) == 0)
		$SelectColumns = GetNeededColumns ($TableName);

	// If there's only one column, we can always (and should always) use DISTINCT
	if (count ($SelectColumns) == 1)
		$Distinct = true;
	
	$AliasToColumnMap = array();    
	// Build the Select string
	$SelectColumnsAliasXML = '<ColumnList'. ($Distinct ? " Distinct='true'" : "")  . '>';
	$aliasIndex = 0;
	foreach ($SelectColumns as $Column)
	{
		// alias all columns whose length is greater than ALIASCOLUMNLENGTH
		if (strlen($Column) > ALIASCOLUMNLENGTH)
		{
			$alias = 'A' . $aliasIndex;
			$aliasIndex++;
		}
		else
			$alias = $Column;
		$AliasToColumnMap[$alias] = $Column;         
		$SelectColumnsAliasXML .= "<Column><Name>" . $Column . '</Name><Alias>' . $alias . '</Alias></Column>';
	}
	 
	// If the sortby field somehow didn't wind up the list of columns stick it in there
	// NOTE: If no columns were selected, the client is requesting all columns, thus do not single out the sort column
	if (is_array($SortByField) || is_array($SortOrder))
	{
		$sortCount = count($SortByField);
		if (count($SortByField) == count($SortOrder))
		{
			for ($i = 0; $i < $sortCount; $i++)
			{
				if (!empty($SortByField[$i]) && !empty($SortOrder[$i]))
				{
					if (!in_array($SortByField[$i], $SelectColumns))
					{
						if (strlen($SortByField[$i]) > ALIASCOLUMNLENGTH)
						{
							$alias = 'A' . $aliasIndex;
							$aliasIndex++;
						}
						else
						{
							$alias = $SortByField[$i];
						}
						$SelectColumnsAliasXML .= "<Column><Name>" . $SortByField[$i] . '</Name><Alias>B' . $alias . '</Alias></Column>';
					}
				}
			}
		}
	}
	else
	{
		if ((count($SelectColumns) > 0) && ($SortByField != '') && (!in_array($SortByField, $SelectColumns)))
		{
			if (strlen($SortByField) > ALIASCOLUMNLENGTH)
			{
				$alias = 'A' . $aliasIndex;
				$aliasIndex++;
			}
			else
			{
				$alias = $SortByField;
			}
			$SelectColumnsAliasXML .= "<Column><Name>" . $SortByField . '</Name><Alias>B' . $alias . '</Alias></Column>';			
		}
	}
   $SelectColumnsAliasXML .= '</ColumnList>';   
	$ParamsArray[] = new SoapVar($SelectColumnsAliasXML, 147);	
	
	if (is_array($SortByField) || is_array($SortOrder))
	{
		$sortCount = count($SortByField);
		if (count($SortByField) == count($SortOrder))
		{
			for ($i = 0; $i < $sortCount; $i++)
			{
				if (!empty($SortByField[$i]) && !empty($SortOrder[$i]))
				{				
					$SortByXML = "<Sort By=\"$SortByField[$i]\" Order=\"$SortOrder[$i]\"/>";
					$ParamsArray[] = new SoapVar($SortByXML, 147);
				}
			}
		}
	}
	else
	{
		if (!empty($SortByField) && !empty($SortOrder))
		{
			$SortByXML = "<Sort By=\"$SortByField\" Order=\"$SortOrder\"/>";
			$ParamsArray[] = new SoapVar($SortByXML, 147);
		}
	}
	
	$res = $client->__soapCall(	'GetFilteredRecordList', $ParamsArray, array('encoding'=>'UTF-8'));    
	
	if(is_array($res))
	{
		$DataRows = array();
		$OutTotalCount = $res['TotalRecordCount'];
		if (is_object($res['GenericDataSet']))
		{
			if (is_object($res['GenericDataSet']->DataRow))
			{
				$RowFields = array();                
				foreach($res['GenericDataSet']->DataRow as $key => $value) 
				{   
					if (array_key_exists($key, $AliasToColumnMap))
					{
						$RowFields[$AliasToColumnMap[$key]] = $value;                        
					}
					else
					{                                               
						$RowFields[$key] = $value;
					}
				}
				$DataRows[] = $RowFields;
			}
			if (is_array($res['GenericDataSet']->DataRow))
			{
				foreach($res['GenericDataSet']->DataRow as $DataRow)
				{
					$RowFields = array();
					foreach($DataRow as $key => $value) 
					{
						if (array_key_exists($key, $AliasToColumnMap))
						{                            
							$RowFields[$AliasToColumnMap[$key]] = $value;
						}
						else
						{                                               
							$RowFields[$key] = $value;
						}
					}					
					$DataRows[] = $RowFields;
				}
			}
		}		
		// Cache the record into the global $RecordDataCache
		if (isset ($_SESSION ['HierarchyLevels'][$TableName]))
		{
			$TableSchema = $_SESSION ['HierarchyLevels'][$TableName];			
			if (!isset ($RecordDataCache [$TableName]))
			{
				$RecordDataCache [$TableName] = array ();
			}
			
			foreach ($DataRows as $Row)
			{
				$keyId = $TableSchema->IdFields [0]->ColumnName;   
				$Id = NULL;
				if (array_key_exists($keyId, $AliasToColumnMap))
				{                 
					if (isset ($Row [$AliasToColumnMap[$keyId]]))
					{
						$Id = $Row [ $AliasToColumnMap[$keyId]];
					}
				}                
				
				if (isset($Id))
				{
					if (!isset ($RecordDataCache [$TableName][$Id]))
					{
						$RecordDataCache [$TableName][$Id] = array ();
					}
					//copy the column values into the $RecordDataCache
					foreach($Row as $key => $value)
					{
						if (array_key_exists($key, $AliasToColumnMap))
						{                            
							$RecordDataCache [$TableName][$Id][$AliasToColumnMap[$key]] = $value;
						}
						else
						{                                               
							$RecordDataCache [$TableName][$Id][$key] = $value;                            
						}                        
					}
					if (array_key_exists($key, $AliasToColumnMap))
					{
						if ($TableName == 'Slide' && isset($Row [ $AliasToColumnMap['ImageId']]) && ($Row [$AliasToColumnMap['ImageId']] > 0))
						{
							$RecordDataCache ['Image'][$Row [$AliasToColumnMap['ImageId']]] = array ();
							foreach ($Row as $Name => $Value)
							{
								$RecordDataCache ['Image'][$Row [$AliasToColumnMap['ImageId']]][$ColumnToAliasMap[$key]] = $Value;
							}
						}
					}
					else
					{                        
						if ($TableName == 'Slide' && isset($Row ['ImageId']) && ($Row ['ImageId'] > 0))
						{
							$RecordDataCache ['Image'][$Row ['ImageId']] = array ();
							foreach ($Row as $Name => $Value)
							{
								$RecordDataCache ['Image'][$Row ['ImageId']][$Name] = $Value;
							}
						}
					}
				}                
			}
		}
		if ($TotalCount !== NULL)
			$TotalCount = $OutTotalCount;
		return $DataRows;			
	}

	return ReportDataServerError($res);
}

//------------------------------------------------------------------
// ADB_AssignCaseToUser -  Assign a case to a user
//------------------------------------------------------------------
function ADB_AssignCaseToUser($UserId, $CaseId, $UpdateCaseMemo = '0', $MemoContent = '', $PALModuleName = '')
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($UserId, 'UserId');
	$ParamsArray[] = new SoapParam ($CaseId, 'CaseId');
	$ParamsArray[] = new SoapParam ($PALModuleName, 'PalModuleName');
	if ($UpdateCaseMemo != '0')
	{
		$ParamsArray[] = new SoapParam ($UpdateCaseMemo, 'UpdateCaseMemo');
		$ParamsArray[] = new SoapParam ($MemoContent, 'MemoContent');
	}
	$res = $client->__soapCall(	'AssignCaseToUser', $ParamsArray, array('encoding'=>'UTF-8'));

	return $res;
}

//------------------------------------------------------------------
// ADB_GetBadges -  Get badge data for current user
//------------------------------------------------------------------
function ADB_GetBadges ()
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam('1', 'DoNotRenewToken');
	$res = $client->__soapCall(	'GetBadges', $ParamsArray, array('encoding'=>'UTF-8'));

	return $res;
}

//------------------------------------------------------------------
// ADB_GetWorkflowUsers -  Get Users for a workflow
//------------------------------------------------------------------
function ADB_GetWorkflowUsers ($WorkFlowName, $DataGroupId=NULL)
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($WorkFlowName, 'PalModuleName');
	if($DataGroupId != NULL)
	{
		$ParamsArray[] = new SoapParam($DataGroupId, 'DataGroupId');    
	}
	$SelectColumnsXML = "<ColumnList Distinct='true'>Id LoginName FullName</ColumnList>";
	$ParamsArray[] = new SoapVar($SelectColumnsXML, 147);
	$SortByXML =  "<Sort By='FullName' Order='Ascending'></Sort>";
	$ParamsArray[] = new SoapVar($SortByXML, 147);
	$res = $client->__soapCall(	'GetWorkflowUsers', $ParamsArray, array('encoding'=>'UTF-8'));
	if (is_array($res) && isset($res['GenericDataSet']->DataRow))
	{
		if (is_array($res['GenericDataSet']->DataRow))
			return $res['GenericDataSet']->DataRow;
		else
			return array($res['GenericDataSet']->DataRow);
	}

	return $res;
}

//------------------------------------------------------------------
// ADB_GetWorkflowUserGroups -  Get User Groups for a workflow
//------------------------------------------------------------------
function ADB_GetWorkflowUserGroups ($WorkFlowName, $DataGroupId=null)
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($WorkFlowName, 'PalModuleName');
	if($DataGroupId != NULL)
	{
		$ParamsArray[] = new SoapParam($DataGroupId, 'DataGroupId');    
	}
	$SelectColumnsXML = "<ColumnList Distinct='true'>Id Name</ColumnList>";
	$ParamsArray[] = new SoapVar($SelectColumnsXML, 147);
	$SortByXML =  "<Sort By='Name' Order='Ascending'></Sort>";
	$ParamsArray[] = new SoapVar($SortByXML, 147);
	$res = $client->__soapCall(	'GetWorkflowUserGroups', $ParamsArray, array('encoding'=>'UTF-8'));
	if (is_array($res) && isset($res['GenericDataSet']->DataRow))
	{
		if (is_array($res['GenericDataSet']->DataRow))
			return $res['GenericDataSet']->DataRow;
		else
			return array($res['GenericDataSet']->DataRow);
	}

	return $res;
}

//------------------------------------------------------------------
// ADB_CreateCaseConsultationRequest -  Create a copy of a case and assign it to the Consultation Request workflow
//------------------------------------------------------------------
function ADB_CreateCaseConsultationRequest ($CaseId)
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($CaseId, 'CaseId');
	$res = $client->__soapCall(	'CreateCaseConsultationRequest', $ParamsArray, array('encoding'=>'UTF-8'));

	return $res;
}

//------------------------------------------------------------------
// ADB_CreateCaseConsultationReview -  Create a copy of a case and assign it to the Consultation Review workflow
//------------------------------------------------------------------
function ADB_CreateCaseConsultationReview ($CaseId, $UserId)
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($CaseId, 'CaseId');
	$ParamsArray[] = new SoapParam($UserId, 'UserId');
	$res = $client->__soapCall(	'CreateCaseConsultationReview', $ParamsArray, array('encoding'=>'UTF-8'));

	return $res;
}

//------------------------------------------------------------------
// ADB_CreateExternalCaseConsultationReview -  Create CaseReviewResult records
//------------------------------------------------------------------
function ADB_CreateExternalCaseConsultationReview ($CaseId)
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($CaseId, 'CaseId');
	$res = $client->__soapCall(	'CreateExternalCaseConsultationReview', $ParamsArray, array('encoding'=>'UTF-8'));

	return $res;
}

//------------------------------------------------------------------
// ADB_CompleteCase -  Mark a case as completed
//------------------------------------------------------------------
function ADB_CompleteCase ($CaseId, $ValidateOnly = true, $SigUserName = '', $SigPassword = '')
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($CaseId, 'CaseId');
	if ($SigUserName !== '')
	{
		$ParamsArray[] = new SoapParam($SigUserName, 'SigUserName');
		$ParamsArray[] = new SoapParam($SigPassword, 'SigPassword');
	}
	if ($ValidateOnly)
		$ParamsArray[] = new SoapParam(1, 'ValidateOnly');
	$res = $client->__soapCall(	'CompleteCase', $ParamsArray, array('encoding'=>'UTF-8'));
	return CheckDBResult($res, false);
}

//------------------------------------------------------------------
// ADB_CompleteCaseReview -  Mark a case Consult Review as completed
//------------------------------------------------------------------
function ADB_CompleteCaseReview ($CaseId, $ValidateOnly)
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($CaseId, 'CaseId');
	if ($ValidateOnly)
		$ParamsArray[] = new SoapParam(1, 'ValidateOnly');
	$res = $client->__soapCall(	'CompleteCaseReview', $ParamsArray, array('encoding'=>'UTF-8'));
	return CheckDBResult($res, false);
}

//------------------------------------------------------------------
// ADB_CompleteExternalCaseReview -  Mark a case Consult Review as completed from SecondSlide
//------------------------------------------------------------------
function ADB_CompleteExternalCaseReview ($CaseReviewResultId, $ValidateOnly = true, $SigUserName = '', $SigPassword = '')
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($CaseReviewResultId, 'ReviewResultId');
	if ($SigUserName !== '')
	{
		$ParamsArray[] = new SoapParam($SigUserName, 'SigUserName');
		$ParamsArray[] = new SoapParam($SigPassword, 'SigPassword');
	}
	if ($ValidateOnly)
		$ParamsArray[] = new SoapParam(1, 'ValidateOnly');
	$res = $client->__soapCall(	'CompleteExternalCaseReview', $ParamsArray, array('encoding'=>'UTF-8'));
	return CheckDBResult($res, false);
}

//------------------------------------------------------------------
// ADB_ResetCaseNotification -  reset case notification (badge)
//------------------------------------------------------------------
function ADB_ResetCaseNotification ($CaseId, $PALModuleName)
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($CaseId, 'CaseId');
	$ParamsArray[] = new SoapParam($PALModuleName, 'PALModuleName');
	$res = $client->__soapCall(	'ResetCaseNotification', $ParamsArray, array('encoding'=>'UTF-8'));

	return $res;
}

//------------------------------------------------------------------
// ADB_AssignCaseToUserGroup -  Assign a case to a user group
//------------------------------------------------------------------
function ADB_AssignCaseToUserGroup($UserGroupId, $CaseId, $UpdateCaseMemo = '0', $MemoContent = '', $PALModuleName = '')
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($UserGroupId, 'UserGroupId');
	$ParamsArray[] = new SoapParam ($CaseId, 'CaseId');
	$ParamsArray[] = new SoapParam ($PALModuleName, 'PalModuleName');
	if ($UpdateMemo != '0')
	{
		$ParamsArray[] = new SoapParam ($UpdateCaseMemo, 'UpdateCaseMemo');
		$ParamsArray[] = new SoapParam ($MemoContent, 'MemoContent');
	}
	$res = $client->__soapCall(	'AssignCaseToUserGroup', $ParamsArray, array('encoding'=>'UTF-8'));

	return $res;
}

//------------------------------------------------------------------
// ADB_RequestMoreWork -  Assign a case to a user group
//------------------------------------------------------------------
function ADB_RequestMoreWork($CaseId, $UpdateCaseMemo = '0', $MemoContent = '')
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($CaseId, 'CaseId');
	if ($UpdateMemo != '0')
	{
		$ParamsArray[] = new SoapParam ($UpdateCaseMemo, 'UpdateCaseMemo');
		$ParamsArray[] = new SoapParam ($MemoContent, 'MemoContent');
	}
	$res = $client->__soapCall(	'RequestMoreWork', $ParamsArray, array('encoding'=>'UTF-8'));

	return $res;
}

/**
* Returns a list of dsc users
* 
* @return array        - List of dsc users in the DB
* Despite its name this does not return rows from DSCUser table!!
* It returns users from that User table that can be part of a conference.
*/
function ADB_GetDSCUsers()
{
	$client = GetSOAPSecurityClient ();

	$ParamsArray = GetAuthVars();
	//remove logged on user from possible participants list. You cannot be a host and participant!!
	$FilterSelfXML = "<FilterBy Column='Id' FilterOperator='&lt;&gt;' FilterValue='".$_SESSION['UserId']."' />";
	$ParamsArray[] = new SoapVar($FilterSelfXML, 147);

	$res = $client->__soapCall('GetDSCUsers', $ParamsArray, array('encoding'=>'UTF-8'));

	CheckDBResult($res, true);

	$UserList = array ();

	if (isset($res['GenericDataSet']->DataRow))
	{
		// if there is more than one user then return the array, otherwise
		// we need to create the array manually.
		if(is_array($res['GenericDataSet']->DataRow))
		{
			$UserList = $res['GenericDataSet']->DataRow;
		}
		else
		{
			$arr = array();
			$arr[] = $res['GenericDataSet']->DataRow;
			$UserList = $arr;
		}
	}

	return $UserList;
}

/**
* Returns a specific dsc user
* 
* @params string $UserId, 
* @return object        - dsc user
* Despite its name this does not return rows from DSCUser table!!
* It returns users from that User table that can be part of a conference.
*/
function ADB_GetDSCUser($UserId)
{
	$client = GetSOAPSecurityClient ();

	$ParamsArray = GetAuthVars();

	// filter by UserId
	$FilterXML = "<FilterBy Column='Id' FilterOperator='=' FilterValue='$UserId' />";
	$ParamsArray[] = new SoapVar($FilterXML, 147);

	$res = $client->__soapCall('GetDSCUsers', $ParamsArray, array('encoding'=>'UTF-8'));

	CheckDBResult($res, true);
 
	if (isset($res['GenericDataSet']->DataRow))
	{
		return $res['GenericDataSet']->DataRow;
	}

	return new stdClass();
}

//------------------------------------------------------------------
// ADB_RecordExists -  Returns whether a record exists and whether the user has access to it
//------------------------------------------------------------------
function ADB_RecordExists($TableName, $FieldName, $FieldValue)
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	$ParamsArray[] = new SoapParam($FieldName, 'FieldName');
	$ParamsArray[] = new SoapParam($FieldValue, 'FieldValue');
	$res = $client->__soapCall(	'RecordExists',		//SOAP Method Name
								$ParamsArray, 					//Parameters
								array('encoding'=>'UTF-8')); 	//Option

	if(is_array($res))
	{
		return $res;
	}
	return ReportDataServerError($res);
}

/**
* ADB_RecordList
* @desc Wrapper for $GetFilteredRecordList   
* @params string $TableName, 
* 		string $SortField, 
* 		array $GetColumnNames,
* 		string $FilterColumn,
* 		string $FilterValue
**/  
function ADB_GetRecordList($TableName, $SortField=NULL, $ColumnNames=array(), $FilterColumn='', $FilterValue='')
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	
	if ($FilterValue != '')
	{
		$FilterOperator = '=';
		$FilterTable = $TableName;
	
		$dom = new DOMDocument(null, 'utf-8');
		$elFilterBy = $dom->CreateElement('FilterBy');
		$elFilterBy->setAttribute('Column',$FilterColumn);
		$elFilterBy->setAttribute('FilterOperator',$FilterOperator);
		$elFilterBy->setAttribute('FilterValue',$FilterValue);
		$elFilterBy->setAttribute('Table',$FilterTable);
		
		$ParamsArray[] = new SoapVar($dom->saveXML($elFilterBy), 147);
	}
		
	if ($SortField != NULL)
	{                                                
		$SortByXML =  "<Sort By=\"$SortField\" Order=\"Ascending\"/>";
		$ParamsArray[] = new SoapVar($SortByXML, 147);
	}

	if (count ($ColumnNames) > 0)
	{
		// Build the SelectColumns string
		$SelectColumnsXML = '<ColumnList ' . (count ($ColumnNames) === 1 ? ' Distinct="true"' : '' )  .'>';
		foreach ($ColumnNames as $Column)
		{
			$SelectColumnsXML .= "[$Column] ";
		}

		$SelectColumnsXML .=' </ColumnList>';
		$ParamsArray[] = new SoapVar($SelectColumnsXML, 147);
	}

	$res = $client->__soapCall(	'GetFilteredRecordList',		//SOAP Method Name
								$ParamsArray, 					//Parameters
								array('encoding'=>'UTF-8')); 	//Option

	if(is_array($res))
	{
		$DataRows = array();
		$OutTotalCount = $res['TotalRecordCount'];
		if (is_object($res['GenericDataSet']))
		{
			if (is_object($res['GenericDataSet']->DataRow))
			{
				$RowFields = array();
				foreach($res['GenericDataSet']->DataRow as $key => $value) 
				{
				   $RowFields[$key] = $value;
				}
	
				$DataRows[] = $RowFields;
			}
			if (is_array($res['GenericDataSet']->DataRow))
			{
				foreach($res['GenericDataSet']->DataRow as $DataRow)
				{
					// the data row comes back as an object, but it's more useful
					// if we turn it into an associative array
					
					$RowFields = array();
					foreach($DataRow as $key => $value) 
					{
					   $RowFields[$key] = $value;
					}
		
					$DataRows[] = $RowFields;
				}
			}
		}
		return $DataRows;			
	}

	return ReportDataServerError($res);
}

/**
* Searches for the immediate preceding and following neighbors in a list given the search parameters
* The counts of $SortByField and $SortOrder must match
* @param string $TableName			Table to search
* @param int $Id					List Id to start from
* @param int $Index					List offset to search for
* @param array() $FilterColumns		Columns to filter on
* @param array() $FilterOperators	Operations for the filter to match
* @param array() $FilterValues		Values to compare against
* @param array() $FilterTables		Tables to search for the columns in
* @param array() $SortByField		Column[s] to sort by.  Defaults to no sort. 
* @param array() $SortOrder         Direction to sort each column by ('Ascending' or 'Descending')
*/
function ADB_GetRecordNeighbors ($TableName='Slide', $Id=1, $Index=-1, $FilterColumns=array(), $FilterOperators=array(), $FilterValues=array(), $FilterTables=array(), $SortByField=array(""), $SortOrder=array('Descending'))
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	$ParamsArray[] = new SoapParam($Id, 'Id');
	if ($Index > 0)
		$ParamsArray[] = new SoapParam($Index, 'RowNumber');

	// Build the filter arguments, Count how many tables for DISTINCT keyword
	for ($i=0; $i<count($FilterValues); $i++)
	{
		$FilterColumn = $FilterColumns[$i];
		$FilterOperator = $FilterOperators[$i];
		$FilterValue = ($FilterOperator == 'LIKE' || $FilterOperator == 'NOTLIKE') ? '%'.EscapeSQLchars($FilterValues[$i]).'%' : $FilterValues[$i];
		$FilterTable = $FilterTables[$i];

		$dom = new DOMDocument(null, 'utf-8');
		$elFilterBy = $dom->CreateElement('FilterBy');
		$elFilterBy->setAttribute('Column',$FilterColumn);
		$elFilterBy->setAttribute('FilterOperator',$FilterOperator);
		$elFilterBy->setAttribute('FilterValue',$FilterValue);
		$elFilterBy->setAttribute('Table',$FilterTable);

		$ParamsArray[] = new SoapVar($dom->saveXML($elFilterBy), 147);
	}	

	// Build the Select string
	$SelectColumns = GetNeededColumns ($TableName);
	$SelectColumnsString = '';
	foreach ($SelectColumns as $Column)
	{
		$SelectColumnsString .= "[$Column] ";
	}

	// Make sure all of the $SortBy columns are in $SelectColumnsString
	foreach ($SortByField as $sortFieldName)
	{
		if (!in_array($sortFieldName, $SelectColumns))
		{
			$SelectColumnsString .= "[$sortFieldName] ";
		}
	}

	$SelectColumnsXML = "<ColumnList Distinct='true'>$SelectColumnsString</ColumnList>";
	$ParamsArray[] = new SoapVar($SelectColumnsXML, 147);

	// Set the sort parameters
	$sortCount = count($SortOrder);
	for ($i = 0; $i < $sortCount; $i++) 
	{
		$sortFieldName = $SortByField[$i];
		$sortOrderName = $SortOrder[$i];
		//only add sorts that are non empty
		if (!empty($sortFieldName)&& !empty($sortOrderName))
		{
			$SortByXML = "<Sort By=\"$sortFieldName\" Order=\"$sortOrderName\"/>";
			$ParamsArray[] = new SoapVar($SortByXML, 147);
		}
	}

	$res = $client->__soapCall('GetNextPrevious', $ParamsArray, array('encoding'=>'UTF-8'));

	if(is_array($res))
	{
		return $res;
	}
	if(is_object($res))
	{
		return array ('PrevId' => 0, 'NextId' => 0);
		//trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
	}
}

//------------------------------------------------------------------
// ADB_GetUnassignedRecords -  Returns a 2D array (i.e. list of 
//	unassigned records at the given data level in the given sort order.
//------------------------------------------------------------------
function ADB_GetUnassignedRecords($TableName, $SortByField=null, $SortOrder="Descending")
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName,'TableName');
	
	$res = $client->__soapCall(	'ListUnAssignedRecords',	//SOAP Method Name
								$ParamsArray);				//Parameters
								
	if(is_array($res))
	{
		$DataRows = array();
		if (isset($res['GenericDataSet']->DataRow))
		{
			if (is_array($res['GenericDataSet']->DataRow))
			{
				foreach($res['GenericDataSet']->DataRow as $DataRow)
				{
					// the data row comes back as an object, but it's more useful
					// if we turn it into an associative array
					$RowFields = array();
					foreach($DataRow as $key => $value) 
					{
					   $RowFields[$key] = $value;
					}
		
					$DataRows[] = $RowFields;
				}
			}
			elseif (is_object($res['GenericDataSet']->DataRow))
			{
				$RowFields = array();
				foreach($res['GenericDataSet']->DataRow as $key => $value) 
				{
				   $RowFields[$key] = $value;
				}
	
				$DataRows[] = $RowFields;	
			}
		}
		
		return $DataRows;			
	}

	return ReportDataServerError($res);
}

/************************************************
 * ADB_PutRecordData - If an Id is passed then updated the specified
 *	row in the specified table.  Otherwize add a new row to the
 *	specified table.
 ************************************************/
function ADB_PutRecordData($TableName, $NameValues, $Id=-1)
{
	return ADB_PutRecord('PutRecordData', $TableName, $NameValues, $Id, true);
}


function ADB_PutRecord($Method, $TableName, $NameValues, $Id, $DoCommit=true)
{
	$client = GetSOAPImageClient ();

	// create the DataRow XML
	$dom = new DOMDocument();
	$ndDataRow = $dom->CreateElement('DataRow');
	foreach ($NameValues as $ColumnName=>$ColumnValue)
	{
		$ndColumn = $dom->CreateElement($ColumnName, xmlencode($ColumnValue));
		$ndDataRow->appendChild($ndColumn);
	}
	$DataRowXML = $dom->saveXML($ndDataRow);

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	if ($DoCommit == false)
		$ParamsArray[] = new SoapParam(1, 'ValidateOnly');

	// if an ID was passed then use it.
	if ($Id > -1)
		$ParamsArray[] = new SoapParam($Id, 'Id');

	$ParamsArray[] = new SoapVar($DataRowXML, XSD_ANYXML); 	//147

	$res = $client->__soapCall($Method, $ParamsArray, NULL, NULL, $OutputHeaders, $Id == -1); // Only execute once if it's a new record

	$Result = CheckDBResult($res, $DoCommit);
	if (($Result == 0) && ($DoCommit))
	{
		if (is_array($res))
			return $res['Id'];
		return $res->Id;
	}

	return $Result;
}


//------------------------------------------------------------------
// ADB_GetRecordData -  Returns an array of data values for the given
//		record ID in the specified table.
//------------------------------------------------------------------
/**
* Returns an array of data values for the given record ID in the specified table.
* 
* @param string $TableName	- DB Table to look in
* @param int $Id			- Id of the record to retrieve
* @param bool $UseCache     - if false, cached data will not be used
* 
* @return array ()			- Array in the form of ColumnName=>Value of the record's data
* 
* - thoare 080828	Gave the function a cache to prevent excessive DataServer calls  
*/
function ADB_GetRecordData($TableName, $Id, $UseCache=true)
{
	global $RecordDataCache;

	if (!$UseCache || !isset ($RecordDataCache [$TableName][$Id]))
	{
		$client = GetSOAPImageClient ();
											
		$ParamsArray = GetAuthVars ();
		$ParamsArray[] = new SoapParam($TableName, 'TableName');
		$ParamsArray[] = new SoapParam($Id, 'Id');
		
		$res = $client->__soapCall(	'GetRecordData', $ParamsArray);

		if(is_array($res))
		{
			if($res['GetRecordDataResult']->ASResult == 0)
			{
				// the data row comes back as an object, but it's more useful
				// if we turn it into an associative array
				$Arr = array();
				foreach($res['DataRow'] as $key => $value) 
				{
				   $Arr[$key] = $value;
				}
				
				$RecordDataCache [$TableName][$Id] = $Arr;
			}			
		}
		if(is_object($res))
		{
			if ($res->ASResult == ROLE_ACCESS_VIOLATION)
			{
				$RecordDataCache [$TableName][$Id] = false;
			}
			elseif($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}

	return $RecordDataCache [$TableName][$Id];
}


// Return the table schema from the database
function ADB_GetSchema($TableName)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName,'TableName');

	$res = $client->__soapCall(	'GetColumns', $ParamsArray);

	if (is_object ($res))
		return ReportDataServerError($res);
	return $res['Columns']->Column;
}

//------------------------------------------------------------------
// ADB_AddChildRecordToParentRecord -  add the specified child to the 
// 		specified parent by adding an entry into the ChildParent 
//		join table.
//------------------------------------------------------------------
function ADB_AddChildRecordToParentRecord($ParentTableName, $ParentId, $ChildTableName, $ChildId)
{
	$client = GetSOAPImageClient ();
	
	// special logic for assigning a core to a spicimen.  This should not be
	// a permanent solution.  Eventually this should be handled in dataserver or
	// something.
	if ($ParentTableName == 'Specimen' && $ChildTableName == 'Core')
	{	
		ADB_PutRecordData('Core', array('SpecimenId'=>$ParentId),$ChildId);
	}
	else
	{
		$ParamsArray = GetAuthVars ();
		$ParamsArray[] = new SoapParam($ParentTableName,'ParentTableName');
		$ParamsArray[] = new SoapParam($ParentId, 'ParentId');
		$ParamsArray[] = new SoapParam($ChildTableName, 'ChildTableName');
		$ParamsArray[] = new SoapParam($ChildId,'ChildId');
		
		$res = $client->__soapCall(	'AddChildRecordToParentRecord',		//SOAP Method Name
									$ParamsArray);						//Parameters
	
		if(is_object($res))
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
}


//------------------------------------------------------------------
// ADB_RemoveChildRecordFromParentRecord - remove the specified child from the 
//		specified parent by removeing the corresponding row in the 
//		ChildParent join table. 
//------------------------------------------------------------------
function ADB_RemoveChildRecordFromParentRecord($ParentTableName, $ParentId, $ChildTableName, $ChildId)
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($ParentTableName,'ParentTableName');
	$ParamsArray[] = new SoapParam($ParentId, 'ParentId');
	$ParamsArray[] = new SoapParam($ChildTableName,'ChildTableName');
	$ParamsArray[] = new SoapParam($ChildId,'ChildId');
	
	$res = $client->__soapCall(	'RemoveChildRecordFromParentRecord',	//SOAP Method Name
								$ParamsArray);							//Parameters

	if(is_object($res))
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}
}

/**
* Delete the specified row from the specified data table.
* 
* @param string $TableName	- Name of the table to look in
* @param int $Id			- Id of the record to delete
* @param bool $DoCommit		- Whether to commit the change or just test for permissions
*/
function ADB_TestDeleteRecord($TableName, $Id, $Parms=null)
{
	return ADB_DeleteRecord($TableName, $Id, false, $Parms);
}
function ADB_DeleteRecord($TableName, $Id, $DoCommit = true, $Parms=null)
{
	return ADB_DeleteThisRecord('DeleteRecordData', $TableName, $Id, $DoCommit, $Parms);
}
function ADB_DeleteThisRecord($Method, $TableName, $Id, $DoCommit = true, $Parms=null)
{
	$client = GetSOAPImageClient ();

	// cascading delete's may take a lot of time so temporarily
	// lengthen the default socket timeout and the max script execution time
	$client->SetTimeout(180);

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName,'TableName');
	$ParamsArray[] = new SoapParam($Id,'Id');
	if ($Method == 'DeleteRecordData')
		$ParamsArray[] = new SoapParam(1, 'DeleteFiles');
	if ($DoCommit == false)
		$ParamsArray[] = new SoapParam(1, 'ValidateOnly');
	if ($Parms)
	{
		foreach ($Parms as $Key => $Value)
			$ParamsArray[] = new SoapParam($Value, $Key);
	}
	// if running in PAL mode, pass the PALModuleName
	if (isset($_SESSION['PALModules']['CurrentPALModuleName']))
		$ParamsArray[] = new SoapParam($_SESSION['PALModules']['CurrentPALModuleName'], 'PALModuleName');
	$res = $client->__soapCall($Method, $ParamsArray);

	return CheckDBResult($res, $DoCommit);
}

/**
* Delete the specified image. if $TableName is Slide, and the image is the last image for a slide record,
* then that slide record is deleted
* 
* @param string $TableName  - the table name, Slide or Image
* @param int $ImageId		- Id of the image to delete
* @param bool $DoCommit		- Whether to commit the change or just test for permissions
* @param array $Parms		- additional parameters to pass to DataServer
*/
function ADB_TestDeleteImageData($TableName, $ImageId, $Parms=null)
{
	return ADB_DeleteImageData($TableName, $ImageId, false, $Parms);
}
function ADB_DeleteImageData($TableName, $ImageId, $DoCommit = true, $Parms=null)
{
	$client = GetSOAPImageClient ();

	// cascading delete's may take a lot of time so temporarily
	// lengthen the default socket timeout and the max script execution time
	$client->SetTimeout(180);

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($ImageId, 'ImageId');
	$ParamsArray[] = new SoapParam(1, 'DeleteFiles');
	if ($DoCommit == false)
		$ParamsArray[] = new SoapParam(1, 'ValidateOnly');

	// if tablename is Slide, then it is ok for DataServer to delete the slide if this
	// is the last remaining image for that slide. If the table name is not Slide (e.g. Image),
	// then DataServer should not delete the slide, even if this is the last image for that slide.
	$ParamsArray[] = new SoapParam($TableName === 'Slide' ? 1 : 0, 'DeleteEmptySlide');
	if ($Parms)
	{
		foreach ($Parms as $Key => $Value)
			$ParamsArray[] = new SoapParam($Value, $Key);
	}
	$res = $client->__soapCall('DeleteImageData', $ParamsArray);

	return CheckDBResult($res, $DoCommit);
}

/**
* Get a list of all images which belong to a record, and all of its descendants
* 
* @param $TableName		- Name of table to look for the record in
* @param $Id			- Which record to get the images for
*/
function ADB_GetAllImagesForRecord ($TableName, $Id)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TableName,'TableName');
	$ParamsArray[] = new SoapParam($Id,'Id');

	$res = $client->__soapCall(	'GetAllImagesForRecord', $ParamsArray);

	//trigger_error(var_export($res,true),E_USER_ERROR);

	$retArray = array ();

	if (is_object ($res))
		return ReportDataServerError($res);
	elseif (is_object ($res ['Images']) && is_object ($res ['Images']->Image))
		$retArray =  array ($res ['Images']->Image->ImageId => $res ['Images']->Image->CompressedFileLocation);
	elseif (is_object ($res ['Images']) && is_array ($res ['Images']->Image))
		foreach ($res ['Images']->Image as $Image)
			$retArray [$Image->ImageId] = $Image->CompressedFileLocation;

	return $retArray;
}

//------------------------------------------------------------------
// ADB_GetRecordDocuments -  Returns a list of child documents
//		that belong to the specified record parent record.
//------------------------------------------------------------------
function ADB_GetRecordDocuments($ParentId,  $ParentTable)
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($ParentTable,'TableName');
	$ParamsArray[] = new SoapParam($ParentId,'Id');

	$res = $client->__soapCall(	'GetRecordDocuments', $ParamsArray);

	if(is_array($res))
	{
		$DocumentList = array();
		
		if (isset($res['GenericDataSet']->DataRow))
		{
			if(is_array($res['GenericDataSet']->DataRow))
			{
				foreach($res['GenericDataSet']->DataRow as $DocumentData)
				{
					$DocumentList[] = $DocumentData;
				}
			}	
			elseif (is_object($res['GenericDataSet']->DataRow))
			{
				$DocumentList[] = $res['GenericDataSet']->DataRow;
			}
		}
		return $DocumentList;
	}
	if(is_object($res))
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}	
}

//------------------------------------------------------------------
// ADB_GetImageData - get the datafields of the specified image
//------------------------------------------------------------------
function ADB_GetImageData($ImageId, $ReturnOnError = false)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($ImageId, 'ImageId');

	$res = $client->__soapCall(	'GetImageData', $ParamsArray);
	CheckDBResult($res, ($ReturnOnError == false));

	if (is_array($res))
	{
		// the data row comes back as an object, but it's more useful
		// if we turn it into an associative array
		$Arr = array();
		foreach($res['ImageData']->Image as $key => $value) 
		{
		   $Arr[$key] = $value;
		}

		return $Arr;
	}

	return NULL;
}


/**
* Get the location of the image either as a complete path on disk, or return
* just the URL of the imageserver that hosts the image.
* 
* @param int $ImageId	- Id of the image to collect analysis results for
* @param bool $ImageServerURL - return just the url of the appropriate imageserver 
* 
* @return string
* 
*/
function ADB_ResolveImageId ($ImageId, $ImageServerURL = false)
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($ImageId, 'ImageId');
	
	$res = $client->__soapCall('ResolveImageId', $ParamsArray);
 
	if (is_object($res) && $res->ASResult != 0)
		return ReportDataServerError($res);

	if (is_array($res) && $res ['ResolveImageIdResult']->ASResult == 0)
	{  
		if ($ImageServerURL)
			return $res['ImageServerUrl']; 
		else
			return $res['CompressedFileLocationResolved'];
	}
}

/**
* Collect the most recent analysis results for an Image
* 
* @param int $ImageId	- Id of the image to collect analysis results for
* 
* @return array()		- Analysis results
*/
function ADB_GetImageAnalysisData ($ImageId)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($ImageId, 'ImageId');

	$res = $client->__soapCall('GetImageAnalysisData', $ParamsArray);

	//trigger_error(var_export($res,true),E_USER_ERROR);

	if(is_object($res) && $res->ASResult != 0)
		return ReportDataServerError($res);
	elseif(is_array($res) && $res ['GetImageAnalysisDataResult']->ASResult == 0 && isset ($res ['AlgorithmResults']->AlgorithmResult))
	{
		$AnalysisData = array();

		$AnalysisData['AnnotationId'] = $res['AnnotationId'];
		if (!empty ($res['Name']))
			$AnalysisData['Name'] = $res['Name'];
		if (!empty ($res['InputAnnotationId']))
			$AnalysisData['InputAnnotationId'] = $res['InputAnnotationId'];
		if (!empty ($res['InputAnnotationName']))
			$AnalysisData['InputAnnotationName'] = $res['InputAnnotationName'];
		if (is_object ($res ['AlgorithmResults']->AlgorithmResult))
			$AnalysisData['AlgorithmResults'] = array ($res ['AlgorithmResults']->AlgorithmResult);
		else
			$AnalysisData['AlgorithmResults'] = $res ['AlgorithmResults']->AlgorithmResult;
			
		return $AnalysisData;
	}
	else
		return array ();
}

/**
* Collect the most recent manual scores for an Image
* 
* @param int $ImageId	- Id of the image to collect manual scores for
* @param bool $Force	- Whether to force a reload from DataServer
* 
* @return array()		- Scores
*/
function ADB_GetImageScores ($ImageId, $Force = false)
{
	static $ScoreDatas = array ();
	
	if (!isset ($ScoreDatas [$ImageId]) || $Force)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($ImageId, 'ImageId');

		$res = $client->__soapCall('GetScoreData', $ParamsArray);

		if (is_object($res) && $res->ASResult != 0)
			return ReportDataServerError($res);

		elseif (is_array($res) && $res ['GetScoreDataResult']->ASResult == 0 && isset ($res ['Scores']->Score))
		{
			// Modify Single $Scores object to same structure as multiple $Scores object for downstream foreach() processing:
			if (isset ($res['Scores']->Score->DisplayOrder))	// test for a single $Scores object
			{
				$ScoreObject = $res['Scores']->Score;
				unset ($res['Scores']->Score);
				$res['Scores']->Score[0] = $ScoreObject;  
			}
			
			$ScoreDatas [$ImageId] = $res ['Scores']->Score;
		}
		else
			return array ();
	}
	return $ScoreDatas [$ImageId];
}

/**
* Collect the most recent interpretation for an Image
* 
* @param int $ImageId	- Id of the image to collect manual scores for
* 
* @return array()		- Interpretation
*/
function ADB_GetImageInterpretationData ($ImageId)
{
	static $InterpretationDatas = array ();

	if (!isset ($InterpretationDatas [$ImageId]))
	{
		$client = GetSOAPImageClient ();

		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($ImageId, 'ImageId');

		$res = $client->__soapCall('GetInterpretationData', $ParamsArray);

		//trigger_error(var_export($res,true),E_USER_ERROR);

		if(is_object($res) && $res->ASResult != 0)
			return ReportDataServerError($res);
		elseif(is_array($res) && $res ['GetInterpretationDataResult']->ASResult == 0 && isset ($res ['Interpretations']->Interpretation) && isset ($res ['InterpretationConfigs']->InterpretationConfig))
		{
			// downstream processing expects an array of InterpretationConfigs - DataServer returns single object if only one InterpretationConfig found
			if (count($res['InterpretationConfigs']->InterpretationConfig) == 1)
				$InterpretationDatas [$ImageId] = array ($res ['Interpretations']->Interpretation,	
							array($res ['InterpretationConfigs']->InterpretationConfig));
			else
				$InterpretationDatas [$ImageId] = array ($res ['Interpretations']->Interpretation,	
							$res ['InterpretationConfigs']->InterpretationConfig);
		}
		else
		{
			return false;
		}
	}
	return $InterpretationDatas [$ImageId];
}

/**
* Save all manual score and interpretation data
* 
* @param int $ImageId				- Id of the image to save
* @param array () $Interpretations	- Array of InterpretationId=>InterpretationText pairs to save
* @param array () $Scores			- Array of ScoreId=>ScoreValue pairs to save
*/
function ADB_UpdateScoreData ($ImageId, $Interpretations = array (), $Scores = array ())
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($ImageId, 'ImageId');

	$DOM = new DOMDocument ();

	$InterpretationsNode = $DOM->createElement ('Interpretations');
	foreach ($Interpretations as $Id => $Text)
	{
		$Node = $InterpretationsNode->appendChild ($DOM->createElement ('Interpretation'));
		
		$Node->appendChild ($DOM->createElement ('Id'))->appendChild ($DOM->createTextNode ($Id));
		$Node->appendChild ($DOM->createElement ('InterpretationText'))->appendChild ($DOM->createTextNode ($Text));
	}

	$ParamsArray [] = new SoapVar ($DOM->saveXML ($InterpretationsNode), 147);

	$ScoresNode = $DOM->createElement ('Scores');
	foreach ($Scores as $Id => $Text)
	{
		$Node = $ScoresNode->appendChild ($DOM->createElement ('Score'));
		
		$Node->appendChild ($DOM->createElement ('Id'))->appendChild ($DOM->createTextNode ($Id));
		$Node->appendChild ($DOM->createElement ('Value'))->appendChild ($DOM->createTextNode ($Text));
	}

	$ParamsArray [] = new SoapVar ($DOM->saveXML ($ScoresNode), 147);

	$res = $client->__soapCall('UpdateScoreData', $ParamsArray);

	ReportDataServerError($res);
}

//------------------------------------------------------------------
// ADB_GetAnnotationLayerAttributes - get the annotation layer
// 		attributes for the specified ImageId.  Does not include
//		region info.
//------------------------------------------------------------------
function ADB_GetAnnotationLayerAttributes($ImageId)
{
	$client = GetSOAPImageClient ();
											
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($ImageId, 'ImageId');
	
	$res = $client->__soapCall(	'GetAnnotationLayerAttributes',		//SOAP Method Name
								$ParamsArray);						//Parameters
	
	$Annotations = array();
	if(is_array($res))
	{
		if($res['GetAnnotationLayerAttributesResult']->ASResult == 0)
		{
			// make sure Annotation is an array
			$res['AnnotationsNode']->Annotations->Annotation = MakeArray($res['AnnotationsNode']->Annotations->Annotation);
			
			// for each annotation make sure Attribute is an array
			foreach ($res['AnnotationsNode']->Annotations->Annotation as $AnnotationNode)
			{
				$Annotation = new cAnnotation();
				$Annotations[] = $Annotation;
				$Annotation->Id = $AnnotationNode->AnnotationId;
				if (!empty ($AnnotationNode->ImageId))
					$Annotation->ImageId = $AnnotationNode->ImageId;
				if (!empty ($AnnotationNode->Name))
					$Annotation->Name = $AnnotationNode->Name;
				if (!empty ($AnnotationNode->InputAnnotationName))
					$Annotation->InputAnnotationName = $AnnotationNode->InputAnnotationName;
				if (!empty ($AnnotationNode->MacroVersion))
					$Annotation->MacroVersion = $AnnotationNode->MacroVersion;
				
				// some additional logic here in case there are no attributes
				if (property_exists($AnnotationNode, 'Attributes'))
				{
					if (isset($AnnotationNode->Attributes))
					{
						if (property_exists($AnnotationNode->Attributes, 'Attribute'))
						{
							$AnnotationNode->Attributes->Attribute = MakeArray($AnnotationNode->Attributes->Attribute);
							foreach($AnnotationNode->Attributes->Attribute as $AttributeNode)
							{
								$Annotation->Attributes[] = new cAttribute($AttributeNode->Name, $AttributeNode->Value);
							}
						}
					}
				}
			}
		}			
	}
	
	if(is_object($res))
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}	
	
	return $Annotations;
}

//------------------------------------------------------------------
// ADB_GetAnnotationXML - get the annotation layer xml for the specified $Id
//		$Parent should be 'ImageId' , 'GenieTrainingSetId ' or 'AnnotationTemplateId '
//		$LayerType - optional parameter to specify annotation layer type 
//------------------------------------------------------------------
function ADB_GetAnnotationXML($Id, $Parent, $LayerType = -1)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($Id, $Parent);
	$ParamsArray[] = new SoapParam('true', 'ResultAsString');

	if ($LayerType != -1)
	{
		// filter by layer type, e.g. type 8 is report region layer
		$ParamsArray[] = new SoapParam($LayerType, 'Type');
	}

	$res = $client->__soapCall('GetAnnotations', $ParamsArray);

	if (is_array($res))
	{
		if ($res['GetAnnotationsResult']->ASResult == 0)
		{
			return $res['AnnotationsNode'];
		}	
	}

	return ReportDataServerError($res);
}

//------------------------------------------------------------------
// ADB_PutAnnotations($annotationXML) - save annotations in database
//------------------------------------------------------------------
function ADB_PutAnnotations($annotationXML)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapVar($annotationXML, XSD_ANYXML); 	//147

	$res = $client->__soapCall('PutAnnotations2', $ParamsArray);

	if (is_object($res))
	{
		if ($res->ASResult == 0)
			return true;
	}
	$res = $res['PutAnnotations2Result'];
	if (is_object($res))
	{
		if ($res->ASResult == 0)
			return true;
	}
	

	return ReportDataServerError($res);
}


//------------------------------------------------------------------
// ADB_GetNextAnnotationId() - ask dataserver to add a new (empty) annotation
//      to the image and return the annotation id.
//------------------------------------------------------------------
function ADB_GetNextAnnotationId($ImageId)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($ImageId, 'ImageId');

	$res = $client->__soapCall('GetAnnotationId', $ParamsArray,
								NULL, NULL, $OutputHeaders, true);//Only Execute once

	if (is_array($res))
	{
		if( $res['GetAnnotationIdResult']->ASResult == 0)
		{
			return $res['AnnotationIdList']->Id;
		}            
	}

	return ReportDataServerError($res);
}


/*************************************************
 * ADB_PutImageData2 - update specified row in the image table, or
 *	if no id is specified then add a new row.  The image will also be
 *	joined with the specified parent.
 * 04/08/08 msmaga	ESIG handling
 *************************************************/
function ADB_PutImageData2($NameValues, $ImageId=-1, $ParentTable="", $ParentId="")
{
	$ParamsArray = GetAuthVars();
	if ($ParentTable != '')
		$ParamsArray[] = new SoapParam($ParentTable,'ParentTable');
	if ($ParentId != '')
		$ParamsArray[] = new SoapParam($ParentId,'ParentId');

	$ImageDataXML = '<ImageData><Image>';
	if ($ImageId > -1)
	{
		$ImageDataXML = $ImageDataXML . "<ImageId>$ImageId</ImageId>";
	}
	foreach ($NameValues as $Name=>$Value)
	{
		$Value = htmlspecialchars($Value, ENT_QUOTES);
		$ImageDataXML = $ImageDataXML . "<$Name>$Value</$Name>";
	}
	$ImageDataXML = $ImageDataXML . '</Image></ImageData>';
	$ParamsArray[] = new SoapVar($ImageDataXML, 147);

	$client = GetSOAPImageClient ();

	$res = $client->__soapCall(	'PutImageData2',	//SOAP Method Name
								$ParamsArray,		//Parameters
								NULL, NULL, $OutputHeaders, $ImageId == -1); // Only execute once if it's a new Image

	//print_r($res);
	if (is_array($res))
	{
		if($res['PutImageData2Result']->ASResult == 0)
			return $res['ImageData']->ImageId;
		if ($res['ASResult'] == ESIG_NOT_VALID)
			return ESIG;
		return $res['ASResult'];
	}
	if(is_object($res))
	{
		if(($res->ASResult == ESIG) || ($res->ASResult == ESIG_NOT_VALID))
			return ESIG;
		if ($res->ASResult == EXCEEDED_IMAGES)
		{
			$_SESSION['ErrorType'] = 'SystemError';
			trigger_error("The maximum number of images has been exceeded");
		}
		else
			return ReportDataServerError($res);
		return NULL;
	}
}


//------------------------------------------------------------------
// ADB_AddAnalysisJob -  submits and analysis job using the specified
//	macro on the specified image.  DataServer's logic for determining 
//	the input annotation is as follows:
/*
If InputAnnotationId is not specified
	If AnnotationLayerIndex is not specified
		Return error
	Else
		If layer index is <= 0 or > number of layers associated with the image
			Return error
		Else
			Use the 1 based layer index to determine InputAnnotatioId to be used
Else if inputannotationid == -1
	Use last man-made layer
Else use specified inputannotationid
*/
//
//------------------------------------------------------------------
//function ADB_AddAnalysisJob($ImageId, $MacroIds, $CreateMarkupImage = "1", $Priority = "1", $InputAnnotationId = null, $InputAnnotationIndex = null)
function ADB_AddAnalysisJobs($ImageIds = array(), $MacroId, $CreateMarkupImage = '1', $Priority = '1', $InputAnnotationId = null, $InputAnnotationIndex = null)
{
	// create the AddJobInfo XML
	$AddJobInfoXML = '';
	$dom = new DOMDocument();
	foreach ($ImageIds as $ImageId)
	{
		$ndAddJobInfo = $dom->CreateElement('AddJobInfo');
		$ndAddJobInfo->appendChild($dom->CreateElement('ImageId', $ImageId));
		$ndAddJobInfo->appendChild($dom->CreateElement('MacroId', $MacroId));
		$ndAddJobInfo->appendChild($dom->CreateElement('CreateMarkup', $CreateMarkupImage));
		if (isset ($InputAnnotationId))
			$ndAddJobInfo->appendChild($dom->CreateElement('InputAnnotationId', $InputAnnotationId));
		elseif (isset ($InputAnnotationIndex))
			$ndAddJobInfo->appendChild($dom->CreateElement('AnnotationLayerIndex', $InputAnnotationIndex));
		
		$AddJobInfoXML .= $dom->saveXML($ndAddJobInfo);
	}
	
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapVar($AddJobInfoXML, 147);
	
	$res = $client->__soapCall(	'AddNewJob',		//SOAP Method Name
								$ParamsArray,		//Method Parameters
								NULL, NULL, $OutputHeaders, true); // Only execute once

	if (is_array($res))
	{
		if($res['AddNewJobResult']->ASResult == 0)
		{
			return $res['JobQueueId'];
		}			
	}

	if ($res->ASResult == EXCEEDED_RUNS)
		trigger_error("The maximum number of algorithm runs has been exceeded");

	return ReportDataServerError($res);
}

/**
* ADB_GetReportTemplates
* @descr	Returns array of templates
* @params	string TableName, report type - 'Case' or 'Project'
*			string CustomerId
* @return	array
* 
**/
// get report templates for $TableName for a specified customer id
// if $CustomerId == '', then all report templates of the requested type are returned

function ADB_GetReportTemplates ($TableName, $CustomerId = '')
{
	$Templates = array();
	if ($CustomerId != '')
	{
		// get report templates for specified customer
		$Temp = ADB_GetFilteredRecordList ('ReportTemplateCustomer', 0, 0, 
			array ('UpToReportTemplateByReportTemplateId.Id', 'UpToReportTemplateByReportTemplateId.Name', 'UpToReportTemplateByReportTemplateId.Description', 'UpToReportTemplateByReportTemplateId.Version'),
			array ('CustomerId', 'UpToReportTemplateByReportTemplateId.Type'), array ("=", "="), array ($CustomerId, $TableName), array ('ReportTemplateCustomer', 'ReportTemplate'), 'UpToReportTemplateByReportTemplateId.Description', 'Ascending');
		foreach($Temp as $T)
		{
			// convert the UpTo... column names to the actual column names
			$Templates[] = array('Id' => $T['UpToReportTemplateByReportTemplateId.Id'], 'Name' => $T['UpToReportTemplateByReportTemplateId.Name'], 'Description' => $T['UpToReportTemplateByReportTemplateId.Description'], 'Version' => $T['UpToReportTemplateByReportTemplateId.Version']);
		}
	}
	else
		// get all report templates for $TableName
		$Templates = ADB_GetFilteredRecordList ('ReportTemplate', 0, 0, array(), array('Type'), array('='), array($TableName), array('ReportTemplate'), 'Description', 'Ascending'); 

	return $Templates;
}


/**
* ADB_GetReportTemplateAssignments
* @descr	 Returns array of templates and assigned status for customer
* @params	string TableName, report type - 'Case' or 'Project'
*			string CustomerId
* @return	array
* 
**/
function ADB_GetReportTemplateAssignments($TableName, $CustomerId)
{
	// get the ids of the templates assigned to this customer
	$Assigneds = ADB_GetFilteredRecordList ('ReportTemplateCustomer', 0, 0, array(), array('CustomerId'), array('='), array($CustomerId), array('ReportTemplateCustomer'));
	// set ['Assigned'] for each assigned template
	return BuildAssignedTemplates($TableName, $Assigneds);
}
/**
* ADB_GetReportTemplateAssignmentsByCustomerName
* @descr	 Returns array of templates and assigned status - queried by customer name
* @params	string TableName, report type - 'Case' or 'Project'
*			string CustomerName
* @return	array
* 
**/
function ADB_GetReportTemplateAssignmentsByCustomerName($TableName, $CustomerName)
{
	// get the ids of the templates assigned to this customer (by name)
	$Assigneds = ADB_GetFilteredRecordList ('ReportTemplateCustomer', 0, 0, array(), array('UpToCustomersByCustomerId.Name'), array('='), array($CustomerName), array('ReportTemplateCustomer'));
	// set ['Assigned'] for each assigned template
	return BuildAssignedTemplates($TableName, $Assigneds);
}
/**
* internal function to returned assigned templates,
* shared by ADB_GetReportTemplateAssignments and ADB_GetReportTemplateAssignmentsByCustomerName
**/
function BuildAssignedTemplates($TableName, $Assigneds)
{
	// get all report templates for $TableName
	$Templates = ADB_GetReportTemplates ($TableName);
	// set ['Assigned'] for each assigned template
	foreach($Templates as $key => $Template)
	{
		$Templates[$key]['Assigned'] = '0';
		foreach($Assigneds as $Assigned)
		{
			if ($Assigned['ReportTemplateId'] == $Templates[$key]['Id'])
			{
				$Templates[$key]['Assigned'] = '1';
				break;
			}
		}
	}
	return $Templates;
}
//------------------------------------------------------------------
// ADB_GetPendingAnalysisJobs -  Returns a list of analysis jobs
// 		which are either "Submitted" or "In Progress"
//------------------------------------------------------------------
function ADB_GetPendingAnalysisJobs()
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam('0', 'StartingId');
	$ParamsArray[] = new SoapParam('3000', 'MaxCount');
	
	$res = $client->__soapCall(	'ListPendingJobs',		//SOAP Method Name
								$ParamsArray);			//Parameters

	$JobList = array();

	if(is_array($res))
	{
		if (isset($res['JobDataArray']->JobData))
		{
			$JobList = MakeArray($res['JobDataArray']->JobData);
		}
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}

	return $JobList;
}

//------------------------------------------------------------------
// ADB_ListJobs -  list jobs with ImageID or JobId
//------------------------------------------------------------------
function ADB_ListJobs($ImageId = null, $JobId = null)
{
	$client = GetSOAPImageClient ();

	$ParmsArray = GetAuthVars();

	if( isset($ImageId))
		$ParmsArray[] = new SoapParam($ImageId, 'ImageId');

	if( isset($JobId))
		$ParmsArray[] = new SoapParam($JobId, 'JobQueueId');
		
	$res = $client->__soapCall(	'ListJobs', $ParmsArray);

	$JobList = array();

	if(is_array($res))
	{
		if (isset($res['JobDataArray']->JobData))
		{
			$JobList = MakeArray($res['JobDataArray']->JobData);
		}
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}

	return $JobList;
}


//------------------------------------------------------------------
// ADB_CancelAnalysisJobs -  Cancels the specified analysis jobs
//------------------------------------------------------------------
function ADB_CancelAnalysisJobs($JobIds = array())
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();

	foreach ($JobIds as $JobId)
		$ParamsArray[] = new SoapParam($JobId, 'JobId');

	$res = $client->__soapCall(	'CancelJob',		//SOAP Method Name
								$ParamsArray);		//Parameters

	if($res->ASResult != 0)
	{
		return ReportDataServerError($res);
	}
}

//------------------------------------------------------------------
// ADB_GetAnalysisMacros -  gets the list of macros
//------------------------------------------------------------------
function ADB_GetAnalysisMacros()
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam('0', 'StartingId');
	$ParamsArray[] = new SoapParam('1000', 'MaxCount');

	$res = $client->__soapCall(	'ListMacros',		//SOAP Method Name
								$ParamsArray);		//Parameters

	$MacroList = array();
	if(is_array($res))
	{
		if (isset($res['MacroDataArray']->MacroData))
		{
			$MacroList = MakeArray($res['MacroDataArray']->MacroData);
		}
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}

	return $MacroList;
}

function ADB_GetFreeTextSearch($startingRowNumber, $endingRowNumber, $recordsPerPage, $searchString, $workflow)
{
	$client = GetSOAPImageClient();
	$SoapVars = GetAuthVars();
	$freeTextSearchXml = "<SearchString>". xmlencode(EscapeSQLchars($searchString)) ."</SearchString>";
	if ($startingRowNumber != null)
	{
		$freeTextSearchXml .= "<StartingRowNumber>$startingRowNumber</StartingRowNumber>";
	}
	if ($endingRowNumber != null)
	{
		$freeTextSearchXml .= "<EndingRowNumber>$endingRowNumber</EndingRowNumber>";
	}
	if ($recordsPerPage != null)
	{
		$freeTextSearchXml .= "<RecordsPerPage>$recordsPerPage</RecordsPerPage>";
	}
	if ($workflow != null)
	{
		$freeTextSearchXml .= "<Workflow>". xmlencode($workflow) ."</Workflow>";
	}
	$SoapVars[] = new SoapVar($freeTextSearchXml, 147);
	$res = $client->__soapCall("GetFreeTextSearch", $SoapVars);
	if (!is_array($res))
	{
		return ReportDataServerError($res);
	}
	if ($res["SearchResultDocument"]->IsSearchConfigured == "false")
	{
		return false;
	}
	$freeTextSearchResult[] = $res["SearchResultDocument"]->IsLastPage;
	$freeTextSearchResult[] = $res["SearchResultDocument"]->NextRowNumber;
	$freeTextSearchResult[] = $res["SearchResultDocument"]->Tokens;
	// make sure all the Fields are arrays. If there is only one field,
	// it will be an object, rather than an array with a single element that is an object
	if (isset($res["SearchResultDocument"]->SearchResults->SearchResult))
	{
		foreach ($res["SearchResultDocument"]->SearchResults->SearchResult as $SearchResult)
		{
			if (isset($SearchResult->Entities))
			{
				foreach ($SearchResult->Entities as $Entity)
				{
					if (isset($Entity))
					{
						foreach ($Entity as $Ent)
						{
							if (isset($Ent->Fields->Field))
							{
								if (is_array($Ent->Fields->Field) == false)
								{
									$tempField = $Ent->Fields->Field;
									$Ent->Fields->Field = array();
									$Ent->Fields->Field[] = $tempField;
									}
							}
						}		
					}
				}
			}
		}
	}
	$freeTextSearchResult[] = $res["SearchResultDocument"]->SearchResults;
	return $freeTextSearchResult;
}

/**
 * @descr 	Retrieves the XML associated with a Macro for Export.
 * @params  int $macroId  -- macro primary key
 * @return  string with appropriate XML.
 */
function ADB_GetMacroXML($macroID)
{
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($macroID, 'MacroId');
	$ParamsArray[] = new SoapParam('true', 'ResultAsString');

	$client = GetSOAPImageClient ();
	$res = $client->__soapCall('GetMacroInfo2', $ParamsArray);
	if (!is_array($res))
	{
		return ReportDataServerError($res);
	}
	return "<Algorithms>" . $res["MacroInfo"]->Algorithms . "</Algorithms>";
}

//------------------------------------------------------------------
// ADB_AddAnalysisMacro -  add a macro to the database.  Give it the 
//		specified name, and the specified MacroXML.
//------------------------------------------------------------------
function ADB_AddAnalysisMacro($MacroName, $DataGroupID, $MacroXml)
{
	$client = GetSOAPImageClient ();

	$MacroName = xmlencode ($MacroName);
	
	$DataGroupID = xmlencode ($DataGroupID);

	$MacroInfoXml = "<MacroInfoNode><MacroInfo Name=\"$MacroName\" DataGroupId=\"$DataGroupID\">$MacroXml</MacroInfo></MacroInfoNode>";

	$SoapVars = GetAuthVars();
	$SoapVars[] = new SoapVar($MacroInfoXml, 147);

	$res = $client->__soapCall(	'AddNewMacro',		//SOAP Method Name
								$SoapVars);			//Parameters

	if(is_array($res))
	{
		if($res['AddNewMacroResult']->ASResult == 0)
		{
			return $res['MacroId'];
		}			
	}
	if(is_object($res))
	{
		// Check for the following errors:
		// -4041 Parameter not found
		// -4027 Duplicate macro name
		// -7020 For permmision error
		// -7057 For data group permissions
		if($res->ASResult == -4041 || $res->ASResult == -4027 || $res->ASResult == -7020 || $res->ASResult == -7057)
		{
			// make the error message shorter
			return TidyUpDataServerErrorMessage($res->ASMessage);
		}
		elseif($res->ASResult != 0)
		{
			SetError('Invalid Macro file');
			header('Location: /AddAnalysisMacro.php');
			exit();
		}
	}
}

/**
 * @descr 	Calls custom DataServer routine to Edit/Add a new Macro record.
 * @params  int $MacroId  -- if Edit, pass in a macroId, else -1
 * @params	string $MacroName -- Macro name string.
 * @params	int $DataGroupId -- Data Group to which this Macro will be assigned.
 * @return  int the projectId if exists, or null or error
 */
function ADB_EditAnalysisMacro($MacroID, $MacroName, $DataGroupID)
{
	$MacroID = xmlencode($MacroID);
	$MacroName = xmlencode($MacroName);
	$DataGroupID = xmlencode($DataGroupID);
	$MacroXml = ADB_GetMacroXML($MacroID);
	$MacroInfoXml = "<MacroInfoNode><MacroInfo MacroId=\"$MacroID\" Name=\"$MacroName\"  DataGroupId=\"$DataGroupID\">$MacroXml</MacroInfo></MacroInfoNode>";
	$SoapVars = GetAuthVars();
	$SoapVars[] = new SoapVar($MacroInfoXml, 147);
	$client = GetSOAPImageClient();
	$res = $client->__soapCall('AddNewMacro', $SoapVars);
	if(isset($res->ASResult))
	{            
		if ($res->ASResult != 0)
		{                            
			return ReportDataServerError($res);
		}
	}
}

//function ADB_DeleteBulkAnalysisMacros($macroIdXml)
//{
//    $SoapVars = GetAuthVars();
//    $SoapVars[] = new SoapVar($macroIdXml, 147);
//    $client = GetSOAPImageClient();
//    $result = $client->__soapCall('DeleteMacroBulk', $SoapVars);
//    if(isset($result->ASResult))
//    {            
//        if ($result->ASResult != 0)
//        {                            
//            return ReportDataServerError($result);
//        }
//    }
//}

//------------------------------------------------------------------
// ADB_DeleteAnalysisMacro - Delete the macro specified by MacroId
//------------------------------------------------------------------
function ADB_DeleteAnalysisMacro($MacroId)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($MacroId, 'MacroId');

	$res = $client->__soapCall(	'DeleteMacro',		//SOAP Method Name
								$ParamsArray);		//Parameters

	if($res->ASResult != 0)
	{
		return ReportDataServerError($res);
	}

	RefreshImageServers('MACROLIST');
}


//------------------------------------------------------------------
// ADB_ListDataGroups -  Returns a 2d array of datagroup data.  If
//		PrivateOnly=1 then this returns only the private datagroups
//		created by this user.
//------------------------------------------------------------------
function ADB_ListDataGroups($PrivateOnly=0)
{
	$client = GetSOAPSecurityClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($PrivateOnly, 'PrivateOnly');

	$res = $client->__soapCall(	'ListDataGroups',		//SOAP Method Name
								$ParamsArray);			//Parameters

	if(is_array($res))
	{
		if (isset($res['DataGroupArray']->DataGroup))
		{
			if (is_array($res['DataGroupArray']->DataGroup))
			{
				return $res['DataGroupArray']->DataGroup;
			}
			else
			{
				$arr = array();
				$arr[] = $res['DataGroupArray']->DataGroup;
				return $arr;
			}
		}
		else
			return array();
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}	
}


//------------------------------------------------------------------
// ADB_DeleteDataGroup - Delete the specified datagroup 
//      with optional roll back.
//------------------------------------------------------------------
function ADB_DeleteDataGroup($DataGroupId, $ValidateOnly=0)
{
	$client = GetSOAPSecurityClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($DataGroupId, 'DataGroupId');
	if ($ValidateOnly)
		$ParamsArray[] = new SoapParam(1, 'ValidateOnly');

	$res = $client->__soapCall(	'DeleteDataGroup', $ParamsArray);

	if ($res->ASResult != 0)
	{
		return ReportDataServerError($res);
	}
	
	return 0;
}


// XXX Deprecated in 10.3
function ADB_PutDataFields ($TableName, $FieldNames, $DisplayNames, $Positions, $AuditAccesses, $Vocabularies)
{
	$client = GetSOAPImageClient ();

	$DataFieldsXML = '<DataFieldsArray>';

	foreach ($FieldNames as $FieldName)
	{
		$XmlEncodedDisplayName = xmlencode($DisplayNames[$FieldName]);
		$XmlEncodedVocabulary = xmlencode($Vocabularies[$FieldName]);
		$Visible = $Positions [$FieldName] == 0 ? 'False' : 'True';
		$AuditAccess = isset($AuditAccesses[$FieldName]) ? 1 : 0;
   
		$DataFieldsXML .=
			'<DataField>' .
				"<TableName>$TableName</TableName>" .
				"<ColumnName>$FieldName</ColumnName>" .
				"<DisplayName>$XmlEncodedDisplayName</DisplayName>" .
				"<Visible>$Visible</Visible>" .
				"<Position>{$Positions[$FieldName]}</Position>" .
				"<AuditAccess>$AuditAccess</AuditAccess>" .
				"<Vocabulary>$XmlEncodedVocabulary</Vocabulary>" .
			'</DataField>';
	}

	$DataFieldsXML .= '</DataFieldsArray>';

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapVar($DataFieldsXML, 147);

	$res = $client->__soapCall('PutDataFields', $ParamsArray);

	if(($res->ASResult == ESIG) || ($res->ASResult == ESIG_NOT_VALID))
		return ESIG;
	else if($res->ASResult != 0)
	{
		return ReportDataServerError($res);
	}
}

// Return an array of hierarchy types indexed by 'arrayKey'
// Note: this is Spectrum's complete list, use GetDataHierarchyTypes() for the licensed list.
function ADB_GetDataHierarchyTypes($arrayKey)
{
	$Records = ADB_GetTable(NULL, 'DataHierarchy', NULL, NULL, NULL, NULL);
	$Hierarchies = array();
	foreach ($Records as $Record)
		$Hierarchies[$Record->$arrayKey] = $Record;
	return $Hierarchies;
}


//------------------------------------------------------------------
//  Returns the requested hierarchy table names and ids
//------------------------------------------------------------------
function ADB_GetDataHierarchy($HierarchyName, $IncludeDocuments, $IncludeReports)
{
	// The DataServer should hold this information, but for now it is hard coded.
	// Note: order is important, it controls the Masthead's order
	if ($HierarchyName == NULL)
	{
		// GetTable (sort by Level)
		$TableNames = array('Case', 'Project', 'Course', 'Lesson',
			'GenieProject', 'GenieTrainingSet',
			'Specimen', 'Slide', 'Image',
			'TMA', 'Core', 'Spot');
	}
	else if ($HierarchyName == 'Clinical')
	{
		// GetTable (Filter by HierarchyId = 'Clinical')
		$TableNames = array('Case',
			'Specimen', 'Slide', 'Image',
			'TMA', 'Core', 'Spot');
	}
	else if ($HierarchyName == 'Research')
	{
		$TableNames = array('Project',
			'Specimen', 'Slide', 'Image',
			'TMA', 'Core', 'Spot');
	}
	else if ($HierarchyName == 'Educational')
	{
		$TableNames = array('Course', 'Lesson',
			'Specimen', 'Slide', 'Image',
			'TMA', 'Core', 'Spot');
	}
	else if ($HierarchyName == 'Genie')
	{
		$TableNames = array('GenieProject', 'GenieTrainingSet',
			'Slide', 'Image');
	}
	else // if ($HierarchyName == 'None')
	{
		// Vanilla Spectrum
		$TableNames = array('Slide', 'Image');
	}
	
	if ($IncludeDocuments)
		$TableNames[] = 'Documents';

	if (($IncludeReports == true) && (IsLicensed('Report') == true))
	{
		if (($HierarchyName == NULL) || ($HierarchyName == 'Clinical') || ($HierarchyName == 'Research'))
			$TableNames[] = 'Reports';
	}


	if (IsLicensed('Health Care Suite') == true)
	{
		$TableNames[] = 'VwCaseInfo';
		$TableNames[] = 'VwCaseResults';
		$TableNames[] = 'VwCaseAggregates';
		$TableNames[] = 'VwSpecimenAggregates';
	}

	$Tables = array();
	$Components = $_SESSION['Components'];
	foreach ($TableNames as $TableName)
	{
		$Table = new stdClass();
		$Table->Name = $TableName;
		if (isset($Components[$TableName]))
			$Table->ComponentId = $Components[$TableName]->Id;
		$Tables[$TableName] = $Table;
	}

	// Only return the licensed components for the hierarchy
	if (IsLicensed('TMA') == false)
	{
		unset ($Tables['TMA']);
		unset ($Tables['Core']);
		unset ($Tables['Spot']);
	}
	if (IsLicensed('Genie') == false)
	{
		unset ($Tables['GenieProject']);
		unset ($Tables['GenieTrainingSet']);
	}

	return $Tables;
}


//------------------------------------------------------------------
// ADB_GetDataFields -  Gets the table's display properties.
//------------------------------------------------------------------
// XXX Deprecated in 10.3
function ADB_GetDataFields($TableName)
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	
	$res = $client->__soapCall(	'GetDataFields', $ParamsArray);

	if (is_array($res))
	{
		$DataFields = array();

		if (isset($res['DataFieldsArray']->DataField))
		{			
			if (is_object($res['DataFieldsArray']->DataField))
			{	
				$DataFields[] = $res['DataFieldsArray']->DataField;
			}
			if (is_array($res['DataFieldsArray']->DataField))
			{
				$DataFields = $res['DataFieldsArray']->DataField;
			}
		}

		// UCV - remove for UCV (user configurable views)
		foreach($DataFields as $DataField)
		{
			$DataField->TableName = $TableName;
		}
		// UCV

		return $DataFields;
	}
	// else
	return ReportDataServerError($res);
}

// Return list of Component Names sorted and indexed by the passed columnName
function ADB_GetComponents($SortColumnName = 'Id', $ComponentTypes = NULL)
{
	$Sort = new cSortField($SortColumnName);

	$Filters = array();
	if ($ComponentTypes)
		$Filters[] = new cFilter('RoleComponent', 'ComponentTypeId', 'in', join(",", $ComponentTypes));

	$Records = ADB_GetTable(NULL, 'RoleComponent', $Filters, NULL, NULL, $Sort);

	$Components = array();
	foreach ($Records as $Record)
		$Components[$Record->$SortColumnName] = $Record;
	return $Components;
}

/**
* Provides the Config array to the user
* 
* Config values are loaded first from the defaults array, then from
* the local Specrtum.ini file, and finally from the Config table in
* the database.  Each layer overrides settings from anything before
* it.  As well, defaults and the ini file are loaded only once each
* session and the database table is requested once per page-load
*/
function ADB_GetConfigValues()
{
	// If it hasn't been loaded for the session yet, grab all the defaults
	if (!isset ($_SESSION ['Config']))
	{
		$_SESSION ['Config'] = GetConfigDefaults();	// Start with the defaults
	}

	unset($_SESSION['Config']['ini']);	// Ensure any new settings are captured
	IniSettings();

		$authenticated = isset($_SESSION['AuthToken']);
		if (!$authenticated)
		{
			// If not authentiecated yet, pass blank token and DB returns only login vars for theme and newsFeedURL
			$_SESSION['AuthToken'] = '';
		}

		$client = GetSOAPSecurityClient ();
		$res = $client->__soapCall('GetConfigValues', GetAuthVars());

		CheckDBResult($res, true);

		if (is_array ($res) && is_array ($res['PropertyArray']->Property))
		{
			foreach ($res ['PropertyArray']->Property as $pair)
				$_SESSION ['Config'][$pair->Name] = $pair->Value;
		}

		if ($authenticated)
		{
			// Transformations

			$HierarchyNames = GetDataHierarchyNames();
			$_SESSION['Config']['ReasonForChange'] = false;
			foreach ($HierarchyNames as $Hierarchy)
			{
				if (isset($_SESSION['Config']['ReasonForChangePrompt' . $Hierarchy]) && ($_SESSION['Config']['ReasonForChangePrompt' . $Hierarchy] != 'Never'))
				{
					$_SESSION['Config']['ReasonForChange' . $Hierarchy] = true;
					if (GetDataHierarchyName() == $Hierarchy)
						$_SESSION['Config']['ReasonForChange'] = true;
				}
				else
					$_SESSION['Config']['ReasonForChange' . $Hierarchy] = false;
			}

			if (IsConfigured('Genie'))
			{
				// Override setting for genie
				$_SESSION['Config']['EnableEvents'] = 1;
			}
		}
		else
		{
			unset ($_SESSION['AuthToken']);
		}
		
		if($_SESSION['Config']['Theme'] == 'default')
		{
			$_SESSION['Config']['Theme'] = GetDefaultTheme();
		}

	return $_SESSION['Config'];
}

/******************************************************
 * ADB_SetConfigValue - Sets a Config setting in DataServer's Config table
 ******************************************************/
function ADB_SetConfigValue($Name, $Value)
{
	$client = GetSOAPSecurityClient ();
 
	$ParamsArray = GetAuthVars();

	$NameValuePairXML = "<Property><Name>$Name</Name><Value>$Value</Value></Property>";
	$ParamsArray[] = new SoapVar($NameValuePairXML, 147);

	$res = $client->__soapCall('SetConfigValue', $ParamsArray);

	CheckDBResult($res, true);
}


// Return array with Name Values for the given component
// Param $AllUsers  if set to true will return all users from the userConfig
// $WithValueSet if set true then will perform filter with Value not nul
function ADB_GetUserConfigMultiSort($Name, $UserId = NULL, $AllUsers = false, $WithValueSet = false)
{
	if ($UserId == NULL)
		$UserId = $_SESSION['UserId'];

	$Filters = array();
	$Filters[] = new cFilter('UserConfig', 'Component', '=', 'MultiColSort');
	if ($WithValueSet)
	{
		$Filters[] = new cFilter('UserConfig', 'VALUE', 'IsNotNull','');	
	}
	if (!$AllUsers)
	{
		$Filters[] = new cFilter('UserConfig', 'UserId', '=', $_SESSION['UserId']);
	}    
	$Filters[] = new cFilter('UserConfig', 'Name', '=', $Name);

	$Records = ADB_GetTable('GetFilteredRecordList', 'UserConfig', $Filters, NULL, NULL, NULL);
	
	$Record ="";
   
   if ($AllUsers)
   {
	   return $Records;
   }  
	if(count($Records)) 
		$Record = $Records[0]->Value;
	
	
	return $Record;
}

// Return array with Name Values for the given component
function ADB_GetUserConfig($ComponentName, $UserId = NULL)
{
	if ($UserId == NULL)
		$UserId = $_SESSION['UserId'];

	$Filters = array();
	$Filters[] = new cFilter('UserConfig', 'Component', '=', 'Spectrum');
	$Filters[] = new cFilter('UserConfig', 'UserId', '=', $_SESSION['UserId']);
	$Filters[] = new cFilter('UserConfig', 'Name', '=', $ComponentName);

	$Records = ADB_GetTable('GetFilteredRecordList', 'UserConfig', $Filters, NULL, NULL, NULL);

	$OptionArray = array();
	if (is_array($Records) && (count($Records) > 0))
	{
		$Record = $Records[0];
		$List = explode(',', $Record->Value);
		// Key/Values are stored as a string: key1:val1,key2:val2
		foreach ($List as $Option)
		{
			$KeyValue = explode(':', $Option);
			if (count($KeyValue) == 2)
				$OptionArray[$KeyValue[0]] = $KeyValue[1];
		}
		// Save the recordId as part of the options to easily reset in SetUserConfig()
		$OptionArray['Id'] = $Record->Id;
	}
	return $OptionArray;
}

// Return user config value string. Differs from ADB_GetUserConfig in that
// the options are a string and not Key/Value pairs
function ADB_GetUserConfigString($ComponentName, $UserId = NULL)
{
	if ($UserId == NULL)
		$UserId = $_SESSION['UserId'];

	$Filters = array();
	$Filters[] = new cFilter('UserConfig', 'Component', '=', 'Spectrum');
	$Filters[] = new cFilter('UserConfig', 'UserId', '=', $_SESSION['UserId']);
	$Filters[] = new cFilter('UserConfig', 'Name', '=', $ComponentName);

	$Records = ADB_GetTable('GetFilteredRecordList', 'UserConfig', $Filters, NULL, NULL, NULL);

	$Options = array();
	if (is_array($Records) && (count($Records) > 0))
	{
		$Record = $Records[0];
		$Options['Value'] = $Record->Value;
		// Save the recordId as part of the options to easily reset in SetUserConfigString()
		$Options['Id'] = $Record->Id;
	}
	return $Options;
}


function ADB_SetUserConfig($ComponentName, $OptionArray, $UserId = NULL)
{
	if ($UserId == NULL)
		$UserId = $_SESSION['UserId'];

	if (isset($OptionArray['Id']))
	{
		$RecordId = $OptionArray['Id'];
		unset($OptionArray['Id']);
	}
	else
	{
		// New record
		$RecordId = -1;
	}

	$DBArray = array();
	foreach ($OptionArray as $FieldName=>$Value)
	{
		$DBArray[] = "$FieldName:$Value";
	}
	$str = implode(',', $DBArray);

	$NameValues = array();
	$NameValues['UserId'] = $UserId;
	$NameValues['Component'] = 'Spectrum';
	$NameValues['Name'] = $ComponentName;
	$NameValues['Value'] = $str;

	return ADB_PutRecord('PutRecordData', 'UserConfig', $NameValues, $RecordId);
}

// set user config string. differs from ADB_SetUserConfig in that the 
// options in $Options are a string rather than Key/Value pairs
// add $ForeignKeyAdjust switch in case if there is no need to convert the field
function ADB_SetUserConfigMultiSort($Name, $String, $UserId = NULL, $ForeignKeyAdjust = true)
{
	if ($UserId == NULL)
		$UserId = $_SESSION['UserId'];
		
		
	$Filters = array();
	$Filters[] = new cFilter('UserConfig', 'Component', '=', 'MultiColSort');
	$Filters[] = new cFilter('UserConfig', 'UserId', '=',$UserId );
	$Filters[] = new cFilter('UserConfig', 'Name', '=', $Name);
	$Records = ADB_GetTable('GetFilteredRecordList', 'UserConfig', $Filters, array('Id'), NULL, NULL);
 
	$RecordId="";
	
	if(count($Records)) 
		$RecordId=$Records[0]->Id;

		 // New record
	if (!$RecordId)
		$RecordId = -1;
	
	$Values = array();
	$Values['UserId'] = $UserId;
	$Values['Component'] = 'MultiColSort';
	$Values['Name'] = $Name;
	// Replace any Foreign Key field with its UpTo syntax
	if ($ForeignKeyAdjust)
	{
		$Values['Value'] = MakeForeignKeyAdjustmentsToString($String);	
	}
	else
	{
		$Values['Value'] = $String;	
	}

	 
	return ADB_PutRecord('PutRecordData', 'UserConfig', $Values, $RecordId);
}

function ADB_SetUserConfigString($ComponentName, $Options, $UserId = NULL)
{
	if ($UserId == NULL)
		$UserId = $_SESSION['UserId'];

	if (isset($OptionArray['Id']))
	{
		$RecordId = $OptionArray['Id'];
		unset($OptionArray['Id']);
	}
	else
	{
		// New record
		$RecordId = -1;
	}

	$Values = array();
	$Values['UserId'] = $UserId;
	$Values['Component'] = 'Spectrum';
	$Values['Name'] = $ComponentName;
	$Values['Value'] = $Options;

	return ADB_PutRecord('PutRecordData', 'UserConfig', $Values, $RecordId);
}


//------------------------------------------------------------------
// ADB_GenLogonReport - Returns a 2D array (i.e. list of records)
//		based on the specified filter.
//------------------------------------------------------------------
function ADB_GenLogonReport($RecordsPerPage=0, $PageIndex=0, $FilterColumns=array(), $FilterOperators=array(), $FilterValues=array(), $SortByField='', $SortOrder='Descending', &$TotalCount = NULL)
{
	global $RecordDataCache;
	$TableName = 'AuthEventHistory';
	$SelectColumns = array();
	$FilterTables = array('AuthEventHistory');

	$OutTotalCount = 0;
	$Distinct = false;

	$client = GetSOAPSecurityClient ();

	$ParamsArray = GetAuthVars ();

	$ParamsArray[] = new SoapParam ($TableName, 'TableName');
	
	if ($TotalCount === NULL && $PageIndex == 1)
	{
		$ParamsArray[] = new SoapParam ($RecordsPerPage,'MaxCount');
	}
	else
	{
		$ParamsArray[] = new SoapParam ($PageIndex, 'PageIndex');

		if ($RecordsPerPage > 0) $ParamsArray[] = new SoapParam($RecordsPerPage, 'RecordsPerPage');
	}

	// Build the filter arguments, Count how many tables for DISTINCT keyword
	for ($i=0; $i<count($FilterValues); $i++)
	{
		$FilterColumn = $FilterColumns[$i];
		$FilterOperator = $FilterOperators[$i];
		$FilterValue = ($FilterOperator == 'LIKE' || $FilterOperator == 'NOTLIKE') ? '%'.EscapeSQLchars($FilterValues[$i]).'%' : $FilterValues[$i];
		// convert '-1 day', '-1 week', and '-1 month' to usable values:
		date_default_timezone_set('UTC');
		if ($FilterValue == '-1 day')
			$FilterValue = date ('Y-m-d', time() - (24*60*60));

		elseif ($FilterValue == '-1 week')
			$FilterValue = date ('Y-m-d', time() - (7*24*60*60));

		elseif ($FilterValue == '-1 month')
			$FilterValue = date("Y-m-d", time() - (31*24*60*60));

		$FilterTable = $FilterTables[0];

		$dom = new DOMDocument(null, 'utf-8');
		$elFilterBy = $dom->CreateElement('FilterBy');
		$elFilterBy->setAttribute('Column',$FilterColumn);
		$elFilterBy->setAttribute('FilterOperator',$FilterOperator);
		$elFilterBy->setAttribute('FilterValue',$FilterValue);
		$elFilterBy->setAttribute('Table',$FilterTable);

		$ParamsArray[] = new SoapVar($dom->saveXML($elFilterBy), 147);
	}

	// If the user didn't pass a select array, grab the default
	if (count ($SelectColumns) == 0)
		$SelectColumns = GetNeededColumns ($TableName);

	// If there's only one column, we can always (and should always) use DISTINCT
	if (count ($SelectColumns) == 1)
		$Distinct = true;

	// Build the Select string
	$SelectColumnsString = '';
	foreach ($SelectColumns as $Column)
		$SelectColumnsString .= "[$Column] ";

	// If we're using DISTINCT and the sortby field somehow didn't wind up the list of columns stick it in there
	if ($SortByField != '' && $Distinct && !in_array($SortByField, $SelectColumns))
	{
		$SelectColumnsString .= "[$SortByField]";
	}

	$SelectColumnsXML = "<ColumnList" . ($Distinct ? " Distinct='true'" : "") . ">$SelectColumnsString</ColumnList>";
	$ParamsArray[] = new SoapVar($SelectColumnsXML, 147);

	// Set the sort parameters
	if ($SortByField && $SortOrder)
	{
		$SortByXML = "<Sort By=\"$SortByField\" Order=\"$SortOrder\"/>";
		$ParamsArray[] = new SoapVar($SortByXML, 147);
	}

	$res = $client->__soapCall(	'GenLogonReport', $ParamsArray, array('encoding'=>'UTF-8'));

	if (is_array ($res) && $res ['GenLogonReportResult']->ASResult != 0)
	{
		trigger_error("DataServer Error: {$res ['GenLogonReportResult']->ASResult}: {$res ['GenLogonReportResult']->ASMessage}", E_USER_ERROR);
	}
	else if (is_object ($res) && $res->ASResult)
	{
		return ReportDataServerError($res);
	}
	else if (!isset ($res ['AuthEventReport']->AuthEvent))
	{
		return array ();
	}
	else if (count ($res ['AuthEventReport']->AuthEvent) == 1)
	{
		$TotalCount = 1;
		return array ($res ['AuthEventReport']->AuthEvent);
	}
	else
	{
		$TotalCount = $res['TotalRecordCount'];
		return $res ['AuthEventReport']->AuthEvent;
	}
}

//------------------------------------------------------------------
// ADB_GetAuthEventStrings - Collect usage event types
//------------------------------------------------------------------
function ADB_GetAuthEventStrings()
{
	$client = GetSOAPSecurityClient ();

	$res = $client->__soapCall(	'GetAuthEventStrings', GetAuthVars());

	$RetArray = array ();

	if (is_array ($res) && $res ['GetAuthEventStringsResult']->ASResult != 0)
	{
		trigger_error("DataServer Error: {$res ['GetAuthEventStringsResult']->ASResult}: {$res ['GetAuthEventStringsResult']->ASMessage}", E_USER_ERROR);
	}
	else if (is_object ($res) && $res->ASResult)
	{
		return ReportDataServerError($res);
	}
	else
	{
		if (!isset ($res ['EventStringArray']->EventArray->EventName))
		{
			$RetArray ['Event'] = array ();
		}
		else if (count ($res ['EventStringArray']->EventArray->EventName) == 1)
		{
			$RetArray ['Event'] = array ($res ['EventStringArray']->EventArray->EventName);
		}
		else
		{
			$RetArray ['Event'] = $res ['EventStringArray']->EventArray->EventName;
		}
		
		if (!isset ($res ['EventStringArray']->ResultArray->Result))
		{
			$RetArray ['Result'] = array ();
		}
		else if (count ($res ['EventStringArray']->ResultArray->Result) == 1)
		{
			$RetArray ['Result'] = array ($res ['EventStringArray']->ResultArray->Result);
		}
		else
		{
			$RetArray ['Result'] = $res ['EventStringArray']->ResultArray->Result;
		}
	}

	return $RetArray;
}

function ADB_GetObjectDataForSlideExport($slideIds)
{
	$client = GetSOAPImageClient();
	$ParamsArray = GetAuthVars();
	
	$slideIdsXml = "<SlideIds>";
	foreach ($slideIds as $slideId)
	{
		$slideIdsXml .= "<SlideId>$slideId</SlideId>";
	}
	$slideIdsXml .= "</SlideIds>";
	$ParamsArray[] = new SoapVar($slideIdsXml, 147);
	$res = $client->__soapCall("GetObjectDataForSlideExport", $ParamsArray);
	if (is_array($res))
	{
		return $res;
	}
	else
	{
		if ($res->ASResult != 0)
		{
			ReportDataServerError($res);
		}
	}
}

function ADB_GetSlideObjectDataSize($slideIds)
{
	$client = GetSOAPImageClient();
	$ParamsArray = GetAuthVars();
	
	$slideIdsXml = "<SlideIds>";
	foreach ($slideIds as $slideId)
	{
		$slideIdsXml .= "<SlideId>$slideId</SlideId>";
	}
	$slideIdsXml .= "</SlideIds>";
	$ParamsArray[] = new SoapVar($slideIdsXml, 147);
	$res = $client->__soapCall("GetSlideObjectDataSize", $ParamsArray);
	if (is_array($res))
	{
		return $res["Size"];
	}
	else
	{
		if ($res->ASResult != 0)
		{
			ReportDataServerError($res);
		}
	}
}

//------------------------------------------------------------------
// ADB_GetTMACores - Returns an array of cores in the specified TMA
//	if a SlideImageId is specified then this call will include spot
//	information for the specified slide image.
//------------------------------------------------------------------
function ADB_GetTMACores($TMAId, $SlideImageId=-1)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($TMAId, 'TMAId');

	// generate spot info if a Slide Image ID is specified
	if ($SlideImageId > 0)
		$ParamsArray[] = new SoapParam($SlideImageId, 'ImageId');

	$res = $client->__soapCall(	'GetTmaCores', $ParamsArray);

	$cores = array();	

	if(is_array($res))
	{
		if(isset($res['TMANode']->Core))
		{
			if (is_array($res['TMANode']->Core))
			{
				$cores = $res['TMANode']->Core;
			}
			else
			{
				$cores[] = $res['TMANode']->Core;
			}
		}
		
		return $cores;
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}	
}


//------------------------------------------------------------------
// ADB_GetTMACoreInfo - returns all data for a core, and optionally
//	the data describing the core's spots and their image annotations
//------------------------------------------------------------------
function ADB_GetTMACoreInfo($CoreId, $bIncludeSpecimenInfo=false, $bIncludeSpotInfo=false, $bIncludeAnnotationInfo=false)
{
	$client = GetSOAPImageClient ();
	
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($CoreId, 'CoreId');
	$ParamsArray[] = new SoapParam($bIncludeSpecimenInfo, 'IncludeSpecimenInfo');
	$ParamsArray[] = new SoapParam($bIncludeSpotInfo, 'IncludeSpotInfo');
	$ParamsArray[] = new SoapParam($bIncludeAnnotationInfo, 'IncludeAnnotationInfo');
	
	$res = $client->__soapCall(	'GetTmaCoreInfo',		//SOAP Method Name
								$ParamsArray);			//Parameters
	
	// if response is an array then it was a success because there'll
	// be a core node and a result node
	if(is_array($res))
	{
		if(isset($res['Core']))
		{
			// we need to ensure that spots is an array
			$Core = $res['Core'];
			if (isset($Core->Spots))
			{
				if (isset($Core->Spots->Spot))
					$Core->Spots = MakeArray($Core->Spots->Spot);
				else
					$Core->Spots = array();
			}
			
			// fore ach spot, we should ensure that Annotations is an array
			foreach ($Core->Spots as $Spot)
			{
				if (isset($Spot->Annotations))
				{
					if (isset($Spot->Annotations->Annotation))
						$Spot->Annotations = MakeArray($Spot->Annotations->Annotation);
					else
						$Spot->Annotations = array();
					
					// for each annotation we should ensure that attributes is an array
					foreach ($Spot->Annotations as $Annotation)
					{					
						// some additional logic here in case there are no attributes
						if (isset($Annotation->Attributes->Attribute))
							$Annotation->Attributes = MakeArray($Annotation->Attributes->Attribute);
						else
							$Annotation->Attributes = array();
					}
				}
			}

			return $Core;
		}
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}
}


function ADB_SetLicenses ($systemId, $systemDesc, $key)
{
	$client = GetSOAPSecurityClient ();

	$ParamsArray = GetAuthVars();
	
	if ($systemId)
		$ParamsArray[] = new SoapParam($systemId, 'SystemId');
	$ParamsArray[] = new SoapParam($systemDesc, 'SystemDesc');
	if ($key)
		$ParamsArray[] = new SoapParam($key, 'Key');

	$res = $client->__soapCall(	'SetLicense', $ParamsArray);

	if ($res->ASResult == 0)
		return 0;

	SetError($res->ASMessage);

	// The decoder's error message was offset by -3000, reset it
	$status = $res->ASResult + 3000;

	return $status;
}


// Return all the system licensing information
// ent:	$Token - current session's token (needed because this is called from Authenticate.php
// 					where the AuthToken Session var is set but not yet established.
function ADB_GetLicenses ($Token = NULL, $DoThrow = true)
{
	$client = GetSOAPSecurityClient ();

	if ($Token)
		$ParamsArray = array (new SoapParam($Token, 'Token'));
	else
		$ParamsArray = GetAuthVars();

	$res = $client->__soapCall('GetLicenses', $ParamsArray);

	$Status = CheckDBResult($res, $DoThrow);
	if ($Status == 0)
	{
		if ((isset($res['Licenses']->SystemId) == NULL) || (isset($res['Licenses']->Key) == NULL))
		{
			// There is no license
			$Status = INVALID_LICENSE;
		}
	}
	if ($Status)
	{
		// Even with an error the SysAdmin page could still use the system ID and license key
		if (is_object($res))
			$res = ObjectToArray($res);
	}

	$Ret = array ();
	$Ret['ASResult'] = $Status;
	$HasEslideMgr = false;
	if (is_array($res))
	{
		if (isset($res['Licenses']) && empty($res['Licenses']) === false)
		{
			foreach ($res['Licenses'] as $Name => $Value)
			{
				if ($Name == 'Components')
				{
					if ($Value == '')	// XXX temporary fix
						continue;
					$Components = $Value->Component;
					// Convert object to array for easier processing
					$Ret['Components'] = array();
					foreach ($Components as $Component)
					{
						if ($Component->Name == 'eSlide Manager')
						{
							if (isset($Component->NumMonthsLeft) && ($Component->NumMonthsLeft != 'EXPIRED'))
								$HasEslideMgr = true;
							elseif (isset($Component->NumDaysLeft) && ($Component->NumDaysLeft != 'EXPIRED'))
								$HasEslideMgr = true;
						}
						if ($Component->Name == 'HcIOC')
						{
							$Ret['Components']['IntraOperative'] = $Component;
						}
						elseif ($Component->Name == 'HcConsults')
						{
							$Ret['Components']['ConsultationReview'] = $Component;
							$Ret['Components']['ConsultationRequest'] = $Component;
						}
						elseif ($Component->Name == 'HcCareConf')
						{
							$Ret['Components']['TumorBoard'] = $Component;
						}
						else if ($Component->Name == 'ExpirationWarningTime')
						{
							// This technically isn't a component
							$Ret['ExpirationWarningTime'] = $Component->NumDays;
						}
						else
						{
							$Ret['Components'][$Component->Name] = $Component;
						}
					}
				}
				elseif ($Name == 'AlgorithmGroups')
				{
					if ($Value == '')	// XXX temporary fix
						continue;
					$Ret['AlgorithmGroups'] = $Value->AlgorithmGroup;
				}
				else
				{
					$Ret[$Name] = $Value;
				}
			}
		}
	}

	// There is no more vanilla spectrum - SpectrumPlus is required (and can expire)
	if ($HasEslideMgr == false)
	{
		SetError('eSlide Manager license has expired. Please contact Aperio at 866-478-3999 or support@aperio.com to renew', E_ERROR);
		$Ret['ASResult'] = INVALID_LICENSE;
	}

	return $Ret;
}

// This is the pre-10.0 call for licensed options (still used in SysAdmin.php)
function ADB_GetFeatures ()
{
	$client = GetSOAPSecurityClient ();

	$res = $client->__soapCall(	'ListLicensedFeatureSet', GetAuthVars());

	if(is_array($res))
	{
		$ret = array ();
		if(isset($res ['FeatureSet']))
		{
			foreach ($res ['FeatureSet'] as $Name => $Value)
			{
				switch ($Name)
				{
				case 'Title':
					$_SESSION['Config']['Title'] = $Value;
					break;	// Don't store as a feature
				case 'UsersDataGroups':
					$ret ['DataGroups'] = $Value;
				default:
					$ret [$Name] = $Value;
				}
			}
		}
		return $ret;
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}	
}

/**
 * Placeholder to contain functions related to reports (Should eventually be replaced by a static class)
 *
 */
function DeclareReportFunctions ()
{
	/**
	 * Retrieves report data relating to a particular record
	 * 
	 * @param string $TableName - Parent object's table name
	 * @param int $Id - Parent object's unique Id
	 * @return array (ReportData) - List of reports generated for the requested record
	 */
	function ADB_GetRecordReportHistory ($TableName, $Id)
	{
		$client = GetSOAPImageClient ();

		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($TableName,'ParentTable');
		$ParamsArray[] = new SoapParam($Id,'ParentId');

		$res = $client->__soapCall(	'ListReports', $ParamsArray);

		if(is_array($res))
		{
			if($res['ListReportsResult']->ASResult == 0)
			{
				if (!$res ['ReportList'])
					return array ();
				elseif (is_array($res ['ReportList']->ReportInfo))
					return $res ['ReportList']->ReportInfo;
				else
					return array ($res ['ReportList']->ReportInfo);
			}
			else
			{
				trigger_error("DataServer Error: {$res['ListReportsResult']->ASResult}: {$res['ListReportsResult']->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Retrieves data from a particular report
	 *
	 * @param int $Id - Id of the report
	 * @return ReportData - Data object describing the report
	 */
	function ADB_GetReportData ($Id)
	{
		$client = GetSOAPImageClient ();

		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($Id,'Id');

		$res = $client->__soapCall('ListReports', $ParamsArray);

		if(is_array($res))
		{
			if($res['ListReportsResult']->ASResult == 0)
			{
				return $res ['ReportList']->ReportInfo;
			}
			else
			{
				trigger_error("DataServer Error: {$res["ListReportsResult"]->ASResult}: {$res["ListReportsResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
}
DeclareReportFunctions ();

/**
 * Static class to hold functions relating to events
 *
 */
class ADB_Customers
{
	/**
	 * Retrieve a customer's information from the database
	 *
	 * @param int $CustomerId - The customer's database-assigned Id number
	 * @return CustomerData - Object containing the customer's information
	 */
	public static function GetCustomerData ($CustomerId)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam('1', 'MaxCount');	// Curently the only way to do this is through a ListCustomers call so we need the MaxCount
		
		// create the Customer Info XML
		$dom = new DOMDocument ();
		$elFilterBy = $dom->CreateElement('FilterBy');
		$elFilterBy->setAttribute('Column','Id');
		$elFilterBy->setAttribute('FilterOperator','=');
		$elFilterBy->setAttribute('FilterValue',$CustomerId);
		$elFilterBy->setAttribute('Table','Customers');
		
		$ParamsArray[] = new SoapVar($dom->saveXML($elFilterBy), 147);
		
		$res = $client->__soapCall(	'ListCustomers',	//SOAP Method Name
									$ParamsArray);		//Parameters
		
		if(is_array($res))
		{
			if($res['ListCustomersResult']->ASResult == 0)
			{
				$Customer = array (); // Need to reformat the data so that the receiver can make better sense of it
				
				$Customer ['DataGroupId'] = $res['CustomerList']->CustomerInfo->DataGroupId;
				$Customer ['CustomerName'] = $res ['CustomerList']->CustomerInfo->Name;
				$Customer ['StreetAddress1'] = $res ['CustomerList']->CustomerInfo->Address->Street;
				$Customer ['StreetAddress2'] = $res ['CustomerList']->CustomerInfo->Address->Street2;
				$Customer ['City'] = $res ['CustomerList']->CustomerInfo->Address->City;
				$Customer ['State'] = $res ['CustomerList']->CustomerInfo->Address->State;
				$Customer ['Country'] = $res ['CustomerList']->CustomerInfo->Address->Country;
				$Customer ['PostalCode'] = $res ['CustomerList']->CustomerInfo->Address->PostalCode;
				$Customer ['PhoneNumber'] = $res ['CustomerList']->CustomerInfo->Phone;
				$Customer ['FaxNumber'] = $res ['CustomerList']->CustomerInfo->Fax;
				$Customer ['LogoFileName'] = $res ['CustomerList']->CustomerInfo->LogoFileName;
				
				return $Customer;
			}
			else
			{
				trigger_error("DataServer Error: {$res["ListCustomersResult"]->ASResult}: {$res["ListCustomersResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Retrieves a list of customer data from the database
	 *
	 * @param int $RecordsPerPage - Total number of records to return in one page
	 * @param int $Page - Which page of records to return
	 * @param string $SortField - 
	 * @param string $SortOrder - 
	 * @param int $Total - Output variable holds total number of customers in the database
	 * @param string $Name - Filter string, filters records to those containing $Name in their Name field
	 * @return array (CustomerData) - List of customers matching the filter contained in the requested page
	 */
	public static function ListCustomers ($RecordsPerPage, $Page, $SortField, $SortOrder, &$Total, $Name = "")
	{
		$client = GetSOAPImageClient ();
		$Total = 0;
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($Page, 'PageIndex');
		if ($RecordsPerPage > 0) $ParamsArray[] = new SoapParam($RecordsPerPage, 'RecordsPerPage');
		
		if ($Name != '')
		{
			// create the Customer Info XML
			$dom = new DOMDocument ();
			$elFilterBy = $dom->CreateElement('FilterBy');
			$elFilterBy->setAttribute('Column','Name');
			$elFilterBy->setAttribute('FilterOperator','LIKE');
			$elFilterBy->setAttribute('FilterValue', '%'.EscapeSQLchars($Name).'%');
			$elFilterBy->setAttribute('Table','Customers');
			
			$ParamsArray[] = new SoapVar($dom->saveXML($elFilterBy), 147);
		}
		
		// Set the sort parameters
		if ($SortField == 'CustomerId') $SortField = 'Id';
		if ($SortField == 'Address') $SortField = 'StreetAddress';
		$SortByXML =  "<Sort By=\"$SortField\" Order=\"$SortOrder\"/>";
		$ParamsArray[] = new SoapVar($SortByXML, 147);
		
		$res = $client->__soapCall(	'ListCustomers',	//SOAP Method Name
									$ParamsArray);		//Parameters
		
		if(is_array($res))
		{
			if($res['ListCustomersResult']->ASResult == 0)
			{
				$Total = isset ($res['TotalRecordCount']) ? $res['TotalRecordCount'] : 0;
				if (!$res ['CustomerList'])
					return array ();
				elseif (is_array ($res ['CustomerList']->CustomerInfo))
					return $res ['CustomerList']->CustomerInfo;
				else
					return array ($res ['CustomerList']->CustomerInfo);
			}
			else
			{
				trigger_error("DataServer Error: {$res["ListCustomersResult"]->ASResult}: {$res["ListCustomersResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Adds a new customer to the database
	 *
	 * @param string $Name - Customer's name
	 * @param string $Street1 - First mailing street address field
	 * @param string $Street2 - Second mailing street address field
	 * @param string $City - Mailing address city
	 * @param string $State - Mailing address state/province/region
	 * @param string $Country - Mailing address country
	 * @param string $PostalCode - Mailing address postal/zip-code
	 * @param string $Phone - Voice phone line number
	 * @param string $Fax - Fax phone line number
	 * @param string $LogoFileName - File name for the customer's logo (contained within SIM's htdocs/customers folder)
	 * @param int	 $DataGroupId - DataGroup Id#
	 * @return int - Id of the newly-created customer
	 */
	public static function AddCustomer ($Name, $Street1, $Street2, $City, $State, $Country, $PostalCode, $Phone, $Fax, $LogoFileName, $DataGroupId, $Data)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		
		// create the Customer Info XML
		$dom = new DOMDocument ();
		$CustInfo = $dom->CreateElement ('CustomerInfo');										// <CustomerInfo>
		$CustInfo->appendChild ($dom->CreateElement ('Name', xmlencode ($Name)));					// <Name>$Name</Name>
		
		$Address = $dom->CreateElement ('Address');													// <Address>
		$Address->appendChild ($dom->CreateElement ('Street', xmlencode ($Street1)));					// <Street>$Street1</Street>
		$Address->appendChild ($dom->CreateElement ('Street2', xmlencode ($Street2)));					// <Street2>$Street2</Street2>
		$Address->appendChild ($dom->CreateElement ('City', xmlencode ($City)));						// <City>$City</City>
		$Address->appendChild ($dom->CreateElement ('State', xmlencode ($State)));						// <State>$State</State>
		$Address->appendChild ($dom->CreateElement ('Country', xmlencode ($Country)));					// <Country>$Country</Country>
		$Address->appendChild ($dom->CreateElement ('PostalCode', xmlencode ($PostalCode)));			// <PostalCode>$PostalCode</PostalCode>
		$CustInfo->appendChild ($Address);															// </Address>
		
		$CustInfo->appendChild ($dom->CreateElement ('Phone', xmlencode ($Phone)));					// <Phone>$Phone</Phone>
		$CustInfo->appendChild ($dom->CreateElement ('Fax', xmlencode ($Fax)));						// <Fax>$Fax</Fax>
		$CustInfo->appendChild ($dom->CreateElement ('LogoFileName', xmlencode ($LogoFileName)));	// <LogoFileName>$LogoFileName</LogoFileName>
		$CustInfo->appendChild ($dom->CreateElement ('DataGroupId', xmlencode ($DataGroupId)));
			
		$CustInfoXML = $dom->saveXML ($CustInfo);												// </CustomerInfo>
		$ParamsArray[] = new SoapVar($CustInfoXML, 147);
		
		$res = $client->__soapCall(	'AddCustomer',		//SOAP Method Name
									$ParamsArray,		//Parameters
									NULL, NULL, $OutputHeaders, true); //Only execute once
		
		if(is_array($res))
		{
			if($res['AddCustomerResult']->ASResult == 0)
			{
				return $res ['Id'];
			}
			else
			{
				trigger_error("DataServer Error: {$res["AddCustomerResult"]->ASResult}: {$res["AddCustomerResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Updates data for a list of customers
	 *
	 * @param array (int) $CustomerIds - List of customer Ids to apply the change to
	 * @param string $Street1 - First mailing street address field
	 * @param string $Street2 - Second mailing street address field
	 * @param string $City - Mailing address city
	 * @param string $State - Mailing address state/province/region
	 * @param string $Country - Mailing address country
	 * @param string $PostalCode - Mailing address postal/zip-code
	 * @param string $Phone - Voice phone line number
	 * @param string $Fax - Fax phone line number
	 * @param string $LogoFileName - File name for the customer's logo (contained within SIM's htdocs/customers folder)
		 * @param int $DataGroupId - DataGroup Id number
	 */
	public static function UpdateCustomers ($CustomerIds, $Name, $Street1, $Street2, $City, $State, $Country, $PostalCode, $Phone, $Fax, $LogoFileName, $DataGroupId)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		
		// create the Customer Info XML
		$dom = new DOMDocument ();
		$CustList = $dom->CreateElement ('CustomerList');
		
		foreach ($CustomerIds as $CustomerId)
		{
			$CustInfo = $dom->CreateElement ('CustomerInfo');
			$CustInfo->appendChild ($dom->CreateElement ('Id', $CustomerId));
			if ($Name !== NULL) $CustInfo->appendChild ($dom->CreateElement ('Name', xmlencode ($Name)));
			
			if ($Street1 !== NULL || $Street2 !== NULL || $City !== NULL || $State !== NULL || $Country !== NULL || $PostalCode !== NULL)
			{
				$Address = $dom->CreateElement ('Address');
				
				if ($Street1 !== NULL) $Address->appendChild ($dom->CreateElement ('Street', xmlencode ($Street1)));
				if ($Street2 !== NULL) $Address->appendChild ($dom->CreateElement ('Street2', xmlencode ($Street2)));
				if ($City !== NULL) $Address->appendChild ($dom->CreateElement ('City', xmlencode ($City)));
				if ($State !== NULL) $Address->appendChild ($dom->CreateElement ('State', xmlencode ($State)));
				if ($Country !== NULL) $Address->appendChild ($dom->CreateElement ('Country', xmlencode ($Country)));
				if ($PostalCode !== NULL) $Address->appendChild ($dom->CreateElement ('PostalCode', xmlencode ($PostalCode)));
				
				$CustInfo->appendChild ($Address);
			}
			
			if ($Phone !== NULL) $CustInfo->appendChild ($dom->CreateElement ('Phone', xmlencode ($Phone)));
			if ($Fax !== NULL) $CustInfo->appendChild ($dom->CreateElement ('Fax', xmlencode ($Fax)));
			if ($LogoFileName !== NULL) $CustInfo->appendChild ($dom->CreateElement ('LogoFileName', xmlencode ($LogoFileName)));
			if ($DataGroupId != NULL) $CustInfo->appendChild ($dom->CreateElement ('DataGroupId', xmlencode ($DataGroupId)));  // deliberate !=NULL so '' is not updating existing data
			
			$CustList->appendChild ($CustInfo);
		}
		
		$CustListXML = $dom->saveXML ($CustList);
		$ParamsArray[] = new SoapVar($CustListXML, 147);
		
		$res = $client->__soapCall(	'UpdateCustomers',		//SOAP Method Name
									$ParamsArray);			//Parameters
		
		if(is_array($res) && $res['UpdateCustomerResult']->ASResult != 0)
			trigger_error("DataServer Error: {$res["UpdateUserResult"]->ASResult}: {$res["UpdateUserResult"]->ASMessage}", E_USER_ERROR);
		else if($res->ASResult != 0)
			return ReportDataServerError($res);
	}
	/**
	 * Assigns report templates to customers                                                                                                                   667
	 *
	  * @param array (int) $CustomerIds - List of customer Ids to assign templates to
	  * @param array (int) $TemplateIds - List of all report template ids for current hierarchy
	  * @param array (int) $AssignedTemplates - Array of report templates to assign to the customer [indexed by $TemplateId]
	**/
	public static function AssignReportTemplates ($CustomerIds, $TableName, $TemplateIds, $AssignedTemplates)
	{
		foreach($CustomerIds as $CustomerId)
		{
			$doc = new DOMDocument();
			$UpdateNode = $doc->createElement('UpdateCustomerReportTemplates');
			$doc->appendChild($UpdateNode);
			$CustomerIdNode = $doc->createElement('CustomerId', $CustomerId);     
			$UpdateNode->appendChild($CustomerIdNode);
			$TableNameNode = $doc->createElement('TableName', $TableName);
			$UpdateNode->appendChild($TableNameNode);
			$TemplatesNode = $doc->createElement('ReportTemplates');
			$UpdateNode->appendChild($TemplatesNode);
			foreach ($TemplateIds as $TemplateId)
			{
				if (isset($AssignedTemplates[$TemplateId]))
				{
					$TemplateNode = $doc->createElement('ReportTemplate');
					$TemplateNode->appendChild($doc->createElement('Id', $TemplateId));
					$TemplatesNode->appendChild($TemplateNode);
				}
			}
			$TemplateXML = $doc->saveXML ($UpdateNode);
			$ParamsArray = GetAuthVars();
			$ParamsArray[] = new SoapVar($TemplateXML, 147);
			
			$client = GetSOAPImageClient ();
			$res = $client->__soapCall( 'UpdateCustomerReportTemplates',        //SOAP Method Name
										$ParamsArray);            //Parameters
			if(is_array($res) && $res['UpdateCustomerReportTemplatesResult']->ASResult != 0)
				trigger_error("DataServer Error: {$res["UpdateCustomerReportTemplatesResult"]->ASResult}: {$res["UpdateCustomerReportTemplatesResult"]->ASMessage}", E_USER_ERROR);
			else if($res->ASResult != 0)
			return ReportDataServerError($res);
		}
	}
	/**
	 * Removes a customer record from the database
	 *
	 * @param int $CustomerIds - Database-assigned Id of the customer to be deleted
	 */
	public static function DeleteCustomers ($CustomerIds)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		
		// create the Customer Info XML
		$dom = new DOMDocument ();
		$CustList = $dom->CreateElement ('CustomerList');
		
		foreach ($CustomerIds as $CustomerId)
			$CustList->appendChild ($dom->CreateElement ('Id', $CustomerId));
		
		$CustListXML = $dom->saveXML ($CustList);
		$ParamsArray[] = new SoapVar($CustListXML, 147);
		
		$res = $client->__soapCall(	'DeleteCustomers', $ParamsArray);

		return CheckDBResult($res, false);
	}
}

/**
 * Placeholder to contain functions related to canned comments (Should eventually be replaced by a static class)
 *
 */
function DeclareCommentFunctions ()
{
	/**
	* ADB_ListCannedComments
	* @descr 	Returns Filtered Comments list according to SsConfigId and/or CommentType
	* @params	int		$SsConfigId
	* @params	string	$CommentType 	(Slide | Case  | Project etc.)
	* @return array		
	*		int		Id 			(Comment.Id)
	* 		int		UserId
	* 		string	Name		
	*		string	Type			(CommentType)
	*		string	CommentText
	* 		int		$SsActionId	(representing appropriate collection of SsFieldValues)
	 *
	*  - 061908 msmaga	updated to accept SsConfigId & Comment Type
	 */
	function ADB_ListCannedComments($SsConfigId = 0, $CommentType = 'Slide')
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		if ($SsConfigId > 0)
			$ParamsArray[] = new SoapParam($SsConfigId, 'SsConfigId');
		$ParamsArray[] = new SoapParam($CommentType, 'CommentType');
		
		$res = $client->__soapCall(	'ListCannedComments',	//SOAP Method Name
									$ParamsArray);			//Parameters
		
		$DataArray = array ();
		
		// check for error
		if (is_object($res) && $res->ASResult != 0)
			return ReportDataServerError($res);
		
		// test for no data    //MS:Not tested
		if (is_array($res) && empty($res['GenericDataSet']))
			return $DataArray;
			
		// test & process as single object
		if (is_array($res) && !empty($res['GenericDataSet']) && !empty($res['GenericDataSet']->DataRow) && !empty($res['GenericDataSet']->DataRow->Id))
		{
			$CommentId = $res['GenericDataSet']->DataRow->Id;
			foreach ($res['GenericDataSet']->DataRow as $Name=>$Value)
				$DataArray[$CommentId][$Name] = $Value;
		}
		
		// if only one item is being returned, process a single object
		if (is_object($res['GenericDataSet']->DataRow))
		{
			$CommentId = $res['GenericDataSet']->DataRow->Id;
			foreach ($res['GenericDataSet']->DataRow as $Name=>$Value)
				$DataArray[$CommentId][$Name] = $Value;
		}
		
		// else this is an array of objects
		else foreach ($res['GenericDataSet']->DataRow as $DataRow)
		{
			$CommentId = $DataRow->Id;
			foreach ($DataRow as $Name=>$Value)
				$DataArray[$CommentId][$Name] = $Value;
		}
		
		return $DataArray;
	}

	/**
	* ADB_ListCannedCommentsForImage ($ImageId, $SsConfigId)
	* @descr 	Returns Filtered Comments list according to Image name (or "0, $SsConfigId") and appropriate SsFieldValues
	* @params 	int		$ImageId
	* @params	int		$SsConfigId
	* @author Mark Smaga <msmaga@aperio.com> 06/10/08
	**/
	function ADB_ListCannedCommentsForImage($ImageId)
	{	
		$DataArray 	= array();
		$client 	= GetSOAPImageClient (); 
			
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($ImageId, 'ImageId');
		
		$res = $client->__soapCall(	'ListCannedCommentsForImage',
									$ParamsArray);
		
		if (is_object($res) && ($res->ASResult != '0'))
		{
			// return empty if image not found
			if ($res->ASResult == IMAGE_NOT_FOUND)
				return $DataArray;
			else
				return ReportDataServerError($res);
		}
		
		// test for no data
		if (empty($res['GenericDataSet']))
			return $DataArray;
		
		// if only one item is being returned, process a single object
		if (is_object($res['GenericDataSet']->DataRow))
		{
			$CommentId = $res['GenericDataSet']->DataRow->Id;
			foreach ($res ['GenericDataSet']->DataRow as $Name=>$Value)
				$DataArray[$CommentId][$Name] = $Value;	   
		}
		
		// else this is an array of objects
		else foreach ($res['GenericDataSet']->DataRow as $DataRow)
		{
			$CommentId = $DataRow->Id;
			foreach ($DataRow as $Name=>$Value)
				$DataArray[$CommentId][$Name] = $Value;
		}
		
		return $DataArray;
	}
	
	
	/**
	 * Retrieve a specific canned comment object from the DataServer
	 *
	 * @param int $CommentId - Comment Id number
	 * @return CannedComment - Canned comment from the database
	 */
	function ADB_GetCannedComment ($CommentId)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($CommentId,'Id');
		
		$res = $client->__soapCall(	'ListComments',		//SOAP Method Name
									$ParamsArray);		//Parameters
		
		if(is_array($res))
		{
			if($res['ListCommentsResult']->ASResult == 0)
			{
				return $res ['CommentList']->CommentInfo;
			}
			else
			{
				trigger_error("DataServer Error: {$res["ListCommentsResult"]->ASResult}: {$res["ListCommentsResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Put canned comment data into the database, either for an existing, or a new canned comment
	 *
	 * @param int $CommentId - Id of the comment to be modified (-1 for a new comment)
	 * @param int $UserId - Id of the user performing the addition or modification
	 * @param string $Type - User-defined type of comment (usually "Case" or "Slide")
	 * @param string $Name - Short name for user-identification
	 * @param string $CommentText - Long contents of the canned comment
	 * @return int - Id value of the modified/added comment
	 */
	function ADB_PutCannedComment ($CommentId, $UserId, $Type, $Name, $CommentText, $SsActionId=NULL)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		
		// create the Customer Info XML
		$dom = new DOMDocument ();
		$CommentInfo = $dom->CreateElement ('CommentInfo');
		
		if ($CommentId != -1)
			$CommentInfo->appendChild ($dom->CreateElement ('Id', xmlencode ($CommentId)));
		else
			$CommentInfo->appendChild ($dom->CreateElement ('Id', 0));
		$CommentInfo->appendChild ($dom->CreateElement ('UserId', xmlencode ($UserId)));
		$CommentInfo->appendChild ($dom->CreateElement ('Name', xmlencode ($Name)));
		$CommentInfo->appendChild ($dom->CreateElement ('Type', xmlencode ($Type)));
		$CommentInfo->appendChild ($dom->CreateElement ('CommentText', xmlencode ($CommentText)));
		if ($SsActionId)
			$CommentInfo->appendChild ($dom->CreateElement ('SsActionId', xmlencode ($SsActionId)));

		$CommentInfoXML = $dom->saveXML ($CommentInfo);
		$ParamsArray[] = new SoapVar($CommentInfoXML, 147);

		$res = $client->__soapCall(	'PutComment', $ParamsArray, NULL, NULL, $OutputHeaders, $CommentId == -1); // Only execulte once if a new comment

		if(is_array($res))
		{
			if($res['PutCommentResult']->ASResult == 0)
			{
				return $res ['Id'];
			}
			else
			{
				trigger_error("DataServer Error: {$res["PutCommentResult"]->ASResult}: {$res["PutCommentResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Deletes the specified comment from the database
	 *
	 * @param int $CommentId - Comment Id number
	 */
	function ADB_DeleteCannedComment ($CommentId)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($CommentId,'Id');
		
		$res = $client->__soapCall(	'DeleteComments',		//SOAP Method Name
									$ParamsArray);			//Parameters
		
		if(is_array($res))
		{
			if($res['DeleteCommentsResult']->ASResult != 0)
			{
				trigger_error("DataServer Error: {$res["DeleteCommentsResult"]->ASResult}: {$res["DeleteCommentsResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}

}
DeclareCommentFunctions ();


//------------------------------------------------------------------------------------------------------
// ADB_ListAnnotationTemplates -  Returns  array of annotation template data
//------------------------------------------------------------------------------------------------------
function ADB_ListAnnotationTemplates($RecordsPerPage=0, $PageIndex=0, $SelectColumns=array(), $FilterColumns=array(), $FilterOperators=array(), 
									 $FilterValues=array(), $FilterTables=array(), $SortByField='', $SortOrder='Descending', &$OutTotalCount)
{
	$DBReader = new cDatabaseReader('GetFilteredRecordList', 'AnnotationTemplate');
	$DBReader->SetReturnType('Arrays');
	$DBReader->SetGetTotalNumRecords(true);
	if ($SelectColumns)
	{
		foreach ($SelectColumns as $Column)
			$DBReader->AddColumn($Column);
	}
	for ($i=0; $i<count($FilterValues); $i++)
	{
		$DBReader->AddFilter($FilterTables[$i], $FilterColumns[$i], $FilterOperators[$i], $FilterValues[$i]);
	}
	if ($SortByField != '')
	{
		$DBReader->SetSort($SortByField, $SortOrder);
	}

	$Templates = $DBReader->GetRecords(0);

	// Add the Annotion XML if requested
	if (in_array('Annotation', $SelectColumns))
	{
		foreach ($Templates as &$Template)
			$Template['Annotation'] = ADB_GetAnnotationXML($Template['Id'], 'AnnotationTemplateId');
	}

	$OutTotalCount = $DBReader->TotalNumRecords;

	return $Templates;
}

/**
 * Static class to hold functions relating to events
 *
 */
class ADB_Events
{
	/**
	 * Retrieve a list of events linked to a specific Data Table
	 *
	 * @param string $TableName - Table to list the events for
	 * @return array (EventData) - List of Events linked to the Data Table
	 */
	public static function ListEvents ($TableName)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapVar("<FilterBy Column='HierarchyLevel' FilterOperator='=' FilterValue='$TableName' />", 147);
		
		$res = $client->__soapCall(	'ListDataEvents',		//SOAP Method Name
									$ParamsArray);			//Parameters
		
		if(is_array($res))
		{
			if($res['ListDataEventsResult']->ASResult == 0)
			{
				if (!$res ['DataEventList'])
					return array ();
				else if (is_array ($res ['DataEventList']->DataEventInfo))
					return $res ['DataEventList']->DataEventInfo;
				else
					return array ($res ['DataEventList']->DataEventInfo);
			}
			else
			{
				trigger_error("DataServer Error: {$res["ListDataEventsResult"]->ASResult}: {$res["ListDataEventsResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Retrieve data regarding a specific event
	 *
	 * @param int $Id - The event's unique database Id
	 * @return EventData - Object containing the event data
	 */
	public static function GetEvent ($Id)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapVar("<FilterBy Column='Id' FilterOperator='=' FilterValue='$Id' />", 147);
		
		$res = $client->__soapCall(	'ListDataEvents',		//SOAP Method Name
									$ParamsArray);			//Parameters
		
		if(is_array($res))
		{
			if($res['ListDataEventsResult']->ASResult == 0)
			{
				if (!$res ['DataEventList'])
					return null;
				else
					return $res ['DataEventList']->DataEventInfo;
			}
			else
			{
				trigger_error("DataServer Error: {$res["ListDataEventsResult"]->ASResult}: {$res["ListDataEventsResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Put data pertaining to an event into the database
	 *
	 * @param int $Id - Unique Id of the event to put, -1 to create a new event
	 * @param string $TableName - Data table the event will operate on
	 * @param string $Name - Unique short name of the event
	 * @param string $Handler - External program to lanch when the event is triggered
	 * @param string $Description - Long description of the event in detail
	 * @return int - Id of the event created/edited
	 */
	public static function PutEvent ($Id = -1, $TableName = null, $Name = null, $Handler = null, $Description = null)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		
		// create the Customer Info XML
		$dom = new DOMDocument ();
		$EventInfo = $dom->CreateElement ('EventInfo');
		
		if ($Id != -1)
			$EventInfo->appendChild ($dom->CreateElement ('Id', xmlencode ($Id)));
		else
			$EventInfo->appendChild ($dom->CreateElement ('Id', 0));
		if ($TableName !== null) $EventInfo->appendChild ($dom->CreateElement ('HierarchyLevel', xmlencode ($TableName)));
		if ($Name !== null) $EventInfo->appendChild ($dom->CreateElement ('Name', xmlencode ($Name)));
		if ($Handler !== null) $EventInfo->appendChild ($dom->CreateElement ('Handler', xmlencode ($Handler)));
		if ($Description !== null) $EventInfo->appendChild ($dom->CreateElement ('Description', xmlencode ($Description)));
		
		$EventInfoXML = $dom->saveXML ($EventInfo);
		$ParamsArray[] = new SoapVar($EventInfoXML, 147);
		
		$res = $client->__soapCall(	'PutDataEvent',		//SOAP Method Name
									$ParamsArray,		//Parameters
									NULL, NULL, $OutputHeaders, $Id == -1); //Only execute once if a new event
		
		if(is_array($res))
		{
			if($res['PutDataEventResult']->ASResult == 0)
			{
				return $res ['Id'];
			}
			else
			{
				trigger_error("DataServer Error: {$res["PutDataEventResult"]->ASResult}: {$res["PutDataEventResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Remove an event from the database
	 * 
	 * @param int $Id - Unique Id of the event to remove
	 */
	public static function DeleteEvent ($Id)
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($Id,'Id');
		
		$res = $client->__soapCall(	'DeleteDataEvents',		//SOAP Method Name
									$ParamsArray);			//Parameters
		
		if(is_array($res))
		{
			if($res['DeleteDataEventResult']->ASResult != 0)
			{
				trigger_error("DataServer Error: {$res["DeleteDataEventsResult"]->ASResult}: {$res["DeleteDataEventsResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
	
	/**
	 * Trigger an event on the DataServer
	 * 
	 * @param string $EventName - Unique name of the event to be fired
	 * @param int $RecordId - Unique Id of the record the event should be fired on
	 * @param array $ExtraParams - An associative array with the extra params to be sent to the event on the DataServer
	 *		example: $ExtraParams = array('SystemId' => $Licenses['SystemId']);
	 */
	public static function FireEvent ($EventName, $RecordId, $ExtraParams=array())
	{
		$client = GetSOAPImageClient ();
		
		$ParamsArray = GetAuthVars();
		$ParamsArray[] = new SoapParam($EventName,'EventName');
		$ParamsArray[] = new SoapParam($RecordId,'DataId');
		
		if (!empty($ExtraParams))
		{
			foreach ($ExtraParams as $Param => $Value)
			{
				$ParamsArray[] = new SoapParam($Value, $Param);
			}
		}

		$res = $client->__soapCall(	'HandleDataEvent',		//SOAP Method Name
									$ParamsArray);			//Parameters
		
		if(is_array($res))
		{
			if($res['HandleDataEventResult']->ASResult != 0)
			{
				trigger_error("DataServer Error: {$res["HandleDataEventResult"]->ASResult}: {$res["HandleDataEventResult"]->ASMessage}", E_USER_ERROR);
			}
		}
		else
		{
			if($res->ASResult != 0)
			{
				return ReportDataServerError($res);
			}
		}
	}
}


// return the date/time from the dataserver
function ADB_GetDate()
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();

	$res = $client->__soapCall('GetDate', $ParamsArray, array('encoding'=>'UTF-8'));

	if (is_array($res) == false)
		return ReportDataServerError($res);
	return $res['Value'];
}

// return true if the dataserver is in a time zone that observes DST
function ADB_IsDst()
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();

	$res = $client->__soapCall('GetDate', $ParamsArray, array('encoding'=>'UTF-8'));

	if (is_array($res) == false)
		return ReportDataServerError($res);
	return $res['IsDaylightSavingTime'];
}

// XXX Deprecated - use cDatabaseReader
// Generic routine to issue a GetFilteredRecordList for retrieving database records.
// ent:	Method to call in DataServer (NULL = GetFilteredRecordList)
// 		Name of database table
// 		array of cFilters for search criteria
// 		array of columnNames to return
// 		array of SoapParams (allows client to set almost any criteria)
// 		cSortField object
function ADB_GetTable($MethodName, $TableName, $Filters, $SelectColumns, $SoapParams, $Sort)
{
	$DBReader = new cDatabaseReader($MethodName, $TableName);
	$DBReader->SetReturnType('Objects');
	if ($SelectColumns)
	{
		foreach ($SelectColumns as $Column)
			$DBReader->AddColumn($Column);
	}
	if ($Filters)
	{
		foreach ($Filters as $Filter)
			$DBReader->AddFilter($Filter->TableName, $Filter->ColumnName, $Filter->Operator, $Filter->Value);
	}
	if ($Sort)
	{
		$DBReader->SetSort($Sort->GetSortField(), $Sort->GetSortOrder());
	}

	return $DBReader->GetRecords(0);
}


/**
*  Informs the DataServer of role this session is assigned
*/
function ADB_SetRoleForSession($RoleId, $DataHierarchyId='')
{
	$client = GetSOAPSecurityClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($RoleId, 'RoleId');
	if ($DataHierarchyId != '')
		$ParamsArray[] = new SoapParam($DataHierarchyId, 'DataHierarchyId');

	$res = $client->__soapCall(	'SetRole', $ParamsArray);
		
	if ($res->ASResult != 0)
	{
		return ReportDataServerError($res);
	}
}


/************************************************
 * ADB_AddUserDefinedField
 *                                  
 * @author Mark Smaga <msmaga@aperio.com> 05/15/08
 * @desc Add a field to the specified table
 *	Parameters:
 *	$TableName - string
 *	$FieldName - string
 *	$FieldType - string vocabulary (
 *		VOCABULARY	DATASERVER	SPECTRUM
 *		Date			datetime		cDateTimeField
 *		Integer		int			cIntegerField
 *		Memo		nvarchar 4000	cMemoField 
 *		Number		float			cNumberField
 *		Text			nvarchar 255	cTextField
 *	)
 *	$DisplayOrder - integer
 *	$DisplayName - string
 *	$Vocabulary - array of strings
 *	$FieldLength - int (optional)                 
 *
 ************************************************/
function ADB_AddUserDefinedField($TableName, $FieldName, $FieldType='Text', $FieldPosition='0',
		$DisplayName='', $Vocabulary='', $AuditAccess, $FieldSize='')
{
	$client = GetSOAPImageClient ();

	// DataServer requires user-defined fields to be prepended with 'Column'
	$FieldName = 'Column' . $FieldName;

	// if a vocabulary is defined, declare $FieldType as Memo or Text.  (Text will allow numeric vocabularies)
	if (!empty($Vocabulary) && (($FieldType != 'Memo') && ($FieldType != 'Comment')) )
		$FieldType = 'Text'; 

	// translate $FieldType into $DataServerType
	switch ($FieldType)
	{
		case 'Date' :
			$DataServerType = 'datetime';
			break;
			
		case 'Integer' :
			$DataServerType = 'int';
			break;	

		case 'Memo' :
			$DataServerType = 'nvarchar';
			if ($FieldSize == '')
				$FieldSize = '-1';
			break;	

		case 'Number' :
			$DataServerType = 'float';
			break;			

		// default covers 'Text' and anything undeclared
		default :
			$DataServerType = 'nvarchar';
			if ($FieldSize == '')
				$FieldSize = '255';
			break;
	}

	$ParamsArray = GetAuthVars();

	// create the  XML Data 
	$dom = new DOMDocument ();
	$Data = $dom->CreateElement ('Data');
	$Data->appendChild ($dom->CreateElement ('TableName', $TableName));
	$Data->appendChild ($dom->CreateElement ('FieldName', xmlencode($FieldName)));
	$Data->appendChild ($dom->CreateElement ('FieldType', $DataServerType));
	$Data->appendChild ($dom->CreateElement ('FieldPosition', $FieldPosition));
	$Data->appendChild ($dom->CreateElement ('AuditAccess', $AuditAccess));
	if ($FieldSize != '')
		$Data->appendChild ($dom->CreateElement ('FieldSize', $FieldSize));
	$Data->appendChild ($dom->CreateElement ('DisplayName', $DisplayName));

	$xmlOutput = $dom->saveXML ($Data);
	$ParamsArray[] = new SoapVar($xmlOutput, 147);			

	if (count($Vocabulary) > 0)
	{
		$XmlEncodedStr = xmlencode($Vocabulary);
		$ParamsArray[] = new SoapVar("<FieldVocabulary>$XmlEncodedStr</FieldVocabulary>", 147);
	}

	$res = $client->__soapCall('AddUserDefinedField', $ParamsArray,
								NULL, NULL, $OutputHeaders, true);	//Only execute once

	if ($res->ASResult != 0)
	{
		return ReportDataServerError($res);
	}
}


/**
* ADB_ListSsFieldConfigs
* @descr List all SsFieldConfig Records
* @return array[TableColumn]
* 		- [Id] int SsFieldConfig.Id
* 		- [TableName] string SsFieldConfig.TableName
* 		- [FieldName] string SsFieldConfig.FieldName
*		- [DisplayName] string <DisplayName for underlying Table|Column
* 		- DataServer will return null if no data
* @author Mark Smaga <msmaga@aperio.com> 05/15/08
*/
function ADB_ListSsFieldConfigs()
{
	$client = GetSOAPImageClient (); 
		
	$res = $client->__soapCall(	'ListSsFieldConfigs',
								GetAuthVars());
		
	if (is_array($res) == false)
	{
		return ReportDataServerError($res);
	}
	
	// convert output to an array
	$ListArray = array();
	if (empty($res['DataRows']))  // unconfigured database
		return $ListArray;
		
	foreach ($res['DataRows']->DataRow as $DataRow)
	{
		foreach ($DataRow as $Name=>$Value)
			$ListArray[$DataRow->FieldName][$Name] = $Value;
	}
	
	return $ListArray;
} 


/**
* ADB_ListScoreActions
* @descr Get Score Action Configuration List
* @params (SsConfigId) <optional>
* @return 	arrays of:
*		[Ssconfig.Id]
*		-	[SsConfig.Id]
* 		-	[SsActionId]
* 		-	[SsConfig.Description]
* 		-	['SsFieldValues']
*			-	[TableField]
* 				-	[SsFieldConfig.TableName . FieldName]
* 		 		-	[SsFieldValue.Id]
*		   		-	[SsFieldConfig.Id]
*				-	[SsFieldConfig.TableName]
* 				-	[SsFieldConfig.FieldName]
* 				-	[SsFieldConfig.DisplayName]
* 				-	[SsFieldValue.Value]
* 
* @author Mark Smaga <msmaga@aperio.com> 05/22/08
*/
// deprecated - use Tables/cScoreActions
function ADB_ListScoreActions($SsActionId = '-1', $ListOnlyActives = true)
{
	$client = GetSOAPImageClient(); 
	$ParamsArray = GetAuthVars();

	if ($ListOnlyActives == false)
		$ParamsArray[] = new SoapParam(1, 'IncludeInactive');

	$res = $client->__soapCall('ListScoreActions', $ParamsArray);

	CheckDBResult($res, true);

	$ConfigArray = array();

	// if empty set, return empty array
	if (empty($res['DataRows']))
		return $ConfigArray;

	if (is_object($res['DataRows']->DataRow))
	{					
		// if only one item is being returned, move to an array for common processing
		$DataRows = array($res['DataRows']->DataRow);
	}
	else // DataRow is already an array
	{
		$DataRows = $res['DataRows']->DataRow;
	}

	foreach ($DataRows as $DataRow)
	{
		if (!empty($DataRow->SsFieldValues->SsFieldValue))		// if only partial data, don't use it
		{
			$Id = $DataRow->SsAction->Id;
			if (($SsActionId != '-1') && ($SsActionId != $Id))	// if SsActionId requested & not yet found: get next Id
				continue;

			foreach ($DataRow->SsAction as $Name=>$Value)					   
				$ConfigArray[$Id][$Name] = $Value;

			foreach ($DataRow->SsFieldValues->SsFieldValue as $SsFieldValue)
			{ 
				$TableField = $SsFieldValue->FieldName;
				foreach ($SsFieldValue as $Name=>$Value)
					$ConfigArray[$Id]['SsFieldValues'][$TableField][$Name] = $Value;
			}
		}
	}

	// sort by Description & return the array
	uasort($ConfigArray, 'DescriptionSort');
	return $ConfigArray;
}
// part of ADB_ListScoreActions();
//		Sorting function  
function DescriptionSort($a, $b)
{
	if ($a['Description'] == $b['Description'])
		return 0;
	return ($a['Description'] < $b['Description']) ? -1 : 1;
}


/**
 * Create/update a Slide Specific Processing configuration set of tables
 ***/
function ADB_PutScoreConfig($SsConfigRecord, $SsActionRecord, $SsScoreConfigs, $SsInterpretationConfigs)
{
	$client = GetSOAPImageClient ();

	$dom = new DOMDocument();
	$ParamsArray = GetAuthVars ();

	$ConfigXML = $dom->CreateElement('SsConfig');
	foreach ($SsConfigRecord as $Idx => $Value)
	{
		$ConfigXML->appendChild($dom->CreateElement($Idx, xmlencode($Value)));
	}
	$xml = $dom->saveXML($ConfigXML);
	$ParamsArray[] = new SoapVar($xml, XSD_ANYXML);

	if ($SsActionRecord)
	{
		$ActionXML = $dom->CreateElement('SsAction');
		foreach ($SsActionRecord as $Idx => $Value)
		{
			$ActionXML->appendChild($dom->CreateElement($Idx, xmlencode($Value)));
		}
		$xml = $dom->saveXML($ActionXML);
		$ParamsArray[] = new SoapVar($xml, XSD_ANYXML);
	}

	if ($SsScoreConfigs)
	{
		$ScoreConfigsElement = $dom->CreateElement('SsScoreConfigs');
		foreach ($SsScoreConfigs as $SsScoreConfig)
		{
			$ScoreConfigElement = $dom->CreateElement('SsScoreConfig');
			foreach ($SsScoreConfig as $Idx => $Value)
			{
				$ScoreConfigElement->appendChild($dom->CreateElement($Idx, xmlencode($Value)));
			}
			$ScoreConfigsElement->appendChild($ScoreConfigElement);
		}
		$xml = $dom->saveXML($ScoreConfigsElement);
		$ParamsArray[] = new SoapVar($xml, XSD_ANYXML);
	}


	if ($SsInterpretationConfigs)
	{
		$InterpretationConfigsElement = $dom->CreateElement('SsInterpretationConfigs');
		foreach ($SsInterpretationConfigs as $SsInterpretationConfig)
		{
			$InterpretationConfigElement = $dom->CreateElement('SsInterpretationConfig');
			foreach ($SsInterpretationConfig as $Idx => $Value)
			{
				$InterpretationConfigElement->appendChild($dom->CreateElement($Idx, xmlencode($Value)));
			}
			$InterpretationConfigsElement->appendChild($InterpretationConfigElement);
		}
		$xml = $dom->saveXML($InterpretationConfigsElement);
		$ParamsArray[] = new SoapVar($xml, XSD_ANYXML);
	}


	$res = $client->__soapCall('PutSsConfig', $ParamsArray, NULL, NULL, $OutputHeaders, $SsConfigRecord ['Id'] == -1); // Only execute once if it's a new SSConfig

	if (is_array($res))
	{
		if ($res['PutSsConfigResult']->ASResult == 0)
		{
			return $res['SsConfigId'];
		}
		$res = $res['PutSsConfigResult'];
	}
	if (is_object($res))
	{
		//if ($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}
}


/**
* ADB_GetScoreConfig
* @descr Get Score Configuration Data
* @params 	int		$SsConfigId or $SsActionId
* @return 	arrays of:
* 		['SsAction']
* 			[Id]
* 			[SsConfigId]
* 			[Description]
*		['SsConfig']
* 			[MacroId]
*			[Description]
*			[FormulaText]
*			[CalculatedScoreConfigId] (SsScoreConfig.Id)
* 		['SsScoreConfigs']
*			[$SsScoreConfig.Id]
*				[DisplayName]
*				[DisplayOrder]
*				[ValueMin]
*				[ValueMax]
*				[ValueStep]
*				[Vocabulary] (Pipe "|" Separated List)
*				[MacroOutputId]
*				[MacroOutput.Title]
* 		['SsInterpretationConfigs']
* 			[SsInterpretation.Id]
* 				[Id]
* 				[SsConfigId]
* 				[SsInterpretationText]
* 				[ScoreValueMin]
* 				[ScoreValueMax]
*/
function ADB_GetScoreConfig($SsConfigId, $SsActionId = NULL)
{
	$client = GetSOAPImageClient (); 
		
	$ParamsArray = GetAuthVars();

	if ($SsConfigId != NULL)
	{
		$ParamsArray[] = new SoapParam($SsConfigId, 'SsConfigId');
	}
	else
	{
		$ParamsArray[] = new SoapParam($SsActionId, 'SsActionId');
		// An Action Config can have many Slide configs, but only one active one.
		// Only get the active configuaration
		$ParamsArray[] = new SoapParam(1, 'ActiveOnly');
	}


	$res = $client->__soapCall('GetSsConfig', $ParamsArray);

	if (is_array($res) == false)
	{
		return ReportDataServerError($res);
	}

	$ConfigArray = array();
	
	// check for empty dataset
	if (empty($res['DataRow']))
		return $ConfigArray;

	// test for no SsConfig data
	if (isset($res['DataRow']->SsConfigs))
	{
		if (isset($res['DataRow']->SsConfigs->SsConfig))
		{
			$SsConfig = $res['DataRow']->SsConfigs->SsConfig;
			foreach ($SsConfig as $Name=>$Value)
				$ConfigArray['SsConfig'][$Name] = $Value;
		}
	}
	
	//	SsAction data
	if (!empty($res['DataRow']->SsAction) && (!empty($res['DataRow']->SsAction->Id)))
	{
		foreach ($res['DataRow']->SsAction as $Name=>$Value)
			$ConfigArray['SsAction'][$Name] = $Value;
	}
	
	// test for no SsScoreConfig data
	if (!empty($res['DataRow']->SsScoreConfigs) && isset($res['DataRow']->SsScoreConfigs->SsScoreConfig))  
	{
		// if one set of data is passed, there will be an SsScoreConfig array
		if (!is_array($res['DataRow']->SsScoreConfigs->SsScoreConfig))
		{
			foreach ($res['DataRow']->SsScoreConfigs as $Record)
			{
				$SsScoreConfigId = $Record->Id;
				foreach ($Record as $Name=>$Value)
					$ConfigArray['SsScoreConfigs'][$SsScoreConfigId][$Name] = $Value;
			}
		}
		
		else	// if more than one set of data is passed, there will be an array of SsScoreConfig arrays
		{
			foreach ($res['DataRow']->SsScoreConfigs->SsScoreConfig as $Record)
			{
				$SsScoreConfigId = $Record->Id;
				foreach ($Record as $Name=>$Value)
					$ConfigArray['SsScoreConfigs'][$SsScoreConfigId][$Name] = $Value;
			}
		}
	}
	
	//	SsInterpetation data
	if (!empty($res['DataRow']->SsInterpretationConfigs))
	{
		// if one set of data are passed, there will be a SsInterpretationConfig array
		if (!is_array($res['DataRow']->SsInterpretationConfigs->SsInterpretationConfig))
		{
			foreach ($res['DataRow']->SsInterpretationConfigs as $Record)
			{
				$SsInterpId = $Record->Id;
				foreach ($Record as $Name=>$Value)
					$ConfigArray['SsInterpretationConfigs'][$SsInterpId][$Name] = $Value;
			}
		}
		
		else 	// if more than one set of data is passed, there will be an array of SsSInterpretationConfig arrays
		{
			foreach ($res['DataRow']->SsInterpretationConfigs->SsInterpretationConfig as $Object)
			{
				$SsInterpretationConfigId = $Object->Id;
				foreach ($Object as $Name=>$Value)
					$ConfigArray['SsInterpretationConfigs'][$SsInterpretationConfigId][$Name] = $Value;
			}
		}
	}

	return $ConfigArray;
}

function ADB_DeleteScoreConfig($SsActionId)
{
	$client = GetSOAPImageClient ();

	// cascading delete's may take a lot of time so temporarily
	// lengthen the default socket timeout and the max script execution time
	$client->SetTimeout(180);

	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($SsActionId, 'SsActionId');

	$res = $client->__soapCall(	'DeleteSsConfig', $ParamsArray);

	if (is_object($res))
	{
		if( $res->ASResult == 0)
			return;
		ReportDataServerError($res);
	}
	trigger_error("DataServer Error: Invalid Response to DeleteSsConfig", E_USER_ERROR);
}


/**
* ADB_ValidateFormula
* @descr Validate syntax for a C-Sharp Scoring formula
* @params 	string	$Formula
* @return 	true if the formula validates; false otherwise
* 
* @author Mark Smaga <msmaga@aperio.com> 05/22/08
**/
function ADB_ValidateFormula($FormulaText='')
{
	// return TRUE if no data
	if ($FormulaText == '')
		return true;

	$client = GetSOAPImageClient (); 

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($FormulaText, 'FormulaText');

	$res = $client->__soapCall(	'ValidateFormula',
								$ParamsArray);

	if ($res->ASResult == INVALID_SCORE_FORMULA) 	// error code for invalid Score formula
		return false;

	if ($res->ASResult != '0')
	{
		return ReportDataServerError($res);
	}

	return true;
}

//------------------------------------------------------------------
// ADB_ListClientSystemDataGroups -  Returns an array of datagroups
//      associated with the specified ClientSystemId.
//------------------------------------------------------------------
function ADB_ListClientSystemDataGroups($SystemId)
{   
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($SystemId, 'SystemId');
	
	$client = GetSOAPImageClient(); 
	$res = $client->__soapCall( 'ListClientSystemDataGroups', $ParamsArray);
	
	if(is_array($res))
	{   
		if (isset($res['ClientSystemDataGroups']->ClientSystemDataGroup))
		{     
			if (is_array($res['ClientSystemDataGroups']->ClientSystemDataGroup))
			{
				// array of datagroups
				return $res['ClientSystemDataGroups']->ClientSystemDataGroup;
			}
			else
			{
				// just one datagroup
				$arr = array();
				$arr[] = $res['ClientSystemDataGroups']->ClientSystemDataGroup;
				return $arr;
			}
		}
		else
		{
			// no datagroups
			return array();
		}
			
	}
	else
	{
		if($res->ASResult != 0)
		{
			trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		}
	}    
}


//------------------------------------------------------------------
// ADB_ListClientSystemDataGroups -  Returns an array of datagroups
//      associated with the specified ClientSystemId.
//------------------------------------------------------------------
function ADB_SendEmail($From, $To, $CC = "", $BCC = "", $Subject = "", $Body = "", $Timeout = 5000)
{   
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($From, 'From');
	$ParamsArray[] = new SoapParam ($To, 'To'); 
	$ParamsArray[] = new SoapParam ($CC, 'CC'); 
	$ParamsArray[] = new SoapParam ($BCC, 'Bcc'); 
	$ParamsArray[] = new SoapParam ($Subject, 'Subject'); 
	$ParamsArray[] = new SoapParam ($Body, 'Body');
	$ParamsArray[] = new SoapParam ($Timeout, 'Timeout');  
	
	$client = GetSOAPImageClient(); 
	$res = $client->__soapCall( 'SendEmail', $ParamsArray);
	
	if($res->ASResult == 0)
	{
		return true;
	}
	else
	{
		trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		return false;
	}
}



//------------------------------------------------------------------
// ADB_GetSelfAssignableRoles -  returns a list of roles with
//      AllowSelfAssign = 1.  Notet this method does not require
//      an auth token because it's used during the self-signup
//      process for seconslide.
//------------------------------------------------------------------
function ADB_GetSelfAssignableRoles()
{   

	$client = GetSOAPSecurityClient(); 
	$res = $client->__soapCall( 'GetSelfAssignableRoles', array() );
  
	if(is_array($res))
	{   

		if (isset($res['Roles']->Role))
		{     
			if (is_array($res['Roles']->Role))
			{
				// array of roles
				return $res['Roles']->Role;
			}
			else
			{
				// just one role
				$arr = array();
				$arr[] = $res['Roles']->Role;
				return $arr;
			}
		}
		else
		{
			// no roles
			return array();
		}
			
	}
	else
	{
		if($res->ASResult != 0)
		{
			trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		}
	} 
}

//------------------------------------------------------------------
// ADB_GetCurrentUserRoles -  returns a list of roles associated
// with the current user. This list includes both roles that are
// directly assigned to the user, as well as roles that are assigned
// to user groups that the user is a member of.
// Note, also returns hidden roles
//------------------------------------------------------------------
function ADB_GetCurrentUserRoles()
{
	$client = GetSOAPSecurityClient(); 
	$ParamsArray = GetAuthVars ();
	$res = $client->__soapCall( 'GetCurrentUserRoles', $ParamsArray );
  
	if(is_array($res))
	{   

		if (isset($res['Roles']) && isset($res['Roles']->Role))
		{
			$Roles = $res['Roles']->Role; 
			if (is_array($Roles))    
				return $Roles;
			else
				return array($Roles);
		}
		else
		{
			// no roles
			return array();
		}
			
	}
	else
	{
		if($res->ASResult != 0)
		{
			trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		}
	} 
}

//------------------------------------------------------------------
// ADB_SignUp() -  creates a new user and emails password
//------------------------------------------------------------------
function ADB_SignUp($LoginName, $Email, $FullName, $RoleId, $NewDataGroupName = "", $NewDataGroupDescription = "")
{   
	$ParamsArray[] = new SoapParam ($LoginName, 'LoginName'); 
	$ParamsArray[] = new SoapParam ($Email, 'Email');
	$ParamsArray[] = new SoapParam ($FullName, 'FullName'); 
	$ParamsArray[] = new SoapParam ($RoleId, 'RoleId'); 
	$ParamsArray[] = new SoapParam ($NewDataGroupName, 'DataGroupName');
	$ParamsArray[] = new SoapParam ($NewDataGroupDescription, 'DataGroupDescription');  
	
	
	$client = GetSOAPSecurityClient(); 
	$res = $client->__soapCall( 'SignUp', $ParamsArray);
	
	if($res->ASResult == 0)
	{
		// success
		return true;
	}
	elseif($res->ASResult == -4036)
	{
		// user already exists
		return $res->ASResult;
	}
	else
	{
		// other error
		trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		return false;
	}
}

//------------------------------------------------------------------
// ADB_ResetPassword() -  sets a temporary password and emails it
//------------------------------------------------------------------
function ADB_ResetPassword($EmailAddress)
{   
	$ParamsArray[] = new SoapParam ($EmailAddress, 'Email');  
	
	$client = GetSOAPSecurityClient(); 
	$res = $client->__soapCall( 'ResetPassword', $ParamsArray);
	
	if($res->ASResult == 0)
	{
		return $res->ASResult;
	}
	else
	{
		if ($res->ASResult == "-4036")
		{
			return $res->ASResult;
		}    
		else
		{
			trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		}
		return $res->ASResult;
	}
}


//------------------------------------------------------------------
// ADB_InviteUser() -  grants users access to the specified datagroup
//      and gives them the specified role.  If each user doesn't exit
//      then that user account is created and a termporary password
//      is emailed to them.  For each user, if $SampleDataGroupName
//      and $SampleDataGroupDescription are set then a sample data
//      group will be created whic only they have access to.
//------------------------------------------------------------------
function ADB_InviteUser($DataGroupId, $RoleId, $LoginNames, $EmailAddresses, $AccessFlags, $SampleDataGroupNames = array(), $SampleDataGroupDescriptions = array())
{   
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($DataGroupId, 'DataGroupId');
	$ParamsArray[] = new SoapParam ($RoleId, 'RoleId');

	$dom = new DOMDocument(null, 'UTF-8');    
	
	$UserInvitationsElement = $dom->CreateElement('UserInvitations');
	for($i = 0; $i < count($LoginNames); $i++)
	{
		$UserInvitationElement = $dom->CreateElement('UserInvitation');
		$UserInvitationElement->appendChild($dom->CreateElement("LoginName", xmlencode($LoginNames[$i]))); 
		$UserInvitationElement->appendChild($dom->CreateElement("E_Mail", xmlencode($EmailAddresses[$i])));
		$UserInvitationElement->appendChild($dom->CreateElement("AccessFlags", $AccessFlags[$i]));
		if(isset($SampleDataGroupNames[$i]))
			$UserInvitationElement->appendChild($dom->CreateElement("DataGroupName", $SampleDataGroupNames[$i]));
		if(isset($SampleDataGroupDescriptions[$i]))
			$UserInvitationElement->appendChild($dom->CreateElement("DataGroupDescription", $SampleDataGroupDescriptions[$i]));
		$UserInvitationsElement->appendChild($UserInvitationElement);
	}
	$ParamsArray[] = new SoapVar($dom->saveXML($UserInvitationsElement), XSD_ANYXML);
   
	$client = GetSOAPSecurityClient(); 
	$res = $client->__soapCall( 'InviteUser', $ParamsArray);
	
	if($res->ASResult == 0)
	{
		return true;
	}
	else
	{   
		trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		return false;
	}
}


/**
* ADB_SendUpdateNotificationEmail
* @descr     For SecondSlide.  Notifies owner of the specified record that the record has been modified.
* @params    string TableName
*            int    RecordId
*            int    SendToAll - if 0 sends to datagroup owner, if 1 sends to all dg users.
* @return    
* 
**/
function ADB_SendUpdateNotificationEmail($TableName, $RecordId, $SendToAll=0)
{   
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam ($TableName, 'TableName');
	$ParamsArray[] = new SoapParam ($RecordId, 'RecordId');
	$ParamsArray[] = new SoapParam ($SendToAll, 'SendToAll');  

	$client = GetSOAPImageClient(); 
	$res = $client->__soapCall( 'SendUpdateNotificationEmail', $ParamsArray);
	
	if($res->ASResult == 0)
	{
		return true;
	}
	else
	{   
		trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
		return false;
	}
}


function GetRecord($TableName, $KeyColumn, $Key)
{
	$Records = GetRecords($TableName);
	foreach ($Records as $Record)
	{
		if (strcasecmp($Record->$KeyColumn, $Key) == 0)
			return $Record;
	}
	return NULL;
}

static $RecordCache = array();
function GetRecords($TableName)
{
	global $RecordCache;
	if (isset($RecordCache[$TableName]) == false)
	{
		if ($TableName == 'Stain')
		{
			$StainRecords = new cDatabaseReader('GetFilteredRecordList', 'Stain');
			$StainRecords->SetCache(true);
			$StainRecords->SetSort('ShortName', 'Ascending');
			$StainRecords->AddColumn('Id');
			$StainRecords->AddColumn('ShortName');
			$StainRecords->AddColumn('DisplayOrder');
			$RecordCache[$TableName] = $StainRecords;
		}
		else
		{
			$DBReader = new cDatabaseReader('GetFilteredRecordList', $TableName);
			$DBReader->SetCache(true);
			$DBReader->SetSort('Name', 'Ascending');
			$DBReader->AddColumn('Id');
			$DBReader->AddColumn('Name');
			$RecordCache[$TableName] = $DBReader;
		}
	}

	return $RecordCache[$TableName]->GetRecords(0);
}


/**
* ADB_IsFileTransferCompleted
* @descr     Returns true/false if all file transfers for the specified record have completed
* @params    string TableName
*            int    RecordId
* @return    boolean
* 
**/
function ADB_IsFileTransferCompleted($TableName, $RecordId)
{    
	$DataArray = array();
	$client = GetSOAPImageClient (); 
		
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	$ParamsArray[] = new SoapParam($RecordId, 'RecordId');

	$res = $client->__soapCall( 'IsFileTransferCompleted',
								$ParamsArray);
	
	if (!is_array($res) && ($res->ASResult != '0'))
	{
		return ReportDataServerError($res);
	}
	
	// return result
	return ($res['Completed'] == '1');
}

/**
 * 
 * @descr     Retrieves list of externally authenticated users, the list is retrieved from the external authentication server    
 * @return UserList - list of external users
 *
 */
function ADB_GetExternalUsers ()
{
	$client = GetSOAPSecurityClient ();  
	
	$res = $client->__soapCall(	'ListExternalUsers', GetAuthVars());
	if(is_array($res))
	{
		if($res['ListExternalUsersResult']->ASResult == 0)
		{
			if (isset($res ['UserList']->User))
			{
				$res ['UserList']->User = MakeArray($res['UserList']->User);
			}
			return $res ['UserList'];
		}
		else
		{
			trigger_error("DataServer Error: {$res["ListExternalUsersResult"]->ASResult}: {$res["ListExternalUsersResult"]->ASMessage}", E_USER_ERROR);
		}
	}
	else
	{
		if($res->ASResult != 0)
		{
			trigger_error("{$res->ASResult}: {$res->ASMessage}", E_USER_ERROR);
		}
	}
}

/**
 * 
 * @descr     Retrieves list of authentication types    
 * @return AuthTypes - list of authentication types
 *
 */
function ADB_GetAuthTypes ()
{
	$client = GetSOAPSecurityClient ();  
	
	$res = $client->__soapCall(	'GetAuthTypes', GetAuthVars());
	if(is_array($res))
	{
		if($res['GetAuthTypesResult']->ASResult == 0)
		{        
			$res['AuthTypes']->AuthType = MakeArray($res['AuthTypes']->AuthType);
			return $res ['AuthTypes'];
		}
		else
		{
			trigger_error("DataServer Error: {$res["GetAuthTypesResult"]->ASResult}: {$res["GetAuthTypesResult"]->ASMessage}", E_USER_ERROR);
		}
	}
	else
	{
		if($res->ASResult != 0)
		{
			trigger_error("{$res->ASResult}: {$res->ASMessage}", E_USER_ERROR);
		}
	}
}

/**
 * 
 * @descr     Retrieves array of externally authenticated users that are not assigned to other users,
 * 			  but may be assigned the current user's external login name
 * @return 	  UserList - array of external users
 *
 */
function ADB_GetUnassignedExternalUsers ($CurUserExternalLoginName)
{
	$Unassigned = array();
	
	// get all external users 
	$ExternalUsers = ADB_GetExternalUsers();
	if (empty($ExternalUsers) == false)
	{
		// get all external login names for all users currently in the Spectrum database
		$Assigned = ADB_GetFilteredRecordList('Users', 0, 0, array('ExternalLoginName'));
		foreach ($Assigned as $user)
			$ExternalLoginNames[] = $user['ExternalLoginName'];
		// add each external user that is not assigned to a different user
		// to the $Unassigned array
		foreach ($ExternalUsers->User as $ExternalUser)
		{
			if (in_array($ExternalUser->UserName, $ExternalLoginNames) == false || $ExternalUser->UserName == $CurUserExternalLoginName)		
			{
				// external user not currently assigned
				$Unassigned[] = $ExternalUser;
			}
		}
	}
	return $Unassigned;
}

/**
 * @descr 	Retrieves information for an externally authenticated user, information is retrieved from the external authentication server
 * @params  string $UserName
 * @return  UserInfo - info for userName
 */
function ADB_GetExternalUserInfo($UserName)
{
	$client = GetSOAPSecurityClient ();
	
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($UserName, 'UserName');
	$res = $client->__soapCall(	'GetExternalUserInfo', $ParamsArray);
	if(is_array($res))
	{
		if($res['GetExternalUserInfoResult']->ASResult == 0)
		{
			return $res ['UserInfo'];
		}
		else
		{
			trigger_error("DataServer Error: {$res["GetExternalUserInfoResult"]->ASResult}: {$res["GetExternalUserInfoResult"]->ASMessage}", E_USER_ERROR);
		}
	}
	else
	{
		if($res->ASResult != 0)
		{
			trigger_error("{$res->ASResult}: {$res->ASMessage}", E_USER_ERROR);
		}
	}
}

/**
 * Database function to copy a record to a specific parent. If no parent id is
 * given, a new one is created.
 * 
 * @param string $parentTable                 Type of parent table (e.g., Case, Specimen, Lesson, etc.)
 * @param string $currentTable                Current table that's being Copied/cloned (e.g., Slide, Specimen, etc.)
 * @param integer|array $copyRecordIds        List of Ids of the current table (selected from grid to be cloned)
 * @param integer $parentId                   If cloning to an existing parent, then pass in parentId
 * @return array                              Returns an array of files to be physically copied using PHP functions.
 * 
 * Sample Returned XML from DataServer:
 * 
 * <WorkflowEntityCopyResult><ASResult>0</ASResult><ASMessage></ASMessage></WorkflowEntityCopyResult> 
 * <Documents> 
 * <Document><Id>136</Id><Location>C:\\UploadedDocs\\Breast CA Report[3003].doc</Location></Document> 
 * <Document><Id>137</Id><Location>C:\\UploadedDocs\\Breast CA Report[3005].doc</Location></Document> 
 * <Document><Id>138</Id><Location>C:\\UploadedDocs\\Perforce User's Guide.pdf</Location></Document> 
 * </Documents>
 */
function ADB_CopyToEntities($parentTable, $currentTable, $copyRecordIds, $copyAnnotationsFlag, $copyDocumentsFlag, $copyReportsFlag, $parentId = null)
{  
	$copyRecordIds = (is_array($copyRecordIds)) ? $copyRecordIds : (array) $copyRecordIds;
	$parentTable = xmlencode($parentTable);
	$currentTable = xmlencode($currentTable);
	
	
	$client = GetSOAPImageClient();
	
	$copyToEntityInfo = "";
	
	foreach ($copyRecordIds as $id) {
		$copyToEntityInfo .= "<CopyEntityInfo Id=\"{$id}\"></CopyEntityInfo>";
	}
	
	$xmlStr = "<ParentTable>{$parentTable}</ParentTable><ParentId>{$parentId}</ParentId><CurrentTable>{$currentTable}</CurrentTable><DataGroupId>1</DataGroupId>"
			. "<CopyAnnotationsFlag>{$copyAnnotationsFlag}</CopyAnnotationsFlag><CopyDocumentsFlag>{$copyDocumentsFlag}</CopyDocumentsFlag><CopyReportsFlag>{$copyReportsFlag}</CopyReportsFlag>"
			. "<CopyEntityInfoNode>{$copyToEntityInfo}</CopyEntityInfoNode>";
	
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapVar($xmlStr, 147);
	
	$res = $client->__soapCall('CopyToEntities', $ParamsArray, null, null, $outputHeaders, true);
	
	return CheckDBResult($res, false);
}

/**
 * Database function to copy workflow entities.
 * 
 * @param string $currentTable           table that's being Copied/cloned (e.g., Slide, Specimen, etc.)
 * @param integer $dataGroupId           The data group Id to be set on the Cloned records.
 * @param bool $copyAnnotationsFlag      True = copy annotation records, false = don't 
 * @param bool $copyDocumentsFlag		 True = copy attachment documents, false = don't
 * @param bool $copyReportsFlag			 True = copy Report documents, false = don't
 * @param integer|array $copyEntityIds   An array of Ids for the entity to be copied.
 * @return array                         An array of files to be copied by PHP
 * 
 * Sample Returned XML from DataServer:
 * <CopyToEntitiesResult><ASResult>0</ASResult><ASMessage></ASMessage></CopyToEntitiesResult> 
 * <Documents> 
 * <Document><Id>136</Id><Location>C:\\UploadedDocs\\Breast CA Report[3003].doc</Location></Document> 
 * <Document><Id>137</Id><Location>C:\\UploadedDocs\\Breast CA Report[3005].doc</Location></Document> 
 * <Document><Id>138</Id><Location>C:\\UploadedDocs\\Perforce User's Guide.pdf</Location></Document> 
 * </Documents>                         
 */
function ADB_CopyWorkflowEntities($currentTable, $dataGroupId, $copyAnnotationsFlag, $copyDocumentsFlag, $copyReportsFlag, $copyEntityIds)
{
	$copyAnnotationsFlag = (int) $copyAnnotationsFlag;
	$copyEntityIds = (array) $copyEntityIds;
	
	$copyEntityInfo = "";
	foreach ($copyEntityIds as $id) {
		$copyEntityInfo .= "<CopyEntityInfo Id=\"{$id}\"></CopyEntityInfo>";
	}
	
	$client = GetSOAPSecurityClient();
	$xmlStr = "<CurrentTable>{$currentTable}</CurrentTable><DataGroupId>{$dataGroupId}</DataGroupId>"
			. "<CopyAnnotationsFlag>{$copyAnnotationsFlag}</CopyAnnotationsFlag><CopyDocumentsFlag>{$copyDocumentsFlag}</CopyDocumentsFlag><CopyReportsFlag>{$copyReportsFlag}</CopyReportsFlag>"
			. "<CopyEntityInfoNode>{$copyEntityInfo}</CopyEntityInfoNode>";
	
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapVar($xmlStr, 147);
	
	$res = $client->__soapCall('WorkflowEntityCopy', $ParamsArray, null, null, $outputHeaders);
	
	$documents = $res['WorkflowEntityCopy']->Documents;
	$docCount = count($documents);
	
	if (!empty($documents) and $docCount > 0) {
		$output = ADB_CopyDocuments($documents);
		if (count($output['copied']) > 0) {
			$res['ReturnedCount'] = $output['copied'];
		}
	}
	
	CheckDBResult($res);
	return $res;
}

/**
 * Database function to get the reference count of files associated with a database record.
 * The file can be an image file or an attachment file
 * 
 * @param string  $TableName        table name
 * @param integer $Id          		id of the record
 * @return mixed                    Returns a count of the number of records referencing the record, or -1 if we have a DataServer error.
 * 
 * Sample Returned XML from DataServer:
 * <GetRecordRefCountResult><ASResult>0</ASResult><ASMessage></ASMessage></GetRecordRefCountResult>
 * <Count>214</Count></GetRecordRefCountResponse>                         
 */
function ADB_GetRecordRefCount($TableName, $Id)
{
	$Id = (int) $Id;
	$client = GetSOAPImageClient();
	$xmlStr = "<TableName>{$TableName}</TableName><Id>{$Id}</Id>";
	
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapVar($xmlStr, 147);
	
	$res = $client->__soapCall('GetRecordRefCount', $ParamsArray, null, null, $outputHeaders);
	
	if (is_array($res) && intval($res['GetRecordRefCountResult']->ASResult,10) == 0)
	{
		return (int) $res['Count'];
	}
	else
	{
		ReportDataServerError($res);
		return -1;
	}
}

/**
 * Database function to obtain a list of all documents for a record
 * 
 * @param string    $TableName - database tablename
 * @param integer   $Id - database id
 * @return array|bool                    Returns a list of documents
 * 
 */
function ADB_GetAllDocumentsForRecord($TableName, $Id)
{
	$client = GetSOAPImageClient();
	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	$ParamsArray[] = new SoapParam($Id, 'Id');

	$res = $client->__soapCall('GetAllDocumentsForRecord', $ParamsArray, array('encoding'=>'UTF-8'));

	if (is_array($res) && $res['GetAllDocumentsForRecordResult']->ASResult == '0')
	{
		return $res['Documents'];
	}
	else
	{
		return false;
	}
}


/**
 * @descr   Saves the User Group information and User to UserGroup assignments or removal of assignments.
 */
function ADB_PutUserGroupsAndUsersUserGroup($UserGroupId,$UserGroupName,$UserGroupDescription,$AddListStr,$RemoveListStr){
	
	$NameValues = array();
	$NameValues['Name'] = $UserGroupName;
	$NameValues['Description'] = $UserGroupDescription;
	$Total = 0;

	$NewUserGroupId = 0;
	// Don't save changes to system UserGroups
	trigger_error($UserGroupId . $UserGroupName);
	if (substr($UserGroupName, 0, 1) != "_" && ($UserGroupId == -1 || $UserGroupId > 99))
	{
		$NewUserGroupId = ADB_PutRecordData('UserGroup',$NameValues, $UserGroupId, true);
	}
	// If it's a system UserGroup, set $NewUserGroupId to $UserGroupId
	if ($UserGroupId >= 1 && $UserGroupId < 100)
	{
		$NewUserGroupId = $UserGroupId;
	}
	
	if ($NewUserGroupId < 0)
		return $NewUserGroupId; // Error has occurred.
	  
	if ($UserGroupId != -1){ // If we're adding a new user group, no removes are necessary
	 
		// Remove from the list.  We need to use PK of UserUserGroup table, so we have to get that
		// first using GetFilteredRecordList.
		if (!empty($RemoveListStr) || $RemoveListStr != ''){
			$RemoveList = explode(",",$RemoveListStr);
			foreach ($RemoveList as $UserId){
				$UUGId = ADB_GetFilteredRecordList('UserUserGroup',0, 0,array('Id'),array('UserGroupId','UserId'),array('=','='),array($UserGroupId,intval($UserId)), array('UserUserGroup','UserUserGroup'),null,null,$Total);
				$res = ADB_DeleteRecord('UserUserGroup',$UUGId[0]['Id'],true);
				if ($res < 0)
					return $res;
				
			}
		}
	}
	
	 // We've established the UserGroupId above
	$ElementArray['UserGroupId'] = $NewUserGroupId;
		   
	// Add the newly assigned records.              
	if (!empty($AddListStr) && $AddListStr != ''){
	
		$AddList = explode(",",$AddListStr);

		foreach ($AddList as $UserId){
			$ElementArray['UserId'] = intval($UserId);
			
			$res = ADB_GetFilteredRecordList('UserUserGroup',
										0,0,
										array('Id'),
										array('UserId','UserGroupId'),
										array('=','='),
										array($UserId,$NewUserGroupId),
										array('UserUserGroup','UserUserGroup'),
										'','',$Total);

			if (!isset($res[0]))    // no record found, so it must be a new one.
				$PKId = -1;
			else $PKId = $res[0]['Id'];

			$NewId = ADB_PutRecordData('UserUserGroup',$ElementArray,$PKId);
			if ($NewId < 0)
				return $NewId;
		}
	}
	
	return $NewUserGroupId;
}

// Delete a user group.  Make sure no users are assigned to the user group first.
// parameter is the user group id.

function ADB_DeleteUserGroup ($UserGroupId)
{
	$Total = 0;
	// Make sure no Active users are assigned to the user group first.  Avoid checking on Users that are inactive. 
	$res = ADB_GetFilteredRecordList('UserUserGroup',0, 0,array('Id','UpToUsersByUserId.Inactive'),array('UserGroupId','UpToUsersByUserId.Inactive'),array('=','='),array($UserGroupId,'0'), array('UserUserGroup','UserUserGroup'),null,null,$Total);
	
	if(count($res) > 0) {
		trigger_error("Unable to delete User Group because Users are currently assigned.");
		return -1;
	}
	
	$res = ADB_DeleteRecord('UserGroup',$UserGroupId);

}

/**
 * @descr   Saves the User to DataGroup or Role assignments or removal of assignments.
 * @param	string $FKFieldName -- "DataGroupId" or "RoleId"
 * @param	int	$FKId -- The Id value of $FKFieldName
 * @param	string $TableName -- The table name where we're adding/removing/modifying data.
 * @param	int $IdFieldName -- "UserId" or "UserGroupId"
 * @param	string $AddListStr -- If $FKFieldName = "DataGroupId" then a string of one or more (Id,AccessFlags) where Id is associated with $IdFieldName comma seperated,
 * 			else just a list of $IdFieldName Ids that need to be added to $TableName
 * @param	string $RemoveListStr -- A list of $IdFieldName Ids that need to be removed from $TableName
 * @return  int $PKId -- PK value if exists or -1.
 */
function ADB_PutUserAndUserGroupWithRoleorDataGroup($FKFieldName,$FKId,$AddListStr,$RemoveListStr){
 
	if (! $FKFieldName == 'DataGroupId' && ! $FKFieldName == 'RoleId' ) {
		trigger_error("Invalid parameters for FKFieldName.  Should be 'DataGroupId' or 'RoleId'.");
		return -3;
	}	
 
	// Remove from the list.  We need to use PK of the record in the table, so we have to get that
	// first using GetFilteredRecordList.
	if (!empty($RemoveListStr) || $RemoveListStr != ''){
		$RemoveList = explode(",",$RemoveListStr);
		foreach ($RemoveList as $RecordId){
			$RecInfo = GetTable_Field_IdInfo($RecordId,$FKFieldName);
			$TableName = $RecInfo['TableName'];
			$IdFieldName = $RecInfo['IdFieldName'];
			$IdValue = $RecInfo['Id'];
		
			// Get the PK for the record in $TableName.
			
			$DelId = ADB_GetPKForAccessLevelRoleToUserUserGroup($FKFieldName,$FKId,$TableName,$IdFieldName,$IdValue);

			// Now remove the record from $TableName using the PK.
			$res = ADB_DeleteRecord($TableName,$DelId,true);
			if ($res < 0)
				return $res;
			  
		}
	} 

	// Setup the array to pass to ADB_PutRecordData.  The data group or role Id is passed in.
	$ElementArray[$FKFieldName] = $FKId;

	// Add the newly assigned records.              
	if (!empty($AddListStr) && $AddListStr != ''){
			
		$TempAddList = explode(",",$AddListStr); 
		

		if ($FKFieldName == 'DataGroupId') {
			
			$AddList = array();
			$AccessFlagList = array();
			
			// If DataGroupId is passed in, the the AddListStr contains add Ids/accessflag Ids intertwined.
			$i = 0;
			while ($i < count($TempAddList))
			{
				$AddList[] = $TempAddList[$i++];
				$AccessFlagList[] = $TempAddList[$i++];
			}
		} else
		{
			$AddList = $TempAddList;
			$AccessFlagList = array();
		}	  
 
		$i = 0;
		foreach ($AddList as $RecordId){
			$RecInfo = GetTable_Field_IdInfo($RecordId,$FKFieldName);
			$TableName = $RecInfo['TableName'];
			$IdFieldName = $RecInfo['IdFieldName'];
			$IdValue = $RecInfo['Id'];

			// Establish the user id or usergroup id depending on what's passed in
			$ElementArray[$IdFieldName] = $IdValue;
			$ElementArray[$FKFieldName] = $FKId;
			if ($FKFieldName != 'RoleId'){
				$ElementArray['AccessFlags'] = $AccessFlagList[$i++];
			}

			$AddId = ADB_GetPKForAccessLevelRoleToUserUserGroup($FKFieldName,$FKId,$TableName,$IdFieldName,$IdValue);
 
			// Insert the new record.
			$NewId = ADB_PutRecordData($TableName,$ElementArray,$AddId);
		}
	}
}

function GetTable_Field_IdInfo($RecordId, $FKFieldName)
{
	$RecList = explode("-", $RecordId);

	$Ret = array();

	$Ret['Id'] = $RecList[1];

	if ($RecList[0] == "U" && $FKFieldName == "RoleId"){
			$Ret['TableName'] = "UserRole";
			$Ret['IdFieldName'] = "UserId";
		} elseif ($RecList[0] == "UG" && $FKFieldName == "RoleId") {
			$Ret['TableName'] = "UserGroupRole";
			$Ret['IdFieldName'] = "UserGroupId";
		} elseif ($RecList[0] == "U" && $FKFieldName == "DataGroupId") {
			$Ret['TableName'] = "AccessLevels";
			$Ret['IdFieldName'] = "UserId";
		} elseif ($RecList[0] == "UG" && $FKFieldName == "DataGroupId") {
			$Ret['TableName'] = "AccessLevelsUserGroup";
			$Ret['IdFieldName'] = "UserGroupId";
		} else {
			trigger_error("Unknown ID Type in Remove List");
			return -1;
		}
	return $Ret; 
}

/**
 * @descr   Removes the PK value for the record in $TableName based on $FKFieldName and $IdFieldName values of $FKId and $RecordId
 * @param	string $FKFieldName -- "DataGroupId" or "RoleId"
 * @param	int	$FKId -- The Id value of $FKFieldName
 * @param	string $TableName -- The table name where we're adding/removing/modifying data.
 * @param	string $IdFieldName -- "UserId" or "UserGroupId"
 * @param	int $RecordId -- Id of $IdFieldName
 */
function ADB_GetPKForAccessLevelRoleToUserUserGroup($FKFieldName,$FKId,$TableName,$IdFieldName,$RecordId){

		// Due to DataServer security restrictions, we need to treat query against AccessLevels differently.
		if ($TableName == 'AccessLevels') {
			$FKFieldName2 = 'DownToAccessLevelsByUserId.' . $FKFieldName;
			$IdFieldName2 = 'DownToAccessLevelsByUserId.' . $IdFieldName;
			
			$Results = ADB_GetFilteredRecordList('Users',0, 0,array('DownToAccessLevelsByUserId.Id'),array($FKFieldName2,$IdFieldName2),
															   array('=','='),
															   array($FKId,$RecordId),
															   array('Users','Users'),'Id','Ascending',$Total);
			$IdColName = 'DownToAccessLevelsByUserId.Id';
		} else {
			$Results = ADB_GetFilteredRecordList($TableName,0, 0,array('Id'),array($FKFieldName,$IdFieldName),
												   array('=','='),
												   array($FKId,$RecordId),
												   array($TableName,$TableName),'','',$Total);
			$IdColName = 'Id';

		}
		
		if (!isset($Results[0]))    // no record found, so it must be a new one.
			$PKId = -1;
		else $PKId = $Results[0][$IdColName];
	
		return($PKId);
}
 
/**
 * @descr   Retrieves the User's direct and UserGroup-related role assignments.
 * @param	int $UserId -- The Id of this user.  Assume the User is not Inactive to avoid extra joins/qualifiers
 * @return	array $EffectiveRoles -- An array of Effective Roles or an empty array if User has no roles or doesn't exist
 */
function ADB_GetEffectiveRoles($UserId){
	
	$EffectiveRoles = array();
	
	$UserRoles = ADB_GetFilteredRecordList('Users',0,0,
						array('DownToUserRoleByUserId.RoleId',
								'DownToUserRoleByUserId.UpToRoleByRoleId.Name',
								'DownToUserRoleByUserId.UpToRoleByRoleId.Description',
								'DownToUserRoleByUserId.UpToRoleByRoleId.DataHierarchyId',
								'DownToUserRoleByUserId.UpToRoleByRoleId.UpToDataHierarchyByDataHierarchyId.Name'),
						array('Id', 'DownToUserRoleByUserId.RoleId'),
						array('=', 'IsNotNULL'),
						array($UserId, ''),
						array('Users', 'Users'),
						'DownToUserRoleByUserId.UpToRoleByRoleId.Name','Ascending',$TotalCount);


	$UserRoles2 = array();   
	$index = 0;
	// Standardize names when comparing across arrays.
	foreach ($UserRoles as $RolesRec1)
	{
		if ($RolesRec1['DownToUserRoleByUserId.UpToRoleByRoleId.Name'] != '')
		{    // user has direct roles.
			$UserRoles2[$index]['RoleId'] = $RolesRec1['DownToUserRoleByUserId.RoleId'];
			$UserRoles2[$index]['RoleName'] = $RolesRec1['DownToUserRoleByUserId.UpToRoleByRoleId.Name'];
			$UserRoles2[$index]['RoleDescription'] = $RolesRec1['DownToUserRoleByUserId.UpToRoleByRoleId.Description'];
			$UserRoles2[$index]['DataHierarchyId'] = $RolesRec1['DownToUserRoleByUserId.UpToRoleByRoleId.DataHierarchyId'];
			$UserRoles2[$index]['DataHierarchy'] = $RolesRec1['DownToUserRoleByUserId.UpToRoleByRoleId.UpToDataHierarchyByDataHierarchyId.Name'];
			$UserRoles2[$index]['AssignedVia'] = 'User';
			$index++;
		}
	}

	$UserGroupRoles = ADB_GetFilteredRecordList('UserUserGroup',0,0,
						array('UpToUserGroupByUserGroupId.Name',
							'UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.RoleId',
							'UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.Name',
							'UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.Description',
							'UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.DataHierarchyId',
							'UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.UpToDataHierarchyByDataHierarchyId.Name'),
						array('UserId', 'UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.RoleId'),
						array('=', 'IsNotNULL'),
						array($UserId, ''),
						array('UserUserGroup', 'UserUserGroup'),
						'UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.Name','Ascending',$TotalCount);


	$UserGroupRoles2 = array();
	$index = 0;
	// Standardize names when comparing across arrays.
	foreach ($UserGroupRoles as $RolesRec2)
	{
		if ($RolesRec2['UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.Name'] != '')
		{    // user has roles via user group.
			$UserGroupRoles2[$index]['RoleId'] = $RolesRec2['UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.RoleId'];
			$UserGroupRoles2[$index]['RoleName'] = $RolesRec2['UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.Name'];
			$UserGroupRoles2[$index]['RoleDescription'] = $RolesRec2['UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.Description'];
			$UserGroupRoles2[$index]['DataHierarchyId'] = $RolesRec2['UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.DataHierarchyId'];
			$UserGroupRoles2[$index]['DataHierarchy'] = $RolesRec2['UpToUserGroupByUserGroupId.DownToUserGroupRoleByUserGroupId.UpToRoleByRoleId.UpToDataHierarchyByDataHierarchyId.Name'];
			$UserGroupRoles2[$index]['AssignedVia'] = $RolesRec2['UpToUserGroupByUserGroupId.Name'];
			$index++;
		}
	}
	
	$CombinedRoles = array_merge($UserRoles2,$UserGroupRoles2);
	asort($CombinedRoles);
	
	$EffectiveRoles = array();
	$index = -1;
	$PrevRoleId = 0;
	$CurrRoleId = 0;
	$TmpEffectiveRoles = array();
	foreach ($CombinedRoles as $RoleRec)
	{
		$CurrRoleId = $RoleRec['RoleId'];

		if ($CurrRoleId != $PrevRoleId)
		{
			$index++;
			// This is a new role.  Create an effective roles record and populate assigned via
			$TmpRoleRec['RoleId'] = $RoleRec['RoleId'];
			$TmpRoleRec['RoleName'] = $RoleRec['RoleName'];
			$TmpRoleRec['RoleDescription'] = $RoleRec['RoleDescription'];
			$TmpRoleRec['DataHierarchyId'] = $RoleRec['DataHierarchyId'];
			$TmpRoleRec['DataHierarchy'] = $RoleRec['DataHierarchy'];
			$TmpRoleRec['AssignedVia'] = ($RoleRec['AssignedVia'] == 'User' ? 'User' : $RoleRec['AssignedVia']);
			
			$TmpEffectiveRoles[$index] = $TmpRoleRec;
			
		} else {
			$TmpEffectiveRoles[$index]['AssignedVia'] = $TmpEffectiveRoles[$index]['AssignedVia'] 
														. ', ' . $RoleRec['AssignedVia'];
		}
		$PrevRoleId = $CurrRoleId;
	}
	
	
	$EffectiveRoles = array();
	// Now determine if the effective role should be hidden for this user.
	foreach ($TmpEffectiveRoles as $RolesRec) {
		$TmpRolesRec['RoleId'] = $RolesRec['RoleId'];
		$TmpRolesRec['RoleName'] = $RolesRec['RoleName'];
		$TmpRolesRec['RoleDescription'] = $RolesRec['RoleDescription'];
		$TmpRolesRec['DataHierarchyId'] = $RolesRec['DataHierarchyId'];
		$TmpRolesRec['DataHierarchy'] = $RolesRec['DataHierarchy'];
		$TmpRolesRec['AssignedVia'] = $RolesRec['AssignedVia'];
		$TmpRolesRec['Hide'] = '';

		
		$res = ADB_GetFilteredRecordList('UserRoleHidden',0,0,
						array('Id'),
						array('UserId','RoleId'),
						array('=','='),
						array($UserId,$RolesRec['RoleId']),
						array('UserRoleHidden','UserRoleHidden'),
						'','',$TotalCount);

		if (isset($res[0])) {
			// found a record, so mark this roleid as 'hide'
			$TmpRolesRec['Hide'] = 'checked';
		} else {
			$TmpRolesRec['Hide'] = '';			
		}

		$EffectiveRoles[] = $TmpRolesRec;

	}    
	
	return($EffectiveRoles);
}
 
/**
 * @descr   Retrieves the user groups to which the user belongs to.
 * @param	int $UserId -- The Id of this user.  Assume the User is not Inactive to avoid extra joins/qualifiers
 * @return	array $UserGroups -- An array of UserGroups or an empty array if User has no usergroup membership
 */
function ADB_GetUserGroupMembership($UserId){
	
	$UserGroup = ADB_GetFilteredRecordList('UserGroup',0,0,array('Id','Name','Description'),array('DownToUserUserGroupByUserGroupId.UserId'),array('='),array($UserId),array('UserGroup'),'','',$TotalCount);

	return($UserGroup);
}

/**
 * @descr   Retrieves the effective data groups for the user.
 * @param	int $UserId -- The Id of this user.  Assume the User is not Inactive to avoid extra joins/qualifiers
 * @return	array $EffectiveDataGroups -- An array of DataGroups or an empty array if User has no datagroup membership
 */
function ADB_GetEffectiveDataGroups($UserId)
{	
	$EffectiveDataGroups = array();
	$index = 0;

	$UserDataGroups = ADB_GetFilteredRecordList('Users',0,0,
			array('DownToAccessLevelsByUserId.DataGroupId',
				'DownToAccessLevelsByUserId.AccessFlags',
				'DownToAccessLevelsByUserId.UpToDataGroupsByDataGroupId.Name',
				'DownToAccessLevelsByUserId.UpToDataGroupsByDataGroupId.Description'),
			array('Id', 'DownToAccessLevelsByUserId.UpToDataGroupsByDataGroupId.ParentDataGroupId'),
			array('=', 'IsNull'),
			array($UserId, ''),
			array('Users','Users', 'Users'),
			'','',$TotalCount);

	$UserGroupDataGroups = ADB_GetFilteredRecordList('UserUserGroup',0,0,
			array('UserGroupId',
				'UpToUserGroupByUserGroupId.Name',
				'UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.DataGroupId',
				'UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.AccessFlags',
				'UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.UpToDataGroupsByDataGroupId.Name',
				'UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.UpToDataGroupsByDataGroupId.Description'),
			array('UpToUsersByUserId.Id'),
			array('='),
			array($UserId),
			array('UserUserGroup'),
			'UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.DataGroupId','Ascending',$TotalCount);


	// Now we need to walk through both lists and figure out the effective permissions.
	
	// User's direct-assigned data groups take precedence, unless the accessflags = 0 (Undefined), so populate effective
	// list with all user direct-assigned data groups whose access flags != 0 first.
	$index = 0;
	$TmpDataGroups = array();
	foreach ($UserDataGroups as $DGRec1){
		$TmpDataGroups[$index]['Id'] = $DGRec1['DownToAccessLevelsByUserId.DataGroupId'];
		$TmpDataGroups[$index]['Name'] = $DGRec1['DownToAccessLevelsByUserId.UpToDataGroupsByDataGroupId.Name'];
		$TmpDataGroups[$index]['Description'] = $DGRec1['DownToAccessLevelsByUserId.UpToDataGroupsByDataGroupId.Description'];
		$TmpDataGroups[$index]['AccessFlags'] = $DGRec1['DownToAccessLevelsByUserId.AccessFlags'];
		$TmpDataGroups[$index]['AssignedVia'] = 'U'; // 'U' assigned via indicates assigned via 'User'
		$index++;
	}
	
	foreach ($UserGroupDataGroups as $DGRec2){
		$TmpDataGroups[$index]['Id'] = $DGRec2['UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.DataGroupId'];
		$TmpDataGroups[$index]['Name'] = $DGRec2['UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.UpToDataGroupsByDataGroupId.Name'];
		$TmpDataGroups[$index]['Description'] = $DGRec2['UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.UpToDataGroupsByDataGroupId.Description'];
		$TmpDataGroups[$index]['AccessFlags'] = $DGRec2['UpToUserGroupByUserGroupId.DownToAccessLevelsUserGroupByUserGroupId.AccessFlags'];
		$TmpDataGroups[$index]['AssignedVia'] = $DGRec2['UpToUserGroupByUserGroupId.Name'];
		$index++;
	}
	
	asort($TmpDataGroups);
	$PrevDGId = 0;
	$CurrDGId = 0;
	$index = -1;
	foreach($TmpDataGroups as $DGRec3) {
		$CurrDGId = $DGRec3['Id'];
			
		if ($CurrDGId != $PrevDGId){
			$index++;
			$EffectiveDataGroups[$index]['Id'] = $DGRec3['Id'];
			$EffectiveDataGroups[$index]['Name'] = $DGRec3['Name'];
			$EffectiveDataGroups[$index]['Description'] = $DGRec3['Description'];
			$EffectiveDataGroups[$index]['EffectiveDG'][] = array('IsEffective' => 1,
																'Via' => $DGRec3['AssignedVia'],
																'AccessFlags' => $DGRec3['AccessFlags']);
			$PrevDGId = $CurrDGId;
		}
		else {
			$EffectiveDataGroups[$index]['EffectiveDG'] = CalcEffectiveDG($EffectiveDataGroups[$index]['EffectiveDG'],$DGRec3);

		}

	}

	return($EffectiveDataGroups);
}

/**
 * @descr   Calculates the effective data group in an array mixed with user and usergroup datagroup access levels.
 * @param	array $CurrDGPerm -- The existing array of DG assignments/perms.
 * @param	array $DGPerm -- The new record to add to array and check.
 * @return	array  -- An array containing the list of user/usergroups, access levels, and which one is effective.
 */
function CalcEffectiveDG($CurrDGPerm, $DGPerm)
{
	$BestPermIndex = array();
	$BestPerm = -1;
	
	$NewDG = array('IsEffective' => 0, 'Via' => $DGPerm['AssignedVia'], 'AccessFlags' => $DGPerm['AccessFlags']);
	
	$CurrDGPerm[] = $NewDG;

	$index = 0;

	// We need to make two passes through the array. 
	// First to determine which index is the best perm.
	// Second pass to re-assign 'IsEffective'
	
	// First pass:
	foreach ($CurrDGPerm as $PermRec1){

		if ($PermRec1['Via'] == 'U'){
			if ($PermRec1['AccessFlags'] != 0){
				// User assigned data group that's is not "Undefined".
				// so best permission.
				$BestPermIndex = array($index);
				break;
			}
		} else {
			if ($PermRec1['AccessFlags'] == $BestPerm) {
				$BestPermIndex[] = $index;
			}
			if ($PermRec1['AccessFlags'] > $BestPerm) {
				$BestPermIndex = array($index);
				$BestPerm = $PermRec1['AccessFlags'];
			}
		}

		$index++; 
	}
	
	$index = 0;
	foreach ($CurrDGPerm as $PermRec2) {
		if (in_array($index,$BestPermIndex)){
			$CurrDGPerm[$index]['IsEffective'] = 1;
		} else {
			$CurrDGPerm[$index]['IsEffective'] = 0;
		}
		$index++;
	}

	return $CurrDGPerm;
}

/**
 * @descr   Maps the numeric access levels to strings.
 * @param	int $Level -- The level number.
 * @return	string $Desc  -- The string representing that access level.
 */
function MapDataGroupAccessLevels($Level)
{   
	if($Level == 0) {
		$Desc = "Full Control";
	} elseif($Level == 1) {
		$Desc = "No Access";
	} elseif($Level == 2) {
		$Desc = "Read Only";
	} elseif($Level == 6) { 
		$Desc = "Full Control";
	} else $Desc = "";
	
	return $Desc;
}

/**
 * @descr   Save a record in the UserRoleHidden table.
 * @param	int $RoleId -- The RoleId to be hidden.
 * @param	int $checked -- indicates whether to hide or not to hide.  We will need to delete any record in UserRoleHidden if the roleid/userid is unchecked
 * @param	int $Userid -- The userid of the user whose role we wish to hide.
 * @return	value >= 0 or -1 indicating success of fail respectively.
 */
function ADB_UpdateUserRoleHidden($RoleId, $checked, $UserId )
{
	$res = ADB_GetFilteredRecordList('UserRoleHidden',0,0,
				array('Id'),
				array('UserId','RoleId'),
				array('=','='),
				array($UserId,$RoleId),
				array('UserRoleHidden','UserRoleHidden'),
				'','',$TotalCount);
	
	if (isset($res[0])) {
		if ($checked == 'unchecked') {
			$res = ADB_DeleteRecord('UserRoleHidden',$res[0]['Id']);
			return $res;
		}			
	} else { // we didn't find the record.
		if ($checked == 'checked') {
			$Rec['UserId'] = $UserId;
			$Rec['RoleId'] = $RoleId;
			$res = ADB_PutRecordData('UserRoleHidden',$Rec);
			return $res;
		}
	}
				
	return 0;
 
} 


/**
 * @descr   Save search/display field admin info.
 * @param	int $Fieldtype - 1 = search, 2 = display
 * @param	string $AddListStr - list of ids to add.
 * @param	string $RemoveListStr - list of ids to remove.
 * @return	value >= 0 or -1 indicating success of fail respectively.
 */ 
function ADB_SaveFTSSearchAndDisplayFields($FieldType,$AddListStr,$RemoveListStr)
{
	$Total = 0;

	if (!(($FieldType == 1) || ($FieldType == 2))) 
	{
		trigger_error("Field type must be 1 or 2");
	}
	
	// Remove from the list.  We need to use PK of the record in the table, so we have to get that
	// first using GetFilteredRecordList.
	if (!empty($RemoveListStr) || $RemoveListStr != '')
	{
		$RemoveList = explode(",",$RemoveListStr);
		foreach ($RemoveList as $RecordId){
		
			$res = ADB_GetFilteredRecordList('FreeTextSearchField',0,0,
						array('Id'),
						array('DataFieldId','SearchFieldType'),
						array('=','='),
						array($RecordId,$FieldType),
						array('FreeTextSearchField','FreeTextSearchField'),
						'','',$Total);

			if(!isset($res[0]['Id'])){
				SpectrumLog("ADB_SaveFTSSearchAndDisplayFields: couldn't find record to delete.");
			} else {
				$DelId = $res[0]['Id'];

				// Now remove the record from $TableName using the PK.
				$res = ADB_DeleteRecord('FreeTextSearchField',$DelId,true);
				if ($res < 0)
					return $res;
			}        
			  
		}
	} 
	// Add the newly assigned records.
	if (!empty($AddListStr) && $AddListStr != '')
	{
		// Setup the array to pass to ADB_PutRecordData.
		$ElementArray['SearchFieldType'] = $FieldType;

		$AddList = explode(",",$AddListStr); 	  
 
		$i = 0;
		foreach ($AddList as $RecordId){
			$ElementArray['DataFieldId'] = $RecordId;
		
			$res = ADB_GetFilteredRecordList('FreeTextSearchField',0,0,
						array('Id'),
						array('DataFieldId','SearchFieldType'),
						array('=','='),
						array($RecordId,$FieldType),
						array('FreeTextSearchField','FreeTextSearchField'),
						'','',$Total);

			if(!isset($res[0]['Id'])){
				$AddId = -1;
			} else {
				$AddId = $res[0]['Id']; 
			}
  
		   // Insert/Update the new record.
		   $NewId = ADB_PutRecordData('FreeTextSearchField',$ElementArray,$AddId);
		}
	}
}


/**
 * Database function to return TB folder structure represented in DataGroups table.
 *
 * @param bool $categoryOnlyOption 		Defaults to false. If true, only returns the category level of the TB. 
 * @return array|bool                    Returns nested array representing tree structure.
 * 
 */
function ADB_GetTBTree($categoryOnlyOption = false)
{
	$Total = 0;
	
	$TBLevel1 = ADB_GetFilteredRecordList('DataGroups',
							0,
							0,
							array('Id','Name'),
							array('Name'),
							array('='),
							array('_HCCC'),
							array('DataGroups'),
							'',
							'',
							$Total);

	if (!isset($TBLevel1[0])){
		 trigger_error("Cannot find _HCCC DataGroup");
		 return -1;
	}
	
	$rootTBDataGroupId = $TBLevel1[0]['Id'];
	
	$TBLevel2 = ADB_GetFilteredRecordList('DataGroups',
							0,
							0,
							array('Id','Name'),
							array('ParentDataGroupId'),
							array('='),
							array($rootTBDataGroupId),
							array('DataGroups'),
							'',
							'',
							$Total);

	if (!isset($TBLevel2[0])){
		 trigger_error("Cannot find Care Conference DataGroups under _HCCC");
		 return -1;
	}
   
	$index = 0;
	foreach($TBLevel2 as $TBRec1) {
		$TmpTBLevel3 = ADB_GetFilteredRecordList('DataGroups',
							0,
							0,
							array('Id','Name'),
							array('ParentDataGroupId'),
							array('='),
							array($TBRec1['Id']),
							array('DataGroups'),
							'',
							'',
							$Total);
		
		$TBLevel3 = array();

		foreach($TmpTBLevel3 as $TBRec2){
			$TBRec2['Level'] = "2";
			$TBLevel3[] = $TBRec2;
		}
		$TBLevel2[$index]['Level'] = "1";
		
		$TBTree[] = array('Id' => $TBLevel2[$index]['Id'], 'Name' => $TBLevel2[$index]['Name'], 'Level' => $TBLevel2[$index]['Level'], 'Children' => ($categoryOnlyOption == false) ? $TBLevel3 : array());
		$index++;

	}
	
	return $TBTree;
}	



/**
 * Database function to create a TB folder under the main TB Folders tree represented in DataGroups table.
 * 
 * @param string  $TBFolderName     	TB name
 * @param integer $ParentId          	id parent TB folder/DataGroup
 * @return array|bool                   Returns Id of new record or error.
 * 
 */
function ADB_CreateTBFolder($TBFolderName,$ParentId)
{
	// Get the parent datagroup name which we will pre-pend to the name to create description.
	$Total = 0;
	$res = ADB_GetFilteredRecordList('Datagroups',0, 0,
				array('Name'),
				array('Id'),
				array('='),
				array($ParentId),
				array('DataGroups'),
				'','',$Total);
	
	if (!isset($res[0])){
		trigger_error("Can't find parent datagroup");
		return -1;
	}
	$ParentName = $res[0]['Name'];
	
	$DataGroupRec['Name'] = $TBFolderName;
	$DataGroupRec['Description'] = $ParentName . " - " . $TBFolderName;
	$DataGroupRec['ParentDataGroupId'] = $ParentId;

	$res = ADB_PutRecordData('DataGroups',$DataGroupRec);
	
	return $res;
}

/**
 * Database function to rename a TB folder under the main TB Folders tree represented in DataGroups table.
 * 
 * @param string  $TBId 		    	TB Id
 * @param integer $NewName          	New name of TB folder.
 * @return array|bool                   Returns Id of updated record or error.
 * 
 */
function ADB_RenameTBFolder($TBId,$NewName)
{
	// Get the parent datagroup name which we will pre-pend to the name to create description.
	$Total = 0;
	$res = ADB_GetFilteredRecordList('Datagroups',0, 0,
				array('UpToDataGroupsByParentDataGroupId.Name'),
				array('Id'),
				array('='),
				array($TBId),
				array('DataGroups'),
				'','',$Total);
	
	if (!is_array($res)){
		trigger_error("Can't find parent datagroup");
		return -1;
	}
	$ParentName = $res[0]['Name'];

	$DataGroupRec['Name'] = $NewName;
	$DataGroupRec['Description'] = $ParentName . " - " . $NewName;

	$res = ADB_PutRecordData('DataGroups',$DataGroupRec,$TBId);
	
	return $res;
}

/**
 * Database function to remove a TB folder under the main TB Folders tree represented in DataGroups table.
 * 
 * @param string  $TBId 		    	TB Id
 * @return array|bool                   Returns 0 or error.
 * 
 */
function ADB_DeleteTBFolder($TBId)
{
	// get Spectrum Health Care client
	$client = GetSOAPShcClient ();
	
	$ParamsArray = GetAuthVars ();
	$ParamsArray[] = new SoapParam($TBId, 'TumorBoardId');
	$res = $client->__soapCall(	'DeleteTumorBoard', $ParamsArray, array('encoding'=>'UTF-8'));

	CheckDBResult($res);
	return $res;	
}

function ADB_CreateRoleCopy($roleName, $roleDescription, $dataHierarchyId, $allowSelfAssign, $copyRoleId)
{
	$client = GetSOAPSecurityClient();
	$parameters = GetAuthVars();
	$parameters[] = new SoapParam($allowSelfAssign, "AllowSelfAssign");
	$parameters[] = new SoapParam($copyRoleId, "CopyRoleId");
	$parameters[] = new SoapParam($dataHierarchyId, "DataHierarchyId");
	$parameters[] = new SoapParam($roleDescription, "Description");
	$parameters[] = new SoapParam($roleName, "Name");
	$res = $client->__soapCall("CreateRoleCopy", $parameters, array('encoding'=>'UTF-8'));
	
	CheckDBResult($res);
	return $res;
}

/**
 * Database function to assign a case to a TB folder.
 * 
 * @param string  $TBId 		    	TB Id
 * @param string  $CaseId 		    	Case Id
 * @return array|bool                   Returns nested array representing tree structure.
 * 
 */
function ADB_AssignTBCase($TBId,$CaseId,$Replace)
{
	if (($DataGroupId = isAssignedToTumorBoard($CaseId)) === 0) {
		// get Spectrum Health Care client
		$client = GetSOAPShcClient ();
		
		$ParamsArray = GetAuthVars ();
		$ParamsArray[] = new SoapParam($CaseId, 'CaseId');
		$ParamsArray[] = new SoapParam($TBId, 'TumorBoardId');
		$ParamsArray[] = new SoapParam($Replace, "Replace");
		$res = $client->__soapCall(	'CreateCaseTumorBoard', $ParamsArray, array('encoding'=>'UTF-8'));
	}
	else {
		$client = GetSOAPShcClient ();

		$SoapVars = GetAuthVars();
		$MoveTumorBoardXML = "<CaseIds><CaseId>".$CaseId."</CaseId></CaseIds>";
		$MoveTumorBoardXML .= "<OldTumorBoardDataGroupId>".$DataGroupId."</OldTumorBoardDataGroupId>";
		$MoveTumorBoardXML .= "<NewTumorBoardDataGroupId>".$TBId."</NewTumorBoardDataGroupId>";
		
		$SoapVars[] = new SoapVar($MoveTumorBoardXML, 147);

		$res = $client->__soapCall(	'MoveCaseToTumorBoard',		//SOAP Method Name
									$SoapVars);			//Parameters
	}
	
	CheckDBResult($res);
	return $res;	
}

/**
 * Database function to check if case is assigned to a TB workflow.
 * 
 * @param string  $CaseId 		    	Case Id
 * @return int                   Returns DataGroupId of TB or 0 if not assigned.
 * 
 */
function isAssignedToTumorBoard($CaseId)
{
	// Right now we're only checking if this case is in the TB datagroup.
	// TODO need to also check that a copy of this case isn't already there and not allow it.
	$TotalCount = 0;
	$res = ADB_GetFilteredRecordList(
			'Case',
			0, 0,
			array('DataGroupId'),
			array('Id','UpToDataGroupsByDataGroupId.UpToDataGroupsByParentDataGroupId.UpToDataGroupsByParentDataGroupId.DownToPALModuleByRootDataGroupId.Name'),
			array('=','='),
			array($CaseId,cDataGroups::ShcTumorBoard_PALName),
			array('Case','Case'),
			'','',$TotalCount);
	
	if (!isset($res[0]))
		return 0;
	else 
		return $res[0]['DataGroupId'];
}

/**
 * Database function to remove a TB folder under the main TB Folders tree represented in DataGroups table.
 * 
 * @param string  $TBId 		    	TB Id
 * @return array|bool                   Returns 0 or error.
 * 
 */
function ADB_MoveTBFolder($TBId,$Parent_TBId)
{
	$DataGroupRec = array();
	
	$DataGroupRec['ParentDataGroupId'] = $Parent_TBId;

	$res = ADB_PutRecordData('DataGroups',$DataGroupRec,$TBId);

	return $res;	
}

/*
 * Function to update the position of a case
 *
 * @param	int			$CaseId			Case ID
 * @param	int			$NewPosition	1-based position
 * @return	array|bool					Returns 0 or error
 *
 */
function ADB_UpdateCasePositions($CaseId, $NewPosition) 
{
	$client = GetSOAPShcClient();
	$SoapVars = GetAuthVars();


	$Xml = "<CaseId>" . $CaseId . "</CaseId>" . 
		   "<NewPosition>" . $NewPosition . "</NewPosition>";

	$SoapVars[] = new SoapVar($Xml, 147);

	$Result = $client->__soapCall("UpdateCasePositions", $SoapVars);
	return $Result;
}


/**
 * Database function to return date format in system settings.
 * 
 * @return string			null or string containing format.
 * 
 */

function ADB_GetSystemConfigDateFormatString()
{
	$Total = 0;
	
	$res = ADB_GetFilteredRecordList(
			'Config',0,0,
			array('Value'),
			array('Name'),
			array('='),
			array('DateFormatHint'),
			array('Config'),
			'','',$Total);
	
	if (!isset($res[0]['Value'])){
		return null;
	}
	
	return $res[0]['Value'];
}

function ADB_GetWorkflowAutomaticEmailFlag($WorkflowName)
{
	$result = ADB_GetFilteredRecordList("PALModule", 0, 0,
									array("AutoGenerateEmails"),
									array("Name"),
									array("="),
									array($WorkflowName),
									array("PALModule"));
									
	if ($result == null || !isset($result[0]['AutoGenerateEmails']))
	{
		trigger_error("Invalid Tumor Board workflow.");
	}
	
	return $result[0]['AutoGenerateEmails'];
}

function ADB_SaveWorkflowEmailFlag($WorkflowName,$emailFlag)
{
	$result = ADB_GetFilteredRecordList("PALModule", 0, 0,
									array("Id"),
									array("Name"),
									array("="),
									array($WorkflowName),
									array("PALModule"));
									
	if (isset($result[0]["Id"]))
	{
		ADB_PutRecordData("PALModule", array("AutoGenerateEmails" => $emailFlag), $result[0]["Id"]);
	}
	else
	{
		trigger_error("Invalid Tumor Board workflow.");
	}    
}

function ADB_GetTumorBoardCategories()
{
	$result = ADB_GetFilteredRecordList("PALModule", 0, 0,
											array("RootDataGroupId"),
											array("Name"),
											array("="),
											array("TumorBoard"),
											array("PALModule"));
	 if (!isset($result[0]["RootDataGroupId"]) || $result[0]["RootDataGroupId"] < 0)
	 {
		 trigger_error("RootDataGroupId is not set for TumorBoard.");
	 }
	 
	 return ADB_GetFilteredRecordList("DataGroups", 0, 0,
											array("Id", "Name", "Description"),
											array("ParentDataGroupId"),
											array("="),
											array($result[0]["RootDataGroupId"]),
											array("DataGroups"));                                       
}

function ADB_UpdateTumorBoardCategories($categories)
{
	$result = ADB_GetFilteredRecordList("PALModule", 0, 0,
										array("RootDataGroupId"),
										array("Name"),
										array("="),
										array("TumorBoard"),
										array("PALModule"));
	if (!isset($result[0]["RootDataGroupId"]) || $result[0]["RootDataGroupId"] < 0)
	{
		trigger_error("RootDataGroupId is not set for TumorBoard.");
	}
	 
	$hasErrors = false;
	$tumorBoardDataGroupId = $result[0]["RootDataGroupId"];
	foreach ($categories as $category)
	{
		$result = ADB_PutRecordData("DataGroups",
										array("Name" => $category->Category, "Description" => $category->Description, "ParentDataGroupId" => $tumorBoardDataGroupId),
										(int)$category->CategoryId);
		if ($result < 1)
		{
			$hasErrors = true;
		}
	}
	return $hasErrors;    
}

function ADB_GetCasePriorities()
{
	return ADB_GetFilteredRecordList("CasePriority", 0, 0,
										array("Id", "BaseName", "Name", "Description", "DisplayOrder"),
										array("DisplayOrder"),
										array("<"),
										array("99"),
										array("CasePriority"),
										"DisplayOrder",
										"Ascending"); 
} 

function ADB_GetDefaultCasePriority()
{
	return ADB_GetFilteredRecordList("CasePriority", 0, 0,
										array("Id", "BaseName", "Name", "Description", "DisplayOrder"),
										array("DisplayOrder"),
										array("="),
										array("99"),
										array("CasePriority")); 
}

function ADB_UpdateCasePriorities($casePriorities)
{
	$client = GetSOAPShcClient();
	$soapVars = GetAuthVars();
	$casePrioritiesXml = "<CasePriorities>";
	foreach($casePriorities as $casePriority)
	{
		$casePrioritiesXml .= "<CasePriority>";
		$delete = false;
		if ($casePriority instanceof stdClass)
		{
			$delete = isset($casePriority->Delete) && $casePriority->Delete;
		}
		else
		{
			$delete = isset($casePriority["Delete"]) && (bool)$casePriority["Delete"];
		}
		$delete = $delete ? "true" : "false";
		$casePrioritiesXml .= "<Delete>$delete</Delete>";
		if (isset($casePriority->Description))
		{
			$casePrioritiesXml .= "<Description>{$casePriority->Description}</Description>";
		}  
		if (isset($casePriority->DisplayOrder))
		{
			$casePrioritiesXml .= "<DisplayOrder>{$casePriority->DisplayOrder}</DisplayOrder>";
		}
		if ($casePriority instanceof stdClass)
		{
			$casePrioritiesXml .= "<Id>{$casePriority->Id}</Id>";
		}
		else
		{
			$casePrioritiesXml .= "<Id>{$casePriority["Id"]}</Id>";
		}
		if (isset($casePriority->Name))
		{
			$casePrioritiesXml .= "<BaseName>{$casePriority->Name}</BaseName>";
		}
		$casePrioritiesXml .= "</CasePriority>";
	}
	$casePrioritiesXml .= "</CasePriorities>";
	$soapVars[] = new SoapVar($casePrioritiesXml, XSD_ANYXML);
	$res = $client->__soapCall("DoCasePriorityAction", $soapVars);
	
	CheckDBResult($res);
	return $res;      
}

function ADB_UpdateCaseTabCaptionFields($caseTabFieldsAdded)
{
	$client = GetSOAPShcClient();
	$soapVars = GetAuthVars();
	$dataFieldsXml = "<DataFieldIds>";
	foreach($caseTabFieldsAdded as $dataFieldId)
	{
		$dataFieldsXml .= "<DataFieldId>$dataFieldId</DataFieldId>";
	}
	$dataFieldsXml .= "</DataFieldIds>";
	$soapVars[] = new SoapVar($dataFieldsXml, XSD_ANYXML);
	$res = $client->__soapCall("UpdateCaseTabCaptionFields", $soapVars);
	
	CheckDBResult($res);
	return $res;    
}

 /**
 * @descr   Returns tab text for a given TableName and RecordId
 * @param    array $TableName -- The tablename
 * @param    string $RecordId -- The record id  
 */   
function ADB_GetRecordCaptionText($TableName, $RecordId)
{
	$client = GetSOAPPalClient();
	$soapVars = GetAuthVars();
	$soapVars[] = new SoapParam($TableName,'TableName');
	$soapVars[] = new SoapParam($RecordId,'RecordId');
	
	$res = $client->__soapCall('GetRecordCaptionText', $soapVars);
	CheckDBResult($res);
	$res = MakeArray($res);
	if (isset($res['RecordCaptionText']))
		return $res['RecordCaptionText'];
	$RecordCaptionText = new stdClass();
	$RecordCaptionText->DefaultCaptionText = new stdClass();
	$RecordCaptionText->CaptionText = $TableName . ' ' . $RecordId;
	return $RecordCaptionText;
}

 /**
 * @descr   Returns ESig data for a table record
 * @param    array $TableName -- The tablename
 * @param    string $RecordId -- The record id
 */   
function ADB_GetESigHistory($TableName, $RecordId)
{
	$client = GetSOAPImageClient ();

	$ParamsArray = GetAuthVars();
	$ParamsArray[] = new SoapParam($TableName, 'TableName');
	$ParamsArray[] = new SoapParam($RecordId, 'RecordId');
	$ParamsArray[] = new SoapVar('<ColumnList>Id PostedOn SigDescription SigFullName RfcReason</ColumnList>', 147);
	$ParamsArray[] = new SoapVar('<Sort By="Id" Order="Descending" />', 147);
	
	$res = $client->__soapCall(	'GetESigHistory',		//SOAP Method Name
								$ParamsArray);		//Parameters
	$EsigHistory = array();
	if(is_array($res))
	{
		if ($res['GetESigHistoryResult']->ASResult == 0)
		{
			if (isset($res['GenericDataSet']->DataRow))
			{
				$DataRows = MakeArray($res['GenericDataSet']->DataRow);
				foreach($DataRows as $DataRow)
				{
					// the data row comes back as an object, but it's more useful
					// if we turn it into an associative array
					$RowFields = array();
					foreach($DataRow as $key => $value) 
					{
					   $RowFields[$key] = $value;
					}
		
					$EsigHistory[] = $RowFields;
				}
			}	
		}
		else
			return ReportDataServerError($res['GetESigHistoryResult']);
	}
	else
	{
		if($res->ASResult != 0)
		{
			return ReportDataServerError($res);
		}
	}

	return $EsigHistory;
}


 /**
 * @descr   Returns array sorted by the value of a key in multi dimension array.
 * @param    array $a -- The multi dimen array to be sorted.
 * @param    string $thiskey -- The name of the key to be sorted.
 */   
function DoSort_Array($a, $thiskey) 
{
	foreach($a as $k => $v) 
	{
		$b[$k] = strtolower($v[$thiskey]); 
	}
	asort($b);
	foreach($b as $key => $val)
	{
		$c[] = $a[$key];
	}

	return $c;
}
/**
 * @descr   Returns array of available dataview fields in array.
 * @param   int $DataViewId - The Id for the view in dbo.DataView
 * @param   array $Options -- For future use, an array of key value pairs.
 * @return  array  -- An array containing the list of all possible visible fields in the dataview.
 */   
function ADB_GetAvailableDataViewFields($DataViewId, $Options = null) 
{
	if (! isset($DataViewId))
	{
		trigger_error("Cannot retrieve DataView fields without valid DataViewId", E_NOTICE);
	}
	{   
		$ParamsArray = GetAuthVars ();
		$ParamsArray[] = new SoapParam ($DataViewId, 'DataViewId');
		
		$client = GetSOAPImageClient(); 
		$res = $client->__soapCall( 'GetAvailableDataViewFields', $ParamsArray);
		
		if(is_array($res))
		{   
			if (isset($res['DataFields']->DataField))
			{     
				if (is_array($res['DataFields']->DataField))
				{
					// array of fields
					return $res['DataFields']->DataField;
				}
				else
				{
					// just one field
					$arr = array();
					$arr[] = $res['DataFields']->DataField;
					return $arr;
				}
			}
			else
			{
				// no fields
				return array();
			}
				
		}
		else
		{
			if($res->ASResult != 0)
			{
				//trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
				trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_NOTICE);
			}
		}    
	}
}

/**
* Replaces key names representing foreign key columns with UpTo mapping for multicolumn sort.
* This function is necessary when List columns are clicked on directly (overriding multicolumn 
* sort) which passes in a string value such as SO-StainId which must be replaced with 
* SO-UpToStainByStainId.ShortName.  
* 
* For example, given a three column sort, the Value field of dbo.UserConfig is of the form:
* SO-UpToStainByStainId.ShortName,Ascending,SO-BlockId,Ascending,SO-CompressedFileLocation,Ascending
*
* A mapping of Foreign Key replacement key/value pairs is maintained in cDatabaseReader private member.
* 
* @param	string	$String	key/value pairs for dbo.UserConfig
* @return	string	key/value pairs containing any applicable foreign key adjustment
* @var cDatabaseReader
*/
function MakeForeignKeyAdjustmentsToString($String)
{
	if ($String === null || $String === '')
	{
		return $String;
	}
	
	$DBReader = new cDatabaseReader();
	$ForeignKeyAdjustments = $DBReader->GetForeignKeyAdjustments();
	if (is_array($ForeignKeyAdjustments))
	{
		$ReplacementString = '';
		$delim = ',';
		$SortPrefix = 'SO-';
		
		$word = strtok($String, $delim);
		while (is_string($word))
		{
			// SO-fields
			if (0 === strcmp($SortPrefix, substr($word, 0, 3)))
			{
				$ForeignKeyField = substr($word,3);
				if (array_key_exists($ForeignKeyField, $ForeignKeyAdjustments ))
				{
					$word = $SortPrefix . $ForeignKeyAdjustments[$ForeignKeyField];
				}
			}
			$ReplacementString .= $word;
			$word = strtok($delim);
			// Comma separator
			if (is_string($word))
			{
				$ReplacementString .= $delim;
			}
		}
		return  $ReplacementString;
	}
	return $String;
}
?>

