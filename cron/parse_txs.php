<?php
ini_set('memory_limit', -1);
ini_set('max_execution_time', 99999);

$timer_start = microtime(true);
$loop_count = 0;

require_once(dirname(__FILE__).'/../inc/config.inc.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');

$daemon = new RPCclient($rpc_user, $rpc_pass);
$getinfo = $daemon->getinfo();
$block_height = $getinfo['blocks'];

if (empty($getinfo) || !empty($daemon->error)) {
  die('error: rpc command getinfo() failed');
}

$l_dat = explode(':', file_get_contents(dirname(__FILE__)."/../db/last_dat"));
$s_dat = explode(':', file_get_contents(dirname(__FILE__)."/../db/stat_dat"));

$l_blk = (int) $l_dat[0];
$l_txn = (int) $l_dat[1];
$o_blk = (int) $l_dat[2];
$o_txn = (int) $l_dat[3];

$inp_num = (int) $s_dat[0];
$out_num = (int) $s_dat[1];
$inp_tot = (int) $s_dat[2];
$out_tot = (int) $s_dat[3];

$bhdb_handle = fopen(dirname(__FILE__).'/../db/bhashes', "r+");
$ohdb_handle = fopen(dirname(__FILE__).'/../db/ohashes', "r+");

$null_64bstr = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0".
"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

function get_block_hash($index) {
  global $bhdb_handle;
  fseek($bhdb_handle, 64*$index);
  return fread($bhdb_handle, 64);
}

function put_block_hash($index, $hash) {
  global $bhdb_handle;
  fseek($bhdb_handle, 64*$index);
  fwrite($bhdb_handle, $hash, 64);
}

function get_orph_hash($index) {
  global $ohdb_handle;
  fseek($ohdb_handle, 64*$index);
  return fread($ohdb_handle, 64);
}

function put_orph_hash($index, $hash) {
  global $ohdb_handle;
  fseek($ohdb_handle, 64*$index);
  fwrite($ohdb_handle, $hash, 64);
}

function scan_tx($tx) {

  $totals = array(
	'in' => array(),
	'out' => array(),
	'in_cnt' => array(),
	'out_cnt' => array()
  );

  $add_txs = array();
  $txid = $tx['txid'];

  if (isset($tx['limit'])) {
    if (isset($add_txs[$tx['vin'][0]['address']])) {
      $add_txs[$tx['vin'][0]['address']] .= "$txid:2\n";
    } else {
      $add_txs[$tx['vin'][0]['address']] = "$txid:2\n";
    }
  } else {

    foreach ($tx['vin'] as $k => $input) {
      $clean_val = remove_ep($input['value']);
      if (isset($add_txs[$input['address']])) {
        $add_txs[$input['address']] .= "$txid:0\n";
	    $inp_sum = $totals['in'][$input['address']];
	    $totals['in'][$input['address']] = bcadd($inp_sum, $clean_val);
	    $totals['in_cnt'][$input['address']] += 1;
      } else {
        $add_txs[$input['address']] = "$txid:0\n";
	    $totals['in'][$input['address']] = $clean_val;
	    $totals['in_cnt'][$input['address']] = 1;
      }
    }

    foreach ($tx['vout'] as $k => $output) {
      $clean_val = remove_ep($output['value']);
      if (isset($add_txs[$output['address']])) {
        $add_txs[$output['address']] .= "$txid:1\n";
	    $out_sum = $totals['out'][$output['address']];
	    $totals['out'][$output['address']] = bcadd($out_sum, $clean_val);
	    $totals['out_cnt'][$output['address']] += 1;
      } else {
        $add_txs[$output['address']] = "$txid:1\n";
	    $totals['out'][$output['address']] = $clean_val;
	    $totals['out_cnt'][$output['address']] = 1;
      }
    }
  }

  return array($add_txs, $totals);
}

function update_astats($add_txs, $totals, $orphan=false) {

  global $inp_tot;
  global $out_tot;
  global $inp_num;
  global $out_num;

  foreach ($add_txs as $address => $value) {

    $sub_dir = substr($address, 1, 2);
    $add_dir = "/../db/txs/$sub_dir/$address";

	if (!file_exists(dirname(__FILE__)."/../db/txs/$sub_dir/")) {
      mkdir(dirname(__FILE__)."/../db/txs/$sub_dir/", 0700);
	}

	if (isset($totals['in'][$address])) {
	  $inp_sum = $totals['in'][$address];
	} else {
	  $inp_sum = '0';
	}
	if (isset($totals['out'][$address])) {
	  $out_sum = $totals['out'][$address];
	} else {
	  $out_sum = '0';
	}
	if (isset($totals['in_cnt'][$address])) {
	  $inp_cnt = $totals['in_cnt'][$address];
	} else {
	  $inp_cnt = '0';
	}
	if (isset($totals['out_cnt'][$address])) {
	  $out_cnt = $totals['out_cnt'][$address];
	} else {
	  $out_cnt = '0';
	}

    if ($orphan) {
	  $stats = file_get_contents(dirname(__FILE__)."$add_dir-stats");
	  $stats_arr = explode(':', $stats);
	  $inp_tot = bcsub($inp_tot, $inp_sum);
	  $out_tot = bcsub($out_tot, $out_sum);
	  $inp_sum = bcsub($stats_arr[0], $inp_sum);
	  $out_sum = bcsub($stats_arr[1], $out_sum);
	  $inp_num -= $inp_cnt;
	  $out_num -= $out_cnt;
	  $inp_cnt = $stats_arr[2] - $inp_cnt;
	  $out_cnt = $stats_arr[3] - $out_cnt;
    } else {	  
	  if (file_exists(dirname(__FILE__)."$add_dir-stats")) {
	    $stats = file_get_contents(dirname(__FILE__)."$add_dir-stats");
	    $stats_arr = explode(':', $stats);
	    $inp_tot = bcadd($inp_tot, $inp_sum);
	    $out_tot = bcadd($out_tot, $out_sum);
	    $inp_sum = bcadd($stats_arr[0], $inp_sum);
	    $out_sum = bcadd($stats_arr[1], $out_sum);
	    $inp_num += $inp_cnt;
	    $out_num += $out_cnt;
	    $inp_cnt += $stats_arr[2];
	    $out_cnt += $stats_arr[3];
	  }
	  file_put_contents(dirname(__FILE__)."$add_dir", $value, FILE_APPEND);
	}
    $stats = "$inp_sum:$out_sum:$inp_cnt:$out_cnt";
	file_put_contents(dirname(__FILE__)."$add_dir-stats", $stats);
  }
}

while ($l_blk <= $block_height) {

  $block_hash = $daemon->getblockhash($l_blk);
  if (empty($block_hash)) {
    $error = "error: could not get block $l_blk";
	break;
  }

  $last_hash = get_block_hash($l_blk);
  if (empty($last_hash) || $last_hash === $null_64bstr) {

    $block = $daemon->getblock($block_hash);
	if (empty($block)) {
      $error = "error: could not get block $block_hash";
	  break;
	}

	$break = false;
	foreach ($block['tx'] as $key => $txid) {
	  $tx = $daemon->getrawtransaction($txid, 1);
	  if (empty($tx)) {
        $error = "error: could not get tx $txid";
	    $break = true;
		break;
	  } else {
	    $tx_dat = scan_tx($tx);
	  }
	}

	if ($break === true) {
	  break;
	} else {
      update_astats($tx_dat[0], $tx_dat[1]);
      put_block_hash($l_blk, $block_hash);
	  $l_txn += count($block['tx']);
	  $l_blk++;
	}

  } elseif ($last_hash === $block_hash) {
    continue;
  } else {

    $block = $daemon->getblock($last_hash);
	if (empty($block)) {
      $error = "error: could not get orphan block $last_hash";
	  break;
	}

	$break = false;
	foreach ($block['tx'] as $key => $txid) {
	  $tx = $daemon->getrawtransaction($txid, 1);
	  if (empty($tx)) {
        $error = "error: could not get orphan tx $txid";
	    $break = true;
		break;
	  } else {
	    $tx_dat = scan_tx($tx);
	  }
	}

	if ($break === true) {
	  break;
	} else {
      update_astats($tx_dat[0], $tx_dat[1], true);
      put_orph_hash($o_blk, $last_hash);
      put_block_hash($l_blk, $null_64bstr);
      $o_txn += count($block['tx']);
      $l_txn -= $o_txn;
      $o_blk++;
	  $l_blk--;
    }
  }

  $loop_count++;
}

file_put_contents(dirname(__FILE__)."/../db/last_dat", "$l_blk:$l_txn:$o_blk:$o_txn");
file_put_contents(dirname(__FILE__)."/../db/stat_dat", "$inp_num:$out_num:$inp_tot:$out_tot");

if (empty($error)) {
  $time = microtime(true) - $timer_start;
  echo "Processed $loop_count blocks in $time seconds";
} else {
  echo $error;
}
?>
