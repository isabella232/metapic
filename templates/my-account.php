<h3><?php esc_html_e( 'Your account', 'metapic' ); ?></h3>
<p><?php printf( esc_html__( 'You are currently logged in as: %s', 'metapic' ), '<strong>' . get_option( 'mtpc_email', '' ) . '</strong>' ) ?></p>
<p><label for="mtpc-autolink-default">
		<input type="hidden" name="metapic_options[mtpc_deeplink_auto_default]" value="0">
		<input type="checkbox" <?php checked( $options["mtpc_deeplink_auto_default"] ) ?> value="1" id="mtpc-autolink-default" name="metapic_options[mtpc_deeplink_auto_default]">
		<?php esc_html_e( 'Activate auto linking by default', 'metapic' ) ?></label></p>
<p class="submit">
	<?php if ( ! $this->debugMode ) : ?>
		<?php submit_button( __( 'Save Changes', 'metapic' ), 'primary', 'submit', false, array( 'style' => 'margin-right:10px;' ) ); ?>
	<?php endif; ?>
	<?php submit_button( __( 'Log out', 'metapic' ), $this->debugMode ? 'secondary' : 'primary', 'logout', false ); ?>
</p>