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
define( 'AUTH_KEY',         'Ma]aKEQr`KyLS!hPclS^RxZONv0bL9;0kg#AnU{w.#rM49IXxs=IRp$0ITy4()V[' );
define( 'SECURE_AUTH_KEY',  '+O$6ST%sXFC;GO8Lb[rG(%,C>?OG)j=k!B;,/vAT&avfXw#;dQkekgiI$HL2#r._' );
define( 'LOGGED_IN_KEY',    'V*q<XoXfj:F]^#8k,DD.j9&/OM|0p>}UE2k)7P`pJ[c/yk] %~HM*DGd&yb}+A;P' );
define( 'NONCE_KEY',        'ZNc.kRJX7Hf=U])Q&dX;bvFCz+FcNA?{9S.;j$RpCi@F2,lRtzS|EFXYwc(3WlTg' );
define( 'AUTH_SALT',        'Hdpvech?bBAr`UA@]]#pFQpJwtOcoT>oYr f#4#+ :c1d4Wk)[pUBv];>WqvQTly' );
define( 'SECURE_AUTH_SALT', 'q2h(5gA)j5}aFN+qTGK:8KxJd#O+J=j{ Ix(Nad#|)K[23C~a- ! Zzv93cYf2^`' );
define( 'LOGGED_IN_SALT',   'KbD1uZp@kg:_,pv+vNkAhfbv}Jpp1YP!c&~!#5OOQm#ELi!__qB5-hsP.1_]@6%4' );
define( 'NONCE_SALT',       '7ZSB=[N$YV>:Ee$[(W|&<=,-%j8S6/CC9L:cfzESC$#3$Ma%:+>>TN+?Md>(V;QT' );

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
