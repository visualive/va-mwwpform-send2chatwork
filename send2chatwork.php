<?php
/**
 * Plugin Name: VA MW WP Form send to chatwork
 * Plugin URI: https://github.com/visualive/va-mwwpform-send2chatwork
 * Description: Mail sent from "MW WP Form" will be forwarded to room of chatwork.
 * Author: KUCKLU
 * Version: 1.0.3
 * Author URI: https://www.visualive.jp
 * Text Domain: va-mwwpf-send2cw
 * Domain Path: /langs
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package    WordPress
 * @subpackage VA MW WP Form send to chatwork
 * @since      1.0.0
 * @author     KUCKLU <kuck1u@visualive.jp>
 * @licenses   GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VA_MWWPF_SEND2CW_FILE', __FILE__ );
define( 'VA_MWWPF_SEND2CW_PLUGIN_URL', plugin_dir_url( VA_MWWPF_SEND2CW_FILE ) );
define( 'VA_MWWPF_SEND2CW_PLUGIN_PATH', plugin_dir_path( VA_MWWPF_SEND2CW_FILE ) );
define( 'VA_MWWPF_SEND2CW_PLUGIN_BASENAME', dirname( plugin_basename( VA_MWWPF_SEND2CW_FILE ) ) );
define( 'VA_MWWPF_SEND2CW_PLUGIN_DATA', get_file_data( VA_MWWPF_SEND2CW_FILE, array(
	'version'     => 'Version',
	'wp_version'  => 'WordPress Version',
	'php_version' => 'PHP Version',
	'db_version'  => 'DB Version',
	'prefix'      => 'Prefix',
	'network'     => 'Network',
) ) );
define( 'VA_MWWPF_SEND2CW_PLUGIN_PREFIX', 'vamwwpfsend2cw_' );
define( 'VA_MWWPF_SEND2CW_OPTION_NAME', 'va_mwwpf_send2cw' );

/**
 * Auto loader.
 *
 * @since 1.0.0
 */
spl_autoload_register( function ( $class ) {
	$slug = preg_replace( '/^\\VAMWWPFSEND2CW/', '', $class );
	$slug = str_replace( '\\', '/', $slug );
	$slug = str_replace( '_', '-', $slug );
	$slug = strtolower( $slug );
	$slug = trim( $slug, '/' );
	$slug = preg_replace( '/[^\/]+$/', 'class-$0', $slug );
	$path = dirname( VA_MWWPF_SEND2CW_FILE ) . "/incs/{$slug}.php";

	if ( file_exists( $path ) ) {
		require_once( $path );
	}
} );
require_once dirname( VA_MWWPF_SEND2CW_FILE ) . '/incs/functions.php';
