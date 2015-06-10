<h2><?= __('Metapic settings page', 'metapic') ?></h2>
<form action="options.php" method="post">
	<?php settings_fields('metapic_options'); ?>
	<h3 class="title">Mina inställningar</h3>

	<p>
		Metapic har aktiverats på din blogg av er näverksadministratör.<br/>
		Du har ett aktivt Metapic-konto och kan nu använda tjänsten för att tagga bilder och skapa kollage.
		<br/>

	</p>
	<p>Kontot är kopplat till e-postadressen: <strong><?= $blogInfo ?></strong></p>
	<p class="submit"><input type="submit" value="<?php esc_attr_e($submitText, 'metapic'); ?>"
	                         class="button button-primary" id="submit" name="submit"></p>
</form>