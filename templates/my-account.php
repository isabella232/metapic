<h3><?= __('Your account', "metapic") ?></h3>
<p><?= sprintf(__('You are currently logged in as: <strong>%s</strong>', 'metapic'), get_option("mtpc_email")) ?></p>
<p class="submit"><input type="submit" value="<?= __('Log out', 'metapic') ?>" class="button <?= $loginPrimary ?>" id="logout" name="logout"></p>