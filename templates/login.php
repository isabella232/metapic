<h3><?php esc_html_e( 'Login', "metapic" ) ?></h3>

<p><?php esc_html_e( 'Please login to your Metapic account', 'metapic' ) ?></p>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><?php esc_html_e( 'Email', "metapic" ) ?></th>
			<td><input type="text" value="" size="40" name="metapic_options[email_string]"
			           id="plugin_text_string"></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Password', "metapic" ) ?></th>
			<td><input type="password" value="" size="40" name="metapic_options[password_string]"
			           id="plugin_text_string">
			</td>
		</tr>
	</tbody>
</table>

<?php submit_button( __( 'Log in', "metapic" ), 'primary', 'login' ); ?>

<p><?php esc_html_e( 'Don\'t have an account?', 'metapic' ) ?>
	<a href="?page=metapic_register" style="text-decoration: none;"><?php esc_html_e( 'Click here to register', 'metapic' ) ?></a>
</p>