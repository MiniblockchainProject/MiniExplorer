    <noscript>
      <div class="alert alert-error">
        <i class="icon-warning-sign"></i> JavaScript must be enabled for this web wallet to function properly!
      </div>
    </noscript>

    <div class="well warning_well<?php if ($rpc_debug == false || empty($rpc_error)) { echo ' no_display'; } ?>">
      <span id="error_text"><?php if ($rpc_debug) { safe_echo("RPC ERROR: $rpc_error"); } ?></span>
    </div>
