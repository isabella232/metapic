<?php
/* @var WP_MTPC $this */

$this->hasAccount = get_option("mtpc_active_account");
$this->submitText = ($this->hasAccount) ? "Deactivate account" : "Create account";
$this->blogInfo = get_bloginfo("admin_email");
$this->user = $this->client->getUserByEmail($this->blogInfo);
$this->validUser = isset($user["id"]);
?>
<div class="wrap">
		<?php if (!$this->hasAccount && $this->user) {
			$this->getTemplate("ms-options/reactivate");
		} else {
			$this->getTemplate("ms-options/new");
		} ?>
</div>