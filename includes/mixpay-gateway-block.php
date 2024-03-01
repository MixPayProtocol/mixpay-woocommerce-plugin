<?php
defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * MixPay Blocks integration
 */
final class MixPayGatewayBlock extends AbstractPaymentMethodType {

	/**
	 * The gateway instance.
	 *
	 * @var MPWC_Gateway
	 */
	private $gateway;

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'mixpay_gateway';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_mixpay_settings', [] );
		$this->gateway  = new MPWC_Gateway();
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {

		$script_path       = '/assets/blocks/frontend/blocks.js';
		$script_asset_path = MPWC_PLUGIN_DIR_PATH . '/assets/blocks/frontend/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => MPWC_VERSION
			);
		$script_url        = MPWC_PLUGIN_URL . $script_path;

		wp_register_script(
			'mpwc-checkout-block',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		return [ 'mpwc-checkout-block' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
		];
	}
}