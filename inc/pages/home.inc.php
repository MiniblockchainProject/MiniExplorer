<div id="latest">
  <center><img src="./img/ajax_loader.gif" alt="Loading ..." /></center>
</div>

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