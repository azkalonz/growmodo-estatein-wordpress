<?php
/**
 * Site header.
 *
 * @package Estatein
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'estatein' ); ?></a>

<?php get_template_part( 'template-parts/components/announcement' ); ?>

<header class="site-header" data-site-header>
	<div class="site-header__inner site-shell">
		<?php if ( has_custom_logo() ) : ?>
			<div class="site-brand site-brand--custom"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<img class="site-brand__mark" src="<?php echo esc_url( estatein_asset_uri( 'images/logo-mark.svg' ) ); ?>" alt="" width="48" height="48">
				<img class="site-brand__wordmark" src="<?php echo esc_url( estatein_asset_uri( 'images/logo-wordmark.svg' ) ); ?>" alt="Estatein" width="113" height="26">
			</a>
		<?php endif; ?>

		<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'estatein' ); ?></span>
			<?php estatein_icon( 'menu', 'menu-toggle__icon' ); ?>
		</button>

		<nav id="primary-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'estatein' ); ?>" data-primary-navigation>
			<?php
			estatein_menu_or_fallback(
				'primary',
				array(
					array(
						'label' => __( 'Home', 'estatein' ),
						'url'   => home_url( '/' ),
					),
					array(
						'label' => __( 'About Us', 'estatein' ),
						'url'   => estatein_page_url( 'about-us' ),
					),
					array(
						'label' => __( 'Properties', 'estatein' ),
						'url'   => estatein_page_url( 'properties' ),
					),
					array(
						'label' => __( 'Services', 'estatein' ),
						'url'   => estatein_page_url( 'services' ),
					),
				)
			);
			?>
			<a class="button button--secondary site-header__contact" href="<?php echo esc_url( estatein_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'estatein' ); ?></a>
		</nav>
	</div>
</header>
