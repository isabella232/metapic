<h3 class="sub-header"><?= __("Total clicks per day - last 10 days", 'metapic') ?></h3>
<?php if (is_array($clicks) && count($clicks) > 0): ?>
	<div class="content">
		<div>
			<h4><?= __("Date", "metapic") ?></h4>
			<ul>
				<?php foreach ($clicks as $click): ?>
					<li class="click-date"><?=  mysql2date( get_option( 'date_format' ), $click["date"]) ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div>
			<h4><?= __("Clicks", "metapic") ?></h4>
			<ul>
				<?php foreach ($clicks as $click): ?>
					<li><?= ($click["link_clicks"] + $click["tag_clicks"]) ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<a href="#" id="metapic-show-stats" class="button"><?= __('Show detailed statistics', 'metapic')?></a>
	</div>
	<footer>
		<div class="clicks-month">
			<h3><?= $month ?></h3>
			<p><?= __('Clicks this month', 'metapic') ?></p>
		</div>

		<div class="clicks-total">
			<h3><?= $total ?></h3>
			<p><?= __('Clicks total', 'metapic') ?></p>
		</div>
	</footer>
<?php else: ?>
	<p><?= __("You have note received any clicks yet.", 'metapic'); ?></p>
<?php endif; ?>
<script type="text/javascript">
	(function($) {
		$("#metapic-show-stats").on("click", function(e) {
			e.preventDefault();
			$.event.trigger({
				type: "metapic",
				baseUrl: "<?= $this->getApiUrl() ?>",
				startPage: "stats",
				hideSidebar: true,
				randomKey: "<?= get_option("mtpc_access_token") ?>"
			});
		});
	})(jQuery);
</script>