<div>
	<h2>Metapic settings page</h2>
	<form action="options.php" method="post">
		<?php settings_fields('metapic_options'); ?>
		<?php do_settings_sections('plugin'); ?>

		<p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button button-primary" id="submit" name="submit"></p>
	</form>
</div>