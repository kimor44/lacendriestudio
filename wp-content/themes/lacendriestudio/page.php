<?php if (!defined('ABSPATH')) { exit; }?>

<?php get_header(); ?>
<?php if ( have_posts() ) : 
  ?>
    <div class="text-base md:text-xl lg:text-2xl px-4 md:container mx-auto home-content">
      <div class="sm:w-full md:w-11/12 lg:w-9/12 mx-auto text-justify leading-relaxed text-white">
      <?php
        while ( have_posts() ) : the_post();
          the_title('<h2 class="text-lg">', '</h2>');
          the_content();
        endwhile;
      ?>
      </div>
    </div>
  <?php
else :
  _e( 'Désolé, nous n\'avons pas trouvé de résultats.' );
endif;
?>
<?php get_footer(); ?>