<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package Easy Search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_footer', 'easy_search_wp_js_templates' );
add_action( 'admin_footer', 'easy_search_admin_js_templates' );

if( ! function_exists( 'easy_search_admin_js_templates' ) ) :
	function easy_search_admin_js_templates() {
		easy_search_js_templates();
	}
endif;

if( ! function_exists( 'easy_search_wp_js_templates' ) ) :
	function easy_search_wp_js_templates() {

		if( ! is_singular( ) ) {
			return;
		}

		$post_content = get_the_content( get_the_ID() ); // Get the post_content
		$is_block_used = strpos($post_content, 'wp:easy-search/easy-search' );
		if( ! $is_block_used ) {
			return;
		}

		easy_search_js_templates();
	}
endif;

if( ! function_exists( 'easy_search_js_templates' ) ) :
	function easy_search_js_templates() {

		// Templates data.
		?>
		<script type="text/template" id="tmpl-easy-search-items">
			<# if ( data.items.length ) {
				var n = 65; #>
				<ul>
					<# for ( key in data.items ) {
						var title = data.items[ key ].title.rendered || data.items[ key ].title; 
						var link = data.items[ key ].link || data.items[ key ].url;
						var type = data.items[ key ].subtype || data.items[ key ].type;

						var short_title = (title.length > n) ? title.substr(0, n-1) + '&hellip;' : title;
						#>
						<li>
							<a href="{{ link }}">{{{ short_title }}} <span class="type {{type}}">{{type}}</span></a>
						</li>
					<# } #>
				</ul>
			<# } #>
		</script>

		<?php
		// Templates data.
		?>
		<script type="text/template" id="tmpl-easy-search-no-docs-found">
			<div class="easy-search-no-docs-found">
				<p>
					Sorry, no results found!.
				</p>
			</div>
		</script>
		<?php
	}

endif;


/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
if( ! function_exists( 'easy_search_block_assets' ) ) :
	function easy_search_block_assets() { // phpcs:ignore
		// Register block styles for both frontend + backend.
		wp_register_style(
			'easy_search-style-css', // Handle.
			plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
			is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
			null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
		);

		// Register block editor script for backend.
		wp_register_script(
			'easy_search-block-js', // Handle.
			plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
			null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
			true // Enqueue the script in the footer.
		);

		// Register block editor script for backend.
		wp_register_script(
			'easy_search-block-front-end-js', // Handle.
			plugins_url( '/dist/front-end.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
			array( 'jquery', 'wp-util' ), // Dependencies, defined above.
			null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
			true // Enqueue the script in the footer.
		);

		$vars = array(
			'api_url' => rest_url() . 'wp/v2/search/',
			'reference' => 'easy-search',
		);
		wp_localize_script( 'easy_search-block-front-end-js', 'EasySearchVars', $vars );

		// Register block editor styles for backend.
		wp_register_style(
			'easy_search-block-editor-css', // Handle.
			plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
			array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
			null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
		);

		// WP Localized globals. Use dynamic PHP stuff in JavaScript via `easySearchGlobal` object.
		wp_localize_script(
			'easy_search-block-js',
			'easySearchGlobal', // Array containing dynamic data for a JS Global.
			[
				'pluginDirPath' => plugin_dir_path( __DIR__ ),
				'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
				// Add more data here that you want to access from `easySearchGlobal` object.
			]
		);

		/**
		 * Register Gutenberg block on server-side.
		 *
		 * Register the block on server-side to ensure that the block
		 * scripts and styles for both frontend and backend are
		 * enqueued when the editor loads.
		 *
		 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
		 * @since 1.16.0
		 */
		register_block_type(
			'easy-search/easy-search', array(
				// Enqueue blocks.style.build.css on both frontend & backend.
				'script'         => 'easy_search-block-front-end-js',
				// Enqueue blocks.style.build.css on both frontend & backend.
				'style'         => 'easy_search-style-css',
				// Enqueue blocks.build.js in the editor only.
				'editor_script' => 'easy_search-block-js',
				// Enqueue blocks.editor.build.css in the editor only.
				'editor_style'  => 'easy_search-block-editor-css',
			)
		);
	}

	// Hook: Block assets.
	add_action( 'init', 'easy_search_block_assets' );
endif;

if( ! function_exists( 'easy_search_shortcode_markup' ) ) :
	/**
	 * Easy Shrotcode Markup
	 *
	 * @param  $atts  Shortcode attributes.
	 * @since 1.1.0
	 * @return mixed
	 */
	function easy_search_shortcode_markup( $atts = array() ) {
		wp_enqueue_style( 'easy_search-style-css' );
		wp_enqueue_script( 'easy_search-block-front-end-js' );

		$atts = shortcode_atts( array(
			'placeholder' => __('Search..', 'easy-search'),
			'subtype' => '',
		), $atts );
		ob_start();
		?>
		<div class="easy-search">
			<div class="easy-search-input-wrap">
				<input type="text" data-subtype="<?php echo esc_html( $atts['subtype'] ); ?>" class="easy-search-input" placeholder="<?php echo esc_html( $atts['placeholder'] ); ?>" />
				<span class="easy-search-spinner"></span>
				<span class="easy-search-close"></span>
			</div>
			<div class="easy-search-result"></div>
		</div>
		<?php
		easy_search_js_templates();
		return ob_get_clean();
	}
	add_shortcode( 'easy_search', 'easy_search_shortcode_markup' );
endif;

