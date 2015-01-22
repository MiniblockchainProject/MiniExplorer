<?php
$address = preg_replace("/[^a-z0-9]/i", '', $_GET['address']);
$confs = empty($_GET['confs']) ? 1 : (int)$_GET['confs'];
$conf_txt = "($confs or more confs)";

$ainfo = $_SESSION[$rpc_client]->listbalances($confs, array($address));
$tx_memp = $_SESSION[$rpc_client]->getrawmempool();

$sub_dir = substr($address, 1, 2);
$ful_dir = "./db/txs/$sub_dir/$address";

$tx_count = count_lines($ful_dir);
$txdb_handle = new SplFileObject($ful_dir);

$filter = empty($_GET['filter']) ? 0 : (int) $_GET['filter'];
$sort_meth = empty($_GET['sort']) ? 0 : (int) $_GET['sort'];
$l_dat = explode(':', file_get_contents("./db/last_dat"));

$l_blk = (int) $l_dat[0];
$l_txn = (int) $l_dat[1];
$tx_set = array();

function update_txset($txid, $append=1) {
  global $address;
  global $tx_set;
  global $rpc_client;
  $tx = $_SESSION[$rpc_client]->getrawtransaction($txid, 1);
  if (isset($tx['limit'])) {
	if ($tx['vin'][0]['address']) {
	  $tx['amount'] = remove_ep($tx['vin'][0]['value']);
	  $tx['type'] = '2';
	  if ($append) {
	    $tx_set[] = $tx;
	  } else {
	    array_unshift($tx_set, $tx);
	  }
	  return true;
	}
  } elseif (!empty($tx)) {
	foreach ($tx['vin'] as $k => $input) {
	  if ($input['address'] === $address) {
		$tx['amount'] = remove_ep($input['value']);
		$tx['type'] = '0';
	    if ($append) {
	      $tx_set[] = $tx;
	    } else {
	      array_unshift($tx_set, $tx);
	    }
		return true;
	  }
	}
	foreach ($tx['vout'] as $k => $output) {
	  if ($output['address'] === $address) {
		$tx['amount'] = remove_ep($output['value']);
		$tx['type'] = '1';
	    if ($append) {
	      $tx_set[] = $tx;
	    } else {
	      array_unshift($tx_set, $tx);
	    }
		return true;
	  }
	}
  }
  return false;
}

if (rpc_error_check(false)) {

  $p = (empty($_GET['p'])) ? 1 : (int)$_GET['p'];
  if ($p < 1) { $p = 1; }
  $start_line = ($p-1) * $txper_page;
  $txset_size = 0;
  
  if ($sort_meth == 0) {
	$start_line = $tx_count - $start_line - 1;
  }
	
  for ($i=$start_line;;) {
  
    try {
      $txdb_handle->seek($i);
	  $line = trim($txdb_handle->current());
	} catch (Exception $e) {
	  break;
	}
	
	if (empty($line)) { continue; }
	$tx_arr = explode(':', $line);
	
	if (($filter == 1) && ($tx_arr[2] != 0)) { continue; }
	if (($filter == 2) && ($tx_arr[2] != 1)) { continue; }

    if (update_txset($tx_arr[0])) {
	  $txset_size++;
	}
	
	if ($txset_size >= $txper_page) { break; }
    if ($sort_meth == 0) { $i--; } else { $i++; }
  }
  
  if (($getinfo['blocks'] - $l_blk) < 10) {
    for ($i=$l_blk;$i<$getinfo['blocks'];$i++) {
      $block_hash = $_SESSION[$rpc_client]->getblockhash($i+1);
      $block = $_SESSION[$rpc_client]->getblock($block_hash);
	  if (!empty($block['tx'])) {
        foreach ($block['tx'] as $key => $txid) {
	      if (update_txset($txid, $sort_meth)) {
		    $tx_count++;
		  }
	    }
	  }
    }
  } else {
    die('error: /cron/parse_txs.php cron job is not running');
  }
  
  foreach ($tx_memp as $key => $txid) {
    if (update_txset($txid, $sort_meth)) {
	  $tx_count++;
	}
  }
  
  $num_pages = ceil($tx_count/$txper_page);
  $start_page = $p - 2;
  if ($start_page < 1) {
	$start_page = 1;
  }
	
  $end_page = $start_page + 4;
  if ($end_page > $num_pages) {
	$end_page = $num_pages;
	$start_page = $end_page - 4;
	if ($start_page < 1) {
	  $start_page = 1;
	}
  }
  
  $ex_vars = '';
  if (!empty($sort_meth)) {
    $ex_vars .= "&amp;sort=$sort_meth";
  }
  if (!empty($filter)) {
    $ex_vars .= "&amp;filter=$filter";
  }

  if ($num_pages > 1) {
	$p_active = ($p == 1) ? " class='active'" : '';
	$nav_html = "<li$p_active><a href='./?address=$address&amp;p=1$ex_vars'>First</a></li>";
    for ($i=$start_page;$i<=$end_page;$i++) {
	  $p_active = ($i == $p) ? " class='active'" : '';
      $nav_html .= "<li$p_active><a href='./?address=$address&amp;p=$i$ex_vars'>$i</a></li>";
    }
	$p_active = ($p == $num_pages) ? " class='active'" : '';
	$nav_html .= "<li$p_active><a href='./?address=$address&amp;p=$num_pages$ex_vars'>Last</a></li>";
  }
  
  $clean_bal = remove_ep($ainfo[0]['balance']);
  $clean_lim = remove_ep($ainfo[0]['limit']);
  $clean_fli = remove_ep($ainfo[0]['futurelimit']);
  
  if (clean_number($clean_bal) === '0') {
    $last_used = 'unknown';
  } else {
	$last_used = 'block '.$ainfo[0]['age'];
  }
?>

<div class='row-fluid'>
  <div class='span6'>
    <h1>Address Details</h1><br />
	<table class='table table-striped table-condensed'>
      <tr><td><b>Address:</b></td><td><?php echo $ainfo[0]['address']; ?></td></tr>
      <tr><td><b>Balance:</b></td><td><?php echo "$clean_bal $curr_code $conf_txt"; ?></td></tr>
      <tr><td><b>Withdrawal Limit:</b></td><td><?php echo "$clean_lim $curr_code"; ?></td></tr>
      <tr><td><b>Pending Limit:</b></td><td><?php echo "$clean_fli $curr_code"; ?></td></tr>
      <tr><td><b>Transactions:</b></td><td><?php echo $tx_count; ?></td></tr>
      <tr><td><b>Last Used:</b></td><td><?php echo $last_used; ?></td></tr>
	</table>
  </div>
  <div class='span6'>
    <div id="qrbox" class="well"><div id="qrcode"></div></div>
  </div>
</div>

<?php
if (!empty($nav_html)) {
  echo "<div class='pagination float_right'><ul>$nav_html</ul></div>";
}
?>

<form id="txlist_form" method="get" action="./">
  <input type="hidden" name="address" value="<?php echo $address; ?>" />
  <select name='filter' id="filter_select">
	<?php $sel_txt = ($filter == 0) ? ' selected="selected"' : ''; ?>
	<option value="0"<?php echo $sel_txt; ?>>all</option>
	<?php $sel_txt = ($filter == 1) ? ' selected="selected"' : ''; ?>
	<option value="1"<?php echo $sel_txt; ?>>sent</option>
	<?php $sel_txt = ($filter == 2) ? ' selected="selected"' : ''; ?>
	<option value="2"<?php echo $sel_txt; ?>>received</option>
  </select>
  <select name='sort' id="sort_select">
	<?php $sel_txt = ($sort_meth == 0) ? ' selected="selected"' : ''; ?>
	<option value="0"<?php echo $sel_txt; ?>>newest first</option>
	<?php $sel_txt = ($sort_meth == 1) ? ' selected="selected"' : ''; ?>
	<option value="1"<?php echo $sel_txt; ?>>oldest first</option>
  </select>
</form>

<script language="JavaScript">
var get_add = '<?php echo $address; ?>';

function draw_qrcode(address) {
  $('#qrcode').html('');
  $('#qrcode').qrcode('cryptonite:'+address);
  $('#qrbox').css('display', 'inline-block');
}

$(document).ready(function() {
  draw_qrcode(get_add);
  $('select').on('change', function() {
    $('#txlist_form').submit();
  });
});
</script>

<?php
  echo "<h3>Transactions:</h3>";
  
  if (empty($tx_set)) {
  
    echo "<p>No transactions are associated with this address yet.</p>";
	
  } else {
  
	echo "<table class='table table-striped'>";
	
	foreach ($tx_set as $tx_key => $tx) {
	  
	  $txid = $tx['txid'];
	  $amount = $tx['amount'];
	  $type = $tx['type'];
	  
	  $in_total = 0;
	  $out_total = 0;
	  
	  $confirmations = empty($tx['confirmations']) ? '0' : $tx['confirmations'];
	  $tx_time = empty($tx['time']) ? date("Y-m-d h:i:s A e") : date("Y-m-d h:i:s A e", $tx['time']);
		
	  echo "<tr><td colspan='2'><a href='./?tx=$txid'>$txid</a></td><td colspan='2' style=".
		   "'text-align:right'>$tx_time</td></tr><tr><td style='vertical-align:middle'>";
		
	  if (count($tx['vin']) > 0) {
		foreach ($tx['vin'] as $key => $value) {
		  $clean_val = remove_ep($value['value']);
		  $in_total = bcadd($in_total, $clean_val);
		  if ($value['coinbase'] == true) {
			echo "<a href='./?address=".$value['address']."'>TheCoinbaseAccount".
				 "</a>:&nbsp;<span class='sad_txt'>$clean_val</span>&nbsp;".
				 "$curr_code&nbsp;(block&nbsp;reward)<br />";
		  } else {
			if ($value['address'] == $address) {
			  $address_val = "<b>$address</b>";
			} else {
			  $address_val = $value['address'];
			}
			echo "<a href='./?address=".$value['address']."'>$address_val</a>:".
				 "&nbsp;<span class='sad_txt'>$clean_val</span>&nbsp;$curr_code<br />";
		  }
		}
	  } else {
		$in_total = 0;
		echo 'No Inputs (coinbase genesis transaction)<br />';
	  }
		
	  echo "<br /></td><td style='vertical-align:middle'>
	  <i class='icon-arrow-right'></i><br /><br />
	  </td><td style='vertical-align:middle'>";
		
	  foreach ($tx['vout'] as $key => $value) {
		$clean_val = remove_ep($value['value']);
		$out_total = bcadd($out_total, $clean_val);
		if (isset($tx['limit'])) {
		  echo "Withdrawal limit of input address updated to: <span class='happy_txt'>".
				remove_ep($tx['limit'])."</span>&nbsp;$curr_code<br />";
		} elseif ($in_total === 0) {
		  echo "<a href='./?address=".$value['address']."'>TheCoinbaseAccount".
				 "</a>:&nbsp;<span class='sad_txt'>$clean_val</span>&nbsp;$curr_code<br />";
		} else {
		  if ($value['address'] == $address) {
			$address_val = "<b>$address</b>";
		  } else {
			$address_val = $value['address'];
		  }
		  echo "<a href='./?address=".$value['address']."'>$address_val</a>:".
			   "&nbsp;<span class='happy_txt'>$clean_val</span>&nbsp;$curr_code<br />";
		}
	  }
		
	  if ($type == 1) {
 		$class = 'btn-success';
	  } else {
		$class = 'btn-danger';
		$amount = '-'.$amount;
	  }
		
	  echo "<br /></td><td style='vertical-align:middle;text-align:center;'>".
	  "<button class='btn btn-small $class'>$amount&nbsp;$curr_code</button><br />".
	  "<small>$confirmations confirmations</small><br /><br /></td></tr>";
	}
	
	echo "</table>";
  }
}
?>
