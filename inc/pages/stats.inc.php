<?php
$balance = $_SESSION[$rpc_client]->listbalances(1, array($cb_address));
$mining_info = $_SESSION[$rpc_client]->getmininginfo();
$tx_stats = $_SESSION[$rpc_client]->gettxoutsetinfo();

$now_time = date("Y-m-d H:i:s e");
$start_time = date("Y-m-d H:i:s e", $launch_time);
$time_diff = get_time_difference($start_time, $now_time);
$coin_supply = remove_ep($tx_stats['total_amount']);
$cb_balance = remove_ep($balance[0]['balance']);
$frac_reman = bcdiv($cb_balance, $total_coin);
$block_rwrd = bcmul($first_reward, $frac_reman);
$l_dat = explode(':', file_get_contents("./db/last_dat"));
?>

<h1>Statistics</h1><br />

<table class="table table-striped">
<tr><td>
  <b>Coin supply:</b></td><td>
  <?php echo float_format($coin_supply, 6)." $curr_code"; ?>
</td></tr><tr><td>
  <b>Unmined coins:</b></td><td>
  <?php echo float_format($cb_balance, 4).' '.$curr_code; ?>
</td></tr><tr><td>
  <b>Block Reward:</b></td><td>
  <?php echo $block_rwrd.' '.$curr_code; ?>
</td></tr><tr><td>
  <b>Block Count:</b></td><td>
  <?php echo $mining_info['blocks']; ?>
</td></tr><tr><td>
  <b>No. Transactions:</b></td><td>
  <?php echo $l_dat[1]; ?>
</td></tr><tr><td>
  <b>Active Addresses:</b></td><td>
  <?php echo $tx_stats['accounts']; ?>
</td></tr><tr><td>
  <b>Difficulty:</b></td><td>
  <?php echo float_format($mining_info['difficulty'], 6); ?>
</td></tr><tr><td>
  <b>Hash Rate:</b></td><td>
  <?php echo float_format(bcdiv($mining_info['networkhashps'], '1000000000'), 4).' GH/s'; ?>
</td></tr><tr><td>
  <b>Run Time:</b></td><td>
  <?php echo round($time_diff['seconds']/60/60/24, 2).' days'; ?>
</td></tr>
</table>
