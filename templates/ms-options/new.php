<h2><?= __('Metapic settings page', 'metapic') ?></h2>
<form action="options.php" method="post">
	<?php settings_fields('metapic_options'); ?>
	<h3 class="title"><?= __('Create account', 'metapic') ?></h3>

	<p>
		<?= __('Metapic has been activated on your site by the network administrator.', 'metapic') ?>

	</p>
	<?php if(is_super_admin()):
		/* @var WPDB $wpdb */
		global $wpdb;
		$users = get_users(["blog_id" => null]);
		?>
		<p>
			<?= __("As the network administrator you can connect this blog to any account on this site.<br/>Please select an account from the list.", 'metapic')?>
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
			<?php //printf(__("In order to start using Metapic to link content, tag images and make collages you first have to accept our terms of use.<br/>An account will be created connected to the email address: <strong>%s</strong>.", 'metapic'), $wp_user->user_email) ?>
			<?php printf(__("In order to start using Metapic to link content, tag images and make collages to earn money just click on the button below.<br/>An account will be created connected to the email address: <strong>%s</strong>.", 'metapic'), $wp_user->user_email) ?>
		</p>
		<!--table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="accept_terms">
						<input type="checkbox" value="1" id="accept_terms"
						       name="accept_terms"> <?= __('I accept the terms.', 'metapic') ?>
					</label>
				</th>

			</tr>
			</tbody>
		</table-->
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