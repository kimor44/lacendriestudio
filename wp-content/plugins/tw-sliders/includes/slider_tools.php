<?php

class Slider_Tools {

  /**
   * Translation method for is_visible meta value
   * 
   * @param string $key the value to translate
   * @return string the value translated
   */
  public static function visibility(string $key) {
    $t_visibility = array(
      'yes' => 'oui',
      'no' => 'non'
    );

    $resp = array_key_exists($key, $t_visibility) ? $t_visibility[$key] : '';

    return $resp;
  }
}