<script>
	(function($) {
		window.$_metapic_user_config = <?= json_encode($config) ?>;
	})(jQuery);
</script>
<!--<script src="//s3-eu-west-1.amazonaws.com/metapic-cdn/site/javascript/metapic/metapic.nattstad.js" id="metapic_load" metapic_userid="--><?//= $user["id"] ?><!--" async></script>-->
<script src="<?= $plugin_url ?>/js/vendor/metapic/loading.js" id="metapic_load" metapic_userid="<?= $user["id"] ?>" metapic_no_login="true" metapic_async_load="false"></script>
<script src="<?= $plugin_url ?>/js/vendor/metapic/metapic.preLoginNoLogin.js" async></script>