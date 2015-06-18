#!/usr/bin/php
<?php

define('LOCKFILE', dirname(__FILE__).'/wdaemon.lock');

if (file_exists(LOCKFILE)) {
    exit(0);
}

file_put_contents(LOCKFILE, time() . "\n");

error_reporting(E_ERROR);

/**
 * Dies ist der wDaemon
 *
 * Das "w" steht für Work.. der arbeitet also die Files ab..
 * Dies ist der pure JobProcessor
 */

require_once(dirname(__FILE__).'/../scripts/bootstrap_cli.php');

$j = new DeedBox_JobProcessor();
$j->handleDocuments();

unlink(LOCKFILE);
exit(0);
