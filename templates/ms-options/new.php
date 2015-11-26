<h2><?php esc_html_e( 'Metapic settings page', 'metapic' ) ?></h2>
<form action="options.php" method="post">
	<?php settings_fields( 'metapic_options' ); ?>
	<h3 class="title"><?php esc_html_e( 'Create account', 'metapic' ) ?></h3>

	<p>
		<?php esc_html_e( 'Metapic has been activated on your site by the network administrator.', 'metapic' ) ?>
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
			<?php esc_html_e( "Please select an account from the list.", 'metapic' ) ?>
		</p>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="mtpc_email"><?php esc_html_e( 'Select user', 'metapic' ) ?></label></th>
				<td>
					<select name="mtpc_email" id="mtpc_email">
						<option value=""><?php esc_html_e( '&mdash; Select &mdash;', 'metapic' ) ?></option>
						<?php foreach ( $users as $user ): ?>
							<option value="<?php echo esc_attr( $user->user_email ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
	<?php else: ?>
		<p>
			<?php printf( esc_html__( "In order to start using Metapic to link content, tag images and make collages to earn money just click on the button below. An account will be created connected to the email address: %s.", 'metapic' ), '<strong>' . $wp_user->user_email . '</strong>' ) ?>
		</p>
		<!--table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="accept_terms">
						<input type="checkbox" value="1" id="accept_terms"
						       name="accept_terms"> <?php esc_html_e( 'I accept the terms.', 'metapic' ) ?>
					</label>
				</th>

			</tr>
			</tbody>
		</table-->
	<?php endif; ?>
	<?php submit_button( $submitText ); ?>
</form>
<script>
	(function( $ ) {
		$( "#mtpc_reactivate" ).on( "submit", function() {
			var mtpcTerms = $( "#accept_terms" );
			if ( mtpcTerms.length > 0 && !mtpcTerms.is( ":checked" ) ) {
				alert( "<?php esc_html_e("You must accept the terms.", "metapic") ?>" );
				return false;
			}
		} );
	})( jQuery );
</script>