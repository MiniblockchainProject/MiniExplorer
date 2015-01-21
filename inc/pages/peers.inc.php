<h1>Connected Peers</h1>
<br />

<table class="table table-striped table-condensed">
<tr>
  <th>IP Address</th>
  <th>Port</th>
  <th>Services</th>
  <th>Version</th>
  <th>Connected</th>
</tr>

<?php
$peers = $_SESSION[$rpc_client]->getpeerinfo();

if (empty($peers)) {
  echo "<tr><td colspan='5'>There are currently 0 connected peers.</td></tr>";
} else {
	foreach ($peers as $key => $value) {
      $ip_port = explode(':', $value["addr"]);
	  echo "<tr><td>".$ip_port[0]."</td>".
		   "<td>".$ip_port[1]."</td><td>".
		   $value["services"]."</td><td>".
		   $value["version"]."</td><td>".
		   date("Y-m-d h:i:s A e",
           $value["conntime"])."</td></tr>";
	}
}
?>
</table>
