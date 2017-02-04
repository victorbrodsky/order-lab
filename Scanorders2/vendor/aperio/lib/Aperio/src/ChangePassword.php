<?
//==================================================================================
// FILE: ChangePassword.php
// DESCRIPTION:	Creates an HTML form for changing the logged-in user's password
//==================================================================================


include_once '/Skeleton.php';
include_once '/DatabaseRoutines.php';

InitializePage(true);

$LoginName = $_SESSION['User']['LoginName'];

// Since token is not totally valid, it needs to be unset for GetConfigValues()
$AuthToken = $_SESSION['AuthToken'];
unset($_SESSION['AuthToken']);
if (isset($_SESSION['Config']))
{
	unset($_SESSION['Config']);
}
ADB_GetConfigValues();
$_SESSION['AuthToken'] = $AuthToken;

$PasswordRequirements = GetPasswordRequirementsString();

// In case of return from DoChangePassword
$UserPassword1 = (isset($_SESSION['UserNewPassword1']) ? $_SESSION['UserNewPassword1'] : '');
$UserPassword2 = (isset($_SESSION['UserNewPassword2']) ? $_SESSION['UserNewPassword2'] : '');
unset($_SESSION['UserNewPassword1']);
unset($_SESSION['UserNewPassword2']);

echo '<HTML>';
CreateHeader(GetProductName() . ' - User Settings');
HeaderInclude('LoginCSS');
?>
	<style>
		p {
			margin-bottom: 5px;
		}
		label
		{
		width: 130px;
		float: left;
		text-align: left;
		margin-right: 0.5em;
		}
	form {
		margin-left: 10px;
	}
	</style>
<?
FinishHeader();

?>
<BODY onload="StartTimer();  document.PasswordForm.UserNewPassword1.focus();" onunload="StopTimer()">
<?
	include_once '/DisplayMessages.php';
	$_SESSION['HelpFile'] = '/Help/eSM/eSlideManager.htm#ChangePassword.htm';
?>
	<? echo GetLoginLogo(); ?>
	<div id='hierarchy' style='text-align: left' >

	<? DisplayMessages($_SESSION); ?>
	<H3>Change Password</H3>	
		<form name='PasswordForm' enctype="multipart/form-data" action="/DoChangePassword.php" method="post" >
			<p><label>Login:</label><? print(htmlspecialchars($LoginName, ENT_NOQUOTES)) ?></p>
			<? 
				if (GetSecondSlideServerEnabled() && isset($_SESSION['User']['FullName']) && ($_SESSION['User']['FullName'] == ''))
				{
					// if this is a retry then restore UserFullName from previous attempt.
					$FullName = isset($_SESSION['UserFullName']) ? $_SESSION['UserFullName'] : '';
					unset($_SESSION['UserNewFullName']);     
			?>
					<p><label>Full Name:</label><input type='text' maxLength=128 name='UserFullName' value='<? echo $FullName; ?>' /></p>
			<?
				}
			?>
			<p><label>New Password:</label><input type='password' autocomplete='off' maxLength=50 name='UserNewPassword1' value='<? echo $UserPassword1; ?>' /></p>
			<? echo $PasswordRequirements; ?>
			<p><label>Retype New Password:</label><input type='password' autocomplete='off' maxLength=50 name='UserNewPassword2' value='<? echo $UserPassword2; ?>' /></p>
			<br>
			<input type=submit value='Save'/>
			<input type='button' name='Cancel' value='Cancel' onclick='javascript:history.go(-1);'/>
		</form>
</BODY>
</HTML>
