<?php
/**
 * Plugin Name:       tw Sliders
 * Description:       A basic slider built with Tailwind CSS framework
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.4.21
 * Author:            Julien Guibert
 */

 /**
 * Register the "sliders" custom post type
 */

function cendrie_create_slider_post_type() {
  $labels = array (
    'name' => __( 'Sliders' ),
    'singular_name' => __( 'Sliders' ),
    'all_items' => __( 'Toutes les slides' ),
    'view_item' => __( 'Voir slide' ),
    'add_new_item' => __( 'Ajouter une nouvelle slide' ),
    'add_new' => __( 'Ajouter une nouvelle slide' ),
    'edit_item' => __( 'Editer slide' ),
    'update_item' => __( 'Mettre à jour slide' ),
    'search_items' => __( 'Rechercher une slide' ),
    'search_items' => __('Sliders')
  );
  $args = array (
    'labels' => $labels,
    'description' => 'Add New Slider contents',
    'menu_position' => 30, /**valeur de votre choix**/
    'public' => true,
    'has_archive' => true,
    'map_meta_cap' => true,
    'capability_type' => 'post',
    'hierarchical' => true,
    'rewrite' => array('slug' => false),
    'menu_icon'=>'dashicons-images-alt2', /**valeur de votre choix : https://developer.wordpress.org/resource/dashicons/#editor-underline **/
    'supports' => array(
      'title',
      'thumbnail','excerpt'
    ),
  );
  register_post_type( 'slider', $args);
}
add_action( 'init', 'cendrie_create_slider_post_type' );
  

/**
* Activate the plugin.
*/
function cendrie_pluginprefix_activate() { 
  // Trigger our function that registers the custom post type plugin.
  cendrie_create_slider_post_type(); 
  // Clear the permalinks after the post type has been registered.
  flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'cendrie_pluginprefix_activate' );

/**
 * Deactivation hook.
 */
function cendrie_pluginprefix_deactivate() {
  // Unregister the post type, so the rules are no longer in memory.
  unregister_post_type( 'slider' );
  // Clear the permalinks to remove our post type's rules from the database.
  flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cendrie_pluginprefix_deactivate' );

require_once('class-carousel-builder.php');

/**
 * [tw_sliders size="large" number_of_slides=3] returns the Carousel with your images.
 * @return string Carousel
*/
add_shortcode( 'tw_slider', 'build_carousel' );
function tw_sliders_init(){
  function build_carousel( $atts ) {
    $attributs = shortcode_atts( array(
      'size'             => '',
      'number_of_slides' => null,
    ), $atts );

    $builder = new Carousel_Builder();
    $carousel = '';

    $image_size = $builder->get_the_format_image_size($attributs['size']);

    $args = array(
      'post_type' => 'slider',
    );
    
    $query_filter = $builder->get_the_query_builder(intval($attributs['number_of_slides']));

    $args = array_merge($args, $query_filter);

    $sliders = new WP_Query($args); 

    $carousel .= '<div id="carouselExampleSlidesOnly" class="carousel slide relative my-10" data-bs-ride="carousel">';
    $carousel .= ' <div class="carousel-inner relative w-full overflow-hidden">';

    while ( $sliders->have_posts() ) {
      $sliders->the_post();

      $class_ac = $sliders->current_post === 0 ? ' active' : '';

      $carousel .= '<div class="carousel-item relative float-left h-[20rem] md:h-[30rem] lg:h-[35rem] xl:h-[40rem] w-full' . $class_ac . '">';

      if(has_post_thumbnail()): $carousel .= get_the_post_thumbnail(get_the_ID(), $image_size, ['class' => 'block h-[20rem] md:h-[30rem] lg:h-[35rem] xl:h-[40rem] mx-auto']); endif;

      $carousel .= '</div>';
    }
    wp_reset_query();
    $carousel .= ' </div>';
    $carousel .= '</div>'; 
    return $carousel;
  }
}
add_action('init', 'tw_sliders_init');

add_filter( 'wp_lazy_loading_enabled', '__return_false' );

