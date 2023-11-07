<?php
/* -------------------------------------------------------------
This file is part of PurplePixie Ping (PPPing)

PPPing is (C) Copyright 2010-2023 PurplePixie Systems

PPPing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

PPPing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with PPPing.  If not, see www.gnu.org/licenses

For more information see www.purplepixie.org/phpping
-------------------------------------------------------------- */

require_once __DIR__ . "/../src/PurplePixie/PhpPing/PPPing.php";
use \PurplePixie\PhpPing\PPPing;


/**
 * PPPing CLI Utility and Demonstration
 ***/



/**
 * Display help information via --help flag or when an error occurs
 **/

function DisplayHelp()
{
    echo "Usage: php ping.php [options] hostname\n\n";
    echo "Options available are as follows:\n\n";
    echo " --ttl x | -t x    Set TTL (not working with ppping yet)\n";
    echo " --debug | -d      Turn on debug mode and output\n";
    echo " --delay x         Minimum delay between pings in seconds (or decimal)\n";
    echo " --help | -h       Display this help and quit\n";
    echo " --count x | -c x  Number of times to ping (default infinity)\n\n";
    echo "hostname must be a resolvable host or an IP address to ping (required)\n\n";
}

/**
 * Catch a signal and quit
 **/

function signal_shutdown($signal)
{
    echo "SIGNAL";
    echo "\n";
    DisplayTotals();
    exit();
}

/**
 * Use pnctl if available
 **/

declare(ticks=1);

/**
 * Process and signal control if we can
 **/

if (function_exists("pnctl_signal")) {
    pcntl_signal(SIGTERM, "signal_shutdown");
    pcntl_signal(SIGHUP, "signal_shutdown");
    pnctl_signal(SIGKILL, "signal_shutdown");
}

/**
 * Display totals
 **/

function DisplayTotals()
{
    global $successes, $failures, $times, $count;
    if (($successes <= 0) || ($count <= 0)) $perc = 0;
    else if ($failures <= 0) $perc = 100;
    else {
        $perc = ($successes / $count) * 100;
        $perc = round($perc, 2);
    }
    echo "Sent " . $count . ": " . $successes . " received, " . $failures . " failed - " . $perc . "% success.\n";
    $results = count($times);
    $high = 0;
    $low = 999999999;
    $total = 0;
    foreach ($times as $time) {
        if ($time > $high) $high = $time;
        if ($time < $low) $low = $time;
        $total += $time;
    }
    if ($total > 0) // has something
    {
        $average = round($total / $results, 3);
        echo "Results: high/low/average = " . $high . "/" . $low . "/" . $average . " ms\n";
    }
}

/**
 * Create PPPing class object
 **/

$ping = new PPPing();

/**
 * Default minimum delay between pings (seconds)
 **/

$delay = 1;

/**
 * Default maximum pings (-1 is forever)
 **/

$maxcount = -1;

/**
 * Record successful times
 **/

$times = array();

/**
 * Record successes
 **/

$successes = 0;

/**
 * Record failures
 **/

$failures = 0;

for ($i = 0; $i < $_SERVER['argc']; $i++) {
    switch ($_SERVER['argv'][$i]) {
        case "--ttl":
        case "-t":
            $ping->ttl = $_SERVER['argv'][++$i];
            echo "Setting TTL to " . $ping->ttl . "\n";
            break;
        case "--debug":
        case "-d":
            $ping->debug = true;
            echo "Debug Mode Enabled\n";
            break;
        case "--delay":
            $delay = $_SERVER['argv'][++$i];
            echo "Setting Delay to " . $delay . "s\n";
            break;
        case "--count":
        case "-c":
            $maxcount = $_SERVER['argv'][++$i];
            echo "Setting count to " . $maxcount . "\n";
            break;
        case "--help":
        case "-h":
            DisplayHelp();
            exit();
            break;

        default:
            $ping->hostname = $_SERVER['argv'][$i];
            break;
    }
}

if ($ping->hostname == "") {
    DisplayHelp();
    exit();
}

/**
 * Ping sequence count
 **/

$count = 0;

/**
 * Quit flag
 **/

$quit = false;

while (!$quit) {
    /**
     * Set ping sequence
     **/

    $ping->sequence = $count;
    echo $count . ": ";
    $start = microtime(true);
    /**
     * Perform ping
     **/

    $result = $ping->Ping();
    if ($result < 0) {
        echo $ping->strError($result);
        $failures++;
    } else {
        $successes++;
        $times[] = $result;
        if ($ping->last["set"]) {
            echo "Reply from " . $ping->last["source"] . " " . $result . "ms" . " | ttl: " . $ping->last["ttl"] . " hops: " . $ping->last["hops"];
        } else echo $result . "ms";
    }
    echo "\n";
    $count++;
    if (($maxcount > -1) && ($count >= $maxcount)) $quit = true;
    else {
        $elapsed = microtime(true) - $start;
        if ($elapsed < $delay) {
            $remains = $delay - $elapsed;
            $sleep = $remains * 1000000;
            usleep($sleep);
        }
    }
}
DisplayTotals();
