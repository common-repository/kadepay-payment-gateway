<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//// admin interface settings fields ////

return apply_filters( 'wc_kadepay_settings',
	array(
		'enabled' => array(
			'title'       => __( 'Enable/Disable', 'woo-gateway-kadepay' ),
			'label'       => __( 'Enable KadePay', 'woo-gateway-kadepay' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',

		),
		'title' => array(
			'title'       => __( 'Title', 'woo-gateway-kadepay' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'woo-gateway-kadepay' ),
			'default'     => __( 'Credit Card (KadePay)', 'woo-gateway-kadepay' ),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __( 'Description', 'woo-gateway-kadepay' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'woo-gateway-kadepay' ),
			'default'     => __( 'Pay with your credit card via KadePay.', 'woo-gateway-kadepay' ),
			'desc_tip'    => true,
		),
		'testmode' => array(
			'title'       => __( 'Test mode', 'woo-gateway-kadepay' ),
			'label'       => __( 'Enable Test Mode', 'woo-gateway-kadepay' ),
			'type'        => 'checkbox',
			'description' => __( 'Place the payment gateway in test mode using test Merchant ID.', 'woo-gateway-kadepay' ),
			'default'     => 'yes',
			'desc_tip'    => false,
		),
		'sandbox_merchant_id' => array(
			'title'       => __( 'Sandbox Merchant ID', 'woo-gateway-kadepay' ),
			'type'        => 'text',
			'description' => __( 'Get your Merchant ID from your KadePay account.', 'woo-gateway-kadepay' ),
			'default'     => '',
			'desc_tip'    => true,
		),

		'live_merchant_id' => array(
			'title'       => __( 'Live Merchant ID', 'woo-gateway-kadepay' ),
			'type'        => 'text',
			'description' => __( 'Get your Merchant ID from your KadePay account.', 'woo-gateway-kadepay' ),
			'default'     => '',
			'desc_tip'    => true,
		)
	)
);
