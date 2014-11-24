<script type="text/javascript">
	(function($) {
		if ($.fn.MetapicEditor) {
			window.$_metapic_access_token = "<?= $token ?>";
			window.$_metapic_user_config = <?= json_encode($config) ?>;
			$.fn.MetapicEditor.config = {
				access_token: window.$_metapic_access_token,
				user_client_id: "<?= get_option("metapic_user_client_id") ?>"
			};
		}
	})(jQuery);
</script>