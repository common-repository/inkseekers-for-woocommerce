<div class="inkseekers-connect">

    <div class="inkseekers-connect-inner">

        <h1><?php esc_html_e('Connect to Inkseekers', 'inkseekers'); ?></h1>

        <img src=" <?php echo esc_url(Inkseekers_Base::inkseekers_get_asset_url() . 'images/connect.svg'); ?>" class="connect-image" alt="connect to inkseekers">

        <?php
        if ( ! empty( $issues ) ) {
            ?>
            <p><?php esc_html_e('To connect your store to Inkseekers, fix the following errors:', 'inkseekers'); ?></p>
            <div class="inkseekers-notice">
                <ul>
                    <?php
                    foreach ( $issues as $issue ) {
                        echo '<li>' . wp_kses_post( $issue ) . '</li>';
                    }
                    ?>
                </ul>
            </div>
            <?php
            $url = '#';
        } else {
            ?><p class="connect-description"><?php esc_html_e('You\'re almost done! Just 2 more steps to have your WooCommerce store connected to Inkseekers for automatic order fulfillment.', 'inkseekers'); ?></p><?php
            $url = Inkseekers_Base::inkseeker_get_host() . 'woocommerce/pluginconnect?website=' . urlencode( trailingslashit( get_home_url() ) ) . '&key=' . urlencode( $consumer_key ) . '&returnUrl=' . urlencode( get_admin_url( null,'admin.php?page=' . Inkseekers_Admin::MENU_SLUG_DASHBOARD ) );
        }

        echo '<a href="' . esc_url($url) . '" class="button button-primary inkseekers-connect-button ' . ( ! empty( $issues ) ? 'disabled' : '' ) . '">' . esc_html__('Connect', 'inkseekers') . '</a>';
        ?>

        <img src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ) ?>" class="loader hidden" width="20px" height="20px" alt="loader"/>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                Inkseekers_Connect.init('<?php echo esc_url( admin_url( 'admin-ajax.php?action=ajax_force_check_connect_status' ) ); ?>');
            });
        </script>
    </div>
</div>