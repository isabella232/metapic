<h2><?= __('Metapic settings page', 'metapic') ?></h2>
<form action="options.php" method="post">
	<?php settings_fields('metapic_options'); ?>
	<h3 class="title"><?= __('My settings', 'metapic') ?></h3>

	<p><?= __('Metapic has been activated on your site by the network administrator.<br/>You have an active account and you can use the service to tag images and create collages.', 'metapic') ?></p>
	<p><?= __('The account is connected to the following email address:', 'metapic') ?> <strong><?= $mtpcEmail ?></strong></p>
	<input type="hidden" name="mtpc_action" id="mtpc_action" value="deactivate"/>
	<p class="submit"><input type="submit" value="<?php esc_attr_e($submitText, 'metapic'); ?>"
	                         class="button button-primary" id="submit" name="submit"></p>
</form>