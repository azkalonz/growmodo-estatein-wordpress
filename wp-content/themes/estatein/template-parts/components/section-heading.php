<?php
/**
 * Reusable section heading.
 *
 * Expected arguments: title, copy, id, button_label, button_url, modifier.
 *
 * @package Estatein
 */

$heading_title = $args['title'] ?? '';
$heading_copy  = $args['copy'] ?? '';
$heading_id    = $args['id'] ?? '';
$button_label  = $args['button_label'] ?? '';
$button_url    = $args['button_url'] ?? '';
$modifier      = $args['modifier'] ?? '';
?>
<header class="section-heading <?php echo esc_attr( $modifier ? 'section-heading--' . sanitize_html_class( $modifier ) : '' ); ?>">
	<div class="section-heading__content">
		<img class="section-heading__spark" src="<?php echo esc_url( estatein_asset_uri( 'images/decor/spark.svg' ) ); ?>" alt="" width="68" height="30" loading="lazy">
		<h2<?php echo $heading_id ? ' id="' . esc_attr( $heading_id ) . '"' : ''; ?>><?php echo esc_html( $heading_title ); ?></h2>
		<?php if ( $heading_copy ) : ?>
			<p><?php echo esc_html( $heading_copy ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $button_label && $button_url ) : ?>
		<a class="button button--secondary section-heading__action" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $button_label ); ?></a>
	<?php endif; ?>
</header>
