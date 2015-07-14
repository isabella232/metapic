<div class="wrap">
	<h2>Metapic site settings page</h2>

	<form method="post" action="" novalidate="novalidate">
		<?php settings_fields('metapic_site_options'); ?>
		<h3><?php _e('API Settings', 'metapic'); ?></h3>

		<p><?php _e('Please enter your credentials below. You should have already received your client credentials from Metapic.<br/>If you haven\'t received your credentials please contact Metapic technical support.', 'metapic'); ?></p>
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