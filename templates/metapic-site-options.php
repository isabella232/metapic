<?php
$autoLinkDefault = get_site_option('mtpc_deeplink_auto_default');
$autoRegDefault = get_site_option('mtpc_registration_auto');
?>
<div class="wrap">
	<h2><?= __('Metapic site settings page', 'metapic')?></h2>

	<form method="post" action="" novalidate="novalidate">
		<?php settings_fields('metapic_site_options'); ?>
		<h3><?php _e('API Settings', 'metapic'); ?></h3>

		<?php if (get_site_option("mtpc_valid_client")): ?>
			<p><?php _e('Welcome,', 'metapic'); ?> <strong><?= get_site_option("mtpc_client_name") ?></strong></p>
		<?php else: ?>
			<p><?php _e('Please enter your credentials below. You should have already received your client credentials from Metapic.<br/>If you haven\'t received your credentials please contact Metapic technical support.', 'metapic'); ?></p>
		<?php endif; ?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="api_key"><?php _e('API Key', 'metapic') ?></label></th>
				<td>
					<input name="api_key" type="text" id="api_key" class="regular-text"
					       value="<?= esc_attr(get_site_option('mtpc_api_key')) ?>"/>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="secret_key"><?php _e('Secret Key', 'metapic') ?></label></th>
				<td>
					<input name="secret_key" type="text" id="secret_key" class="regular-text"
					       value="<?= esc_attr(get_site_option('mtpc_secret_key')) ?>"/>
				</td>


			</tr>
			<tr>
				<th scope="row"><label for="mtpc-autolink-default"><?php _e('Automatic linking', 'metapic') ?></label></th>
				<td>
					<label for="mtpc-autolink-default">
							<input type="hidden" name="mtpc_deeplink_auto_default" value="0">
							<input type="checkbox" <?php checked($autoLinkDefault) ?> value="1" id="mtpc-autolink-default" name="mtpc_deeplink_auto_default">
							<?= __('Activate auto linking by default', 'metapic') ?></label>
				</td>


			</tr>
			<tr>
				<th scope="row"><label for="mtpc-registration-auto"><?php _e('Automatic registration', 'metapic') ?></label></th>
				<td>
					<label for="mtpc-registration-auto">
						<input type="hidden" name="mtpc_registration_auto" value="0">
						<input type="checkbox" <?php checked($autoRegDefault) ?> value="1" id="mtpc-registration-auto" name="mtpc_registration_auto">
						<?= __('Activate automatic registration', 'metapic') ?></label>
				</td>
			</tr>
			<?php if ($debugMode) { ?>
				<tr>
					<th scope="row"><label for="api_url"><?php _e('Api url', 'metapic') ?></label></th>
					<td>
						<input name="API url" type="text" id="api_url" class="regular-text"
						       value="<?= esc_attr(get_site_option('mtpc_api_url')) ?>"/>
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php submit_button(); ?>
	</form>
</div>