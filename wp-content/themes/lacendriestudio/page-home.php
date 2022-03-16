<?php
/**
* Template Name: Page d'accueil
*
* @package WordPress
*/

if (!defined('ABSPATH')) { exit; }?>

<?php get_header();

if ( have_posts() ) : 
  while ( have_posts() ) : the_post();
    ?>
      <div class="md:container md:mx-auto sm:container sm:mx-auto lg:container lg:mx-auto xl:container xl:mx-auto px-4 flex flex-col">
        <?php the_content(); ?>
      </div>
    <?php
  endwhile;
else :
  _e( 'Désolé, nous n\'avons pas trouvé de résultats.' );
endif;
get_footer(); ?>
