<?php if ( isset( $_GET['updated'] ) ) {
	?><div id="message" class="updated notice is-dismissible"><p><?php _e( 'Options saved.' ) ?></p></div><?php
} ?>
<div class="wrap">
	<h2>Metapic site settings page</h2>
	<form method="post" action="" novalidate="novalidate">
		<?php wp_nonce_field( 'siteoptions' ); ?>
		<h3><?php _e( 'API Settings' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="api_key"><?php _e( 'API Key' ) ?></label></th>
				<td>
					<input name="api_key" type="text" id="api_key" class="regular-text" value="<?php echo esc_attr( get_site_option( 'mtpc_api_key' ) ) ?>" />
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="secret_key"><?php _e( 'Secret Key' ) ?></label></th>
				<td>
					<input name="secret_key" type="text" id="secret_key" class="regular-text" value="<?php echo esc_attr( get_site_option( 'mtpc_secret_key' ) ) ?>" />
					<p class="description" id="admin-email-desc">
						<?php _e( 'This email address will receive notifications. Registration and support emails will also come from this address.' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>