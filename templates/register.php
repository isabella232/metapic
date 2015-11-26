<div class="wrap">
	<h2><?php esc_html_e( 'Metapic registration', 'metapic' ); ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'metapic_register_options' ); ?>
		<p><?php esc_html_e( 'Enter your email address and your password to start using Metapic!', 'metapic' ) ?></p>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Email', "metapic" ); ?></th>
				<td><input type="text" value="" size="40" name="metapic_register_options[email_string]"
				           required></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Password', "metapic" ); ?></th>
				<td><input type="password" value="" size="40" name="metapic_register_options[password_string]"
				           required>
				</td>
			</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Create account', "metapic" ), 'primary', 'register' ); ?>
	</form>
</div>