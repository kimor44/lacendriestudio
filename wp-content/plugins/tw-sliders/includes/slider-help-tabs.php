<?php

class Slider_Help_Tabs {

  private $screen;

  public function __construct( WP_Screen $screen ) {
    $this->screen = $screen;
  }

  public function set_help_tabs( string $type ) {
		switch ( $type ) {
			case 'presentation':
				$this->screen->add_help_tab( array(
					'id' => 'presentation_overview',
					'title' => __( 'Vue d\'ensemble', 'text-slider' ),
					'content' => $this->content( 'presentation_overview' ),
					'priority' => 1 ) );

				$this->screen->add_help_tab( array(
					'id' => 'presentation_carousel_management',
					'title' => __( 'Gestion du Carrousel', 'text-slider' ),
					'content' => $this->content( 'presentation_carousel_management' ),
					'priority' => 10 ) );

				return;
  }

	private function content( $name ) {
		$content = array();

		$content['presentation_overview'] = '<p>' . __( "Sur cette page, vous allez avoir un guide pour apprendre à créer et gérer votre carrousel.", 'text-slider' ) . '</p>';
    
		$content['presentation_carousel_management'] = '<p>' . __( "Suivez ce guide pas à pas pour ajoutez, supprimez et gérez la visibilité des slides.<br/>", 'text-slider' );
		$content['presentation_carousel_management'] .= __( "Intégrez ensuite votre carrousel où vous voulez dans votre site, sois dans une page sois dans un article.<br/>", 'text-slider' );
		$content['presentation_carousel_management'] .= __( "Vous avez aussi la possibilité de préciser quelle taille d'images vous souhaiter utiliser parmis celles proposées.", 'text-slider' ) . '</p>';
    
		if ( ! empty( $content[$name] ) ) {
			return $content[$name];
		}
	}

	public function sidebar() {
		$content = '<p><strong>' . __( 'Plus d\'informations :', 'text-slider' ) . '</strong></p>';

		$this->screen->set_help_sidebar( $content );
	}
}