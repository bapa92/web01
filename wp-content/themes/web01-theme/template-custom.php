<?php
/**
 * Template Name: Custom Template
 */
?>
<?php while (have_posts()) : the_post(); ?>
<!--   <?php get_template_part('templates/page', 'header'); ?> -->
  <?php get_template_part('templates/content', 'page'); ?>
<?php endwhile; ?>
 <?php
        $my_query = new WP_Query(array(
            'order' => 'ASC',
            'orderby' => 'menu_order',
            'post_parent' => $post->ID,
            'post_type' => 'page',
        ));
        if($my_query->have_posts())
        { 
            while($my_query->have_posts())
            {
                $my_query->the_post();
?>

                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
                <?php
            }
        }
        #wp_reset_postdata();
    ?>