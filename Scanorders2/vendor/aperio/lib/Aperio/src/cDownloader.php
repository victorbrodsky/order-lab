<? 

/**
 * Class to encapsulate file downloads to the browser
 **/

class cDownLoader
{
	public function __construct($DstFname, $DataLen, $FileExtension)
	{
		//This will set the Content-Type to the appropriate setting for the file
		switch( $FileExtension ) 
		{
	 		case 'pdf': $ctype='application/pdf'; break;
			case 'csv': $ctype='application/csv'; break;
			case "exe": $ctype="application/octet-stream"; break;
			case "zip": $ctype="application/zip"; break;
			case "doc": $ctype="application/msword"; break;
			case "xls": $ctype="application/vnd.ms-excel"; break;
			case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
			case "gif": $ctype="image/gif"; break;
			case "png": $ctype="image/png"; break;
			case "jpeg":
			case "jpg": $ctype="image/jpg"; break;
			case "mp3": $ctype="audio/mpeg"; break;
			case "wav": $ctype="audio/x-wav"; break;
			case "mpeg":
			case "mpg":
			case "mpe": $ctype="video/mpeg"; break;
            case "mp4": $ctype="video/mp4"; break;
			case "mov": $ctype="video/quicktime"; break;
			case "avi": $ctype="video/x-msvideo"; break;

			//The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
			case "php": trigger_error("Cannot be used for ". $FileExtension ." files", E_USER_ERROR); break;

			default: $ctype="application/force-download";
		}
	
		//Begin writing headers

		// PHP Supplied (close - not exact)
		// header("HTTP/1.1 200 OK");
		// header("Date: Thu, 28 Aug 2008 22:58:14 GMT");
		// header("Server: Apache");
		// header("Pragma: public");
		// header("Expires: 0");
		// header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	
		// This is needed for SSL with IE
		header("Pragma: public");

		header("Cache-Control: public");	// required for IE6 to auto-load .pdf
		header("Content-Description: File Transfer");
		header('Content-Length: '. $DataLen);
	
		//Use the switch-generated Content-Type
		header("Content-Type: $ctype");

		//Force the download
		$str='Content-Disposition: attachment; filename="'.$DstFname.'";'; 
		header($str );
		header("Content-Transfer-Encoding: binary");
	
		// Send it in packet in order to flush
		ob_implicit_flush(true);

		set_time_limit(172800); // allow this script to run for up to 48 hrs
	}

	function WriteData($Data)
	{
		echo $Data;
		ob_flush();
	}

	function WriteFile($Fname)
	{
		// send data in packets
		$packetSize = 8 * 1024;
		$FileSize = FileSize($Fname);
		$fh = fopen($Fname, 'r');
		for ($i=0; $i<$FileSize; $i+=$packetSize)
		{
			$packet = fread($fh, $packetSize);
            //need to clean object buffer before sending data to it
            ob_clean();
			$this->WriteData($packet);
		}

		fclose($fh);
	}
}

?>
