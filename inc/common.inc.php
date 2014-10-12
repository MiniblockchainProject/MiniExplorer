<?php
// call required includes
require_once('./lib/common.lib.php');
require_once('./inc/config.inc.php');

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
    require_once('./inc/pages/rawtx.inc.php');
	exit;
  } elseif (isset($_GET['rawblock'])) {
    require_once('./inc/pages/rawblock.inc.php');
	exit;
  } elseif (isset($_GET['q'])) {
    if (empty($_GET['q'])) {
      $page = 'api';
	  $page_title = "Query API";
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
  $title_arr = array('search' => 'Search', 'stats' => 'Stats', 'api' => 'API');
  if (isset($title_arr[$page])) {
    $page_title = $title_arr[$page];
  } else {
    $page_title = 'Page Not Found';
  }
}
?>
