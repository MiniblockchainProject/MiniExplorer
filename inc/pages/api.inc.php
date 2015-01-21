<?php if (empty($_GET['q'])) { ?>

<h1>Query API</h1><hr />

<div class="row-fluid">
  <div class="span5">

	<h3>Network Data</h3>
	<p>
	  <a href="./?q=getdifficulty">getdifficulty</a> - current mining difficulty<br />
	  <a href="./?q=gethashrate">gethashrate</a> - estimated hash rate (hash/s)<br />
	  <a href="./?q=getblockcount">getblockcount</a> - current block height<br />
	  <a href="./?q=getlasthash">getlasthash</a> - hash of latest block
	</p>

	<h3>Coin Data</h3>
	<p>
	  <a href="./?q=blockreward">blockreward</a> - current block reward<br />
	  <a href="./?q=coinsupply">coinsupply</a> - total coins mined<br />
	  <a href="./?q=unminedcoins">unminedcoins</a> - total unmined coins<br />
	  <a href="./?q=runtime">runtime</a> - time since first block (secs)
	</p>

	<h3>Transaction Data</h3>
	<p>
	  <a href="./?q=txinput">txinput</a>/TxHash - total tx input value<br />
	  <a href="./?q=txoutput">txoutput</a>/TxHash - total tx output value<br />
	  <a href="./?q=txfee">txfee</a>/TxHash - tx fee value (inputs - outputs)<br />
	  <a href="./?q=txcount">txcount</a> - number of tx's in blockchain
	</p>

	<h3>Address Data</h3>
	<p>
	  <a href="./?q=addressbalance">addressbalance</a>/Address/Confs - balance of address<br />
	  <a href="./?q=addresslimit">addresslimit</a>/Address/Confs - withdrawal limit of address<br />
	  <a href="./?q=addresslastseen">addresslastseen</a>/Address - block when address last used<br />
	  <a href="./?q=addresscount">addresscount</a> - number of non-empty addresses
	</p>

	<h3>JSON Data</h3>
	<p>
	  <a href="./?q=getinfo">getinfo</a> - general information<br />
	  <a href="./?q=txinfo">txinfo</a>/TxHash - transaction information<br />
	  <a href="./?q=addressinfo">addressinfo</a>/Address/Confs - address information<br />
	  <a href="./?q=blockinfo">blockinfo</a>/BlockHash - block information
	</p>

  </div>
  <div class="span7">

	<h2>Usage</h2>

	<p>The following example shows a correct URL for checking the balance of an address, disregarding transactions with less than 3 confirmations (the confirmation argument is always optional, the default value is 1). All other queries which take 1 or more arguments use the same arg1 and arg2 parameter names as shown below.</p>

	<pre>/?q=addressbalance&amp;arg1=CGTta3M4t3yXu8uRgkKvaWd2d8DQvDPnpL&amp;arg2=3</pre>

	<p>Or if URL rewriting is active you can use this more friendly format:</p>

	<pre>/q/addressbalance/CGTta3M4t3yXu8uRgkKvaWd2d8DQvDPnpL/3</pre>
	
  </div>
</div>

<?php
} else {
  $q = preg_replace("/[^a-z]/", '', strtolower($_GET['q']));
  
  switch ($q) {
    case 'getdifficulty': ////////////////////////////////////////////
	  $mining_info = $_SESSION[$rpc_client]->getmininginfo();
	  $result = $mining_info['difficulty'];
      break;
    case 'gethashrate': ////////////////////////////////////////////
	  $mining_info = $_SESSION[$rpc_client]->getmininginfo();
	  $result = $mining_info['networkhashps'];
      break;
    case 'getblockcount': ////////////////////////////////////////////
	  $mining_info = $_SESSION[$rpc_client]->getmininginfo();
      $result = $mining_info['blocks'];
      break;
    case 'getlasthash': ////////////////////////////////////////////
      $result = $_SESSION[$rpc_client]->getbestblockhash();
      break;	  
    case 'blockreward': ////////////////////////////////////////////
	  $balance = $_SESSION[$rpc_client]->listbalances(1, array($cb_address));
      $cb_balance = remove_ep($balance[0]['balance']);
      $frac_reman = bcdiv($cb_balance, $total_coin);
      $result = bcmul($first_reward, $frac_reman);
      break;
    case 'coinsupply': ////////////////////////////////////////////
      $tx_stats = $_SESSION[$rpc_client]->gettxoutsetinfo();
      $result = remove_ep($tx_stats['total_amount']);
      break;
    case 'unminedcoins': ////////////////////////////////////////////
	  $balance = $_SESSION[$rpc_client]->listbalances(1, array($cb_address));
      $result = remove_ep($balance[0]['balance']);
      break;
    case 'runtime': ////////////////////////////////////////////
      $now_time = date("Y-m-d H:i:s e");
	  $start_time = date("Y-m-d H:i:s e", $launch_time);
	  $time_diff = get_time_difference($start_time, $now_time);
	  $result = $time_diff['seconds'];
      break;  
    case 'txinput': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('tx hash not specified');
	  } else {
        $tx_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['arg1']));
        $tx = $_SESSION[$rpc_client]->getrawtransaction($tx_id, 1);
	    $total_in = '0';
	    if (count($tx['vin']) > 0) {
	      foreach ($tx['vin'] as $key => $value) {
	        $clean_val = remove_ep($value['value']);
	        $total_in = bcadd($total_in, $clean_val);
	      }
	    } else {
	      $total_in = '0';
	    }
	    $result = $total_in;
        break;
	  }
    case 'txoutput': ////////////////////////////////////////////
      $tx_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['arg1']));
      $tx = $_SESSION[$rpc_client]->getrawtransaction($tx_id, 1);
	  $total_out = '0';
	  foreach ($tx['vout'] as $key => $value) {
	    $clean_val = remove_ep($value['value']);
	    $total_out = bcadd($total_out, $clean_val);
	  }
	  $result = $total_out;
      break;
    case 'txfee': ////////////////////////////////////////////
      $tx_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['arg1']));
      $tx = $_SESSION[$rpc_client]->getrawtransaction($tx_id, 1);
	  $total_in = '0';
	  $total_out = '0';
	  if (count($tx['vin']) > 0) {
	    foreach ($tx['vin'] as $key => $value) {
	      $clean_val = remove_ep($value['value']);
	      $total_in = bcadd($total_in, $clean_val);
	    }
	  } else {
	    $total_in = '0';
	  }
	  foreach ($tx['vout'] as $key => $value) {
	    $clean_val = remove_ep($value['value']);
	    $total_out = bcadd($total_out, $clean_val);
	  }
	  $result = bcsub($total_in, $total_out);
      break;
    case 'txcount': ////////////////////////////////////////////
      $l_dat = explode(':', file_get_contents("./db/last_dat"));
	  $result = $l_dat[1];
      break;  
    case 'addressbalance': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('address was not specified');
	  } else {
        $address = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
        $confs = empty($_GET['arg2']) ? 1 : (int)$_GET['arg2'];
        $ainfo = $_SESSION[$rpc_client]->listbalances($confs, array($address));
        $result = remove_ep($ainfo[0]['balance']);
        break;
	  }
    case 'addresslimit': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('address was not specified');
	  } else {
        $address = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
        $confs = empty($_GET['arg2']) ? 1 : (int)$_GET['arg2'];
        $ainfo = $_SESSION[$rpc_client]->listbalances($confs, array($address));
        $result = remove_ep($ainfo[0]['limit']);
        break;
	  }
    case 'addresslastseen': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('address was not specified');
	  } else {
        $address = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
        $confs = empty($_GET['arg2']) ? 1 : (int)$_GET['arg2'];
        $ainfo = $_SESSION[$rpc_client]->listbalances($confs, array($address));
        $balance = remove_ep($ainfo[0]['balance']);
	    if (clean_number($balance) === '0') {
		  $last_used = 'unknown';
	    } else {
		  $last_used = $ainfo[0]['age'];
	    }
	    $result = $last_used;
		break;
	  }
    case 'addresscount': ////////////////////////////////////////////
      $tx_stats = $_SESSION[$rpc_client]->gettxoutsetinfo();
	  $result = $tx_stats['accounts'];
      break;
    case 'getinfo': ////////////////////////////////////////////
	  $ginfo = $getinfo;
	  unset($ginfo['balance']);
	  unset($ginfo['proxy']);
	  unset($ginfo['keypoololdest']);
	  unset($ginfo['keypoolsize']);
	  unset($ginfo['paytxfee']);
	  header('Content-Type: application/json');
	  echo json_encode($ginfo);
      exit;
    case 'txinfo': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('tx hash not specified');
	  } else {
        $tx_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['arg1']));
        $tinfo = $_SESSION[$rpc_client]->getrawtransaction($tx_id, 1);
        header('Content-Type: application/json');
	    echo json_encode($tinfo);
        exit;
	  }
    case 'addressinfo': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('address not specified');
	  } else {
        $address = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
        $confs = empty($_GET['arg2']) ? 1 : (int)$_GET['arg2'];
        $ainfo = $_SESSION[$rpc_client]->listbalances($confs, array($address));
	    unset($ainfo[0]['ours']);
	    unset($ainfo[0]['account']);
        header('Content-Type: application/json');
        echo json_encode($ainfo[0]);
        exit;
	  }
    case 'blockinfo': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('block hash not specified');
	  } else {
        $block = $_SESSION[$rpc_client]->getblock($_GET['arg1']);
        header('Content-Type: application/json');
        echo json_encode($block);
        exit;
	  }
    default: ////////////////////////////////////////////
       die('unknown command');
  }

  if (rpc_error_check() && $result !== '') {
    echo $result;
  }
}
?>
