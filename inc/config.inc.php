<?php
// change level of php error reporting
$error_level = E_ALL;

// enable/disable rpc error reporting
$rpc_debug = true;

// install directory ('/' if installed at root)
$install_dir = '/';

// website title
$site_name = 'Block Explorer';

// default time zone used by server
$time_zone = 'UTC';

// coin currency code
$curr_code = 'XCN';

// show coinbase tx's on home page
$show_cbtxs = true;

// number of decimal places
$dec_count = 10;

// number of tx's shown per page
$txper_page = 10;

// RPC client name
$rpc_client = 'cryptonited';

// RPC username
$rpc_user = '';

// RPC password
$rpc_pass = '';

// address of coinbase account
$cb_address = 'CGTta3M4t3yXu8uRgkKvaWd2d8DQvDPnpL';

// ignore crap under this line
$inter_prot = (empty($_SERVER['HTTPS'])) ? 'http://' : 'https://';
$base_url = $inter_prot.$_SERVER['HTTP_HOST'].$install_dir;
bcscale($dec_count);
ini_set('display_errors', 1); 
error_reporting($error_level);
date_default_timezone_set($time_zone);
?>
