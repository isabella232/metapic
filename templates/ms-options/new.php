<h2><?= __('Metapic settings page', 'metapic') ?></h2>
<form action="options.php" method="post">
	<?php settings_fields('metapic_options'); ?>
	<h3 class="title">Skapa Metapic-konto</h3>

	<p>
		Metapic har aktiverats på din blogg av er näverksadministratör.<br/>
		För att börja använda Metapic för att tagga bilder och göra kollage så måste du först acceptera
		medlemsvillkoren.
		<br/>
		Ett konto kommer att skapas kopplat till e-postadressen: <strong><?= $blogInfo ?></strong>
	</p>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row"><label for="accept_terms">
					<input type="checkbox" value="1" id="accept_terms"
					       name="accept_terms"> <?= __('I accept the terms.', 'metapic') ?>
				</label>
			</th>
			<td></td>
		</tr>
		</tbody>
	</table>
	<p class="submit"><input type="submit" value="<?php esc_attr_e($submitText, 'metapic'); ?>"
	                         class="button button-primary" id="submit" name="submit"></p>
</form>