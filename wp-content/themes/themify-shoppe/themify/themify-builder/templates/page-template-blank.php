<?php
/**
 * Blank Page Template
 *
 * Template used when Blank Canvas option is enabled for a page. Disables most everything from the active theme.
 *
 * @package Themify Builder
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php the_content(); ?>

	<?php endwhile; ?>

<?php wp_footer(); ?>

</body>
</html>