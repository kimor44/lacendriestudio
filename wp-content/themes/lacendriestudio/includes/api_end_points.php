<?php

/**
 * API end points
 */

/**
 * Current version of TailwindCSS
 * @return string the tailwind version
 */
function current_tailwind_version()
{
  $pathToPackageJsonFile = get_template_directory_uri() . '/package.json';

  $json = file_get_contents($pathToPackageJsonFile);

  $packageJson = json_decode($json, true);

  // check if tailwind dependency exists
  if (isset($packageJson['devDependencies']['tailwindcss'])) {
    $tailwindCSSVersion = $packageJson['devDependencies']['tailwindcss'];
  } else {
    $tailwindCSSVersion =  "Not found";
  }

  return $tailwindCSSVersion;
}

function cendrie_dependencies_versions()
{
  $return = array(
    'wordpress'  => get_bloginfo('version'),
    'tailwindcss' => current_tailwind_version()
  );

  // the twice commented returns work
  // return new WP_REST_Response($return);
  // return rest_ensure_response($return);
  wp_send_json($return);
}

/**
 * This is our callback function that embeds our resource in a WP_REST_Response
 */
function prefix_get_private_data_permissions_check()
{
  // Some code if restriction needed about user capabilities
  // Remind to look at this link
  // https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/#permissions-callback

  return true;
}

function cendrie_get_current_versions()
{
  register_rest_route('cendrie/v1', '/versions', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'cendrie_dependencies_versions',
    'permission_callback' => 'prefix_get_private_data_permissions_check',
  ));
}

add_action('rest_api_init', 'cendrie_get_current_versions', 10);
