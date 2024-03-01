<?php

class MPWC_Gateway extends WC_Payment_Gateway {

    /**
     * MPWC_Gateway constructor.
     */
    public function __construct() {

        $this->id                 = 'mixpay_gateway';
        $this->has_fields         = false;
        $this->method_title       = esc_html(__('MixPay Payment', 'wc-gateway-mixpay'));
        $this->method_description = esc_html(__('Allows Cryptocurrency payments via MixPay', 'wc-mixpay-gateway'));

        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title                   = $this->get_option('title');
        $this->description             = $this->get_option('description');
        $this->instructions            = $this->get_option('instructions');
        $this->mixin_id                = $this->get_option('mixin_id');
        $this->payee_uuid              = $this->get_option('payee_uuid');
        $this->store_name              = $this->get_option('store_name');
        $this->settlement_asset_id     = $this->get_option('settlement_asset_id');
        $this->invoice_prefix          = $this->get_option('invoice_prefix', 'WORDPRESS-WC-');
        $this->debug                   = $this->get_option('debug', false);

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action('woocommerce_thankyou_' . $this->id, [$this, 'thankyou_page']);
        add_action('woocommerce_api_wc_gateway_mixpay', [$this, 'mixpay_callback']);
        add_filter('woocommerce_settings_api_sanitized_fields_' . $this->id, [$this, 'deal_options'], 1, 1);

        // Customer Emails
        add_action('woocommerce_email_before_order_table', [$this, 'email_instructions'], 10, 3);
    }

    /**
     * Admin form fields
     */
    public function init_form_fields() {

        $this->form_fields = apply_filters('wc_offline_form_fields', [
            'enabled' => [
                'title'   => esc_html(__('Enable/Disable', 'wc-mixpay-gateway')),
                'type'    => 'checkbox',
                'label'   => esc_html(__('Enable MixPay Payment', 'wc-mixpay-gateway')),
                'default' => 'yes',
            ],
            'title' => [
                'title'       => esc_html(__('Title', 'wc-mixpay-gateway')),
                'type'        => 'text',
                'description' => esc_html(__('This controls the title which the user sees during checkout.', 'wc-mixpaypayment-gateway')),
                'default'     => esc_html(__('MixPay Payment', 'wc-mixpay-gateway')),
            ],
            'description' => [
                'title'       => esc_html(__('Description', 'wc-mixpay-gateway')),
                'type'        => 'textarea',
                'description' => esc_html(__('This controls the description which the user sees during checkout.', 'wc-mixpay-gateway')),
                'default'     => esc_html(__('Expand your payment options with MixPay! BTC, ETH, LTC and many more: pay with anything you like!', 'wc-mixpay-gateway')),
            ],
            'mixin_id' => [
                'title'       => esc_html(__('Mixin Id ', 'wc-mixpay-gateway')),
                'type'        => 'text',
                'description' => __('(<strong style="color: red">Before setting the Mixin Id, the MixPay robot 7000104220 must be added to the Mixin wallet as a contact</strong>) This controls the mixin id or multisig group (minxinid_1|minxinid_2|minxinid_3|threshold)', 'wc-mixpay-gateway'),
            ],
            'payee_uuid' => [
                'title'             => esc_html(__('Payee Uuid ', 'wc-mixpay-gateway')),
                'type'              => 'text',
                'description'       => esc_html(__('This controls the assets payee uuid.', 'wc-mixpay-gateway')),
            ],
            'settlement_asset_id' => [
                'title'       => esc_html(__('Settlement Asset ', 'wc-mixpay-gateway')),
                'type'        => 'select',
                'description' => esc_html(__('This controls the assets received by the merchant.', 'wc-mixpay-gateway')),
                'options'     => $this->get_settlement_asset_lists(),
            ],
            'instructions' => [
                'title'       => esc_html(__('Instructions', 'wc-gateway-gateway')),
                'type'        => 'textarea',
                'description' => esc_html(__('The contents of this option are displayed on order received page and order email', 'wc-gateway-gateway')),
                'default'     => esc_html('Thanks for your using MixPay Payment!'),
            ],
            'store_name' => [
                'title'       => esc_html(__('Store Name', 'wc-mixpay-gateway')),
                'type'        => 'text',
                'description' => esc_html(__("(Optional) This option is useful when you have multiple stores, and want to view each store's  payment history in MixPay independently.", 'wc-mixpay-gateway')),
            ],
            'debug' => [
                'title'       => esc_html(__('Debug', 'wc-mixpay-gateway')),
                'type'        => 'text',
                'description' => esc_html(__('(this will Slow down website performance) Send post data to debug', 'wc-mixpay-gateway')),
            ],
        ]);

    }

    /**
     * Process Admin Settings | Validate
     */
    public function process_admin_options() {

        parent::process_admin_options();
    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page()
    {
        if ($this->instructions) {
            echo wpautop(wptexturize($this->instructions));
        }
    }

    /**
     * Add content to the WC emails.
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && $this->id === $order->get_payment_method()) {
            echo wpautop(wptexturize($this->instructions));
        }
    }

    /**
     * Get gateway icon.
     *
     * @return string
     */
    public function get_icon()
    {
        if ($this->get_option('show_icons') === 'no') {
            return '';
        }

        $url = WC_HTTPS::force_https_url(plugins_url('/assets/images/payemt_button.png', __FILE__));
        $icon_html .= '<img width="300" src="' . esc_attr($url) . '" alt="mixpay" />';

        return apply_filters('woocommerce_gateway_icon', $icon_html, $this->id);
    }
    
    /**
     * Let's Process the Payment xD
     */
    public function process_payment($order_id)
    {
        $order        = wc_get_order($order_id);
        $redirect_url = $this->generate_mixpay_url($order);

        if (! wp_next_scheduled('check_payments_result_cron_hook', [$order_id])) {
            wp_schedule_event(time(), 'every_minute', 'check_payments_result_cron_hook', [$order_id]);
        }

        return [
            'result'   => 'success',
            'redirect' => $redirect_url,
        ];
    }

    /**
     * Generate the mixpay button link
     */
    public function generate_mixpay_url($order)
    {
        if ($order->status != 'completed' && get_post_meta($order->id, 'MixPay payment complete', true) != 'Yes') {
            $order->add_order_note(esc_html('Customer is being redirected to MixPay...'));
        }

        $amount = number_format($order->get_total(), 8, '.', '');
        $rev    = bccomp($amount, 0, 8);

        if ($rev === 0) {
            $order->update_status('completed', 'The order amount is zero.');

            return $this->get_return_url($order);
        } elseif ($rev === -1) {
            throw new Exception('The order amount is incorrect, please contact customer');
        }

        $mixpay_args = $this->get_mixpay_args($order);

        $code = esc_html($this->post_payment_code($mixpay_args));

        if (empty($code)) {
            throw new Exception('Server error, please try again.');
        }

        $mixpay_adr  = MIXPAY_CODE_LINK . '/' . $code;

        return $mixpay_adr;
    }

    /**
     * Get MixPay Args
     */
    public function get_mixpay_args($order)
    {
        $mixpay_args = [
            'payeeId'           => $this->payee_uuid,
            'orderId'           => $this->invoice_prefix . $order->get_order_number(),
            'tagname'           => $this->store_name,
            'settlementAssetId' => $this->settlement_asset_id,
            'quoteAssetId'      => strtolower($order->get_currency()),
            'quoteAmount'       => number_format($order->get_total(), 8, '.', ''),
            'returnTo'          => $this->get_return_url($order),
            'callbackUrl'       => site_url() . '/?wc-api=wc_gateway_mixpay',
            'isTemp'            => true,
            'cancelUrl'         => $order->get_cancel_order_url_raw(),
        ];

        $mixpay_args = apply_filters('woocommerce_mixpay_args', $mixpay_args);

        return $mixpay_args;
    }


    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     */
    public function admin_options()
    {
        ?>
        <h3><?php _e('MixPay Payment', 'woocommerce'); ?></h3>
        <p><?php _e('Completes checkout via MixPay Payment', 'woocommerce'); ?></p>

        <?php if ($this->enabled) { ?>

        <table class="form-table">
            <?php
            $this->generate_settings_html();
            ?>
        </table>
        <!--/.form-table-->

    <?php } else { ?>
        <div class="inline error">
            <p><strong><?php _e('Gateway Disabled', 'woocommerce'); ?></strong>: <?php _e('MixPay Payment does not support your store currency.', 'woocommerce'); ?></p>
        </div>
    <?php }
    }

    /**
     * @param array $posted
     *
     * @return void
     */
    public function mixpay_callback()
    {
        ob_start();
        global $woocommerce;

        $request_json         = file_get_contents('php://input');
        $request_data         = json_decode($request_json, true);
        $payments_result_data = $this->get_payments_result($request_data['orderId'], $request_data['payeeId']);
        $valid_order_id       = str_replace($this->invoice_prefix, '', $request_data['orderId']);
        $result               = $this->update_order_status($valid_order_id, $payments_result_data);

        ob_clean();
        wp_send_json(['code' => $result['code']], $result['status']);
    }

    public static function check_payments_result($order_id)
    {
        $mixpay_gatway        = (new self());
        $payments_result_data = $mixpay_gatway->get_payments_result($mixpay_gatway->invoice_prefix . $order_id, $mixpay_gatway->payee_uuid);
        $mixpay_gatway->update_order_status($order_id, $payments_result_data);
    }

    public function update_order_status($order_id, $payments_result_data)
    {
        $order  = new WC_Order($order_id);
        $result = ['code' => 'FAIL', 'status' => 500];

        $status_before_update = $order->get_status();

        if ($payments_result_data['status'] == 'pending' && $status_before_update == 'pending') {
            $order->update_status('processing', 'Order is processing.');
        } elseif ($payments_result_data['status'] == 'success' && in_array($status_before_update, ['pending', 'processing'])) {
            $order_quote_amount  = number_format($order->get_total(), 8, '.', '');
            $order_quote_assetid = strtolower($order->get_currency());

            if ($payments_result_data['payeeId'] == $this->payee_uuid
                && $payments_result_data['quoteAssetId'] == $order_quote_assetid
                && $payments_result_data['quoteAmount'] == $order_quote_amount) {
                $order->update_status('completed', 'Order has been paid.');
            } else {
                $order->update_status('failed', 'Order has been failed, reason: Payment Info Is Error.');
            }
            $result = ['code' => 'SUCCESS', 'status' => 200];
        } elseif ($payments_result_data['status'] == 'failed') {
            $order->update_status('cancelled', "Order has been cancelled, reason: {$payments_result_data['failureReason']}.");
        } elseif ($status_before_update == 'cancelled' && $payments_result_data['status'] == 'success') {
            if ($payments_result_data['payeeId'] == $this->payee_uuid
                && $payments_result_data['quoteAssetId'] == $order_quote_assetid
                && $payments_result_data['quoteAmount'] == $order_quote_amount) {
                $order->update_status('processing', 'Order is processing.');
            }
        }

        if (! $order->has_status(['pending', 'processing'])) {
            wp_clear_scheduled_hook('check_payments_result_cron_hook', [$order_id]);
        }

        $this->debug_post_out(
            'update_order_status',
            [
                'payments_result_data'       => $payments_result_data,
                'order_status_before_update' => $status_before_update,
                'order_status_after_update'  => $order->get_status(),
            ]
        );

        return $result;
    }

    public function get_payments_result($order_id, $payee_uuid)
    {
        $response             = wp_remote_get(MIXPAY_PAYMENTS_RESULT . "?orderId={$order_id}&payeeId={$payee_uuid}");
        $payments_result_data = wp_remote_retrieve_body($response);

        return json_decode($payments_result_data, true)['data'];
    }

    public function get_quote_asset_lists()
    {
        $key               = 'mixpay_quote_asset_lists';
        $quote_asset_lists = get_option($key);

        if (isset($quote_asset_lists['expire_time']) && $quote_asset_lists['expire_time'] > time()) {
            return $quote_asset_lists['data'];
        }

        $response      = wp_remote_get(MIXPAY_QUOTE_ASSETS_API);
        $response_data = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_data, true)['data'] ?? [];
        $lists         = array_column($response_data, 'assetId');

        if (! empty($lists)) {
            $quote_asset_lists = $lists;
            update_option($key, ['data' => $lists, 'expire_time' => time() + MIXPAY_ASSETS_EXPIRE_SECONDS]);
        }

        return $quote_asset_lists;
    }

    public function get_settlement_asset_lists()
    {
        $key                    = 'mixpay_settlement_asset_lists';
        $settlement_asset_lists = get_option($key);

        if (isset($settlement_asset_lists['expire_time']) && $settlement_asset_lists['expire_time'] > time()) {
            return $settlement_asset_lists['data'];
        }

        $response      = wp_remote_get(MIXPAY_SETTLEMENT_ASSETS_API);
        $response_data = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_data, true)['data'] ?? [];

        $lists = [];

        foreach ($response_data as $asset) {
            $item_asset_id         = esc_attr($asset['assetId']);
            $lists[$item_asset_id] = empty($asset['network']) ? esc_html($asset['symbol']) : esc_html($asset['symbol'] . ' - ' . $asset['network']);
        }

        if (! empty($lists)) {
            $settlement_asset_lists = $lists;
            update_option($key, ['data' => $lists, 'expire_time' => time() + MIXPAY_ASSETS_EXPIRE_SECONDS]);
        }

        return $settlement_asset_lists;
    }

    public function getRandomString($length = 8)
    {
        $captcha = '';

        for ($i = 0; $i < $length; $i++) {
            $captcha .= chr(mt_rand(65, 90));
        }

        return $captcha;
    }


    public function deal_options($settings)
    {
        if (empty($settings['title'])) {
            WC_Admin_Settings::add_error(esc_html('Title is required'));
        }

        if (empty($settings['mixin_id']) && empty($settings['payee_uuid'])) {
            WC_Admin_Settings::add_error(esc_html('Mixin Id or Payee Uuid is required'));
        }

        if (empty($settings['settlement_asset_id'])) {
            WC_Admin_Settings::add_error(esc_html('Settlement asset is required'));
        }

        if (! empty($settings['store_name']) && ! preg_match('/^[a-zA-Z0-9]+$/u', $settings['store_name'])) {
            WC_Admin_Settings::add_error(esc_html('Store Name must only contain letters and numbers'));
        }

        $mixin_id = $settings['mixin_id'] ?? '';

        if (! empty($mixin_id)) {
            if (strpos($mixin_id, '|') !== false) {
                $receiver_mixin_ids = explode('|', $mixin_id);
                $threshold          = end($receiver_mixin_ids);
                array_pop($receiver_mixin_ids);
                $receiver_uuids = [];

                if (count($receiver_mixin_ids) < $threshold) {
                    WC_Admin_Settings::add_error(esc_html('Multisig threshold not more than the count of receivers'));
                }

                foreach ($receiver_mixin_ids as $mixin_id) {
                    $receiver_uuids[] = esc_html($this->get_mixin_uuid($mixin_id));
                }
                $settings['payee_uuid'] = esc_html($this->get_multisig_id($receiver_uuids, $threshold));
            } else {
                $settings['payee_uuid'] = esc_html($this->get_mixin_uuid($mixin_id));
            }
        }

        if (empty($settings['payee_uuid'])) {
            WC_Admin_Settings::add_error(esc_html('Payee uuid was not obtained, please try again later. (Make sure you have added MixPay robot 7000104220 as a contact in your Mixin wallet)'));
        }

        if (empty($settings['invoice_prefix'])) {
            $settings['invoice_prefix'] = esc_html($this->getRandomString());
        }

        return $settings;
    }

    public function get_mixin_uuid($mixin_id)
    {
        $response      = wp_remote_get(MIXPAY_MIXINUUID_API . "/{$mixin_id}");
        $response_data = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_data, true)['data'] ?? [];

        if (empty($response_data['payeeId'])) {
            WC_Admin_Settings::add_error(esc_html('Mixin uuid was not obtained, please try again later'));
        }

        return $response_data['payeeId'] ?? '';
    }

    public function get_multisig_id($receiver_uuids, $threshold)
    {
        $response = wp_remote_post(MIXPAY_MULTISIG_API, [
            'body' => [
                'receivers' => $receiver_uuids,
                'threshold' => $threshold,
            ],
        ]);

        $response_data = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_data, true)['data'] ?? [];

        return $response_data['multisigId'] ?? '';
    }

    public function post_payment_code($payments_data)
    {
        $response = wp_remote_post(MIXPAY_ONE_TIME_PAYMENT, [
            'body' => $payments_data,
        ]);

        $response_data = wp_remote_retrieve_body($response);
        $result        = json_decode($response_data, true);

        if ($result['success']) {
            return $result['data']['code'];
        } else {
            if ($result['code'] != 0) {
                throw new Exception($result['message']);
            }
        }

        return null;
    }

    public function debug_post_out($key, $datain)
    {
        if ($this->debug) {
            $data = [
                'payee_uuid' => $this->payee_uuid,
                'store_name' => $this->store_name,
                $key         => $datain,
            ];
            wp_remote_post($this->debug, ['body' => $data]);
        }
    }

}

/**
 * Adds Gateway into WooCommerce
 *
 * @param $gateways
 * @return mixed
 */
if ( !function_exists( 'add_mixpay_to_wc' ) ):
    function add_mixpay_to_wc( $gateways ) {
        $gateways[] = 'MPWC_Gateway';
        return $gateways;
    }
endif;

add_filter( 'woocommerce_payment_gateways', 'add_mixpay_to_wc' );
