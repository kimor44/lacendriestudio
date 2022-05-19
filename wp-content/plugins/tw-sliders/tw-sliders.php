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
 * Create the checkbox metabox in the admin panel of "sliders" plugin
 * to show or not the slide in the carousel
*/
function add_checkbox_is_visible() {
    add_meta_box(
        'is_visible',
        'Afficher l\'image dans le caroussel ?',
        'build_is_visible_form',
        'slider',
        'advanced',
        'high',
    );
}
add_action( 'add_meta_boxes', 'add_checkbox_is_visible' );

/**
 * Render Meta Box content.
 *
 * @param WP_Post $post The post object.
 */
function build_is_visible_form( $post ) {
    $value = get_post_meta( $post->ID, 'is_visible_meta_key', true );
    $checked = $value == "yes" ? "checked" : "";
    ?>
    <input type="checkbox" id="is_visible" name="is_visible" value="yes" <?php echo $checked; ?>>
    <label for="is_visible">Cocher pour afficher l'image dans le carousel</label>
    <?php
}

/**
 * Save the meta when the post is saved.
 *
 * @param int $post_id The ID of the post being saved.
 */
function save_is_visible_postdata( $post_id ) {
    if(!array_key_exists('is_visible', $_POST)){
      $_POST['is_visible'] = "no";
    }
    update_post_meta(
        $post_id,
        'is_visible_meta_key',
        $_POST['is_visible'],
    );
}
add_action( 'save_post', 'save_is_visible_postdata' );
