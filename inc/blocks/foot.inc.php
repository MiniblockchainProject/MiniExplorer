	<hr />
    <div id="footer">
	  <?php
	  $status = 'ok';
	  if ($getinfo['blocks'] !== $getinfo['headers']) {
	    $status = 'not synced';
	  } elseif ($getinfo['testnet'] === true) {
	    $status = 'using testnet';
	  } elseif (!empty($getinfo['errors'])) {
	    if (strpos($getinfo['errors'], 'Warning:') === false) {
	      $status = 'rpc error';
	    }
	  }
	  ?>
      <p>Status: <a href="./?q=getinfo"><?php echo $status; ?></a>
	  | Connections: <a href="./?page=peers"><?php echo $getinfo['connections']; ?></a>
	  <br />&copy; Mini-blockchain Project <?php echo date("Y"); ?></p>
    </div>
