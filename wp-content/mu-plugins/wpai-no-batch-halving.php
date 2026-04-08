<?php
/**
 * WP All Import — disable server-side batch halving
 *
 * Gdy JS klient WP All Import wysyła ?failures=N>0 (po sieciowym glitchu lub
 * po kliknięciu "Continue Import"), serwer w
 * wp-all-import-pro/controllers/admin/import.php (~linia 2905) dzieli
 * records_per_request na pół. Po 2-3 takich zdarzeniach batch spada z 100 do
 * 12, import wlecze się i klient ogląda kolejne komunikaty terminate.
 *
 * Ten mu-plugin zeruje parametr `failures` zanim WP All Import go odczyta —
 * dzięki czemu auto-resume (Settings → Auto-retry failed import) sam ponowi
 * batch, ale ZAWSZE z pełną prędkością ustawioną w konfiguracji importu.
 *
 * Działa wyłącznie na requeście /wp-admin/admin.php?page=pmxi-admin-import&action=process
 * Nie modyfikuje żadnych plików pluginu.
 *
 * Włączanie/wyłączanie: define('WPAI_NO_BATCH_HALVING', true) w wp-config.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPAI_NO_BATCH_HALVING' ) || ! WPAI_NO_BATCH_HALVING ) {
	return;
}

add_action( 'plugins_loaded', function () {
	if ( ! is_admin() ) {
		return;
	}
	if ( ( $_GET['page'] ?? '' ) !== 'pmxi-admin-import' ) {
		return;
	}
	if ( ( $_GET['action'] ?? '' ) !== 'process' ) {
		return;
	}
	if ( empty( $_GET['failures'] ) ) {
		return;
	}

	$_GET['failures']     = 0;
	$_REQUEST['failures'] = 0;
}, 1 );
