<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Handles dismissing admin notices.
 *
 * @package   WPTRT/admin-notices
 * @author    WPTRT <themes@wordpress.org>
 * @copyright 2023 WP Full Picture
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link      https://github.com/WPTRT/admin-notices
 */

namespace FUPI;

/**
 * The FUPI_Dismiss class, responsible for dismissing and checking the status of admin notices.
 *
 * @since 1.0.0
 */
class FUPI_Dismiss {

	/**
	 * The notice-ID.
	 *
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $id;

	/**
	 * The prefix we'll be using for the option/user-meta.
	 *
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $prefix;

	/**
	 * The notice's scope. Can be "user" or "global".
	 *
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $scope;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $id     A unique ID for this notice. Can contain lowercase characters and underscores.
	 * @param string $prefix The prefix that will be used for the option/user-meta.
	 * @param string $scope  Controls where the dismissal will be saved: user or global.
	 */
	public function __construct( $id, $prefix, $scope = 'global' ) {

		// Set the object properties.
		$this->id     = sanitize_key( $id );
		$this->prefix = sanitize_key( $prefix );
		$this->scope  = ( in_array( $scope, [ 'global', 'user' ], true ) ) ? $scope : 'global';

		// Handle AJAX requests to dismiss the notice.
		add_action( 'wp_ajax_wptrt_dismiss_notice', [ $this, 'ajax_maybe_dismiss_notice' ] );
	}

	/**
	 * Print the script for dismissing the notice.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	public function print_script() {

		// Create a nonce.
		$nonce = wp_create_nonce( 'wptrt_dismiss_notice_' . $this->id );
		?>
		<script>
		window.addEventListener( 'load', function() {
			var dismissBtn  = document.querySelector( '#wptrt-notice-<?php echo esc_attr( $this->id ); ?> .notice-dismiss' );

			// Add an event listener to the dismiss button.
			dismissBtn.addEventListener( 'click', function( event ) {
				var httpRequest = new XMLHttpRequest(),
					postData    = '';

				// Build the data to send in our request.
				// Data has to be formatted as a string here.
				postData += 'id=<?php echo esc_attr( rawurlencode( $this->id ) ); ?>';
				postData += '&action=wptrt_dismiss_notice';
				postData += '&nonce=<?php echo esc_html( $nonce ); ?>';

				httpRequest.open( 'POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>' );
				httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
				httpRequest.send( postData );
			});
		});
		</script>
		<?php
	}

	/**
	 * Check if the notice has been dismissed or not.
	 *
	 * @access public
	 * @since 1.0
	 * @return bool
	 */
	public function is_dismissed() {

		// Check if the notice has been dismissed when using user-meta.
		if ( 'user' === $this->scope ) {
			return ( get_user_meta( get_current_user_id(), "{$this->prefix}_{$this->id}", true ) );
		}

		return ( get_option( "{$this->prefix}_{$this->id}" ) );
	}

	/**
	 * Run check to see if we need to dismiss the notice.
	 * If all tests are successful then call the dismiss_notice() method.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function ajax_maybe_dismiss_notice() {

		// Sanity check: Early exit if we're not on a wptrt_dismiss_notice action.
		if ( ! isset( $_POST['action'] ) || 'wptrt_dismiss_notice' !== $_POST['action'] ) {
			return;
		}

		// Sanity check: Early exit if the ID of the notice is not the one from this object.
		if ( ! isset( $_POST['id'] ) || $this->id !== $_POST['id'] ) {
			return;
		}

		// Security check: Make sure nonce is OK.
		check_ajax_referer( 'wptrt_dismiss_notice_' . $this->id, 'nonce', true );

		// If we got this far, we need to dismiss the notice.
		$this->dismiss_notice();
	}

	/**
	 * Actually dismisses the notice.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function dismiss_notice() {
		if ( 'user' === $this->scope ) {
			update_user_meta( get_current_user_id(), "{$this->prefix}_{$this->id}", true );
			return;
		}
		update_option( "{$this->prefix}_{$this->id}", true, false );
	}
}

// phpcs:ignore WordPress.Files.FileName
/**
 * Admin-FUPI_Notices class.
 *
 * Creates an admin notice with consistent styling.
 *
 * @package   WPTRT/admin-notices
 * @author    WPTRT <themes@wordpress.org>
 * @copyright 2019 WPTRT
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link      https://github.com/WPTRT/admin-notices
 */

/**
 * The Admin_FUPI_Notice class, responsible for creating admin notices.
 *
 * Each notice is a new instance of the object.
 *
 * @since 1.0.0
 */
class FUPI_Notice {

	/**
	 * The notice-ID.
	 *
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $id;

	/**
	 * The notice message.
	 *
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $message;

	/**
	 * The notice title.
	 *
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $title;

	/**
	 * An instance of the \WPTRT\AdminFUPI_Notices\FUPI_Dismiss object.
	 *
	 * @access public
	 * @since 1.0
	 * @var \WPTRT\AdminFUPI_Notices\FUPI_Dismiss
	 */
	public $dismiss;

	/**
	 * The notice arguments.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $options = [
		'scope'         => 'global',
		'type'          => 'info',
		'alt_style'     => false,
		'capability'    => 'edit_theme_options',
		'option_prefix' => 'wptrt_notice_dismissed',
		'screens'       => [],
	];

	/**
	 * Allowed HTML in the message.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $allowed_html = [
		'p'      => [],
		'a'      => [
			'href' => [],
			'rel'  => [],
			'class'	 => [],
		],
		'em'     => [],
		'strong' => [],
		'br'     => [],
	];

	/**
	 * An array of allowed types.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $allowed_types = [
		'info',
		'success',
		'error',
		'warning',
	];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $id      A unique ID for this notice. Can contain lowercase characters and underscores.
	 * @param string $title   The title for our notice.
	 * @param string $message The message for our notice.
	 * @param array  $options An array of additional options to change the defaults for this notice.
	 *                        [
	 *                            'screens'       => (array)  An array of screens where the notice will be displayed.
	 *                                                        Leave empty to always show.
	 *                                                        Defaults to an empty array.
	 *                            'scope'         => (string) Can be "global" or "user".
	 *                                                        Determines if the dismissed status will be saved as an option or user-meta.
	 *                                                        Defaults to "global".
	 *                            'type'          => (string) Can be one of "info", "success", "warning", "error".
	 *                                                        Defaults to "info".
	 *                            'alt_style'     => (bool)   Whether we want to use alt styles or not.
	 *                                                        Defaults to false.
	 *                            'capability'    => (string) The user capability required to see the notice.
	 *                                                        Defaults to "edit_theme_options".
	 *                            'option_prefix' => (string) The prefix that will be used to build the option (or post-meta) name.
	 *                                                        Can contain lowercase latin letters and underscores.
	 *                        ].
	 */
	public function __construct( $id, $title, $message, $options = [] ) {

		// Set the object properties.
		$this->id      = $id;
		$this->title   = $title;
		$this->message = $message;
		$this->options = wp_parse_args( $options, $this->options );

		// Sanity check: Early exit if ID or message are empty.
		if ( ! $this->id || ! $this->message ) {
			return;
		}

		/**
		 * Allow filtering the allowed HTML tags array.
		 *
		 * @since 1.0.2
		 * @param array $allowed_html The list of allowed HTML tags.
		 * @return array
		 */
		$this->allowed_html = apply_filters( 'wptrt_admin_notices_allowed_html', $this->allowed_html );

		// Instantiate the FUPI_Dismiss object.
		$this->dismiss = new FUPI_Dismiss( $this->id, $this->options['option_prefix'], $this->options['scope'] );
	}

	/**
	 * Prints the notice.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function the_notice() {

		// Early exit if we don't want to show this notice.
		if ( ! $this->show() ) {
			return;
		}

		$html  = $this->get_title();
		$html .= $this->get_message();

		// Print the notice.
		printf(
			'<div id="%1$s" class="%2$s">%3$s</div>',
			'wptrt-notice-' . esc_attr( $this->id ), // The ID.
			esc_attr( $this->get_classes() ), // The classes.
			$html // The HTML.
		);
	}

	/**
	 * Determine if the notice should be shown or not.
	 *
	 * @access public
	 * @since 1.0
	 * @return bool
	 */
	public function show() {

		// Don't show if the user doesn't have the required capability.
		if ( ! current_user_can( $this->options['capability'] ) ) {
			return false;
		}

		// Don't show if we're not on the right screen.
		if ( ! $this->is_screen() ) {
			return false;
		}

		// Don't show if notice has been dismissed.
		if ( $this->dismiss->is_dismissed() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the notice classes.
	 *
	 * @access public
	 * @since 1.0
	 * @return string
	 */
	public function get_classes() {
		$classes = [
			'notice',
			'is-dismissible',
		];

		// Make sure the defined type is allowed.
		$this->options['type'] = in_array( $this->options['type'], $this->allowed_types, true ) ? $this->options['type'] : 'info';

		// Add the class for notice-type.
		$classes[] = 'notice-' . $this->options['type'];

		// Do we want alt styles?
		if ( $this->options['alt_style'] ) {
			$classes[] = 'notice-alt';
		}

		// Combine classes to a string.
		return implode( ' ', $classes );
	}

	/**
	 * Returns the title.
	 *
	 * @access public
	 * @since 1.0
	 * @return string
	 */
	public function get_title() {

		// Sanity check: Early exit if no title is defined.
		if ( ! $this->title ) {
			return '';
		}

		return sprintf(
			'<h2 class="notice-title">%s</h2>',
			wp_strip_all_tags( $this->title )
		);
	}

	/**
	 * Returns the message.
	 *
	 * @access public
	 * @since 1.0
	 * @return string
	 */
	public function get_message() {
		return wpautop( wp_kses( $this->message, $this->allowed_html ) );
	}

	/**
	 * Evaluate if we're on the right place depending on the "screens" argument.
	 *
	 * @access private
	 * @since 1.0
	 * @return bool
	 */
	private function is_screen() {

		// If screen is empty we want this shown on all screens.
		if ( ! $this->options['screens'] || empty( $this->options['screens'] ) ) {
			return true;
		}

		// Make sure the get_current_screen function exists.
		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once ABSPATH . 'wp-admin/includes/screen.php';
		}

		/** @var \WP_Screen $current_screen */
		$current_screen = get_current_screen();
		// Check if we're on one of the defined screens.
		return ( in_array( $current_screen->id, $this->options['screens'], true ) );
	}
}

// phpcs:ignore WordPress.Files.FileName
/**
 * Admin-FUPI_Notices class.
 *
 * Handles creating FUPI_Notices and printing them.
 *
 * @package   WPTRT/admin-notices
 * @author    WPTRT <themes@wordpress.org>
 * @copyright 2019 WPTRT
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link      https://github.com/WPTRT/admin-notices
 */

/**
 * The Admin_FUPI_Notice class, responsible for creating admin notices.
 *
 * Each notice is a new instance of the object.
 *
 * @since 1.0.0
 */
class FUPI_Notices {

	/**
	 * An array of notices.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $notices = [];

	/**
	 * Adds actions for the notices.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function boot() {

		// Add the notice.
		add_action( 'admin_notices', [ $this, 'the_notices' ] );

		// Print the script to the footer.
		add_action( 'admin_footer', [ $this, 'print_scripts' ] );
	}

	/**
	 * Add a notice.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $id      A unique ID for this notice. Can contain lowercase characters and underscores.
	 * @param string $title   The title for our notice.
	 * @param string $message The message for our notice.
	 * @param array  $options An array of additional options to change the defaults for this notice.
	 *                        See FUPI_Notice::__constructor() for details.
	 * @return void
	 */
	public function add( $id, $title, $message, $options = [] ) {
		$this->notices[ $id ] = new FUPI_Notice( $id, $title, $message, $options );
	}

	/**
	 * Remove a notice.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $id The unique ID of the notice we want to remove.
	 * @return void
	 */
	public function remove( $id ) {
		unset( $this->notices[ $id ] );
	}

	/**
	 * Get a single notice.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $id The unique ID of the notice we want to retrieve.
	 * @return FUPI_Notice|null
	 */
	public function get( $id ) {
		if ( isset( $this->notices[ $id ] ) ) {
			return $this->notices[ $id ];
		}
		return null;
	}

	/**
	 * Get all notices.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_all() {
		return $this->notices;
	}

	/**
	 * Prints the notice.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function the_notices() {
		$notices = $this->get_all();

		foreach ( $notices as $notice ) {
			$notice->the_notice();
		}
	}

	/**
	 * Prints scripts for the notices.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function print_scripts() {
		$notices = $this->get_all();

		foreach ( $notices as $notice ) {
			if ( $notice->show() ) {
				$notice->dismiss->print_script();
			}
		}
	}
}
