<h2><?= __('Metapic settings page', 'metapic') ?></h2>
<form action="options.php" method="post">
	<?php settings_fields('metapic_options'); ?>
	<h3 class="title"><?= __('My settings', 'metapic') ?></h3>

	<p><?= __('Metapic has been activated on your site by the network administrator.<br/>You have an active account and you can use the service to tag images and create collages.', 'metapic') ?></p>
	<p><?= __('The account is connected to the following email address:', 'metapic') ?> <strong><?= $mtpcEmail ?></strong></p>
	<p><label for="mtpc-autolink-default">
			<input type="hidden" name="metapic_options[mtpc_deeplink_auto_default]" value="0">
			<input type="checkbox" <?php checked(get_option("mtpc_deeplink_auto_default")) ?> value="1" id="mtpc-autolink-default" name="metapic_options[mtpc_deeplink_auto_default]">
			<?= __('Activate auto linking by default', 'metapic') ?></label></p>
	<p class="submit">
		<input type="submit" value="<?php esc_attr_e('Save Changes'); ?>"
	                         class="button button-primary" id="submit" name="submit" style="margin-right:10px;">
		<input type="submit" value="<?php esc_attr_e($submitText, 'metapic'); ?>"
	                         class="button" id="submit" name="deactivate"></p>
</form>