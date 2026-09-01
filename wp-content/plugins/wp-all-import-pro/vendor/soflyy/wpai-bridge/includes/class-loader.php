<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class WPAI_Bridge_Loader {

    private static $initialized = false;

    public static function register( $version, $dir ) {
        if ( ! defined( 'WPAI_BRIDGE_VERSION' ) ) {
            $resolved = ( null === $version || '' === $version )
                ? self::resolve_version( $dir )
                : (string) $version;
            define( 'WPAI_BRIDGE_VERSION', $resolved );
        }
        if ( ! defined( 'WPAI_BRIDGE_DIR' ) ) {
            define( 'WPAI_BRIDGE_DIR', trailingslashit( $dir ) );
        }
        if ( ! defined( 'WPAI_BRIDGE_URL' ) ) {
            define( 'WPAI_BRIDGE_URL', self::resolve_url( trailingslashit( $dir ) ) );
        }

        if ( did_action( 'plugins_loaded' ) && ! doing_action( 'plugins_loaded' ) ) {
            self::init();
            return;
        }
        add_action( 'plugins_loaded', array( __CLASS__, 'init' ), 20 );
    }

    /**
     * The git tag is the only place this package's version is written, so read it
     * back from Composer's metadata rather than keeping a literal in sync by hand.
     *
     * The file is read from disk before \Composer\InstalledVersions is consulted
     * because free requires its autoloader from inside PMXI_Plugin::__construct(),
     * which can run after this — the same timing that stops us shipping an
     * autoload block.
     */
    private static function resolve_version( $dir ) {
        $installed = trailingslashit( $dir ) . '../../composer/installed.php';

        if ( is_readable( $installed ) ) {
            $data = include $installed;
            if ( isset( $data['versions']['soflyy/wpai-bridge']['pretty_version'] ) ) {
                return ltrim( (string) $data['versions']['soflyy/wpai-bridge']['pretty_version'], 'v' );
            }
        }

        if ( class_exists( '\Composer\InstalledVersions' )
            && \Composer\InstalledVersions::isInstalled( 'soflyy/wpai-bridge' ) ) {
            $pretty = \Composer\InstalledVersions::getPrettyVersion( 'soflyy/wpai-bridge' );
            if ( is_string( $pretty ) && '' !== $pretty ) {
                return ltrim( $pretty, 'v' );
            }
        }

        return '0.0.0-dev';
    }

    public static function init() {
        if ( self::$initialized ) {
            return;
        }
        self::$initialized = true;

        require_once WPAI_BRIDGE_DIR . 'includes/class-host.php';

        if ( ! WPAI_Bridge_Host::is_supported() ) {
            add_action( 'admin_notices', array( 'WPAI_Bridge_Host', 'render_unsupported_notice' ) );
            return;
        }

        // Also loaded by the gate; required here for hosts that load the SDK direct.
        require_once WPAI_BRIDGE_DIR . 'includes/remote-status.php';
        require_once WPAI_BRIDGE_DIR . 'includes/functions.php';
        spl_autoload_register( array( __CLASS__, 'autoload' ) );
        self::init_components();
    }

    public static function autoload( $class ) {
        if ( strpos( $class, 'WPAI_Bridge_' ) !== 0 ) {
            return;
        }

        $class_file = str_replace( 'WPAI_Bridge_', '', $class );
        $class_file = str_replace( '_', '-', strtolower( $class_file ) );
        $class_file = 'class-' . $class_file . '.php';

        $file_path = WPAI_BRIDGE_DIR . 'includes/' . $class_file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }

    /**
     * Resolved from the SDK directory rather than a plugin basename, because the
     * SDK lives inside its host's vendor tree, not at a plugin root.
     */
    private static function resolve_url( $dir ) {
        $url = plugin_dir_url( $dir . 'bootstrap.php' );
        return trailingslashit( apply_filters( 'wpai_bridge_url', $url, $dir ) );
    }

    private static function init_components() {
        // The only path that creates the cache table: an embedded SDK has no
        // activation hook to run it from.
        if ( get_option( WPAI_Bridge_File_Structure_Cache::DB_VERSION_OPTION ) !== WPAI_Bridge_File_Structure_Cache::DB_VERSION ) {
            if ( ! WPAI_Bridge_File_Structure_Cache::create_table() ) {
                add_action( 'admin_notices', array( 'WPAI_Bridge_Host', 'render_table_failure_notice' ) );
            }
        }

        WPAI_Bridge_LLM_Config_API::getInstance();
        WPAI_Bridge_AJAX_Handlers::getInstance();
        WPAI_Bridge_UI_Injector::getInstance();
        WPAI_Bridge_Import_Flow::getInstance();
        WPAI_Bridge_File_Processor::getInstance();
        WPAI_Bridge_Template_Preparation::getInstance();
        WPAI_Bridge_File_Structure_Cache::getInstance();
        WPAI_Bridge_Import_Outcome::getInstance();
    }
}
