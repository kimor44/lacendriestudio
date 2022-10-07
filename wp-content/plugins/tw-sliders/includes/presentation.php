<?php

if(! class_exists('Presentation')) {
  class Presentation {
    function get_the_content() {
      $content = '<h1>' . get_admin_page_title() . '</h1>';
      $content .= '<p class="slider-presentation-paragraph">Le carrousel permet de créer autant de slides que vous le souhaitez
                  et de ne faire apparaître sur votre page publique que celles qui vous intéressent.</p>';

      $content .= '<p class="slider-presentation-paragraph">Pour ceci, vous n&apos;avez qu&apos;&agrave; suivre le facicule ci-dessous
                  en cliquant sur le bouton :</p>
                  <p class="slider-presentation-paragraph">
                    <img id="get-started-button" src="' . plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/get_started_button.png" width="50%" />
                  </p>
                  <p class="slider-presentation-paragraph">
                  et laissez vous guider.</p>';
      $content .= '<p class="content-iframe">
                    <iframe src="https://scribehow.com/embed/Add_slide_and_handle_visibility__cmLJlfnzSl2ZNCQ4Id_pbw"
                      width="900"
                      height="640"
                      allowfullscreen
                      frameborder="0">
                    </iframe>
                  </p>';
      return $content;
    }
  }
}