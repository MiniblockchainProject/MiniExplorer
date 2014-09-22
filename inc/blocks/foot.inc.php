	<hr />
    <div id="footer">
	  <?php
	  $status = 'ok';
	  if ($getinfo['blocks'] !== $getinfo['headers']) {
	    $status = 'not synced';
	  } elseif ($getinfo['testnet'] === true) {
	    $status = 'using testnet';
	  } elseif (!empty($getinfo['errors'])) {
	    $status = 'rpc error';
	  }
	  ?>
      <p>Status: <?php echo $status; ?> | Connections: <?php echo $getinfo['connections']; ?>
	  <br />&copy; Mini-blockchain Project <?php echo date("Y"); ?></p>
    </div>