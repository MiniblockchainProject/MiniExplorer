<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Install MiniExplorer</title>
</head>
<body style="padding:20px;text-align:center;">
<?php
// create these files and folders in PHP to
// ensure that PHP has necessary permissions

if (!file_exists('./../db/last_dat')) {
    if (file_put_contents('./../db/last_dat', '0:0:0:0')) {
	  chmod('./../db/last_dat', 0700);
	} else {
	  die('error: unable to create /db/last_dat');
	}
}

if (!file_exists('./../db/stat_dat')) {
    if (file_put_contents('./../db/stat_dat', '0:0:0:0')) {
	  chmod('./../db/stat_dat', 0700);
	} else {
	  die('error: unable to create /db/stat_dat');
	}
}

if (!file_exists('./../db/bhashes')) {
    if (file_put_contents('./../db/bhashes', '') !== false) {
	  chmod('./../db/bhashes', 0700);
	} else {
	  die('error: unable to create /db/bhashes');
	}
}

if (!file_exists('./../db/ohashes')) {
    if (file_put_contents('./../db/ohashes', '') !== false) {
	  chmod('./../db/ohashes', 0700);
	} else {
	  die('error: unable to create /db/ohashes');
	}
}

if (!file_exists("./../db/txs/")) {
  if (!mkdir("./../db/txs/", 0700)) {
    die('error: unable to create /db/txs/');
  }
}
?>
<h1>Installation Successful</h1>

<p>To finish up you should delete the install folder then edit the /inc/config.inc.php file and apply the correct settings. Next you will need to create a cron job which runs /cron/parse_txs.php once per minute. It will take a long time to complete the first time it runs so you should manually run the script once before creating the cron job. If you get the error 'could not get block/tx' and you're sure you have the full chain just wait a few seconds then start the script again (the daemon will temporarily stop working if overloaded). You will probably have to restart the script many times the first time it processes the full blockchain.</p>

</body>
</html>
