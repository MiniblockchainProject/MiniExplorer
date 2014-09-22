<?php
if (isset($_GET['q'])) {
  $qstr = preg_replace("/[^a-z0-9]/i", '', $_GET['q']);
  $qlen = strlen($qstr);
  if ($qlen >= 64) {
    $tx = $_SESSION[$rpc_client]->getrawtransaction($qstr);
    if (!empty($tx) && empty($_SESSION[$rpc_client]->error)) {
	  redirect("./?tx=$qstr");
	} else {
	  redirect("./?block=$qstr");
	}
  } else {
    if (is_numeric($qstr)) {
	  redirect("./?b=$qstr");
	} else {
	  redirect("./?address=$qstr");
	}
  }
} else {
?>

<h1>Search Blockchain</h1><br />

<form name="search_form" class="form-horizontal" method="get" action="./">
  <h3>Find Address <small>input a valid address</small></h3>
  <input type="text" class="long_input" name="address" value="" maxlength="34" />
  <input type="submit" class="btn" value="Search" />
</form>

<form name="search_form" class="form-horizontal" method="get" action="./">
  <h3>Find Transaction <small>input a valid txid</small></h3>
  <input type="text" class="long_input" name="tx" value="" maxlength="64" />
  <input type="submit" class="btn" value="Search" />
</form>
  
<form name="search_form" class="form-horizontal" method="get" action="./">
  <h3>Find Block <small>input valid block hash</small></h3>
  <input type="text" class="long_input" name="block" value="" maxlength="64" />
  <input type="submit" class="btn" value="Search" />
</form>

<?php
}
?>