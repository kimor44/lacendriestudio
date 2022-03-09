<?php
/**
* La Cendrie Studio functions and definitions
*
* @package LaCendrieStudio
* @since LaCendrieStudio 1.0
*/

function cendrie_add_theme_scripts() {
    /* Enqueue styles */
    wp_enqueue_style( 'style', get_stylesheet_uri() );
    /* others stylesheets
      wp_enqueue_style( 'name_of_file', get_template_directory_uri() . '/assets/css/name_of_file.css', array(), '1.1', 'all' );
    */
    
    /* Enqueue scripts 
      wp_enqueue_script( 'script', get_template_directory_uri() . '/assets/js/script.js', array ( 'jquery' ), 1.1, true);
    */

    /* conditional loading script
      if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
      }
    */
    
}
add_action( 'wp_enqueue_scripts', 'cendrie_add_theme_scripts' );

/*
Set maximum content width to 800 pixels
if ( ! isset( $content_width ) )
    $content_width = 800; // pixels
*/

if ( ! function_exists( 'lacendriestudio_setup' ) ) :
  function lacendriestudio_setup() {
    /**
     * Enable support for post thumbnails and featured images.
    */
    add_theme_support( 'post-thumbnails' );

    /**
     * Enable support for the following post formats:
     * aside, gallery, quote, image, and video
     */
    add_theme_support( 'post-formats',  array ( 'aside', 'gallery', 'quote', 'image', 'video' ) );
  }
endif;