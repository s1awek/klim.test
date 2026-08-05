<?php

defined( 'ABSPATH' ) || exit;

/**
 * phpseclib2_compat's SFTP::rawlist() shim assumes every listing entry is an
 * object and recurses into arrays. phpseclib3 returns each entry of a
 * non-recursive listing as an attribute array, so the shim recurses into the
 * attribute array and assigns `permissions` onto the scalar attribute values —
 * a fatal Error on PHP 8. This override supplies a correct mode->permissions
 * alias without touching the vendored package, so `composer update` stays free
 * to track new phpseclib2_compat releases (and this file can be dropped once
 * upstream ships a fix).
 */
class PMXI_Net_SFTP extends \phpseclib\Net\SFTP {

	public function rawlist( $dir = '.', $recursive = false ) {
		$result = $this->getSFTPObject()->rawlist( $dir, $recursive );
		if ( ! $result ) {
			return false;
		}
		return self::add_permissions_alias( $result );
	}

	private static function add_permissions_alias( array $files ) {
		foreach ( $files as $name => &$data ) {
			if ( is_object( $data ) ) {
				if ( isset( $data->mode ) ) {
					$data->permissions = $data->mode;
				}
			} elseif ( is_array( $data ) ) {
				if ( self::is_attribute_list( $data ) ) {
					if ( array_key_exists( 'mode', $data ) ) {
						$data['permissions'] = $data['mode'];
					}
				} else {
					$data = self::add_permissions_alias( $data );
				}
			}
		}
		return $files;
	}

	/**
	 * A single file's attribute list vs. a nested directory listing. phpseclib3
	 * tags attribute lists with a scalar "filename" and typically a scalar "mode".
	 */
	private static function is_attribute_list( array $entry ) {
		return ( isset( $entry['filename'] ) && is_scalar( $entry['filename'] ) )
			|| ( array_key_exists( 'mode', $entry ) && is_scalar( $entry['mode'] ) );
	}
}

/**
 * Flysystem's SFTP adapter hard-codes `new \phpseclib\Net\SFTP(...)` in
 * connect(). Override connect() to build our subclass instead, reusing the
 * parent's login / host-key / root handling unchanged. connect() runs after
 * disconnect() nulls the connection, so we recreate it on each (re)connect.
 */
class PMXI_SftpAdapter extends \League\Flysystem\Sftp\SftpAdapter {

	public function connect() {
		if ( ! $this->connection instanceof PMXI_Net_SFTP ) {
			$this->connection = new PMXI_Net_SFTP( $this->host, $this->port, $this->timeout );
		}
		parent::connect();
	}
}
