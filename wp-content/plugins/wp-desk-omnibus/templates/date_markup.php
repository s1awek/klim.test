<?php
/**
 * @var string $date Deprecated.
 * @var \DateTimeInterface $date_object
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<span class='js-omnibus-date'>
	<?php echo esc_html( date_i18n( get_option( 'date_format' ), $date_object->getTimestamp() + $date_object->getOffset() ) ); ?>
</span>
