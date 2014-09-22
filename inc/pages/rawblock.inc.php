<?php
$block_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['rawblock']));
$raw_block = $_SESSION[$rpc_client]->getblock($block_id);

if (rpc_error_check(false)) {
  header('Content-Type: application/json');
  echo json_encode($raw_block);
}
?>