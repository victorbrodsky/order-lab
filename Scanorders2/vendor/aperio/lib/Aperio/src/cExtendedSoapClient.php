<?

//==================================================================================
// FILE: cExtendedSoapCient.php
// DESCRIPTION:	This class is a simple wrapper for the SoapClient class provided by
//	in order to allow the ability to force an automatic retry on all attempts to
//	contact the DataServer.
//
// 091007	vunger	Added SetTimeout()
//==================================================================================

include_once '/Skeleton.php';

class cSOAPClients 
{
    const Image                         = 1;
    const Security                      = 2;
    const SpectrumHealthcare            = 3;  
    const Pal							= 4;  
}

function GetSOAPImageClient ()
{
	static $ImgClient = 0;
	
	if ($ImgClient === 0)
		$ImgClient = new cExtendedSoapClient(NULL, array('location'=> GetDataServerURL() . '/Aperio.Images/Image.asmx', 'uri'=>'http://www.aperio.com/webservices/'));
	
	return $ImgClient;
}

function GetSOAPSecurityClient ()
{
	static $SecClient = 0;

	if ($SecClient === 0)
		$SecClient = new cExtendedSoapClient(NULL, array('location'=> GetDataServerURL() . '/Aperio.Security/Security2.asmx', 'uri'=>'http://www.aperio.com/webservices/'));

	return $SecClient;
}

// get Spectrum HealthCare client
function GetSOAPShcClient ()
{
	static $SHCClient = 0;

	if ($SHCClient === 0)
		$SHCClient = new cExtendedSoapClient(NULL, array('location'=> GetDataServerURL() . '/Aperio/Shc', 'uri'=>'http://www.aperio.com/webservices/'));

	return $SHCClient;
}

// get PAL client
function GetSOAPPalClient ()
{
	static $PalClient = 0;

	if ($PalClient === 0)
		$PalClient = new cExtendedSoapClient(NULL, array('location'=> GetDataServerURL() . '/Aperio/Pal', 'uri'=>'http://www.aperio.com/webservices/'));

	return $PalClient;
}

$RENEW_TOKEN = true;
function SetTokenRenewal($TrueFalse)
{
	global $RENEW_TOKEN;
	$RENEW_TOKEN = $TrueFalse;
}

function GetAuthVars ()
{
	global $RENEW_TOKEN;

	if ($RENEW_TOKEN)
	{
		return array
		(
			new SoapParam ($_SESSION ['AuthToken'], 'Token'),
		);
	}
	else
	{
		return array
		(
			new SoapParam ($_SESSION ['AuthToken'], 'Token'),
			new SoapParam ('1', 'DoNotRenewToken')
		);
	}
}


class cExtendedSoapClient extends SoapClient
{
	/**
	* Error message to display if the maximum number of attempts has failed.
	* 
	* @var string
	*/
	private	$TimeoutRefreshMsg = true;

	public function SetTimeout($Timeout, $TimeoutRefreshMsg = true)
	{
		SetSocketTimeout($Timeout);
		$this->TimeoutRefreshMsg = $TimeoutRefreshMsg;
	}


	/**
	* @param string $function_name	- Function name to call
	* @param array $arguments		- Array of SoapParam parameters
	* @param array $options			- Array of options
	* @param mixed $input_headers	- SoapHeader object
	* @param mixed &$output_headers	- Output SoapHeader parameter
	* @param bool $ForceOnce		- Force call to only happen once
	*/
	function __soapCall ($function_name, $arguments, $options = NULL, $input_headers = NULL, &$output_headers = NULL, $ForceOnce = false)
	{
		// Reset any lingering error message
		$_SESSION['DataServerError'] = '';

		$SocketTimeout = ini_get ('default_socket_timeout');

		// Ensure an exception is thrown
		SetExecutionTimeout($SocketTimeout + 10);

		$ConnAttempts = ($ForceOnce == true) ? 1 :  2;
		$ErrorMsg = 'Unknown Error';

		while (true)
		{
			try
			{
				return parent::__soapCall ($function_name, $arguments, $options, $input_headers, $output_headers);
				// Note: This function will sometimes directly call trigger_error('SoapClient::__doRequest()...', E_WARNING)
				// 			if it cannot connect to the DataServer
			}
			catch (Exception $e)
			{
				//$code = $e->getCode(); // always 0
				$ErrorMsg = $e->getMessage();

				if ($ErrorMsg == 'Could not connect to host')
				{
					$ConnAttempts--;
					if ($ConnAttempts > 0)
						continue;
				}
				// else: Do not retry commands, since it could result in duplicate savings of records.
			}

			break;
		}

		// Try to not allow this routine to be called again before displaying the error;
		//   that would result in (2 * numAttempts * timeout) wait for the user.
		SetSafeMode();

		// Transcribe known error messages into something more meaningful
		if ($ErrorMsg == 'Error Fetching http headers') // Timeout while reading DataServer response
		{
			if ($this->TimeoutRefreshMsg)
				trigger_error ("DataServer timed-out after $SocketTimeout seconds when trying to load this page.  Please wait a moment and try again by pressing the refresh button.", E_USER_ERROR);
			else
				trigger_error ("DataServer timeout after $SocketTimeout seconds", E_USER_ERROR);
		}
		if ($ErrorMsg == 'looks like we got no XML document')
			trigger_error ('Malformed XML returned from DataServer', E_USER_ERROR);
		trigger_error ($ErrorMsg, E_USER_ERROR);
	}
}

?>
