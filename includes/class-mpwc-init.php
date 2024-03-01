<?php

class MPWC_Init {

    private static $_instance;

    public static function get_instance() {
        if( self::$_instance == null ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct() {
        $this->validate();
    }

    public function validate() {

        if( !function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            $this->init();
        }
        else {
            add_action( 'admin_notices', array( $this, 'missing_wc' ) );
        }
    }

    /**
     * Shows Notice
     */
    public function missing_wc() {

        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'In order to use MixPay Gateway for WooCommerce, make sure WooCommerce is installed and active.', 'sample-text-domain' ); ?></p>
        </div>
        <?php

    }

    /**
     * Finally initialize the Plugin :)
     */
    private function init() {

		add_action( 'woocommerce_blocks_loaded', array( $this, 'checkout_block_support' ) );

        $this->includes();
        $this->hooks();

    }

    /**
     * Includes files
     */
    public function includes() {

        require 'class-gateway.php';

    }

    /**
     * Action, Filter Hooks
     */
    public function hooks() {

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

    }

    public function admin_enqueue_scripts() {
        wp_enqueue_script(
            'mpwc-custom-scripts',
            MPWC_PLUGIN_URL . 'assets/js/scripts.js',
            array( 'jquery' ),
            MPWC_VERSION,
            true
        );
    }

    /**
	 * Registers WooCommerce Blocks integration.
	 */
	public function checkout_block_support() {
		
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			
			require_once 'mixpay-gateway-block.php';
	
			add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'register_checkout_block' ) );
		}
	}

	public function register_checkout_block( $payment_method_registry ) {
		$payment_method_registry->register( new MixPayGatewayBlock );
	}
}
