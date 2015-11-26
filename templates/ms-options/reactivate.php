<h2><?php esc_html_e( 'Activate Metapic account', 'metapic' ) ?></h2>
<form action="options.php" method="post" id="mtpc_reactivate">
	<?php settings_fields( 'metapic_options' ); ?>
	<input type="hidden" name="mtpc_action" value="reactivate" />
	<p>
		<?php printf( esc_html__( 'Metapic has been activated on your site by the network administrator. There is an existing account connected to your email address, %s.', 'metapic' ), '<strong>' . $wp_user->user_email . '</strong>' ); ?>
	</p>

	<p>
		<?php esc_html_e( "This could mean that you're either already using Metapic on a blog in the network or that you have used Metapic before. Total clicks will be pooled from all blogs using your account in the network", 'metapic' ) ?>
	</p>
	<?php if ( is_super_admin() ):
		/* @var WPDB $wpdb */
		global $wpdb;
		$users = get_users( [ "blog_id" => null ] );
		?>
		<p>
			<?php esc_html_e( "As the network administrator you can connect this blog to any account on this site.", 'metapic' ) ?>
		</p>
		<p>
			<?php esc_html_e( 'Please select an account from the list.', 'metapic' ); ?>
		</p>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="mtpc_email"><?php esc_html_e( 'Select user', 'metapic' ); ?></label></th>
				<td>
					<select name="mtpc_email" id="mtpc_email">
						<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
						<?php foreach ( $users as $user ): ?>
							<option value="<?php echo esc_attr( $user->user_email ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
	<?php endif; ?>
	<?php submit_button( __( 'Activate account', 'metapic' ) ); ?>
</form>
<script>
	(function( $ ) {
		$( "#mtpc_reactivate" ).on( "submit", function() {
			var mtpcEmail = $( "#mtpc_email" );
			if ( mtpcEmail.length > 0 && mtpcEmail.val() == "" ) {
				alert( "<?php esc_html_e( "You must select a user.", "metapic" ) ?>" );
				return false;
			}
		} );
	})( jQuery );
</script>