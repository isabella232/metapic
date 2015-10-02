<a href="#" id="metapic-help-button" class="button"><?= __("How to earn money", "metapic") ?></a>
<script type="text/javascript">
	(function($) {
		$("#metapic-help-button").on("click", function(e) {
			e.preventDefault();
			$.event.trigger({
				type: "metapic",
				baseUrl: "<?= $this->getApiUrl() ?>",
				startPage: "guide",
				hideSidebar: true,
				randomKey: "<?= get_option("mtpc_access_token") ?>"
			});
		});
	})(jQuery);
</script>