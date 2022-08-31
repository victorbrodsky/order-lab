<?php
/**
 * Created by PhpStorm.
 * User: Oleg Ivanov
 * Date: 6/29/2022
 * Time: 5:03 PM
 */

ob_implicit_flush(true);ob_end_flush();

$cmd = "ping 127.0.0.1";

$descriptorspec = array(
    0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
    1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
    2 => array("pipe", "w")    // stderr is a pipe that the child will write to
);
flush();
$process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
echo "<pre>";
if (is_resource($process)) {
    while ($s = fgets($pipes[1])) {
        print $s;
        flush();
    }
}
echo "</pre>";





