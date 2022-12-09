<?php

/**
 * Method helpers for handle the admin section (columns building).
 */
if (!class_exists('Admin_Handling')) {
  class Admin_Handling
  {

    protected int $post_id;

    public function __construct(int $post_id)
    {
      $this->post_id = $post_id;
    }
    /**
     * Get the current post thumbnail
     * @return void 
     */
    function get_the_thumbnail_for_custom_column(): void
    {
      the_post_thumbnail('thumbnail', $this->post_id);
    }

    /**
     * Get the current value for custom metabox (is_visible)
     * and handle it to display in the right langage
     * Build HTML for making style
     * @return void
     */
    function get_is_visible_metabox_for_custom_column(): void
    {
      require_once('includes/slider_tools.php');

      $value = get_post_meta($this->post_id, 'is_visible_meta_key', true);
      $translated_value = Slider_Tools::visibility($value);
      if (!isset($translated_value)) {
        $translated_value = "no";
      }
      echo '<div class="visible-cell visible-' . $value . '">' . $translated_value . '</div>';
    }
  }
}
