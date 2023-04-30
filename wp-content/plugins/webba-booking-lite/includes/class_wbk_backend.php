<?php

// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
include 'backend/class_wbk_backend_options.php';
include 'backend/class_wbk_backend_schedule.php';
require WP_WEBBA_BOOKING__PLUGIN_DIR . '/deprecated/solo-framework/solo-framework.php';
// define main backend class
class WBK_Backend
{
    // 	available components of backend (based on files in classes folder)
    private  $components ;
    public function __construct()
    {
        //add action for wp menu construction
        add_action( 'admin_menu', array( $this, 'createAdminMenu' ) );
        //set components of backend
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action(
            'in_plugin_update_message-webba-booking/webba-booking-lite.php',
            array( $this, 'prefix_plugin_update_message' ),
            10,
            2
        );
        add_action(
            'in_plugin_update_message-webba-booking-lite/webba-booking-lite.php',
            array( $this, 'prefix_plugin_update_message' ),
            10,
            2
        );
        $backend_schedule = new WBK_Backend_Schedule();
        $backend_options = new WBK_Backend_Options();
        $slf = new SoloFramework( 'wbk_settings_data' );
        
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-appearance' ) {
            slf_register_actions();
            $slf->loadSectionAssets( 'wbk_extended_appearance_options' );
        }
    
    }
    
    public function prefix_plugin_update_message( $data, $response )
    {
        
        if ( isset( $data['upgrade_notice'] ) ) {
            $message = str_replace( array( '<p>', '</p>' ), array( '<div>', '</div>' ), $data['upgrade_notice'] );
            echo  '<style type="text/css">
			#webba-booking-lite-update .update-message p:not(:first-child){
				display: none;
			}
            </style>' ;
        }
    
    }
    
    public function settings_updated()
    {
        
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
            date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
            $time_corr = intval( get_option( 'wbk_email_admin_daily_time', '68400' ) );
            $midnight = strtotime( 'today midnight' );
            $timestamp = strtotime( 'today midnight' ) + $time_corr;
            if ( $timestamp < time() ) {
                $timestamp += 86400;
            }
            wp_clear_scheduled_hook( 'wbk_daily_event' );
            wp_schedule_event( $timestamp, 'daily', 'wbk_daily_event' );
            date_default_timezone_set( 'UTC' );
        }
    
    }
    
    public function inline_upload_enquene()
    {
        wp_enqueue_script( 'wp-tinymce' );
        // add common css
        if ( isset( $_GET['page'] ) && ($_GET['page'] == 'wbk-options' || $_GET['page'] == 'wbk-schedule' || $_GET['page'] == 'wbk-gg-calendars' || $_GET['page'] == 'wbk-forms') ) {
        }
    }
    
    public function createAdminMenu()
    {
        global  $current_user ;
        
        if ( current_user_can( 'manage_options' ) || WBK_Validator::checkAccessToSchedule() || WBK_Validator::checkAccessToGgCalendarPage() ) {
            $root_name = __( 'Webba Booking', 'wbk' );
            $root_name = apply_filters( 'wbk_root_menu_title', $root_name );
            add_menu_page(
                $root_name,
                $root_name,
                'read',
                'wbk-main',
                array( 'WBK_Renderer', 'render_backend_page' ),
                WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/webba-booking.png'
            );
            add_submenu_page(
                'wbk-main',
                __( 'Appearance', 'wbk' ),
                __( 'Appearance', 'wbk' ),
                'manage_options',
                'wbk-appearance',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            add_submenu_page(
                'wbk-main',
                __( 'Services', 'wbk' ),
                __( 'Services', 'wbk' ),
                'manage_options',
                'wbk-services',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            add_submenu_page(
                'wbk-main',
                __( 'Service categories', 'wbk' ),
                __( 'Service categories', 'wbk' ),
                'manage_options',
                'wbk-service-categories',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            add_submenu_page(
                'wbk-main',
                __( 'Appointments', 'wbk' ),
                __( 'Appointments', 'wbk' ),
                'read',
                'wbk-appointments',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            add_submenu_page(
                'wbk-main',
                __( 'Schedule', 'wbk' ),
                __( 'Schedule', 'wbk' ),
                'read',
                'wbk-schedule',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            add_submenu_page(
                'wbk-main',
                __( 'Email templates', 'wbk' ),
                __( 'Email templates', 'wbk' ),
                'manage_options',
                'wbk-email-templates',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            add_submenu_page(
                'wbk-main',
                __( 'Coupons', 'wbk' ),
                __( 'Coupons', 'wbk' ),
                'read',
                'wbk-coupons',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            add_submenu_page(
                'wbk-main',
                __( 'Pricing rules', 'wbk' ),
                __( 'Pricing rules', 'wbk' ),
                'read',
                'wbk-pricing-rules',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            $hook = add_submenu_page(
                'wbk-main',
                __( 'Settings', 'wbk' ),
                __( 'Settings', 'wbk' ),
                'manage_options',
                'wbk-options',
                array( 'WBK_Renderer', 'render_backend_page' )
            );
            add_action( 'load-' . $hook, array( $this, 'settings_updated' ) );
            global  $submenu ;
            unset( $submenu['wbk-main'][0] );
        }
    
    }
    
    protected function is_edit_page( $new_edit = null )
    {
        global  $pagenow ;
        //make sure we are on the backend
        if ( !is_admin() ) {
            return false;
        }
        
        if ( $new_edit == 'edit' ) {
            return in_array( $pagenow, array( 'post.php' ) );
        } elseif ( $new_edit == 'new' ) {
            return in_array( $pagenow, array( 'post-new.php' ) );
        } else {
            return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
        }
    
    }
    
    public function admin_notices()
    {
        echo  WBK_Admin_Notices::labelUpdate() ;
        echo  WBK_Admin_Notices::appearanceUpdate() ;
        echo  WBK_Admin_Notices::emailLandingUpdate() ;
        echo  WBK_Admin_Notices::stripe_fields_update_norice() ;
        echo  WBK_Admin_Notices::wbk_4_0_update() ;
        echo  WBK_Admin_Notices::sms_compability() ;
        echo  WBK_Admin_Notices::stripe_conflict() ;
    }

}