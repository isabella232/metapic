<?php
global $post;
$deepLinkActive = (int)get_option( "mtpc_deeplink_auto" );
$deepLinkPost = get_post_meta($post->ID, "mtpc_deeplink_auto", true);
$deepLinkStatus = ($deepLinkPost) ? __( "Active", "metapic" ) : __( "Inactive", "metapic" );
?>
<div class="misc-pub-section mtpc-deeplinking">
	<span class="mtpc-deeplink-text"><?= __( "Auto link content:", "metapic" ) ?></span>
	<span id="deeplink-status-display">
		<span class="deeplink-status-text status-0" <?php if ($deepLinkPost) { ?>style="display: none;"<?php } ?>><?= __( "Inactive", "metapic" ) ?></span>
		<span class="deeplink-status-text status-1" <?php if (!$deepLinkPost) { ?>style="display: none;"<?php } ?>><?= __( "Active", "metapic" ) ?></span>
	</span>
	<a href="#deeplink-status-select" class="edit-deeplink-status hide-if-no-js"><span
			aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span
			class="screen-reader-text"><?php _e( 'Edit auto linking', 'metapic' ); ?></span></a>

	<div class="hide-if-js" id="deeplink-status-select">
		<div class="deeplink-status-edit">
			<input type="hidden" value="<?= $deepLinkPost ?>" id="deeplink-status-auto" name="mtpc_deeplink_auto"/>
			<input type="checkbox" value="1" id="deeplink-status-check" name="mtpc_deeplink_auto_check" <?php if ($deepLinkPost) { ?>checked="checked"<?php } ?>>
			<label class="selectit" for="deeplink-status-check"><?= __( "Activate auto linking", "metapic" ) ?></label>
		</div>
		<p>
			<a class="save-deeplink-status hide-if-no-js button" href="#deeplink-status-select"><?php _e('OK'); ?></a>
			<a class="cancel-deeplink-status hide-if-no-js button-cancel" href="#deeplink-status-select"><?php _e('Cancel'); ?></a>
		</p>
	</div>
</div>

<!--div class="hide-if-js" id="post-status-select" style="display: block;">
	<input type="hidden" value="publish" id="hidden_post_status" name="hidden_post_status">
	<select id="post_status" name="post_status">
		<option value="publish" selected="selected">Publicerat</option>
		<option value="pending">Väntar på granskning</option>
		<option value="draft">Utkast</option>
	</select>
	<a class="save-post-status hide-if-no-js button" href="#post_status">OK</a>
	<a class="cancel-post-status hide-if-no-js button-cancel" href="#post_status">Avbryt</a>
</div-->