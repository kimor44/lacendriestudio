<?php

if (!class_exists('Presentation')) {
  class Presentation
  {
    function get_the_content()
    {
      $content = '<h1>' . get_admin_page_title() . '</h1>';
      $content .= '<article class="presentation-content">';
      $content .= '<p>Le carrousel permet de créer autant de slides que vous le souhaitez
                  et de ne faire apparaître sur votre page publique que celles qui vous intéressent.</p>';

      $content .= '<p>Pour ceci, vous n&apos;avez qu&apos;&agrave; suivre le facicule ci-dessous
                  en cliquant sur le bouton :</p>
                  <p>
                    <img id="get-started-button" src="' . plugin_dir_url(dirname(__FILE__)) . 'assets/img/get_started_button.png" width="50%" />
                  </p>
                  <p>
                  et laissez vous guider.</p>';
      $content .= '<p class="content-iframe">
                    <iframe src="https://scribehow.com/embed/Add_slide_and_handle_visibility__cmLJlfnzSl2ZNCQ4Id_pbw"
                      width="900"
                      height="640"
                      allowfullscreen
                      frameborder="0">
                    </iframe>
                  </p>';
      $content .= '<p>
                    Quand vous avez ajouté et géré toutes vos slides,
                    Il vous faut ajouter votre carrousel sur votre site.
                  </p>';
      $content .= '<p>
                    Pour cela, dans le contenu d\'une page ou d\'un article,
                    ajoutez le <code>shortecode [/]</code> (code court) suivant :<br/>
                    <code>[tw_sliders]</code>
                  </p>';
      $content .= '<p>Ce shortcode prend aussi le paramètre optionnel <code>size</code></p>';
      $content .= '<p>Par exemple : <code>[tw_sliders size="large"]</code></p>';
      $content .= '<p>Les tailles disponibles sont les suivantes :</p>';
      $content .= '<ul>';
      foreach (wp_get_registered_image_subsizes() as $key => $image) {
        $content .= '<li>' . $key . ' => ' . $image['width'] . ' x ' . $image['height'] . 'px</li>';
      }
      $content .= '</ul>';
      $content .= '<p>
                    Si vous avez une taille personalisée, il est conseillé de
                    l\'utiliser.<br/>
                    Autrement, privilégiez plutôt la taille "large" qui est paramétrable
                    dans la section "Réglages" > "Médias".
                  </p>';
      $content .= '</article>';
      return $content;
    }
  }
}
