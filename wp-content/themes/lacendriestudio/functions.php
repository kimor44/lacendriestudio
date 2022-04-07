<?php
/**
* La Cendrie Studio functions and definitions
*
* @package LaCendrieStudio
* @since LaCendrieStudio 1.0
*/

/*
* Remove useless image sizes
*/
function cendrie_remove_extra_image_sizes() {
  foreach ( get_intermediate_image_sizes() as $size ) {
    if ( in_array( $size, array( '1536x1536', '2048x2048' ) ) ) {
      remove_image_size( $size );
    }
  }
}
add_action('init', 'cendrie_remove_extra_image_sizes');

function cendrie_add_theme_scripts() {
    /* Enqueue styles */
    wp_enqueue_style( 'style', get_stylesheet_uri(), array(), time(), 'all' );
    // others stylesheets
    wp_enqueue_style( 'main', get_template_directory_uri() . '/assets/css/main.css', array(), time(), 'all' );
    wp_enqueue_style( 'main.prod', get_template_directory_uri() . '/assets/css/main.prod.css', array(), time(), 'all' );
    
    
    /* Enqueue scripts */
    wp_enqueue_script( 'script', get_template_directory_uri() . '/node_modules/tw-elements/dist/js/index.min.js', array(), time(), true);


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

if ( ! function_exists( 'cendrie_custom_header_setup' ) ) {
  function cendrie_custom_header_setup() {
    /**
     * Enable support for post thumbnails and featured images.
    */
    add_theme_support( 'post-thumbnails' );
    // Add new image size
    add_image_size( 'cendrie_large_size', 1200, 630 );

    /**
     * Enable support for the following post formats:
     * aside, gallery, quote, image, and video
     */
    add_theme_support( 'post-formats',  array ( 'aside', 'gallery', 'quote', 'image', 'video' ) );
    
    // Custom background
    $args_cbg = array(
      'default-color' => '000001',
    );
    add_theme_support( 'custom-background', $args_cbg );

    // Custom header image
    $args_ch = array(
      'default-image' => get_template_directory_uri() . '/assets/images/logo_la_cendrie.jpg',
      'header-text' => false,
      'width' => 800,
      'height' => 200,
      'flex-width' => true,
      'flex-height' => true,
      'uploads' => true,
    );
    add_theme_support( 'custom-header', $args_ch );
  }
}  
add_action( 'after_setup_theme', 'cendrie_custom_header_setup' );

// Add classes to the_content() hook
function cendrie_replace_content( $text_content ) {
    if ( is_page() ) {
      $text = array(
        '<p>' => '<p class="mt-4 md:mt-5 lg:mt-6 xl:mt-8 2xl:mt-10">',
        '<figure class="' => '<figure class="mb-4 md:mb-5 lg:mb-6 xl:mb-8 2xl:mb-10 ',
      );

      $text_content = str_ireplace( array_keys( $text ), $text, $text_content );
    }

    if ( is_front_page() ) {
      /**
       * Parts to handle (filter) HTML elements & attributes passed in the_content() function
       * Don't hesitate to take a look in the wp_kses_allowed_html function
       * in the file : wp-includes/kses.php
       */

      // Returns an array of allowed HTML tags and attributes for a given context.
      $allowedHtmltags = wp_kses_allowed_html('post');
      // set the allowed attributes for <img> tag
      $allowedHtmltags['img'] = array('alt' => true, 'id' => true, 'src' => true, 'srcset' => true, 'sizes' => true);
      // Filter given text by the allowed HTML elements names, attribute names
      $text_content = wp_kses($text_content, $allowedHtmltags);
    }

    return $text_content;
}    
add_filter( 'the_content', 'cendrie_replace_content' );
