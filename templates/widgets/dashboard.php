<?php if (is_array($clicks) && count($clicks) > 0): ?>
	<div style="overflow:hidden;">
		<div style="width:50%;float:left;">
			<h4><?= __("Date", "metapic") ?></h4>
			<ul>
				<?php foreach ($clicks as $click): ?>
					<li><?= $click["date"] ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div style="width:50%;float:left;">
			<h4><?= __("Clicks", "metapic") ?></h4>
			<ul>
				<?php foreach ($clicks as $click): ?>
					<li><?= ($click["link_clicks"] + $click["tag_clicks"]) ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
<?php else: ?>
	<p><?= __("You have note received any clicks yet.", 'metapic'); ?></p>
<?php endif; ?>
