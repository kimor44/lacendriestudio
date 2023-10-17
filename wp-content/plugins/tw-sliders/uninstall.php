<?php

// Exit if uninstall constant is not defined
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

// Delete custom post type posts
$args = array(
  'post_type' => 'slider',
  'posts_per_page' => -1,
);
$slider_posts = get_posts($args);
foreach ($slider_posts as $post) {
  wp_delete_post($post->ID, false);
}

// Remove custom post type capabilities
$role = get_role('administrator');
$capabilities = array(
  'edit_slides',
  'edit_other_slides',
  'delete_slides',
  'publish_slides',
  'read_private_slides',
  'delete_private_slides',
  'delete_published_slides',
  'delete_other_slides',
  'edit_private_slides',
  'edit_published_slides',
);

foreach ($capabilities as $capability) {
  $role->remove_cap($capability);
}

// Remove meta-box
require_once('class-carousel-metabox.php');
remove_meta_box(Carousel_Metabox::META_BOX_ID, 'slider', 'normal');

// Remove custom role
remove_role('writer');
