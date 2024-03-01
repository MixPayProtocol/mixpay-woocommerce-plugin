
import { sprintf, __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'mixpay_gateway_data', {} );
const title = settings.title || 'MixPay Payment';
const defaultLabel = __(
	title,
	'mpwc'
);

const label = decodeEntities( title ) || defaultLabel;
/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || 'Expand your payment options with MixPay! BTC, ETH, LTC and many more: pay with anything you like!' );
};
/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components;
	return <PaymentMethodLabel text={ label } />;
};

/**
 * MixPay method config object.
 */
const gatewayConfig = {
	name: "mixpay_gateway",
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( gatewayConfig );
