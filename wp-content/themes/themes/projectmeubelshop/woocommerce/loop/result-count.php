<?php
/**
 * Custom result count markup for Projectmeubelshop archives.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
$total = (int) $total;

if ( 1 === $total ) {
	$count_label = __( '1 product', 'projectmeubelshop-child' );
} elseif ( $total <= $per_page || -1 === $per_page ) {
	$count_label = sprintf(
		/* translators: %d: total products */
		_n( '%d product', '%d producten', $total, 'projectmeubelshop-child' ),
		$total
	);
} else {
	$first       = ( $per_page * $current ) - $per_page + 1;
	$last        = min( $total, $per_page * $current );
	$count_label = sprintf(
		/* translators: 1: first result number, 2: last result number, 3: total products */
		__( '%1$d-%2$d van %3$d producten', 'projectmeubelshop-child' ),
		$first,
		$last,
		$total
	);
}
?>
<p class="pms-tax-toolbar__count" role="status" aria-live="polite">
	<span class="pms-tax-toolbar__count-label">Resultaten</span>
	<span class="pms-tax-toolbar__count-value"><?php echo esc_html( $count_label ); ?></span>
</p>
