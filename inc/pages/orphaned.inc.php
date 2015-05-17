<h1>Orphaned Blocks</h1>
<br />

<table class="table table-striped">
<tr>
  <th>Number</th>
  <th>Time</th>
  <th>Difficulty</th>
  <th>Nonce</th>
  <th>Transactions</th>
  <th>Size (kB)</th>
</tr>

<?php
$ohdb_handle = fopen('./db/ohashes', "r+");

function get_orph_hash($index) {
  global $ohdb_handle;
  fseek($ohdb_handle, 64*$index);
  return fread($ohdb_handle, 64);
}

$l_dat = explode(':', file_get_contents("./db/last_dat"));

if ($l_dat[2] == 0) {

  echo "<tr><td colspan='6'>Our node has not yet seen any orphaned blocks.</td></tr>";

} else {

  for ($i=0;$i<=$l_dat[2];$i++) {

    $orph_hash = get_orph_hash($i);
    $block[$i] = $_SESSION[$rpc_client]->getblock($orph_hash);
  
    echo "<tr><td><a href='./?block=".$block[$i]['hash'].
    "'>".$block[$i]['height']."</a></td><td>".
    date("Y-m-d h:i A e", $block[$i]['time']).
    "</td><td>".$block[$i]['difficulty'].
    "</td><td>".$block[$i]['nonce'].
    "</td><td>".count($block[$i]['tx']).
    "</td><td>".round($block[$i]['size']/1024, 2).
    "</td></tr>";
  }
}

// TODO: add pagination
?>

</table>

<p><b>NOTE:</b> this is not an exhaustive list of every block orphaned.</p>
