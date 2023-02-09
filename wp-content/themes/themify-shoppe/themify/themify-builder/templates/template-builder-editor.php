<?php defined( 'ABSPATH' ) || exit;?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <!-- wp_header -->
        <?php wp_head(); ?>
    </head>
    <?php
    $classes = implode(' ',get_body_class('single single-template-builder-editor'));
    ?>
    <body class="<?php echo $classes;?>">
        <div class="single-template-builder-container">
            <?php if (have_posts()) : the_post(); ?>
                    <h2 class="builder_title"><?php the_title() ?></h2>
                    <?php the_content(); ?>
            <?php endif; ?>
        </div>
        <!-- wp_footer -->
        <?php wp_footer();?> 
    </body>
</html>
