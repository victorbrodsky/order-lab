<?
/**
* This contains php utility functions
* 
* @package Utils
*
* - vunger 	080401		Added IsSpectrumPlus(), DeepCopy()
* - thoare 	08/04/15		Added Report Image Thumbnails
* - vunger 	080422		Changed GetInstallType() to support login decision
* 						Changed SpectrumErrorHandler() to support different error pages
* 						Add GetTopTable(), ReturnSuccess(), HasRolePermission().
* 						Added NewPermitted(), AddPermitted(). (probably temporary).
* 						Added support for php subdirectories, including InitializeHtml().
* - vunger 	080424		Added IsConfigured()
* - vunger 	080508		Modified UpdataSystemSettings() to only add hierarchy tables as "Features"
* 							if they are in the current session's installation type
* 						Removed GetLicenseKey();
* - vunger 	080603		Implementation of Licensing.
* - vunger 	080604		Branched some CheckUser() functionality into InitializePage() to avoid some DataServer hits.
* - msmaga	080613		Added SsKeyTableName() for SsFieldConfig reference
* - vunger 	080625		Added IsLicensed()
* - vunger 	080702		Added ErrorReturn(), GetPassedParms(), CheckTimeFormat()
* - vunger 	080711		Added GetDataHierarchyTypes(), GetDataHierarchyNames()
* - msmaga	080825		Added FormattedXML as development aid
* - vunger	080917		Added SetErrorPage(), SavePageParms(), GetURL(), InitializePage() now sets default handler.
* - vunger	081030		Added NullErrorHandler(), support for SafeMode
* - vunger	081031		Added SetDefaultErrorPage(), Added check for $_SESSION in InitializePage
* - ellis	081104		urlencode thumbnail url's
* - msmaga 	081111		Added ValidXMLString(); ValidXMLFile()
* - vunger	090225		Added ExtractCell()
* - vunger	090305		Added SetReturnParms(),
* 						Added array_first_key(), array_first_element(), array_last_key(), array_last_element()
* - vunger	090903		Added GetVersion() and AddTask()
* - jmedellin 091106 	Added support for ViewingMode
* - vunger	100426		Fixed DeepCopy2() for embedded objects
* - rellis	100624		Remove GetReportTemplateFolder
* - rellis	100820		Correction to GetPasswordRequirementsString, changed $CONFIG to $_SESSION['Config'] 
* - rellis	101022		add GetReferer function
* - rellis	101108		modify GoToStartPage to ensure start page is valid for the current hierarchy
* - rellis	101118		add GetGlobalImageShare()
* - rellis	101215		correction to AppendThumbnailHTMLNode to pass ThumbnailHeight to GetThumbnailURL()
* - pkraft	110603		corrected GetUserName()
* - pkraft	110721		Added SetSessionServerAndHostAddresses() to be called in UpdateConfiguration()
* - pkraft	110810		SetSessionServerAndHostAddresses() does not append port if SSL is on
* - pkraft	120224		Added TidyUpDataServerErrorMessage() to make return errors user-friendly
* - vunger	120316		Moved TidyUpDataServerErrorMessage() to DatabaseRoutines.php
* - rellis	120614		Remove unset($_SESSION['DefaultImageShare']) from UpdateSystemSettings
*
*/

include_once '/History.php';
include_once '/DatabaseRoutines.php';
include_once '/cImageFile.php';
include_once '/ErrorHandlingFunctions.php';

// The following is a garbage value.  I picked it to be a very unlikely
// legitimate input so I could have some value to catch when recieving data
// from forms that might be using the vocabulary fields.  Just don't ask where
// it came from.  Hopefully, I'll find a better way later.
define ("INVALID_VALUE", "THE RINGWORLD IS UNSTABLE!!!");

// Aperio error codes
define ('E_ENTRY', (E_USER_WARNING + 1));

// DataGroup definitions
define ('DEFAULT_DATAGROUP', '1');

// definition for 'Slide Specific Processing "Type"'
define ('SSTYPE', 'SlideSpecific');

define('HKEY_CURRENT_USER', 0x80000001);


function GetSpectrumVersion()
{
	// try to read the spectrum version from the session
	// variable.  If it doesn't exist, then try to read
	// it from a text file and also save it in the session
	// variable for next time.
///???	if (session_id () == '') session_start();
	
	if (isset($_SESSION['SpectrumVersion']))
	{
		return $_SESSION['SpectrumVersion'];
	}
	// get version info from SpectrumVersion.txt
	elseif (file_exists('SpectrumVersion.txt'))
	{
		$fp = fopen('SpectrumVersion.txt', 'r');
		list ($major, $minor, $revision, $build) = fscanf($fp,'%d%d%d%d');
		fclose($fp);
		$_SESSION['SpectrumVersion'] = "$major.$minor.$revision.$build";
		return $_SESSION['SpectrumVersion'];
	}
	elseif (file_exists('..\SpectrumVersion.txt'))
	{
		$fp = fopen('..\SpectrumVersion.txt', 'r');
		list ($major, $minor, $revision, $build) = fscanf($fp,"%d%d%d%d");
		fclose($fp);
		$_SESSION['SpectrumVersion'] = "$major.$minor.$revision.$build";
		return $_SESSION['SpectrumVersion'];
	}
	else
	{
		$major = '0';
		$minor = '0';
		$revision = '0';
		$build = '0';
		$_SESSION['SpectrumVersion'] = "$major.$minor.$revision.$build";
		return $_SESSION['SpectrumVersion'];
	}
}

// Return major/minor version number
function GetVersion()
{
	$Version = GetSpectrumVersion();
	$pos = strrpos($Version, '.');
	return substr($Version, 0, $pos);
}

function GetProductName()
{
	return 'eSlideManager';
}
function IniSettings()
{
	if (isset($_SESSION['Config']['ini']) == false)
	{
		$IniArray = my_parse_ini_file();

		if (isset($IniArray['default_socket_timeout']) == false)
		{
			// Default to a 30 second socket timeout (the PHP default is 10 seconds)
			$IniArray['default_socket_timeout'] = 30;
		}

		foreach ($IniArray as $Name => $Value)
		{
			// First check for user overrides of standard php ini settings
			if ($Name == 'memory_limit')
			{
				$_SESSION['Config']['ini']['memory_limit'] = $Value;
				// Ensure this value is passed in to every new page (see InitializePage())
				AssignCookie('memory_limit', $Value);
			}
			elseif ($Name == 'max_execution_time')
				$_SESSION['Config']['ini']['max_execution_time'] = $Value;
			elseif ($Name == 'default_socket_timeout')
				$_SESSION['Config']['ini']['default_socket_timeout'] = $Value;
			else  // not a php ini setting - store as a config setting
			{
				// fix a bug(?) in parsing UNC paths. If a UNC path is specified in the ini file with quotes around it, e.g.
				//  DocumentUploadDirectory="\\aperio-00816\UploadedDocs\", then it will be parsed as \aperio-00816\UploadedDocs\ rather than \\aperio-00816\UploadedDocs\ (one leading slash rather than two leading slashes),
				// but if it is not enclosed in quotes, then it parses properly. So, handle the case where it starts with a single slash
				if ($Name == 'DocumentUploadDirectory' || $Name == 'ImageUploadDirectory' || $Name == 'ReportTemplateFolder')
				{
					// if it starts with a slash
					if (strlen($Value) > 1)
					{
						if (substr($Value, 0, 1) === "\\")
						{
							// but the second character is not a slash, add an additional slash
							if (substr($Value, 1, 1) !== "\\")
								$Value = "\\" . $Value;
						}
					}
				}
				$_SESSION ['Config'][$Name] = $Value;
			}
		}
	}

	foreach ($_SESSION['Config']['ini'] as $key => $value)
	{
		ini_set ($key, $value);
	}
}

function my_parse_ini_file()
{
	// look for the ini file relative to the htdocs folder
	//PAL
	$File = $_SERVER['DOCUMENT_ROOT'] . '/../conf/Spectrum.ini';
	if (file_exists($File))
	{
		return parse_ini_file($File);
	}
	// fail
	else 
	{
		trigger_error('Spectrum.ini file not found');
	}
}

function CreateURI($URL)
{
	return (GetBaseURL() . '/' . $URL);
}

function GetBaseURL()
{
	if (isset($_SESSION['Config']['ini']['SpectrumURL']))
	{
		// A proxy server may be translating browser requests to a different protocol (eg. https) or address (Apache rewrite)
		// Thus the server address here is different than that of the browser.
		return $_SESSION['Config']['ini']['SpectrumURL'];
	}

	if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on'))
		return 'https://' . $_SERVER['HTTP_HOST'];
	else
		return 'http://' . $_SERVER['HTTP_HOST'];
}


function GetHTTP()
{
	if (IsSSL())
		return 'https:';
	else
		return 'http:';
}

function IsSSL()
{
	return isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on');
}

// Includes port if applicable; called in SetSessionVars.php
/* When SSL is on, the HTTP port must be 80.  When SSL is not on, the HTTP port may be standard (80) or 
 * nonstandard (e.g., 81). SSL must be on standard port 443.  If we later wish to support nonstandard SSL ports, 
 * then change to: 
 * if ((!IsSSL() && $_SERVER['SERVER_PORT'] != '80') || (isSSL() &&  $_SERVER['SERVER_PORT'] != '443'))
*/
function SetSessionServerAndHostAddresses ()
{
	$ServerAddr = $_SERVER['SERVER_ADDR'];
	$HostAddr = $_SERVER['SERVER_NAME'];
	if (!IsSSL() && $_SERVER['SERVER_PORT'] != '80')
	{
		if (strpos($ServerAddr, ':') === False)
		{
			$ServerAddr .=  ':' . $_SERVER['SERVER_PORT'];
		}
		if (strpos($HostAddr, ':') === False)
		{
			$HostAddr .= ':' . $_SERVER['SERVER_PORT'];
		}
	}
	$_SESSION['ServerAddr'] = $ServerAddr;
	$_SESSION['HostAddr'] = $HostAddr;
}

// $_SESSION['ServerAddr'] has port as well as $_SERVER_ADDR if port is not 80
function GetSessionServerAddress ()
{
	return isset($_SESSION['ServerAddr']) ?  $_SESSION['ServerAddr'] : $_SERVER['SERVER_ADDR']; 
}

function GetHtmlBase()
{
	// Spectrum's base is at http://$host
	return isset($_SESSION['HostAddr']) ?  $_SESSION['HostAddr'] : $_SERVER['SERVER_NAME'];
}

function AddOptionsTask(DOMElement $cell, $id, $name)
{
    $dom = $cell->ownerDocument;
    $hiddenField = $dom->createElement("input");
    $hiddenField->setAttribute("type", "hidden");
    $hiddenField->setAttribute("value", $name);
    $hiddenField->setAttribute("name", "MacroID" . $id . "HiddenNameField");
    $cell->appendChild($hiddenField);
    $radioButton = $dom->createElement("input");
    $radioButton->setAttribute("name", "MacroRadioButtonList");
    $radioButton->setAttribute("type", "radio");
    $radioButton->setAttribute("align", "middle");
    $radioButton->setAttribute("value", $id);
    $radioButton->setAttribute("onclick", "SetSelectedMacro(this)");
    $cell->appendChild($radioButton);
}

//
// ent:	Image file path
// 		Flag on whether to add the token to the URL
//
function GetImageServerURLForImage ($ImageLoc, $AddToken = true)
{
	$ImageFile = new cImageFile($ImageLoc);
	$ImageFile->AddTokenToURL($AddToken);
	if (IsSSL())  
		return $ImageFile->GetSSLURL();
	return $ImageFile->GetURL();
}

function RefreshImageServers($Parms='')
{
	$ImageServerURLs = GetInternalImageServerURLs();
	foreach ($ImageServerURLs as $URL)
	{
		$ImageFile = new cImageFile($URL);
		$ImageFile->AddIdToURL(false);	// entire ImageSever reset
		$ImageFile->Reset($Parms);
	}
}


function GetDataServerURL()
{
	// create the DataServer URL from Spectrum ini file
	
	$IniArray = my_parse_ini_file();
	if (isset($IniArray['DataServerHost']) && isset($IniArray['DataServerPort']))
	{
		return "http://" . $IniArray['DataServerHost'] . ":" . $IniArray['DataServerPort'];
	}
	else
	{
		spectrum_error("Could not read 'DataServerHost' and 'DataServerPort' from Spectrum.ini", E_USER_ERROR);
	}
}

function GetTheme()
{
	if (isset($_SESSION['Config']['Theme'])) {
		return $_SESSION['Config']['Theme'];
	} else {
		return 'unknown';	
	}
}

function GetLoginLogo()
{
	return '<div id="loginLogo"><img src ="/Images/eSlideManager_lg.png" /></div>';
}

function GetInstallType()
{
	$IniArray = my_parse_ini_file();
	$InstallType = $IniArray['InstallationType'];
	return $InstallType;
}
function GetDefaultTheme()
{
	$DefaultTheme = 'blue';
	return $DefaultTheme;
}

function GetRecordsPerPage()
{
	// check if value in $Config
	$Config = $_SESSION['Config'];
	
	return $Config['RecordsPerPage'];
}


/**
* Returns the user name for the specified user id
* 
* @param int $UserId	- User Id to retrieve the name of
* 
* @return string		- Coresponding user name
*/
function GetUserName ($UserId)
{
	$Users = ADB_ListUsers();
	
	// try and return the user name.
	// if we don't have permnission return "User X"
	foreach ($Users as $User)
	{
		if ($User->UserId == $UserId)
			$UserName = $User->FullName;			
	}
	if (isset($UserName))
		return $UserName;
	else
		return "User $UserId";
}

/**
* Returns the macro name for the specified macro id
* 
* @param int $MacroId	- Macro Id to retrieve the name of
* 
* @return string		- Coresponding macro name
*/
function GetMacroName ($MacroId)
{
	static $Macros = null;
	
	if ($Macros == null)
		$Macros = ADB_GetFilteredRecordList ('Macro', GetRecordsPerPage (), 1, array ('MacroId', 'MacroName'), array ('MacroMarkedDeleted'), array ('='), array ('0'), array ('Macro'), 'MacroId', 'Descending', $Total);
	
	foreach ($Macros as $Macro)
		if ($Macro ['MacroId'] == $MacroId)
			return $Macro ['MacroName'];
	
	return '';
}

function GetDocumentUploadDirectory()
{
	// read the value from Spectrum.ini
	
	//$IniArray = my_parse_ini_file();
	$IniArray = $_SESSION ['Config'];
	if (isset($IniArray['DocumentUploadDirectory']))
	{
		return $IniArray['DocumentUploadDirectory'];
	}
	else
	{
		return "C:\\UploadedDocs\\";
	}
}


function GetImagesRoot()
{
	if (IsSpectrumForEducators())
		return GetWebSlidesFolder();

	// look in the ImageServer.ini file in \Program Files\ScanScope\ImageServer
	$ImageServerIni = '';
	$Folder = getenv('PROGRAMFILES') . '\\ScanScope\\ImageServer\\';
	if (file_exists($Folder . 'ImageServer.ini'))
		$ImageServerIni = $Folder . 'ImageServer.ini';

	// if we found ImageServer.ini, then read the Root folder from it.
	if ($ImageServerIni != '')
	{
		$fh = fopen($ImageServerIni, 'r');
		$contents = fread($fh, filesize($ImageServerIni));
		// break up the in contents into words
		$parts = explode(' ', $contents);
		// look for the -dir word
		$key = array_search('-dir',$parts);
		// the next word is the root dir
		$ImagesRoot = $parts[$key + 1];
		// trim off quotes
		$ImagesRoot = trim($ImagesRoot, "\"");
	}
	else
	{
		$ImagesRoot = 'C:';
	}

	return $ImagesRoot;
}


function GetWebSlidesFolder()
{
	if (isset($_SESSION['WebSlides']))
		return $_SESSION['WebSlides'];

	$WebSlides = NULL;

	// look in the Apache config file for the WebSlides alias
	$ApacheConfig = getenv('PROGRAMFILES') . '\\Apache Group\\Apache2\\conf\\httpd.conf';
	if (file_exists($ApacheConfig))
	{
		$fh = fopen($ApacheConfig, 'r');
		while (!feof($fh))
		{
			$Line = fgets($fh, 4096); // Read a line.
			if (strncmp($Line, 'Alias', 5) == 0)
			{
				$Parts = explode(' ', $Line);
				if (count($Parts) >= 3)
				{
					if (strpos($Parts[1], 'WebSlides') !== false)
					{
						$WebSlides = trim($Parts[2], "\" \t\n\r"); // trim quotes,lineFeeds,spaces
						$WebSlides = str_replace ('/', '\\', $WebSlides); // Convert to window's delimiters
						break;
					}
				}
			}
		}
		fclose($fh);
	}

	if ($WebSlides == NULL)
		$WebSlides = 'C:\\WebSlides';

	$_SESSION['WebSlides'] = $WebSlides;
	return $WebSlides;
}


function GetSecondSlideServerEnabled()
{
	// read the value from $_SESSION
	if (isset($_SESSION['Config']['SecondSlideServerEnabled']))
	{
		return $_SESSION['Config']['SecondSlideServerEnabled'];
	}
	else
	{
		// Probabley called from Login
		// read the value from Spectrum.ini (default to false)
		$IniArray = my_parse_ini_file();
		if (isset($IniArray['SecondSlideServerEnabled']))
		{
			$_SESSION['Config']['SecondSlideServerEnabled'] = true; 
			return $IniArray['SecondSlideServerEnabled'] == 1;
		}
		else
		{
			$_SESSION['Config']['SecondSlideServerEnabled'] = false; 
			return false;
		}
	}
}

function GetSecondSlideServerURL()
{
	// read the value from Spectrum.ini
	$IniArray = my_parse_ini_file();
 
	if (isset($IniArray['SecondSlideServerURL']))
		return $IniArray['SecondSlideServerURL'];
	else
		return "https://eslideshare.com";
}

function GetSecondSlideUploadURL()
{
	// read the value from Spectrum.ini
	$IniArray = my_parse_ini_file();
 
	if (isset($IniArray['SecondSlideUploadURL']))
		return $IniArray['SecondSlideUploadURL'];
	else
		return "http://uploads.eslideshare.com";
}

function GetInboundUploadURL()
{    
	// read the value from Spectrum.ini
	$IniArray = my_parse_ini_file();

	if (isset($IniArray['InboundUploadURL']))
		return $IniArray['InboundUploadURL'];
	else
		trigger_error("InboundUplaodURL not specified in Spectrum.ini");
}


function SetSocketTimeout($Seconds, $Force=false)
{
	// default_socket_timeout is set in InitializePage() (_SESSION['Config']['ini']['default_socket_timeout'])
	$SocketTimeout = ini_get ('default_socket_timeout');
	if (($Seconds > $SocketTimeout) || $Force)
		ini_set ('default_socket_timeout', $Seconds);

	// The ini setting 'default_socket_timeout will throw an exception (see below),
	//  whearas an execution timeout implements a run-time error.
	// Ensure we get the socket timeout exception for client handling
	SetExecutionTimeout($Seconds + 10);
}

function SetExecutionTimeout($Seconds, $Force=false)
{
	$executionTimeLimit = ini_get('max_execution_time');
	if (($executionTimeLimit < $Seconds) || $Force)
	{
		set_time_limit ($Seconds); // Set max_execution_time (this also does an ini_set('max_execution_time'))
	}
}

//generates a random password which contains all letters (both uppercase and lowercase) and all numbers
function GeneratePassword($length) 
{
	$password='';
	for ($i=0;$i<=$length;$i++) 
	{
		$chr='';
		switch (mt_rand(1,3)) 
		{
			case 1:
				$chr=chr(mt_rand(48,57));
				break;
			case 2:
				$chr=chr(mt_rand(65,90));
				break;
			case 3:
				$chr=chr(mt_rand(97,122));   
		}
		$password.=$chr;
	}    
	return $password;
}


function GetDataHierarchyName()
{
	if (isset($_SESSION['Config']['DataHierarchyName']))
		return $_SESSION['Config']['DataHierarchyName'];
	return 'None';  // Vanilla Spectrum (only Spectrum Plus has hierarchy types)
}

function GetDataHierarchyNames()
{
	$HierarchyTypes = GetDataHierarchyTypes('Name');
	return array_keys($HierarchyTypes);
}

// Return valid hierarchy names
function GetDataHierarchyTypes($arrayKey)
{
	$Hierarchies = ADB_GetDataHierarchyTypes('Name');

	unset($Hierarchies['None']);

	if (IsConfigured('Genie') == false)
	{
		unset($Hierarchies['Genie']);
	}

	if (IsSpectrumForEducators())
	{
		foreach ($Hierarchies as $Name => $Hierarchy)
		{
			if ($Name != 'Educational')
			{
				unset ($Hierarchies[$Name]);
			}
		}
	}

	if ($arrayKey == 'Name')
		return $Hierarchies;

	// Index by Id
	$HierarchiesById = array();
	foreach($Hierarchies as $Hierarchy)
	{
		$HierarchiesById[$Hierarchy->Id] = $Hierarchy;
	}
	return $HierarchiesById;
}

// return the top table of the current data hierarchy
function GetTopTable()
{
	if (isset($_SESSION['HierarchyLevels']))
	{
		$TableSchema = array_first_element($_SESSION['HierarchyLevels']);
		return $TableSchema;
	}
	return NULL;
}

// return the name of the top table of the current data hierarchy
function GetTopTableName()
{
	if (isset($_SESSION['HierarchyLevels']))
	{
		$TableSchema = array_first_element($_SESSION['HierarchyLevels']);
		return $TableSchema->TableName;
	}
	return 'Unknown';
}

function GetTMAEnabled()
{
	return IsConfigured('TMA');
}

function GetReportsEnabled ()
{
	if (IsConfigured('Reports'))
	{
		$TopTableName = GetTopTableName();
		if (HasRolePermission ($TopTableName, 'ViewReport') || HasRolePermission ($TopTableName, 'Report'))
			return true;
	}
	return false;
}

// deprecated - use GetThumbnailHeight()
function GetThumbnailHeights()
{
	$return = array ();
	$return[] = GetImageThumbnailHeight('List');
	$return[] = GetLabelThumbnailHeight('List');
	$return[] = GetMacroThumbnailHeight('List');
	$return[] = GetReportThumbnailHeight('List');
    $return[] = GetAnalysisThumbnailHeight('List');
	return $return;
}

// Return size for the thumbnail display
function GetThumbnailHeight($TableName, $FieldName, $Page)
{
	if ($FieldName == 'ImageThumbnail')
	{
		if ($TableName == 'Specimen')
		{
			return GetSpecimenImageThumbnailHeight($Page);
		}
		else
		{
			return GetImageThumbnailHeight($Page);
		}
	}
	if ($FieldName == 'LabelThumbnail')
		return GetLabelThumbnailHeight($Page);
	if ($FieldName == 'MacroThumbnail')
		return GetMacroThumbnailHeight($Page);
	if ($FieldName == 'ReportThumbnail')
		return GetReportThumbnailHeight($Page);
    if ($FieldName == 'AnalysisThumbnail')
        return GetAnalysisThumbnailHeight($Page);
	return 0;
}

function GetAnalysisThumbnailHeight($Page)
{
    if (HasRolePermission('Image', 'ViewLabel'))
    {
        if ($Page == 'List')
        {
            if ($_SESSION['Config']['DisplayAnalysisThumbnail'] == 1)
                return $_SESSION['Config']['AnalysisThumbnailHeight'];
        }
        else // if ($Page == 'Record')
        {
            if ($_SESSION['Config']['DisplayRecordAnalysisThumbnail'] == 1)
                return 180;
        }
    }
    return 0;
}

function GetLabelThumbnailHeight($Page)
{
	if (HasRolePermission('Image', 'ViewLabel'))
	{
		if ($Page == 'List')
		{
			if ($_SESSION['Config']['DisplayLabelThumbnail'] == 1)
				return $_SESSION['Config']['LabelThumbnailHeight'];
		}
		else // if ($Page == 'Record')
		{
			if ($_SESSION['Config']['DisplayRecordLabelThumbnail'] == 1)
				return 180;
		}
	}
	return 0;
}

function GetMacroThumbnailHeight($Page)
{
	// Macros often have embedded labels, thus limit their viewing
	if (HasRolePermission('Image', 'ViewLabel'))
	{
		if ($Page == 'List')
		{
			if ($_SESSION['Config']['DisplayMacroThumbnail'] == 1)
				return $_SESSION['Config']['MacroThumbnailHeight'];
		}
		else // if ($Page == 'Record')
		{
			if ($_SESSION['Config']['DisplayRecordMacroThumbnail'] == 1)
				return 180;
		}
	}

	return 0;
}

function GetImageThumbnailHeight($Page)
{
	if ($Page == 'List')
	{
		if ($_SESSION['Config']['DisplayImageThumbnail'] == 1)
			return $_SESSION['Config']['ImageThumbnailHeight'];
	}
	else // if ($Page == 'Record')
	{
		if ($_SESSION['Config']['DisplayRecordImageThumbnail'] == 1)
				return 180;
	}

	return 0;
}

function GetReportThumbnailHeight($Page)
{
	if (GetReportsEnabled())
	{
		if ($Page == 'List')
		{
			if ($_SESSION['Config']['DisplayReportThumbnail'] == 1)
				return $_SESSION['Config']['ReportThumbnailHeight'];
		}
		else // if ($Page == 'Record')
		{
			if ($_SESSION['Config']['DisplayRecordReportThumbnail'] == 1)
				return 180;
		}
	}

	return 0;
}

function GetSpecimenImageThumbnailHeight($Page)
{
	if ($Page == 'List')
	{
		if ($_SESSION['Config']['DisplaySpecimenImageThumbnail'] == 1)
			return $_SESSION['Config']['SpecimenImageThumbnailHeight'];
	}
	else // if ($Page == 'Record')
	{
		if ($_SESSION['Config']['DisplaySpecimenRecordImageThumbnail'] == 1)
			return 180;
	}

	return 0;
}


function IsSpectrumForEducators()
{
	return false;	// We are no longer supporting Spectrum for Educators - give them full capability
	//return (IsConfigued('SpectrumForEducators'));
}


//------------------------------------------------------------------
// CheckUser - makes sure user has authenticated
//	If not, redirect to the login page unless $ReturnOnFail is set.
//	$ReturnOnFail should be used for AXAJ pages which would cause
//	significant problems with their callers if they returned the login page.
// NOTE: Call this method if the display page does not do any database access,
// 			otherwise InitPage() is more efficient
//------------------------------------------------------------------
function CheckUser($ReturnOnFail = false)
{
	if (InitializePage($ReturnOnFail) == false)
		return false;

	if (isset($_SESSION['SafeMode']))
	{
		// Cannot access the DataServer in safe mode
		return true;
	}

	// see if 'AuthToken' is invalid
	if( !ADB_IsValidToken() )
	{
		if ($ReturnOnFail) return false;
		
		// remember which page we were going to... then go to login page.
		if (IsAjaxRequest() == false)
			$_SESSION['OverrideStartPage'] = $_SERVER['REQUEST_URI'];
		$error = urlencode("Session is invalid or has timed out");
		header("Location: /Login.php?error=$error"); 
		exit;
	}

	return true;
}

/**
* Call this at the beginning of every display page.  If the page does no database access,
* call CheckUser() instead to ensure the user resets the timeout
* 
* @param bool $ReturnOnFail			- If TRUE, do not throw an error if login check fail
* @param bool $OverrideStartPage	- If TRUE, Allow the user to return to this page after logging in
* 
* @return bool						- TRUE if login with DataServer succeeded, FALSE otherwise
*/
function InitializePage ($ReturnOnFail = false, $OverrideStartPage = true)
{
	if (isset($_COOKIE['memory_limit']))
	{
		// PHP resets the memory limit to its php.ini setting upon every page load
		// If _SESSION has exceeded this limit, then we  will get a memory error during session_start(),
		//  therefore set the client designated memory size here.
		ini_set('memory_limit', $_COOKIE['memory_limit']);
	}

	// Setup $_SESSION for this page
	if (session_id () == '') session_start();

	// Clear the php error message
	$php_errormsg = '';

	unset($_SESSION['HealthCareMode']);

	if (isset($_SESSION['Config']['TraceLevel']) && $_SESSION['Config']['TraceLevel'] > 0)
	{
		$TraceLevel = $_SESSION['Config']['TraceLevel'];
		$Page = $_SERVER['REQUEST_URI'];
		if ($TraceLevel == 1)
		{
			// Don't report constant pings
			if (strncmp($Page, '/UpdateFields.php', 17))
			{
				SpectrumLog('Page: ' . $Page);
			}
		}
		else
		{
			SpectrumLog('Page: ' . $Page);
		}
	}

	if (!isset($_SESSION['AuthToken']))
	{
		if (isset($_REQUEST['AJAX']))
		{
			$Response = array('Alert' => 'User is not logged in');
			AjaxReply($Response);
			exit;
		}

		// see if the URL is public (i.e. autoguest logon)
		if (isset($_REQUEST['autoguest']))
		{
			// Set the page to go to after guest login
			$_SESSION['OverrideStartPage'] = $_SERVER['REQUEST_URI'];
			// & authenticate as guest
			header("Location: /Authenticate.php?user=guest&password=none");
			exit;
		}
		elseif ($OverrideStartPage)
		{
			// user tried to go directly to a page without logging in
			// remember the page, then log in.
			//$OverrideStartPage = "http".(isset($_SERVER['SSL_SESSION_ID'])?"s":"") . "://" . $_SERVER ['HTTP_HOST'] . $_SERVER ['SCRIPT_NAME'];
			//AddPage();
			//$OverrideStartPage = $_SERVER['REQUEST_URI'];

			$_SESSION['OverrideStartPage'] = $_SERVER['REQUEST_URI'];
		}

		// We can get here when IE (not other browsers) tries to load an image where src=''
		// This apparently will load /Login.php ("//<host"), which clears _SESSION, then all future requests end here
		header("Location: /Login.php"); //?OverrideStartPage" . urlencode($OverrideStartPage));
		exit;
	}

	// Our default error handlers
	set_error_handler('SpectrumErrorHandler');
    set_exception_handler('SpectrumErrorHandler');    
	register_shutdown_function('SpectrumShutdown');

	// Special ini processing for php ini overrides
	IniSettings();

	// Cache the browser User agent string once per session
	CacheBrowserUserAgent();
	
	//If the refering page was from the PALModules then clear out any $_SESSION tables
	//This is necessary to clear the advanced search results from SHC 
	//    cTableSchema->GetListObj()->GetDatabaseReader()  
	//    has the cached DataServerMethod Parameters 
	//    
	//Also clear the red/green box messages to prevent actions like saving records
	//  to display the results when switching back to SP+
	if (isset($_REQUEST['HealthCareReferer']))
	{
		unset($_SESSION['SuccessString']);
		unset($_SESSION['ErrorString']);
		unset($_SESSION['LongErrorStrings']);
		unset($_SESSION['ErrorType']);
		unset($_SESSION['DebugString']);
		
		ClearListObjs();
		$tables = array('Case', 'Specimen', 'Slide');
		foreach ($tables as $name)
		{
			if (isset($_SESSION['Tables'][$name] ))
			{
				unset($_SESSION['Tables'][$name]);		
			}
			if (isset($_SESSION['Lists']['List' . $name] ))
			{
				unset($_SESSION['Lists']['List' . $name]);		
			}		
		}
	}

	if (!isset($_SESSION['Initialized']))
	{
		// Multiple FF browsers share the _SESSION parameters.
		// If one browser has started but not finished logging in, then the _SESSION variables will not be set,
		// 	and the other browser will encounter undefined indexes.

		if ($ReturnOnFail)
		{
			SetReturnPage(-1);
			return false;
		}

		if (isset($_SESSION['ErrorString']))
		{
			$ErrorString = 'ErrorString=' . $_SESSION['ErrorString']; 
			if ($_SESSION['LongErrorStrings'] != '')
			{
				// NOTE: if LongErrorStrings has <br>s they get URL encoded, and therefore do not do linebreaks on the login page.
				foreach ($_SESSION['LongErrorStrings'] as $str)
					$ErrorString .= "&LongErrorStrings[]=$str"; 
			}
			header("Location: /Login.php?$ErrorString");
		}
		else
		{
			header('Location: /Login.php?ErrorString=Session could not initialize'); 
		}
		exit;
	}

	CheckRequest();

	// Setup parameters for this page
	unset($_SESSION['ErrorSet']);
	$_SESSION['CurrentParms'] = array();
	SetParms($_REQUEST, $_SERVER['REQUEST_URI']);


	// Default to return to the last page
	SetReturnPage(-1);
	//SetReturnPage($_SESSION['ErrorPageDefault']);	// This will mimic the previous behavior

	return true;
}


// This function initializes a page to ensure commonality.
// It must be called within the <HEAD> section, and before any .css and .js includes.
// It was originally required to allow files to reside in subdirectories, eg. Roles/Roles.php.
function InitializeHtml()
{
	/***********************************************************************************
	// Set base for html
	// SSL does not like this.
	//	It causes warnings for displaying both secure and nonsecure items,
	//	 and does not allow for arrays to be passed between pages
	//	 eg. <INPUT type='checkbox' name='table[]' value='xxx' />
	// Therefore we do not set the base
	// An outfall of this, is that we cannot have php files in subfolders,
	//	they all must be in one directory.
	$host = GetHtmlBase();
	echo "<base href='http://$host'>\n";
	*************************************************************************************/

	// Load needed javascript code (need html base set)
	$SpectrumVersion = GetSpectrumVersion();
	//echo "<script type='text/javascript' src='/js/Spectrum.js?$SpectrumVersion'> </script>\n";

	/***********************************************************************************
	// Set base for javascript
	// IE cannot take fully-qualified URL, eg. http://host/Logoff.php
	//	 	and so set BaseLocation to a relative URL.
	// (See above comments on SSL)
	// SCRIPT_NAME should be used since it's alwyas there, regardless of HTTPS usage
	$fullLocation = $_SERVER['SCRIPT_NAME'];
	$numSubDirs = substr_count($fullLocation, "/");
	$relativePath = "";
	for ($i = 1; $i < $numSubDirs; $i++)
		$relativePath .= "../";
	echo "<script type='text/javascript'>";
	echo "BaseLocation = '$relativePath';";
	echo "</script>\n";
	*************************************************************************************/
}

function FreeMemory()
{
	ClearListObjs();
}

function CreateMastHead()
{
	if (GetParm('Masthead', 1) == 0)
		return;
	include ('Masthead.php');	
}

function CreateFooter()
{
	// close center (content) div
	echo "</div>";
	if (GetParm('Footer', 1) == 0)
		return;
	include ('/Footer.php');	
}

// XXX Move this to SetSessionVars.php?
function GoToStartPage ()
{
	$host = $_SERVER['SERVER_NAME'];

	$TableOkay = true;
	$HierarchyOkay = true;
	//default the start page to login
	$StartPage = '/Login.php';

	$TableString = strstr ($_SESSION['StartPage'], 'TableName=');
	if ($TableString)
	{
		$TableName = substr ($TableString, strlen ('TableName='));

		$Offset = strpos ($TableName, '&');
		if ($Offset)
		{
			$TableName = substr ($TableName, 0, $Offset);
		}

		$TableOkay = (GetTableObj($TableName) != NULL);
		$HierarchyOkay = isset($_SESSION['HierarchyLevels'][$TableName]);
	}
	if ($TableOkay && $HierarchyOkay)
	{
		$StartPage = urldecode($_SESSION['User']['StartPage']);
	}	
	header ('Location: ' . $StartPage);
}


// Deprecated (it used to do more)
function SetErrorHandler()
{
	set_error_handler('SpectrumErrorHandler');
}


// Set the default page that should be set for error routing.
// Can be overridden by SetReturnPage().
function SetDefaultErrorPage($Page)
{
	$_SESSION['ErrorPageDefault'] = $Page;
}

// Specify the page to navigate in case of success/error.
//		Page must be numeric
// 		 0 means the last browser loaded page
//		-1 means the previously browser loaded page
function SetReturnPage($Page)
{
	$_SESSION['ReturnPage'] = $Page;
}


function SpectrumShutdown()
{
	// A fatal error will be unhandled and type == E_ERROR (else it is just an unhandled non-fatal error)
	$error = error_get_last();
	if (($error !== NULL) && ($error['type'] == E_ERROR))
	{
		// Fatal error
		ob_clean();

		$FileName = basename($error['file']);	// It's a security violation to display whole path
		$Info = "Fatal Error: " . $FileName . " line:" . $error['line'] . " : " . $error['message'] . PHP_EOL;

		SpectrumLog($Info);

		if (IsHTML())
		{
			CreateDTD();
			echo "<body>\n";
			echo $Info;
			echo "</body> </html>\n";
		}
		else // AJAX
		{
			$Response = array('Alert' => $Info);
			AjaxReply($Response);
		}
		exit();
	}
}

function IsHTML()
{
	return (IsParmSet('AJAX') == false);
}

function IsAjax()
{
	//return IsParmSet('AJAX');
	// Must be able to determine the request type even before CurrentParms is set for this page
	if (isset($_REQUEST['AJAX']))
		return true;
	return false;
}


$DO_NAVIGATE = false;
function SpectrumErrorHandler($errno, $errstr, $errfile, $errline) 
{
	global $DO_NAVIGATE;
	
	//append using the log4j frameworks
	SpectrumPlusErrorHandlerAppender($errno, $errstr, $errfile, $errline);

	// if error_reporting() == 0 it was called 
	// with @ in front of it, so ignore error.
	if (error_reporting() == 0)
	{
		return;
	}

	$RecursiveError = false;
	if ((IsAjax() == false) && isset($_SESSION['ErrorString']) && ($_SESSION['ErrorString'] != ''))
	{
		// Error handler called twice before page was painted - recursive error
		// Ensure we display the first error
		$RecursiveError = true;

		SetSafeMode();
		if ($_SESSION['SafeMode'] == 2)
		{
			// Our second error in safe mode; display a safer page
			if (isset($_SESSION['ErrorPageDefault']))
			{
				$_SESSION['ReturnPage'] = $_SESSION['ErrorPageDefault'];
			}
		}
		else if ($_SESSION['SafeMode'] > 2)
		{
			// This implies the error page is not safe enough, that must get fixed!
			header('Location: /Logoff.php'); 
			// Prevents us from going into an endless redirect loop
			// Makes masthead.php safer
			exit -1;
		}
	}

	// first see if it's a token timeout error.
	// if it is, then redirect to the login page.
	if (stristr($errstr, '-7002') != false)
	{
		ob_end_clean();

		if (IsAjax())
		{
			AjaxAlert('Session is invalid or has timed out');
		}

		SetError('Session is invalid or has timed out');
		// remember which page we were requesting, then go to login page, 
		// never save an AJAX request as the OverrideStartPage
		$_SESSION['OverrideStartPage'] = $_SERVER['REQUEST_URI'];
		$host = GetHtmlBase();
		//supply whole path in case base is a subdirectory.
		header("Location: http://$host/Login.php"); 
		exit -1;
	}

	switch($errno) 
	{
		case E_ENTRY:
			$type = 'Warning';
			$display = true;
			$DoTrace = false;
			break;
		case E_USER_NOTICE:
            $type = 'Notice';
            $display = true;
            $DoTrace = false; // user should not see DataServer detail on hover over notice
            break;
		case E_NOTICE:	// php syntax error
			$type = 'Notice';
			$display = true;
			$DoTrace = true;
			break;
		case E_COMPILE_WARNING:
		case E_CORE_WARNING:
		case E_USER_WARNING:
		case E_WARNING:	// Some php syntax & SoapClient
			$type = 'Warning';
			$display = true;
			$DoTrace = true;
			break;
		case E_USER_ERROR:
		case E_COMPILE_ERROR:
		case E_CORE_ERROR:
		case E_ERROR:
			$type = 'Fatal Error';
			$display = true;
			$DoTrace = true;
			break;
		case E_PARSE:
			$type = 'Parse Error';
			$display = true;  
			$DoTrace = true;
			break;
		default:
			$type = 'Unknown Error';
			$display = true;  
			$DoTrace = true;
			break;
	}

	$DoTrace = ($DoTrace == true) && isset ($_SESSION ['Config']['DoTrace']) && ($_SESSION ['Config']['DoTrace'] == true);

	if (preg_match('/DataServer Error: /', $errstr))
		$ShortErrorMsg = 'DataServer Error';  // verbose DataServer Error - make it short
	else
		$ShortErrorMsg = $errstr;	 // no errortype to display on short spectrum_error messages
	$LongErrorMsg = "$type: $errstr";
	if ($DoTrace && ($errfile != null))
	{
		$errfile = basename($errfile);	// security violation to display whole path
		$LongErrorMsg = $LongErrorMsg . " occurred in $errfile on $errline";
	}

	$CallStack = array ();
	if ($DoTrace)
	{
		// Build the stack trace
		$Trace = debug_backtrace ();
		foreach ($Trace as $Step)
		{
			// Skip the steps that are already in the error-catching code path
			if (isset ($Step ['function']) && in_array ($Step ['function'], array ('trigger_error', 'spectrum_error', __FUNCTION__)))
				continue;

			if (isset ($Step ['args']))
			{
				foreach ($Step ['args'] as &$Arg)
				{
					if (is_object($Arg))
					{
						// Objects can become recursive and stack overflow var_export()
						$Arg = get_class($Arg);
					}
					elseif (is_array($Arg))
					{
						$Arg = 'array';
					}
					else
					{
						$Arg = var_export ($Arg, true);
					}
				}
			}

			$CallStackItem = basename($Step ['file'])  . ' [' . $Step ['line'] . '] ' .
				(isset ($Step ['class']) ? $Step ['class'] : '') .
				(isset ($Step ['type']) ? $Step ['type'] : '') .
				(isset ($Step ['function']) ? $Step ['function'] : '') .
				(isset ($Step ['args']) ? ' (' . implode (', ', $Step ['args']) . ')' : '');
				
			// some function calls may have VERY large string parameters which can 
			// really delay processing and displaying error messages, so limit each 
			// call stack string to 2000 characters  
			if (strlen($CallStackItem) > 2000)
				$CallStackItem = substr($CallStackItem, 0, 2000) . "..."; 
				
			$CallStack[] = $CallStackItem; 
		}
	}

	//$error_msg = date("M j G:i:s T Y") . " $type: \"$errstr\" occurred in $errfile on $errline";
	//$email_addr = "shashagen@aperio.com";
	//$remote_dbg = "localhost";
	@ob_end_clean();

	//$stdlog = true;
	//$email = false;
	//$remote = false;
	//if($email) error_log($LongErrorMsgWithDate, 1, $email_addr);
	//if($remote) error_log($LongErrorMsgWithDate, 2, $remote_dbg);
	//if( $stdlog) 
	SpectrumLog($LongErrorMsg);
	if ($DoTrace)
		SpectrumLog($CallStack, false);

	if ($display) 
	{
		if ($RecursiveError == false)
		{
			SetError($ShortErrorMsg);
			$_SESSION['LongErrorStrings'] = array($LongErrorMsg);
			if ($DoTrace)
				$_SESSION['LongErrorStrings'] = array_merge($_SESSION['LongErrorStrings'], $CallStack);
		}

		if (IsAjax())
		{
			$Response = array('Alert' => $LongErrorMsg);
			AjaxReply($Response);
			exit();
		}
		else if (is_numeric($_SESSION['ReturnPage']))
		{
			//if ($DO_NAVIGATE)
			{
				Navigate($_SESSION['ReturnPage']);
				exit();
			}
			//$URL = GetURL($_SESSION['ReturnPage']);
			//$location = GetBasePage($URL);
		}
		else
		{
			$location = $_SESSION['ReturnPage'];
		}

		$host = GetHtmlBase();
		header("Location: //$host/$location", false);
		exit();
	}
}

// return true if current request is an ajax request
function IsAjaxRequest()
{
	$requestAJAX = isset($_REQUEST['AJAX']) ? $_REQUEST['AJAX'] : '';
	return stristr($requestAJAX, 'true') !== false ||
		   $requestAJAX == '1' ||
		   stristr($_SERVER['REQUEST_URI'], 'ajax=true') !== false ||
		   stristr($_SERVER['REQUEST_URI'], 'ajax=1') !== false ||		   
		   (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && stristr($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') !== false);
}

// Special error handler to ensure control is returned to caller
function NullErrorHandler($errno, $errstr, $errfile, $errline) 
{
	return true;
}


// replacement for trigger_error 
// for variable error display/logging, and display "friendlier" errors
function spectrum_error($message, $error_type = null, $errfile = null, $errline = null)
{
	SpectrumErrorHandler($error_type, $message, $errfile, $errline); 
	exit();
}

function SpectrumLog($Item, $IsNewEntry=true)
{
	$LogFolder = $_SERVER['DOCUMENT_ROOT'] . '\\logs';
	if (!file_exists($LogFolder))
		mkdir($LogFolder);

	if ($IsNewEntry && file_exists("$LogFolder\\Spectrum.log"))
	{
		$FileSize = filesize("$LogFolder\\Spectrum.log");
		if ($FileSize > 1000000)
		{
			$i = 9;
			if (file_exists("$LogFolder\\Spectrum$i.log"))
				unlink("$LogFolder\\Spectrum9.log");
			while (true)
			{
				$j = $i - 1;
				if (file_exists("$LogFolder\\Spectrum$j.log"))
				{
					rename("$LogFolder\\Spectrum$j.log", "$LogFolder\\Spectrum$i.log");
				}
				$i = $j;
				if ($i <= 1)
				{
					rename("$LogFolder\\Spectrum.log", "$LogFolder\\Spectrum$i.log");
					break;
				}
			}
		}
	}

	$fp = fopen("$LogFolder\\Spectrum.log", 'a+');

	if ($IsNewEntry)
	{
		$DateTime = GetTime('F jS Y H:i:s');
		fwrite($fp,  $DateTime);
	}

	if (is_array($Item))
	{
		foreach($Item as $Key => $Str)
			fwrite($fp,  "\t$Key:\t$Str\r\n");
	}
	else
	{
		fwrite($fp,  "\t$Item\r\n");
	}

	fclose($fp);
}


// Temporarily (this page only) extend the PHP memory
function SetMemory($Size)
{
	$New = intval(str_replace('M', '000000', $Size));
	$Current = ini_get('memory_limit');
	$Current = intval(str_replace('M', '000000', $Current));
	if ($New > $Current)
	{
		ini_set('memory_limit', $Size);
	}
}


function SetSafeMode($Level=0)
{
	if ($Level > 0)
		$_SESSION['SafeMode'] = $Level;
	if (isset($_SESSION['SafeMode']))
	{
		// Got an error in SafeMode, get safer
		$_SESSION['SafeMode'] += 1;
	}
	else
	{
		$_SESSION['SafeMode'] = 1;
	}
}


/**
* Generate a subsection header (H3) and block (DIV)
* 
* @param DOMDocument $DOM		DOM Document the section belongs to
* @param string $Name			String used to programatically refer to the subsection
* @param string $Heading		String to be used in the header text
* @param bool $DefaultDisplay	In lieu of a cookie should this section be expanded
* 
* @return array(DOMElement)		H3 and DIV elements created
*/
function MakeSubsection (DOMDocument $DOM, $Name, $Heading, $DefaultDisplay)
{
	//$DOM = $RootNode->ownerDocument;
	if (isset($_COOKIE[$Name]))
		$Display = ($_COOKIE[$Name] == '1');
	else
		$Display = $DefaultDisplay;

	// Set up the expandable header
	$HeaderNode = $DOM->createElement ('H3');
	$DetailsImg = $HeaderNode->appendChild ($DOM->createElement ('IMG'));
	$DetailsImg->setAttribute ('src', $Display ? '/images/Minus.gif' : '/images/Plus.gif');
	$DetailsImg->setAttribute ('style', 'cursor: pointer');
	$DetailsImg->setAttribute ('id', 'img' . $Name);
	$DetailsImg->setAttribute ('onclick', "ToggleSection('img$Name', 'div$Name', '$Name');");
	$HeaderNode->appendChild ($DOM->createTextNode ($Heading));

	// And the subsection to hold everything
	$DivNode = $DOM->createElement ('DIV');
	$DivNode->setAttribute ('class', 'SubSection');
	$DivNode->setAttribute ('id', 'div' . $Name);
	$DivNode->setAttribute ('style', $Display ? '' : 'display:none;');
	// Meaning of the following:
	//$DivNode->setAttribute ('style', 'padding-left:10%; width:100%');
	//  IE: width is 100% of the div AFTER the 10% shift left (thus right = 100% of the div)
	//  FF: width is 100% of the window width (thus right always exceeds the window right)

	return array ($HeaderNode, $DivNode);
}


function CreateDeadLink($DOM, $str)
{
	$Link = $DOM->createElement ('span');
	$Link->setAttribute ('class', 'DeadLink');
	$Link->appendChild ($DOM->createTextNode ($str));
	return $Link;
}


// Add a task (link or javascript call) to a DOMElement
function AddTask(DOMElement $Cell, $Command, $Link, $OnClick=NULL)
{
	$DOM = $Cell->ownerDocument;

	if ($Cell->hasChildNodes())
		$Cell->appendChild ($DOM->createTextNode (' | '));

	$Anchor = $DOM->createElement ('A');

	if ($Link)
		$Anchor->setAttribute ('href', $Link);
	else
		$Anchor->setAttribute ('href', '#');

	if ($OnClick)
		$Anchor->setAttribute ('onclick', $OnClick);

	$Anchor->appendChild ($DOM->createTextNode ($Command));

	$Cell->appendChild ($Anchor);
}


function AppendThumbnailHTMLNode($ParentNode, $ImageRecord, $TableName, $FieldName, $Page, $DisplayImmediately=true, $ReadOnly=false)
{
	global $PALMode;
	// generate thumbnail HTML and appended to provided Node.  
	// XML DOM Equivalent for GetThumbnailHTMLString()  

	// if inbound file transfer (secondslide)
	if (isset($ImageRecord['TransferProgress']) && 
		$ImageRecord['TransferProgress'] != '' && 
		$ImageRecord['TransferProgress'] != '100' &&  
		GetSecondSlideServerEnabled() )  
	{
		$ParentNode->appendChild ($DOM->createTextNode('Transfer ' . $ImageRecord['TransferProgress'] . '%'));  
		return;
	}

	$DOM = $ParentNode->ownerDocument;
	$OSpanTag = $DOM->createElement ('SPAN');
	$OSpanTag->setAttribute ('style', 'white-space: nowrap;');
	$BSpanTag = $DOM->createElement ('SPAN');
	$BSpanTag->setAttribute ('class', 'hoverbase');
	$ImageTag = $DOM->createElement ('IMG');
	$BSpanTag->appendChild ($ImageTag);
	$OSpanTag->appendChild ($BSpanTag);

	$ImageId = $ImageRecord['ImageId'];
	$ImageLocation = $ImageRecord['CompressedFileLocation'];
	$MarkupId = "";
	

	$ThumbnailHeight = GetThumbnailHeight($TableName, $FieldName, $Page);
	if ($OSpanTag->childNodes->length > 1)
		$ThumbnailHeight = $ThumbnailHeight - 5;
	$ThumbnailURL = GetThumbnailURL($ImageRecord, $FieldName, $ThumbnailHeight);
	if (strlen($ThumbnailURL) > 0)
	{
//		$DOM = $ParentNode->ownerDocument;
//		$ImageTag = $DOM->createElement ('IMG');

		if ($ReadOnly == false)
		{
			$ViewingMode =  $_SESSION['User']['ViewingMode'];
			$ImageId = $ImageRecord['ImageId']; 
			$ImageServerURL = GetImageServerURLForImage($ImageRecord['CompressedFileLocation']);
			$ThumbnailDiv = $ParentNode->appendChild ($DOM->createElement ('div'));
			$ThumbnailDiv->setAttribute ('class', 'thumbnailDiv');
			$ThumbnailDiv->setAttribute ('imageid', $ImageId);
			$Link = $ThumbnailDiv->appendChild ($DOM->createElement ('A'));
			$Link->setAttribute ('style', 'text-decoration: none;');
			$Link->setAttribute ('href', '');
			$Link->setAttribute ('onclick', "SingleViewWithImageScopeOrBrowser ('$ImageServerURL','$ImageId','$ViewingMode'); return false;");
			$Link->appendChild ($OSpanTag);
		}
		else
		{
			$ParentNode->appendChild ($OSpanTag);
		}

		if ($Page == 'Details')
		{
			// The delayed display caused problems when in auto-view mode.
			// Besides, the delay was only intended for large lists
			$DisplayImmediately = true;
		}

		$ImageTag->setAttribute ('alt', '');
		$ImageTag->setAttribute ('border', 0); 
		$ImageTag->setAttribute ('height', $ThumbnailHeight);
		$ImageTag->setAttribute ('onerror', 'ImageLoadError(this);');
		if ($DisplayImmediately)
		{
			$ImageTag->setAttribute ('src', $ThumbnailURL);
		}
		else
		{
			$ImageTag->setAttribute ('class', 'ImageDelay');
			$ImageTag->setAttribute ('image', $ThumbnailURL);
			$ImageTag->setAttribute ('src', '/Images/1x.gif');	// blank image
		}

		if ($PALMode && ($TableName == 'Specimen'))
		{
			if (HasRolePermission('Image', 'Delete'))
			{
				$Br = $ThumbnailDiv->appendChild ($DOM->createElement('br'));
				$TaskLink = $ThumbnailDiv->appendChild ($DOM->createElement ('a'));
				$TaskLink->setAttribute ('href', '#');
				$TaskLink->setAttribute ('title', 'Delete this Image from the Specimen');
				if ($ReadOnly)
					$TaskLink->setAttribute ('class', 'BulkLink Disabled thumbnailDelLink');
				else
					$TaskLink->setAttribute ('class', 'BulkLink thumbnailDelLink');
				$TaskLink->setAttribute ('onclick',"if (!hasClass (this, 'Disabled')) jsPAL.deleteElement($ImageId, 'Specimen', this, 'PALDeleteSpecimenThumbnail', true);return false;");
				
				$TaskLink->appendChild ($DOM->createTextNode ('Delete Image'));
			}
		}
		$iPad = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
		//disabling hovering popups for Ipad for now

		if ((isset($PALMode) === false) && ($iPad === false))
		{
			$Hover = $DOM->createElement ('SPAN');
			$Hover->setAttribute ('class', 'hoverpopup');
			$HoverTop = -$ThumbnailHeight-20;
			$Hover->setAttribute ('style', "top: {$HoverTop}px; left: {$ThumbnailHeight}px;");
			$HoverImage = $DOM->createElement ('IMG');
			if ($DisplayImmediately)
			{
				$HoverImage->setAttribute ('src', GetThumbNailURL($ImageRecord, $FieldName, 200));
			}
			else
			{
				$HoverImage->setAttribute ('class', 'ImageDelay');
				$HoverImage->setAttribute ('image', GetThumbNailURL($ImageRecord, $FieldName, 200));
				$HoverImage->setAttribute ('src', '/Images/1x.gif');
			}
			$HoverImage->setAttribute ('alt', '');
			$HoverImage->setAttribute ('style', 'margin: 0px;');
			$HoverImage->setAttribute ('border', 0);
			$Hover->appendChild ($HoverImage);
			$BSpanTag->appendChild($Hover);
		}
	}
}

// generate thumbnail HTML and return as string. String Equivalent for AppendThumbnailHTMLNode()
function GetThumbnailHTMLString($Image, $TableName, $FieldName, $Page)
{
	if (isset($Image['TransferProgress']) && 
		$Image['TransferProgress'] != '' && 
		$Image['TransferProgress'] != '100' &&
		GetSecondSlideServerEnabled() )
	{
		return 'Transfer ' . $Image['TransferProgress'] . '%';
	}

	$ThumbnailString = '';
	$ThumbnailHeight = GetThumbnailHeight($TableName, $FieldName, $Page);
	$ThumbnailURL = GetThumbnailURL($Image, $FieldName, $ThumbnailHeight);
	if (strlen($ThumbnailURL) > 0)
	{
		$ImageServerURL = GetImageServerURLForImage($Image['CompressedFileLocation']);
		$ImageId = $Image['ImageId'];
		$ViewingMode = $_SESSION['User']['ViewingMode'];

		$ThumbnailString = "\n<A href='#' onclick=\"SingleViewWithImageScopeOrBrowser('$ImageServerURL','$ImageId','$ViewingMode'); return false; \"  >";
		$ThumbnailString .= "\n<img src='$ThumbnailURL' height='$ThumbnailHeight' border='0' alt='' onerror='this.style.display=\"none\";'  />";
		$ThumbnailString .= "\n</A>";
	}

	return $ThumbnailString;
}

/**
* Gets a URL pointed to the requested thumbnail of the specified Image
* 
* @return string			URL that can be put in an IMG tag src attribute for the thumbnail desired
*/
function GetThumbnailURL ($ImageRecord, $FieldName, $Size)
{
	// Do not pass both width and height - it will display a distorted image.
	// In SpectrumPlus the thumbnail column will expand to accomadate.
	return GetThumbnailURL2($ImageRecord['ImageId'], $ImageRecord['CompressedFileLocation'], $FieldName, 0, $Size);
}

function GetThumbnailURL2 ($ImageId, $CompressedFileLocation, $FieldName, $Width, $Height)
{
	$ImageFile = new cImageFile($CompressedFileLocation);
	$ImageFile->SetId($ImageId);
	$ImageFile->AddIdToURL(true);
	$ImageFile->SetDomainMapping('Relative');
	$IsFusedImage = $ImageFile->IsFusedImage();
   
	$URL = $ImageFile->GetSSLURL();

	if ($FieldName == 'ImageThumbnail')
		$TypeIdx = 1;
	else if ($FieldName == 'LabelThumbnail')
		$TypeIdx = 2;
	else if ($FieldName == 'MacroThumbnail')
		$TypeIdx = 3;
	else if ($FieldName == 'AnalysisThumbnail')
		return GetLastAnalysis($ImageId, $Height);
	else // if ($FieldName == 'ReportThumbnail')
		return BuildReportRegionURL($URL, $ImageId);
			
	if ($IsFusedImage)
	{
		// append random number to the URL to make it unique, defeats browser caching
		// always cache bust AFI images because a user can "change" the image
		return "$URL?0+0+0+$Height+-$TypeIdx+80+S+0+" . rand();
	}
	else
	{
		return "$URL?0+0+$Width+$Height+-$TypeIdx+80+S+0";
	}
}


/**
 * @descr   Save search/display field admin info.
 * @param	int $ImageId - image id of the primary image for which we want the markup image
 * @param	int $Size -- size (height) of the thumbnail.
 * @return	string URL to image server.
*/
function GetLastAnalysis($ImageId, $Size)
{
	$TotalCount = 0;
	$TypeIdx = 1;
	$MarkUpImageId = 0;
	$annotationMarkupId = 0;

	$retMarkupImageId1 = ADB_GetFilteredRecordList(
		'Image',0,0,
		array('CompressedFileLocation','DownToAnnotationByImageId.MarkupImageId'),
		array('ImageId','DownToAnnotationByImageId.Type'),
		array('=','='),
		array($ImageId,'3'), // 3 - analysis.
		array('Image','Image'),
		'DownToAnnotationByImageId.AnnotationId',
		'Descending',
		$TotalCount);

	$annotationMarkupId = isset($retMarkupImageId1[0]['DownToAnnotationByImageId.MarkupImageId'])?$retMarkupImageId1[0]['DownToAnnotationByImageId.MarkupImageId']:0;

	if($annotationMarkupId==0){

		$retMarkupImageId2 = ADB_GetFilteredRecordList(
			'Image',0,0,
			array('CompressedFileLocation','DownToAnnotationByImageId.DownToAnnotationRegionByAnnotationId.MarkupImageId'),
			array('ImageId','DownToAnnotationByImageId.Type'),
			array('=','='),
			array($ImageId,'3'), // 3 - analysis.
			array('Image','Image'),
			'DownToAnnotationByImageId.DownToAnnotationRegionByAnnotationId.MarkupImageId',
			'Descending',
			$TotalCount);
		 $annotationMarkupId = isset($retMarkupImageId2[0]['DownToAnnotationByImageId.DownToAnnotationRegionByAnnotationId.MarkupImageId'])?$retMarkupImageId2[0]['DownToAnnotationByImageId.DownToAnnotationRegionByAnnotationId.MarkupImageId']:0;
	}

	if($annotationMarkupId==0) 
		return "";

	$retMarkUpFileLocation = ADB_GetFilteredRecordList(
		'Image',0,0,
		array('CompressedFileLocation'),
		array('ImageId','ImageTypeId'),
		array('=','='),
		array($annotationMarkupId,'2'),
		array('Image','Image'),'','',$TotalCount);

	if (!isset($retMarkUpFileLocation[0]['CompressedFileLocation']))
		return '';

	$ImageFile = new cImageFile($retMarkUpFileLocation[0]['CompressedFileLocation']);
	$ImageFile->SetId($annotationMarkupId);
	$ImageFile->AddIdToURL(true);
	$ImageFile->SetDomainMapping('Relative');

	$URL = $ImageFile->GetSSLURL();

	// append random number to the URL to make it unique, defeats browser caching
	return "$URL?0+0+0+$Size+-$TypeIdx+80+S+0+" . rand();

}

function GetImageURL ($ImageId, $CompressedFileLocation)
{
	$ImageFile = new cImageFile($CompressedFileLocation);
	$ImageFile->SetId($ImageId);
	$ImageFile->AddIdToURL(true);
	$ImageFile->SetDomainMapping('Relative');

	$URL = $ImageFile->GetSSLURL();

	return $URL;
}

function GetCrossDomainSafeImageURL ($ImageId, $CompressedFileLocation)
{
	$ImageFile = new cImageFile($CompressedFileLocation);
	$ImageFile->SetId($ImageId);
	$ImageFile->AddIdToURL(true);
	$ImageFile->SetDomainMapping('Relative');

	$URL = $ImageFile->GetCrossDomainSafeURL();

	return $URL;
}

function BuildReportRegionURL($URL, $ImageId)
{
	// get annotations for image and find the report region (type 8 is report region)
	$Ann = ADB_GetAnnotationXML($ImageId, 'ImageId', 8);
	if (empty($Ann))
		return false;
	$doc = new DOMDocument('1.0', 'utf-8');
	if ($doc->loadXML($Ann) === false)
		return false;
	
	// get the report region
	$Region = $doc->getElementsByTagName('Region')->item(0);
	if (!isset($Region))
		return false;
	$Zoom = $Region->getAttribute('Zoom');
	if ($Zoom == '')
		$Zoom = 1.0;
	$Vertex = $Region->getElementsByTagName("Vertex");
	if (!isset($Vertex))
		return false;
	$xMin = 9999999;
	$yMin = 9999999;
	$xMax = -9999999;
	$yMax = -9999999;
	// get report region coordinates 	
	for ($i = 0; $i < $Vertex->length; $i++)
	{
		$x = $Vertex->item($i)->getAttribute("X");
		$y = $Vertex->item($i)->getAttribute("Y");
		if ($x == '' || $y == '')
			return false;
		$xMin = min($xMin, $x);
		$yMin = min($yMin, $y);
		$xMax = max($xMax, $x);
		$yMax = max($yMax, $y);
	}
	// build an image URL for the area defined by the report region
	// URL format:
	// //server/@@token/@imageid?left+top+width+height+zoom
	return $URL . '?' . 
		   $xMin * $Zoom . '+' . 
		   $yMin * $Zoom . '+' . 
		   ($xMax - $xMin) * $Zoom . '+' . 
		   ($yMax - $yMin) * $Zoom . '+' . 
		   1.0 / $Zoom;
}

function GetCwsLocation($Location)
{
	// Ensure just a folder (sometimes the .ini file is referenced)
	if (strrchr($Location, '.'))
		return dirname($Location);
	// else
		return $Location;
}


function GetFileExtension(&$FileName) 
{
	$Ext = strtolower (strrchr ($FileName, '.'));
	if ($Ext)
		return substr ($Ext, 1);
	return '';
}

function GetImageType ($FileName)
{
/*	$Ext = GetFileExtension($FileName);
	if ($Ext)
		return $Ext;
	if (is_file($FileName . '\\slidescan.ini') )
		return 'cws';
	if (is_file($FileName . '\\slidedat.ini') )
		return 'mrxs';
	return '';
*/
	// check if filename contains slidescan.ini
	if (stripos($FileName, 'slidescan.ini') !== false)
		return 'cws';

	// check if filename contains slidedat.ini
	if (stripos($FileName, 'slidedat.ini') !== false)
		return 'mrxs';

	// if filename has an extension, then return it.
	$Ext = GetFileExtension($FileName);
	if ($Ext && $Ext != "ini")
		return $Ext;
	
	// lastly check if filename is actually a folder which 
	// contains a file named slidescan.ini or slidedat.ini
	if (is_file($FileName . '\\slidescan.ini') )
		return 'cws';
	if (is_file($FileName . '\\slidedat.ini') )
		return 'mrxs';
	return '';
}


function xmlencode($txt)
{

	$txt = str_replace('&', '&amp;', $txt);
	$txt = str_replace('<', '&lt;', $txt);
	$txt = str_replace('>', '&gt;', $txt);
	$txt = str_replace("'", '&apos;', $txt);
	$txt = str_replace('"', '&quot;', $txt);

	return $txt;
}

function xmldecode($txt)
{

	$txt = str_replace('&amp;', '&', $txt);
	$txt = str_replace('&lt;', '<', $txt);
	$txt = str_replace('&gt;', '>', $txt);
	$txt = str_replace('&apos;', "'", $txt);
	$txt = str_replace('&quot;', '"', $txt);

	return $txt;
}

function bulkencode($haystack, $needles=array())
{
	foreach ($needles as $find=>$replace)
		$haystack = str_replace($find, $replace, $haystack); 

	return $haystack;   
}

function bulkdecode($haystack, $needles=array())
{
	foreach ($needles as $find=>$replace)
		$haystack = str_replace($replace, $find, $haystack);

	return $haystack;
}


function CheckPasswordOkay ($UserName, $NewPassword1, $NewPassword2)
{
	if ($NewPassword1 != $NewPassword2)
		return array (false, 'New passwords do not match');

	// All other restrictions are checked by the DataServer
	return array (true, '');
}

function GetPasswordRequirementsString ()
{
	if (IsConfigured('SplCharsReq'))
	{
		$Specials = str_split ($_SESSION['Config']['SpecialPasswordChars']);
		$SpecialsString = "{$Specials[0]}, ";
		for ($c = 1; $c < count ($Specials); $c++)
			$SpecialsString = "$SpecialsString,".($c<count($Specials)-1?" {$Specials [$c]}":" or {$Specials [$c]}");
		$SpecialsString = xmlencode($SpecialsString);
	}

	$Config = $_SESSION['Config'];
	$PasswordLength = $Config ['MinPwdLen'] > 0 ? "be at least {$Config ['MinPwdLen']} character" . ($Config ['MinPwdLen'] > 1 ? "s" : "") . " long" : "";
	$PasswordSpecials = IsConfigured('SplCharsReq') ? "contain special characters ($SpecialsString)" : '';
	$PasswordsRemembered = $Config ['OldPwdCount'] > 0 ? ($Config ['OldPwdCount'] > 1 ? "be different from previous {$Config ['OldPwdCount']} passwords" : "be different from previous password") : "";

	$PasswordRequirements = "";
	if ($PasswordLength != "" || $PasswordSpecials != "" || $PasswordsRemembered != "")
	{
		$PasswordRequirements = "(password must ";
		
		if ($PasswordLength != "")
		{
			$PasswordRequirements = $PasswordRequirements . $PasswordLength;
		}

		if ($PasswordLength != "" && $PasswordSpecials != "" && $PasswordsRemembered != "")
		{
			$PasswordRequirements = $PasswordRequirements . ", ";
		}
		elseif (($PasswordLength != "" && $PasswordSpecials != "") || ($PasswordLength != "" && $PasswordsRemembered))
		{
			$PasswordRequirements = $PasswordRequirements . " and ";
		}

		if ($PasswordSpecials != "")
		{
			$PasswordRequirements = $PasswordRequirements . $PasswordSpecials;
		}

		if ($PasswordLength != "" && $PasswordSpecials != "" && $PasswordsRemembered != "")
		{
			$PasswordRequirements = $PasswordRequirements . ", and ";
		}
		elseif ($PasswordSpecials != "" && $PasswordsRemembered != "")
		{
			$PasswordRequirements = $PasswordRequirements . " and ";
		}

		if ($PasswordsRemembered != "")
		{
			$PasswordRequirements = $PasswordRequirements . $PasswordsRemembered;
		}

		$PasswordRequirements = $PasswordRequirements . ")";
	}

	return $PasswordRequirements;
}


// Return the list of all visible, or necessary (like Id) columns in the given table schema (excluding non-searchable/non-sortable memo fields)
function GetNeededColumns ($TableName)
{
	$Columns = array ();

	if (isset ($_SESSION ['HierarchyLevels'][$TableName]))
	{
		foreach ($_SESSION ['HierarchyLevels'][$TableName]->Fields as $Field)
			if ($Field->IsNeeded)
				$Columns [] = $Field->ColumnName;

		if ($TableName == 'Slide')
		{
			$Columns [] = 'ScanStatus';
			$Columns [] = 'CompressedFileLocation';
			$Columns [] = 'ImageId';
		}

		$Columns = array_unique($Columns);
	}

	return $Columns;
}

/**
 * Grab any changes to system-wide settings since the last check (this does't actually track changes yet, just grabs current state)
 *
 */
function UpdateSystemSettings ()
{
	// see if the user has the ability to create new records in at least
	// one datgroup and store that boolean value as a session variable.
	// This value is used frequently throughout spectrum to determine
	// whether to show "Add New" links, etc.
	// XXX - This should be obsoleted by roles, must check customer usage
	// MS 9-22-08 Session access exists for Roles but not DataGroups.  Saving Datagroup access here.
	$DataGroupArray = array();
	$DataGroupList = array();
	$DataGroups = ADB_ListAccessByUser();
	$bNewPermitted = false;
	foreach($DataGroups as $DataGroup)
	{
		if ($DataGroup->AccessFlags == 'Full')
		{
			$bNewPermitted = true;
			$DataGroupArray[$DataGroup->DataGroupId] = 'Full';
			$DataGroupList[$DataGroup->DataGroupId] = $DataGroup;
		}
		elseif ($DataGroup->AccessFlags == 'Read')
		{
			$DataGroupArray[$DataGroup->DataGroupId] = 'Read';
			$DataGroupList[$DataGroup->DataGroupId] = $DataGroup;
		}
	}

	$_SESSION['NewPermitted'] = $bNewPermitted;
	$_SESSION['DeletePermitted'] = $bNewPermitted;	// Same as NewPermitted for now
	$_SESSION['DataGroupAccess'] = $DataGroupArray;	// Deprecated
	ksort($DataGroupList);
	$_SESSION['User']['DataGroups'] = $DataGroupList;

	// Set default datagroup
	$_SESSION['User']['DataGroupDefaultName'] = '';
	$_SESSION['User']['DataGroupDefaultId'] = -1;
	foreach ($DataGroupList as $DataGroup)
	{
		if ($DataGroup->AccessFlags == 'Full')
		{
			$_SESSION['User']['DataGroupDefaultName'] = $DataGroup->DataGroupName;
			$_SESSION['User']['DataGroupDefaultId'] = $DataGroup->DataGroupId;
			break;
		}
	}

	// Get ImageServer URLs
	$_SESSION['ImageServers'] = array();
	$ServerSchema = GetTableObj('ImageServer');
	$Records = $ServerSchema->GetRecords();
	foreach ($Records as $Record)
	{
		if (($Record['Path'] == '*') || IsUNC($Record['Path']))
		{
			$UNC = strtolower($Record['Path']);	// Ensure case insensitivity
			$_SESSION['ImageServers'][$UNC]['Internal'] = $Record['Location'];
			if (isset($Record['ExternalLocation']))
				$_SESSION['ImageServers'][$UNC]['External'] = $Record['ExternalLocation'];
			else
				$_SESSION['ImageServers'][$UNC]['External'] = $Record['Location'];
		}
	}
}

//
// Updates	_SESSION['Components']
// 			_SESSION['LicensedComponents']
// 			_SESSION['Config']
// 			_SESSION['ConfiguredComponents'] // Licensed components for user's hierarchy
//          _SESSION['ServerAddr']
//          _SESSION['HostAddr']
//
function UpdateConfiguration()
{
	if (isset($_SESSION['Components']) == false)
	{
		$_SESSION['Components'] = ADB_GetComponents('Name');
	}
	$ComponentNames = array_keys($_SESSION['Components']);

	// Licensed Components
	$LicensedComponents = array();

	// Now disable licensed components that are not licensed
	$Licenses = ADB_GetLicenses();

	if (isset($Licenses['Components']))
	{
		$OptionalComponents = $Licenses['Components'];
	}
	else
	{
		// Probably vanilla Spectrum
		$OptionalComponents = array();
	}

	foreach ($OptionalComponents as $ComponentName => $Details)
	{
		if (isset($Details->NumMonthsLeft) && ($Details->NumMonthsLeft != 'EXPIRED'))
			$LicensedComponents[$ComponentName] = true;
		else if (isset($Details->NumDaysLeft) && ($Details->NumDaysLeft != 'EXPIRED'))
			$LicensedComponents[$ComponentName] = true;
	}

	if (isset($LicensedComponents['eSlide Manager']))
	{
		// Default everything as licensed
		$licensed = true;
		// Set deprecated flag to ensure functionality
		$LicensedComponents['SpectrumPlus'] = true;
	}
	else
	{
		// Default everything as not licensed
		$licensed = false;
		$LicensedComponents['eSlide Manager'] = false;
		$LicensedComponents['SpectrumPlus'] = false;
	}
	foreach ($ComponentNames as $ComponentName)
	{
		$LicensedComponents[$ComponentName] = $licensed;
	}
	// Add a couple of components
	$LicensedComponents['DataHierarchy'] = $licensed;
	// Rename some components for more readable code
	$LicensedComponents['Roles'] = $licensed;

	if ($LicensedComponents['eSlide Manager'] == false)
	{
		// Vanilla Spectrum licensed components
		$LicensedComponents['CalibrationResults'] = true;
		$LicensedComponents['Config'] = true;
		$LicensedComponents['DataFields'] = true;
		$LicensedComponents['Documents'] = true;
		$LicensedComponents['Image'] = true;
		$LicensedComponents['JobQueue'] = true;
		$LicensedComponents['LicenseAdministration'] = true;
		$LicensedComponents['Macro'] = true;
		$LicensedComponents['ScanScope'] = true;
		$LicensedComponents['Slide'] = true;
		$LicensedComponents['Stain'] = true;
		$LicensedComponents['Users'] = true;	// but no Add ability
	}

	if (isset($LicensedComponents['TMA']))
	{
		$LicensedComponents['Core'] = true;
		$LicensedComponents['Spot'] = true;
	}
	else
	{
		$LicensedComponents['TMA'] = false;
		$LicensedComponents['Core'] = false;
		$LicensedComponents['Spot'] = false;
	}

	if (isset($LicensedComponents['Reporting']))
	{
		// Need both the 'Report' (for DataTable creation) and 'Reports' component
		$LicensedComponents['Report'] = true;
	}
	else
	{
		$LicensedComponents['Reports'] = false;
		$LicensedComponents['Report'] = false;
		$LicensedComponents['Customers'] = false;
		//$LicensedComponents['Comments'] = false;
	}

	if (isset($LicensedComponents['Genie']))
	{
		// 'Genie' is not in the Components table
		$LicensedComponents['Genie'] = true;
	}
	else
	{
		$LicensedComponents['Genie'] = false;
		$LicensedComponents['AnnotationTemplate'] = false;
		$LicensedComponents['ClassifierDefinition'] = false;
		$LicensedComponents['GenieProject'] = false;
		$LicensedComponents['GenieTrainingSet'] = false;
	}

	if (isset($LicensedComponents['Compliance']))
	{
		$LicensedComponents['ReasonForChange'] = true;
	}
	else
	{
		$LicensedComponents['Compliance'] = false;
		$LicensedComponents['ReasonForChange'] = false;
	}

	if (isset($LicensedComponents['Sharing']) == false)
	{
		$LicensedComponents['Sharing'] = false;
	}

	if (isset($LicensedComponents['Health Care Suite']))
	{
		$LicensedComponents['VwCaseInfo'] = true;
		$LicensedComponents['VwCaseResults'] = true;
		$LicensedComponents['VwCaseAggregates'] = true;
		$LicensedComponents['VwSpecimenAggregates'] = true;
		$LicensedComponents['Conferencing'] = true;
		$LicensedComponents['IntraOperative'] = true;
	}
	else
	{
		$LicensedComponents['Health Care Suite'] = false;
		$LicensedComponents['VwCaseInfo'] = false;
		$LicensedComponents['VwCaseResults'] = false;
		$LicensedComponents['VwCaseAggregates'] = false;
		$LicensedComponents['VwSpecimenAggregates'] = false;
		$LicensedComponents['Conferencing'] = false;
		$LicensedComponents['IntraOperative'] = false;
	}

	/****
	if (IsConfigured('SpectrumForEducators'))
	{
		// Clear Clinical and Research tables
		$LicensedComponents['Case'] = false;
		$LicensedComponents['Project'] = false;
	}
	*******/    
    $LicensedComponents['SystemId'] = $Licenses['SystemId'];

	$_SESSION['LicensedComponents'] = $LicensedComponents;


	// Update _SESSION['Config']
	ADB_GetConfigValues();


	// Now create the ConfiguredComponents list.
	// This is dependent upon the user's session, including his hierarchy.
	$HierarchyName = GetDataHierarchyName();
	$_SESSION['ConfiguredComponents'] = GetComponentsForHierarchy($HierarchyName);

	if (isset($_SESSION['RoleId']))
	{
		// Update role permissions - they are dependent upon ConfiguredComponents
		SetRolesForSession($_SESSION['RoleId']);
	}

	SetSessionServerAndHostAddresses();
}

function GetComponentsForHierarchy($HierarchyName)
{
	$Components = $_SESSION['LicensedComponents'];

	// First clear all data table components (it's safer to reset them below)
	$HierarchyTables = ADB_GetDataHierarchy(NULL, true, true);
	foreach ($HierarchyTables as $Table)
	{
		$Components[$Table->Name] = false;
	}
	// Set the appropriate tables
	$HierarchyTables = ADB_GetDataHierarchy($HierarchyName, true, true);
	foreach ($HierarchyTables as $Table)
	{
		$Components[$Table->Name] = true;
	}

	// Null implies all hierarchies
	if ($HierarchyName != NULL)
	{
		if ($HierarchyName == 'Educational')
		{
			$Components['SsConfig'] = false;
		}
		if ($HierarchyName != 'Genie')
		{
			// Genie hierarchy specific
			$Components['AnnotationTemplate'] = false;
			$Components['ClassifierDefinition'] = false;
		}
	}

	if (IsLicensed('Health Care Suite') && ($HierarchyName == 'Clinical'))
	{
		$Components['Health Care Suite'] = true;
	}
	else if (IsLicensed('Life Science Suite') && ($HierarchyName == 'Project'))
	{
		$Components['Life Science Suite'] = true;
	}

	if ($Components['Reports'] == true)
	{
		$Components['Report'] = true;
		$Components['Reporting'] = true; // This is for licensing (XXX remove 'Report')
		$Components['Customers'] = true;
	}
	else
	{
		$Components['Report'] = false;
		$Components['Reporting'] = false;
		$Components['Customers'] = false;
	}

	if ((isset($_SESSION['Config']['Compliance']) == false) || ($_SESSION['Config']['Compliance'] == false))
	{
		// Compliance may be licensed but not configured (the compliance installer must be run)
		$Components['Compliance'] = false;
	}

	return $Components;
}

// return true if component is licensed for the system
function IsLicensed($Component)
{
	if (isset($_SESSION['LicensedComponents'][$Component]))
	{
		return $_SESSION['LicensedComponents'][$Component];
	}
	return false;
}

// return true if component is configured in the system for this session (data hierarchy dependent)
function IsConfigured($Component)
{
	$Value = GetConfigValue($Component);
	if (($Value == null) || ($Value == false) || ($Value === 'False'))
		return false;
	return true;
}

function GetConfigValue($Component)
{
	if (isset($_SESSION['ConfiguredComponents'][$Component]))
		return $_SESSION['ConfiguredComponents'][$Component];
	elseif (isset($_SESSION['Config'][$Component]))
		return $_SESSION['Config'][$Component];
	return null;
}


// return true if current role allows for given command
function HasRolePermission($ComponentName, $Command)
{
	// hack for secondslide.  Since roles are not granular
	// enough for our needs we need to have special logic
	// do disable some functionality here.
	if (GetSecondSlideServerEnabled() && !IsAdmin())
	{
		if ($Command == 'Assign' || $Command == 'Export' || $Command == 'Copy')
		{
			return false;
		}
	}

	if (($Command == 'Add') || ($Command == 'Assign') || ($Command == 'Export') || ($Command == 'Clone'))
	{
		// These commands got collapsed into Add (temporarily)
		$Command = 'Add';
	}

	if (isset($_SESSION['RoleCommands'][$ComponentName]))
	{
		if (in_array($Command, $_SESSION['RoleCommands'][$ComponentName]))
			return true;
	}
	return false;
}


// return true if current role allows for given command and session is configured for it.
// This may now be unneeded since the configuration is checked before setting a role. (Use HasRolePermission()).
function HasPermission($ComponentName, $Command)
{
	if (IsConfigured($ComponentName))
		return HasRolePermission($ComponentName, $Command);
	return false;
}


// The right to copy an slide, currently depends on multiple roles.
function HasCopySlidePermission()
{
	if (HasRolePermission('Image', 'Copy') && HasAddPermission('Slide'))
	{
		return true;
	}
	else
	{
		return false;
	}
}


// return true if user is the administrator
function IsAdmin()
{
	if ($_SESSION['LoginName'] == 'administrator')
		return true;
	return false;
}


function GetConfigDefaults ()
{
	$DefaultArray = array
	(
		'RecordsPerPage' => '20',
		'DocumentUploadDirectory' => 'C:\\UploadedDocs\\',
		'ImageUploadDirectory' => 'C:\\UploadedImages\\',
		'InstallationType' => 'Clinical',

		'DisplayLabelThumbnail' => '0',
		'DisplayMacroThumbnail' => '0',
		'DisplayImageThumbnail' => '1',
		'DisplayReportThumbnail' => '0',
		'DisplaySpecimenImageThumbnail' => '0',

		'DisplayRecordLabelThumbnail' => '1',
		'DisplayRecordMacroThumbnail' => '0',
		'DisplayRecordImageThumbnail' => '1',
		'DisplayRecordReportThumbnail' => '0',
		'DisplaySpecimenRecordImageThumbnail' => '1',

		'MacroThumbnailHeight' => '30',
		'LabelThumbnailHeight' => '60',
		'ImageThumbnailHeight' => '30',
		'ReportThumbnailHeight' => '30',
		'SpecimenImageThumbnailHeight' => '30',

		'ReportTemplateFolder' => realpath (getcwd () . '\\..\\ReportTemplates') . '\\',

		//FOR DEBUGGING    Apache and HTDOCS are not necessarly in the same folder structure
		//                 Copy the full path to the ReportTemplates Diretory underneath APACHE [WITH Trailing Slash]
		//                 This is required for the GenerateReport() function to be able to read the temporary APML file
		//'ReportTemplateFolder' => 'C:\\Program Files (x86)\\Aperio\\Spectrum\\ReportTemplates\\',

		'ImageServerURLs' => 'localhost',

		'Theme' => 'default',
		'NewsFeedURL' => 'http://blog.aperio.com/atom.xml',

		'MinPwdLen' => '0',
		'MaxDaysBetweenLogins' => '0',
		'MaxLoginAttempts' => '0',
		'SplCharsReq' => 'False',
		'OldPwdCount' => '0',

		'LogType' => 'none',
		'FromAddress' => '123@321.com',
		'AdminEmail' => '',
		'SMTPUsername' => '',
		'SMTPServer' => '',
		'SMTPPassword' => '',

		'ProxyHost' => '',
		'ProxyPort' => '',
		'ProxyUser' => '',
		'ProxyPasswordEncoded' => '',

		////
		// Non-GUI options below
		//	These options do not appear in the Spectrum GUI and must be set
		//	either in Spectrum.ini or the Config table manually.  Using the
		//	Spectrum.ini file will allow different instances of Spectrum to
		//	run with different settings, but using the Config table instead
		//	will override anything set in any Spectrum.ini file. -TH, 10/07
		////

		'ClinicalGuest' => '0',     // 0 - No Guest access in Clinical configuration, 1 - Guest button & login available in clinical configuration
		'CustomerDataGroups'=>'0',  // 0 - No DataGroup option in Customer data, 1 - DataGroup data available in Customer data.

		'DoTrace' => true,
		'TraceLevel' => 0,

		'Compliance' => false,
		'EnableESig' => false,
		'EnableAuditAccess' => false,
		'ReasonForChangePrompt' => 'Never',

		'SpectrumForEducators' => false,
		'GetSecondSlideServerEnabled' => false,

		'ExternalHostNames' => '',
		'PALHCPollingFrequency' => 10,

		//default the logging to OFF
		'LogLevelJavascript' => 'OFF',
		'LogLevelImageServer' => 'OFF'
	);

	ksort($DefaultArray);	// Purely for debug

	return $DefaultArray;
}


//function SetCookie($Name, $Value, $ExpireTime)
// Add this cookie to the user@host cookie file.
// NOTE: RFC 2109 states that browsers only need to support a limit of 4K bytes per cookie file
function AssignCookie($Name, $Value, $ExpireTime=0, $HttpOnly = false)
{
	$Secs = intval ($ExpireTime);
	if ($Secs > 0)
	{
		$Units = strchr($ExpireTime, ' ');
		if ($Units != false)
		{
			if ($Units == ' days')
				$Secs *= 24*60*60;
			elseif ($Units == ' years')
				$Secs *= 365*24*60*60;
			elseif ($Units == ' hours')
				$Secs *= 60*60;
			elseif ($Units == ' minutes')
				$Secs *= 60;
		}
		$Secs += time();
	}
	//else Expire at end of session

	setcookie($Name, $Value, $Secs, '/', '', false, $HttpOnly);
}

function RemoveCookie($Name)
{
	// Remove the cookie (expire time of Jan 1, 1970 + 1 second)
	setcookie($Name, '', 1, '/', '', false, false);
}

// Return to calling page with the error set
// ent:	string to display
function ReturnSuccess ($String)
{
	$_SESSION['SuccessString'] = $String;

	Navigate($_SESSION['ReturnPage']);
}

// Return to the given page, return the passed parameters
// ent:	string to display
function ReturnError ($String)
{
	global $DO_NAVIGATE;

	// Issue error
	$DO_NAVIGATE = true; // clients calling ReturnError() should be sure page returned to uses GetParm()
	SpectrumErrorHandler(E_ENTRY, $String, NULL, NULL);

	exit();
}


// deprecated
function ErrorReturn ($String)
{
	ReturnError($String);
}


// Has this page returned with an error set?
function ReturnedWithError()
{
	return isset($_SESSION['ErrorSet']);
}

// Set both this page's paramters and our calling page's parameters (in case of error)
// The 'Do' pages (pages that act upon a config page) should call this.
// deprecated - call SetReturnParms() (SetParms() is done in InitializePage())
function SetPassedParms($Parms)
{
	// Set for this page
	//SetParms($Parms); // done in InitializePage()
	// Set for calling page
	SetReturnParms($Parms);
}

function SetPassedParm($Key, $Parm)
{
	// Set for this page
	SetParm($Key, $Parm);
	// Set for calling page
	SetReturnParm($Key, $Parm);
}

// For checkboxes, only fields that were checked are passed.
// This method determines and sets the field's state even if the field was not checked.
// It does this for both this page and the calling page
function SetPassedParmFromCheckBox($Key)
{
	if (isset($_REQUEST[$Key]))
		$Value = true;
	else
		$Value = false;

	SetPassedParm($Key, $Value);
}

// Setup the parameters to return to the calling page in case of an error.
function SetReturnParms($Parms = NULL)
{
	// Update the last page in history
	SetPageParms(-1, $Parms);
}
function SetReturnParm($Key, $Value)
{
	// Update the calling page (last page in history)
	$Index = GetPageIndex(-1);
	$_SESSION['PageHistory'][$Index]['Params'][$Key] = $Value;
}

// Save these parameters for this page's navigation
function SetPageParms($PageIndex, $Parms = NULL)
{
	if ($PageIndex == 0)
		$PageIndex = -1;	// Current page is the last page added via AddPage();
	$Index = GetPageIndex($PageIndex);

	if ($Parms)
	{
		ScrubParms($Parms, $Parms);
	}
	else
	{
		// Stored parms are already scrubbed
		$Parms = $_SESSION['CurrentParms'];
	}

	foreach ($Parms as $Key => $Value)
		$_SESSION['PageHistory'][$Index]['Params'][$Key] = $Value;
}

// Overwrite the array of parameters used for page configuration
function SetParms(&$Parms, $ThisURL=NULL)
{
	if ($Parms == $_REQUEST)
	{
		ScrubParms($Parms, $_SESSION['CurrentParms']);

		SetNavigation($Parms);

		if (($ThisURL != NULL) && IsNavigating())
		{
			// Overlay with parameters from the history (they may have been modified by SetReturnParms())
			$Page = GetPage($_SESSION['History']['NavIndex']);
			$ReturnedParms = $Page['Params'];
			ApplyParms($ReturnedParms);
		}
	}
	else
	{
		// Parameters are already scrubbed
		foreach ($Parms as $Key => $Value)
			$_SESSION['CurrentParms'][$Key] = $Value;
	}
}

// Ensure no questionable characters are injected into the HTTP request.
// Note we do allow these characters within forms (POSTs), but with SSL there can be no injections.
// We also need to allow these characters with a limited number of GET requests.  Allow for an override.
$DO_CHECK_REQUEST = true;	// Deprecated
$ScrubPattern = '/[<>{}()="\']/';
function IncludeForScrubbing($Chars)
{
	global $ScrubPattern;
	if ($ScrubPattern)
		$ScrubPattern = str_replace(']', $Chars . ']', $ScrubPattern);
	else
		$ScrubPattern = '/[' . $Chars . ']/';
}
function ExcludeFromScrubbing($Chars)
{
	global $ScrubPattern;
	if ($ScrubPattern != null)
	{
		if ($Chars == 'All')
		{
			$ScrubPattern = null;
		}
		else
		{
			$CharList = str_split($Chars, 1);
			$ScrubPattern = str_replace($CharList, '', $ScrubPattern);
		}
	}
}
// Deprecated
function SetCheckRequest($Flag)
{
	if ($Flag == false)
		ExcludeFromScrubbing('All');
}
function CheckRequest()
{
global $DO_CHECK_REQUEST;
if ($DO_CHECK_REQUEST)
	CheckParms($_GET);
}
function CheckParms($Parms)
{
	global $ScrubPattern;

	if ($ScrubPattern == null)
		return;

	foreach ($Parms as $Key => $Value)
	{
		if (preg_match($ScrubPattern, $Key))
		{
			ReturnError('Invalid parameter passed to page');
		}
		if (is_array($Value))
		{
			CheckParms($Value);
		}
		else
		{
			if (preg_match($ScrubPattern, $Value))
			{
				// Allow for comparison operators (searches)
				if (
					($Value != '<') && ($Value != '>') && ($Value != '<>')
		 		&& ($Value != '=') && ($Value != '!=') && ($Value != '<=') && ($Value != '>=') && ($Value != '!=')
				)
				{
					ReturnError('Invalid parameter passed to page');
				}
			}
		}
	}
}

// Sanitize the incoming parameters to avoid code injections
function ScrubParms(&$SourceArray, &$DestArray)
{
	foreach ($SourceArray as $Key => $Value)
	{
		$Key = ScrubParm($Key);
		if (is_array($Value))
		{
			// Use recursion to cover any input array depth.
			ScrubParms($Value, $DestArray[$Key]);
		}
		else
		{
			$DestArray[$Key] = ScrubParm($Value);
		}
	}
}

function ScrubParm($Value)
{
	if (($Value == '<') || ($Value == '>') || ($Value == '<>'))
	{
		// Ensure we allow for the greater than, less than operator used in searches
		return $Value;
	}
	return strip_tags($Value);
}

function SetParm($Key, $Value)
{
	$_SESSION['CurrentParms'][$Key] = $Value;
}


// Add set of paramters to this page's parameters
function ApplyParms($Parms, $Override=true)
{
	if (is_array($Parms) == false)
	{
		// $Parms must be a save key
		$Parms = GetSavedParms($Parms);
	}
	if ($Override)
	{
		// These paramters overide current parameters
		$_SESSION['CurrentParms'] = array_merge($_SESSION['CurrentParms'], $Parms);
	}
	else
	{
		// Saved paramters overide these parameters
		$_SESSION['CurrentParms'] = array_merge($Parms, $_SESSION['CurrentParms']);
	}
}


function GetParms()
{
	return $_SESSION['CurrentParms'];
}

// Try to return the requested parameter.
// If the parameter was not found, either return the supplied Default, or throw an error
function GetParm($parmKey, $Default = NULL)
{
	if (isset($_SESSION['CurrentParms'][$parmKey]))
		return $_SESSION['CurrentParms'][$parmKey];

	// Return the supplied default or blow up
	if (isset($Default))
		return $Default;
	// else
	trigger_error("Required parameter '$parmKey' not passed to page");
}

// Return true if parameter was set
function IsParmSet($parmKey)
{
	if (GetParm($parmKey, false) === false)
		return false;
	// else
	return true;
}

//
// The following functions are used for page parameter storage for the life of the session
//

// Save array of parameters to maintain state for a page
function SaveParms($Key, $Parms)
{
	foreach ($Parms as $parmKey => $parmValue)
		$_SESSION['PageParms'][$Key][$parmKey] = $parmValue;
}

// Save parameter to maintain state for a page
function SaveParm($Key, $parmKey, $parmValue)
{
	$_SESSION['PageParms'][$Key][$parmKey] = $parmValue;
}

function GetSavedParms($Key)
{
	if (isset($_SESSION['PageParms'][$Key]))
	{
		return ($_SESSION['PageParms'][$Key]);
	}
	// else
	return array();
}

function GetSavedParm($Key, $parmKey, $Default=NULL)
{
	if (isset($_SESSION['PageParms'][$Key][$parmKey]))
		return $_SESSION['PageParms'][$Key][$parmKey];
	else
		return $Default;
}

function ClearSavedParms($Key)
{
	unset($_SESSION['PageParms'][$Key]);
}

function ClearSavedParm($Key, $parmKey)
{
	unset($_SESSION['PageParms'][$Key][$parmKey]);
}

// End of page parameter function


// Return the request URL
// Passed URL can be numeric or a string
// 		 0 means the current page
//		-1 means the last page added via AddPage()
function GetURL($Parm)
{
	if (is_numeric($Parm))
	{
		if ($Parm == 0)
		{
			// Current page
			$URL = $_SERVER['SCRIPT_NAME'];
		}
		elseif (isset ($_SESSION['PageHistory']))
		{
			// $Parm should be negative, thus decrement the index
			$pastPageIdx = count($_SESSION['PageHistory']) + $Parm;
			if (isset($_SESSION['PageHistory'][$pastPageIdx]['URL']))
				$URL = $_SESSION['PageHistory'][$pastPageIdx]['URL'];
			else
				$URL = '/Welcome.php';
		}
		else
			$URL = '/Welcome.php';
		return $URL;
	}
	// else
	return $Parm;
}

// Return the page name stripped of path and parameters
function GetBasePage($URL = NULL)
{
	if ($URL == NULL)
		$URL = $_SERVER['SCRIPT_NAME'];

	// Strip parameters from URL
	$pos = strpos($URL, '?');
	if ($pos != false)
		$Page = substr($URL, 0, $pos);
	else
		$Page = $URL;

	// Strip path from URL (note the required === FALSE)
	$pos = strrpos($Page, '/');
	if ($pos === FALSE)
		$pos = 0;
	else
		$pos++;
	$Page = substr($Page, $pos);

	return $Page;
}


// Return current time in binary
function GetTime($Format='')
{
	$TimeZones = array (
		'-12' => 'Pacific/Kwajalein',
		'-11' => 'Pacific/Samoa',
		'-10' => 'Pacific/Honolulu',
		'-9' => 'America/Anchorage', 
		'-8' => 'America/Los_Angeles',
		'-7' => 'America/Denver',
		'-6' => 'America/Chicago',
		'-5' => 'America/New_York',
		'-4' => 'America/Puerto_Rico',
		'-3' => 'America/Argentina/Buenos_Aires',
		'-2' => 'Atlantic/South_Georgia',
		'-1' => 'Atlantic/Azores',
		'+1' => 'Europe/Berlin',
		'+2' => 'Europe/Athens',
		'+3' => 'Europe/Moscow',
		'+4' => 'Asia/Dubai',
		'+5' => 'Asia/Karachi',
		'+6' => 'Asia/Almaty',
		'+7' => 'Asia/Saigon',
		'+8' => 'Asia/Singapore',
		'+9' => 'Asia/Tokyo',
		'+10' => 'Australia/Queensland',
		'+11' => 'Pacific/Kosrae',
		'+12' => 'Pacific/Wake'
	);
	if (isset($_SESSION['TimeZoneOffset']) == false)
	{
		// "System time" is where the dataServer is
		//$oldErrorLevel = error_reporting(0);	// Must lower error level to avoid hard-coding timezone
		$Date = ADB_GetDate();
		//Determine if DataServer is in a timezone that observes DST
		$IsDst = ADB_IsDst();
		if ($Date == NULL)
			return 'Unknown Date/Time';
		$strLen = strlen($Date);
		$gmtOffset = substr($Date, ($strLen - 6), 3);
		$_SESSION['TimeZoneOffset'] = "$gmtOffset hours";
		//error_reporting($oldErrorLevel);
		$gmtOffset = intval($gmtOffset);
	} 
	
	//Set the date.timezone setting for PHP (based on PECL/Linux timezone names); default to UTC
	if (isset($gmtOffset) && isset($TimeZones[$gmtOffset])) 
	{
		$TimeZone = $TimeZones[$gmtOffset];
		//Move back 1 hr to find the correct time zone name, if in DST
		if ($IsDst == TRUE) 
			$TimeZone = $TimeZones[$gmtOffset - 1];   
		date_default_timezone_set($TimeZone);
	} else 
	{
		date_default_timezone_set('UTC');    
	}

	// Request the current GMT time and offset it.
	$timezoneOffset = $_SESSION['TimeZoneOffset'];
	$curDate = strtotime($timezoneOffset);

	if ($Format == '')
		return $curDate;
	return date($Format, $curDate);
}

function GetMemoryUsage()
{
	//$Mem = memory_get_usage();
	$Mem = memory_get_peak_usage(true);
	$MemMbytes = ($Mem / 1000000) . ' MBytes';
	$Allowed = ini_get('memory_limit');
	return 'Peak Memory Usage: ' . $MemMbytes . ' / ' . $Allowed;
}

// Deprecated
function TransferInputsToForm()
{
	$Parms = GetParms();
	foreach ($Parms as $key => $value)
	{
		AddToForm($key, $value);
	}
}

// Convenience routine to create hidden inputs
function AddToForm($Name, $Value)
{
	if (is_array($Value))
	{
		$Name .= "[]";
		foreach($Value as $element)
			echo "<input type=hidden name=$Name value=$element>";
	}
	else
	{
		echo "<input type=hidden name=$Name value=$Value>";
	}
}

function DeepCopy (&$src)
{
	$dst;	// blank declaration
	DeepCopy2($dst, $src);
	return $dst;
}


// 2 argument deep copy (php needs overloading)
// This will create/overwrite the destination if necessary.
function DeepCopy2 (&$dst, &$src)
{
	if (is_object($src))
	{
		$className = get_class($src);
		if (!is_object($dst))
		{
			// First clone the source object to get its methods
			$dst = clone($src);
			// Cloning will copy pointers of all objects to the destination, remove these references so their objects will be copied
			$members = get_class_vars($className);
			foreach($members as $k => &$v)
				$dst->$k = NULL;
		}
		// else
		//  Assume it is correctly instantiated
		//  (We cannot clone because it may be a subclass)
		$reflection = new ReflectionClass($className);
		if ($reflection->hasMethod('DeepCopy'))
		{
			// Copy all members
			$src->DeepCopy($dst);
		}
		else
		{
			// We can't copy private members
			$members = get_class_vars($className);
			foreach($members as $k => &$v)
			{
				DeepCopy2($dst->$k, $src->$k);
			}
		}
	}
	else if (is_array($src))
	{
		if (!is_array($dst))
			$dst = array();
		foreach($src as $k => &$v)
		{
			DeepCopy2($dst[$k], $v);
		}
	}
	else
	{
		$dst = $src;
	}
}


// This function is probably temporary.
// It currently bundles the role to add an image's parent (eg. slide) with that of an image.
// In a future release the Image.Add role should go away.
function HasAddPermission($TableName)
{
	if (HasDataWritePermissions () &&
		HasRolePermission($TableName, 'Add'))
	{
		if (($TableName == 'Slide') || ($TableName == 'Specimen') || ($TableName == 'Spot'))
		{
			return HasRolePermission('Image', 'Add');
		}
		if ($TableName == 'TMA')
		{
			return HasRolePermission('Core', 'Add');
		}
		return true;
	}
	return false;
}

/**
* Returns true if the current user has write access to at least one Data Group
*/
function HasDataWritePermissions ()
{
	foreach($_SESSION['User']['DataGroups'] as $DataGroup)
	{
		if ($DataGroup->AccessFlags == 'Full')
			return true;
	}

	return false;
}

 /**
* Returns true if the current user has read access to  Data Group passed in
*/
function HasDataReadPermissions ($DataGroupId)
{
	foreach($_SESSION['User']['DataGroups'] as $DataGroup)
	{
		if ($DataGroup->AccessFlags == 'Read' && $DataGroup->DataGroupId == $DataGroupId)
			return true;
	}

	return false;
}

// Ensure time format based on configured DateFormat
function CheckTimeFormat(&$Value, &$Message, $IncludeTime = true, $FutureOK = false, $IncludeSeconds = false)
{
	$Value = trim ($Value);
		
	if ($Value == '')
		return true;
	
	if (in_array ($Value, array ('-1 day', '-1 week', '-1 month')))
	{
		$Value = @date ('Y-m-d H:i:s', strtotime ($Value));
		return true;
	}
	
	$DateFormatHint = GetConfigValue('DateFormatHint');
	$Regex = GetConfigValue('DateFormatRegex');
	if ($IncludeTime)
	{
		if (!$IncludeSeconds)
		{
			$Message = 'Invalid Date Format (' . $DateFormatHint . ' hh:mm)';
			$Regex = substr($Regex, 0, strlen($Regex) - 2);
			$Regex .= ' (\d\d):(\d\d)$/';
		}
		else
		{
			$Message = 'Invalid Date Format (' . $DateFormatHint . ' hh:mm:ss)';
			$Regex = substr($Regex, 0, strlen($Regex) - 2);
			$Regex .= ' (\d\d):(\d\d):(\d\d)$/';
		}
	}
	else
	{
		$Message = 'Invalid Date Format (' . $DateFormatHint .')';
	}
	if (strlen($Value) < strlen('yyyy/mm/dd'))
	{
		return false;
	}
	if (preg_match ($Regex, $Value, $matches) == 0)
	{
		return false;
	}
	switch ($DateFormatHint)
	{
		case 'yyyy/mm/dd':
		case 'yyyy-mm-dd':
		case 'yyyy.mm.dd':
			$Year = $matches[1];
			$Month = $matches[2];
			$Day = $matches[3];
			break;
		case 'dd/mm/yyyy':
		case 'dd-mm-yyyy':
		case 'dd.mm.yyyy':
			$Year = $matches[3];
			$Month = $matches[2];
			$Day = $matches[1]; 
			break;
		case 'mm/dd/yyyy':
		case 'mm-dd-yyyy':
		case 'mm.dd.yyyy':
			$Year = $matches[3];
			$Month = $matches[1];
			$Day = $matches[2];
			break;
		default:
			return true; //??			
	}
	$Message = 'Date/Time out of range';
	if ($Year < 1753 || $Year [1] > 9999) return false;
	if ($Month < 1 || $Month > 12) return false;
	switch ($Month)
	{
	case 2:
		if ($Year % 4 == 0 && ($Year % 100 != 0 || $Year % 400 == 0))
		{
			if ($Day < 1 || $Day > 29) return false;
		}
		else if ($Day < 1 || $Day > 28) return false;
		break;
	case 4:
	case 6:
	case 9:
	case 11:
		if ($Day < 1 || $Day > 30) return false;
		break;
	default:
		if ($Day < 1 || $Day > 31) return false;
	}
	if (isset ($matches [4]) && ($matches [4] < 0 || $matches [4] > 23)) return false;
	if (isset ($matches [5]) && ($matches [5] < 0 || $matches [5] > 59)) return false;
	if (isset ($matches [6]) && ($matches [6] < 0 || $matches [6] > 59)) return false;
	
	if (!isset ($matches [4]) && $IncludeTime)
		$Value .= ' 00:00:00';

	if ($FutureOK)
		return true;	
		
	// MS:  Test for Future:  since server can be in another ('future') timezone from client, allow 23hrs 59minutes maximum variance
	$datetime = @getdate (Time() + (60*60*23) + (60*59));
	$Message = 'Future Dates/Times not valid';
	
	if ($Year > $datetime['year'])
		return false;
	elseif ($Year == $datetime['year'])
	{
		if ($Month > $datetime['mon'])
			return false;
		elseif ($Month == $datetime['mon'])
		{
			if ($Day > $datetime['mday'])
				return false;
			elseif ($Day == $datetime['mday'])
			{
				if (isset ($matches [4]) && ($matches [4] > $datetime['hours']))
					return false;
				elseif (isset ($matches [4]) && ($matches [4] == $datetime['hours']))
				{
					if ($matches [5] > $datetime['minutes'])
						return false;
					elseif ($matches [5] == $datetime['minutes'])
					{
						if ($matches [6] > $datetime['seconds'])
							return false;
					}
				}
			}
		}
	}
	$Message = '';
	
	return true;
}

/**
* convert date from the configured date format to the format that DataServer expects: yyyy-mm-ddThh:mm:ss
* 
* @param mixed $Value
*/
function ConvertDateToStandardFormat($Value, $DateFormat = '', $IncludeSeconds = false)
{
	if ('' == $DateFormat)
		$DateFormat = GetConfigValue('DateFormat');
    $Date = DateTime::createFromFormat($DateFormat, $Value);
    if ($Date !== false)  
    {
    	if (false == $IncludeSeconds) 
    	{
        	return $Date->format('Y-m-d') . 'T00:00:00';
		}
        else
        {
        	return  $Date->format('Y-m-d H:i:s');
		}
	}
	return '';
}

/**
* convert date/time to the configured date format currently selected
* 
* @param mixed $Value
*/

function ConvertDateToCurrentConfigFormat($Value, $IncludeTime = false)
{
    if ($Value != '')
    {
    	if (strlen($Value) < strlen('yyyy/mm/dd'))
    		return $Value;
        try
        {
        	// DateTime doesn't handle date formats with '.' as the delimiter,
        	// but Spectrum does
        	if (strstr($Value, '.') !== false)
        		$Value = str_replace('.', '/', $Value);
        	if ($IncludeTime)
        	{
        		if (strlen($Value) === strlen('yyyy/mm/dd'))
        		{
        			// append time
        			$Value .= ' 00:00:00';
				}
        		elseif (strlen($Value) === strlen('yyyy/mm/dd hh:mm'))
        		{
        			// append seconds
        			$Value .= ':00';
				}
        	}
            $ThisDateTime = new DateTime($Value);
        }
        catch(Exception $e) 
        {
            return  $Value; // not a valid date value passed, return $Value unchanged 
        }

        $ThisFormat = GetConfigValue('DateFormat');  
		if ($IncludeTime)
		{
			$ThisFormat .= ' H:i:s';
		}

        $RetDate = $ThisDateTime->format($ThisFormat);      
        return $RetDate;
 
    }
     return  '';
}

/**
* convert date/time from the configured date format to the format that DataServer expects: yyyy-mm-ddThh:mm:ss
* 
* @param mixed $Value
*/
function ConvertDateTimeToStandardFormat($Value)
{
	if (IsStandardFormatDate($Value) === false)
	{
	    $Date = DateTime::createFromFormat(GetConfigValue('DateFormat') . ' H:i:s', $Value);
	    if ($Date !== false)
	        return $Date->format('Y-m-d') . 'T' . $Date->format('H:i:s');

	    // maybe there was no time specified in $Value, try to format it as a Date rather than a DateTime
	    $Date = ConvertDateToStandardFormat($Value);
	    if ($Date !== false)
	        return $Date;
		return '';
	}
	return $Value;
}
/**
* 
* test for date in 'standard' format, i.e. yyyy-mm-dd hh:mm:ss, yyyy-mm-ddThh:mm:ss, or yyyy-mm-dd
*/
function IsStandardFormatDate($Date)
{
	return preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/', $Date, $Matches) !== 0 ||
	       preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)$/', $Date, $Matches) !== 0 ||
	       preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)$/', $Date, $Matches) !== 0;
}
/**
* very basic html formatting of XML string for readability and debugging
*
*/
function FormattedXML ($xmlStr)
{	
	// left column (column 1)
	$xmlDiv 	= preg_replace("/<DIV/i",		"\n\n<DIV",			$xmlStr);
	$xml_Div	= preg_replace("/<\/DIV>/i",	"\n</DIV>\n",		$xmlDiv);
	$xmlForm	= preg_replace("/<FORM/i",	"\n\n<FORM",		$xml_Div);
	$xml_Form	= preg_replace("/<\/FORM>/i",	"\n</FORM>\n",		$xmlForm);
	// single-tab (column 2)
	$xmlTable 	= preg_replace("/<TABLE/i",	"\n\n\t<TABLE",		$xml_Form);
	$xml_Table	= preg_replace("/<\/TABLE>/i",	"\n\t</TABLE>\n",	$xmlTable);
	$xmlTR 		= preg_replace("/<TR/i",		"\n\n\t<TR",		$xml_Table);
	$xml_TR		= preg_replace("/<\/TR>/i",	"\n\t</TR>",		$xmlTR);
	$xmlTH		= preg_replace("/<TH/i",		"\n\t\t<TH", 		$xml_TR);
	$xmlTD		= preg_replace("/<TD/i",		"\n\t\t<TD",		$xmlTH);
	// 2 tabs (column 3)
	$xmlP		= preg_replace("/<P/i",		"\n\t\t<P",			$xmlTD);
	$xmlSelect	= preg_replace("/<SELECT/i",	"\n\t\t<SELECT",	$xmlP);
	$xml_Select	= preg_replace("/<\/SELECT/i",	"\n\t\t</SELECT",	$xmlSelect);
	$xmlUL		= preg_replace("/<UL/i",		"\n\t\t<UL",		$xml_Select);
	$xml_UL		= preg_replace("/<\/UL/i",		"\n\t\t</UL",		$xmlUL);
	// 3 tabs (column 4)
	$xmlOption	= preg_replace("/<OPTION/i",	"\n\t\t\t<OPTION",	$xml_UL);
	$xmlInput	= preg_replace("/<INPUT/i",	"\n\t\t\t<INPUT",	$xmlOption);
	$xmlLI		= preg_replace("/<LI/i",		"\n\t\t\t<LI",		$xmlInput);
	$xmlA		= preg_replace("/<A/i",		"\n\t\t\t<A",		$xmlLI);
	return 		  $xmlA;
}

/**
* Return true if any visible user fields are editable
*
*/
function HasEditableField ($TableName, $Recurse = true)
{
	if ($Recurse) // go to top of hierarchy
	{
		$Tables = array();
		$Table = $TableName;

		while (!empty ($_SESSION['HierarchyLevels'][$Table]->ParentTableName))
			$Table = $_SESSION['HierarchyLevels'][$Table]->ParentTableName;

		// load $Tables[] from hierarchy top to bottom
		$Tables[] = $Table;
		while (!empty ($_SESSION['HierarchyLevels'][$Table]->ChildTableName))
		{
			$Table = $_SESSION['HierarchyLevels'][$Table]->ChildTableName;
			$Tables[] = $Table;
		}	
	}
	else
		$Tables[] = $TableName;

	foreach ($Tables as $Table)
		foreach ($_SESSION['HierarchyLevels'][$Table]->Fields as $Field)
			if ($Field->Visible && $Field->ReadOnly == false)
				return true;

	return false;
}

function GetLowestAccessLevel($AccessLevel1, $AccessLevel2)
{
	if (($AccessLevel1 == 'None') || ($AccessLevel2 == 'None'))
		return 'None';
	if (($AccessLevel1 == 'Read') || ($AccessLevel2 == 'Read'))
		return 'Read';
	if (($AccessLevel1 == 'Full') || ($AccessLevel2 == 'Full'))
		return 'Full';
	return 'Default';
}

function GetHighestAccessLevel($AccessLevel1, $AccessLevel2)
{
	if (($AccessLevel1 == 'Full') || ($AccessLevel2 == 'Full'))
		return 'Full';
	if (($AccessLevel1 == 'Read') || ($AccessLevel2 == 'Read'))
		return 'Read';
	if (($AccessLevel1 == 'None') || ($AccessLevel2 == 'None'))
		return 'None';
	return 'Default';
}

/**
*  return true if XML is properly formed
*	verbose error message returned as optional second parameter
*/
function validXMLstring($xml, &$Error='')
{
	libxml_use_internal_errors(true);
	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->loadXML($xml);

	$errors = libxml_get_errors();
	if (empty($errors))
		return true;

	$error = $errors[ 0 ];
	if ($error->level < 3)
		return true;

	$lines = explode("r", $xml);
	$line = $lines[($error->line)-1];
	$Error = "Malformed XML: {$error->message} at line {$error->line}";

	return false;
}

/**
*  return true if XML file $FileName is properly formed
*	verbose error message returned as optional second parameter
*/
function validXMLFile($FileName, &$Error='')
{
	if (($FileName != '') && (is_readable ($FileName)))
	{
		$xmlString = '';
		$fp = @fopen($FileName, 'r');
		$xmlString = @fread ($fp, filesize($FileName));
		fclose ($fp);
		
		if (!validXMLString ($xmlString, $Error))
			return false;
			
		return true;
	}
	$Error = "Unable to read file <em>$FileName</em>";
	return false;
}

// Ensure number is floating point
function IsFloat($Num, $Strict)
{
	if (preg_match('/^[0-9]*\.[0-9]+$/', $Num) != 0)
		return true;
	if ($Strict == false)
		return is_numeric($Num);
	return false;
}


function IsMarkup (&$filePath)
{
	if (strpos($filePath, "\\_Markup_"))
	{
		// New convention puts all markup images into this folder
		return true;
	}
	// Legacy files may not be contained in a markup folder, thus:
	//	Legacy code: This was working for years, but obviously has a problem in that users could name their images with this convention.
	return preg_match ("/\((\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\)\.svs$/", $filePath) != 0;
}

// Is this path a UNC path?
function IsUNC($Path, $Strict=false)
{
	$Levels = explode('\\', $Path);
	if ($Strict)
	{
		// Strict = '\\host\shareName'
		if (count($Levels) != 4)
			return false;
	}
	else if (count($Levels) < 4)
	{
		return false;
	}
	if (($Levels[0] != '') || ($Levels[1] != ''))
		return false;
	if (strlen($Levels[2]) == 0)
		return false;
	if (strlen($Levels[3]) == 0)
		return false;
	return true;
}

// return components of image path
function SplitImagePath($Path)
{
	if ($Path == '')
	{
		// This check is mostly to prevent an infinite loop when calling GetDefaultImagePath();
		return array('', '');
	}
	foreach ($_SESSION['ImageServers'] as $UNC => $ServerURLs)
	{
		$Len = strlen($UNC);
		if (strncasecmp($Path, $UNC, $Len) == 0 && ($Len == strlen($Path) || ($c = substr($Path, $Len, 1)) == '\\' || $c == '/'))
		{
			return array($UNC, substr($Path, $Len));
		}
	}
	$DefaultUNC = GetDefaultImagePath();
	$Len = strlen($DefaultUNC);
	if (strncasecmp($Path, $DefaultUNC, $Len) == 0 && ($Len == strlen($Path) || ($c = substr($Path, $Len, 1)) == '\\' || $c == '/'))
	{
		return array($DefaultUNC, substr($Path, $Len));
	}
	return array('', '');
}

function GetDefaultImagePath()
{
	if (isset($_SESSION['DefaultImageShare']) == false)
	{
		if (isset($_SESSION['ImageServers']['*']))
		{
			// Get what the default image server considers its UNC, and add to the servers' list
			$ImageFile = new cImageFile('');
			$Contents = $ImageFile->ReadDirectory();
			if ($Contents == false)
				return '';
			$_SESSION['DefaultImageShare'] = $Contents['BasePath'];
		}
		else
		{
			$_SESSION['DefaultImageShare'] = '';
		}
	}
	return $_SESSION['DefaultImageShare'];
}

// Return the common ImageServer internal name (or FALSE) for the images
function GetCommonImageServer($ImageIds)
{
	$CommonImageServer = null;
	$ImageSchema = GetTableObj('Image');
	$Records = $ImageSchema->GetRecordsByIds($ImageIds);
	foreach ($Records as $Record)
	{
		$ImageServer = GetInternalImageServer($Record['CompressedFileLocation']);
		if ($ImageServer !== $CommonImageServer)
		{
			if ($CommonImageServer !== null)
				return FALSE;
			$CommonImageServer = $ImageServer;
		}
	}
	return $CommonImageServer;
}

function AjaxReply($Response)
{
	header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header ('Pragma: public');
	header ('Pragma: no-cache');
	header ('Content-Type: text/plain; charset=utf-8');

	echo json_encode ($Response);
}

function AjaxAlert($Message, $Response=array())
{
	$Response['Alert'] = $Message;
	AjaxReply($Response);
	exit;
}

// Strip the outer tags from the given node.
// This is necessary for AJAX transport (among other things)
function ExtractCell($Node)
{
	$DOM = $Node->ownerDocument;
	$TaggedCell = $DOM->saveXML ($Node);

	$Start = strpos ($TaggedCell, '>') + 1;
	$End = strrpos($TaggedCell, '<');
	if ($Start < $End)
	{
		$Contents = substr ($TaggedCell, $Start, $End - $Start);
	}
	else
	{
		// No contents, tag is self terminating, i.e. <TD ... />
		$Contents = '';
	}

	return $Contents;
}

function IsValidImageOperation($Operation, $FileName, &$ErrMsg)
{
	if ($FileName == '')
	{
		$ErrMsg = 'No Image Filename';
		return false;
	}

	$FileType = GetImageType($FileName);

	if (in_array($FileType, array ('jpg', 'jpeg', 'jp2', 'scn', 'tif', 'tiff', 'ndpi')))
		return true;

	if ($FileType == 'svs')
		return (IsSpectrumForEducators() == false);

	if ($FileType == 'cws')
	{
		if ($Operation == 'Upload')
		{
			$ErrMsg = 'Spectrum does not support the uploading of Composite Web Slide (CWS) files';
			return false;
		}
		return true;
	}

	if ($FileType == 'afi')
	{
		if (($Operation == 'Upload') || ($Operation == 'Reference'))
		{
			$ErrMsg = 'Adding of fused images from Spectrum is not yet supported';
			return false;
		}
		if (($Operation == 'Move') || ($Operation == 'Copy'))
		{
			$ErrMsg = 'Spectrum does not yet support moving or copying of fused images';
			return false;
		}
		return true;
	}

	if ($FileType == 'mrxs')
	{
		if ($Operation == 'Upload')
		{
			$ErrMsg = 'Spectrum does not support the uploading of Mirax files';
			return false;
		}
		return true;
	}

	if ($FileType == 'vms')
	{
		if ($Operation == 'Upload')
		{
			$ErrMsg = 'Spectrum does not support the uploading of VMS files';
			return false;
		}
		return true;
	}

	if ($FileType == 'vmu')
	{
		if ($Operation == 'Upload')
		{
			$ErrMsg = 'Spectrum does not support the uploading of VMU files';
			return false;
		}
		if (($Operation == 'Move') || ($Operation == 'Copy'))
		{
			$ErrMsg = 'Spectrum does not support moving or copying of VMU files';
			return false;
		}
		return true;
	}

	return false;
}


function SetError($Str, $ErrType = '')
{
	$_SESSION['ErrorString'] = $Str;
	$_SESSION['ErrorType'] = $ErrType;
}

function SetSuccess($Str)
{
	$_SESSION['SuccessString'] = $Str;
}

function SetDebug($Str)
{
	$_SESSION['DebugString'][] = $Str;

}

function GetSearchTitle($DefaultTitle)
{
	if (isset($_SESSION['SearchTitle']))
	{
		$SearchTitle = $_SESSION['SearchTitle'];
		unset ($_SESSION['SearchTitle']);
		if ($SearchTitle)
			return $SearchTitle;
	}
	return $DefaultTitle;
}

function ObjectToArray($Obj)
{
	$Arr = array();
	foreach (get_object_vars($Obj) as $Key => $Value)
		$Arr[$Key] = $Value;
	return $Arr;
}
function ArrayToObject($Arr)
{
	$Obj = new stdclass();
	foreach ($Arr as $Key => $Value)
		$Obj->$Key = $Value;
	return $Obj;
}

// PHP has no way to get the first key or element of an array, thus...
function array_first_key($Array)
{
	foreach ($Array as $Key => $Element)
	{
		return $Key;
	}
	return NULL;
}
function array_first_element($Array)
{
	foreach ($Array as $Element)
	{
		return $Element;
	}
	return NULL;
}
function array_last_key($Array)
{
	$LastKey = NULL;
	foreach ($Array as $Key => $Element)
	{
		$LastKey = $Key;
	}
	return $LastKey;
}
function array_last_element($Array)
{
	$LastElement = NULL;
	foreach ($Array as $Element)
	{
		$LastElement = $Element;
	}
	return $LastElement;
}

// Convet number to string containing units of MByes, GByte, TByte
// ent:	Num
// 		Log10 of number's magnitude (0, 3=KBytes, 6=GBytes, 9=TBytes)
function ConvertNumToStr($Num, $Log10, $DecimalAccuracy = 2)
{
	while ($Num >= 1024)
	{
		$Num /= 1024;
		$Log10 += 3;
		if ($Log10 > 9)
			break;
	}

	$Num = number_format($Num, $DecimalAccuracy);

	if ($Log10 < 3)
		return $Num;
	if ($Log10 < 6)
	{
		if ($Num == 1)
			return $Num . ' MByte';
		else
			return $Num . ' MBytes';
	}
	if ($Log10 < 9)
	{
		if ($Num == 1)
			return $Num . ' GByte';
		else
			return $Num . ' GBytes';
	}
	if ($Num == 1)
		return $Num . ' TByte';
	else
		return $Num . ' TBytes';
}


// XXX Move to DataGroups.php when time permits
function CreateDataGroupDropdown($DataGroupId, $Width, $Permission=true)
{
	if ($Permission)
	{
		echo "<td><select style='width: $Width; maxlength='75' type='text' name='DataGroupId' value='xxx' />";
		foreach($_SESSION['User']['DataGroups'] as $DataGroup)
		{
			if ($DataGroup->AccessFlags == 'Full')
			{
				if ($DataGroup->DataGroupId == $DataGroupId)
					echo "
					<option selected='selected' value='$DataGroup->DataGroupId'> $DataGroup->DataGroupName </option>";
				else
					echo "
					<option value='$DataGroup->DataGroupId'> $DataGroup->DataGroupName </option>";
			}
		}
		if ($DataGroupId == '[various]')
			echo "<option selected='selected' value='[various]'> [various] </option>";
		echo ' </select></td>';
	}
	else
	{
		$DataGroupName = DataGroupIdToName($DataGroupId);
		$Str = EncodeText($DataGroupName);
		echo "<td>$Str</td>";
	}
}

// XXX Move to DataGroups.php when time permits
function DataGroupNameToId($DataGroupName)
{
	foreach($_SESSION['User']['DataGroups'] as $DataGroup)
	{
		if ($DataGroup->DataGroupName == $DataGroupName)
		{
			return $DataGroup->DataGroupId;
		}
	}
	return -1;
}

// XXX Move to DataGroups.php when time permits
function DataGroupIdToName($DataGroupId)
{
	foreach($_SESSION['User']['DataGroups'] as $DataGroup)
	{
		if ($DataGroup->DataGroupId == $DataGroupId)
		{
			return $DataGroup->DataGroupName;
		}
	}
	if ($DataGroupId == '[various]')
		return '[various]';
	return '';
}

// get refering page. Use this rather than getenv('HTTP_REFERER') or $_SERVER['HTTP_REFERER'] which are not supported by Internet Explorer
function GetReferer()
{
	$Page = GetPage(-1);
	return isset($Page['URL']) ? $Page['URL'] : '';
}

function GetGlobalImageShare()
{
	return isset($_SESSION['Config']['GlobalImageShare']) ? $_SESSION['Config']['GlobalImageShare'] : '';
}

// for when we want to put a file in a temp folder that apache can access
function GetGlobalTempPath()
{
	return isset($_ENV['TEMP']) ? $_ENV['TEMP'] : '';
}

function outputHdrUserRole()
{
	if (isset($_SESSION['FullName']) && $_SESSION['FullName'] != '')
	{
		$UserName = $_SESSION['FullName'];
	}
	else
	{
		$UserName = $_SESSION['LoginName'];
	}
	$Roles = ADB_GetCurrentUserRoles();
?>
	<div id='hdrUserRole'>
	<img id='hdrUserRoleImg' src="/Images/User.png" alt="">
	<div id='hdrUserRoleInfo'>
		<span>Welcome&nbsp;<? echo $UserName ?></span>
		<br><span>Role&nbsp;&nbsp;</span>
		<?
		if ($_SESSION['Config']['BrowserUserAgent']['isiDevice'])
		{		
			?>
			<select disabled='true' onchange='setRoleFromSelect(this);'>
			<?			
		}
		else
		{
			?>
			<select onchange='setRoleFromSelect(this);'>
			<?			
		}		
		foreach ($Roles as $Role)
		{
			// skip hidden roles. 
			if ($Role->Hide != "1")
			{
				echo "<option value='$Role->Id|$Role->DataHierarchyId'";
				if ($Role->Id == $_SESSION['RoleId'])
				{
					echo "selected";
				}
				echo " >$Role->Name</option>";
			}
		}
		?>
		</select>
	</div>
	</div>
<?
}	

/**
 * Return true if string passed  contain "<" or ">" characters, spaces, and for minimum number of characters 
 * @param string $TValue text value to be validated
 * @param string $TName The name of the field being validated
 * @param int $TMinLength The minimum length that is required, by default set to 0. if set to 0 will not perform check against space and length
 * @param string $TMinLengthStr The minimum length that is required in words, , by default set to null
  */
function IsNotValidText($TValue, $TName, $TMinLength = 0 , $TMinLengthStr = null)
{
	if (!(strpos($TValue,'>') === false) || !(strpos($TName,'<') === false)) 
	{ 
		$_SESSION['ErrorString'] = $TName .' cannot contain  \'>\' OR \'< \' characters ';
		return true;
	}
	
	
	if ((strlen($TValue) < $TMinLength || !(strpos($TValue,' ') === false)) && ($TMinLength > 0) ) 
	{
	 	$_SESSION['ErrorString'] = $TName . ' must be at least '. $TMinLengthStr . ' characters and cannot contain spaces'; 
		return true;
	}
	
	return false;
}

function CacheBrowserUserAgent()
{
	if (!isset($_SESSION['Config']['BrowserUserAgent']))
	{
		$browser = GetBrowser(null);
		$_SESSION['Config']['BrowserUserAgent'] = $browser;

		$_SESSION['Config']['BrowserUserAgent']['isiPad'] = false;
		if (preg_match ('/iPad/i', $browser['browser']))
		{
			$_SESSION['Config']['BrowserUserAgent']['isiPad'] = true;
		}
		$_SESSION['Config']['BrowserUserAgent']['isiPhone'] = false;
		if (preg_match ('/iPhone/i', $browser['browser']))
		{
			$_SESSION['Config']['BrowserUserAgent']['isiPhone'] = true;
		}
		$_SESSION['Config']['BrowserUserAgent']['isiDevice'] = $_SESSION['Config']['BrowserUserAgent']['isiPad'] || $_SESSION['Config']['BrowserUserAgent']['isiPhone'] ;
		
		$_SESSION['Config']['BrowserUserAgent']['isIE'] = false;
		$_SESSION['Config']['BrowserUserAgent']['isIE8'] = false;
		if ((preg_match ('/IE/i', $browser['browser'])))
		{
			$_SESSION['Config']['BrowserUserAgent']['isIE'] = true;
			if (0 === strcasecmp($browser['majorver'], '8'))
				$_SESSION['Config']['BrowserUserAgent']['isIE8'] = true;
		}
	}
}

function browserIsIE8()
{
	if (!isset($_SESSION['Config']['BrowserUserAgent']))
		CacheBrowserUserAgent();
	return isset($_SESSION['Config']['BrowserUserAgent']['isIE8']) && $_SESSION['Config']['BrowserUserAgent']['isIE8'] == true;
}

function browserIsIE()
{
	if (!isset($_SESSION['Config']['BrowserUserAgent']))
		CacheBrowserUserAgent();
	return isset($_SESSION['Config']['BrowserUserAgent']['isIE']) && $_SESSION['Config']['BrowserUserAgent']['isIE'] == true;
}

function GetBrowser($UserAgent)
{
	$browser = get_browser($UserAgent, true);
	// special check for IE, which returns IE7 even if browser is actually IE8 or IE9
	if ((preg_match ('/IE/i', $browser['browser'])))
	{
		// IE8 uses trident/4.0, IE9 uses trident/5.0
		// if trident is not in $browser['browser_name_pattern'], then check $_SERVER['HTTP_USER_AGENT'] 
		$ua = $browser['browser_name_pattern'];
		if (preg_match('/trident\//i', $ua) === 0)
			$ua = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/trident\/4.0/i', $ua))
		{
			// IE 8
			$browser['majorver'] = '8';
		}
		else if (preg_match('/trident\/5.0/i', $ua))
		{
			// IE 9
			$browser['majorver'] = '9';
		}
		$browser['version'] = $browser['majorver'] . '.' . $browser['minorver'];
		$browser['parent'] = 'IE ' . $browser['version'];
	}
	return $browser;
}

/**
* Return true if browser is Internet Explorer
*/
function isIE()
{
	return  preg_match ('/IE/i', $_SESSION['Config']['BrowserUserAgent']['browser']);
}
/**
 * Return string with Search Items to display in SP Healthcare top navigation
 */
function GetSavedSearchListItems()
{
	$SavedSearchTableSchema = GetTableObj("SavedSearch");
	if (!isset($SavedSearchTableSchema))
	{
		return;
	}
	$SavedSearchTableSchema->DBReader->SetReturnType("Array");
	$SavedSearches = $SavedSearchTableSchema->DBReader->GetRecords(0); 

	if (!isset($SavedSearches))
	{
		return;
	}
	
	$EscapedSearchName = '';
	$SearchId = '';
	$Res ='';
	foreach ($SavedSearches as $SavedSearch)
	{
		$EscapedSearchName  = htmlspecialchars($SavedSearch["Name"]);
		$SearchId = $SavedSearch["Id"];
		
		if (strstr($EscapedSearchName, "*"))
		{
			$Res .= "<a onclick='jsPAL.displayAdvancedSearchCriteria(null, $SearchId, false);'><div class='search_hover'>$EscapedSearchName</div></a>";
		}
		else
		{
			$Res .= "<a onclick='jsPAL.displayAdvancedSearchCriteria(null, $SearchId, true);'><div class='search_hover'>$EscapedSearchName</div></a>";
		}
	} 
	return $Res;
}
/**
 * Clears $_SESSION['SearchFTSList'] that holds searching fields 
 * used to populate top navigation Advanced Search fields List
 */
function ClearCachedSearchingFieldsList()
{
	unset($_SESSION['SearchFTSList']);
}

/**
 * Clears out all $_SESSION tables
 * // need to call this after there are changes made in table settings
 */
 function ClearCachedTables()
 {
	 unset ($_SESSION['Lists']);
	 $_SESSION['Lists'] = array();
	 unset ($_SESSION['Tables']);
	 $_SESSION['Tables'] = array();
	 unset ($_SESSION['PALModules']['Tables']);
	 $_SESSION['PALModules']['Tables'] = array();
	 echo 'Session Tables Cleared';
 }
