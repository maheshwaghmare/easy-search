<?php
/**
 * Plugin Name: Easy Search
 * Description: Zero configuration Gutenberg block for quick search the post, pages, and custom post types. Not using the Gutenberg block then simply use the shortcode <code>[easy_search]</code> anywhere.
 * Plugin URI: https://github.com/maheshwaghmare/easy-search/
 * Author: Mahesh M. Waghmare
 * Author URI: https://maheshwaghmare.com/
 * Version: 1.1.0
 * License: GPL2
 * Text Domain: easy-search
 *
 * @package Easy Search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Set constants.
define( 'EASY_SEARCH_VER', '1.1.0' );
define( 'EASY_SEARCH_FILE', __FILE__ );
define( 'EASY_SEARCH_BASE', plugin_basename( EASY_SEARCH_FILE ) );
define( 'EASY_SEARCH_DIR', plugin_dir_path( EASY_SEARCH_FILE ) );
define( 'EASY_SEARCH_URI', plugins_url( '/', EASY_SEARCH_FILE ) );

require_once EASY_SEARCH_DIR . 'src/init.php';