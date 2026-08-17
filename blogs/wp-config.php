<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u353399544_bhiu_blogs' );

/** Database username */
define( 'DB_USER', 'u353399544_bhiu_blogs' );

/** Database password */
define( 'DB_PASSWORD', 'rDTq]JEE%R6W' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         '[_s?~[U$5Qt%{f0L+o^2/jMsCXwp|nr0tqKs{?b{II/6sYBu=T0}Lu[LVf_g!N(e' );
define( 'SECURE_AUTH_KEY',  'H{I$?lslQadC0NSQSiseYT+g^7sf>MWt@`eb*$4qkYw~[36G)}~WT)$Xr$0i&5n:' );
define( 'LOGGED_IN_KEY',    '^mbGlUnb;~45Sh{K$d1rsjlN(+mq0B2pJl$Iq+eJoj8 Md.QNI*WrARy32y_(;Om' );
define( 'NONCE_KEY',        'r6Ci;~gelEA<$IhB~FKqzu=_,;f ZF+;tIC,!3Xf#`.BWk9kgAdtjO(8LU1.v6dk' );
define( 'AUTH_SALT',        '*<KmqKhQa8E`g)UB`{6b@6tW-Pny`8SG6<W,=iY*ic?3*xHUuOP44$]%]xh]bx F' );
define( 'SECURE_AUTH_SALT', 'lAy=%AjKXl:+}U|EeUuDs^^iZy;^}CeU:JWyV jJCBu.PW`QpC$(vE}BI~l]adX)' );
define( 'LOGGED_IN_SALT',   '@dWYz-I5B{0-<iW40]s^dJXY{~&nnD63u4@mMW<av#io_0@tPwJ(:]fE8xTrL_Z@' );
define( 'NONCE_SALT',       ',4Vz*~13(rk,+uYW$`MlA73(>A]w6Y}u~4oEIYZBIVoGAAoB)eE9,?Ut^*H2>rHd' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
