<div id="latest">
  <center><img src="./img/ajax_loader.gif" alt="Loading ..." /></center>
</div>

<h3>Other Pages</h3>

<p>
  <a href="./?page=richlist">Rich List</a> - A list of the top 1000 richest addresses<br />
  <a href="./?page=orphaned">Orphaned Blocks</a> - A list of valid blocks not in the main chain<br />
  <a href="./?page=mempool">Memory Pool</a> - A list of transactions currently in our memory pool<br />
  <a href="./?page=peers">Connected Peers</a> - A list of nodes currently connected to our node
</p>

<script language="JavaScript">
function handle_update(response) {
  $('#latest').fadeOut(500, function() {
    $('#latest').html(response);
    $('#latest').fadeIn(500);
  });
}

function update_page() {
  ajax_get('./inc/pages/jobs/latest.inc.php', handle_update, '');
}

$(document).ready(function() {
  update_timer = setInterval(update_page, 60000);
  update_page();
});
</script>
