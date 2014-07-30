<?php
	//echo getcwd();
	//chdir('usr\local\bin');
	//echo getcwd();
	//echo "<br>";
	
	date_default_timezone_set('America/New_York');
	$date = date('m/d/Y h:i:s a', time());
	$time_start = microtime(true);
	echo "Running PhantomJS version: ";
	
	//echo exec('/usr/local/bin/phantomjs.exe --version 2>&1');
	echo exec('C:\casperjs\bin\phantomjs --version 2>&1');
	
	echo "<br />";
	echo "Running CasperJS version: ";
	
	//echo exec('/usr/local/bin/casperjs --version 2>&1');
	//echo exec('C:\Python34\python usr\local\bin\casperjs --version 2>&1');
	echo exec('C:\casperjs\bin\casperjs --version 2>&1');
	
	echo "<br />";
	$version = apache_get_version();
	echo "$version\n";
	echo " on ";
	echo PHP_OS;
	echo "<br />";
	$userip = $_SERVER['REMOTE_ADDR'];
	$browserinfo = $_SERVER['HTTP_USER_AGENT'];
	$time_end = microtime(true);//end it on the line you want to end it
	$time = $time_end - $time_start;
	echo "<br />";
	echo "Request completed in $time seconds\n on $date";

$file = 'casperjs_log.txt';
$oldContents = file_get_contents($file);
$fr = fopen($file, 'w');
$txt = "Session initiated: $browserinfo from $userip on $date" . PHP_EOL . PHP_EOL ;
fwrite($fr, $txt);
fwrite($fr, $oldContents);
fclose($fr);
?>
