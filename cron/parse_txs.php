<?php
ini_set('memory_limit', -1);
ini_set('max_execution_time', 999);

require_once(dirname(__FILE__).'/../inc/config.inc.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');

$bhdb_handle = fopen(dirname(__FILE__).'/../db/bhashes', "r+");
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

$daemon = new RPCclient($rpc_user, $rpc_pass, $rpc_host, $rpc_port, $rpc_url);
$getinfo = $daemon->getinfo();
$block_height = $getinfo['blocks'];

if (empty($getinfo) || !empty($daemon->error)) {
  die('error: rpc command getinfo() failed');
}

$l_dat = explode(':', file_get_contents(dirname(__FILE__)."/../db/last_dat"));
$l_blk = (int) $l_dat[0];
$l_txn = (int) $l_dat[1];

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
	$add_txs = array();
	$break = false;
	$n_txn = 0;
	foreach ($block['tx'] as $key => $txid) {
	  $tx = $daemon->getrawtransaction($txid, 1);
	  if (empty($tx)) {
        $error = "error: could not get tx $txid";
	    $break = true;
		break;
	  } else {
	    $n_txn++;
		if (isset($tx['limit'])) {
		  $clean_val = remove_ep($tx['vin'][0]['value']);
		  if (isset($add_txs[$tx['vin'][0]['address']])) {
		    $add_txs[$tx['vin'][0]['address']] .= "$txid:$clean_val:2\n";
		  } else {
		    $add_txs[$tx['vin'][0]['address']] = "$txid:$clean_val:2\n";
		  }
		} else {
	      foreach ($tx['vin'] as $k => $input) {
		    $clean_val = remove_ep($input['value']);
		    if (isset($add_txs[$input['address']])) {
		      $add_txs[$input['address']] .= "$txid:$clean_val:0\n";
		    } else {
		      $add_txs[$input['address']] = "$txid:$clean_val:0\n";
		    }
	      }
	      foreach ($tx['vout'] as $k => $output) {
		    $clean_val = remove_ep($output['value']);
		    if (isset($add_txs[$output['address']])) {
		      $add_txs[$output['address']] .= "$txid:$clean_val:1\n";
		    } else {
		      $add_txs[$output['address']] = "$txid:$clean_val:1\n";
		    }
	      }
		}
	  }
	}
	if ($break === true) {
	  break;
	} else {
	  foreach ($add_txs as $address => $value) {
	    $sub_dir = substr($address, 1, 2);
		if (!file_exists(dirname(__FILE__)."/../db/txs/$sub_dir/")) {
          mkdir(dirname(__FILE__)."/../db/txs/$sub_dir/", 0700);
		}
	    file_put_contents(dirname(__FILE__)."/../db/txs/$sub_dir/$address", $value, FILE_APPEND);
	  }
      put_block_hash($l_blk, $block_hash);
	  $l_txn += $n_txn;
	  $l_blk++;
	}
  } elseif ($last_hash === $block_hash) {
    continue;
  } else {
    put_block_hash($l_blk, $null_64bstr);
	$l_blk--;
  }
}

file_put_contents(dirname(__FILE__)."/../db/last_dat", "$l_blk:$l_txn");

if (empty($error)) {
  echo 'done';
} else {
  echo $error;
}
?>
