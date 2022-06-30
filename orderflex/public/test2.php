<?php
/**
 * Created by PhpStorm.
 * User: Oleg Ivanov
 * Date: 6/29/2022
 * Time: 5:03 PM
 */

header("Content-type: text/plain");

// tell php to automatically flush after every output
// including lines of output produced by shell commands
disable_ob();

$command = 'ping 127.0.0.1';
$command = 'C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\vendor\bin\phpunit';
echo "command=$command <br>";

$tests = 'C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\tests\App\TestBundle';
$tests = $tests . '\UserTest.php';
echo "tests=$tests <br>";

system($command . " " . $tests);

function disable_ob() {
    // Turn off output buffering
    ini_set('output_buffering', 'off');
    // Turn off PHP output compression
    ini_set('zlib.output_compression', false);
    // Implicitly flush the buffer(s)
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);
    // Clear, and turn off output buffering
    while (ob_get_level() > 0) {
        // Get the curent level
        $level = ob_get_level();
        // End the buffering
        ob_end_clean();
        // If the current level has not changed, abort
        if (ob_get_level() == $level) break;
    }
    // Disable apache output buffering/compression
    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', '1');
        apache_setenv('dont-vary', '1');
    }
}



