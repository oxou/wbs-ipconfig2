<?php

// Copyright (C) Nurudin Imsirovic <github.com/oxou>
//
// Changes the output of Windows' |ipconfig| and adds colors
//
// Created: 2023-04-03 10:26 AM
// Updated: 2023-04-03 11:26 AM

// TODO(oxou):
//
// 1. We need to add more parameters to this command so that
//    we can filter out specific interfaces, or the opposite,
//    to be able to disable colored output, and so on.

require_once "ipconfig-parse.php";

// Absolute command
$realcmd = "c:\\windows\\system32\\ipconfig.exe";

// Shift arguments that we'll pass onto $realcmd
array_shift($argv);
--$argc;

function main() {
    global $argv, $realcmd;

    // Store stdout of the $realcmd
    $stdout = shell_exec($realcmd) ?? '';
    $iscrlf = intval(strpos($stdout, "\r") !== false);

    // Replace CRLF with LF to be easier to parse.
    $stdout = str_replace(
        array("\r\n", "\r"),
        array("\n", ""),
        $stdout
    );

    // Here we store the new output format with colors
    $newstdout = __ipconfig_parse($stdout);

    $newstdout = implode($iscrlf ? "\r\n" : "\n", $newstdout);
    file_put_contents("php://stdout", $newstdout);
}

main();

?>