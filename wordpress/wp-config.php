<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

$url = "https://digitalgxp.com/blog/";
define('WP_HOME', "https://digitalgxp.com/blog");
define('WP_SITEURL', "https://digitalgxp.com/blog");

// Rewrite your access URL to the admin page
// $_SERVER['REQUEST_URI'] = '/blog' . $_SERVER['REQUEST_URI'];
$_SERVER['REQUEST_URI'] = str_replace("/wp-admin/", "/blog/wp-admin/",  $_SERVER['REQUEST_URI']);


// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', getenv('MYSQL_DATABASE') );

/** Database username */
define( 'DB_USER', getenv('MYSQL_USER') );

/** Database password */
define( 'DB_PASSWORD', getenv('MYSQL_PASSWORD') );

/** Database hostname */
define( 'DB_HOST', getenv('DB_HOST') );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', getenv('DB_CHARSET') );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'VtpYM+G-CZQ)=Qpv9|y#P<kA*@Y71x;*NN>;.02w!AY[INR+Zd.6yFvGCwFON5$@' );
define( 'SECURE_AUTH_KEY',  'GO,Ag5TNWBqtR8~?A|U W<k{1|C7(Bj5AH5+pE2YP`H&,l76n,h?4./Jr5}ajo7O' );
define( 'LOGGED_IN_KEY',    '.o`dEUU`@zopfmTc[ty5CM&ShOds7nq>+=oE:~_F<KhZ|X0;6p6g?O]Ny[i9r1pC' );
define( 'NONCE_KEY',        'xQA*(X$l^^JDCZe,WqN>u[<=0`f*.adI.YU:QBRgyleW{:pEW&(VM|g&NNm&$=4&' );
define( 'AUTH_SALT',        ' bnj*J*UXjef< CVzBu:AbKf$,hb4xKx%)4%(X&VOPn`!4QP@5Ei4>$n>!<m^h(Q' );
define( 'SECURE_AUTH_SALT', 'X5Tqp^zx]~p[/#67r`;1J=$_f5(&XdzEvYy$9z4]X<i&U#2_xZEvnUGBCye#JR`F' );
define( 'LOGGED_IN_SALT',   '[LfqT<M82$Nv;yMnoSvVyaZAU5]rr(EV*|tUOLxYhj}~{:vu+Ucdz;n-+V$tbE[Y' );
define( 'NONCE_SALT',       'Q,/g8C,>}AD=UgdLgR=<!$6+2H0PUWzj_H9CJ6aP6Fiyb3F<cgLGX2!5Gf0X>jF|' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
