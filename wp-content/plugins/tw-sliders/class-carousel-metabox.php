<?php

abstract class Carousel_Metabox {

  const META_BOX_ID = 'is_visible';
  const META_KEY = 'is_visible_meta_key';
  const NONCE = '_cendrie_is_visible_nonce';
  const FILTER = 'visibility_filtering';
 
  /**
   * Create the checkbox metabox in the admin panel of "sliders" plugin
   * to show or not the slide in the carousel
  */
  public static function add_checkbox_is_visible($postType, $post) {
    if($postType == 'slider' && current_user_can('publish_posts', $post)){
      add_meta_box(
          self::META_BOX_ID,
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
    if (!isset($_POST['post_type']) || $_POST['post_type'] != 'slider' ) {
      return;
    };

    $nonce = $_POST[self::NONCE];
    if (
        wp_verify_nonce($nonce, self::NONCE) &&
        current_user_can('publish_posts', $post_id)
        ) {
        if(!array_key_exists(self::META_BOX_ID, $_POST)){
          $_POST[self::META_BOX_ID] = "no";
        }
        update_post_meta(
            $post_id,
            self::META_KEY,
            $_POST[self::META_BOX_ID],
        );
    }
  }

  /**
   * Render Meta Box content.
   *
   * @param WP_Post $post The post object.
   */
  public static function build_is_visible_form( WP_Post $post ) {
    $value = get_post_meta( $post->ID, self::META_KEY, true );
    $checked = $value == "yes" ? "checked" : "";
		// Add an nonce field so we can check for it later.
		wp_nonce_field( self::NONCE, self::NONCE );
    ?>
      <input type="checkbox" id="<?= self::META_BOX_ID ?>" name="<?= self::META_BOX_ID ?>" value="yes" <?php echo $checked; ?>>
      <label for="<?= self::META_BOX_ID ?>">Cocher la case pour afficher l'image dans le carrousel</label>
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
      <label class="inline-edit-col-right label-is-visible" for="<?= self::META_BOX_ID ?>">
        <span class="title">Visible ?</span>
        <span class="input-text-wrap">
          <input type="checkbox" id="<?= self::META_BOX_ID ?>" name="<?= self::META_BOX_ID ?>" value="yes" >
        </span>
      </label>
    <?php
    }
  }

  /**
   * Build filter for the is_visible meta data
   * 
   * @param string $post_type The post type slug
   */
  public static function is_visible_filtering( string $post_type ){
    
    if('slider' !== $post_type){
      return;
    }

    $current_plugin = isset($_GET[self::FILTER]) ? $_GET[self::FILTER] : '';

    global $wpdb;

    $visibilities = $wpdb->get_col( 
      $wpdb->prepare( "
        SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s' 
        ORDER BY pm.meta_value DESC", 
        self::META_KEY
      ) 
    );

    $t_visibility = array(
      'yes' => 'oui',
      'no' => 'non'
    );

    echo '<select id="' . self::FILTER . '" name="' . self::FILTER . '">';
    echo '<option value="0"'. selected('toutes les visibilités', $current_plugin) . '>' . __( 'Toutes les visibilités', 'text-slider' ) . '</option>';
      foreach($visibilities as $visibility){
        echo '<option value="' . $visibility . '" '. selected($visibility, $current_plugin) .'>' . ucfirst($t_visibility[$visibility]) . '</option>';
      }
    echo '</select>';
  }

  /**
   * Query filters slides according to visibility
   * 
   * @param WP_Query $query the WP_Query instance
   */
  public static function is_visible_filter_parsing( WP_Query $query ) {

    // modify the query only if it admin and main query.
    if( !(is_admin() AND $query->is_main_query()) ){ 
      return $query;
    }

    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
    
    // we want to modify the query for the targeted custom post and filter option
    if( !('slider' === $post_type && isset($_GET[self::FILTER]) ) ){
      return $query;
    }

    // for the default value of our filter no modification is required
    if ( $_GET[self::FILTER] == '0' ){
      return $query;
    }

    // modify query_vars
    if ($post_type == 'slider' && isset($_GET[self::FILTER])){
      $query = $query->query_vars = array(
        'post_type' => $post_type,
        'meta_key' => self::META_KEY,
        'meta_value' => $_GET[self::FILTER],
        'meta_compare' => '='
      );
      return $query;
    }
  }
}

add_action( 'add_meta_boxes', [ 'Carousel_Metabox', 'add_checkbox_is_visible' ], 10, 2 );
add_action( 'save_post', [ 'Carousel_Metabox', 'save_is_visible_postdata' ] );
add_action( 'quick_edit_custom_box', [ 'Carousel_Metabox', 'display_quick_edit_is_visible'], 10, 2);
add_action( 'restrict_manage_posts', [ 'Carousel_Metabox', 'is_visible_filtering' ], 10, 1);
add_action( 'parse_query', [ 'Carousel_Metabox', 'is_visible_filter_parsing' ], 10, 1);
