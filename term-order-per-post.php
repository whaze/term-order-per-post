<?php
/**
 * Plugin Name:       Term Order Per Post
 * Plugin URI:        https://profiles.wordpress.org/whaze/
 * Description:       Order taxonomy terms individually per post, directly from the Gutenberg editor sidebar.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Jérôme Buquet
 * Author URI:        https://profiles.wordpress.org/whaze/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       term-order-per-post
 * Domain Path:       /languages
 *
 * @package Whaze\TermOrderPerPost
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TERM_ORDER_PER_POST_DIR', plugin_dir_path( __FILE__ ) );
define( 'TERM_ORDER_PER_POST_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/functions.php';
