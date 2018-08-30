<h1 style="display:inline-block">Orphaned Blocks</h1>

<?php
$l_dat = explode(':', file_get_contents("./db/last_dat"));

$p = (empty($_GET['p'])) ? 1 : (int)$_GET['p'];
if ($p < 1) { $p = 1; }
$num_pages = ceil($l_dat[2]/100);
$start_line = ($p-1) * 100;
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

if ($num_pages > 1) {
  $p_active = ($p == 1) ? " class='active'" : '';
  $nav_html = "<li$p_active><a href='./?page=orphaned&amp;p=1'>First</a></li>";
  for ($i=$start_page;$i<=$end_page;$i++) {
    $p_active = ($i == $p) ? " class='active'" : '';
    $nav_html .= "<li$p_active><a href='./?page=orphaned&amp;p=$i'>$i</a></li>";
  }
  $p_active = ($p == $num_pages) ? " class='active'" : '';
  $nav_html .= "<li$p_active><a href='./?page=orphaned&amp;p=$num_pages'>Last</a></li>";
}

if (!empty($nav_html)) {
  echo "<div class='pagination float_right'><ul>$nav_html</ul></div>";
}
?>

<table class="table table-striped">
<tr>
  <th>Number</th>
  <th>Time</th>
  <th>Difficulty</th>
  <th>Nonce</th>
  <th>Transactions</th>
  <th>Size</th>
</tr>

<?php
$ohdb_handle = fopen('./db/ohashes', "r+");

function get_orph_hash($index) {
  global $ohdb_handle;
  fseek($ohdb_handle, 64*$index);
  return fread($ohdb_handle, 64);
}

if ($l_dat[2] == 0) {

  echo "<tr><td colspan='6'>Our node has not yet seen any orphaned blocks.</td></tr>";

} else {

  $items_pp = ($l_dat[2] < 100) ? $l_dat[2] : 100;
  
  for ($i=0; $i<$items_pp; $i++) {

    $orph_hash = get_orph_hash($start_line+$i);
	if (empty($orph_hash)) break;
    $orph_hash = bin2hex(strrev(hex2bin($orph_hash)));
    $block = $_SESSION[$rpc_client]->getblock($orph_hash);
  
    echo "<tr><td><a href='./?block=".$block['hash'].
    "'>".$block['height']."</a></td><td>".
    date("Y-m-d h:i A e", $block['time']).
    "</td><td>".$block['difficulty'].
    "</td><td>".$block['nonce'].
    "</td><td>".count($block['tx']).
    "</td><td>".round($block['size']/1024, 2).
    " kB</td></tr>";
  }
}
?>

</table>
