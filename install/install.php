<?php
// create these files and folders in PHP to
// ensure that PHP has necessary permissions

if (!file_exists('./../db/last_dat')) {
    if (file_put_contents('./../db/last_dat', '0:0')) {
	  chmod('./../db/last_dat', 0700);
	} else {
	  die('error: unable to create /db/last_dat');
	}
}

if (!file_exists('./../db/bhashes')) {
    if (file_put_contents('./../db/bhashes', '') !== false) {
	  chmod('./../db/bhashes', 0700);
	} else {
	  die('error: unable to create /db/bhashes');
	}
}

if (!file_exists("./../db/txs/")) {
  if (!mkdir("./../db/txs/", 0700)) {
    die('error: unable to create /db/txs/');
  }
}
?>

<h1>Installation Successful</h1>

<p>To finish up you should delete the install folder then create a cron job which runs /cron/parse_txs.php once per minute. It will take along time to complete the first time it runs so you should run the script once before creating the cron job. If you get the error 'could not get block/tx' and you're sure you have the full chain then try waiting a few seconds then start the script again.</p>