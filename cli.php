<?php

// Copyright (C) Nurudin Imsirovic <github.com/oxou>
//
// Changes the output of Windows' |ipconfig| and adds colors
//
// Created: 2023-04-03 10:26 AM
// Updated: 2023-05-07 12:34 AM

// TODO(oxou):
//
// 1. We need to add more parameters to this command so that
//    we can filter out specific interfaces, or the opposite,
//    to be able to disable colored output, and so on.

require_once "ipconfig-parse.php";

// Absolute command
$realcmd = "c:\\windows\\system32\\ipconfig.exe /all";

// Shift arguments that we'll pass onto $realcmd
array_shift($argv);
--$argc;

function main() {
    global $argv, $realcmd;
    $argv_string = implode(' ', $argv);

    $flag_help = !(str_contains($argv_string, "--help") || str_contains($argv_string, " -h "));
    $flag_colors = !(str_contains($argv_string, "--no-color") || str_contains($argv_string, "--no-colors") || str_contains($argv_string, "--nc"));
    //$flag_match = !(str_contains($argv_string, "--match"));

    // To extract the match string, we must do a strpos of 1st and 2nd quote (") character
    // after the --match= parameter. This could go wrong several ways, but the match should
    // always be provided in the quotation marks.  The format must adhere to: --match="MATCH"
    // or else the match procedure will fail and the message "Malformed match." will be
    // printed.

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
    $newstdout = __ipconfig_parse($stdout, $flag_colors/*, $flag_match_reconstructed*/);

    $newstdout = implode($iscrlf ? "\r\n" : "\n", $newstdout);
    file_put_contents("php://stdout", $newstdout);
}

main();

?>