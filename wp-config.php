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
define( 'DB_NAME', 'lemoncode_wc_test' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'z[;^$-a^jdM.JJYZec6w:{&9(w*q0EG)d^vJ.S-g|*!u3#_$=O&k#@jaOcqkfa[&' );
define( 'SECURE_AUTH_KEY',  'U]<_gxX,*ZK[L?b*;VnfaC?$z`O[LrI!uq>J:sNyN_@?KosgO*7Y|rfu8L)`{N`&' );
define( 'LOGGED_IN_KEY',    '8SRFPq`*Jaql%G88]4*aSdH@Hibl=V,TtBp&vztP85WR|&=$9A24yT!$R#PmT!-S' );
define( 'NONCE_KEY',        'PorGoMwheVqi9O1*o&M9X*_n]^%]a`?<O<zf_p`s34=UAycp]|E8*TI71Dg~WW8C' );
define( 'AUTH_SALT',        'G1`P^$wTP}R]hkjRlzR`?8SH5U^+=$rz(/(hq%ma3N+v:P<f<^Ke{VD]=ww:hDHJ' );
define( 'SECURE_AUTH_SALT', 'yLCYOR~LbeFtYh(kC/U]}*s*3TOB`APz#,;)bN9<JatnZ7p/1khY6K}]&3l!qF85' );
define( 'LOGGED_IN_SALT',   ' Hv$g,p,{ENZ8[q0_3:AZ5V7GaWJ~$J+LD`U`Dd/4;AWu)dQ7)z(0ElspAnV#L}v' );
define( 'NONCE_SALT',       '9B}=kO`EBLUq8M&W|6*}u?tXjTv_g5Q-I3z?oWq KD2<R^B3C|,3?~:8$u!|0#OZ' );

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
