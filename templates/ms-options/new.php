<h2><?= __('Metapic settings page', 'metapic') ?></h2>
<form action="options.php" method="post">
	<?php settings_fields('metapic_options'); ?>
	<h3 class="title">Skapa Metapic-konto</h3>

	<p>
		Metapic har aktiverats på din blogg av er näverksadministratör.
	</p>
	<?php if(is_super_admin()):
		/* @var WPDB $wpdb */
		global $wpdb;
		$users = get_users(["blog_id" => null]);
		?>
		<p>
			Som nätverksadministratör kan du koppla denna blogg till valfritt användarkonto på denna sajt.<br/>
			Var god välj ett konto i listan.
		</p>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="mtpc_email"><?php _e('Select user', 'metapic') ?></label></th>
				<td>
					<select name="mtpc_email" id="mtpc_email">
						<option value=""><?= __( '&mdash; Select &mdash;' ) ?></option>
						<?php foreach ($users as $user):  ?>
							<option value="<?= $user->user_email ?>"><?= $user->display_name ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<?php else: ?>
		<p>
			För att börja använda Metapic för att tagga bilder och göra kollage så måste du först acceptera
			medlemsvillkoren.<br/>
			Ett konto kommer att skapas kopplat till e-postadressen: <strong><?= $wp_user->user_email ?></strong>
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
	<?php endif; ?>
	<p class="submit"><input type="submit" value="<?php esc_attr_e($submitText, 'metapic'); ?>"
	                         class="button button-primary" id="submit" name="submit"></p>
</form>
<script>
	(function($) {
		$("#mtpc_reactivate").on("submit", function() {
			var mtpcTerms = $("#accept_terms");
			if (mtpcTerms.length > 0 && !mtpcTerms.is(":checked")) {
				alert("<?= __("You must accept the terms.", "metapic") ?>");
				return false;
			}
		});
	})(jQuery);
</script>