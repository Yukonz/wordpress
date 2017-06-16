<?php
/**
 * Created by PhpStorm.
 * User: dev70
 * Date: 12.06.17
 * Time: 11:23
 */
?>

<?php get_header(); ?>

<div id='wrapper'>
    <div id='menu'>

    <?php
    wp_nav_menu( array(
        'menu'            => 'main_menu',
        'container'       => 'div',
        'menu_class'      => 'main_menu'
    ) );

    wp_nav_menu( array(
        'menu'            => 'secondary_menu',
        'container'       => 'div',
        'menu_class'      => 'secondary_menu'
    ) );
    ?>

    </div>
    <div id='posts'>
        <h3>Иерархические записи</h3>
        <ul>
            <?php $query = new WP_Query( array( 'post_type' => 'post_hierarchical' ) ); ?>
            <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
            <li><h4><a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a></h4></li>
            <?php endwhile; else: ?>
                <h2>Error!</h2>
            <?php endif; ?>
        </ul>


        <h3>Записи с комментарими</h3>
        <ul>
            <?php $query = new WP_Query( array( 'post_type' => 'post_comments' ) ); ?>
            <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
            <li><h4><a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a></h4></li>
            <?php endwhile; else: ?>
                <h2>Error!</h2>
            <?php endif; ?>
        </ul>


        <h3>Записи с миниатюрой</h3>
        <ul>
            <?php $query = new WP_Query( array( 'post_type' => 'post_thumbnails' ) ); ?>
            <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
            <li><h4><a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a></h4></li>
            <?php endwhile; else: ?>
                <h2>Error!</h2>
            <?php endif; ?>
        </ul>
    </div>
    <div id='sidebar'>
        <?php dynamic_sidebar('sidebar_1'); ?>
    </div>
</div>

<?php get_footer(); ?>

