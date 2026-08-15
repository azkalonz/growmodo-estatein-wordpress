<?php
/**
 * Shared horizontal rail controls.
 *
 * Expected arguments: target, label, current, total.
 *
 * @package Estatein
 */

$target  = $args['target'] ?? '';
$label   = $args['label'] ?? __( 'items', 'estatein' );
$current = $args['current'] ?? '01';
$total   = $args['total'] ?? '03';
?>
<div class="rail-controls" data-rail-controls="<?php echo esc_attr( $target ); ?>">
	<p class="rail-controls__count"><span data-rail-current><?php echo esc_html( $current ); ?></span> <?php esc_html_e( 'of', 'estatein' ); ?> <?php echo esc_html( $total ); ?> <span class="screen-reader-text"><?php echo esc_html( $label ); ?></span></p>
	<div class="rail-controls__buttons">
		<button class="icon-button" type="button" data-rail-previous aria-controls="<?php echo esc_attr( $target ); ?>">
			<span class="screen-reader-text"><?php /* translators: %s: Content rail item label. */ printf( esc_html__( 'Previous %s', 'estatein' ), esc_html( $label ) ); ?></span>
			<?php estatein_icon( 'arrow-left' ); ?>
		</button>
		<button class="icon-button" type="button" data-rail-next aria-controls="<?php echo esc_attr( $target ); ?>">
			<span class="screen-reader-text"><?php /* translators: %s: Content rail item label. */ printf( esc_html__( 'Next %s', 'estatein' ), esc_html( $label ) ); ?></span>
			<?php estatein_icon( 'arrow-right' ); ?>
		</button>
	</div>
</div>
