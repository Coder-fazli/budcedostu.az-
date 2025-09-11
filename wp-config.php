<?php
define( 'WP_CACHE', true );



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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u217165591_budcea' );

/** Database username */
define( 'DB_USER', 'u217165591_budcesots' );

/** Database password */
define( 'DB_PASSWORD', 'G8p@F86moLy' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '#BhT[(tM|IMeOx}uUvX?;l/NYCcCzJ&[+.a_B@4Q&IrT%yj}.10&tvWKXb$i|82S' );
define( 'SECURE_AUTH_KEY',   'Zg}GZh/_- Ir6HHcm3/1ZCVe4IqT?~S@{>7@*v*_?nBvqx%yO/}%q1Rph*]el[eY' );
define( 'LOGGED_IN_KEY',     ';T~!=[Z+>Gq@Q|Rg{CS.tk^z!db)v%,+HvPSSgzK#@(f_!|(K]9U7x63e&yZZhFW' );
define( 'NONCE_KEY',         'IM206G5N6o.ssiBGqepT2,.cChDY+[E9FNX:gd^?[iXiUK(0RVpL]e:D!tR5TIr:' );
define( 'AUTH_SALT',         'v-XoU=u{NC6^k`7j3Es-4XWV|bP=xV8vwWF3]/J7rHbRI,U|{I4X}ZIBluo1>&vW' );
define( 'SECURE_AUTH_SALT',  'ahS/iJyF{R}~sCx09`+.y,hq=s.+`^?zXCU}8^aiZMbz5Na+f$!#&^nxkq3ESNuD' );
define( 'LOGGED_IN_SALT',    'sN:]SvdurFq58y:Mr*3CkK(1/>!1AGA]Wg[B;h(gq/En3@L}n;N~I,?)hhSE1Qx&' );
define( 'NONCE_SALT',        'EyW~O{c:K0yE8/7SC+t?867O~FS7T9q3/;Eg4TI5*g@}}EPn/<wwo#Vm&Qjwew?b' );
define( 'WP_CACHE_KEY_SALT', '|DBct|Ol*ByU#}K3HLfAoz7{a5b.}#w88r]m0<bt)9rLJGh?$o,!!  aoT2XK`8A' );


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
define( 'WP_DEBUG', true );


/* Add any custom values between this line and the "stop editing" line. */



define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
