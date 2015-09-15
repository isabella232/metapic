<h3><?= __('Login', "metapic") ?></h3>

<p><?= __('Please login to your Metapic account', 'metapic') ?></p>
<table class="form-table">
	<tbody>
	<tr>
		<th scope="row"><?= __('Email', "metapic") ?></th>
		<td><input type="text" value="" size="40" name="metapic_options[email_string]"
		           id="plugin_text_string"></td>
	</tr>
	<tr>
		<th scope="row"><?= __('Password', "metapic") ?></th>
		<td><input type="password" value="" size="40" name="metapic_options[password_string]"
		           id="plugin_text_string">
		</td>
	</tr>
	</tbody>
</table>
<p class="submit"><input type="submit" name="login" id="login" class="button <?= $loginPrimary ?>"
                         value="<?= __('Log in', "metapic") ?>"> </p>
<p><?= __('Don\'t have an account?', 'metapic') ?> <a href="?page=metapic_register" style="text-decoration: none;"><?= __('Click here to register', 'metapic') ?></a></p>