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
      <div class="text-base md:text-xl lg:text-2xl px-4 md:container mx-auto home-content">
        <div class="sm:w-full md:w-11/12 lg:w-9/12 mx-auto text-justify leading-relaxed">
          <?php the_content(); ?>
        </div>
      </div>
    <?php
  endwhile;
else :
  _e( 'Désolé, nous n\'avons pas trouvé de résultats.' );
endif;
get_footer(); ?>
