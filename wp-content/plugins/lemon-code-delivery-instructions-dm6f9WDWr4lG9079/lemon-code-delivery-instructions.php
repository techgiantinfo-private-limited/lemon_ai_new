<?php
/**
 * Plugin Name:       Lemon Code Delivery Instructions
 * Description:       Adds an optional delivery instructions field to Classic and Block checkout and shows it in the admin order screen, order emails and the customer order details page.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Lemon Code
 * License:           GPL-2.0-or-later
 * Text Domain:       lemon-code-delivery-instructions
 * Requires Plugins:  woocommerce
 */

defined( 'ABSPATH' ) || exit;

// Guard against a double include (function_exists() cannot be used here: PHP
// declares this file's functions before running it, so the check is always true).
if ( defined( 'LCDI_PLUGIN_LOADED' ) ) {
	return;
}
define( 'LCDI_PLUGIN_LOADED', true );

// WooCommerce Blocks stores order-location additional fields under this prefix.
const LCDI_BLOCK_META_KEY   = '_wc_other/lemon-code/delivery-instructions';
const LCDI_CLASSIC_META_KEY = '_delivery_instructions';
const LCDI_MAX_LENGTH       = 500;

/**
 * Normalise a raw submitted value (already slashed for POST input).
 */
function lcdi_sanitize_instructions( $raw ) {
	if ( ! is_string( $raw ) ) {
		return '';
	}

	$value = sanitize_textarea_field( wp_unslash( $raw ) );
	$value = function_exists( 'mb_substr' )
		? mb_substr( $value, 0, LCDI_MAX_LENGTH )
		: substr( $value, 0, LCDI_MAX_LENGTH );

	return trim( $value );
}

/**
 * Value saved by Block checkout (WooCommerce Blocks owns these meta keys).
 */
function lcdi_get_block_instructions( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return '';
	}

	$value = $order->get_meta( LCDI_BLOCK_META_KEY );

	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

/**
 * Value saved by Classic checkout.
 */
function lcdi_get_classic_instructions( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return '';
	}

	$value = $order->get_meta( LCDI_CLASSIC_META_KEY );

	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

/**
 * Get delivery instructions from an order, whatever checkout was used.
 */
function lcdi_get_delivery_instructions( $order ) {
	$value = lcdi_get_block_instructions( $order );

	if ( '' !== $value ) {
		return $value;
	}

	return lcdi_get_classic_instructions( $order );
}

/**
 * Value we should render ourselves.
 *
 * WooCommerce Blocks already outputs order-location fields in the admin order
 * screen and on the customer order details page, so we only render the value
 * it does not own (i.e. the Classic checkout one).
 */
function lcdi_get_own_instructions( $order ) {
	if ( '' !== lcdi_get_block_instructions( $order ) ) {
		return '';
	}

	return lcdi_get_classic_instructions( $order );
}

/**
 * CLASSIC CHECKOUT: display the textarea.
 */
function lcdi_render_classic_field( $checkout ) {
	$value = '';

	if ( isset( $_POST['delivery_instructions'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the checkout nonce.
		$value = lcdi_sanitize_instructions( $_POST['delivery_instructions'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	if ( '' === $value && $checkout instanceof WC_Checkout ) {
		$value = (string) $checkout->get_value( 'delivery_instructions' );
	}

	woocommerce_form_field(
		'delivery_instructions',
		array(
			'type'              => 'textarea',
			'label'             => __( 'Delivery Instructions', 'lemon-code-delivery-instructions' ),
			'placeholder'       => __( 'Optional - Add delivery instructions', 'lemon-code-delivery-instructions' ),
			'required'          => false,
			'class'             => array( 'form-row-wide' ),
			'clear'             => true,
			'maxlength'         => LCDI_MAX_LENGTH,
			'custom_attributes' => array(
				'rows'      => 3,
				'maxlength' => LCDI_MAX_LENGTH,
			),
		),
		$value
	);
}
add_action( 'woocommerce_after_order_notes', 'lcdi_render_classic_field' );

/**
 * CLASSIC CHECKOUT: save the field onto the order.
 */
function lcdi_save_classic_field( $order ) {
	if ( ! $order instanceof WC_Order || ! isset( $_POST['delivery_instructions'] ) ) {
		return;
	}

	$value = lcdi_sanitize_instructions( $_POST['delivery_instructions'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the checkout nonce.

	if ( '' === $value ) {
		$order->delete_meta_data( LCDI_CLASSIC_META_KEY );
	} else {
		$order->update_meta_data( LCDI_CLASSIC_META_KEY, $value );
	}
}
add_action( 'woocommerce_checkout_create_order', 'lcdi_save_classic_field' );

/**
 * BLOCK CHECKOUT: enforce the same 500 character limit as Classic checkout.
 */
function lcdi_validate_block_value( $value ) {
	if ( is_string( $value ) && mb_strlen( $value ) > LCDI_MAX_LENGTH ) {
		return new WP_Error(
			'lcdi_instructions_too_long',
			__( 'Delivery instructions must be 500 characters or fewer.', 'lemon-code-delivery-instructions' )
		);
	}

	return true;
}

/**
 * BLOCK CHECKOUT: normalise the submitted value.
 */
function lcdi_sanitize_block_value( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$value = function_exists( 'mb_substr' )
		? mb_substr( $value, 0, LCDI_MAX_LENGTH )
		: substr( $value, 0, LCDI_MAX_LENGTH );

	return trim( sanitize_text_field( $value ) );
}

/**
 * BLOCK CHECKOUT: register the additional field.
 */
function lcdi_register_block_checkout_field() {
	if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		return;
	}

	woocommerce_register_additional_checkout_field(
		array(
			'id'                => 'lemon-code/delivery-instructions',
			'label'             => __( 'Delivery Instructions', 'lemon-code-delivery-instructions' ),
			'location'          => 'order',
			'type'              => 'text',
			'required'          => false,
			'attributes'        => array(
				'maxLength' => LCDI_MAX_LENGTH,
			),
			'sanitize_callback' => 'lcdi_sanitize_block_value',
			'validate_callback' => 'lcdi_validate_block_value',
		)
	);
}
add_action( 'woocommerce_init', 'lcdi_register_block_checkout_field' );

/**
 * ADMIN: show the value for Classic checkout orders.
 *
 * Block checkout orders are rendered by WooCommerce Blocks in the shipping
 * address meta box, so we skip them to avoid printing the value twice.
 */
function lcdi_display_admin_instructions( $order ) {
	$value = lcdi_get_own_instructions( $order );

	if ( '' === $value ) {
		return;
	}

	echo '<p><strong>' . esc_html__( 'Delivery Instructions', 'lemon-code-delivery-instructions' ) . '</strong><br>';
	echo nl2br( esc_html( $value ) );
	echo '</p>';
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'lcdi_display_admin_instructions' );

/**
 * EMAIL: add the instructions to order emails.
 *
 * WooCommerce Blocks does not add order fields to emails, so both sources are shown.
 */
function lcdi_add_email_instructions( $fields, $sent_to_admin, $order ) {
	$value = lcdi_get_delivery_instructions( $order );

	if ( '' !== $value ) {
		$fields['lcdi_delivery_instructions'] = array(
			'label' => __( 'Delivery Instructions', 'lemon-code-delivery-instructions' ),
			'value' => nl2br( esc_html( $value ) ),
		);
	}

	return $fields;
}
add_filter( 'woocommerce_email_order_meta_fields', 'lcdi_add_email_instructions', 10, 3 );

/**
 * CUSTOMER: show the value for Classic checkout orders.
 *
 * WooCommerce Blocks renders Block checkout fields on this page itself.
 */
function lcdi_display_order_instructions( $order ) {
	$value = lcdi_get_own_instructions( $order );

	if ( '' === $value ) {
		return;
	}

	echo '<h2>' . esc_html__( 'Delivery Instructions', 'lemon-code-delivery-instructions' ) . '</h2>';
	echo '<p>' . nl2br( esc_html( $value ) ) . '</p>';
}
add_action( 'woocommerce_order_details_after_order_table', 'lcdi_display_order_instructions' );

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage.
 */
function lcdi_declare_hpos_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
}
add_action( 'before_woocommerce_init', 'lcdi_declare_hpos_compatibility' );
