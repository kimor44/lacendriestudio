<?php

class Slider_Help_Tabs
{

	private $screen;

	public function __construct(WP_Screen $screen)
	{
		$this->screen = $screen;
	}

	public function set_help_tabs(string $type)
	{
		global $pagenow, $page;

		switch ($type) {
			case 'presentation':
				$this->screen->add_help_tab(array(
					'id' => 'presentation_overview',
					'title' => __('Vue d\'ensemble', 'text-slider'),
					'content' => $this->content('presentation_overview'),
					'priority' => 1
				));

				$this->screen->add_help_tab(array(
					'id' => 'presentation_carousel_management',
					'title' => __('Gestion du Carrousel', 'text-slider'),
					'content' => $this->content('presentation_carousel_management'),
					'priority' => 10
				));

				return;

			case 'list':
				$this->screen->add_help_tab(array(
					'id' => 'list_sliders_overview',
					'title' => __('Vue d\'ensemble', 'text-slider'),
					'content' => $this->content('list_sliders_overview')
				));

				$this->screen->add_help_tab(array(
					'id' => 'list_screen_content',
					'title' => __('Contenu de l\'écran', 'text-slider'),
					'content' => $this->content('list_screen_content')
				));

				$this->screen->add_help_tab(array(
					'id' => 'list_available_actions',
					'title' => __('Actions disponibles', 'text-slider'),
					'content' => $this->content('list_available_actions')
				));

				$this->sidebar();

				return;

			case 'edit':
				$this->screen->add_help_tab(array(
					'id' => 'edit_sliders_overview',
					'title' => __('Vue d\'ensemble', 'text-slider'),
					'content' => $this->content('edit_sliders_overview')
				));

				$this->sidebar();

				return;
		}
	}

	private function content($name)
	{
		$content = array();

		$content['presentation_overview'] = '<p>' . __("Sur cette page, vous allez avoir un guide pour apprendre à créer et gérer votre carrousel.", 'text-slider') . '</p>';

		$content['presentation_carousel_management'] = '<p>' . __("Suivez ce guide pas à pas pour ajoutez, supprimez et gérez la visibilité des slides.<br/>", 'text-slider');
		$content['presentation_carousel_management'] .= __("Intégrez ensuite votre carrousel où vous voulez dans votre site, sois dans une page sois dans un article.<br/>", 'text-slider');
		$content['presentation_carousel_management'] .= __("Vous avez aussi la possibilité de préciser quelle taille d'images vous souhaiter utiliser parmis celles proposées.", 'text-slider') . '</p>';

		$content['list_sliders_overview'] = '<p>' . __("Sur cet écran, vous avez un apperçu de toutes vos slides.<br/>Vous pouvez personnaliser son affichage afin qu’il corresponde au mieux à vos besoins.", 'text-slider') . '</p>';

		$content['list_screen_content'] = '<p>' . __("Sur cet écran, vous pouvez personaliser l'affichage de plusieurs manières :", 'text-slider') . '</p>';
		$content['list_screen_content'] .= '<ul>';
		$content['list_screen_content'] .= '<li>' . __("Vous pouvez afficher/masquer les colonnes en fonction de vos besoins, et décider du nombre de publications à afficher par écran à l’aide de l’onglet « Options de l’écran ».", 'text-slider') . '</li>';
		$content['list_screen_content'] .= '<li>' . __("Vous pouvez filtrer la liste des slides par état en utilisant les liens au dessus de la liste des slides. La vue par défaut affiche toutes les slides.", 'text-slider') . '</li>';
		$content['list_screen_content'] .= '<li>' . __("Vous pouvez affiner la liste pour qu’elle n’affiche que les slides d’un mois donné, à l’aide du menu déroulant situé au-dessus de la liste. Cliquez sur le bouton « Filtrer » après avoir fait votre choix. Vous pouvez également affiner la liste en cliquant sur l’auteur ou autrice d’une slide.", 'text-slider') . '</li>';
		$content['list_screen_content'] .= '<li>' . __("Vous pouvez rechercher une/des slide/s en tapant son nom dans la barre de recherche située au-dessus de la liste puis en cliquant sur « Rechercher des slides ».", 'text-slider') . '</li>';
		$content['list_screen_content'] .= '</ul>';

		$content['list_available_actions'] = '<p>' . __("Passer la souris au-dessus d’une ligne de la liste des publications", 'text-slider') . '</p>';
		$content['list_available_actions'] .= '<ul>';
		$content['list_available_actions'] .= '<li>' . __("<strong>Modifier</strong> vous envoie sur l’écran de modification de cette slide. Vous pouvez également vous rendre sur cet écran en cliquant sur le titre de la slide.", 'text-slider') . '</li>';
		$content['list_available_actions'] .= '<li>' . __("<strong>Modification Rapide</strong> vous donne un accès rapide aux métadonnées de votre slide, vous permettant de mettre à jour certains détails sans devoir quitter la liste.", 'text-slider') . '</li>';
		$content['list_available_actions'] .= '<li>' . __("<strong>Corbeille</strong> retire la slide de la liste et la déplace dans la corbeille, d’où vous pourrez la supprimer définitivement.", 'text-slider') . '</li>';
		$content['list_available_actions'] .= '</ul>';

		$content['edit_sliders_overview'] = '<p>' . __("Sur cet écran, vous pouvez éditer une slide. Une slide se compose des éléments suivants:", 'text-slider') . '</p>';
		$content['edit_sliders_overview'] .= '<p>' . __("<strong>Titre</strong> est le titre que vous donnerez à votre slide. Il apparaîtra uniquement sur le récapitulatif des slides.", 'text-slider') . '</p>';
		$content['edit_sliders_overview'] .= '<p>' . __("<strong>Afficher l'image dans le carrousel</strong> est la case à cocher si vous voulez que votre slide soit visible.", 'text-slider') . '</p>';
		$content['edit_sliders_overview'] .= '<p>' . __("<strong>Image mise en avant</strong> est l'endroit où vous choisissez la photo de votre slide.", 'text-slider') . '</p>';

		if (!empty($content[$name])) {
			return $content[$name];
		}
	}

	private function sidebar()
	{
		$content = '<p><strong>' . __('Pour plus d\'informations :', 'text-slider') . '</strong></p>';
		$content .= "<p><a href='" . menu_page_url('presentation', false)  . "' >" . __('Documentation', 'text-slider') . "</a></p>";

		$this->screen->set_help_sidebar($content);
	}
}
