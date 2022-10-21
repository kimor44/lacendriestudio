<?php

abstract class Carousel_Metabox {
 
  /**
   * Create the checkbox metabox in the admin panel of "sliders" plugin
   * to show or not the slide in the carousel
  */
  public static function add_checkbox_is_visible() {
    add_meta_box(
        'is_visible',
        'Afficher l\'image dans le carrousel ?',
        [ self::class, 'build_is_visible_form' ],
        'slider',
        'advanced',
        'high',
    );
  }

  /**
   * Save the meta when the post is saved.
   *
   * @param int $post_id The ID of the post being saved.
   */
  public static function save_is_visible_postdata( $post_id ) {
    if(!array_key_exists('is_visible', $_POST)){
      $_POST['is_visible'] = "no";
    }
    update_post_meta(
        $post_id,
        'is_visible_meta_key',
        $_POST['is_visible'],
    );
  }

  /**
   * Render Meta Box content.
   *
   * @param WP_Post $post The post object.
   */
  public static function build_is_visible_form( $post ) {
    $value = get_post_meta( $post->ID, 'is_visible_meta_key', true );
    $checked = $value == "yes" ? "checked" : "";
    ?>
      <input type="checkbox" id="is_visible" name="is_visible" value="yes" <?php echo $checked; ?>>
      <label for="is_visible">Cocher la case pour afficher l'image dans le carrousel</label>
    <?php
  }
}

add_action( 'add_meta_boxes', [ 'Carousel_Metabox', 'add_checkbox_is_visible' ] );
add_action( 'save_post', [ 'Carousel_Metabox', 'save_is_visible_postdata' ] );
