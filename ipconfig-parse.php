<?php

// Copyright (C) Nurudin Imsirovic <github.com/oxou>
//
// Part of wbs-ipconfig2
//
// Parses the ipconfig stdout and returns
// a modified output with colors.
//
// Created: 2023-04-03 10:34 AM
// Updated: 2023-05-07 12:33 AM

function __ipconfig_parse(string $stdout = '', bool $colors = false, string $match = ''): array {
    if ($stdout == '')
        return [];

    $lines = explode("\n", $stdout);
    $lines_count = sizeof($lines);
    $inside_ipconfig_block = true;
    $newstdout = [];

    // Interfaces returned by ipconfig
    // These interfaces are indexed inside this array
    // and then all the information related to that
    // interface is stored inside of that index.
    $interfaces = [];
    $interface = null; // Last interface encountered in the loop

    for ($index = 0; $index < $lines_count; $index++) {
        $line = $lines[$index];

        // Skip over empty lines
        if (empty($line))
            continue;

        if ($line === "Windows IP Configuration")
            $inside_ipconfig_block = true;

        if ($inside_ipconfig_block) {
            // Check if the current line is a line
            // defining the interface name.
            // If it is, create a $interfaces index
            // with the name of that interface.
            $line_before = $lines[$index - 1];
            $line_after  = $lines[$index + 1];

            if (empty($line_before) && empty($line_after) && $line[strlen($line) - 1] === ':') {
                $colon_pos = strpos($line, ':');
                $interface = substr($line, 0, $colon_pos);
                $interfaces[$interface] = [];
                continue;
            }

            // Information regarding that interface.
            if (substr($line, 0, 3) === '   ' && substr($line, 4, 1) !== ' ') {
                $line_trim = trim($line);
                $line_parts = explode(':', $line_trim, 2);
                $key = $line_parts[0];
                $key = str_replace('.', '', $key);
                $key = trim($key);
                $value = trim($line_parts[1]);
                $interfaces[$interface][$key] = $value;
            }
        }
    }

    //$find_match = !empty($match);
    //$interfaces_matched = 0;

    // Write out the $interfaces to $newstdout with colors
    foreach ($interfaces as $index => $interface) {
        $newstdout[] = '';

        if (empty($index))
            $index = "Windows IP Configuration";

        $newstdout[] = $colors ? "\x1B[36m[$index]\x1B[0m" : "[$index]";

        foreach ($interface as $key => $value) {
            //if ($find_match)
                //if (!str_contains(strtolower($key), strtolower($match)))
                    //continue;

            $key_padded = str_pad($key, 35, ' ', STR_PAD_RIGHT);

            if (empty($value))
                $newstdout[] = $colors ? "  \x1B[31m$key_padded : <empty>\x1B[0m" : "  $key_padded : <empty>";
            else
                $newstdout[] = $colors ? "  \x1B[33m$key_padded : \x1B[32m$value\x1B[0m" : "  $key_padded : $value";
        }
    }

    if ($find_match && $interfaces_matched == 0) {
        $newstdout = [
            $colors ? "\x1B[31mError: No interfaces matched.\x1B[0m" : "Error: No interfaces matched."
        ];
    }

    return $newstdout;
}

?>