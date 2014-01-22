<? 
/**
* Create a form for logging into spectrum
* @author	Steven Hashagen
* @package	Login
*
* - vunger	080416	Changed Guest button display to accomadate multiple installation types.
* - msmaga	080416	Added authenticate='off' attribute to the form.
* - msmaga	081009	SSL decoding
*/

// Simplepie has several waring errors that need to be ignored (E_ALL)
set_error_handler('LoginErrorHandler', E_ALL);

include_once '/DisplayMessages.php';
include('Utils/simplepie.inc');


// Once memory in SESSION has been expanded, we will not be able to reload _SESSION.
// Ensure we use the client's memory request
$IniArray = my_parse_ini_file();
if (isset($IniArray['memory_limit']))
{
	ini_set('memory_limit', $IniArray['memory_limit']);
	AssignCookie('memory_limit', $IniArray['memory_limit'], 0);
}
else
{
	RemoveCookie('memory_limit');
}

session_start();

// This removes the Token error messagee if it was generated because a session timed out.
if (isset($_SESSION['ErrorString']))
{
    if (isset($_REQUEST['error']))
    {
       $requestError = htmlspecialchars ($_REQUEST['error'], ENT_NOQUOTES);
       $sessionError = htmlspecialchars ($_SESSION['ErrorString'], ENT_NOQUOTES);
       if ($requestError == "Session is invalid or has timed out" && 
               $sessionError == "Token is invalid or has timed out.")
           unset($_SESSION['ErrorString']);
    }
}

$MessageList = array();
if (isset($_SESSION['ErrorString']))
{
	$MessageList['ErrorString'] = $_SESSION['ErrorString'];
	if (isset($_SESSION['LongErrorStrings']))
		$MessageList['LongErrorStrings'] = $_SESSION['LongErrorStrings'];
}
elseif (isset($_REQUEST['ErrorString']))
{
	$MessageList['ErrorString'] = $_REQUEST['ErrorString'];
}
if (isset($_SESSION['SuccessString']))
{
	$MessageList['SuccessString'] = $_SESSION['SuccessString'];
}

// Clear lingering session data
// Delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) 
{
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]);
}

try
{
	// session_regenerate_id occassionally fails, don't want to call LoginErrorHandler if it does fail  
	set_error_handler('NullErrorHandler');
	session_regenerate_id(true);	// change the SessionID and set $delete_old_session param to true
}
catch (Exception $e)
{
}

// Unset all of the session variables.
$_SESSION = array(); 

set_error_handler('LoginErrorHandler', E_ALL);
if (isset($_COOKIE['PHPSESSID']))
{
	// Mark cookie as HTTP only to avoid javascript compromises
	AssignCookie('PHPSESSID', $_COOKIE['PHPSESSID'], 0, true);
}

ADB_GetConfigValues();

CreateDTD();	
CreateHeader('eSlide Manager - Login');
HeaderInclude('LoginCSS');
HeaderInclude('randimage');
FinishHeader();
?>
<BODY onload="document.frmLogon.user.focus(); checkActiveX();" id="SpectrumLoginPage" >
	<div id="loginBkgd"></div>
	<script type="text/javascript">showImage();</script>
	<div id="loginWrapper" > 
            
	<? echo GetLoginLogo(); ?>
		<div id="loginBoxes" >
   			<div id="loginInside" >
	      		<?
				if (isset($_REQUEST['message']))
				{
					//XXX Why are these separate? These message should be placed into MessageList.
					$Message = htmlspecialchars ($_REQUEST['message'], ENT_NOQUOTES);
					// ssl connections double-encode when outputting htmlspecialchars.  We'll remove space encodings only.
					if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on'))
						$Message = str_replace('%20', ' ', $Message);

					echo "<div class='successDiv'>$Message</div>";	
				}
				elseif (isset($_REQUEST['error']))
				{
					//XXX Why are these separate? These message should be placed into MessageList.
					$Error = htmlspecialchars ($_REQUEST['error'], ENT_NOQUOTES);
					if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on'))
						$Error = str_replace('%20', ' ', $Error);
	
					echo "<div class='errorDiv'>$Error</div>";	
				}

				// The short and long error messages are still contained in _SESSION
				DisplayMessages($MessageList);
                                
                                

				?>
                            
                                <br><br>
                                AUTHENTICATION TEST FOR SCAN ORDER: 
                                <br><br>
                            
				<form name='frmLogon' action='/order/Ldap/authenticate.php' method='post' autocomplete='off'>
					<input type='hidden' name='TimezoneOffset' value='' >
					<label>Username:</label>
					<input class="loginText" type="text" name="user" size="20" autocomplete='off'>
					<label>Password:</label>
					<input class="loginText" type="password" name="password" size="20" autocomplete='off'>
					<!--Swap lines, for testing only <input class="loginBtn" type='submit'  name='submit' value='User Login' > -->
					<div id="loginBtnWrap">
     					<input class="loginBtn" type='submit'  name='submit' value='User Login' >
        				<?
						$AllowGuestButton = true;
						if (strstr($IniArray['InstallationType'], 'clinical'))
						{
							// We cannot allow the guest button for clinical installations, unless...
							$AllowGuestButton = (isset($IniArray['ClinicalGuest']) && $IniArray['ClinicalGuest'] == '1');
						}

						if ($AllowGuestButton)
						{
							echo "<input type='submit' class='loginBtn' name='submit' value='Guest Login' onclick=".'"'."javascript: document.frmLogon.user.value='guest'; document.frmLogon.password.value='none'; return true;".'"'."/>";
						}

						echo "</div>";
						echo "<div id='passwordText'>";
						if (GetSecondSlideServerEnabled())
						{
							echo "Forgot your password? <a href='/2S_ResetPassword.php'>Reset</a><br />";   
							echo "Not a member? <a href='/2S_SignUp.php'>Sign up</a>";
						}
						else
						{
							echo "<a  href='javascript: HelpW = window.open(\"/Help/eSM/eSlideManager.htm#Login.htm\",\"Hwin\",\"left=250,top=60,width=750,height=700,toolbar=0,scrollbars=1,resizable=1\");HelpW.focus();'>Login Help</a>";
						}

							echo "</div>";
						?>
				</form>
				<!-- This div is needed to prevent IE from collapsing the container div above the submit buttons -->
				<!-- set the focus to the username text box -->
				<script type='text/javascript' charset='UTF-8'>
					document.frmLogon.user.focus();
					document.frmLogon.TimezoneOffset.value = new Date ().getTimezoneOffset () * 60;
				</script>
				</div>
			</div>
		</div>
	<? 
	$NewsFeedURL = $_SESSION['Config']['NewsFeedURL'];
	
	if($NewsFeedURL != "") {
		function microtime_float()
		{
			if (version_compare(phpversion(), '5.0.0', '>='))
			{
				return microtime(true);
			}
			else
			{
				list($usec, $sec) = explode(' ', microtime());
				return ((float) $usec + (float) $sec);
			}
		}
		
		$start = microtime_float();
		
		$feed = new SimplePie();
		$feed->set_feed_url($NewsFeedURL);
		$feed->init();
		$feed->handle_content_type();
	}
	?>
	<?
    if($NewsFeedURL != "" && $feed->data) {
		echo '<div id="rssWrapper">';
			
			?>	
			<div id="rss"  style="margin: 0 auto">
				<?php if ($feed->data): 
					echo '<h1>'.$feed->get_title().'</h1>';
					echo '<h3 class="rssDescript">'.$feed->get_description().'</h3>';
					$items = $feed->get_items(0,4); 
					$count = count($items);
					foreach($items as $item):
						$count--;
                        if($count > 0)
							echo '<div class="chunk">';
                        else 
                        	echo '<div class="lastChunk">';
						?>
                        <h2 class="rssChunkTitle"><a target="_blank" href="<?php echo $item->get_permalink(); ?>">
						<? 
						echo $item->get_title();
						echo '</a><span class="rssDate">';
						echo $item->get_date('j M Y');
						echo '</span></h2>';
						echo $item->get_description();
						echo '</div>';
					endforeach;
					echo '</div>';
				endif; 	
		echo '</div>';
	}
	echo '<div style="clear: both"></div>';
	echo '<br></br>';


?>
<!-- set the focus to the username text box -->
<script type='text/javascript' charset='UTF-8'>
	document.frmLogon.user.focus();
	document.frmLogon.TimezoneOffset.value = new Date ().getTimezoneOffset () * 60;
	
	// for Internet Explorer, ActiveX must be enabled
	function checkActiveX()
	{
        if (window.ActiveXObject)
        {
			// IE
			try
			{
				var xmlDomObject = new ActiveXObject("Microsoft.XMLDOM");
					delete xmlDomObject;
					xmlDomObject = null;
			}
			catch(e)
			{
				var msg = "ActiveX controls are disabled in your browser<br>You must enable ActiveX controls to use eSlide Manager<br><br>";
				alert(msg.replace(/<br>/gi, '\n'));
				$('.Footer').append('<div class="footerErrorMsgWhite"><br>' + msg + '</div>');
			}
		}
	}
</script>
<? include("/footer.php"); ?>
</BODY>
</html>

<?
// Login cannot handle recursive errors, so trap any errors here
function LoginErrorHandler($errno, $errstr, $errfile, $errline) 
{
	$ErrorList['ErrorString'] = $errstr;
	DisplayMessages($ErrorList);
	exit();
}
?>
