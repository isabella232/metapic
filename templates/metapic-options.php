<?php
$activeAccount = get_option("mtpc_active_account");
?>
<div class="wrap">
	<h2><?= __('Metapic settings page', 'metapic')?></h2>
	<form action="options.php" method="post">
		<?php settings_fields('metapic_options'); ?>
		<?php do_settings_sections('plugin'); ?>
		<input type="hidden" name="mtpc_action" value="login"/>

		<p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button button-primary" id="submit" name="submit"></p>
	</form>
</div>