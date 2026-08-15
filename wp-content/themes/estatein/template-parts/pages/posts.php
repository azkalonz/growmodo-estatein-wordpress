<?php
/**
 * Shared generic post listing.
 *
 * @package Estatein
 */

$posts_title       = $args['title'] ?? __( 'Latest Insights', 'estatein' );
$posts_description = $args['description'] ?? '';
?>
<main id="primary" class="site-main posts-page">
	<header class="page-intro">
		<div class="page-intro__inner site-shell">
			<h1><?php echo esc_html( $posts_title ); ?></h1>
			<?php
			if ( $posts_description ) :
				?>
				<p><?php echo esc_html( $posts_description ); ?></p><?php endif; ?>
			<?php
			if ( ! empty( $args['search'] ) ) :
				?>
				<?php get_search_form(); ?><?php endif; ?>
		</div>
	</header>
	<section class="page-section site-shell" aria-label="<?php esc_attr_e( 'Content', 'estatein' ); ?>">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<article <?php post_class( 'post-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="post-card__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php
							the_post_thumbnail(
								'large',
								array(
									'alt'     => '',
									'loading' => 'lazy',
								)
							);
							?>
																</a>
						<?php endif; ?>
						<div class="post-card__body">
							<p class="post-card__meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<?php the_excerpt(); ?>
							<a class="button button--secondary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'estatein' ); ?></a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'prev_text' => __( 'Previous', 'estatein' ),
					'next_text' => __( 'Next', 'estatein' ),
				)
			);
			?>
		<?php else : ?>
			<div class="empty-state">
				<span class="empty-state__icon"><?php estatein_icon( 'search' ); ?></span>
				<h2><?php esc_html_e( 'Nothing matched yet', 'estatein' ); ?></h2>
				<p><?php esc_html_e( 'Try a different phrase or return to the property collection.', 'estatein' ); ?></p>
				<div class="button-row"><a class="button button--primary" href="<?php echo esc_url( estatein_page_url( 'properties' ) ); ?>"><?php esc_html_e( 'Browse Properties', 'estatein' ); ?></a></div>
			</div>
		<?php endif; ?>
	</section>
</main>
