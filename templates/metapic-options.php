<?php

/* @var WP_MTPC $this */
$activeAccount = get_option("mtpc_active_account");
$options = get_option('metapic_options');
$loginPrimary = ($this->debugMode) ? "" : "button-primary";

?>
<div class="wrap">
	<h2><?= __('Metapic settings page', 'metapic') ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields('metapic_options'); ?>
		<?php if ($activeAccount) {
			$this->getTemplate("my-account", ["loginPrimary" => $loginPrimary, 'options' => $options]);
		}
		else {
			$this->getTemplate("login", ["loginPrimary" => $loginPrimary, 'options' => $options]);
		} ?>

		<?php if ($this->debugMode): ?>
		<h3><?= __('Advanced settings', 'metapic') ?></h3>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><?= __('Server address', 'metapic') ?></th>
				<td><input type="text" value="<?= $options["uri_string"] ?>" size="40" name="metapic_options[uri_string]"
				           id="plugin_text_string"></td>

			</tr>
            <tr>
                <th scope="row"><?= __('Userserver address', 'metapic') ?></th>
                <td><input type="text" value="<?= $options["user_api_uri_string"] ?>" size="40" name="metapic_options[user_api_uri_string]"
                           id="plugin_text_string"></td>

            </tr>
            <tr>
                <th scope="row"><?= __('CDN address', 'metapic') ?></th>
                <td><input type="text" value="<?= $options["cdn_uri_string"] ?>" size="40" name="metapic_options[cdn_uri_string]"
                           id="plugin_text_string"></td>

            </tr>


			</tbody>
		</table>
		<p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes'); ?>"
		                         class="button button-primary" id="submit" name="submit"></p>
		<?php endif; ?>
	</form>
</div>