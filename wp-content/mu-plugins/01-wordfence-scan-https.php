<?php
/**
 * Plugin Name: Wordfence - adres startu skanu po https (Cloudflare)
 * Description: Witryna stoi za Cloudflare, ktory przekierowuje http na https kodem 301, a w bazie
 *              siteurl/home nadal maja schemat http. WordPress buduje admin_url() ze schematem
 *              "admin", ktory w set_url_scheme() schodzi do http zawsze, gdy is_ssl() jest falszem
 *              i nie ma FORCE_SSL_ADMIN - a tak jest w kontekscie crona (WP-CLI) oraz przy
 *              terminacji TLS po stronie Cloudflare. Wordfence startuje proces skanowania zadaniem
 *              nieblokujacym (wp_remote_get, timeout 0.01, blocking => false), ktore z definicji
 *              nie przechodzi przekierowan, wiec 301 konczy sprawe i admin-ajax.php nigdy sie nie
 *              uruchamia. Skutek: skany startuja i nie koncza sie od 13.04.2026.
 *              Ten filtr podnosi do https wylacznie adresy admin-ajax.php. FORCE_SSL_ADMIN nie
 *              wchodzi w gre - przy terminacji TLS na Cloudflare origin nie widzi https, wiec
 *              wp-admin wpadloby w petle przekierowan.
 *              Podpis startu skanu (wfScanEngine::_signStartURL) obcina schemat i host przed
 *              podpisaniem, wiec podmiana schematu go nie uniewaznia.
 * Author: wellmade.online
 * Version: 1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'admin_url', function ( $url, $path ) {
	if ( is_string( $path ) && 0 === strpos( $path, 'admin-ajax.php' ) && 0 === strpos( $url, 'http://' ) ) {
		return 'https://' . substr( $url, 7 );
	}
	return $url;
}, 10, 2 );
