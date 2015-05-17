<h1>Raw Transaction</h1>
<br />

<?php
$tx_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['rawtx']));
$raw_tx = $_SESSION[$rpc_client]->getrawtransaction($tx_id, 1);

if (rpc_error_check(false)) {
  echo '<pre>'.json_encode($raw_tx, JSON_PRETTY_PRINT).'</pre>';
}
?>
