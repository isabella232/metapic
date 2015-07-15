<h2><?= __('Aktivera Metapic-konto', 'metapic') ?></h2>
<form action="options.php" method="post" id="mtpc_reactivate">
	<?php settings_fields('metapic_options'); ?>
	<input type="hidden" name="mtpc_action" value="reactivate"/>
	<p>
		Metapic har aktiverats på er sajt av er näverksadministratör.<br/>
		Det finns redan ett Metapic-konto kopplat till din e-postadress, <strong><?= $wp_user->user_email ?></strong>.
	</p>

	<p>
		Detta kan betyda att du antingen redan använder Metapic på en blogg i detta nätverk eller att du har använt Metapic tidigare.<br/>
		Om du redan använder Metapic på en annan blogg i nätverket så kommer klick att räknas från båda bloggarna.<br/>
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
	<?php endif; ?>
	<p class="submit"><input type="submit" value="<?php esc_attr_e('Activate account', 'metapic'); ?>"
	                         class="button button-primary" id="submit" name="submit"></p>
</form>
<script>
	(function($) {
		$("#mtpc_reactivate").on("submit", function() {
			var mtpcEmail = $("#mtpc_email");
			if (mtpcEmail.length > 0 && mtpcEmail.val() == "") {
				alert("<?= __("You must select a user.", "metapic") ?>");
				return false;
			}
		});
	})(jQuery);
</script>