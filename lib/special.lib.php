<?php
function rpc_error_check($die=true) {
  global $rpc_debug, $rpc_client;
  if (!empty($_SESSION[$rpc_client]->error)) {
    if ($rpc_debug === true) {
	  if ($die) {
        die($_SESSION[$rpc_client]->error);
	  } else {
	    echo $_SESSION[$rpc_client]->error;
		return false;
	  }
    } else {
	  if ($die) {
        die('rpc error');
	  } else {
	    echo 'rpc error';
		return false;
	  }
    }
  } else {
    return true;
  }
}

function post_get_check() {
  $_POST = clean_form_input($_POST);
  $_GET = clean_form_input($_GET);
  if (empty($_POST) && empty($_GET)) {
    return false;
  } else {
    return true;
  }
}

function update_config($new_config) {

  try {
    $file_targ = dirname(__FILE__).'/../inc/config.inc.php';
    $config_file = file_get_contents($file_targ, true);
	$config_file = str_replace("\r\n", "\n", $config_file);
    $config_array = explode("\n", $config_file);

    foreach ($new_config as $key1 => $value1) {
      foreach ($config_array as $key2 => $value2) {
	    if (strpos($value2, '$'.$key1.' = ') !== false) {
	      $new_val = str_replace("'", "\'", $value1);
	      if (is_string($GLOBALS[$key1])) {
	        $new_val = "'$new_val'";
	      }
	      $config_array[$key2] = '$'.$key1.' = '.$new_val.';';
	    }
	  }
    }

    $config_file = implode("\r\n", $config_array);
    if (file_put_contents($file_targ, $config_file)) {
	  return true;
	} else {
	  return false;
	}

  } catch (Exception $e) {
    return false;
  }
}
?>