<div class="inkseekers-stats">
	<div class="inkseekers-stats-item">
		<h4><?php echo esc_html(get_woocommerce_currency_symbol($stats['currency'])) . ' ' . esc_html($stats['orders_today']['total']); ?></h4>
        <b>
            <?php
            echo esc_html($stats['orders_today']['orders']);
            echo ' ' . _n('ORDER', 'ORDERS', $stats['orders_today']['orders'], 'inkseekers' );
            ?>
        </b>
        <?php esc_html_e('today', 'inkseekers'); ?>
	</div>
	<div class="inkseekers-stats-item">
        <h4>
            <?php echo esc_html(get_woocommerce_currency_symbol($stats['currency'])) . ' ' . esc_html($stats['orders_last_7_days']['total']); ?>
	        <?php echo '<span class="dashicons dashicons-arrow-' . esc_attr($stats['orders_last_7_days']['trend']) .'-alt"></span>'; ?>
        </h4>
        <b>
            <?php
            echo esc_html($stats['orders_last_7_days']['orders']);
            echo ' ' . _n( 'ORDER', 'ORDERS', $stats['orders_last_7_days']['orders'], 'inkseekers' );
            ?>
        </b>
        <?php esc_html_e('last 7 days', 'inkseekers'); ?>
	</div>
	<div class="inkseekers-stats-item">
        <h4>
            <?php echo esc_html(get_woocommerce_currency_symbol($stats['currency'])) . ' ' . esc_html($stats['orders_last_28_days']['total']); ?>
	        <?php echo '<span class="dashicons dashicons-arrow-' . esc_attr($stats['orders_last_28_days']['trend']) .'-alt"></span>'; ?>
        </h4>
        <b>
	        <?php
	        echo esc_html($stats['orders_last_28_days']['orders']);
	        echo ' ' . _n( 'ORDER', 'ORDERS', $stats['orders_last_28_days']['orders'], 'inkseekers' );
	        ?>
        </b> <?php esc_html_e('last 28 days', 'inkseekers'); ?>
	</div>
	<div class="inkseekers-stats-item">
        <h4>
            <?php echo esc_html(get_woocommerce_currency_symbol($stats['currency'])) . ' ' . esc_attr($stats['profit_last_28_days']); ?>
	        <?php echo '<span class="dashicons dashicons-arrow-' . esc_attr($stats['profit_trend_last_28_days']) .'-alt"></span>'; ?>
        </h4>
        <b><?php esc_html_e('PROFIT', 'inkseekers'); ?></b> <?php esc_html_e('last 28 days', 'inkseekers'); ?>
	</div>
</div>