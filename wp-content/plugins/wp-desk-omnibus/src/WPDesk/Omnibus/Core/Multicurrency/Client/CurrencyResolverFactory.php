<?php

declare(strict_types=1);

namespace WPDesk\Omnibus\Core\Multicurrency\Client;

use WPDesk\Omnibus\Core\Utils\ExternalPlugin;

final class CurrencyResolverFactory {

	private ExternalPlugin $wcml;

	public function __construct( ExternalPlugin $wcml ) {
		$this->wcml = $wcml;
	}

	public function get_resolver(): CurrencyResolver {
		if ( $this->wcml->is_active() ) {
			return new WPMLCurrencyResolver( new RawDefaultCurrencyResolver() );
		}

		return new RawDefaultCurrencyResolver();
	}
}
