<?php
$tx_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['rawtx']));
$raw_tx = $_SESSION[$rpc_client]->getrawtransaction($tx_id, 1);

if (rpc_error_check(false)) {
  header('Content-Type: application/json');
  echo json_encode($raw_tx);
}
?>