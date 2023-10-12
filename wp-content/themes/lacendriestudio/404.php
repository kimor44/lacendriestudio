<?php

/**
 * The template for displaying the 404 template in the La Cendrie Studio theme.
 *
 * @package WordPress
 */


if (!defined('ABSPATH')) {
  exit;
} ?>

<?php get_header();
?>
<div class="text-lg md:text-xl lg:text-2xl px-4 md:container mx-auto">
  <div class="sm:w-full md:w-11/12 lg:w-9/12 mx-auto text-center leading-relaxed">
    <h1 class="text-white uppercase text-4xl"><?= _e('Page non trouv&eacute;e'); ?></h1>
    <div class="h-12"></div>
    <p class="text-white"><?= _e('Oups, je crois qu&apos;il y a une fausse note !! - '); ?><a href="<?php echo get_home_url(); ?>" class="underline"><?= _e('Retour &agrave; l&apos;accueil'); ?></a></p>
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/404.png" alt="404 image" />
  </div>
</div>
<?php
get_footer();
