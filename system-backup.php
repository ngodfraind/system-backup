<?php

//use lsblk & /etc/fstab to find wich partition to back up
const TEMP_DIRECTORY  = '';
const PARTITIONS = '';
const REMOTE_SERVER = '';
const REMOTE_USER = '';
const REMOTE_PWD = '';
const REMOTE_DEST = '';
const LOG_FILE = '';

//test if everything is ok.
if (!commandExists('fsarchiver')) {
	$msg = "fsarchiver command does not exists.\n";
	logError($msg);
	throw new Exception($msg);
}

if (!is_writable(TEMP_DIRECTORY)) {
	$msg = "The temporary folder is not writable.\n";
	logError($msg);
	throw new Exception($msg);
}

if (!is_writable(LOG_FILE)) {
	$msg = "The log file is not writable.\n";
	logError($msg);
	throw new Exception($msg);
}

$ds = DIRECTORY_SEPARATOR;
$command = "fsarchiver savefs " . TEMP_DIRECTORY . "/archive.fsa " . PARTITIONS . " -A";
echo $command;
$con = ftp_connect(REMOTE_SERVER);

if ($con) {
	$loggedIn = ftp_login($con,  REMOTE_USER, REMOTE_PWD);
	if ($loggedIn) {
		$success = ftp_put(
			$con, 
			REMOTE_DEST . $ds . 'archive-' . date("m-d-y_H-i-s"), 
			TEMP_DIRECTORY . "/archive.fsa",
			FTP_BINARY
		);
		if (!$success) {
			logError("The file archive.fsa was not uploaded to the remote server."); 
		}
		unlink(TEMP_DIRECTORY . "/archive.fsa");
	} else {
		logError("Coudl't login to " . REMOTE_SERVER);
	}
} else {
	logError("Couldn't connect to " . REMOTE_SERVER);
}

function logError($log)
{
	$logLine = date("m-d-y h:i:s") . ': ' . $log . PHP_EOL; 
	file_put_contents(LOG_FILE, $logLine, FILE_APPEND);
}

function commandExists($cmd) {
    $returnVal = shell_exec("which $cmd");
    
    return (empty($returnVal) ? false : true);
}

