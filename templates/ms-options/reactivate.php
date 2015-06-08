<h2><?= __('Aktivera Metapic-konto', 'metapic') ?></h2>
<form action="options.php" method="post">
	<?php settings_fields('metapic_options'); ?>
	<input type="hidden" name="mtpc_action" value="reactivate"/>
	<p>
		Metapic har aktiverats på din blogg av er näverksadministratör.<br/>
		Det finns redan ett Metapic-konto kopplat till din e-postadress, <strong><?= $blogInfo ?></strong>.
	</p>

	<p>Genom att trycka på knappen nedan så aktiverar du ditt konto igen.</p>

	<p class="submit"><input type="submit" value="<?php esc_attr_e('Reactivate account', 'metapic'); ?>"
	                         class="button button-primary" id="submit" name="submit"></p>
</form>