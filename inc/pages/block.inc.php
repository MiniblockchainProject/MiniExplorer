<?php
if (isset($_GET['b'])) {
  $bnumb = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['b']));
  $bhash = $_SESSION[$rpc_client]->getblockhash(abs($bnumb));
  if (!empty($bhash)) {
    $block = $_SESSION[$rpc_client]->getblock($bhash);
	$chain_info = "<sup class='main_info'>[main chain]</sup>";
  } else {
	$break = true;
  }
} elseif (isset($_GET['block'])) {
  $bhash = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['block']));
  $block = $_SESSION[$rpc_client]->getblock($bhash);
  if (!empty($block)) {
    $chash = $_SESSION[$rpc_client]->getblockhash($block['height']);
	if ($bhash === $chash) {
	  $chain_info = "<sup class='main_info'>[main chain]</sup>";
	} else {
	  $chain_info = "<sup class='orphan_info'>[orphan chain]</sup>";
	}
  } else {
	$break = true;
  }
}

if (!isset($break) || rpc_error_check(false)) {

	echo "<h1><a href='./?b=".$block['height']."'>Block #".$block['height']."</a> $chain_info</h1>";
	echo "<div class='row-fluid'><div class='span5'>";
	echo "<h3>Summary:</h3><table class='table table-striped table-condensed'>";
	echo "<tr><td><b>Version:</b></td><td>".$block['version']."</td></tr>";
	echo "<tr><td><b>Size:</b></td><td>".round($block['size']/1024, 2)." kB</td></tr>";
	echo "<tr><td><b>Transactions:</b></td><td>".count($block['tx'])."</td></tr>";
	echo "<tr><td><b>Confirmations:</b></td><td>".$block['confirmations']."</td></tr>";
	echo "<tr><td><b>Difficulty:</b></td><td>".$block['difficulty']."</td></tr>";
	echo "<tr><td><b>Nonce:</b></td><td>".$block['nonce']."</td></tr>";
	echo "<tr><td><b>Timestamp:</b></td><td>".date("Y-m-d h:i:s A e", $block['time'])."</td></tr>";
	echo "</table>";

	echo '</div><div class="span7">';
	echo '<h3>Hashes:</h3><table class="table table-striped">';
	if (isset($block['previousblockhash'])) {
	  echo "<tr><td><b>Previous Block:</b></td><td><a href='./?block=".
	  $block['previousblockhash']."'>".$block['previousblockhash']."</a></td></tr>";
	}
	if (isset($block['nextblockhash'])) {
	  echo "<tr><td><b>Next Block:</b></td><td><a href='./?block=".
	  $block['nextblockhash']."'>".$block['nextblockhash']."</a></td></tr>";
	}
	echo "<tr><td><b>Block Hash:</b></td><td><a href='./?rawblock=".$block['hash']."'>".$block['hash']."</a></td></tr>";
	echo "<tr><td><b>Master Hash:</b></td><td>".$block['accountroot']."</td></tr>";
	echo "<tr><td><b>Merkle Root:</b></td><td>".$block['merkleroot']."</td></tr>";
	echo '</table></div></div>';

	echo "<h3>Transactions:</h3>
	<table class='table table-striped'>";

	foreach ($block['tx'] as $key => $txid) {
	  $tx = $_SESSION[$rpc_client]->getrawtransaction($txid, 1);
	  
	  if (!empty($tx)) {
		$in_total = 0;
		$out_total = 0;
		
		echo "<tr><td colspan='2'><a href='./?tx=$txid'>$txid</a></td><td colspan='2' style='text-align:right'>".
			 date("Y-m-d h:i:s A e", $tx['time'])."</td></tr><tr><td style='vertical-align:middle'>";
		
	    if (count($tx['vin']) > 0) {
		  foreach ($tx['vin'] as $key => $value) {
		    $clean_val = remove_ep($value['value']);
		    $in_total = bcadd($in_total, $clean_val);
		    if ($value['coinbase'] == true) {
			  echo "<a href='./?address=".$value['address']."'>TheCoinbaseAccount".
				   "</a>:&nbsp;<span class='sad_txt'>$clean_val</span>&nbsp;$curr_code&nbsp;(block&nbsp;reward)<br />";
		    } else {
			  echo "<a href='./?address=".$value['address']."'>".$value['address'].
				   "</a>:&nbsp;<span class='sad_txt'>$clean_val</span>&nbsp;$curr_code<br />";
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
			echo "<a href='./?address=".$value['address']."'>".$value['address'].
				 "</a>:&nbsp;<span class='happy_txt'>$clean_val</span>&nbsp;$curr_code<br />";
		  }
		}
		
		echo "<br /></td><td style='vertical-align:middle'>
		<b>Total:</b>&nbsp;$out_total&nbsp;$curr_code<br />
		<b>Fee:</b>&nbsp;".(($in_total===0)?'0':bcsub($in_total, $out_total)).
		"&nbsp;$curr_code<br /><br /></td></tr>";
	  }
	}
	
	echo "</table>";
} else {
  echo "<p>The specified block could not be found.</p>";
}
?>