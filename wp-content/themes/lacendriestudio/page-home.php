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


$args = array(
    'post_type'      => 'slider',
    'posts_per_page' => 3,
);
$loop = new WP_Query($args);
?>
<div class="text-base md:text-xl lg:text-2xl px-4 md:container mx-auto">
  <div class="sm:w-full md:w-11/12 lg:w-9/12 mx-auto text-justify leading-relaxed">
    <div id="carouselExampleSlidesOnly" class="carousel slide relative" data-bs-ride="carousel">
      <div class="carousel-inner relative w-full overflow-hidden">
        <?php
          $counter = 0;
          while ( $loop->have_posts() ) {
            $loop->the_post();
            $class_ac = '';
            if($counter === 0){
              $class_ac = 'active';
            }
          ?>
            <div class="carousel-item relative float-left h-[20rem] md:h-[30rem] lg:h-[35rem] xl:h-[40rem] w-full <?php echo $class_ac; ?>">
              <?php if(has_post_thumbnail()): the_post_thumbnail('cendrie_large_size', ['class' => 'block h-[20rem] md:h-[30rem] lg:h-[35rem] xl:h-[40rem] mx-auto']); endif; ?>
            </div>
          <?php
            $counter++;
          }
          wp_reset_query();
        ?>
      </div>
    </div>
  </div>
</div>
<?php
get_footer(); ?>
