<h1>Raw Block</h1>
<br />

<?php
$block_id = preg_replace("/[^a-f0-9]/", '', strtolower($_GET['rawblock']));
$raw_block = $_SESSION[$rpc_client]->getblock($block_id);

if (rpc_error_check(false)) {
  echo '<pre>'.json_encode($raw_block, JSON_PRETTY_PRINT).'</pre>';
}
?>
