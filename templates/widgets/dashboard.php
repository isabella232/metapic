<h3 class="sub-header"><?php esc_html_e( "Total clicks per day - last 10 days", 'metapic' ) ?></h3>
<?php if ( is_array( $clicks ) && count( $clicks ) > 0 ): ?>
	<div class="content">
		<div>
			<h4><?php esc_html_e( "Date", "metapic" ) ?></h4>
			<ul>
				<?php foreach ( $clicks as $click ): ?>
					<li class="click-date"><?= mysql2date( get_option( 'date_format' ), $click["date"] ) ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div>
			<h4><?php esc_html_e( "Clicks", "metapic" ) ?></h4>
			<ul>
				<?php foreach ( $clicks as $click ): ?>
					<li><?php echo ( $click["link_clicks"] + $click["tag_clicks"] ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<a href="#" id="metapic-show-stats" class="button"><?php esc_html_e( 'Show detailed statistics', 'metapic' ) ?></a>
	</div>
	<footer>
		<div class="clicks-month">
			<h3><?php echo esc_html( $month ); ?></h3>
			<p><?php esc_html_e( 'Clicks this month', 'metapic' ) ?></p>
		</div>

		<div class="clicks-total">
			<h3><?php echo esc_html( $total ); ?></h3>
			<p><?php esc_html_e( 'Clicks total', 'metapic' ) ?></p>
		</div>
	</footer>
<?php else: ?>
	<p><?php esc_html_e( "You have not received any clicks yet.", 'metapic' ); ?></p>
<?php endif; ?>
<script type="text/javascript">
	(function( $ ) {
		$( "#metapic-show-stats" ).on( "click", function( e ) {
			e.preventDefault();
			$.event.trigger( {
				type       : "metapic",
				baseUrl    : "<?php echo esc_js( $this->getApiUrl() ); ?>",
				startPage  : "stats",
				hideSidebar: true,
				randomKey  : "<?php echo esc_js( get_option("mtpc_access_token") ); ?>"
			} );
		} );
	})( jQuery );
</script>