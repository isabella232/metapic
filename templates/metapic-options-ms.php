<?php
/* @var WP_MTPC $this */

$this->hasAccount = get_option("mtpc_active_account");
$this->blogInfo = get_bloginfo("admin_email");
$this->mtpcEmail = get_option("mtpc_email");
/* @ WP_User $this->wp_user */
$this->wp_user = wp_get_current_user();
$this->user = $this->client->getUserByEmail($this->mtpcEmail);
$this->validUser = isset($this->user["id"]);
$this->submitText = ($this->hasAccount && $this->user) ? __("Deactivate account", "metapic") : __("Create account", "metapic");
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