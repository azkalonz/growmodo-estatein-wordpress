<?php
/**
 * Accessible FAQ accordion.
 *
 * Expected arguments: items and id.
 *
 * @package Estatein
 */

$items        = $args['items'] ?? array();
$accordion_id = $args['id'] ?? 'faq-list';
?>
<div class="faq-list" id="<?php echo esc_attr( $accordion_id ); ?>" data-accordion>
	<?php foreach ( $items as $index => $item ) : ?>
		<?php
		$button_id = $accordion_id . '-button-' . $index;
		$panel_id  = $accordion_id . '-panel-' . $index;
		?>
		<article class="faq-item">
			<h3>
				<button id="<?php echo esc_attr( $button_id ); ?>" type="button" aria-expanded="true" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-accordion-trigger>
					<span><?php echo esc_html( $item['question'] ); ?></span>
					<?php estatein_icon( 'chevron-down' ); ?>
				</button>
			</h3>
			<div id="<?php echo esc_attr( $panel_id ); ?>" class="faq-item__panel" role="region" aria-labelledby="<?php echo esc_attr( $button_id ); ?>">
				<p><?php echo esc_html( $item['answer'] ); ?></p>
			</div>
		</article>
	<?php endforeach; ?>
</div>
