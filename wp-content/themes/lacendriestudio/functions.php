<?php
/**
* La Cendrie Studio functions and definitions
*
* @package LaCendrieStudio
* @since LaCendrieStudio 1.0
*/

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