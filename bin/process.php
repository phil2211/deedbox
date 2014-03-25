#!/usr/bin/php
<?php

//lockfile schreiben, damit der JobProcessor nicht x-fach gleichzeitig ausgeführt wird
define('LOCKFILE', '/tmp/deedbox.lock');

if (file_exists(LOCKFILE))
   // exit;
file_put_contents(LOCKFILE, time() . "\n");

require_once(dirname(__FILE__).'/../scripts/bootstrap_cli.php');

/**
 * IST NOCH NICHT AUF MEHRERE USER AUSGERICHTET - GEHT NOCH NICHT!
 */
/*
$jobProcessor = new DeedBox_JobProcessor();
$jobProcessor->uploadDocuments();
*/
//lockfile entfernen
unlink(LOCKFILE);
