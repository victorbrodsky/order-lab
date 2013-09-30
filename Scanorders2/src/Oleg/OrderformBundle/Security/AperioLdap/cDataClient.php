<?php
namespace Oleg\OrderformBundle\Security\AperioLdap;

class cDataClient {
    
public  $Token          = "";
public  $RenewToken     = true;
private $DataServerURL  = "";
private $ImgClient      = null;
private $SecClient      = null;
private $ProxyHost      = "";
private $ProxyPort      = "";
private $ProxyUser      = "";
private $ProxyPassword  = "";

// constructor
function cDataClient($DataServerURL, $ProxyHost = "", $ProxyPort = "", $ProxyUser = "", $ProxyPassword = "")
{
    //echo "me";
    //exit();
    // initialize soap clients
    $this->DataServerURL    = $DataServerURL;
    $this->ProxyHost        = $ProxyHost;
    $this->ProxyPort        = $ProxyPort;
    $this->ProxyUser        = $ProxyUser;
    $this->ProxyPassword    = $ProxyPassword;
    
    $this->__wakeup();
}

public function __wakeup()
{
    // need to reconnect soap clients on wakeup
    $Params = array();
    $Params["location"]     = $this->DataServerURL . "/Aperio.Security/Security2.asmx";
    $Params["uri"]          = "http://www.aperio.com/webservices/";
    
    if ($this->ProxyHost != "" && $this->ProxyPort != "")
    {
        $Params["proxy_host"]   = $this->ProxyHost;
        $Params["proxy_port"]   = (int) $this->ProxyPort;
        
        if ($this->ProxyUser != "")
        {
            $Params["login"]    = $this->ProxyUser;
        }
        
        if ($this->ProxyPassword != "")
        {
            $Params["password"] = $this->ProxyPassword;
        }
    }
    
    $this->SecClient = new cExtendedSoapClient(NULL, $Params);
    
    $Params["location"] = $this->DataServerURL . "/Aperio.Security/Image.asmx";    
    $this->ImgClient = new cExtendedSoapClient(NULL, $Params);
}
    


function Authenticate($UserName, $Password)
{
    // since this is usually the first call to dataserver put it in a try/catch block
    // because it might fail if dataserver is not accessible.  If an exception occurs
    // we can display a decent error message.
    try
    {
        $res = $this->SecClient->__soapCall(    'Logon',                                                                    //SOAP Method Name
                                    array('soap_version'=>SOAP_1_2,new SoapParam($UserName, 'UserName'),         //Parameters
                                    new SoapParam($Password, 'PassWord')));    
    }
    catch (Exception $e) 
    {
        $DataServerURL = GetDataServerURL();
        trigger_error("Spectrum SOAP Error:  Unable to communicate with DataServer at $DataServerURL", E_USER_ERROR);
    }

    $ReturnArray = array('ReturnCode'=>'-1','ReturnText'=>'');

    if(is_array($res))
    {
        if($res['LogonResult']->ASResult == 0)
        {
            $ReturnArray['ReturnCode'] = 0;
            $ReturnArray['Token'] = $res['Token'];
            $ReturnArray['UserId'] = $res['UserData']->UserId;
            $ReturnArray['FullName'] = $res['UserData']->FullName;
            $ReturnArray['LoginName'] = $res['UserData']->LoginName;
            $ReturnArray['Phone'] = $res['UserData']->Phone;
            $ReturnArray['E_Mail'] = $res['UserData']->E_Mail;
            $ReturnArray['LastLoginTime'] = $res['UserData']->LastLoginTime;
            $ReturnArray['PasswordDaysLeft'] = $res['UserData']->PasswordDaysLeft;
            $ReturnArray['UserMustChangePassword'] = $res['UserData']->UserMustChangePassword;
            if (isset($res['UserData']->StartPage))
            $ReturnArray['StartPage'] = $res['UserData']->StartPage;
            else
                $ReturnArray['StartPage'] = '/Welcome.php';
            $ReturnArray['AutoView'] = ($res['UserData']->AutoView == '1') ? true : false;
            $ReturnArray['ViewingMode'] = $res['UserData']->ViewingMode;
            $ReturnArray['DisableLicenseWarning'] = ($res['UserData']->DisableLicenseWarning == '1') ? true : false;
            if (isset($res['UserData']->ScanDataGroupId))
            $ReturnArray['ScanDataGroupId'] = $res['UserData']->ScanDataGroupId;
            else
                $ReturnArray['ScanDataGroupId'] = DEFAULT_DATAGROUP;
        }
        
        $this->Token = $ReturnArray['Token'];
    }
    if(is_object($res))
    {
        $ReturnArray['ReturnCode'] = $res->ASResult;
        $ReturnArray['ReturnText'] = $res->ASMessage;
    }
    return $ReturnArray;

}

function GetAuthVars ()
{
    return array
    (
        new SoapParam ($this->Token, 'Token'),
        new SoapParam ($this->RenewToken ? '0' : '1', 'DoNotRenewToken')
    );
}

function IsValidToken($Token)
{   
    $res = $this->SecClient->__soapCall(    'IsValidToken',                                                    //SOAP Method Name
                                array(new SoapParam($Token, 'Token')));            //Parameters    

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



/**
* Accepts a valid existing token and returns a new token for the same
* user and same role.
* 
* @return string $Token     - new token
* 
*/
function GetAdditionalToken()
{

    $res = $this->SecClient->__soapCall ('GetAdditionalToken', $this->GetAuthVars());

    if(is_array($res))
    {
        if($res['GetAdditionalTokenResult']->ASResult == 0)
        {
            return $res['Token'];
        }            
    }
    return ReportDataServerError($res);   

}

//------------------------------------------------------------------
// CreateNewUser -  add a new user to the User table and return the new UserId.
//------------------------------------------------------------------
function AddNewUser ($FullName, $PhoneNumber, $Email, $LoginName, $Password, $UserMustChangePassword, $DisableLicenseWarning, $ViewingMode)
{
    $client = $this->SecClient;
    
    // create the UserData XML
    $dom = new DOMDocument();
    $ndUserData = $dom->CreateElement('UserData');
    $ndUserData->appendChild($dom->CreateElement('LoginName', xmlencode($LoginName)));
    $ndUserData->appendChild($dom->CreateElement('FullName', xmlencode($FullName)));
    $ndUserData->appendChild($dom->CreateElement('Phone', xmlencode($PhoneNumber)));
    $ndUserData->appendChild($dom->CreateElement('E_Mail', xmlencode($Email)));
    $ndUserData->appendChild($dom->CreateElement('Password', xmlencode($Password)));
    $ndUserData->appendChild($dom->CreateElement('DisableLicenseWarning', $DisableLicenseWarning));
    $ndUserData->appendChild($dom->CreateElement('ViewingMode', ($ViewingMode?'True':'False')));
    
    $ndPrivileges = $dom->CreateElement('Privileges');
    $ndUserData->appendChild($ndPrivileges);
    
    $ndAccountStatus = $dom->CreateElement('AccountStatus');
        $ndAccountStatus->appendChild($dom->CreateElement('LockOnInvalidPass','True'));
        $ndAccountStatus->appendChild($dom->CreateElement('LockIfUnused','True'));
        $ndAccountStatus->appendChild($dom->CreateElement('PasswordCanExpire','True'));
        $ndAccountStatus->appendChild($dom->CreateElement('UserMustChangePassword', ($UserMustChangePassword?'True':'False')));
    $ndUserData->appendChild($ndAccountStatus);
    
    $UserDataXML = $dom->saveXML($ndUserData);
    
    $ParamsArray = $this->GetAuthVars();
    $ParamsArray[] = new SoapVar ($UserDataXML, 147);
    
    $res = $client->__soapCall ('AddUser',                    // SOAP Method Name
                                $ParamsArray,                // Parameters    
                                NULL, NULL, $OutputHeaders, true);    // Don't make multiple attempts
    
    if(is_array($res))
    {
        if($res['AddUserResult']->ASResult == 0)
        {
            return $res['UserId'];
        }            
    }
    if(is_object($res) && $res->ASResult != 0)
    {
        if ($res->ASResult == -7023 || $res->ASResult == -7026)
            return $res;
        return ReportDataServerError($res);
    }
}

//------------------------------------------------------------------
// ListUsers -  Returns a list of users    
//        @return array        - List of users in the DB      
//------------------------------------------------------------------
function ListUsers()
{
    $client = $this->SecClient;   
    
    $res = $client->__soapCall(    'ListUsers',        //SOAP Method Name
                                $this->GetAuthVars ());    //Parameters
    
    $UnsortedList = array ();
    if(is_array($res))
    {
        // if there is more than one user then return the array, otherwise
        // we need to create the array manually.
        if(is_array($res['UserDataArray']->UserData))
        {
            $UnsortedList = $res['UserDataArray']->UserData;
        }
        else
        {
            $arr = array();
            $arr[] = $res['UserDataArray']->UserData;
            $UnsortedList = $arr;
        }
    }
    else
    {
        if($res->ASResult != 0)
        {
            return ReportDataServerError($res);
        }
    }
    
    foreach ($UnsortedList as $User)
        $Cache [$User->UserId] = $User;
    ksort ($Cache);
    
    return $Cache;
}

//------------------------------------------------------------------
// IsLoginTaken - see if the specified login is taken by a user
//     other than the one specified in UserId
//------------------------------------------------------------------
function IsLoginTaken($LoginName, $UserId=-1)
{
    $UserList = $this->ListUsers();
    foreach ($UserList as $User)
    {
        if (strtolower($User->LoginName) == strtolower($LoginName) && ($User->UserId != $UserId) )
            return true;
    }
    return false;
}

/**********************************************************
 * ListAccessByUser -  Returns the user's access level to each
 *         of the datagroups.  If no user is specified, it lists the 
 *        current user's datagroups and access levels.
 * 04/16/08 msmaga    Added 'Forced' parameter to verify updates
 **********************************************************/
function ListAccessByUser($UserId=null, $Forced=false)
{
    static $Access = null;
    
    if (($Access === null) || ($Forced))
    {
        $client = $this->SecClient;
        
        $ParamsArray = $this->GetAuthVars();
        
        if ($UserId != null)
        {
            // pass the UserId if it was set
            $ParamsArray[] = new SoapParam($UserId, 'UserId');
        }
        $res = $client->__soapCall(    'ListAccessByUser',        //SOAP Method Name
                                    $ParamsArray);            //Parameters
        
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
 * ListAccessByDataGroup -  returns a list of users who
 *      have access to the specified datagroup along with
 *      their access level
 **********************************************************/
function ListAccessByDataGroup($DataGroupId, $PrivateOnly=0)
{

    $client = $client = $this->SecClient;  
    
    $ParamsArray = $this->GetAuthVars();  
    
    $ParamsArray[] = new SoapParam($DataGroupId, 'DataGroupId');
    $ParamsArray[] = new SoapParam($PrivateOnly, 'PrivateOnly');      

    $res = $client->__soapCall( 'ListAccessByDataGroup', $ParamsArray);
    
    if(is_array($res))
    {
        // if there is more than one user then return the array, otherwise
        // we need to create the array manually.
        $Access = array();
        if (isset($res['AccessDataByDataGroupArray']->AccessDataByUser))
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
// UpdateAccessByUser -  updates the user's access level to each
//         of the the specified datagroups
//------------------------------------------------------------------
function UpdateAccessByUser($UserId, $DataGroupIds, $AccessLevels)
{
    $client = $this->SecClient;
    
    
    $AccessByUserXML = "<AccessDataByUserArray><UserId>$UserId</UserId>";
    for ($i=0; $i<count($DataGroupIds); $i++)
    {
        $DataGroupId = $DataGroupIds[$i];
        $AccessLevel = $AccessLevels[$i];
        $AccessByUserXML = $AccessByUserXML . "<AccessDataByUser><DataGroupId>$DataGroupId</DataGroupId><AccessFlags>$AccessLevel</AccessFlags></AccessDataByUser>";
    }
    
    $AccessByUserXML = $AccessByUserXML . '</AccessDataByUserArray>';
    
    $ParamsArray = $this->GetAuthVars();
    $ParamsArray[] = new SoapVar($AccessByUserXML, 147);
    
    $res = $client->__soapCall(    'UpdateAccessByUser',        //SOAP Method Name
                                $ParamsArray);                //Parameters
    
    if($res->ASResult != 0)
    {
        return ReportDataServerError($res);
    }

}



function GetRoleByName($RoleName)
{
    $Filters = array();
    $Filters[] = new cFilter("Role", "Name", "=", $RoleName);
    $Roles = $this->GetTable(NULL, "Role", $Filters, NULL, NULL, NULL);
    if (is_array($Roles))
    {
        // Roles is a one element array, return the one element
        return $Roles[0];
    }
    return NULL;
}

// Return list of roles (RoleRecord) that belong to this user, keyed by role id.
function GetUserRoles($UserId)
{
    $Records = $this->GetUserRoleRecordsById($UserId);

    // Now get role definitions
    $Roles = array();
    foreach ($Records as $Record)
    {
        $Roles[] = $this->GetRole($Record->RoleId);
    }

    return $this->SortForDisplay($Roles);
}

// Return list of user role records that belong to this user, indexed by role Id
function GetUserRoleRecordsById($UserId)
{
    // Get the list of role Ids for this user
    $Filters = array();
    $Filters[] = new cFilter("UserRole", "UserId", "=", $UserId);
    $Records = $this->GetTable(NULL, "UserRole", $Filters, NULL, NULL, NULL);

    $OrderedList = array();
    foreach ($Records as $Record)
    {
        $OrderedList[$Record->RoleId] = $Record;
    }
    return $OrderedList;
}

function SortForDisplay($RoleRecords)
{
    global $LAST_SYSTEM_ROLE;
    global $ANALYSIS_ROLE;

    $Roles = array();

    // First list user's roles
    foreach ($RoleRecords as $RoleRecord)
    {
        if ($RoleRecord->Id > $LAST_SYSTEM_ROLE)
            $Roles[$RoleRecord->Name] = $RoleRecord;
    }

    // Sort alphabetically
    ksort($Roles);

    // Now add system roles
    // NOTE: '_' alphabetically precedes lower case roles, thus add these after the ksort()
    foreach ($RoleRecords as $RoleRecord)
    {
        if ($RoleRecord->Id <= $LAST_SYSTEM_ROLE)
        {
            // We do not display the _Analysis role
            if ($RoleRecord->Id != $ANALYSIS_ROLE)
                $Roles[$RoleRecord->Name] = $RoleRecord;
        }
    }

    return $Roles;
}

// Generic routine to issue a GetFilteredRecordList for retrieving database records.
// ent:    Method to call in DataServer (NULL = GetFilteredRecordList)
//         Name of database table
//         array of cFilters for search criteria
//         array of columnNames to return
//         array of SoapParams (allows client to set almost any criteria)
//         cSortField object
function GetTable($MethodName, $TableName, $Filters, $SelectColumns, $SoapParams, $Sort)
{
    // Clear any previous error
    unset ($_SESSION['DataServerError']);

    $client = $this->ImgClient;

    $ParamsArray = $this->GetAuthVars();
    $ParamsArray[] = new SoapParam($TableName, 'TableName');

    if ($SoapParams)
    {
        // Client passed SoapParams
        foreach ($SoapParams as $parm)
            $ParamsArray[] = $parm;
    }

    if ($Filters)
    {
        foreach ($Filters as $Filter)
        {
            $dom = new DOMDocument(null, 'utf-8');
            $elFilterBy = $dom->CreateElement('FilterBy');
            $elFilterBy->setAttribute('Column', $Filter->ColumnName);
            $elFilterBy->setAttribute('FilterOperator', $Filter->Operator);
            $elFilterBy->setAttribute('FilterValue', $Filter->Value);
            if ($Filter->TableName)
                $elFilterBy->setAttribute('Table', $Filter->TableName);
            $ParamsArray[] = new SoapVar($dom->saveXML($elFilterBy), 147);
        }
    }

    if ($SelectColumns)
    {
        // $SelectColumnsXML = '<ColumnList>[ColumnName] [Access]</ColumnList>';
        $SelectColumnsXML = '<ColumnList>';
        foreach ($SelectColumns as $ColumnName)
                $SelectColumnsXML = $SelectColumnsXML . ' [' . $ColumnName . '] ';
        $SelectColumnsXML .= '</ColumnList>';
        $ParamsArray[] = new SoapVar($SelectColumnsXML, 147);
    }

    if ($Sort)
    {
        $SortByXML =  "<Sort By=\"$Sort->FieldName\" Order=\"$Sort->SortOrder\"/>";
        $ParamsArray[] = new SoapVar($SortByXML, 147);
    }

    if ($MethodName == NULL)
        $MethodName = 'GetFilteredRecordList';

    $res = $client->__soapCall($MethodName,
                            $ParamsArray,
                            array('encoding'=>'UTF-8'));

    if (is_array($res) == false)
        return ReportDataServerError($res);

    //$Status = $res['GetFilteredRecordListResult'];
    //$TotalNumRecords = $res['TotalRecordCount'];
    $DataSet = $res['GenericDataSet'];
    if (is_string($DataSet))
    {
        // Soap's way of saying no records
        //return NULL;
        // Return an empty array
        return array();
    }
    if (is_array($DataSet->DataRow))
        return $DataSet->DataRow;
    // Return a one element array
    return array($DataSet->DataRow);
}


/************************************************
 * PutRecordData - If an Id is passed then updated the specified
 *    row in the specified table.  Otherwize add a new row to the
 *    specified table.
 * 04/08/08 msmaga    ESIG checking
 ************************************************/
function PutRecordData($TableName, $NameValues, $Id=-1)
{
    return $this->PutRecord('PutRecordData', $TableName, $NameValues, $Id);
}


function PutRecord($Method, $TableName, $NameValues, $Id)
{
    $client = $this->ImgClient;
                                       
    // create the DataRow XML
    $dom = new DOMDocument();
    $ndDataRow = $dom->CreateElement('DataRow');
    foreach ($NameValues as $ColumnName=>$ColumnValue)
    {
        $ndColumn = $dom->CreateElement($ColumnName, xmlencode($ColumnValue));
        $ndDataRow->appendChild($ndColumn);
    }
    $DataRowXML = $dom->saveXML($ndDataRow);
    
    $ParamsArray = $this->GetAuthVars ();
    $ParamsArray[] = new SoapParam($TableName, 'TableName');
    
    // if an ID was passed then use it.
    if ($Id > -1)
        $ParamsArray[] = new SoapParam($Id, 'Id');

    $ParamsArray[] = new SoapVar($DataRowXML, XSD_ANYXML);     //147

    $res = $client->__soapCall($Method, $ParamsArray);

    if(is_array($res))
    {
        if ($res[$Method . 'Result']->ASResult == 0)
        {
            return $res['Id'];
        }
        if(($res['ASResult'] == ESIG) || ($res['ASResult'] == ESIG2))
            return ESIG;
    }
    if(is_object($res))
    {
        if(($res->ASResult == ESIG) || ($res->ASResult == ESIG2))
            return ESIG;
        if($res->ASResult == DESCRIPTION_EXISTS)
            return DESCRIPTION_EXISTS;
        else if($res->ASResult != 0)
        {
            trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
        }
    }
}

//------------------------------------------------------------------
// GetRecordData -  Returns an array of data values for the given
//        record ID in the specified table.
//------------------------------------------------------------------
/**
* Returns an array of data values for the given record ID in the specified table.
* 
* @param string $TableName    - DB Table to look in
* @param int $Id            - Id of the record to retrieve
* 
* @return array ()            - Array in the form of ColumnName=>Value of the record's data
* 
* - thoare 080828    Gave the function a cache to prevent excessive DataServer calls
*/
function GetRecordData($TableName, $Id)
{
    global $RecordDataCache;
    
    if (!isset ($RecordDataCache [$TableName][$Id]))
    {
        $client = $this->ImgClient;  
                                            
        $ParamsArray = $this->GetAuthVars();
        $ParamsArray[] = new SoapParam($TableName, 'TableName');
        $ParamsArray[] = new SoapParam($Id, 'Id');
        
        $res = $client->__soapCall(    'GetRecordData',    //SOAP Method Name
                                    $ParamsArray);        //Parameters


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
            if($res->ASResult == ROLE_VIOLATION)
            {
                $RecordDataCache [$TableName][$Id] = false;
            }
            elseif($res->ASResult != 0)
            {
                trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
            }
        }
    }
    
    return $RecordDataCache [$TableName][$Id];
}

function AddDataGroup($GroupName, $GroupDescription)
{
    $client = $this->SecClient;
    
    $EscapedGroupName = xmlencode($GroupName);
    $EscapedGroupDesc = xmlencode($GroupDescription);
    
    $DataGroupXML = "<DataGroup><Name>$EscapedGroupName</Name><Description>$EscapedGroupDesc</Description></DataGroup>";
    
    $ParamsArray = $this->GetAuthVars();
    $ParamsArray[] = new SoapVar($DataGroupXML, 147);
    
    $res = $client->__soapCall('AddDataGroup', $ParamsArray);   
                        
    if(is_array($res))
    {
        return $res['DataGroupId'];
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
// DeleteDataGroup - Delete the specified datagroup
//------------------------------------------------------------------
function DeleteDataGroup($DataGroupId)
{
    $client = $this->SecClient;
    
    $ParamsArray = $this->GetAuthVars();
    $ParamsArray[] = new SoapParam($DataGroupId, 'DataGroupId');
    
    $res = $client->__soapCall( 'DeleteDataGroup',              //SOAP Method Name
                                $ParamsArray);                  //Parameters
    
    if($res->ASResult != 0)
    {
        trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
    }
}

//------------------------------------------------------------------
// ListDataGroups -  Returns a 2d array of datagroup data
//------------------------------------------------------------------
function ListDataGroups()
{   
    $res = $this->SecClient->__soapCall( 'ListDataGroups',          //SOAP Method Name
                                $this->GetAuthVars());              //Parameters
    
    if(is_array($res))
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
    {
        if($res->ASResult != 0)
        {
            trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
        }
    }    
}

//------------------------------------------------------------------
// ListClientSystemDataGroupsResponse -  Returns an array of datagroups
//      associated with the specified ClientSystemId.
//------------------------------------------------------------------
function ListClientSystemDataGroups($SystemId)
{   
    $ParamsArray = $this->GetAuthVars ();
    $ParamsArray[] = new SoapParam ($SystemId, 'SystemId');
    
    $res = $this->ImgClient->__soapCall( 'ListClientSystemDataGroups', $ParamsArray);
    
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
    
    //print_r($arr);
    //exit;
}


//------------------------------------------------------------------
// GetFilteredRecordList -  Returns a 2D array (i.e. list of records)
//        based on the specified filter.
//------------------------------------------------------------------
function GetFilteredRecordList($TableName='Slide', $RecordsPerPage=0, $PageIndex=0, $SelectColumns=array(), $FilterColumns=array(), $FilterOperators=array(), $FilterValues=array(), $FilterTables=array(), $SortByField='', $SortOrder='Descending', &$OutTotalCount)
{
    global $RecordDataCache;
    
    $OutTotalCount = 0;
    $Distinct = false;
    
    $ParamsArray = $this->GetAuthVars ();
    $ParamsArray[] = new SoapParam($TableName,'TableName');
    $ParamsArray[] = new SoapParam($PageIndex, 'PageIndex');
    if ($RecordsPerPage > 0) $ParamsArray[] = new SoapParam($RecordsPerPage, 'RecordsPerPage');
    
    // Build the filter arguments, Count how many tables for DISTINCT keyword
    for ($i=0; $i<count($FilterValues); $i++)
    {
        $FilterColumn = $FilterColumns[$i];
        $FilterOperator = $FilterOperators[$i];
        $FilterValue = ($FilterOperator == 'LIKE' || $FilterOperator == 'NOTLIKE')  ? '%'.EscapeSQLchars($FilterValues[$i]).'%' : $FilterValues[$i];
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
        
        $ParamsArray[] = new SoapVar($dom->saveXML($elFilterBy), 147);
        
        if ($TableName != $FilterTables[$i])
            $Distinct = true;
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
    if ($SortByField == '' && in_array($SortByField, $SelectColumns)) $SortByField = ($TableName == 'Image' ? 'ImageId' : 'Id');
    $SortByXML =  "<Sort By=\"$SortByField\" Order=\"$SortOrder\"/>";
    $ParamsArray[] = new SoapVar($SortByXML, 147);
    

    $res = $this->ImgClient->__soapCall(    'GetFilteredRecordList',        //SOAP Method Name
                                $ParamsArray,                     //Parameters
                                array('encoding'=>'UTF-8'));     //Option

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
                    $RowFields = array();
                    foreach($DataRow as $key => $value) 
                    {
                       $RowFields[$key] = $value;
                    }
                    
                    $DataRows[] = $RowFields;
                }
            }
        }
        
        // Caching
        if (isset ($_SESSION ['HierarchyLevels'][$TableName]))
        {
            /**
            * @var cTableSchema
            */
            $TableSchema = $_SESSION ['HierarchyLevels'][$TableName];
            
            if (!isset ($RecordDataCache [$TableName]))
                $RecordDataCache [$TableName] = array ();
            
            foreach ($DataRows as $Row)
            {
                if (isset ($Row [$TableSchema->IdFields [0]->ColumnName]))
                {
                    $Id = $Row [$TableSchema->IdFields [0]->ColumnName];
                    $RecordDataCache [$TableName][$Id] = array ();
                    
                    foreach ($Row as $Name => $Value)
                        $RecordDataCache [$TableName][$Id][$Name] = $Value;
                    
                    if ($TableName == 'Slide' && $Row ['ImageId'] > 0)
                    {
                        $RecordDataCache ['Image'][$Row ['ImageId']] = array ();
                        
                        foreach ($Row as $Name => $Value)
                            $RecordDataCache ['Image'][$Row ['ImageId']][$Name] = $Value;
                    }
                }
            }
        }
        
        return $DataRows;            
    }
    if(is_object($res))
    {
        trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
    }    
}

//------------------------------------------------------------------
// GetChildList -  Returns a list of child data records
//        that belong to the specified record parent record.  Since this 
//        could be a very large list, MaxRecords limits the count.
//------------------------------------------------------------------
function GetChildList($ParentId,  $ParentTableName , $ChildTableName)
{
    $client = $this->ImgClient;
    
    $ParamsArray = $this->GetAuthVars();
    $ParamsArray[] = new SoapParam($ParentTableName,'ParentTableName');
    $ParamsArray[] = new SoapParam($ParentId,'ParentId');
    $ParamsArray[] = new SoapParam($ChildTableName,'ChildTableName');
    
    $res = $client->__soapCall(    'GetChildList',        //SOAP Method Name
                                $ParamsArray);         //Parameters
    
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
    if(is_object($res))
    {
        if($res->ASResult != 0)
        {
            trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
        }
    }
}


//------------------------------------------------------------------
// GetRecordImages -  Returns a list of images that are
//        associated with the specified data record in the specified
//        table.
//------------------------------------------------------------------
function GetRecordImages($Id,  $TableName , $MaxImages = 200)
{
    $client = $this->ImgClient;
    
    $ParamsArray = $this->GetAuthVars ();
    $ParamsArray[] = new SoapParam($TableName,'TableName');
    $ParamsArray[] = new SoapParam($Id,'Id');
    
    $res = $client->__soapCall(    'GetRecordImages',        //SOAP Method Name
                                $ParamsArray);             //Parameters

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
            trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR);
        }
    }    
    
    return $ImageList;
}


//------------------------------------------------------------------
// GetRecordDocuments -  Returns a list of child documents
//        that belong to the specified record parent record.
//------------------------------------------------------------------
function GetRecordDocuments($ParentId,  $ParentTable)
{
    $client = $this->ImgClient;
    
    $ParamsArray = $this->GetAuthVars();
    $ParamsArray[] = new SoapParam($ParentTable,'TableName');
    $ParamsArray[] = new SoapParam($ParentId,'Id');
    
    $res = $client->__soapCall(    'GetRecordDocuments',    //SOAP Method Name
                                $ParamsArray);            //Parameters
                                            
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
            trigger_error("DataServer Error: $res->ASResult: $res->ASMessage", E_USER_ERROR); 
        }
    }    
}



//------------------------------------------------------------------
// ADB_SendEmail -  
//------------------------------------------------------------------
function SendEmail($From, $To, $CC = "", $BCC = "", $Subject = "", $Body = "", $Timeout = 5000)
{   
    $ParamsArray = $this->GetAuthVars ();
    $ParamsArray[] = new SoapParam ($From, 'From');
    $ParamsArray[] = new SoapParam ($To, 'To'); 
    $ParamsArray[] = new SoapParam ($CC, 'CC'); 
    $ParamsArray[] = new SoapParam ($BCC, 'Bcc'); 
    $ParamsArray[] = new SoapParam ($Subject, 'Subject'); 
    $ParamsArray[] = new SoapParam ($Body, 'Body');
    $ParamsArray[] = new SoapParam ($Timeout, 'Timeout');  
    
    $client = $this->ImgClient; 
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



}
?>
