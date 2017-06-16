<?php
/**
 * Created by PhpStorm.
 * User: dev70
 * Date: 12.06.17
 * Time: 14:35
 */
get_header();

echo 'post with thumbnails';
?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <h4><?php the_title(); ?></h4>
    <p><?php the_content(); ?></p>
    <?php the_post_thumbnail(); ?>

<?php endwhile; else: ?>
    <h2>Error!</h2>
<?php endif; ?>

<?php get_footer(); ?>



