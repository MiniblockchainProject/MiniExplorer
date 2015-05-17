<?php
// call required includes
require_once('./lib/common.lib.php');
require_once('./inc/config.inc.php');

// check if website is disabled
if (!$site_enabled) { die($offline_msg); }

// start the session
session_start();

// connect to RPC client
$_SESSION[$rpc_client] = new RPCclient($rpc_user, $rpc_pass);

// get general network info
$getinfo = $_SESSION[$rpc_client]->getinfo();

// save any errors to variable
$rpc_error = $_SESSION[$rpc_client]->error;

// get current page
if (empty($_GET['page'])) {
  if (isset($_GET['address'])) {
    $page = 'address';
	$page_title = "Address ".$_GET['address'];
  } elseif (isset($_GET['b'])) {
    $page = 'block';
	$page_title = "Block #".$_GET['b'];
  } elseif (isset($_GET['block'])) {
    $page = 'block';
	$page_title = "Block ".$_GET['block'];
  } elseif (isset($_GET['tx'])) {
    $page = 'tx';
	$page_title = "Transaction ".$_GET['tx'];
  } elseif (isset($_GET['rawtx'])) {
    $page = 'rawtx';
	$page_title = "Raw Transaction ".$_GET['rawtx'];
  } elseif (isset($_GET['rawblock'])) {
    $page = 'rawblock';
	$page_title = "Raw Block ".$_GET['rawblock'];
  } elseif (isset($_GET['q'])) {
    if (empty($_GET['q'])) {
      $page = 'api';
	  $page_title = "API";
    } else {
      require_once('./inc/pages/api.inc.php');
	  exit;
	}
  } else {
    $page = 'home';
	$page_title = 'Home';
  }
} else {
  $page = urlencode($_GET['page']);
  $title_arr = array('search' => 'Search', 
    'orphaned' => 'Orphaned Blocks',
    'stats' => 'Stats', 'api' => 'API',
	'mempool' => 'Memory Pool',
	'peers' => 'Connected Peers'
  );
  if (isset($title_arr[$page])) {
    $page_title = $title_arr[$page];
  } else {
    $page_title = 'Page Not Found';
  }
}
?>
