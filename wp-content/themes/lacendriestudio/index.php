<?php if (!defined('ABSPATH')) {
  exit;
} ?>

<?php get_header(); ?>
<?php if (have_posts()) :
?>
  <div class="text-black container mx-auto">
    <?php
    while (have_posts()) : the_post();
      the_title('<h2 class="text-lg text-slate-500">', '</h2>');
      the_content();
    endwhile;
    ?>
  </div>
<?php
else :
  _e('Désolé, nous n\'avons pas trouvé de résultats.');
endif;
?>
<?php get_footer(); ?>