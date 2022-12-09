<?php

/**
 * Displays the site header for desktop.
 *
 * @package WordPress
 */

?>

<?php if (get_header_image()) :
  if (is_front_page()) : ?>
    <img class="mx-auto" src="<?php header_image(); ?>" width="<?php echo absint(get_custom_header()->width); ?>" height="<?php echo absint(get_custom_header()->height); ?>" alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>">
  <?php else : ?>
    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
      <img class="mx-auto" src="<?php header_image(); ?>" width="<?php echo absint(get_custom_header()->width); ?>" height="<?php echo absint(get_custom_header()->height); ?>" alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>">
    </a>
  <?php endif; ?>
<?php endif;
