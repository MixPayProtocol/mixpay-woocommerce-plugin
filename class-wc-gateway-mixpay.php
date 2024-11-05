<?php

/**
 * @wordpress-plugin
 * Plugin Name:             MixPay Gateway for WooCommerce
 * Plugin URI:              https://github.com/MixPayHQ/mixpay-woocommerce-plugin
 * Description:             Cryptocurrency Payment Gateway.
 * Version:                 1.1.5
 * Author:                  MixPay Payment
 * License:                 GPLv2 or later
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:             wc-mixpay-gateway
 * Domain Path:             /i18n/languages/
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MPWC_PLUGIN_FILE' ) ) {
    define( 'MPWC_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MPWC_VERSION' ) ) {
    define( 'MPWC_VERSION', '1.1.5' );
}

if ( ! defined( 'MPWC_PLUGIN_URL' ) ) {
    define( 'MPWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'MPWC_PLUGIN_DIR_PATH' ) ) {
    define( 'MPWC_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if (! defined('MIXPAY_SUPPORT_EMAIL')) {
    define('MIXPAY_SUPPORT_EMAIL', 'bd@mixpay.me');
}

if (! defined('MIXPAY_PAY_LINK')) {
    define('MIXPAY_PAY_LINK', 'https://mixpay.me/pay');
}

if (! defined('MIXPAY_CODE_LINK')) {
    define('MIXPAY_CODE_LINK', 'https://mixpay.me/code');
}

if (! defined('MIXPAY_API_URL')) {
    define('MIXPAY_API_URL', 'https://api.mixpay.me/v1');
}

if (! defined('MIXPAY_SETTLEMENT_ASSETS_API')) {
    define('MIXPAY_SETTLEMENT_ASSETS_API', MIXPAY_API_URL . '/setting/settlement_assets');
}

if (! defined('MIXPAY_QUOTE_ASSETS_API')) {
    define('MIXPAY_QUOTE_ASSETS_API', MIXPAY_API_URL . '/setting/quote_assets');
}

if (! defined('MIXPAY_MIXINUUID_API')) {
    define('MIXPAY_MIXINUUID_API', MIXPAY_API_URL . '/user/mixin_uuid');
}

if (! defined('MIXPAY_MULTISIG_API')) {
    define('MIXPAY_MULTISIG_API', MIXPAY_API_URL . '/multisig');
}

if (! defined('MIXPAY_ASSETS_EXPIRE_SECONDS')) {
    define('MIXPAY_ASSETS_EXPIRE_SECONDS', 600);
}

if (! defined('MIXPAY_PAYMENTS_RESULT')) {
    define('MIXPAY_PAYMENTS_RESULT', MIXPAY_API_URL . '/payments_result');
}

if (! defined('MIXPAY_ONE_TIME_PAYMENT')) {
    define('MIXPAY_ONE_TIME_PAYMENT', MIXPAY_API_URL . '/one_time_payment');
}

require dirname( MPWC_PLUGIN_FILE ) . '/includes/class-mpwc-init.php';

add_action( 'plugins_loaded', 'load_mpwc' );


/**
 * Loads Plugin
 */
function load_mpwc() {
    MPWC_Init::get_instance();
}


/**
 * Adds plugin page links
 */
if (! function_exists('wc_mixpay_plugin_links')) {
    function wc_mixpay_plugin_links($links)
    {
        $plugin_links = [
            '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=mixpay_gateway')) . '">' . esc_html(__('Configure', 'wc-mixpay-gateway')) . '</a>',
            '<a href="mailto:' . esc_html(MIXPAY_SUPPORT_EMAIL) . '">' . esc_html(__('Email Developer', 'wc-mixpay-gateway')) . '</a>',
        ];

        return array_merge($plugin_links, $links);
    }
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_mixpay_plugin_links');

if (! function_exists('add_cron_every_minute_interval')) {
    function add_cron_every_minute_interval($schedules)
    {
        if (! isset($schedules['every_minute'])) {
            $schedules['every_minute'] = [
                'interval' => 60,
                'display'  => esc_html__('Every minute'),
            ];
        }

        return $schedules;
    }
}
add_filter('cron_schedules', 'add_cron_every_minute_interval');
