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
 * Load script & styles for slider admin panel
 */
function slider_admin_scripts() {
  $screen = get_current_screen();
  if($screen->post_type == 'slider'){
    wp_enqueue_style('slider_admin', plugin_dir_url(__FILE__) . '/assets/css/slider_admin.css');
  }
}
add_action( 'admin_enqueue_scripts', 'slider_admin_scripts' );

/**
 * Add metabox for carousel, build HTML and save data
 */
require_once('class-carousel-metabox.php');

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
      'thumbnail',
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
      'post_type'   => 'slider',
      'meta_key'    => 'is_visible_meta_key',
      'meta_value'  => 'yes',
    );
    
    $sliders = new WP_Query($args); 

    $carousel .= '<div id="carouselExampleControls" class="carousel slide relative" data-bs-ride="carousel">';
    $carousel .= '<div class="carousel-inner relative w-full overflow-hidden">';

    while ( $sliders->have_posts() ) {
      $sliders->the_post();

      $class_ac = $sliders->current_post === 0 ? ' active' : '';

      $carousel .= '<div class="carousel-item relative float-left w-full h-[15rem] sm:h-[25rem] md:h-[39.375rem]' . $class_ac . '">';
      $carousel .= '<div class="flex items-center h-full">';

      if(has_post_thumbnail()): $carousel .= get_the_post_thumbnail(get_the_ID(), $image_size, ['class' => 'block w-full']); endif;

      $carousel .= '</div></div>';
    }
    wp_reset_query();
    $carousel .= '</div>
    <button
      class="carousel-control-prev absolute top-0 bottom-0 flex items-center justify-center p-0 text-center border-0 hover:outline-none hover:no-underline focus:outline-none focus:no-underline left-0"
      type="button"
      data-bs-target="#carouselExampleControls"
      data-bs-slide="prev"
    >
      <span class="carousel-control-prev-icon inline-block bg-no-repeat" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button
      class="carousel-control-next absolute top-0 bottom-0 flex items-center justify-center p-0 text-center border-0 hover:outline-none hover:no-underline focus:outline-none focus:no-underline right-0"
      type="button"
      data-bs-target="#carouselExampleControls"
      data-bs-slide="next"
    >
      <span class="carousel-control-next-icon inline-block bg-no-repeat" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>';

    $carousel .= '</div>';
    return $carousel;
  }
}
add_action('init', 'tw_sliders_init');

add_filter( 'wp_lazy_loading_enabled', '__return_false' );

/**
* Add custom columns at the slider custom-post-type
* @return array columns
*/
function slider_custom_columns($columns) {
  $custom_col_order = array(
    'cb' => $columns['cb'],
    'overview' => __( 'Aperçu', 'textdomain' ),
    'title' => $columns['title'],
    'date' => $columns['date']
  );
  return $custom_col_order;
}
add_filter( 'manage_slider_posts_columns', 'slider_custom_columns' );

/**
* Display related content for the expected column
*/
function display_thumbnail_of_slider( $column, $post_id ) {
  require_once('class-admin-handling.php');
  $admin_handle_init = new Admin_Handling($post_id);

  if ($column == 'overview'){
    $admin_handle_init->get_the_thumbnail_for_custom_column();
  }

  if($column == 'visible'){
    $admin_handle_init->get_is_visible_metabox_for_custom_column();
  }
}
add_action( 'manage_slider_posts_custom_column' , 'display_thumbnail_of_slider', 10, 2 );
 