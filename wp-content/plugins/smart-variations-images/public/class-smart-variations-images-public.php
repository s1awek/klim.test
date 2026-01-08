<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.rosendo.pt
 * @since      1.0.0
 *
 * @package    Smart_Variations_Images
 * @subpackage Smart_Variations_Images/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for enqueuing the public-facing stylesheet and JavaScript.
 *
 * @package    Smart_Variations_Images
 * @subpackage Smart_Variations_Images/public
 * @author     David Rosendo <david@rosendo.pt>
 */
class Smart_Variations_Images_Public {
    /**
     * Contains an array of script handles registered by the plugin.
     *
     * @since    1.0.0
     * @access   public
     * @var      array $scripts Array of registered script handles.
     */
    public $scripts = [];

    /**
     * Contains an array of style handles registered by the plugin.
     *
     * @since    1.0.0
     * @access   public
     * @var      array $styles Array of registered style handles.
     */
    public $styles = [];

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The plugin options.
     *
     * @since    1.0.0
     * @access   private
     * @var      stdClass $options The current plugin options.
     */
    private $options;

    /**
     * Holds the current instance for template interception.
     *
     * @since    5.2.20
     * @access   protected
     * @var      Smart_Variations_Images_Public|null $current_instance Stored instance reference.
     */
    protected static $current_instance = null;

    /**
     * Stack of Smart_Variations_Images_Public instances for nested template calls.
     *
     * @since    5.2.20
     * @access   protected
     * @var      array<int, Smart_Variations_Images_Public> $instance_stack Instance stack.
     */
    protected static $instance_stack = [];

    /**
     * Stack of template contexts while overriding WooCommerce templates.
     *
     * @since    5.2.20
     * @access   protected
     * @var      array<int, array<string, mixed>> $template_stack Template context stack.
     */
    protected static $template_stack = [];

    /**
     * Keeps track of products already localized to scripts to avoid duplication.
     *
     * @since    5.2.20
     * @access   protected
     * @var      array<int, bool> $localized_products Indexed list of localized product IDs.
     */
    protected static $localized_products = [];

    /**
     * Flag to avoid enqueueing inline styles multiple times per request.
     *
     * @since    5.2.20
     * @access   protected
     * @var      bool $inline_styles_added Whether the inline CSS for the placeholder skeleton was added.
     */
    protected static $inline_styles_added = false;

    /**
     * Flag to determine if SitePress should run for WPML compatibility.
     *
     * @since    1.0.0
     * @access   protected
     * @var      bool $runSitePress Whether to run SitePress for WPML compatibility.
     */
    protected $runSitePress;

    /**
     * The product ID being processed.
     *
     * @since    1.0.0
     * @access   protected
     * @var      int|bool $pid The product ID, or false if not set.
     */
    protected $pid;

    /**
     * Cache store for loaded product data to avoid repeated queries per request.
     *
     * @since    5.2.20
     * @var      array<string, array> $product_cache
     */
    protected static $product_cache = [];

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string   $plugin_name The name of the plugin.
     * @param    string   $version     The version of this plugin.
     * @param    stdClass $options     The plugin options.
     */
    public function __construct( string $plugin_name, string $version, stdClass $options ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = $options;
        $this->options->template = wp_get_theme()->template;
        $this->pid = false;
        $this->runSitePress = false;
        self::set_current_instance( $this );
    }

    /**
     * Determine if the current page should load SVI functionality.
     *
     * @since    1.0.0
     * @return   bool True if SVI should load, false otherwise.
     */
    public function handleLoadCondition() : bool {
        global $post;
        if ( $post instanceof WP_Post ) {
            return function_exists( 'is_product' ) && is_product() || has_shortcode( $post->post_content, 'product_page' ) || is_woocommerce() || has_shortcode( $post->post_content, 'product_category' ) || has_shortcode( $post->post_content, 'dt_products_carousel' );
        }
        return false;
    }

    /**
     * Register and enqueue the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function load_scripts() : void {
        $this->register_scripts();
        $this->register_styles();
        $this->enqueue_script( 'imagesloaded' );
        if ( !$this->handleLoadCondition() ) {
            return;
        }
        if ( property_exists( $this->options, 'slider' ) && ($this->options->slider || property_exists( $this->options, 'lightbox_thumbnails' ) && $this->options->lightbox_thumbnails && property_exists( $this->options, 'lightbox' ) && $this->options->lightbox) ) {
            $this->enqueue_script( $this->plugin_name . '-swiper' );
            $this->enqueue_style( $this->plugin_name . '-swiper' );
        }
        if ( $this->options->lens ) {
            $this->enqueue_script( 'ezplus' );
        }
        if ( $this->options->lightbox ) {
            $handle = 'photoswipe' . SMART_SCRIPT_DEBUG . '.js';
            $list = 'enqueued';
            if ( !wp_script_is( $handle, $list ) ) {
                $this->enqueue_script( $this->plugin_name . '-photoswipe' );
                $this->enqueue_script( $this->plugin_name . '-photoswipe-ui-default' );
                $this->enqueue_style( $this->plugin_name . '-photoswipe' );
                $this->enqueue_style( $this->plugin_name . '-photoswipe-default-skin' );
            }
        }
        $this->loadMainFiles();
    }

    /**
     * Load the main JavaScript and CSS files for the plugin.
     *
     * @since    1.0.0
     */
    public function loadMainFiles() : void {
        $this->enqueue_script( $this->plugin_name . '-manifest' );
        $this->enqueue_script( $this->plugin_name . '-vendor' );
        $this->enqueue_script( $this->plugin_name );
        $this->enqueue_style( $this->plugin_name );
        $this->ensure_inline_styles();
        wp_localize_script( $this->plugin_name, 'wcsvi', [
            'prod'     => ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? false : true ),
            'options'  => $this->options,
            'call'     => admin_url( 'admin-ajax.php' ),
            'version'  => $this->version,
            'products' => [],
        ] );
    }

    /**
     * Register a script for use.
     *
     * @since    1.0.0
     * @param    string   $handle    Name of the script. Should be unique.
     * @param    string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
     * @param    string[] $deps      An array of registered script handles this script depends on.
     * @param    string|null $version String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
     * @param    bool     $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default true.
     */
    public function register_script(
        string $handle,
        string $path,
        array $deps = ['jquery'],
        ?string $version = '',
        bool $in_footer = true
    ) : void {
        $this->scripts[] = $handle;
        wp_register_script(
            $handle,
            $path,
            $deps,
            $version,
            $in_footer
        );
    }

    /**
     * Register and enqueue a script for use.
     *
     * @since    1.0.0
     * @param    string   $handle    Name of the script. Should be unique.
     * @param    string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
     * @param    string[] $deps      An array of registered script handles this script depends on.
     * @param    string|null $version String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
     * @param    bool     $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default true.
     */
    public function enqueue_script(
        string $handle,
        string $path = '',
        array $deps = ['jquery'],
        ?string $version = '',
        bool $in_footer = true
    ) : void {
        if ( !in_array( $handle, $this->scripts, true ) && $path ) {
            $this->register_script(
                $handle,
                $path,
                $deps,
                $version,
                $in_footer
            );
        }
        wp_enqueue_script( $handle );
    }

    /**
     * Register a style for use.
     *
     * @since    1.0.0
     * @param    string   $handle  Name of the stylesheet. Should be unique.
     * @param    string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
     * @param    string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
     * @param    string|null $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
     * @param    string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
     * @param    bool     $has_rtl If has RTL version to load too.
     */
    public function register_style(
        string $handle,
        string $path,
        array $deps = [],
        ?string $version = '',
        string $media = 'all',
        bool $has_rtl = false
    ) : void {
        $this->styles[] = $handle;
        wp_register_style(
            $handle,
            $path,
            $deps,
            $version,
            $media
        );
        if ( $has_rtl ) {
            wp_style_add_data( $handle, 'rtl', 'replace' );
        }
    }

    /**
     * Register and enqueue a style for use.
     *
     * @since    1.0.0
     * @param    string   $handle  Name of the stylesheet. Should be unique.
     * @param    string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
     * @param    string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
     * @param    string|null $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
     * @param    string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
     * @param    bool     $has_rtl If has RTL version to load too.
     */
    public function enqueue_style(
        string $handle,
        string $path = '',
        array $deps = [],
        ?string $version = '',
        string $media = 'all',
        bool $has_rtl = false
    ) : void {
        if ( !in_array( $handle, $this->styles, true ) && $path ) {
            $this->register_style(
                $handle,
                $path,
                $deps,
                $version,
                $media,
                $has_rtl
            );
        }
        wp_enqueue_style( $handle );
    }

    /**
     * Register all scripts for the plugin.
     *
     * @since    1.0.0
     */
    public function register_scripts() : void {
        $version = $this->version;
        $register_scripts = [
            'imagesloaded'                                => [
                'src'     => '//unpkg.com/imagesloaded@4/imagesloaded.pkgd' . SMART_SCRIPT_DEBUG . '.js',
                'deps'    => [],
                'version' => $version,
            ],
            $this->plugin_name . '-swiper'                => [
                'src'     => '//cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
                'deps'    => [],
                'version' => '11.0.0',
            ],
            'ezplus'                                      => [
                'src'     => self::get_asset_url( 'js/jquery.ez-plus' . SMART_SCRIPT_DEBUG . '.js' ),
                'deps'    => ['jquery'],
                'version' => $version,
            ],
            $this->plugin_name . '-photoswipe'            => [
                'src'     => '//cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.3/photoswipe' . SMART_SCRIPT_DEBUG . '.js',
                'deps'    => [],
                'version' => '4.1.3',
            ],
            $this->plugin_name . '-photoswipe-ui-default' => [
                'src'     => '//cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.3/photoswipe-ui-default' . SMART_SCRIPT_DEBUG . '.js',
                'deps'    => [$this->plugin_name . '-photoswipe'],
                'version' => '4.1.3',
            ],
            $this->plugin_name . '-manifest'              => [
                'src'     => self::get_asset_url( 'js/manifest' . SMART_SCRIPT_DEBUG . '.js' ),
                'deps'    => ['jquery'],
                'version' => $version,
            ],
            $this->plugin_name . '-vendor'                => [
                'src'     => self::get_asset_url( 'js/vendor' . SMART_SCRIPT_DEBUG . '.js' ),
                'deps'    => [$this->plugin_name . '-manifest'],
                'version' => $version,
            ],
            $this->plugin_name                            => [
                'src'     => self::get_asset_url( 'js/smart-variations-images-public' . SMART_SCRIPT_DEBUG . '.js' ),
                'deps'    => ['jquery', $this->plugin_name . '-vendor', 'imagesloaded'],
                'version' => $version,
            ],
        ];
        if ( $this->options->lightbox ) {
            $handle = 'photoswipe' . SMART_SCRIPT_DEBUG . '.js';
            $list = 'enqueued';
            if ( !wp_script_is( $handle, $list ) ) {
                $register_scripts[$this->plugin_name] = [
                    'src'     => self::get_asset_url( 'js/smart-variations-images-public' . SMART_SCRIPT_DEBUG . '.js' ),
                    'deps'    => [
                        'jquery',
                        $this->plugin_name . '-vendor',
                        'imagesloaded',
                        $this->plugin_name . '-photoswipe',
                        $this->plugin_name . '-photoswipe-ui-default'
                    ],
                    'version' => $version,
                ];
            }
        }
        foreach ( $register_scripts as $name => $props ) {
            $this->register_script(
                $name,
                $props['src'],
                $props['deps'],
                $props['version']
            );
        }
    }

    /**
     * Register all styles for the plugin.
     *
     * @since    1.0.0
     */
    public function register_styles() : void {
        $version = $this->version;
        $register_styles = [
            $this->plugin_name                              => [
                'src'     => self::get_asset_url( 'css/smart-variations-images-public' . SMART_SCRIPT_DEBUG . '.css' ),
                'deps'    => [],
                'version' => $version,
                'has_rtl' => false,
            ],
            $this->plugin_name . '-swiper'                  => [
                'src'     => '//cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
                'deps'    => [],
                'version' => '11.0.0',
                'has_rtl' => false,
            ],
            $this->plugin_name . '-photoswipe'              => [
                'src'     => '//cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.3/photoswipe' . SMART_SCRIPT_DEBUG . '.css',
                'deps'    => [],
                'version' => $version,
                'has_rtl' => false,
            ],
            $this->plugin_name . '-photoswipe-default-skin' => [
                'src'     => '//cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.3/default-skin/default-skin' . SMART_SCRIPT_DEBUG . '.css',
                'deps'    => [$this->plugin_name . '-photoswipe'],
                'version' => $version,
                'has_rtl' => false,
            ],
        ];
        if ( svi_fs()->can_use_premium_code__premium_only() && $this->options->video ) {
            $register_styles['plyr'] = [
                'src'     => '//cdnjs.cloudflare.com/ajax/libs/plyr/3.7.8/plyr.css',
                'deps'    => [],
                'version' => $version,
                'has_rtl' => false,
            ];
        }
        foreach ( $register_styles as $name => $props ) {
            $this->register_style(
                $name,
                $props['src'],
                $props['deps'],
                $props['version'],
                'all',
                $props['has_rtl']
            );
        }
    }

    /**
     * Return the URL of an asset.
     *
     * @since    1.0.0
     * @param    string $path The path to the asset relative to the plugin directory.
     * @return   string The full URL to the asset.
     */
    private static function get_asset_url( string $path ) : string {
        return plugins_url( $path, __FILE__ );
    }

    /**
     * Remove hooks to ensure the plugin works properly.
     *
     * @since    1.1.1
     */
    public function remove_hooks() : void {
        global $product;
        $run = true;
        if ( $product instanceof WC_Product ) {
            $run = $this->validate_run( $product );
        }
        if ( !$run ) {
            return;
        }
        remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 10 );
        remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
        // Mr. Tailor
        remove_action( 'woocommerce_before_single_product_summary_product_images', 'woocommerce_show_product_images', 20 );
        remove_action( 'woocommerce_product_summary_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
        // Electro support
        remove_action( 'woocommerce_before_single_product_summary', 'electro_show_product_images', 20 );
        // Aurum support
        remove_action( 'woocommerce_before_single_product_summary', 'aurum_woocommerce_show_product_images', 25 );
        // Remove images from Bazar theme
        if ( class_exists( 'YITH_WCMG' ) ) {
            $this->remove_filters_for_anonymous_class(
                'woocommerce_before_single_product_summary',
                'YITH_WCMG_Frontend',
                'show_product_images',
                20
            );
            $this->remove_filters_for_anonymous_class(
                'woocommerce_product_thumbnails',
                'YITH_WCMG_Frontend',
                'show_product_thumbnails',
                20
            );
        }
    }

    /**
     * Remove hooks/filters after theme setup to ensure the plugin works properly.
     *
     * @since    1.1.1
     */
    public function after_setup_theme() : void {
        if ( class_exists( 'Razzi\\Theme' ) ) {
            add_filter( 'razzi_product_gallery_is_slider', function ( $data ) {
                return 0;
            }, 9999 );
        }
    }

    /**
     * Render the frontend for builders by filtering the WooCommerce template.
     *
     * @since    1.0.0
     * @param    string     $located       The path to the located template.
     * @param    string     $template_name The name of the template being loaded.
     * @param    array|null $args          Arguments passed to the template (may be null for some calls).
     * @param    string|null $template_path The template path (may be null from some plugins).
     * @param    string     $default_path  The default path.
     * @return   string The path to the template to use.
     */
    public function filter_wc_get_template(
        string $located,
        string $template_name,
        ?array $args,
        ?string $template_path,
        string $default_path
    ) : string {
        global $product;
        if ( !$product instanceof WC_Product || defined( 'DOING_AJAX' ) && !$this->options->quick_view ) {
            return $located;
        }
        $run = $this->validate_run( $product );
        if ( !$run ) {
            return $located;
        }
        $theme_file = 'single-product/product-image.php';
        if ( $this->options->template === 'flatsome' ) {
            add_filter(
                'woocommerce_single_product_image_thumbnail_html',
                '__return_empty_string',
                10,
                2
            );
            $theme_file = 'woocommerce/single-product/product-gallery-thumbnails.php';
        }
        if ( $this->options->template === 'porto' ) {
            add_filter(
                'woocommerce_single_product_image_thumbnail_html',
                '__return_empty_string',
                10,
                2
            );
            add_filter(
                'woocommerce_single_product_image_html',
                '__return_empty_string',
                10,
                2
            );
            $theme_file = 'single-product/product-thumbnails.php';
        }
        if ( $template_name === $theme_file ) {
            self::set_current_instance( $this );
            self::push_template_context( [
                'located'       => $located,
                'template_name' => $template_name,
                'args'          => $args,
                'template_path' => $template_path,
                'default_path'  => $default_path,
            ] );
            return plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/smart-variations-images-product-template.php';
        }
        return $located;
    }

    /**
     * Render the frontend shortcode for gallery display.
     *
     * @since    1.0.0
     * @return   string The rendered shortcode output.
     */
    public function render_sc_frontend() : string {
        ob_start();
        include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/smart-variations-images-public-display.php';
        $output_string = ob_get_clean();
        return $output_string;
    }

    /**
     * Render the frontend app.
     *
     * @since    1.0.0
     */
    public function render_frontend() : void {
        global $product;
        if ( $this->options->template === 'Divi' && $this->validate_runningDivi( $product ) && !wp_doing_ajax() ) {
            return;
        }
        $run = true;
        if ( $product instanceof WC_Product ) {
            $run = $this->validate_run( $product );
        }
        if ( !$run ) {
            return;
        }
        include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/smart-variations-images-public-display.php';
    }

    /**
     * Capture the main frontend template output for direct rendering within template overrides.
     *
     * @since    5.2.20
     * @return   string Captured HTML output.
     */
    public function capture_frontend_template() : string {
        ob_start();
        $this->render_frontend();
        return (string) ob_get_clean();
    }

    /**
     * Generate lightweight gallery markup to prevent layout shifts before Vue initializes.
     *
     * @since    5.2.20
     * @param    array $data       Gallery payload returned by loadProduct().
     * @param    int   $product_id Product identifier.
     * @return   string            Pre-rendered HTML markup.
     */
    public function get_initial_gallery_markup( array $data, int $product_id ) : string {
        if ( !isset( $data['images'] ) || !is_array( $data['images'] ) || empty( $data['images'] ) ) {
            return '';
        }
        $primary = $data['images'][0];
        $width = ( isset( $primary['large_image_width'] ) ? intval( $primary['large_image_width'] ) : (( isset( $primary['width'] ) ? intval( $primary['width'] ) : 0 )) );
        $height = ( isset( $primary['large_image_height'] ) ? intval( $primary['large_image_height'] ) : (( isset( $primary['height'] ) ? intval( $primary['height'] ) : 0 )) );
        $ratio = 100;
        if ( $width > 0 && $height > 0 ) {
            $ratio = round( $height / max( $width, 1 ) * 100, 2 );
        }
        $thumbnails = [];
        if ( count( $data['images'] ) > 1 ) {
            $thumbnails = array_slice( $data['images'], 0, min( 6, count( $data['images'] ) ) );
        }
        ob_start();
        ?>
        <div class="svi-initial-gallery" aria-hidden="true">
            <div class="svi-initial-main" style="--svi-aspect: <?php 
        echo esc_attr( $ratio );
        ?>%;">
                <span class="svi-skeleton"></span>
            </div>
            <?php 
        if ( !empty( $thumbnails ) ) {
            ?>
                <ul class="svi-initial-thumbs">
                    <?php 
            foreach ( $thumbnails as $thumb_index => $thumb ) {
                ?>
                        <li class="svi-initial-thumb">
                            <span class="svi-skeleton"></span>
                        </li>
                    <?php 
            }
            ?>
                </ul>
            <?php 
        }
        ?>
        </div>
        <?php 
        return trim( ob_get_clean() );
    }

    /**
     * Prime the localized JavaScript object with product gallery data to avoid large data attributes.
     *
     * @since    5.2.20
     * @param    int   $product_id Product identifier.
     * @param    array $data       Prepared gallery payload.
     */
    public function prime_product_data( int $product_id, array $data ) : void {
        if ( wp_doing_ajax() || isset( self::$localized_products[$product_id] ) ) {
            return;
        }
        $encoded = wp_json_encode( $data );
        if ( false === $encoded || null === $encoded ) {
            return;
        }
        $inline = 'window.wcsvi = window.wcsvi || {}; wcsvi.products = wcsvi.products || {}; wcsvi.products[' . $product_id . '] = ' . $encoded . ';';
        wp_add_inline_script( $this->plugin_name, $inline, 'before' );
        self::$localized_products[$product_id] = true;
    }

    /**
     * Normalises attribute values into comparable slugs and combination keys.
     *
     * @since    5.2.20
     * @param    array $values Attribute values to normalise.
     * @return   array Normalised slug combinations.
     */
    protected function normalize_attribute_values( array $values ) : array {
        $slugs = [];
        foreach ( $values as $value ) {
            if ( !is_scalar( $value ) ) {
                continue;
            }
            $value = trim( (string) $value );
            if ( $value === '' ) {
                continue;
            }
            $slugs[] = sanitize_title( strtolower( $value ) );
        }
        $slugs = array_values( array_unique( array_filter( $slugs ) ) );
        if ( count( $slugs ) > 1 ) {
            $combo_text = sanitize_title( strtolower( implode( '_svipro_', $slugs ) ) );
            $count = substr_count( $combo_text, '_svipro_' );
            if ( $count > 1 ) {
                for ($x = 2; $x <= $count; $x++) {
                    $pos = $this->strposX( $combo_text, '_svipro_', $x );
                    if ( $pos !== false ) {
                        $slugs[] = substr( $combo_text, 0, $pos );
                    }
                }
            }
            $slugs[] = $combo_text;
        }
        return array_values( array_unique( $slugs ) );
    }

    /**
     * Builds a slug-to-image map from the stored SVI dataset.
     *
     * @since    5.2.20
     * @param    array $sviEntries Raw SVI dataset.
     * @return   array<string, mixed> Map of slug combinations to image identifiers.
     */
    protected function build_slug_image_map( array $sviEntries ) : array {
        $map = [];
        foreach ( $sviEntries as $entry ) {
            if ( !isset( $entry['slugs'] ) || !is_array( $entry['slugs'] ) || empty( $entry['slugs'] ) ) {
                continue;
            }
            if ( !isset( $entry['imgs'][0] ) ) {
                continue;
            }
            $slugs = array_map( static function ( $slug ) {
                return sanitize_title( strtolower( (string) $slug ) );
            }, $entry['slugs'] );
            $slugs = array_filter( $slugs );
            if ( empty( $slugs ) ) {
                continue;
            }
            $combo = sanitize_title( strtolower( implode( '_svipro_', $slugs ) ) );
            $map[$combo] = $entry['imgs'][0];
        }
        return $map;
    }

    /**
     * Resolves the most appropriate SVI image ID for the provided slug combinations.
     *
     * @since    5.2.20
     * @param    array $slugs_confirm Slugs representing the current selection.
     * @param    array $slug_map      Map of slug combinations to image identifiers.
     * @return   int|null             Matching attachment ID or null when not found.
     */
    protected function resolve_variation_image_id( array $slugs_confirm, array $slug_map ) : ?int {
        foreach ( $slugs_confirm as $slug ) {
            if ( array_key_exists( $slug, $slug_map ) ) {
                return (int) filter_var( $slug_map[$slug], FILTER_SANITIZE_NUMBER_INT );
            }
        }
        $bestMatch = null;
        $bestScore = 0;
        foreach ( $slugs_confirm as $slug ) {
            foreach ( $slug_map as $key => $attachment ) {
                $score = 0;
                similar_text( $slug, $key, $score );
                if ( $score > $bestScore && $score > 70 ) {
                    $bestScore = $score;
                    $bestMatch = (int) filter_var( $attachment, FILTER_SANITIZE_NUMBER_INT );
                }
            }
        }
        return $bestMatch;
    }

    /**
     * Ensure placeholder skeleton styles are available for the initial gallery markup.
     *
     * @since    5.2.20
     */
    protected function ensure_inline_styles() : void {
        if ( self::$inline_styles_added ) {
            return;
        }
        $css = <<<'CSS'
.svi_wrapper {
    position: relative;
}

.svi_wrapper .svi-initial-holder {
    position: relative;
    opacity: 1;
    transition: opacity 0.25s ease, visibility 0.25s ease;
    z-index: 1;
}

.svi_wrapper .svi-app-entry {
    position: relative;
    opacity: 0;
    transition: opacity 0.25s ease;
    z-index: 2;
}

.svi_wrapper.svi-app-mounted .svi-app-entry {
    opacity: 1;
}

.svi_wrapper.svi-app-ready .svi-initial-holder {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.svi_wrapper .svi-initial-gallery {
    position: relative;
    width: 100%;
    margin-bottom: 0;
}

.svi_wrapper .svi-initial-holder,
.svi_wrapper .svi-app-entry {
    width: 100%;
}

.svi_wrapper .svi-initial-main {
    position: relative;
    width: 100%;
    padding-bottom: var(--svi-aspect, 100%);
    background: #f6f7f8;
    border-radius: 6px;
    overflow: hidden;
}

.svi_wrapper .svi-initial-main .svi-skeleton,
.svi_wrapper .svi-initial-thumb .svi-skeleton {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: linear-gradient(90deg, rgba(238, 238, 238, 0.7) 0%, rgba(250, 250, 250, 0.95) 50%, rgba(238, 238, 238, 0.7) 100%);
    background-size: 200% 100%;
    animation: sviSkeletonPulse 1.6s linear infinite;
    border-radius: inherit;
}

.svi_wrapper .svi-initial-thumbs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.svi_wrapper .svi-initial-thumb {
    width: 64px;
    height: 64px;
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}

.svi_wrapper .svi-initial-holder > img {
    display: block;
    width: 100%;
    height: auto;
}

@keyframes sviSkeletonPulse {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
CSS;
        wp_add_inline_style( $this->plugin_name, $css );
        self::$inline_styles_added = true;
    }

    /**
     * Render the frontend for quick view.
     *
     * @since    1.0.0
     */
    public function render_quick_view_frontend() : void {
        global $product;
        $product = wc_get_product( intval( $_POST['id'] ) );
        ob_start();
        include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/smart-variations-images-public-display.php';
        $return = ob_get_clean();
        header( "Content-type: text/html" );
        echo $return;
        wp_die();
    }

    /**
     * Retrieves cached product data or loads it when missing.
     *
     * @since    5.2.20
     * @param    int  $pid            Product ID to retrieve data for.
     * @param    bool $translateSlugs Whether slugs require translation support.
     * @return   array               Prepared product dataset.
     */
    protected function get_cached_product_data( int $pid, bool $translateSlugs = false ) : array {
        $cacheKey = $pid . '|' . (( $translateSlugs ? '1' : '0' ));
        if ( !array_key_exists( $cacheKey, self::$product_cache ) ) {
            $data = $this->loadProduct( $pid, $translateSlugs );
            self::$product_cache[$cacheKey] = ( is_array( $data ) ? $data : [] );
        }
        return self::$product_cache[$cacheKey];
    }

    /**
     * Render showcase variations under attributes/swatches.
     *
     * @since    1.0.0
     */
    public function render_before_add_to_cart_button() : void {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/smart-variations-images-public-display-drop.php';
    }

    /**
     * Validate that slug data is not corrupt or missing.
     *
     * @since    1.1.1
     * @param    array      $woosvi_slug The SVI slug data.
     * @param    WC_Product $product     The WooCommerce product object.
     * @param    int        $pid         The product ID.
     * @param    array      $theslugs    The available slugs.
     * @return   array      The validated SVI slug data.
     */
    public function validateSlugs(
        array $woosvi_slug,
        WC_Product $product,
        int $pid,
        array $theslugs
    ) : array {
        if ( $product->is_type( 'variable' ) ) {
            foreach ( $woosvi_slug as $k => $v ) {
                if ( array_key_exists( 'slugs', $v ) ) {
                    foreach ( $v['slugs'] as $k2 => $slug ) {
                        if ( !array_key_exists( $slug, $theslugs ) ) {
                            $bigger = 95;
                            foreach ( $theslugs as $extra => $check ) {
                                $perc = 0;
                                similar_text( $extra, $slug, $perc );
                                if ( $perc > $bigger ) {
                                    $bigger = $perc;
                                    $woosvi_slug[$k]['slugs'][$k2] = trim( $extra );
                                    update_post_meta( $pid, 'woosvi_slug', $woosvi_slug );
                                }
                            }
                        }
                    }
                }
            }
        }
        return $woosvi_slug;
    }

    /**
     * Return the product information to be displayed via AJAX.
     *
     * @since    1.1.1
     */
    public function loadProductAjax() : void {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            wp_send_json_error( 'Invalid request method.' );
        }
        $product_id = ( isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 );
        if ( $product_id <= 0 ) {
            wp_send_json_error( 'Invalid product ID.' );
        }
        $product_data = $this->loadProduct( $product_id );
        if ( $product_data ) {
            wp_send_json_success( $product_data );
        } else {
            wp_send_json_error( 'Failed to load product data.' );
        }
    }

    /**
     * Return the product information to be displayed.
     *
     * @since    1.1.1
     * @param    int|bool $pid           The product ID, or false to get from input.
     * @param    bool     $translateSlugs Whether to translate slugs for WPML compatibility.
     * @return   array    The product data, or void if outputting JSON directly.
     */
    public function loadProduct( $pid = false, bool $translateSlugs = false ) {
        $return = [];
        if ( $pid ) {
            $this->pid = $pid;
        } else {
            $data = json_decode( file_get_contents( "php://input" ), true );
            $this->pid = intval( $data['id'] );
        }
        $original_pid = $this->pid;
        if ( class_exists( 'SitePress' ) && !$this->runSitePress ) {
            $this->pid = $this->wpml_original( $this->pid );
        }
        $product = wc_get_product( $this->pid );
        $default_img = $product->get_image_id();
        $attachment_ids = [$default_img];
        $woosvi_slug = get_post_meta( $this->pid, 'woosvi_slug', true );
        if ( empty( $woosvi_slug ) ) {
            $this->fallback();
            $woosvi_slug = get_post_meta( $this->pid, 'woosvi_slug', true );
        }
        if ( !is_array( $woosvi_slug ) ) {
            $woosvi_slug = [];
        } else {
            $attributes = get_post_meta( $this->pid, '_product_attributes' );
            $theslugs = $this->getAttributes( $attributes, $this->pid );
            $return['slugs'] = $theslugs;
            $woosvi_slug = $this->validateSlugs(
                $woosvi_slug,
                $product,
                $this->pid,
                $theslugs
            );
            if ( class_exists( 'SitePress' ) && !$this->runSitePress && $product->is_type( 'variable' ) && $original_pid !== $this->pid ) {
                $return['slugs'] = $this->wpml( $original_pid, $product, $this->pid );
                if ( $translateSlugs ) {
                    $woosvi_slug = $this->translateSlugs( $woosvi_slug, $return['slugs'] );
                }
            }
        }
        $attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );
        if ( !$product->is_type( 'variable' ) || empty( $woosvi_slug ) ) {
            $attachment_ids = array_unique( $attachment_ids );
            $attachment_ids = array_values( array_filter( $attachment_ids ) );
        } else {
            $attachment_ids = array_merge( $attachment_ids, $this->get_svigallery_image_ids( $woosvi_slug ) );
        }
        $attachment_ids = array_filter( $attachment_ids );
        foreach ( $woosvi_slug as $k => $v ) {
            if ( array_key_exists( 'slugs', $v ) ) {
                if ( $v['slugs'][0] === 'svidefault' && $default_img ) {
                    array_unshift( $woosvi_slug[$k]['imgs'], $default_img );
                }
                $woosvi_slug[$k]['slugs'] = array_map( 'strtolower', $v['slugs'] );
            }
        }
        if ( svi_fs()->can_use_premium_code__premium_only() && property_exists( $this->options, 'sviproglobal' ) && $this->options->sviproglobal === 'end' ) {
            $sviproglobal = false;
            foreach ( $woosvi_slug as $k => $v ) {
                if ( array_key_exists( 'slugs', $v ) && $v['slugs'][0] === 'sviproglobal' ) {
                    $sviproglobal = $v;
                    unset($woosvi_slug[$k]);
                }
            }
            if ( $sviproglobal ) {
                $woosvi_slug = array_values( $woosvi_slug );
                $woosvi_slug[] = $sviproglobal;
            }
        }
        if ( $attachment_ids ) {
            foreach ( $attachment_ids as $attachment_id ) {
                $video = '';
                if ( is_array( $attachment_id ) ) {
                    $attachment_id = $attachment_id['id'];
                }
                // Normalize attachment id and optional suffix
                $attachment_id = (string) $attachment_id;
                $attachment_id_parts = explode( 'k', $attachment_id );
                $thek = ( count( $attachment_id_parts ) > 1 ? $attachment_id_parts[1] : false );
                $attachment_id = ( isset( $attachment_id_parts[0] ) ? intval( $attachment_id_parts[0] ) : 0 );
                // Skip invalid/empty IDs to avoid type errors downstream
                if ( $attachment_id <= 0 ) {
                    continue;
                }
                if ( $product->is_type( 'variable' ) ) {
                    $gotvideo = ( svi_fs()->can_use_premium_code__premium_only() ? ( $default_img == $attachment_id && $thek === '' ? $this->getMainVideo__premium_only( $woosvi_slug, $default_img ) : $video ) : '' );
                    $img_data = array_merge( [
                        'id'          => intval( $attachment_id ),
                        'idk'         => $thek,
                        'video'       => $gotvideo,
                        'product_img' => $default_img == $attachment_id,
                    ], $this->getMainImage( $attachment_id ) );
                } else {
                    $gotvideo = ( svi_fs()->can_use_premium_code__premium_only() ? $this->getMainVideo__premium_only( $woosvi_slug, $attachment_id, ( $default_img == $attachment_id ? 'wc_svimainvideo' : $attachment_id ) ) : '' );
                    $img_data = array_merge( [
                        'id'          => intval( $attachment_id ),
                        'idk'         => $thek,
                        'video'       => $gotvideo,
                        'product_img' => $default_img == $attachment_id,
                    ], $this->getMainImage( $attachment_id ) );
                }
                $return['images'][] = apply_filters( 'svi_image', $img_data );
            }
        }
        $return['svi'] = $woosvi_slug;
        if ( $pid ) {
            return $return;
        }
        header( "Content-type: application/json" );
        echo json_encode( $return );
        wp_die();
    }

    /**
     * Returns the image IDs in the order they exist in the product.
     *
     * @since    1.0.0
     * @param    array $woosvi_slug The SVI slug data.
     * @return   array The array of image IDs.
     */
    public function get_svigallery_image_ids( array $woosvi_slug ) : array {
        $imgs = [];
        if ( svi_fs()->can_use_premium_code__premium_only() && property_exists( $this->options, 'sviproglobal' ) && $this->options->sviproglobal === 'end' ) {
            $sviproglobal = false;
            foreach ( $woosvi_slug as $k => $v ) {
                if ( array_key_exists( 'slugs', $v ) && $v['slugs'][0] === 'sviproglobal' ) {
                    $sviproglobal = $v;
                    unset($woosvi_slug[$k]);
                }
            }
            if ( $sviproglobal ) {
                $woosvi_slug = array_values( $woosvi_slug );
                $woosvi_slug[] = $sviproglobal;
            }
        }
        foreach ( $woosvi_slug as $k => $v ) {
            foreach ( $v['imgs'] as $imgID ) {
                if ( is_array( $imgID ) ) {
                    continue;
                }
                if ( array_key_exists( 'video', $v ) && array_key_exists( $imgID, $v['video'] ) ) {
                    $imgs[] = [
                        'id'    => $imgID . 'k' . $k,
                        'video' => $v['video'][$imgID],
                    ];
                } else {
                    if ( array_key_exists( 'video', $v ) && !array_key_exists( 'wc_svimainvideo', $v['video'] ) ) {
                        $imgs[] = $imgID . 'k' . $k;
                    } else {
                        $imgs[] = $imgID . 'k' . $k;
                    }
                }
            }
        }
        return $imgs;
    }

    /**
     * Runs the fallback and saves the data if SVI slugs are missing.
     *
     * @since    1.0.0
     */
    public function fallback() : void {
        $product_image_gallery = [];
        if ( metadata_exists( 'post', $this->pid, '_product_image_gallery' ) ) {
            $product_image_gallery = explode( ',', get_post_meta( $this->pid, '_product_image_gallery', true ) );
        } else {
            $attachment_ids = get_posts( [
                'post_parent'    => $this->pid,
                'numberposts'    => -1,
                'post_type'      => 'attachment',
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'post_mime_type' => 'image',
                'fields'         => 'ids',
                'meta_key'       => '_woocommerce_exclude_image',
                'meta_value'     => '0',
            ] );
            $attachment_ids = array_diff( $attachment_ids, [get_post_thumbnail_id()] );
            if ( is_array( $attachment_ids ) && count( $attachment_ids ) > 0 ) {
                $product_image_gallery = $attachment_ids;
            }
        }
        if ( !is_array( $product_image_gallery ) || count( $product_image_gallery ) < 1 ) {
            return;
        }
        $product_image_gallery = array_filter( $product_image_gallery );
        $order = [];
        foreach ( $product_image_gallery as $value ) {
            $woosvi_slug = get_post_meta( $value, 'woosvi_slug_' . $this->pid, true );
            if ( is_array( $woosvi_slug ) ) {
                $data = [];
                foreach ( $woosvi_slug as $v ) {
                    if ( count( $v ) > 1 ) {
                        $data[] = strtolower( implode( '_svipro_', $v ) );
                    } else {
                        $data[] = strtolower( $v );
                    }
                }
                $woosvi_slug = $data;
            }
            if ( !$woosvi_slug ) {
                $woosvi_slug = get_post_meta( $value, 'woosvi_slug', true );
            }
            if ( !$woosvi_slug ) {
                $woosvi_slug = 'nullsvi';
            }
            if ( is_array( $woosvi_slug ) ) {
                foreach ( $woosvi_slug as $v ) {
                    if ( is_array( $v ) ) {
                        $order[$v[0]][] = $value;
                    } else {
                        $order[$v][] = $value;
                    }
                }
            } else {
                $order[$woosvi_slug][] = $value;
            }
        }
        unset($order['nullsvi']);
        $ordered = [];
        foreach ( $order as $k => $v ) {
            $arr = [
                'slugs' => explode( '_svipro_', $k ),
                'imgs'  => $v,
            ];
            $ordered[] = $arr;
        }
        update_post_meta( $this->pid, 'woosvi_slug', $ordered );
    }

    /**
     * Returns the specific image data.
     *
     * @since    1.0.0
     * @param    int $attachment_id The attachment ID of the image.
     * @return   array The image data.
     */
    public function getMainImage( int $attachment_id ) : array {
        $full_size = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
        $thumb_size = apply_filters( 'woocommerce_gallery_thumbnail_size', apply_filters( 'woocommerce_thumbnail_size', 'shop_thumbnail' ) );
        $image_size = apply_filters( 'woocommerce_gallery_image_size', ( $this->options->main_imagesize ?: $full_size ) );
        $thumb_image = apply_filters( 'woocommerce_gallery_thumbnail_size', ( $this->options->thumb_imagesize ?: $thumb_size ) );
        // If ID is invalid or attachment is missing, gracefully fall back to a placeholder
        if ( $attachment_id <= 0 || !get_post( $attachment_id ) ) {
            $thumb_info = $this->imgtagger( wc_placeholder_img( $thumb_image ) );
            $img = $this->imgtagger( wc_placeholder_img( $image_size ) );
            $full_src = ( function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src() : '' );
            $full_img_sizes = [
                'full_image'        => $full_src,
                'full_image_width'  => 0,
                'full_image_height' => 0,
                'thumb_class'       => ( !empty( $thumb_info['class'] ) ? $thumb_info['class'] : 'size-' . $thumb_image ),
            ];
            return array_merge( $img, $full_img_sizes );
        }
        $thumbnail_src = ( wp_get_attachment_image_src( $attachment_id, $this->options->thumb_imagesize ) ?: [null, 0, 0] );
        $large_image = ( wp_get_attachment_image_src( $attachment_id, $image_size ) ?: [null, 0, 0] );
        $image_src = ( wp_get_attachment_image_src( $attachment_id, $full_size ) ?: [null, 0, 0] );
        $image_full = ( wp_get_attachment_image_src( $attachment_id, $full_size ) ?: [null, 0, 0] );
        $thumb_info = $this->imgtagger( wp_get_attachment_image( $attachment_id, $thumb_image, false ) );
        $img = $this->imgtagger( wp_get_attachment_image(
            $attachment_id,
            $image_size,
            false,
            [
                'title'                   => get_the_title( $attachment_id ),
                'data-caption'            => wp_get_attachment_caption( $attachment_id ),
                'data-src'                => $image_src[0] ?? '',
                'data-large_image'        => $large_image[0] ?? $image_src[0],
                'data-large_image_width'  => $large_image[1] ?? 0,
                'data-large_image_height' => $large_image[2] ?? 0,
                'data-thumb_image'        => $thumbnail_src[0] ?? $image_src[0],
                'data-thumb_image_width'  => $thumbnail_src[1] ?? 0,
                'data-thumb_image_height' => $thumbnail_src[2] ?? 0,
            ]
        ) );
        $full_img_sizes = [
            'full_image'        => $image_full[0] ?? $image_src[0],
            'full_image_width'  => $image_full[1] ?? 0,
            'full_image_height' => $image_full[2] ?? 0,
            'thumb_class'       => ( !empty( $thumb_info['class'] ) ? $thumb_info['class'] : 'size-' . $thumb_image ),
        ];
        return array_merge( $img, $full_img_sizes );
    }

    /**
     * Returns the specific image to be displayed.
     *
     * @since    1.0.0
     * @param    int $attachment_id The attachment ID of the image.
     * @return   string The HTML for the image.
     */
    public function returnImage( int $attachment_id ) : string {
        $full_size = apply_filters( 'woocommerce_gallery_full_size', ( property_exists( $this->options, 'showcase_imagesize' ) && $this->options->showcase_imagesize ? $this->options->showcase_imagesize : (( $this->options->main_imagesize ?: $this->options->thumb_imagesize )) ) );
        $image_size = apply_filters( 'woocommerce_gallery_image_size', ( $this->options->main_imagesize ?: $full_size ) );
        $thumbnail_src = ( wp_get_attachment_image_src( $attachment_id, $this->options->thumb_imagesize ) ?: [null, 0, 0] );
        $large_image = ( wp_get_attachment_image_src( $attachment_id, $image_size ) ?: [null, 0, 0] );
        $image_src = ( wp_get_attachment_image_src( $attachment_id, $full_size ) ?: [null, 0, 0] );
        return wp_get_attachment_image(
            $attachment_id,
            $full_size,
            false,
            [
                'title'                   => get_the_title( $attachment_id ),
                'data-caption'            => wp_get_attachment_caption( $attachment_id ),
                'data-src'                => $image_src[0],
                'data-large_image'        => $large_image[0],
                'data-large_image_width'  => $large_image[1],
                'data-large_image_height' => $large_image[2],
                'data-thumb_image'        => $thumbnail_src[0],
                'data-thumb_image_width'  => $thumbnail_src[1],
                'data-thumb_image_height' => $thumbnail_src[2],
                'class'                   => 'svitn_img attachment-svi-icon size-svi-icon',
            ]
        );
    }

    /**
     * Break image tags into an array of attributes.
     *
     * @since    1.0.0
     * @param    string $fullimg_tag The full HTML image tag.
     * @return   array The array of image attributes.
     */
    public function imgtagger( string $fullimg_tag ) : array {
        preg_match_all( '/(alt|title|src|caption|woosvislug|svizoom-image|srcset|title|sizes|width|height|class|thumb_image|thumb_image_width|thumb_image_height|large_image|large_image_width|large_image_height)=("[^"]*")/i', $fullimg_tag, $fullimg_split );
        foreach ( $fullimg_split[2] as $key => $value ) {
            if ( $value === '""' ) {
                $fullimg_split[2][$key] = "";
            } else {
                $fullimg_split[2][$key] = str_replace( '"', "", $value );
            }
        }
        return array_combine( $fullimg_split[1], $fullimg_split[2] );
    }

    /**
     * Builds the product loop to display SVI galleries.
     *
     * @since    1.0.0
     */
    public function svi_product_tn_images() : void {
        global $product;
        if ( !$product instanceof WC_Product ) {
            return;
        }
        $this->loadMainFiles();
        $productData = $this->get_cached_product_data( $product->get_id(), true );
        $data = ( isset( $productData['svi'] ) && is_array( $productData['svi'] ) ? $productData['svi'] : [] );
        if ( svi_fs()->is_free_plan() ) {
            $data = array_slice( $data, 0, 2 );
        }
        $get = [];
        foreach ( $data as $img ) {
            $get[] = $img['imgs'][0];
        }
        $get = array_unique( $get );
        if ( !empty( $get ) ) {
            echo '<div class="svitn_wrapper">';
            foreach ( $get as $img ) {
                echo $this->returnImage( $img );
            }
            echo '</div>';
        }
    }

    /**
     * Return SVI galleries authorized to be displayed in the loop.
     *
     * @since    1.0.0
     * @param    array $data The SVI gallery data.
     * @return   array The authorized galleries.
     */
    public function getAvailableLoopGalleries( array $data ) : array {
        $gals = [];
        foreach ( $data as $v ) {
            if ( array_key_exists( 'loop_hidden', $v ) && !$v['loop_hidden'] ) {
                $gals[] = $v;
            }
        }
        return $gals;
    }

    /**
     * Find the position of the nth occurrence of a substring in a string.
     *
     * @since    1.0.0
     * @param    string $haystack The string to search in.
     * @param    string $needle   The substring to search for.
     * @param    int    $number   The occurrence number to find.
     * @return   int    The position of the nth occurrence.
     */
    public function strposX( string $haystack, string $needle, int $number ) : int {
        preg_match_all(
            "/{$needle}/",
            utf8_decode( $haystack ),
            $matches,
            PREG_OFFSET_CAPTURE
        );
        return $matches[0][$number - 1][1];
    }

    /**
     * Returns the most probable image based on similarity.
     *
     * @since    1.0.0
     * @param    string $img           The current image HTML.
     * @param    array  $slugs_confirm The confirmed slugs to match against.
     * @param    array  $data          The SVI gallery slugs and image IDs.
     * @param    WC_Product $product   The WooCommerce product object.
     * @param    bool   $html          Whether to return HTML or not.
     * @return   string The updated image HTML.
     */
    /**
     * Extracts height and width attributes from an HTML image string.
     *
     * @since    5.2.20
     * @param    string $html HTML snippet containing an <img> tag.
     * @return   array{0:string,1:string} Height and width attribute values.
     */
    protected function extract_image_dimensions_from_html( string $html ) : array {
        $height = '';
        $width = '';
        $dom = new \DOMDocument();
        $previousState = libxml_use_internal_errors( true );
        if ( $dom->loadHTML( $html ) ) {
            $imgNode = $dom->getElementsByTagName( 'img' )->item( 0 );
            if ( $imgNode instanceof \DOMElement ) {
                $height = (string) $imgNode->getAttribute( 'height' );
                $width = (string) $imgNode->getAttribute( 'width' );
            }
        }
        libxml_clear_errors();
        libxml_use_internal_errors( $previousState );
        return [$height, $width];
    }

    public function findSimilar(
        string $img,
        array $slugs_confirm,
        array $data,
        WC_Product $product,
        bool $html = false
    ) : string {
        $found = false;
        if ( !is_array( $slugs_confirm ) && !is_object( $slugs_confirm ) ) {
            return $img;
        }
        $normalized_slugs = $this->normalize_attribute_values( (array) $slugs_confirm );
        $slug_map = $data;
        if ( isset( $data[0] ) && is_array( $data[0] ) && array_key_exists( 'slugs', $data[0] ) ) {
            $slug_map = $this->build_slug_image_map( $data );
        }
        if ( !is_array( $slug_map ) ) {
            $slug_map = [];
        }
        $image_id = $this->resolve_variation_image_id( $normalized_slugs, $slug_map );
        if ( !$image_id ) {
            return $img;
        }
        if ( $html ) {
            list( $imgHeight, $imgWidth ) = $this->extract_image_dimensions_from_html( $img );
            $img = '<div style="margin-bottom: 5px"><img src="' . (( $image_id ? current( wp_get_attachment_image_src( $image_id, 'thumbnail' ) ) : wc_placeholder_img_src() )) . '" alt="' . esc_attr__( 'Product image', 'woocommerce' ) . '" height="' . esc_attr( $imgHeight ) . '" width="' . esc_attr( $imgWidth ) . '" style="vertical-align:middle; margin-' . (( is_rtl() ? 'left' : 'right' )) . ': 10px;" /></div>';
        } else {
            $image_title = $product->get_title();
            $img = wp_get_attachment_image(
                $image_id,
                apply_filters( 'single_product_small_thumbnail_size', $this->options->thumb_imagesize ),
                0,
                [
                    'title' => $image_title,
                    'alt'   => $image_title,
                ]
            );
        }
        return $img;
    }

    /**
     * Check if the product is authorized to load SVI.
     *
     * @since    1.0.0
     * @param    WC_Product $product The WooCommerce product object.
     * @return   bool True if SVI should load, false otherwise.
     */
    public function validate_run( WC_Product $product ) : bool {
        $woosvi_slug = get_post_meta( $product->get_id(), 'woosvi_slug', true );
        $run = get_post_meta( $product->get_id(), '_checkbox_svipro_enabled', true );
        if ( $run === 'yes' ) {
            return false;
        }
        // Normalize odd stored values for woosvi_slug so checks behave as expected
        if ( is_string( $woosvi_slug ) ) {
            $trimmed = trim( $woosvi_slug );
            if ( $trimmed === '""' || $trimmed === "''" || $trimmed === '[]' ) {
                $woosvi_slug = [];
            } else {
                $decoded = json_decode( $trimmed, true );
                if ( json_last_error() === JSON_ERROR_NONE && (is_array( $decoded ) && count( $decoded ) === 0) ) {
                    $woosvi_slug = [];
                }
            }
        }
        // Respect global setting: disable SVI when no SVI data is configured
        if ( property_exists( $this->options, 'svi_disabled_woosvislug' ) ) {
            $disable_on_empty = filter_var( $this->options->svi_disabled_woosvislug, FILTER_VALIDATE_BOOLEAN );
            // Normalize odd empty meta values like '""' to empty
            if ( $woosvi_slug === '""' ) {
                $woosvi_slug = '';
            }
            if ( $disable_on_empty && empty( $woosvi_slug ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the page is running Divi or a Divi template.
     *
     * @since    1.0.0
     * @param    WC_Product $product The WooCommerce product object.
     * @return   bool True if running Divi, false otherwise.
     */
    public function validate_runningDivi( WC_Product $product ) : bool {
        $post_content = get_post( $product->get_id() );
        $content = $post_content->post_content;
        $pos = strpos( $content, 'et_pb_wc_images' );
        $pos2 = strpos( $content, 'et_pb_wc_gallery' );
        if ( $pos !== false || $pos2 !== false ) {
            return true;
        }
        if ( class_exists( 'ET_Theme_Builder_Request' ) ) {
            $tb_layouts = et_theme_builder_get_template_layouts( ET_Theme_Builder_Request::from_post( $product->get_id() ) );
            if ( !empty( $tb_layouts ) && $tb_layouts[ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE]['override'] ) {
                $templateContent = get_the_content( null, false, $tb_layouts[ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE]['id'] );
                $pos = strpos( $templateContent, 'et_pb_wc_images' );
                $pos2 = strpos( $templateContent, 'et_pb_wc_gallery' );
                if ( $pos !== false || $pos2 !== false ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Replace Divi's WooCommerce Images module output with SVI gallery.
     *
     * @since    5.2.22
     * @param    mixed  $output      The HTML output of the module (string on frontend, array in admin).
     * @param    string $render_slug The slug/shortcode of module.
     * @param    object $element     The module object.
     * @return   mixed The modified or original output.
     */
    public function replace_divi_module_output( $output, string $render_slug, $element ) {
        global $product;
        // Only replace et_pb_wc_images module, and not in the visual builder
        if ( $render_slug !== 'et_pb_wc_images' || isset( $_REQUEST['et_fb'] ) ) {
            return $output;
        }
        // Return early if output is not a string (e.g., in admin/builder context)
        if ( !is_string( $output ) ) {
            return $output;
        }
        if ( !$product instanceof WC_Product ) {
            return $output;
        }
        // Check if SVI should run for this product
        $run = $this->validate_run( $product );
        if ( !$run ) {
            return $output;
        }
        // Render SVI gallery
        ob_start();
        include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/smart-variations-images-public-display.php';
        return ob_get_clean();
    }

    /**
     * Store the current instance reference for template rendering.
     *
     * @since    5.2.20
     * @param    self $instance Instance to register.
     */
    public static function set_current_instance( self $instance ) : void {
        if ( self::$current_instance instanceof self ) {
            self::$instance_stack[] = self::$current_instance;
        }
        self::$current_instance = $instance;
    }

    /**
     * Retrieve the current instance reference used for template rendering.
     *
     * @since    5.2.20
     * @return   self|null Current instance or null.
     */
    public static function get_current_instance() : ?self {
        return self::$current_instance;
    }

    /**
     * Push template context information onto the internal stack.
     *
     * @since    5.2.20
     * @param    array<string, mixed> $context Context data to push.
     */
    public static function push_template_context( array $context ) : void {
        self::$template_stack[] = $context;
    }

    /**
     * Pop the most recent template context off the internal stack.
     *
     * @since    5.2.20
     * @return   array<string, mixed>|null Context data or null if stack empty.
     */
    public static function pop_template_context() : ?array {
        if ( empty( self::$template_stack ) ) {
            return null;
        }
        return array_pop( self::$template_stack );
    }

    /**
     * Restore the previous instance after template rendering completes.
     *
     * @since    5.2.20
     */
    public static function clear_current_instance() : void {
        if ( !empty( self::$instance_stack ) ) {
            self::$current_instance = array_pop( self::$instance_stack );
            return;
        }
        self::$current_instance = null;
    }

    /**
     * Sanitize text and return it as JSON.
     *
     * @since    1.0.0
     */
    public function woosvi_slugify() : void {
        header( "Content-type: application/json" );
        echo json_encode( sanitize_title( strtolower( $_POST['data'] ) ) );
        wp_die();
    }

    /**
     * Returns an array with available attributes.
     *
     * @since    1.0.0
     * @param    array $attributes The product attributes.
     * @param    int   $pid        The product ID.
     * @return   array The available attributes.
     */
    public function getAttributes( array $attributes, int $pid ) : array {
        $data = [];
        if ( count( $attributes ) > 0 ) {
            foreach ( $attributes[0] as $att => $attribute ) {
                if ( $attribute['is_taxonomy'] && $attribute['is_variation'] ) {
                    $terms = wp_get_post_terms( $pid, urldecode( $att ), 'all' );
                    if ( !empty( $terms ) ) {
                        foreach ( $terms as $term ) {
                            $data[strtolower( esc_attr( $term->slug ) )] = trim( esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) );
                        }
                    }
                } elseif ( !$attribute['is_taxonomy'] && $attribute['is_variation'] ) {
                    $terms = explode( '|', $attribute['value'] );
                    foreach ( $terms as $term ) {
                        $data[sanitize_title( $term )] = trim( esc_html( apply_filters( 'woocommerce_variation_option_name', $term ) ) );
                    }
                }
            }
        }
        return array_filter( $data );
    }

    /**
     * Get the original language ID for WPML compatibility.
     *
     * @since    1.0.0
     * @param    int $id The product ID.
     * @return   int The original language ID, or the input ID if not found.
     */
    public function wpml_original( int $id ) : int {
        global $wpdb;
        $orig_lang_id = $wpdb->get_var( "SELECT trans2.element_id FROM {$wpdb->prefix}icl_translations AS trans1 INNER JOIN {$wpdb->prefix}icl_translations AS trans2 ON trans2.trid = trans1.trid WHERE trans1.element_id = " . $id . " AND trans2.source_language_code IS NULL" );
        return ( is_null( $orig_lang_id ) ? $id : (int) $orig_lang_id );
    }

    /**
     * Get translated slugs for WPML compatibility.
     *
     * @since    1.0.0
     * @param    int        $pid      The product ID.
     * @param    WC_Product $product  The WooCommerce product object.
     * @param    int        $original The original product ID.
     * @return   array|bool The translated slugs, or false if not applicable.
     */
    public function wpml( int $pid, WC_Product $product, int $original ) {
        global $sitepress;
        if ( !$product->is_type( 'variable' ) ) {
            return false;
        }
        $slugs = [];
        $attributes = get_post_meta( $pid, '_product_attributes' );
        if ( !empty( $attributes ) ) {
            foreach ( $attributes[0] as $att => $attribute ) {
                if ( $attribute['is_taxonomy'] && $attribute['is_variation'] ) {
                    $valid_attr = esc_attr( $att );
                    $terms = wp_get_post_terms( $pid, $valid_attr, 'all' );
                    if ( is_wp_error( $terms ) ) {
                        $valid_attr = esc_attr( $attribute['name'] );
                        $terms = wp_get_post_terms( $pid, $valid_attr, 'all' );
                    }
                    foreach ( $terms as $term ) {
                        remove_filter(
                            'get_term',
                            [$sitepress, 'get_term_adjust_id'],
                            1,
                            1
                        );
                        $gtb = get_term( icl_object_id(
                            $term->term_id,
                            $valid_attr,
                            true,
                            $sitepress->get_default_language()
                        ) );
                        $slugs[strtolower( esc_attr( $gtb->slug ) )] = esc_attr( $term->slug );
                        add_filter(
                            'get_term',
                            [$sitepress, 'get_term_adjust_id'],
                            1,
                            1
                        );
                    }
                }
            }
        }
        $attributes_original = get_post_meta( $original, '_product_attributes' );
        if ( !empty( $attributes_original ) ) {
            foreach ( $attributes_original[0] as $att => $attribute ) {
                if ( !$attribute['is_taxonomy'] && $attribute['is_variation'] ) {
                    if ( array_key_exists( $att, $attributes[0] ) ) {
                        $values = $attributes[0][$att]['value'];
                        if ( !empty( $values ) ) {
                            $terms = explode( '|', $values );
                            $terms_original = explode( '|', $attribute['value'] );
                            foreach ( $terms_original as $tr => $term ) {
                                $slugs[sanitize_title( $term )] = trim( esc_attr( $terms[$tr] ) );
                            }
                        }
                    }
                }
            }
        }
        return $slugs;
    }

    /**
     * Get translated IDs for WPML compatibility.
     *
     * @since    1.0.0
     * @param    int $id The product ID.
     * @return   array|bool The translated IDs, or false if not found.
     */
    public function wpml_ids( int $id ) {
        global $wpdb;
        $trid = $wpdb->get_var( "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id = " . $id . " AND source_language_code IS NULL" );
        if ( $trid > 0 ) {
            $translations = $wpdb->get_results( "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid = " . $trid . " AND source_language_code IS NOT NULL" );
        } else {
            return false;
        }
        if ( $translations ) {
            $ids = [];
            foreach ( $translations as $v ) {
                $ids[] = $v->element_id;
            }
            return $ids;
        }
        return false;
    }

    /**
     * Translate slugs for WPML compatibility.
     *
     * @since    1.0.0
     * @param    array $woosvi_slug The SVI slug data.
     * @param    array $wpml_slugs  The WPML translated slugs.
     * @return   array The translated SVI slug data.
     */
    public function translateSlugs( array $woosvi_slug, array $wpml_slugs ) : array {
        foreach ( $woosvi_slug as $k => $v ) {
            if ( array_key_exists( 'slugs', $v ) ) {
                foreach ( $v['slugs'] as $k2 => $slug ) {
                    if ( array_key_exists( $slug, $wpml_slugs ) ) {
                        $woosvi_slug[$k]['slugs'][$k2] = trim( $wpml_slugs[$slug] );
                    }
                }
            }
        }
        return $woosvi_slug;
    }

    /**
     * Run integrations for third-party plugins.
     *
     * @since    1.0.0
     */
    public function run_integrations() : void {
        $this->svi_Yith_Badge();
    }

    /**
     * Integrate with YITH Badge Management plugin.
     *
     * @since    1.0.0
     */
    public function svi_Yith_Badge() : void {
        if ( !function_exists( 'YITH_WCBM_Frontend' ) ) {
            return;
        }
        global $product;
        $yith_badge = YITH_WCBM_Frontend();
        echo $yith_badge->show_badge_on_product( ' ' );
    }

    /**
     * Detect the filtered attribute and values from the URL query parameters.
     *
     * @since    1.0.0
     * @return   array|null Array with 'attribute' and 'values' (array of values) if a filter is applied, null otherwise.
     */
    public function get_filtered_attribute() : ?array {
        $filtered_attribute = null;
        foreach ( $_GET as $key => $value ) {
            if ( strpos( $key, 'filter_' ) === 0 && !empty( $value ) ) {
                $attribute = str_replace( 'filter_', '', $key );
                $values = array_map( 'sanitize_title', explode( ',', $value ) );
                $filtered_attribute = [
                    'attribute' => $attribute,
                    'values'    => $values,
                ];
                break;
            }
        }
        return $filtered_attribute;
    }

    /**
     * Filter the main product image based on applied attribute filters.
     *
     * @since    1.0.0
     * @param    string     $image       The current product image HTML.
     * @param    WC_Product $product     The WooCommerce product object.
     * @param    string     $size        The image size.
     * @param    array      $attr        The image attributes.
     * @param    bool       $placeholder Whether to use a placeholder image.
     * @return   string     The updated product image HTML.
     */
    public function filter_main_product_image(
        string $image,
        WC_Product $product,
        string $size,
        array $attr,
        bool $placeholder
    ) : string {
        if ( !is_shop() && !is_product_taxonomy() ) {
            return $image;
        }
        $filtered_attribute = $this->get_filtered_attribute();
        if ( !$filtered_attribute ) {
            error_log( 'SVI: No filter applied for product ' . $product->get_id() );
            return $image;
        }
        if ( !isset( $filtered_attribute['values'] ) || empty( $filtered_attribute['values'] ) ) {
            error_log( 'SVI: Filter applied but no values for product ' . $product->get_id() . ': ' . print_r( $filtered_attribute, true ) );
            return $image;
        }
        error_log( 'SVI: Filter applied for product ' . $product->get_id() . ': ' . print_r( $filtered_attribute, true ) );
        $data = $this->get_cached_product_data( $product->get_id(), true );
        if ( !isset( $data['svi'] ) || empty( $data['svi'] ) ) {
            error_log( 'SVI: No SVI gallery data for product ' . $product->get_id() );
            return $image;
        }
        error_log( 'SVI: SVI gallery data for product ' . $product->get_id() . ': ' . print_r( $data['svi'], true ) );
        $filtered_values = $filtered_attribute['values'];
        $matching_images = [];
        foreach ( $filtered_values as $filtered_value ) {
            $found = false;
            foreach ( $data['svi'] as $variation ) {
                if ( !isset( $variation['slugs'] ) || empty( $variation['slugs'] ) || !isset( $variation['imgs'] ) || empty( $variation['imgs'] ) ) {
                    error_log( 'SVI: Skipping variation with missing slugs or images for product ' . $product->get_id() . ': ' . print_r( $variation, true ) );
                    continue;
                }
                $slugs = array_map( 'sanitize_title', $variation['slugs'] );
                if ( in_array( $filtered_value, $slugs ) ) {
                    $image_id = $variation['imgs'][0];
                    $matching_images[$filtered_value] = [
                        'image_id' => $image_id,
                        'slug'     => $filtered_value,
                    ];
                    error_log( 'SVI: Found matching variation for ' . $filtered_value . ' in product ' . $product->get_id() . ': Image ID ' . $image_id );
                    $found = true;
                    break;
                }
            }
            if ( !$found ) {
                error_log( 'SVI: Variation ' . $filtered_value . ' not found for product ' . $product->get_id() . ', skipping.' );
            }
        }
        error_log( 'SVI: Matching images for product ' . $product->get_id() . ': ' . print_r( $matching_images, true ) );
        if ( empty( $matching_images ) ) {
            error_log( 'SVI: No matching variation images found for product ' . $product->get_id() );
            return $image;
        }
        if ( count( $matching_images ) === 1 ) {
            $first_match = reset( $matching_images );
            $image_id = $first_match['image_id'];
            $slug = $first_match['slug'];
            error_log( 'SVI: Only one matching image found for product ' . $product->get_id() . ': Image ID ' . $image_id );
            $image = wp_get_attachment_image(
                $image_id,
                $size,
                false,
                array_merge( $attr, [
                    'title'        => get_the_title( $image_id ),
                    'data-caption' => wp_get_attachment_caption( $image_id ),
                    'alt'          => esc_attr( $product->get_name() . ' - ' . $slug ),
                ] )
            );
            return $image;
        }
        $num_images = count( $matching_images );
        $split_images = array_slice(
            $matching_images,
            0,
            $num_images,
            true
        );
        $image_html = '';
        // Determine the layout type based on the filter_attribute_cut option
        $layout_type = ( property_exists( $this->options, 'filter_attribute_cut' ) ? $this->options->filter_attribute_cut : 'diagonal' );
        if ( $layout_type === 'vertical' ) {
            // Vertical layout: Stack images vertically
            $image_html = '';
            $aria_labels = [];
            // Initialize as an array
            foreach ( $split_images as $slug => $match ) {
                $image_id = $match['image_id'];
                $aria_labels[] = $slug;
                // Add slug to aria labels
                $image_url = wp_get_attachment_image_url( $image_id, $size );
                if ( !$image_url ) {
                    error_log( 'SVI: Failed to retrieve image URL for image ID ' . $image_id . ' in product ' . $product->get_id() );
                    continue;
                }
                error_log( 'SVI: Rendering image for ' . $slug . ' with URL ' . $image_url );
                $image_html .= '<li data-image="' . esc_url( $image_url ) . '" style="width: 100%; height: ' . 100 / $num_images . '%;">';
                $image_html .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $product->get_name() . ' - ' . $slug ) . '" />';
                $image_html .= '</li>';
            }
        } elseif ( $layout_type === 'horizontal' ) {
            // Horizontal layout: Arrange images side by side (existing behavior)
            $initial_width = 100 / $num_images;
            $reduced_width = 10;
            $expanded_width = 100 - ($num_images - 1) * $reduced_width;
            $aria_labels = [];
            $index = 0;
            foreach ( $split_images as $slug => $match ) {
                $image_id = $match['image_id'];
                $aria_labels[] = $slug;
                $image_url = wp_get_attachment_image_url( $image_id, $size );
                if ( !$image_url ) {
                    error_log( 'SVI: Failed to retrieve image URL for image ID ' . $image_id . ' in product ' . $product->get_id() );
                    continue;
                }
                error_log( 'SVI: Rendering image for ' . $slug . ' with URL ' . $image_url );
                $left_position = $index * $initial_width;
                $style = "width: {$initial_width}%; left: {$left_position}%;";
                $image_html .= '<li data-image="' . esc_url( $image_url ) . '" style="' . $style . '">';
                $image_html .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $product->get_name() . ' - ' . $slug ) . '" />';
                $image_html .= '</li>';
                $index++;
            }
        } else {
            // Diagonal layout (default): Apply diagonal cuts
            $aria_labels = [];
            $index = 0;
            foreach ( $split_images as $slug => $match ) {
                $image_id = $match['image_id'];
                $aria_labels[] = $slug;
                $image_url = wp_get_attachment_image_url( $image_id, $size );
                if ( !$image_url ) {
                    error_log( 'SVI: Failed to retrieve image URL for image ID ' . $image_id . ' in product ' . $product->get_id() );
                    continue;
                }
                error_log( 'SVI: Rendering image for ' . $slug . ' with URL ' . $image_url );
                // Add a class to indicate the position of the image (first, middle, last)
                $position_class = '';
                if ( $index === 0 ) {
                    $position_class = 'first';
                } elseif ( $index === $num_images - 1 ) {
                    $position_class = 'last';
                } else {
                    $position_class = 'middle';
                }
                $image_html .= '<li data-image="' . esc_url( $image_url ) . '" class="' . $position_class . '">';
                $image_html .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $product->get_name() . ' - ' . $slug ) . '" />';
                $image_html .= '</li>';
                $index++;
            }
        }
        if ( empty( $image_html ) ) {
            error_log( 'SVI: No valid images found for accordion layout in product ' . $product->get_id() );
            return $image;
        }
        // Add the layout type as a data attribute for CSS/JS to use
        $split_layout = '<ul class="svi-image-compare" data-ratio="1x1" data-num-images="' . $num_images . '" data-cut="' . esc_attr( $layout_type ) . '" aria-label="Accordion view of ' . esc_attr( implode( ' and ', $aria_labels ) ) . ' variations">' . $image_html . '</ul>';
        return $split_layout;
    }

}
