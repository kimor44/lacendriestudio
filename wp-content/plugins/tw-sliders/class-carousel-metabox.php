<?php

abstract class Carousel_Metabox
{

  public const META_BOX_ID = 'is_visible';
  public const META_KEY = 'is_visible_meta_key';
  public const NONCE = '_cendrie_is_visible_nonce';
  public const FILTER = 'visibility_filtering';


  //_____Start_Add_Meta_box_____
  /**
   * Create the checkbox metabox in the admin panel of "sliders" plugin
   * to show or not the slide in the carousel
   *
   * @param string $post_type Post Type
   * @param WP_Post $post Post Object
   */
  public static function add_checkbox_is_visible(string $post_type, WP_Post $post)
  {
    if ($post_type == 'slider' && current_user_can('publish_posts', $post)) {
      add_meta_box(
        self::META_BOX_ID,
        'Afficher l\'image dans le carrousel ?',
        [self::class, 'build_is_visible_form'],
        'slider',
        'advanced',
        'high',
      );
    }
  }

  /**
   * Render Meta Box content.
   *
   * @param WP_Post $post The post object.
   */
  public static function build_is_visible_form(WP_Post $post)
  {
    $value = get_post_meta($post->ID, self::META_KEY, true);
    $checked = $value == "yes" ? "checked" : "";
    // Add an nonce field so we can check for it later.
    wp_nonce_field(self::NONCE, self::NONCE);
?>
    <input type="checkbox" id="<?= self::META_BOX_ID ?>" name="<?= self::META_BOX_ID ?>" value="yes" <?php echo $checked; ?>>
    <label for="<?= self::META_BOX_ID ?>">Cocher la case pour afficher l'image dans le carrousel</label>
    <?php
  }

  /**
   * Save the meta when the post is saved.
   *
   * @param int $post_id The ID of the post being saved.
   */
  public static function save_is_visible_postdata(int $post_id)
  {
    if (!isset($_POST['post_type']) || $_POST['post_type'] != 'slider') {
      return;
    };

    $nonce = $_POST[self::NONCE];
    if (
      wp_verify_nonce($nonce, self::NONCE) &&
      current_user_can('publish_posts', $post_id)
    ) {
      if (!array_key_exists(self::META_BOX_ID, $_POST)) {
        $_POST[self::META_BOX_ID] = "no";
      }
      update_post_meta(
        $post_id,
        self::META_KEY,
        $_POST[self::META_BOX_ID],
      );
    }
  }
  //_____End_Add_Meta_box_____


  //_____Start_Quick_Edit_and_Bulk_Edit_Mode_custom_checkbox_____
  /**
   * Display Meta Box in quick edit mode
   *
   *  @param string $column_name Name of the column to edit.
   */
  public static function is_visible_custom_box_quick_edit_bulk_edit(string $column_name)
  {
    if (current_user_can('publish_posts') && $column_name == 'visible') {
      // Add a nonce field so we can check for it later.
      wp_nonce_field(self::NONCE, self::NONCE);
    ?>
      <label class="is_visible_field" for="<?= self::META_BOX_ID ?>">
        <span class="title">Visible ?</span>
        <span class="input-text-wrap">
          <input type="checkbox" id="<?= self::META_BOX_ID ?>" name="<?= self::META_BOX_ID ?>" value="yes">
        </span>
      </label>
<?php
      require_once('includes/is-visible-edit-style.php');
    }
  }
  //_____End_Quick_Edit_and_Bulk_Edit_Mode_custom_checkbox_____


  //_____Start_Filter_____
  /**
   * Build filter for the is_visible meta data
   *
   * @param string $post_type The post type slug
   */
  public static function is_visible_filtering(string $post_type)
  {

    require_once('includes/slider_tools.php');

    if ('slider' !== $post_type) {
      return;
    }

    $current_plugin = isset($_GET[self::FILTER]) ? $_GET[self::FILTER] : '';

    global $wpdb;

    $visibilities = $wpdb->get_col(
      $wpdb->prepare(
        "
        SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s'
        ORDER BY pm.meta_value DESC",
        self::META_KEY
      )
    );

    echo '<select id="' . self::FILTER . '" name="' . self::FILTER . '">';
    echo '<option value="0"' . selected('toutes les visibilités', $current_plugin) . '>' . __('Toutes les visibilités', 'text-slider') . '</option>';
    foreach ($visibilities as $visibility) {
      echo '<option value="' . $visibility . '" ' . selected($visibility, $current_plugin) . '>' . ucfirst(Slider_Tools::visibility($visibility)) . '</option>';
    }
    echo '</select>';
  }

  /**
   * Query filters slides according to visibility
   *
   * @param WP_Query $query the WP_Query instance
   * @return WP_Query $query the WP_Query instance modified
   */
  public static function is_visible_filter_parsing(WP_Query $query)
  {

    // modify the query only if it admin and main query.
    if (!($query->is_main_query())) {
      return $query;
    }

    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';

    // we want to modify the query for the targeted custom post and filter option
    if (!('slider' === $post_type && isset($_GET[self::FILTER]))) {
      return $query;
    }

    // for the default value of our filter no modification is required
    if ($_GET[self::FILTER] == '0') {
      return $query;
    }

    // modify query_vars
    if ($post_type == 'slider' && isset($_GET[self::FILTER])) {
      $query = $query->query_vars = array(
        'post_type' => $post_type,
        'meta_key' => self::META_KEY,
        'meta_value' => $_GET[self::FILTER],
        'meta_compare' => '='
      );
      return $query;
    }
  }
  //_____End_Filter_____


  //_____Start_Sort_Column_____
  /**
   * Insert the 'visible' column to the sortable columns array
   *
   * @param array $columns Array with all the sortable columns
   * @return array $columns Array of columns filterd with the new "visible" column
   */
  public static function enable_sortable_is_visible_column(array $columns)
  {
    unset($columns['comments']);

    $columns['visible'] = 'visibility';

    return $columns;
  }

  /**
   * Handle WP_Query to sort sliders by meta_values "is_visible"
   *
   * @param WP_Query $query The WP_Query instance
   */
  public static function visibility_order_by(WP_Query $query)
  {
    $order_by = $query->get('orderby');

    if ($order_by == 'visibility') {
      $query->set('meta_key', self::META_KEY);
      $query->set('orderby', 'meta_value title');
    }
  }
  //_____End_Sort_Column_____


  //_____Start_Bulk_update_____
  /**
   * Update is_visible meta data in the bulk update mode
   *
   * @param int $post_ID ID of the current post
   */
  public static function bulk_edit_custom_is_visible(int $post_ID)
  {
    $nonce = $_REQUEST[self::NONCE];
    if (!wp_verify_nonce($nonce, self::NONCE)) {
      return;
    }

    $visibility = !empty($_REQUEST[self::META_BOX_ID]) ? $_REQUEST[self::META_BOX_ID] : 'no';

    update_post_meta(
      $post_ID,
      self::META_KEY,
      $visibility,
    );
  }
  //_____End_Bulk_update_____
}

add_action('add_meta_boxes', ['Carousel_Metabox', 'add_checkbox_is_visible'], 10, 2);
add_action('save_post', ['Carousel_Metabox', 'save_is_visible_postdata']);

global $pagenow;

if ($pagenow == 'edit.php' && is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == 'slider') {
  add_action('quick_edit_custom_box', ['Carousel_Metabox', 'is_visible_custom_box_quick_edit_bulk_edit'], 10, 1);
  add_action('restrict_manage_posts', ['Carousel_Metabox', 'is_visible_filtering'], 10, 1);
  add_action('parse_query', ['Carousel_Metabox', 'is_visible_filter_parsing'], 10, 1);
  add_filter('manage_edit-slider_sortable_columns', ['Carousel_Metabox', 'enable_sortable_is_visible_column'], 10, 1);
  add_action('pre_get_posts', ['Carousel_Metabox', 'visibility_order_by'], 10, 1);
  add_action('bulk_edit_custom_box', ['Carousel_Metabox', 'is_visible_custom_box_quick_edit_bulk_edit'], 11, 2);
  add_action('save_post', ['Carousel_Metabox', 'bulk_edit_custom_is_visible'], 11, 1);
}
