<h3><?= __('Your account', "metapic") ?></h3>
<p><?= sprintf(__('You are currently logged in as: <strong>%s</strong>', 'metapic'), get_option("mtpc_email")) ?></p>
<p><label for="mtpc-autolink-default">
		<input type="hidden" name="metapic_options[mtpc_deeplink_auto_default]" value="0">
		<input type="checkbox" <?php checked($options["mtpc_deeplink_auto_default"]) ?> value="1" id="mtpc-autolink-default" name="metapic_options[mtpc_deeplink_auto_default]">
		<?= __('Activate auto linking by default', 'metapic') ?></label></p>
<p class="submit">
	<?php if (!$this->debugMode): ?><input type="submit" value="<?php esc_attr_e('Save Changes'); ?>"
	                                       class="button button-primary" id="submit" name="submit" style="margin-right:10px;"><?php endif; ?>
	<input type="submit" value="<?= __('Log out', 'metapic') ?>" class="button <?= $loginPrimary ?>" id="logout" name="logout">
</p>