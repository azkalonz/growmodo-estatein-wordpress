<?php
/**
 * Generic single post.
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
				<div class="page-intro__inner site-shell">
					<p class="entry-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
					<h1><?php the_title(); ?></h1>
				</div>
			</header>
			<div class="page-section site-shell entry-content">
				<?php
				if ( has_post_thumbnail() ) :
					?>
					<?php
					the_post_thumbnail(
						'full',
						array(
							'class'   => 'entry-hero',
							'loading' => 'eager',
						)
					);
					?>
					<?php endif; ?>
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
			</div>
		</article>
		<nav class="post-navigation site-shell" aria-label="<?php esc_attr_e( 'Post navigation', 'estatein' ); ?>">
			<?php previous_post_link( '<div>%link</div>', esc_html__( 'Previous: %title', 'estatein' ) ); ?>
			<?php next_post_link( '<div>%link</div>', esc_html__( 'Next: %title', 'estatein' ) ); ?>
		</nav>
	<?php endwhile; ?>
</main>
<?php
get_footer();
