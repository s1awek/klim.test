# WP All Import — AI Bridge SDK

AI-powered automatic import configuration for WP All Import: the REST surface, the
Step 1 and Step 3 UI, template preparation, the file-structure cache, and the signed
frontend-layer client.

This is a **library, not a plugin**. It has no plugin header and no entry point of its
own — a host plugin embeds it and requires `gate.php`.

## Requirements

- WP All Import Pro 5.0.4+ **or** WP All Import (free) 4.2.0+
- PHP 7.4+

## Installing in a host plugin

```json
"repositories": [
  { "type": "vcs", "url": "https://github.com/soflyy/wpai-bridge-plugin.git" }
],
"require": { "soflyy/wpai-bridge": "^1.1" }
```

Then load it from **file scope in the host's main plugin file**, before
`PMXI_Plugin` is instantiated:

    require_once __DIR__ . '/vendor/soflyy/wpai-bridge/gate.php';

and render the settings seam somewhere in the host's settings view:

    <?php do_action( 'pmxi_settings_sections', $post ); ?>

Those two lines are the entire host-side integration. `gate.php` owns the setting
that turns the SDK on: the option, the Experimental section on the settings screen,
saving it, and the decision to require `bootstrap.php` at all. A host carries no
copy of the feature's UI or wording, so free and Pro cannot drift.

The setting ships **off**. While it is off the SDK is never loaded, so nothing it
registers exists.

Placement of the seam matters: WP All Import's Save button lives inside the final
settings section's row, so the seam belongs *before* that section, not after it.

Three rules matter. The first two are about load order:

1. **File scope, before `PMXI_Plugin::getInstance()`.** WP All Import decides inside
   its constructor whether to load its libraries, via
   `pmxi_is_admin_dashboard_or_cron_import`. The bridge's REST namespace needs them,
   so that filter must already be registered by then. (Free has no such gate, but the
   same placement is correct there — one rule beats two with an exception.)
2. **Do not route this through Composer's autoloader.** The SDK has its own class
   loader and declares no `autoload` block. In the free plugin the Composer autoloader
   is required from *inside* `PMXI_Plugin`, which is too late; an explicit
   `require_once` is predictable in both hosts.
3. **Require `gate.php`, not `bootstrap.php`.** Requiring `bootstrap.php` directly
   loads the SDK unconditionally and bypasses the opt-in setting. That is only
   correct in a test bootstrap that means to force it on.

Commit `vendor/soflyy/wpai-bridge/` to the host repository: releases ship the git
archive of the host's tag, and end users never run Composer.

The host must also run the cleanup from its own `uninstall.php`:

    require_once __DIR__ . '/vendor/soflyy/wpai-bridge/includes/class-uninstall.php';
    WPAI_Bridge_Uninstall::run();

There is no activation hook to worry about: the cache table is created by the
loader's DB-version check, which also handles upgrades.

## Do not Strauss-prefix this package

Third-party libraries in a host plugin's `vendor/` should be prefixed to avoid
collisions. This one must not be, and the reason is not stylistic: **wpai-MCP
reaches these classes by name, as strings** — `class_exists( 'WPAI_Bridge_FL_Signer' )`
and `WPAI_MCP_Bridge_Service::call( 'WPAI_Bridge_LLM_Config_API', … )`. Strauss does
not rewrite class names inside string literals, so prefixing renames the classes and
MCP silently stops finding them: guidance requests go unsigned and every
bridge-backed tool returns `handler unavailable`, with nothing failing in the host.

`delete_vendor_packages` would also remove the directory the host's `require_once`
points at, and the SDK's own class loader maps `WPAI_Bridge_*` to
`includes/class-*.php`, which prefixed names no longer match.

If a host adopts Strauss, exclude this package:

```json
"exclude_from_prefix": { "packages": ["soflyy/wpai-bridge"] },
"exclude_from_copy":   { "packages": ["soflyy/wpai-bridge"] }
```

Prefixing is unnecessary here anyway: these class names are already globally unique
and first-party, not a generic library that two plugins might bundle at different
versions.

## Multiple copies

A normal install has exactly one copy. If a second is loaded anyway, `bootstrap.php`
is define-guarded so the later require is inert rather than fatal. To pin a specific
copy — debugging, or a test environment staging its own build — define
`WPAI_BRIDGE_SDK_DIR` before the first require.

## Updating

    composer update soflyy/wpai-bridge
    # commit the refreshed vendor/soflyy/wpai-bridge/ and composer.lock

## Tests

    composer install
    WP_TESTS_DIR=/tmp/wordpress-tests-lib vendor/bin/phpunit

Needs the classic WP tests lib with `wp-all-import-pro` installed under its
`WP_CONTENT_DIR`. `tests/bootstrap.php` loads the SDK exactly as a host does. CI runs
this suite through the `wpai-bridge-plugin` suite in `soflyy/wpai-test-harness`.
