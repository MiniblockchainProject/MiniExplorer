<?php
$tx_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['tx']));
$tx = $_SESSION[$rpc_client]->getrawtransaction($tx_id, 1);

if (rpc_error_check(false)) {
	$input_str = '';
	$output_str = '';
	$total_in = 0;
	$total_out = 0;
	
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
	echo "<p><b>TxID:</b> <a href='./?rawtx=".
		 $tx['txid']."'>".$tx['txid']."</a></p>";
		 
	if (isset($tx['blockhash'])) {
	  echo "<p><b>Block:</b> <a href='./?block=".$tx['blockhash']."'>".$tx['blockhash']."</a></p>";
	} else {
	  echo "<p><b>Block:</b> not in a block yet</p>";
	}
	echo "<p><b>Time Sent:</b> ".(isset($tx['time'])?date("Y-m-d h:i A e", $tx['time']):'unknown')."</p>";
	echo "<p><b>Confirmations:</b> ".(isset($tx['confirmations'])?$tx['confirmations']:'0')."</p>";
	echo "<p><b>Lock Height:</b> ".$tx['lockheight']."</p>";
	echo "<p><b>Total Input:</b> $total_in $curr_code</p>";
	echo "<p><b>Total Output:</b> $total_out $curr_code</p>";
	echo "<p><b>Fee:</b> ".bcsub($total_in, $total_out)." $curr_code</p>";
	echo "<p><b>Message:</b> ".(empty($tx['msg'])?'none':safe_str($tx['msg']))."</p>";
	
	echo "<h3>Inputs:</h3><p>$input_str</p>";	
	echo "<h3>Outputs:</h3><p>$output_str</p>";

}
?>