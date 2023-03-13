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

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wp' );

/** Database password */
define( 'DB_PASSWORD', 'Digital_GxP@2023' );

/** Database hostname */
define( 'DB_HOST', 'mysql' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '&~lqD/sLEQ27/iPrFACie1}X|N=_Pvq4-&26aA7B}X9<<sdrl&BwLU3qRA1T?B$4' );
define( 'SECURE_AUTH_KEY',  'qt sDV9C|[-hZQwyb}.kQHX6r4YrTCy>Qg};CVoRF&vN@//d|9q~Tj#.nSkwF)@V' );
define( 'LOGGED_IN_KEY',    'sr-wU}rva5D]GSFQVubKXn0p-7mCJq1K%s|RD.8fW!jM 3}Na^Mw3OUE^5m=p^fY' );
define( 'NONCE_KEY',        'O0Y4ET_Vy@Ol-5tN<z:aJ/aI4^|$x<aIvd)2HbuIR-/0(04E%fZaZ=hr3:<1V{Q4' );
define( 'AUTH_SALT',        'Lk.RYiQ]|bCf~AYIA-ex_9e$hksCONj3 dZizgZrH*43Q`}X:T[hpbkv9eG%UY+e' );
define( 'SECURE_AUTH_SALT', 'N}YEhzaw2>P71@5femGl[Z.B.T >5Q)c$VpER,P1j&`bs29&Iif1leraW|No0R3A' );
define( 'LOGGED_IN_SALT',   '&XA__H7,M[iqY.AzVy><L16G)1Ou[]E74wB4|ZA)Lgt;]K~_>=XAYDna)*Af#f8Q' );
define( 'NONCE_SALT',       '^0eMQylnoGor;z=P1oD{ Is%7>khr>@d%%8Mr=A*nS&6~kUCeV?,r0$ZT^(Ld>$R' );

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
