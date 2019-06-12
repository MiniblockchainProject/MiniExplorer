<?php if (empty($_GET['q'])) { ?>

<h1>Query API</h1><hr />

<div class="row-fluid">
  <div class="span5">

	<h3>Network Data</h3>
	<p>
	  <a href="./?q=getinfo">getinfo</a> - general information<br />
	  <a href="./?q=getdifficulty">getdifficulty</a> - current mining difficulty<br />
	  <a href="./?q=gethashrate">gethashrate</a> - estimated hash rate (hash/s)<br />
	  <a href="./?q=getblockcount">getblockcount</a> - current block height<br />
	  <a href="./?q=getlasthash">getlasthash</a> - hash of latest block
	</p>

	<h3>Transaction Data</h3>
	<p>
	  <a href="./?q=txinfo">txinfo</a>/TxHash - transaction information<br />
	  <a href="./?q=txinput">txinput</a>/TxHash - total tx input value<br />
	  <a href="./?q=txoutput">txoutput</a>/TxHash - total tx output value<br />
	  <a href="./?q=txfee">txfee</a>/TxHash - tx fee value (inputs - outputs)<br />
	  <a href="./?q=txcount">txcount</a> - number of tx's in blockchain
	</p>

	<h3>Address Data</h3>
	<p>
	  <a href="./?q=addressinfo">addressinfo</a>/Address/Confs - address information<br />
	  <a href="./?q=addresstxns">addresstxns</a>/Address/Count - get recent tx's for address<br />
	  <a href="./?q=addresstxpage">addresstxpage</a>/Address/Page/Count - get tx's for address in paged format<br />
	  <a href="./?q=addresstxcount">addresstxcount</a>/Address/TxType - count tx's sent/received by address<br />
	  <a href="./?q=addressbalance">addressbalance</a>/Address/Confs - balance of address<br />
	  <a href="./?q=addresslimit">addresslimit</a>/Address/Confs - withdrawal limit of address<br />
	  <a href="./?q=addresslastseen">addresslastseen</a>/Address - block when address last used<br />
	  <a href="./?q=addresscount">addresscount</a> - number of non-empty addresses
	</p>

	<h3>Other Data</h3>
	<p>
	  <a href="./?q=blockinfo">blockinfo</a>/BlockHash - block information<br />
	  <a href="./?q=blockreward">blockreward</a> - current block reward<br />
	  <a href="./?q=coinsupply">coinsupply</a> - total coins mined<br />
	  <a href="./?q=unminedcoins">unminedcoins</a> - total unmined coins<br />
	  <a href="./?q=runtime">runtime</a> - time since first block (secs)
	</p>
	
	<h3>Propagation</h3>
	<p>
	  <a href="./?q=sendrawtx">sendrawtx</a>/TxHex - send raw transaction hex<br />
	  <a href="./?q=submitblock">submitblock</a>/BlockHex - submit raw block hex
	</p>

  </div>
  <div class="span7">

	<h2>Usage</h2>

	<p>The following example shows a correct URL for checking the balance of an address, disregarding transactions with less than 3 confirmations. All other queries which take 1 or more arguments use the same arg1 and arg2 parameter names as shown below.</p>

	<pre>/?q=addressbalance&amp;arg1=CGTta3M4t3yXu8uRgkKvaWd2d8DQvDPnpL&amp;arg2=3</pre>

	<p>Or if URL rewriting is active you can use this more friendly format:</p>

	<pre>/q/addressbalance/CGTta3M4t3yXu8uRgkKvaWd2d8DQvDPnpL/3</pre>
		
	<br /><br />
	
	<h4>addressbalance</h4>
	
	<p>The <i>Confs</i> parameter is optional, the default value is 1.</p><br />
	
	<h4>addresstxns</h4>
	
	<p>The <i>Count</i> parameter is optional, if not supplied it will return all transactions for that address. The returned json contains the transaction id's (TxHash) along with the type of transaction ('received', 'sent', 'limit updated', and 'invalid/orphaned'). Results are ordered newest to oldest.</p><br />
	
	<h4>addresstxpage</h4>
	
	<p>Provides a page-based list of transactions for an address. The <i>Page</i> parameter will determine the page returned and starts at 0 for the most recent page. The <i>Count</i> parameter determines the number of transactions per page and is optional (default is 10). Results are ordered newest to oldest.</p><br />
	
	<h4>addresstxcount</h4>
	
	<p>The <i>TxType</i> parameter is optional, if not supplied it will return the sum of both sent and received transactions. Valid TxType values are 1 and 2 (1 = sent, 2 = received).</p>
	
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
      $result = bcadd(remove_ep($tx_stats['total_amount']), $hacked_coins);
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
	case 'sendrawtx': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('tx hex not specified');
	  } else {
	    $raw_tx = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
	    $result = $_SESSION[$rpc_client]->sendrawtransaction($raw_tx);
	  }
	  break;
	case 'submitblock': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('block hex not specified');
	  } else {
	    $raw_blk = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
	    $result = $_SESSION[$rpc_client]->submitblock($raw_blk);
	  }
	  break;
	case 'addresstxpage': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('address was not specified');
	  } else {
	    $page_json = '';
	    $address = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
		$sub_dir = strtolower(substr($address, 1, 2));
		$txn_file = "./db/txs/$sub_dir/$address";
		$txns_str = @file_get_contents($txn_file);
		if ($txns_str === false || empty($txns_str)) die('no transactions found');
		$txns = explode("\n", rtrim($txns_str));
		$page = (int)(empty($_GET['arg2'])? 0 : $_GET['arg2']);
		if ($page < 0) die('invalid page number requested');
		$numpp = (int)(empty($_GET['arg3'])? 10 : $_GET['arg3']);
		if ($numpp <= 0) die('invalid number of txns per page requested');
		$txn_cnt = count($txns);
		if ($txn_cnt > 0) {
	      $page_cnt = ceil((float)$txn_cnt/$numpp);
		  if ($numpp < $txn_cnt) {
		    $txns = array_slice($txns, $page*$numpp, $numpp);
		  }
		  for ($i=count($txns)-1; $i >= 0; --$i) {
		    $txn = explode(':', $txns[$i]);
            $tinfo = $_SESSION[$rpc_client]->getrawtransaction($txn[0], 1);
			unset($tinfo['hex']);
			foreach ($tinfo['vin'] as $key => $value) {
			  $tinfo['vin'][$key]['value'] = remove_ep($value['value']);
			}
		    foreach ($tinfo['vout'] as $key => $value) {
			  $tinfo['vout'][$key]['value'] = remove_ep($value['value']);
		    }
		    $page_json .= json_encode($tinfo).',';
		  }
		  header('Content-Type: application/json');
		  echo '{"pagesTotal": '.$page_cnt.', "txs": ['.rtrim($page_json, ',').']}';
		  exit;
		} else {
		  header('Content-Type: application/json');
		  echo '{"pagesTotal": 0, "txs": []}';
		  exit;
		}
	  }
	case 'addresstxns': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('address was not specified');
	  } else {
	    $address = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
		$sub_dir = strtolower(substr($address, 1, 2));
		$txn_file = "./db/txs/$sub_dir/$address";
		$txns_str = @file_get_contents($txn_file);
		if ($txns_str === false || empty($txns_str)) die('no transactions found');
		$txns = explode("\n", rtrim($txns_str));
	    if (!empty($_GET['arg2'])) {
		  $num = (int)$_GET['arg2'];
		  if ($num <= 0) die('invalid number of txns requested');
		  if ($num < count($txns)) {
		    $txns = array_slice($txns, -$num, $num);
		  }
		}
		for ($i=count($txns)-1; $i >= 0; --$i) {
		  $txn = explode(':', $txns[$i]);
		  $txns[$i] = array('txid' => $txn[0], 'type' => tx_type_str($txn[1]));
		}
	  }
	  header('Content-Type: application/json');
	  echo json_encode($txns);
      exit;
	case 'addresstxcount': ////////////////////////////////////////////
	  if (empty($_GET['arg1'])) {
	    die('address was not specified');
	  } else {
	    $address = preg_replace("/[^a-z0-9]/i", '', $_GET['arg1']);
        $tx_type = empty($_GET['arg2']) ? 0 : (int)$_GET['arg2'];
		$sub_dir = strtolower(substr($address, 1, 2));
		$stats_file = "./db/txs/$sub_dir/$address-stats";
		$stats_str = @file_get_contents($stats_file);
		if ($stats_str === false || empty($stats_str)) die('0');
		$stats = explode(':', $stats_str);
		if ($tx_type == 1) {
			$result = $stats[2];
		} elseif ($tx_type == 2) {
			$result = $stats[3];
		} else {
			$result = $stats[2] + $stats[3];
		}
        break;
	  }
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
