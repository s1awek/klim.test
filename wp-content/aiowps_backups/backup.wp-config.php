<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'klimpolsxvwp');

/** MySQL database username */
define('DB_USER', 'klimpolsxvwp');

/** MySQL database password */
define('DB_PASSWORD', 'djnbQTD8GfgsD97twL5k');

/** MySQL hostname */
define('DB_HOST', 'klimpolsxvwp.mysql.db');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'hP8OE)@lT?|3=z;r+ta(&tM7:U~m|i]z_YXt9uof%4ufe[vJpta3O}cf s^WI|7$');
define('SECURE_AUTH_KEY',  'gV)?WGgRx |OiqVLg50T!FI{9)]98.)VT3fVYm4>k)Gpw^{Q9)!#rbE/2yQ*q[D<');
define('LOGGED_IN_KEY',    'n:y*0|tm]us *oxzauhr<%N+:4[~vg>cam{&e5,=jN5z!uFd>QC7^@X<`nx;qA.m');
define('NONCE_KEY',        '?-|4%yyQyf!?{>](x/b6?KcA^OQ%y)VHU*^|IvWhRVRN#~Qo6;@JEVJ2KFNtopPo');
define('AUTH_SALT',        '_~~yUapYb]]cVt4NW*G)qP?U?}]LlwS}#~$sW%_&_Ach?+PQ#Y$:3d3^9u/*$v#:');
define('SECURE_AUTH_SALT', 'K,yZX0p6Vr!x6IQ@Xh{ul,lXb`dR~@~~SVE/W4#I&9ks,~USVnW80NAWwsT~>S_.');
define('LOGGED_IN_SALT',   'P^Z<s,eRU12bn%4?|ob~ObtLp^Gn`B_5w-ZLo!Zu?Xv9F>CkQA5?I*=nZ;^q;B_t');
define('NONCE_SALT',       '`!!4{;qld_0cvp%G18`Y1n?i7<g2baFS$SwRcfqk5Uh,hhUzpB,G%.^T&hznZ(YF');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpklm_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
 // Enable WP_DEBUG mode
 define( 'WP_DEBUG', true );

 // Enable Debug logging to the /wp-content/debug.log file
 define( 'WP_DEBUG_LOG', true );
 
 // Disable display of errors and warnings 
 define( 'WP_DEBUG_DISPLAY', false );
 @ini_set( 'display_errors', 0 );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
