<?php

/**
 * API end points
 */

function cendrie_dependencies_versions()
{
  $return = array(
    'wordpress'  => get_bloginfo('version')
  );

  // the twice commented returns work
  // return new WP_REST_Response($return);
  // return rest_ensure_response($return);
  wp_send_json($return);
}

function cendrie_get_current_versions()
{
  register_rest_route('cendrie/v1', '/versions', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'cendrie_dependencies_versions'
  ));
}

add_action('rest_api_init', 'cendrie_get_current_versions', 10);
