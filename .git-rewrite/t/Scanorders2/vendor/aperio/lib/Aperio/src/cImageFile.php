<?
/***********************************************************************************
* This class encapsulates the management of image files.  The class abstracts the 
* image type (i.e. SVS, CWS, TIF, etc) so that the user can simply Move(), Copy() or
* Delete() the image without having to be concerned with file type.  In particular,
* this is valuable with CWS files which are actually directoriesFilePath
*
* Typical use case is to Select() an image file, then move, copy, or delete it.
* 
* @package Tables
* 
* 05/02/08 shashagen	created module
* 01/06/09 rellis       modified CopyFile and MoveFile to copy and move .VMS and .MRXS files
* 06/30/10	vunger		Rewrote for Multisite
* 08/10/10	rellis		add RecurseDirectory function
* 08/10/10	rellis		add / to COPY and MOVE ImageServer requests
* 08/17/10	rellis		add _resolveHostAddr (Win7 can't handle hostname in URL for fopen)
* 09/17/10	rellis		ImageServer Move command is actually Rename, not Move
* 09/24/10	rellis		add GetImageId()
* 10/14/10	rellis		add IncludeMarkups parameter to RecurseDirectory
* 10/11/02	rellis		add retry logic for COPYPROGRESS
* 10/11/08	rellis		add code to GetImageId() to restore the previous error handler
* 10/11/22	rellis		add sleep(2) to COPYPROGRESS loop
* 11/01/17	rellis		call ImageServer MOVE rather than RENAME when moving files
* 11/03/08  rellis      add GetUNCsForImageServer function. Pass directory parameter to DIR+A request
* 11/03/24	rellis		modify IsExternalHost to handle ExternalHostNames that are IP addresses
* 11/05/10	rellis		modify DoRecurseDirectory to pass $FilePath to ReadDirectory to support alternate image roots
* 11/05/24	rellis		modify GetImageId to call DataServer directly rather than routing the request through ImageServer
*
************************************************************************************/
static $encode = array('%'=>'%25', ' '=>'%20', '#'=>'%23', '$'=>'%24', '&'=>'%26', '+'=>'%2b', ','=>'%2c', ';'=>'%3b', '='=>'%3d');


// Special error handler to ensure control is returned to caller
function ImageFileErrorHandler($errno, $errstr, $errfile, $errline) 
{
	// Return false so php_errormsg gets set
	return false;
}

class cImageFile
{
	private $ImageId	= null;
	private	$FilePath = '';
	private	$ExternalHostName = '';

	private	$DoAddToken = true;
	private	$DoAddId = true;
	private	$DoAddPath = false;
	private	$Mapping = 'RequestHostName';

	private	$Socket = null;
	private	$ErrorString = '';
	private	$ErrorCode = '';
	private	$FileList = array();


	public function __construct($FilePath='')
	{
		$this->SetFilePath($FilePath);
	}

	public function __destruct()
	{
		$this->_closeSocket();
	}

	public function SetFilePath($FilePath)
	{
		$this->FilePath = $FilePath;
	}

	public function SetId($ImageId)
	{
		$this->ImageId = $ImageId;
	}

	// Deprecated
	public function SelectId($ImageId)
	{
		$this->SetId($ImageId);
		return true;
	}

	public function GetFilePath()
	{
		if (($this->FilePath == '') && ($this->ImageId))
		{
			$ImageData = ADB_GetImageData($this->ImageId);
			$this->FilePath = xmldecode($ImageData['CompressedFileLocation']);
		}
		return $this->FilePath;
	}

	public function AddTokenToURL($Flag)
	{
		$this->DoAddToken = $Flag;
	}

	public function AddIdToURL($Flag)
	{
		$this->DoAddId = $Flag;
		if ($Flag)
			$this->DoAddPath = false;
	}

	public function AddPathToURL($Flag)
	{
		$this->DoAddPath = $Flag;
		if ($Flag)
			$this->DoAddId = false;
	}
   
	public function IsFusedImage()
	{
      $filePath = $this->GetFilePath();
		return ( 0 === strcasecmp(strrpos($filePath, '.'), '.afi')) ;      
	}   

	// This function exists for SIS file generation.  The browser's javascript sends the hostname of what it is connecting to,
	// 		so when ImageScope launches it will connect to that server if the ImageURL starts with a '.'.
	// So if the browser/ImageScope are outside of a DMZ, the returned URL from here will contain the DMZ proxy's name.
	public function SetExternalHostName($HostName)
	{
		$this->SetDomainMapping('SuppliedHostName');
		$this->ExternalHostName = $HostName;
	}

	public function SetDomainMapping($Mapping)
	{
		$this->Mapping = $Mapping;
	}

	public function GetURL()
	{
		$ImageLoc = $this->GetFilePath();

		$ImageLoc = $this->ResolveTMAFileLoc($ImageLoc);
		if (IsExternalHost())
		{
			$URL = GetExternalImageServer($ImageLoc);
		}
		else
		{
			$URL = GetInternalImageServer($ImageLoc);
		}
		if ($URL == '')
		{
			//trigger_error("No ImageServer for $ImageLoc");
			return '';
		}

		$URL = $this->_mapImageServerURL($URL);

		return $this->_addExtras($URL);
	}

	// If running SSL, return a url that can support SSL
	public function GetSSLURL()
	{
		$ImageLoc = $this->GetFilePath();
		$ImageLoc = $this->ResolveTMAFileLoc($ImageLoc);

		if ((IsSSL()) || IsExternalHost())
			$URL = GetExternalImageServer($ImageLoc);
		else
			$URL = GetInternalImageServer($ImageLoc);
		if ($URL == '')
		{
			// Return a bogus URL to produce a broken thumbnail in the browser
			return 'ImageNotFound';
		}

		$URL = $this->_mapImageServerURL($URL);

		return $this->_addExtras($URL);
	}	
   	
	public function GetCrossDomainSafeURL()
	{
		$ImageLoc = $this->GetFilePath();
		$ImageLoc = $this->ResolveTMAFileLoc($ImageLoc);
		$browser = GetBrowser(null);
		//IE7 is the most 'strict' browser with respect to CORS
		//default to IE7
		$IsIE7 = ($browser['browser'] == 'IE' && $browser['majorver'] == 7);
		if ((IsSSL()) || IsExternalHost() || $IsIE7)
		{			
			$URL = GetExternalImageServer($ImageLoc);
		}
		else
		{
			$URL = GetInternalImageServer($ImageLoc);
		}
		if ($URL == '')
		{
			// Return a bogus URL to produce a broken thumbnail in the browser
			return 'ImageNotFound';
		}

		$URL = $this->_mapImageServerURL($URL);

		return $this->_addExtras($URL);
	}   

	private function ResolveTMAFileLoc($ImageLoc)
	{
		// if the file location starts with @ sign then it refers to another
		// ImageID and must be resolved.  This happens in the case of TMA spots
		// which are sub-images of the whole slide image.
		if (substr($ImageLoc, 0, 1) == '@')
		{
			// get the first number which is the ImageId
			$ImageIdString = '';
			$i = 1;
			while (is_numeric(substr($ImageLoc, $i, 1)))
			{
				$ImageIdString .= substr($ImageLoc, $i, 1);
				$i++;
			}
			$ImageLoc = ADB_ResolveImageId($ImageIdString);
		}
		return $ImageLoc;
	}

	// Always use this Apache's rewrite rule
	public function GetBrowserURL()
	{
		$this->SetDomainMapping('RequestHostName');
		$URL = $this->_mapImageServerURL('./imageserver');
		return $this->_addExtras($URL);
	}

	private function _addExtras($URL)
	{
		global $encode;
		if ($this->DoAddToken)
			$URL = $URL . "/@@{$_SESSION['AuthToken']}";

		if ($this->DoAddPath)
		{
			$FilePath = $this->GetFilePath();
			// The ImageSever URLs cannot contain its basepath
			list($UNC, $SubPath) = SplitImagePath($FilePath);
			if ($SubPath)
			{
				$URL .= '/' . str_replace ('\\', '/', bulkencode($SubPath, $encode));
			}
		}

		if (($this->DoAddId) && ($this->ImageId))
			$URL = $URL . '/@' . $this->ImageId;

		return $URL;
	}

	public function GetErrorCode()
	{
		return $this->ErrorCode;
	}

	public function GetErrorMessage()
	{
		return $this->ErrorString;
	}

	public function Reset($Parms='')
	{
		$SaveAddId = $this->DoAddId;
		//!! RESET command does not have image id as a part of the path. the imageid follows the RESET command
		$this->DoAddId = false;
		$URL = $this->GetURL() . '/RESET?@' . $this->ImageId;
		$this->DoAddId = $SaveAddId;
		if ($Parms)
			$URL .= '?' . $Parms;

		$this->_execute($URL);
	}
    
    // if specified, $AlternateRoot specifies an alternate root path
    // for ImageServer to use, i.e. the path is not in ImageServer's
    // base folder.
	public function ReadDirectory($AlternateRoot = null)
	{
		$this->AddPathToURL(true);

		$Content = array();
		$Content['BasePath'] = '';
		$Content['Folders'] = array();
		$Content['Files'] = array();

		//SetSocketTimeout(30);

		$URL = $this->GetURL() . '/?DIR+A';

        /**
        * Valid URL strings may be
        *   //server/imageserver/TOKEN/PATH/?DIR+A 
        *   //server:82/TOKEN/PATH/?DIR+A
        *   //IP_ADDR:82/TOKEN/PATH/DIR+A
        * where PATH may be
        *   empty
        *   alternateServer/Share  (AlternateRoot)
        *   alternateServer/Share/SubDirectory
        *   SubDirectory
        * If alternateServer/share is in middle of URL, strip it out and append to end
        **/

		if ($AlternateRoot != null)
		{		
			list($UNC, $SubPath) = SplitImagePath($AlternateRoot);
			$TempUNC = str_replace ('\\', '/', $UNC);
            
            // Do not try to match the start of the URL. This will avoid the case where 
			//   //server/imageserver/... was found as a match for UNC \\server\images
            if (strstr(substr($URL, 2), $TempUNC))
			{
				$URL = str_replace($TempUNC, "", $URL);
			}
			$URL .="+$UNC";	
		}
		if ($this->_execute($URL) == false)
			return false;

		try
		{
			while (true)
			{
				$Response = fgets ($this->Socket);
				if ($Response == null)
					break;
				$Parms = explode('|', $Response);
				if (strncmp($Parms[1], 'dir', 3) == 0)
					$Content['Folders'][] = $Parms[0];
				elseif (strncmp($Parms[1], 'file', 4) == 0)
					$Content['Files'][] = $Parms[0];
				elseif (strncmp($Parms[1], 'basefolder', 8) == 0)
					$Content['BasePath'] = $Parms[0];
			}
		}
		catch (Exception $e)
		{
			$this->ErrorString = $e->getMessage();
			return false;
		}

		$this->_closeSocket();

		return $Content;
	}

	public function RecurseDirectory($FilePath, $IncludeMarkups = false)
	{
	    $this->FileList = array();
	    $SaveFilePath = $this->GetFilePath();
	    $this->DoRecurseDirectory($FilePath, $IncludeMarkups);
	    $this->SetFilePath($SaveFilePath);
	    return $this->FileList;
	}

	private function DoRecurseDirectory($FilePath, $IncludeMarkups)
	{
		$this->SetFilePath($FilePath);
		$Content = $this->ReadDirectory($FilePath);
		$BasePath = $Content['BasePath'] . '\\';
		$Files = $Content['Files'];
		foreach($Files as $File)
		{
		    $this->FileList[] = $BasePath . $File;
		}
		// recurse through all subfolders
		$Folders = $Content['Folders'];
		foreach ($Folders as $Folder)
		{
			if ($Folder != '_Markup_' || $IncludeMarkups)
				$this->DoRecurseDirectory($FilePath . '\\' . $Folder);
		}
	}

	/**
	 * Copy the selected file to the specified folder
	 * @return true if file exists, false otherwise
	 */
	public function CopyFile($DestinationFolder, &$OutNewFileLocation)
	{
		return $this->TransferFile('Copy', $DestinationFolder, $OutNewFileLocation);
	}

	/**
	 * Move the selected file to the specified folder
	 *
	 * @return true if file exists, false otherwise
	 */
	public function MoveFile($DestinationFolder, &$OutNewFileLocation)
	{
		return $this->TransferFile('Move', $DestinationFolder, $OutNewFileLocation);
	}

	/**
	* Move or Copy a file
	* 
	* @param mixed $Operation - 'Move' or 'Copy'
	* @param mixed $DestinationFolder
	* @param mixed $OutNewFileLocation
	*/
	private function TransferFile($Operation, $DestinationFolder, &$OutNewFileLocation)
	{
		global $encode;
		set_error_handler('ImageFileErrorHandler');
		if ($Operation != 'Copy' && $Operation != 'Move')
		{
			$this->ErrorString = "Invalid Operation for TransferFile - $Operation";
			return false;
		}
		$this->ErrorString = '';

		$FilePath = $this->GetFilePath();
		if (IsValidImageOperation($Operation, $FilePath, $this->ErrorString) == false)
			return false;

		// make sure we have a valid file selected
		if ($this->ImageId == null)
		{
			$this->ErrorString = 'no file selected';
			return false;
		}

		list($UNC, $DestPath) = SplitImagePath($DestinationFolder);

		$DestPath = bulkencode($DestPath, $encode);
		if (substr($DestPath, 0, 1) != '\\')
			$DestPath = "\\$DestPath";
		$URL = $this->GetURL();
		if ($URL == '')
		{
			$this->ErrorString = "Can not " . $Operation . " this image because there is no ImageServer associated with the image";
			return false;
		}
		$URL .= '?' . strtoupper($Operation) . "+$UNC$DestPath";

		if ($this->_execute($URL) == false)
			return false;
		$TransferIdx = trim(fgets($this->Socket));

		return $this->TransferProgress($DestinationFolder, $TransferIdx, $OutNewFileLocation);

	}
	private function TransferProgress($DestinationFolder, $TransferIdx, &$OutNewFileLocation)	
	{
		// Read the status, close the pipe
		$StatusURL = $this->GetURL() . "?TRANSFERPROGRESS+$TransferIdx";
		$RetryCount = 0;
		$MaxRetries = 5;
		while (true)
		{
			sleep(2);
			if ($this->_execute($StatusURL) == false)
			{
				if (++$RetryCount > $MaxRetries)
					return false;
				continue;
			}
			$RetryCount = 0;
			$Progress = trim(fgets($this->Socket));
			if (is_numeric($Progress))
			{
				if ($Progress == 100)
					break;
			}
			else
			{
				$this->ErrorString = str_replace('cAIC:', '', $Progress);
				return false;
			}
		}

		$this->_closeSocket();

		$BaseName = basename($this->GetFilePath());
		if (strcasecmp($BaseName, 'slidescan.ini') != 0)
			$OutNewFileLocation = "$DestinationFolder\\$BaseName";
		else
		{
		    // cws file is actually a folder containing slidescan.ini
			$Folders = explode('\\', dirname($this->GetFilePath()));
			$CWSFolder = array_pop($Folders);
			$OutNewFileLocation = "$DestinationFolder\\$CWSFolder\\$BaseName";
		}
		return true;
	}

	public function DeleteFile()
	{
		set_error_handler('ImageFileErrorHandler');
		$this->ErrorString = '';

		$FilePath = $this->GetFilePath();
		if (IsValidImageOperation('Delete', $FilePath, $this->ErrorString) == false)
			return false;

		// TMA Spots are rectangles within an image, (indicated in the file path by a ?x+y+w+z),
		// 	and thus if the image file is deleted, other references will be compromised.
		// The image is also fathered by a Slide, and can be deleted that way.
		if (strpos($FilePath, '?' ) === true )
		{
			$this->ErrorString = 'Cannot delete image references';
			return false;
		}

		// Delete the image, return true even if image does not exist- this allows us to delete an image record whose file is gone
		$URL = $this->GetURL();
		if ($URL == '')
		{
			$this->ErrorString = "This image can not be deleted because there is no ImageServer associated with the image file";
			return false;
			
		}
		$URL .= '?DELETE+NOEXIST';
		if ($this->_execute($URL) == false)
			return false;

		// Read the status
		$Status = fgets ($this->Socket);
		$this->_closeSocket();

		return true;
	}
	public function GetImageId()
	{
		$prevHandler = set_error_handler('ImageFileErrorHandler');
		$ImageId = $this->GetImageIdInternal();
		if ($prevHandler !== NULL)
			set_error_handler($prevHandler);
		return $ImageId;
	}
	private function GetImageIdInternal()
	{
		$ImageId = 0;
		$Res = ADB_GetFilteredRecordList('Image', 0, 0, array('ImageId'), array('CompressedFileLocation'), array('='), array($this->FilePath), array('Image'));
		if (is_array($Res) && isset($Res[0]) && isset($Res[0]['ImageId']))
		{
			$ImageId = $Res[0]['ImageId'];
		}
		// If ImageId is zero, could mean record doesn't exist or could mean user does not have access
		if ($ImageId == 0)
		{
			$Res = ADB_RecordExists('Image', 'CompressedFileLocation', $this->FilePath);
			if (isset($Res['RecordInfo']) && is_object($Res['RecordInfo']))
			{
				if ($Res['RecordInfo']->Exists == 1 && $Res['RecordInfo']->AccessFlags == 'None')
					$this->ErrorCode = 401;	// set no access error
			}
		}
		return $ImageId;
	}
	
	// resolve hostname portion of URL to host address.
	private function _resolveHostAddr($URL)
	{
		$Begin = strpos($URL, '//');
		if ($Begin !== false)
		{
			$Begin += 2;
			$End = strpos($URL, '/', $Begin + 1);
			if ($End !== false)
			{
				$EndColon = strpos($URL, ':', $Begin + 1);
				if ($EndColon !== false && $EndColon < $End)
					$End = $EndColon;
				$HostName = gethostbyname(substr($URL, $Begin, $End - $Begin));
				$URL = substr($URL, 0, $Begin) . $HostName . substr($URL, $End);
			}
		}
		return $URL;
	}

	private function _execute ($URL)
	{
		$this->ErrorCode = 0;
		$this->ErrorString = '';

		if ($this->Socket)
			$this->_closeSocket();

		try
		{
			// fopen on Win7 doesn't work with hostnames, but does work with host addresses
			$URL = $this->_resolveHostAddr($URL);

			$PrevHandler = set_error_handler('NullErrorHandler');
			$this->Socket = @fopen (('http:'.$URL), 'r');
			set_error_handler($PrevHandler);
			if ($this->Socket != null)
				return true;

			$this->ErrorString = 'Unknown error';
			if (isset($http_response_header[0]))
			{
				// Get the error message & code from the http response
				$this->ErrorString = 'ImageServer: ' . $http_response_header[0];
				$Response = explode(' ', $http_response_header[0]);
				if (isset($Response[1]))
					$this->ErrorCode = $Response[1];
			}

			if (isset($php_errormsg) && ($php_errormsg != ''))
			{
				$this->ErrorString = $this->_stripToken($php_errormsg);
			}
			else
			{
				// Authentication failure?
				// Image opened by another process like ImageScope?
			}
		}
		catch (Exception $e)
		{
			$this->ErrorString = $e->getMessage();
		}

		SpectrumLog('Failed ImageServer Request: ' . $this->ErrorString);
		SpectrumLog($URL, false);
		return false;
	}

	private function _closeSocket()
	{
		if ($this->Socket)
		{
			@fclose($this->Socket);
			$this->Socket = null;
		}
	}

	private function _stripToken($Str)
	{
		$Pos1 = strpos($Str, '@@');
		if ($Pos1 == FALSE)
			return $Str;
		$StrippedStr = substr($Str, 0, $Pos1);

		$Pos2 = strpos($Str, '/', $Pos1);
		if ($Pos2)
			$StrippedStr .= substr($Str, $Pos2, (strlen($Str) - $Pos2));

		return $StrippedStr;
	}

	private function _mapImageServerURL($URL)
	{
		if ($URL[0] == '.')
		{
			if ($this->Mapping == 'SuppliedHostName')
			{
				$URL = str_replace('.', '//' . $this->ExternalHostName, $URL);
			}
			else if ($this->Mapping == 'Relative')
			{
				// Browser can use a relative URL, don't supply a domain
				$URL = str_replace('.', '', $URL);
			}
			else if ($this->Mapping == 'InternalHostName')
			{
				// Map request to known server
                $URL = str_replace('.', '//' . GetSessionServerAddress(), $URL);
			}
			else
			{
				// Use hostname supplied in http request
				$URL = str_replace('.', '//' . $_SERVER['HTTP_HOST'], $URL);
			}
		}
		return $URL;
	}
}


//
//  Functions
//

function GetInternalImageServer($Path)
{
	list($UNC, $SubPath) = SplitImagePath($Path);
	if (isset($_SESSION['ImageServers'][$UNC]))
		return $_SESSION['ImageServers'][$UNC]['Internal'];
	if (isset($_SESSION['ImageServers']['*']))
		return $_SESSION['ImageServers']['*']['Internal'];
	return '';
}

function GetExternalImageServer($Path)
{
	list($UNC, $SubPath) = SplitImagePath($Path);
	if (isset($_SESSION['ImageServers'][$UNC]))
		return $_SESSION['ImageServers'][$UNC]['External'];
	if (isset($_SESSION['ImageServers']['*']))
		return $_SESSION['ImageServers']['*']['External'];
	return '';
}

// returns array of UNCs for the ImageServer
// $ImageServer must be the internal name
function GetUNCsForImageServer($ImageServer)
{
	$UNCs = array();
	foreach ($_SESSION['ImageServers'] as $UNC => $Server)
	{
		if ($Server['Internal'] == $ImageServer)
			$UNCs[] = $UNC;
	}
	return $UNCs;
}

// Return the address for the image server whose URL matches that of the browser
// This is needed for flex code that can only connect to applications that match HTTP_HOST
function GetBrowserImageServer($AddToken = true)
{
	$ImageFile = new cImageFile();
	$ImageFile->AddTokenToURL($AddToken);
	return $ImageFile->GetBrowserURL();
}

function GetInternalImageServerURLs()
{
	$URLs = array();
	foreach ($_SESSION['ImageServers'] as $Paths)
		$URLs[] = $Paths['Internal'];
	return array_unique($URLs);
}

// Check if the host connecting to us is on the list of external host names.
// If a DMZ connects to this apache, it must use a virtual host that is on the list of external names.
//	That will force the returned URL to use the external address and not the internal.
// Thus all requests to use (Apache) should use one of two rewrite rules, one for internal and one for external.
// 	We allow for several external hosts only for convenience - only one is needed for sites where the client cannot access the ImageServer.
function IsExternalHost()
{
	$Host = $_SERVER['HTTP_HOST'];
	$HostAddr = $_SERVER['SERVER_ADDR'];
	$ExternalHosts = explode(',', $_SESSION['Config']['ExternalHostNames']);
	foreach($ExternalHosts as $ExternalHost)
	{
		$ExternalHost = trim($ExternalHost);
		if (($ExternalHost == '*') || ($ExternalHost == $Host) || ($ExternalHost == $HostAddr))
			return true;
	}
	return false;
}

?>
