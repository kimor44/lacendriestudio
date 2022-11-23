<?php

abstract class Carousel_Metabox {

  const META_KEY = 'is_visible';
  const NONCE = '_cendrie_is_visible_nonce';
 
  /**
   * Create the checkbox metabox in the admin panel of "sliders" plugin
   * to show or not the slide in the carousel
  */
  public static function add_checkbox_is_visible($postType, $post) {
    if($postType == 'slider' && current_user_can('publish_posts', $post)){
      add_meta_box(
          self::META_KEY,
          'Afficher l\'image dans le carrousel ?',
          [ self::class, 'build_is_visible_form' ],
          'slider',
          'advanced',
          'high',
      );
    }
  }

  /**
   * Save the meta when the post is saved.
   *
   * @param int $post_id The ID of the post being saved.
   */
  public static function save_is_visible_postdata( int $post_id ) {
    $nonce = $_POST[self::NONCE];
    if (
        wp_verify_nonce($nonce, self::NONCE) &&
        current_user_can('publish_posts', $post_id)
        ) {
        if(!array_key_exists(self::META_KEY, $_POST)){
          $_POST[self::META_KEY] = "no";
        }
        update_post_meta(
            $post_id,
            'is_visible_meta_key',
            $_POST[self::META_KEY],
        );
    }
  }

  /**
   * Render Meta Box content.
   *
   * @param WP_Post $post The post object.
   */
  public static function build_is_visible_form( WP_Post $post ) {
    $value = get_post_meta( $post->ID, 'is_visible_meta_key', true );
    $checked = $value == "yes" ? "checked" : "";
		// Add an nonce field so we can check for it later.
		wp_nonce_field( self::NONCE, self::NONCE );
    ?>
      <input type="checkbox" id="<?= self::META_KEY ?>" name="<?= self::META_KEY ?>" value="yes" <?php echo $checked; ?>>
      <label for="<?= self::META_KEY ?>">Cocher la case pour afficher l'image dans le carrousel</label>
    <?php
  }

  /**
   * Display Meta Box in quick edit mode
   * 
   *  @param string $column_name Name of the column to edit.
   *  @param string $post_type The post type slug, or current screen name if this is a taxonomy list table.
   */
  public static function display_quick_edit_is_visible(string $column_name, string $post_type) {
    if (current_user_can('publish_posts') && $column_name == 'visible') {
        // Add an nonce field so we can check for it later.
        wp_nonce_field( self::NONCE, self::NONCE );
      ?>
      <label class="inline-edit-status alignleft" for="<?= self::META_KEY ?>">
        <span class="title">Visibilité</span>
        <span class="input-text-wrap">
          <input type="checkbox" id="<?= self::META_KEY ?>" name="<?= self::META_KEY ?>" value="yes" >
        </span>
      </label>
    <?php
    }
  }
}

add_action( 'add_meta_boxes', [ 'Carousel_Metabox', 'add_checkbox_is_visible' ], 10, 2 );
add_action( 'save_post', [ 'Carousel_Metabox', 'save_is_visible_postdata' ] );
add_action( 'quick_edit_custom_box', [ 'Carousel_Metabox', 'display_quick_edit_is_visible'], 10, 2);
