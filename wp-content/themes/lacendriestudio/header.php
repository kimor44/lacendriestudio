<?php if (!defined('ABSPATH')) { exit; }?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="cendrie-h-full">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" charset="<?php bloginfo( 'charset' ); ?>"/>
<?php wp_head(); ?> <!-- Load WP objects for head-tag -->

</head>


<body <?php body_class('cendrie-body-template cendrie-h-full'); ?> id="cendrie-body">
  <header>
    <?php get_template_part( 'template-parts/header/desktop' ); ?>
  </header>

  <div class="cendrie-page">
