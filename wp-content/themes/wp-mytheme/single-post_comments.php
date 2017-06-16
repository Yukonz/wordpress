<?php
/**
 * Created by PhpStorm.
 * User: dev70
 * Date: 12.06.17
 * Time: 14:35
 */

get_header();
echo 'post with comments';
?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <h4><?php the_title(); ?></h4>
    <p><?php the_content(); ?></p>

    <?php
    if ( comments_open() || get_comments_number() ) {
    comments_template();
    }
    ?>

<?php endwhile; else: ?>
    <h2>Error!</h2>
<?php endif; ?>

<?php get_footer(); ?>