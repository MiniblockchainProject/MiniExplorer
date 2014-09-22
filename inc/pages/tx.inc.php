<?php
$tx_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['tx']));
$tx = $_SESSION[$rpc_client]->getrawtransaction($tx_id, 1);

if (rpc_error_check(false)) {
	$input_str = '';
	$output_str = '';
	$total_in = 0;
	$total_out = 0;
	
	if (count($tx['vin']) > 0) {
	  foreach ($tx['vin'] as $key => $value) {
	    $clean_val = remove_ep($value['value']);
	    $total_in = bcadd($total_in, $clean_val);
	    if ($value['coinbase'] == true) {
		  $input_str .= "<a href='./?address=".$value['address']."'>TheCoinbaseAccount".
					    "</a> &rarr; <span class='sad_txt'>$clean_val</span> $curr_code (block reward)<br />";
	    } else {
		  $input_str .= "<a href='./?address=".$value['address']."'>".$value['address'].
					    "</a> &rarr; <span class='sad_txt'>$clean_val</span> $curr_code<br />";
	    }
	  }
	} else {
	  $total_in = 0;
	  $input_str = 'No Inputs (coinbase genesis transaction)<br />';
	}
		
	foreach ($tx['vout'] as $key => $value) {
	  $clean_val = remove_ep($value['value']);
	  $total_out = bcadd($total_out, $clean_val);
	  if (isset($tx['limit'])) {
		$output_str .= "Withdrawal limit of input address updated to: <span class='happy_txt'>".
					   remove_ep($tx['limit'])."</span> $curr_code<br />";
	  } else {
		$output_str .= "<a href='./?address=".$value['address']."'>".$value['address'].
					   "</a> &larr; <span class='happy_txt'>$clean_val</span> $curr_code<br />";
	  }
	}
	
	echo "<h1>Transaction Details</h1><br />";
	echo "<table class='table table-striped table-condensed' style='width:auto;'>";
	
	echo "<tr><td><b>TxID:</b></td><td><a href='./?rawtx=".
		 $tx['txid']."'>".$tx['txid']."</a></td></tr>";
		 
	if (isset($tx['blockhash'])) {
	  echo "<tr><td><b>Block:</b></td><td><a href='./?block=".
	       $tx['blockhash']."'>".$tx['blockhash']."</a></td></tr>";
	} else {
	  echo "<tr><td><b>Block:</b></td><td>not in a block yet</td></tr>";
	}
	
	$tx_time = isset($tx['time']) ? date("Y-m-d h:i A e", $tx['time']) : 'unknown';
	$confirmations = isset($tx['confirmations']) ? $tx['confirmations'] : '0';
	$tx_message = empty($tx['msg']) ? 'none' : safe_str($tx['msg']);
	$tx_fee = ($total_in === 0) ? '0' : bcsub($total_in, $total_out);
	
	echo "<tr><td><b>Time Sent:</b></td><td>$tx_time</td></tr>";
	echo "<tr><td><b>Confirmations:</b></td><td>$confirmations</td></tr>";
	echo "<tr><td><b>Lock Height:</b></td><td>".$tx['lockheight']."</td></tr>";
	echo "<tr><td><b>Total Input:</b></td><td>$total_in $curr_code</td></tr>";
	echo "<tr><td><b>Total Output:</b></td><td>$total_out $curr_code</td></tr>";
	echo "<tr><td><b>Fee:</b></td><td>$tx_fee $curr_code</td></tr>";
	echo "<tr><td><b>Message:</b></td><td>$tx_message</td></tr>";
	echo "</table>";
	
	echo "<h3>Inputs:</h3><p>$input_str</p>";	
	echo "<h3>Outputs:</h3><p>$output_str</p>";
}
?>
