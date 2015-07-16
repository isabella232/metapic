<h2><?= __('Activate Metapic account', 'metapic') ?></h2>
<form action="options.php" method="post" id="mtpc_reactivate">
	<?php settings_fields('metapic_options'); ?>
	<input type="hidden" name="mtpc_action" value="reactivate"/>
	<p>
		<?php printf(__('Metapic has been activated on your site by the network administrator.<br/>There is an existing account connected to your email address, <strong>%s</strong>.', 'metapic'), $wp_user->user_email) ?>

	</p>

	<p>
		<?= __("This could mean that you're either already using Metapic on a blog in the network or that you have used Metapic before.<br/>Total clicks will be pooled from all blogs using your account in the network", 'metapic') ?>
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
	<?php endif; ?>
	<p class="submit"><input type="submit" value="<?php _e('Activate account', 'metapic'); ?>"
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