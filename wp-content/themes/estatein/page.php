<?php
/**
 * Generic page.
 *
 * @package Estatein
 */

get_header();
?>
<main id="primary" class="site-main singular-page">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article <?php post_class(); ?>>
			<header class="page-intro">
				<div class="page-intro__inner site-shell"><h1><?php the_title(); ?></h1></div>
			</header>
			<div class="page-section site-shell entry-content">
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
