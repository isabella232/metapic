<script>
	(function($) {
		window.$_metapic_user_config = <?= json_encode($config) ?>;
	})(jQuery);
</script>
<script src="//s3-eu-west-1.amazonaws.com/metapic-cdn/site/javascript/metapic/metapic.nattstad.js" id="metapic_load" metapic_userid="<?= $user["id"] ?>" async></script>