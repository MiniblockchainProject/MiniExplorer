<div style="display:inline-block">
  <h1>Rich List</h1>

  <p>A list of the top 1000 richest addresses. Last updated: 
  <?php safe_echo(date("Y-m-d H:i:s T", filemtime('./db/rich_list'))); ?></p><br />
</div>

<?php
$p = (empty($_GET['p'])) ? 1 : (int)$_GET['p'];
if ($p < 1) { $p = 1; }
$start_index = ($p-1) * 100;
$start_page = $p - 2;

if ($start_page < 1) {
  $start_page = 1;
}

$end_page = $start_page + 4;
if ($end_page > 10) {
  $end_page = 10;
  $start_page = $end_page - 4;
  if ($start_page < 1) {
    $start_page = 1;
  }
}

$p_active = ($p == 1) ? " class='active'" : '';
$nav_html = "<li$p_active><a href='./?page=richlist&amp;p=1'>First</a></li>";

for ($i=$start_page;$i<=$end_page;$i++) {
  $p_active = ($i == $p) ? " class='active'" : '';
  $nav_html .= "<li$p_active><a href='./?page=richlist&amp;p=$i'>$i</a></li>";
}

$p_active = ($p == 10) ? " class='active'" : '';
$nav_html .= "<li$p_active><a href='./?page=richlist&amp;p=10'>Last</a></li>";

echo "<div class='pagination float_right' style='margin-top:40px'><ul>$nav_html</ul></div>";
?>

<table class="table table-striped">
<tr>
	<th>Address</th>
	<th>Balance</th>
</tr>

<?php
$rl_file_str = trim(file_get_contents('./db/rich_list', "r+"));
$rich_list = explode("\n", $rl_file_str);
$icount = 0;

for ($i=$start_index; $icount<100; $i++) {
	$icount++;
	$addr_stats = explode(":", $rich_list[$i]);

	echo "<tr><td><a href='./?address=".$addr_stats[0]."'>".
	$addr_stats[0]."</a></td><td>".
	pretty_format(int_to_coins($addr_stats[1])).
	" XCN</td></tr>";
}
?>

</table>