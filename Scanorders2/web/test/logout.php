<?php
	date_default_timezone_set('America/New_York');
	$date = date('m/d/Y h:i:s a', time());
	$userip = $_SERVER['REMOTE_ADDR'];
$file = 'casperjs_log.txt';
$oldContents = file_get_contents($file);
$fr = fopen($file, 'w');
$txt = "Session terminated: $userip went AWOL on $date" . PHP_EOL . PHP_EOL ;
fwrite($fr, $txt);
fwrite($fr, $oldContents);
fclose($fr);
?>
