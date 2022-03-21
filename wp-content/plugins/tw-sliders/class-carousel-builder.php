<?php
/**
 * Method helpers for building carousel.
 */

if ( ! class_exists( 'Carousel_Builder' ) ) {
  class Carousel_Builder {
    /**
     * Check and get the right size.
     *
     * @param string $size
     * @return string
     */
    function get_the_format_image_size( string $size = '' ) {
      if(!is_string($size)){
        return new WP_Error( 'broke', __( "Size parameter send is not a string" ) );
      }

      if ( !in_array( $size, get_intermediate_image_sizes() ) ) {
        $size = '';
      }

      return $size;
    }

    /**
     * format the query.
     *
     * @param int $nb_slides
     * @return array
     */
    function get_the_query_builder( int $nb_slides = null ) {
      $query_filter = array();

      if ( $nb_slides > 0 ) {
        $query_filter['posts_per_page'] = $nb_slides;
      }

      return $query_filter;
    }
  }
}
