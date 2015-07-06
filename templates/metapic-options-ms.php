<?php
/* @var WP_MTPC $this */

$this->hasAccount = get_option("mtpc_active_account");
$this->blogInfo = get_bloginfo("admin_email");
$this->user = $this->client->getUserByEmail($this->blogInfo);
$this->validUser = isset($user["id"]);
$this->submitText = ($this->hasAccount && $this->user) ? "Deactivate account" : "Create account";

?>
<div class="wrap">
		<?php if (!$this->hasAccount && $this->user) {
			$this->getTemplate("ms-options/reactivate");
		}
		else if ($this->hasAccount && $this->user) {
			$this->getTemplate("ms-options/my-account");
		}
		else {
			$this->getTemplate("ms-options/new");
		} ?>
</div>