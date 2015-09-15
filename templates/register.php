<div class="wrap">
	<h2><?= __('Metapic registration', 'metapic') ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields('metapic_register_options'); ?>
		<p><?= __('Enter your email address and your password to start using Metapic!', 'metapic') ?></p>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><?= __('Email', "metapic") ?></th>
				<td><input type="text" value="" size="40" name="metapic_register_options[email_string]"
				           required></td>
			</tr>
			<tr>
				<th scope="row"><?= __('Password', "metapic") ?></th>
				<td><input type="password" value="" size="40" name="metapic_register_options[password_string]"
				           required>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="register" id="register" class="button button-primary"
		                         value="<?= __('Create account', "metapic") ?>"></p>
	</form>
</div>