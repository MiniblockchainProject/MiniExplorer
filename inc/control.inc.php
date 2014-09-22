<?php
if (isset($index_call)) {
  if (!empty($page)) {
    if (file_exists("./inc/pages/$page.inc.php")) {
      require_once("./inc/pages/$page.inc.php");
    } else {
	  echo "<p>The requested page was not found, sorry! :(</p>";  
    }
  } else {
    require_once('./inc/pages/home.inc.php');
  }
} else {
  echo "error: invalid page access";
}
?>
