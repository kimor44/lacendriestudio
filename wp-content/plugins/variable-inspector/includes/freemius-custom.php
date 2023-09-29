<?php

/**
 * Uninstall method from Freemius to replace WP core's default uninstall methods
 * so that uninstallation data is available to Freemius
 */
function bwvi_fs_uninstall_cleanup() 
{
    // Do clean up during uninstallation process here
}
    
bwvi_fs()->add_action('after_uninstall', 'bwvi_fs_uninstall_cleanup');

// Get plugin name which may include premium suffix
// bwvi_fs()->get_plugin_name();

// Get plugin version
// bwvi_fs()->get_plugin_version();
    
/**
 * Update opt-in header on plugin installation
 * 
 * @link https://freemius.com/help/documentation/wordpress-sdk/opt-in-message/
 */
function bwvi_fs_custom_connect_header( $header_html ) 
{
    return '<h2 style="text-align:center;">Help us improve this plugin</h2>';
}
    
bwvi_fs()->add_filter( 'connect-header', 'bwvi_fs_custom_connect_header' );
    
/**
 * Customize opt-in message on installing the plugin for the first time
 * 
 * @link https://freemius.com/help/documentation/wordpress-sdk/opt-in-message/
 */
function bwvi_fs_custom_connect_message( $message, $user_first_name, $product_title, $user_login, $site_link, $freemius_link ) 
{
    return sprintf(
        '%1$s, you can help us make the plugin more compatible with your site and better at doing what you need it to by opting to share some basic WordPress environment info. You\'ll receive an email to verify your consent. <br /><br />If you skip this, that\'s okay! %2$s will still work just fine.',
        $user_first_name,
        $product_title
    );
}
    
bwvi_fs()->add_filter( 'connect_message', 'bwvi_fs_custom_connect_message', 10, 6 );
    
/**
 * Customize opt-in message on updating plugin to a freemius-integrated version
 * 
 * @link https://freemius.com/help/documentation/wordpress-sdk/opt-in-message/
 */
function bwvi_fs_custom_connect_message_on_update( $message, $user_first_name, $product_title, $user_login, $site_link, $freemius_link ) 
{
    return sprintf(
        '%1$s, you can help us make the plugin more compatible with your site and better at doing what you need it to by opting to share some basic WordPress environment info. You\'ll receive an email to verify your consent. <br /><br />If you skip this, that\'s okay! %2$s will still work just fine.',
        $user_first_name,
        $product_title
    );
}
    
bwvi_fs()->add_filter( 'connect_message_on_update', 'bwvi_fs_custom_connect_message_on_update', 10, 6 );