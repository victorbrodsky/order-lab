<?php

set_time_limit(3600);

date_default_timezone_set('America/New_York');
$date = date('m/d/Y h:i:s a', time());
$time_start = microtime(true);
$output = exec("C:\casperjs\bin\casperjs phporder.js");
    if (strpos($output, 'Fail: 0') === FALSE) {
		require_once('PHPMailer_5.2.4/class.phpmailer.php');
		$mail             = new PHPMailer();
		$mail->IsSMTP();     
		$mail->SMTPDebug  = 1;                   
		$mail->SMTPAuth   = true;                  
		$mail->SMTPSecure = "ssl";                
		$mail->Host       = "smtp.gmail.com";      
		$mail->Port       = 465;                   
		$mail->IsHTML(true);     
		$mail->Username   = "casperjs.testingunit@gmail.com";  
		$mail->Password   = "HAL90O0)x!";            
		$mail->SetFrom('casperjs.testingunit@gmail.com');
		$mail->AddReplyTo("casperjs.testingunit@gmail.com");
		$mail->Subject    = "casperJS: Server failure occured on $date";
		$mail->Body    = 
		"The casperJS testing unit has picked up a server fault: $output
			<br /><br />
		Timestamp: $date
			<br /><br />
		Please contact the server administrator at:
		<br />
		Name: HAL-9000
		<br />
		Telephone: 212-101-0110
		<br />
		Email: casperjs.testingunit@gmail.com
		<br /><br />
		¡Buena suerte!";
		$mail->AddAddress("casperjs.testingunit@gmail.com");
		if(!$mail->Send()) {
		//echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
		//  echo "An error has occured and an email with the fault has been sent.";
		echo '<span style="color:#FF0000">An error has occured; it was logged and an email notification has been sent.</span>';
		$userip = $_SERVER['REMOTE_ADDR'];
		$file = 'casperjs_error.txt';
		$oldContents = file_get_contents($file);
		$fr = fopen($file, 'w');
		$txt = "ERROR log: $output. Requested by: $userip on $date." . PHP_EOL . PHP_EOL ;
		fwrite($fr, $txt);
		fwrite($fr, $oldContents);
		fclose($fr);	
		echo "<br />";
	    echo "<br />";
		}
    }
	echo "Test Results: $output";
	$userip = $_SERVER['REMOTE_ADDR'];
	$time_end = microtime(true);
	$time = $time_end - $time_start;
	echo "<br />";
	echo "<br />";
	echo "Last test completed in $time seconds\n on $date";
	$file = 'casperjs_log.txt';
	$oldContents = file_get_contents($file);
	$fr = fopen($file, 'w');
	$txt = "Log: $output. Test completed in $time seconds\n on $date. Requested by: $userip" . PHP_EOL . PHP_EOL ;
	fwrite($fr, $txt);
	fwrite($fr, $oldContents);
	fclose($fr);		
?>
