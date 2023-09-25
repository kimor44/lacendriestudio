<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
function wbk_woocommerce_checkout_fields( $fields )
{
    if ( get_option( 'wbk_woo_prefil_fields', '' ) != 'true' ) {
        return $fields;
    }
    if ( !session_id() ) {
        session_start();
    }
    if ( isset( $_SESSION['wbk_name'] ) ) {
        $fields['billing']['billing_first_name']['default'] = $_SESSION['wbk_name'];
    }
    if ( isset( $_SESSION['wbk_last_name'] ) ) {
        $fields['billing']['billing_last_name']['default'] = $_SESSION['wbk_last_name'];
    }
    if ( isset( $_SESSION['wbk_email'] ) ) {
        $fields['billing']['billing_email']['default'] = $_SESSION['wbk_email'];
    }
    if ( isset( $_SESSION['wbk_email'] ) ) {
        $fields['billing']['billing_phone']['default'] = $_SESSION['wbk_phone'];
    }
    return $fields;
}

function wbK_woocommerce_coupon_is_valid( $value, $coupon, $discounts )
{
    foreach ( $discounts->get_items() as $item ) {
        
        if ( isset( $item->object['wbk_appointment_ids'] ) ) {
            $booking_ids = explode( ',', $item->object['wbk_appointment_ids'] );
            foreach ( $booking_ids as $booking_id ) {
                $booking = new WBK_Booking( $booking_id );
                if ( !$booking->is_loaded() ) {
                    continue;
                }
                if ( !WBK_Validator::check_Coupon( $coupon->get_code(), array( $booking->get_service() ) ) ) {
                    $value = false;
                }
            }
        }
    
    }
    return $value;
}

function wbk_delete_order_item( $item_id )
{
    $order_item = new WC_Order_Item_Product( $item_id );
    
    if ( $order_item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
        if ( get_option( 'wbk_woo_product_id', '' ) == '' ) {
            return;
        }
        $item_meta = wc_get_order_item_meta( $item_id, 'IDs', true );
        if ( $item_meta == '' ) {
            return;
        }
        $booking_ids = explode( ',', $item_meta );
        
        if ( get_option( 'wbk_appointments_default_status', 'approved' ) == 'approved' ) {
            $status = 'approved';
        } else {
            $status = 'pending';
        }
        
        foreach ( $booking_ids as $booking_id ) {
            $booking = new WBK_Booking( $booking_id );
            if ( !$booking->is_loaded() ) {
                continue;
            }
            $booking->set( 'status', $status );
            $booking->set( 'payment_method', '' );
            $booking->set( 'payment_id', '' );
            $booking->save();
        }
    }

}

function wbk_order_deleted( $order_id )
{
    global  $post_type ;
    if ( $post_type !== 'shop_order' ) {
        return;
    }
    $order = new WC_Order( $order_id );
    $booking_ids = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        
        if ( $item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
            $booking_ids_this = explode( ',', wc_get_order_item_meta( $item_id, 'IDs', true ) );
            $booking_ids = array_merge( $booking_ids, $booking_ids_this );
        }
    
    }
    
    if ( get_option( 'wbk_appointments_default_status', 'approved' ) == 'approved' ) {
        $status = 'approved';
    } else {
        $status = 'pending';
    }
    
    foreach ( $booking_ids as $booking_id ) {
        $booking = new WBK_Booking( $booking_id );
        if ( !$booking->is_loaded() ) {
            continue;
        }
        $booking->set( 'status', $status );
        $booking->set( 'payment_method', '' );
        $booking->set( 'payment_id', '' );
        $booking->save();
    }
}

function wbk_order_cancelled_refunded( $order_id )
{
    global  $post_type ;
    if ( $post_type !== 'shop_order' ) {
        return;
    }
    $order = new WC_Order( $order_id );
    $booking_ids = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        
        if ( $item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
            $booking_ids_this = explode( ',', wc_get_order_item_meta( $item_id, 'IDs', true ) );
            $booking_ids = array_merge( $booking_ids, $booking_ids_this );
        }
    
    }
    
    if ( get_option( 'wbk_appointments_default_status', 'approved' ) == 'approved' ) {
        $status = 'approved';
    } else {
        $status = 'pending';
    }
    
    foreach ( $booking_ids as $booking_id ) {
        $booking = new WBK_Booking( $booking_id );
        if ( !$booking->is_loaded() ) {
            continue;
        }
        $booking->set( 'status', $status );
        $booking->set( 'payment_method', '' );
        $booking->set( 'payment_id', '' );
        $booking->save();
    }
}

function wbk_add_booking_data_to_cart_item( $cart_item_data, $product_id, $variation_id )
{
    return $cart_item_data;
}

function wbk_display_booking_data_text_cart( $item_data, $cart_item )
{
    date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
    if ( empty($cart_item['wbk_appointment_ids']) ) {
        return $item_data;
    }
    $booking_ids = explode( ',', wc_clean( $cart_item['wbk_appointment_ids'] ) );
    if ( count( $booking_ids ) == 0 ) {
        return $item_data;
    }
    $payment_details = WBK_Price_Processor::get_payment_items( $booking_ids, 0 );
    $booking_order_text = get_option( 'wbk_woo_cart_title', '' );
    
    if ( $booking_order_text != '' ) {
        $order_text = WBK_Placeholder_Processor::process_placeholders( $booking_order_text, $booking_ids );
    } else {
        $order_text = implode( ',', $payment_details['item_names'] );
    }
    
    $meta_key = wbk_get_translation_string( 'wbk_product_meta_key', 'wbk_product_meta_key', 'Appointments' );
    $item_data[] = array(
        'key'     => $meta_key,
        'value'   => $order_text,
        'display' => '',
    );
    date_default_timezone_set( 'UTC' );
    return $item_data;
}

function wbk_calculate_booking_product_price( $cart_object )
{
    if ( !WC()->session->__isset( "reload_checkout" ) ) {
        foreach ( $cart_object->cart_contents as $key => $value ) {
            $prod_id = $value['data']->get_id();
            if ( $prod_id == get_option( 'wbk_woo_product_id', '' ) ) {
                
                if ( isset( $value['wbk_appointment_ids'] ) ) {
                    $booking_ids = explode( ',', $value['wbk_appointment_ids'] );
                    $payment_details = WBK_Price_Processor::get_payment_items( $booking_ids, 0 );
                    $price = $payment_details['total'];
                    $credits_amount = get_post_meta( $prod_id, '_credits_amount', true );
                    if ( !$credits_amount ) {
                        $value['data']->set_price( $price );
                    }
                    if ( $price == 0 ) {
                        $cart_object->remove_cart_item( $key );
                    }
                }
            
            }
        }
    }
}

function wbk_add_booking_text_to_order_items(
    $item,
    $cart_item_key,
    $values,
    $order
)
{
    if ( empty($values['wbk_appointment_ids']) ) {
        return;
    }
    date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
    $booking_ids = explode( ',', wc_clean( $values['wbk_appointment_ids'] ) );
    $payment_details = WBK_Price_Processor::get_payment_items( $booking_ids, 0 );
    $booking_order_text = get_option( 'wbk_woo_cart_title', '' );
    
    if ( $booking_order_text != '' ) {
        $order_text = WBK_Placeholder_Processor::process_placeholders( $booking_order_text, $booking_ids );
    } else {
        $order_text = implode( ',', $payment_details['item_names'] );
    }
    
    $meta_key = wbk_get_translation_string( 'wbk_product_meta_key', 'wbk_product_meta_key', 'Appointments' );
    $item->add_meta_data( $meta_key, $order_text );
    $item->add_meta_data( 'IDs', $values['wbk_appointment_ids'] );
    date_default_timezone_set( 'UTC' );
}

function wbk_woocommerce_payment_complete( $order_id )
{
    $default_value = array( 'complete_status', 'thankyou_message' );
    $complete_action = get_option( 'wbk_woo_complete_action', $default_value );
    if ( !in_array( 'complete_status', $complete_action ) ) {
        return;
    }
    $order = wc_get_order( $order_id );
    $booking_ids = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        if ( !is_object( $item ) ) {
            continue;
        }
        
        if ( $item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
            $booking_ids_this = explode( ',', wc_get_order_item_meta( $item_id, 'IDs', true ) );
            $booking_ids = array_merge( $booking_ids, $booking_ids_this );
        }
    
    }
    $update_status = get_option( 'wbk_woo_update_status', 'paid' );
    if ( $update_status == 'disabled' ) {
        $update_status = 'woocommerce';
    }
    
    if ( count( $booking_ids ) > 0 ) {
        $bf = new WBK_Booking_Factory();
        $bf->set_as_paid( $booking_ids, 'woocommerce' );
        do_action( 'wbk_woocommerce_order_placed', $booking_ids, $order_id );
    }

}

function wbk_woocommerce_thankyou( $order_id )
{
    $default_value = array( 'complete_status', 'thankyou_message' );
    $complete_action = get_option( 'wbk_woo_complete_action', $default_value );
    if ( !in_array( 'thankyou_message', $complete_action ) ) {
        return;
    }
    $order = wc_get_order( $order_id );
    $booking_ids = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        if ( !is_object( $item ) ) {
            continue;
        }
        
        if ( $item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
            $booking_ids_this = explode( ',', wc_get_order_item_meta( $item_id, 'IDs', true ) );
            $booking_ids = array_merge( $booking_ids, $booking_ids_this );
        }
    
    }
    $update_status = get_option( 'wbk_woo_update_status', 'paid' );
    if ( $update_status == 'disabled' ) {
        $update_status = 'woocommerce';
    }
    $bf = new WBK_Booking_Factory();
    $bf->set_as_paid( $booking_ids, 'woocommerce' );
    do_action( 'wbk_woocommerce_order_thankyou', $booking_ids, $order_id );
}

class WBK_WooCommerce
{
    static function add_to_cart( $booking_ids )
    {
        return json_encode( array(
            'status'  => 0,
            'details' => __( 'Payment method not supported', 'webba-booking-lite' ),
        ) );
    }
    
    public static function render_initial_form(
        $input,
        $payment_method,
        $booking_ids,
        $button_class
    )
    {
        if ( $payment_method == 'woocommerce' ) {
            return $input .= WBK_Renderer::load_template( 'frontend/woocommerce_init', array( $booking_ids, $button_class ), false );
        }
        return $input;
    }

}
function wbk_woocommerce_coupon_options_usage_restriction( $coupon_id, $coupon )
{
    if ( !$coupon->is_type( array( 'percent' ) ) ) {
        return;
    }
    $options = WBK_Model_Utils::get_services( true );
    wbk_woocommerce_wp_multi_select( array(
        'id'      => 'webba_services',
        'name'    => 'webba_services[]',
        'label'   => __( 'Webba Booking services', 'webba-booking-lite' ),
        'options' => $options,
    ) );
    $options = WBK_Model_Utils::get_pricing_rules( true );
    wbk_woocommerce_wp_multi_select( array(
        'id'      => 'webba_pricing_rules',
        'name'    => 'webba_pricing_rules[]',
        'label'   => __( 'Webba Booking pricing rules', 'webba-booking-lite' ),
        'options' => $options,
    ) );
}

function wbk_woocommerce_coupon_options_save( $post_id )
{
    $coupon = new WC_Coupon( $post_id );
    if ( !$coupon->is_type( array( 'percent' ) ) ) {
        return;
    }
    $ids = $_POST['webba_services'];
    $options = WBK_Model_Utils::get_services( true );
    $validated = array();
    foreach ( $ids as $id ) {
        if ( array_key_exists( $id, $options ) ) {
            $validated[] = $id;
        }
    }
    update_post_meta( $post_id, 'webba_services', $validated );
    $ids = $_POST['webba_pricing_rules'];
    $options = WBK_Model_Utils::get_pricing_rules( true );
    $validated = array();
    foreach ( $ids as $id ) {
        if ( array_key_exists( $id, $options ) ) {
            $validated[] = $id;
        }
    }
    update_post_meta( $post_id, 'webba_pricing_rules', $validated );
}

function wbk_woocommerce_wp_multi_select( $field, $variation_id = 0 )
{
    global  $thepostid, $post ;
    
    if ( $variation_id == 0 ) {
        $the_id = ( empty($thepostid) ? $post->ID : $thepostid );
    } else {
        $the_id = $variation_id;
    }
    
    $field['class'] = ( isset( $field['class'] ) ? $field['class'] : 'select short' );
    $field['wrapper_class'] = ( isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '' );
    $field['name'] = ( isset( $field['name'] ) ? $field['name'] : $field['id'] );
    $meta_data = maybe_unserialize( get_post_meta( $the_id, $field['id'], true ) );
    $meta_data = ( $meta_data ? $meta_data : array() );
    $field['value'] = ( isset( $field['value'] ) ? $field['value'] : $meta_data );
    echo  '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" multiple="multiple">' ;
    foreach ( $field['options'] as $key => $value ) {
        echo  '<option value="' . esc_attr( $key ) . '" ' . (( in_array( $key, $field['value'] ) ? 'selected="selected"' : '' )) . '>' . esc_html( $value ) . '</option>' ;
    }
    echo  '</select> ' ;
    if ( !empty($field['description']) ) {
        
        if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
            echo  '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />' ;
        } else {
            echo  '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>' ;
        }
    
    }
}

function wbk_woocommerce_coupon_get_discount_amount(
    $discount_amount,
    $discounting_amount,
    $cart_item,
    $single,
    $coupon
)
{
    if ( empty($cart_item['wbk_appointment_ids']) ) {
        return $discount_amount;
    }
    $allowed_services = array();
    $allowed_pricing_rules = array();
    if ( is_array( $coupon->get_meta( 'webba_services' ) ) ) {
        $allowed_services = $coupon->get_meta( 'webba_services' );
    }
    if ( is_array( $coupon->get_meta( 'webba_pricing_rules' ) ) ) {
        $allowed_pricing_rules = $coupon->get_meta( 'webba_pricing_rules' );
    }
    if ( count( $allowed_services ) == 0 && count( $allowed_pricing_rules ) == 0 ) {
        return $discount_amount;
    }
    $coupon_amount = $coupon->get_amount();
    $booking_ids = explode( ',', wc_clean( $cart_item['wbk_appointment_ids'] ) );
    $overriden_discount_amount = 0;
    foreach ( $booking_ids as $booking_id ) {
        $booking = new WBK_Booking( $booking_id );
        
        if ( $booking->get_amount_details() != '' ) {
            $amount_details = json_decode( $booking->get_amount_details(), true );
            if ( $amount_details == null || !is_array( $amount_details ) ) {
                continue;
            }
            foreach ( $amount_details as $item ) {
                switch ( $item['type'] ) {
                    case 'service_price':
                        
                        if ( in_array( $booking->get_service(), $allowed_services ) ) {
                            $amount = $item['amount'] * $booking->get_quantity();
                            $overriden_discount_amount += $amount * ($coupon_amount / 100);
                        }
                        
                        break;
                    case 'pricing_rule':
                        
                        if ( in_array( $item['rule_id'], $allowed_pricing_rules ) ) {
                            if ( $item['amount'] < 0 ) {
                                break;
                            }
                            $amount = $item['amount'] * $booking->get_quantity();
                            $overriden_discount_amount += $amount * ($coupon_amount / 100);
                        }
                        
                        break;
                }
            }
        }
    
    }
    return $overriden_discount_amount;
}
