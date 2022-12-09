<?php

/**
 * Plugin Name:       tw Sliders
 * Description:       A basic slider built with Tailwind CSS framework
 * Version:           1.1.1
 * Requires at least: 5.2
 * Requires PHP:      7.4.21
 * Author:            Julien Guibert
 * Text Domain:       text-slider
 */

if (!function_exists('cendrie_pluginprefix_activate')) {
  /**
   * Activate the plugin.
   */
  function cendrie_pluginprefix_activate()
  {
    // Trigger our function that registers the custom post type plugin.
    cendrie_create_slider_post_type();
    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules();
  }
}
register_activation_hook(__FILE__, 'cendrie_pluginprefix_activate');

if (!function_exists('cendrie_pluginprefix_deactivate')) {
  /**
   * Deactivation hook.
   */
  function cendrie_pluginprefix_deactivate()
  {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type('slider');
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
  }
}
register_deactivation_hook(__FILE__, 'cendrie_pluginprefix_deactivate');

if (!function_exists('slider_admin_scripts')) {
  /**
   * Load script & styles for slider admin panel
   */
  function slider_admin_scripts()
  {
    $screen = get_current_screen();
    if ($screen->post_type == 'slider' || $screen->base == 'slider_page_presentation') {
      // Loading Sliders admin CSS
      $css_file = plugin_dir_url(__FILE__) . '/assets/css/slider_admin.css';
      wp_enqueue_style('slider_admin', $css_file, array(), dirname($css_file), false);

      // Loadind Sliders admin script
      $js_file = plugin_dir_url(__FILE__) . '/assets/js/populate_is_visible_field.js';
      wp_enqueue_script('populate_is_visible_field', $js_file, array(), false, true);
    }
  }
}
add_action('admin_enqueue_scripts', 'slider_admin_scripts');

/**
 * Add metabox for carousel, build HTML and save data
 */
require_once('class-carousel-metabox.php');

if (!function_exists('cendrie_create_slider_post_type')) {
  /**
   * Register the "sliders" custom post type
   */
  function cendrie_create_slider_post_type()
  {
    $labels = array(
      'name' => __('Sliders'),
      'singular_name' => __('Sliders'),
      'all_items' => __('Toutes les slides'),
      'view_item' => __('Voir slide'),
      'add_new_item' => __('Ajouter une nouvelle slide'),
      'add_new' => __('Ajouter une nouvelle slide'),
      'edit_item' => __('Editer slide'),
      'update_item' => __('Mettre à jour slide'),
      'search_items' => __('Rechercher une slide')
    );
    $args = array(
      'labels' => $labels,
      'description' => 'Add New Slider contents',
      'menu_position' => 30,
      /**valeur de votre choix**/
      'public' => true,
      'has_archive' => true,
      'map_meta_cap' => true,
      'capability_type' => 'post',
      'hierarchical' => true,
      'rewrite' => array('slug' => false),
      'menu_icon' => 'dashicons-images-alt2',
      /**valeur de votre choix : https://developer.wordpress.org/resource/dashicons/#editor-underline **/
      'supports' => array(
        'title',
        'thumbnail',
      ),
      'show_in_admin_bar' => false
    );
    register_post_type('slider', $args);
    add_filter('page_row_actions', 'tw_sliders_remove_view_action', 10, 2);
  }
}
add_action('init', 'cendrie_create_slider_post_type');

require_once('class-carousel-builder.php');

if (!function_exists('tw_sliders_init')) {
  /**
   * [tw_sliders size="large"] returns the Carousel with your images.
   * @return string Carousel
   */
  add_shortcode('tw_slider', 'build_carousel');
  function tw_sliders_init()
  {
    function build_carousel($atts)
    {
      $attributs = shortcode_atts(array(
        'size' => '',
      ), $atts);

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

      while ($sliders->have_posts()) {
        $sliders->the_post();

        $class_ac = $sliders->current_post === 0 ? ' active' : '';

        $carousel .= '<div class="carousel-item relative float-left w-full h-[15rem] sm:h-[25rem] md:h-[39.375rem]' . $class_ac . '">';
        $carousel .= '<div class="flex items-center h-full">';

        if (has_post_thumbnail()) : $carousel .= get_the_post_thumbnail(get_the_ID(), $image_size, ['class' => 'block w-full']);
        endif;

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
}
add_action('init', 'tw_sliders_init');

add_filter('wp_lazy_loading_enabled', '__return_false');

if (!function_exists('tw_sliders_remove_view_action')) {
  function tw_sliders_remove_view_action($actions, $post)
  {
    if ($post->post_type == 'slider') {
      unset($actions['view']);
    }

    return $actions;
  }
}

if (!function_exists('slider_custom_columns')) {
  /**
   * Add custom columns at the slider custom-post-type
   * @return array columns
   */
  function slider_custom_columns($columns)
  {
    $custom_col_order = array(
      'cb' => $columns['cb'],
      'overview' => __('Aperçu', 'text-slider'),
      'title' => $columns['title'],
      'visible' => __('Visible ?', 'text-slider'),
      'date' => $columns['date']
    );
    return $custom_col_order;
  }
}
add_filter('manage_slider_posts_columns', 'slider_custom_columns');

if (!function_exists('display_thumbnail_of_slider')) {
  /**
   * Display related content for the expected column
   */
  function display_thumbnail_of_slider($column, $post_id)
  {
    require_once('class-admin-handling.php');
    $admin_handle_init = new Admin_Handling($post_id);

    if ($column == 'overview') {
      $admin_handle_init->get_the_thumbnail_for_custom_column();
    }

    if ($column == 'visible') {
      $admin_handle_init->get_is_visible_metabox_for_custom_column();
    }
  }
}
add_action('manage_slider_posts_custom_column', 'display_thumbnail_of_slider', 10, 2);

if (!function_exists('add_presentation_page_to_slider')) {
  function add_presentation_page_to_slider()
  {
    $slider_pres_page = add_submenu_page(
      'edit.php?post_type=slider',
      'Comment se servir du carrousel',
      'Présentation',
      'manage_options',
      'presentation',
      'build_sub_menu_slider'
    );

    add_action('load-' . $slider_pres_page, 'pres_help_menu');

    /*
    * Will add a help menu to the edit page
    */
    add_action('load-edit.php', 'tw_slider_load_help_menu');

    /*
    * Will add a help menu to the new post page
    */
    add_action('load-post-new.php', 'tw_slider_load_help_menu');
  }
}
add_action('admin_menu', 'add_presentation_page_to_slider');


require_once('includes/slider-help-tabs.php');

function build_sub_menu_slider()
{
  $screen = get_current_screen();
  if ($screen->base == 'slider_page_presentation') {
    require_once('includes/presentation.php');

    $presentation = new Presentation();

    echo $presentation->get_the_content();
  }
}

function pres_help_menu()
{
  // check user capabilities
  if (!current_user_can('manage_options')) {
    return;
  }

  $current_screen = get_current_screen();

  $help_tabs = new Slider_Help_Tabs($current_screen);

  $help_tabs->set_help_tabs('presentation');
}

if (!function_exists('tw_slider_load_help_menu')) {
  function tw_slider_load_help_menu()
  {
    // check user capabilities
    if (!current_user_can('manage_options')) {
      return;
    }

    $types = array(
      'slider' => 'edit',
      'edit-slider' => 'list'
    );

    $current_screen = get_current_screen();

    if ('slider' != $current_screen->id && 'edit-slider' != $current_screen->id) {
      return;
    }

    $help_tabs = new Slider_Help_Tabs($current_screen);

    $help_tabs->set_help_tabs($types[$current_screen->id]);
  }
}
