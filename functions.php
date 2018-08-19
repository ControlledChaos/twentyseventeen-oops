<?php
/**
 * Twenty Seventeen Oops! functions and definitions.
 *
 * Based on the Twenty Seventeen theme.
 *
 * @link       https://wordpress.org/themes/twentyseventeen/
 *
 * @package    WordPress
 * @subpackage Twenty_Seventeen_Oops
 * @since      1.0.0
 *
 * @todo       Rename this when development is further along.
 *             It's best not to have it tied to a year.
 */

/*
Twenty Seventeen Oops! is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Twenty Seventeen Oops! is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Twenty Seventeen Oops!. If not, see {URI to Plugin License}.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Twenty Seventeen only works in WordPress 4.7 or later.
if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
	require get_template_directory() . '/includes/back-compat.php';
	return;
}

// Get plugins path to check for active plugins.
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Twenty Seventeen Oops! functions and definitions.
 *
 * @since  1.0.0
 * @access public
 */
final class Oops_Functions {

	/**
	 * Get an instance of the class.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object Returns the instance.
	 */
	public static function instance() {

		// Varialbe for the instance to be used outside the class.
		static $instance = null;

		if ( is_null( $instance ) ) {

			// Set variable for new instance.
			$instance = new self;

			// Get class dependencies.
			$instance->dependencies();

		}

		// Return the instance.
		return $instance;

	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return self
	 */
	private function __construct() {

		// Theme setup.
		add_action( 'after_setup_theme', [ $this, 'setup' ] );

		// Set the content width in pixels.
		add_action( 'template_redirect', [ $this, 'content_width' ], 0 );

		// Add preconnect for Google Fonts.
		add_filter( 'wp_resource_hints', [ $this, 'resource_hints' ], 10, 2 );

		// Register widget areas.
		add_action( 'widgets_init', [ $this, 'widgets_init' ] );

		// Handles JavaScript detection.
		add_action( 'wp_head', [ $this, 'javascript_detection' ], 0 );

		// Display custom color CSS.
		add_action( 'wp_head', [ $this, 'colors_css_wrap' ] );

		// Enqueue scripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Add a pingback url auto-discovery header.
		add_action( 'wp_head', [ $this, 'pingback_header' ] );

		// Replaces "[...]" in excerpts with ...
		add_filter( 'excerpt_more', [ $this, 'excerpt_more' ] );

		// Add custom image sizes attribute.
		add_filter( 'wp_calculate_image_sizes', [ $this, 'content_image_sizes_attr' ], 10, 2 );

		// Filter the `sizes` value in the header image markup.
		add_filter( 'get_header_image_tag', [ $this, 'header_image_tag' ], 10, 3 );

		// Add custom image sizes attribute
		add_filter( 'wp_get_attachment_image_attributes', [ $this, 'post_thumbnail_sizes_attr' ], 10, 3 );

		// Use front-page.php for Front page static page.
		add_filter( 'frontpage_template', [ $this, 'front_page_template' ] );

		// Modifies tag cloud widget arguments.
		add_filter( 'widget_tag_cloud_args', [ $this, 'widget_tag_cloud_args' ] );

		// Remove the Draconian capital P filter.
		remove_filter( 'the_title', 'capital_P_dangit', 11 );
		remove_filter( 'the_content', 'capital_P_dangit', 11 );
		remove_filter( 'comment_text', 'capital_P_dangit', 31 );

	}

	/**
	 * Define plugin constants.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function constants() {

		/**
		 * The current version of the theme.
		 *
		 * @since  1.0.0
		 * @return string Returns the latest plugin version.
		 */
		if ( ! defined( 'OOPS_VERSION' ) ) {
			define( 'OOPS_VERSION', '1.0.0' );
		}

	}

	/**
	 * Throw error on object clone.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __clone() {

		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'This is not allowed.', 'twentyseventeen-oops' ), OOPS_VERSION );

	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __wakeup() {

		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'This is not allowed.', 'twentyseventeen-oops' ), OOPS_VERSION );

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Uses `get_theme_file_path` instead of `get_theme_file_path`
	 * as in the Twenty Seventeen theme. This make child-theming easier.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function dependencies() {

		/**
		 * Implement the Custom Header feature.
		 */
		require get_theme_file_path( '/includes/custom-header.php' );

		/**
		 * Custom template tags for this theme.
		 */
		require get_theme_file_path( '/includes/template-tags.php' );

		/**
		 * Additional features to allow styling of the templates.
		 */
		require get_theme_file_path( '/includes/template-functions.php' );

		/**
		 * Customizer additions.
		 */
		require get_theme_file_path( '/includes/customizer.php' );

		/**
		 * SVG icons functions and filters.
		 */
		require get_theme_file_path( '/includes/icon-functions.php' );

	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function setup() {

		/**
		 * Make theme available for translation.
		 */
		load_theme_textdomain( 'twentyseventeen-oops' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/**
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/**
		 * Enable support for Post Thumbnails on posts and pages.
		 */
		add_theme_support( 'post-thumbnails' );

		add_image_size( 'twentyseventeen-featured-image', 2000, 1200, true );

		add_image_size( 'twentyseventeen-thumbnail-avatar', 100, 100, true );

		// Set the default content width.
		$GLOBALS['content_width'] = 525;

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			[
				'top'    => __( 'Top Menu', 'twentyseventeen-oops' ),
				'social' => __( 'Social Links Menu', 'twentyseventeen-oops' ),
			]
		);

		/**
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5', [
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			]
		);

		/**
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats', [
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			]
		);

		// Add theme support for Custom Logo.
		add_theme_support(
			'custom-logo', [
				'width'      => 250,
				'height'     => 250,
				'flex-width' => true,
				'flex-height' => true,
			]
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Block editor wide images.
		add_theme_support( 'align-wide' );

		/**
		 * Color arguments.
		 *
		 * Some WordPress admin colors used here for demonstration.
		 */
		$color_args = [
			[
				'name'  => __( 'White', 'controlled-chaos' ),
				'slug'  => 'oops-white',
				'color' => '#fff',
			],
			[
				'name'  => __( 'Light Gray', 'controlled-chaos' ),
				'slug'  => 'oops-wp-gray',
				'color' => '#cccccc',
			],
			[
				'name'  => __( 'Medium Gray', 'controlled-chaos' ),
				'slug'  => 'oops-wp-gray',
				'color' => '#888888',
			],
			[
				'name'  => __( 'Dark Gray', 'controlled-chaos' ),
				'slug'  => 'oops-wp-gray',
				'color' => '#222222',
			],
			[
				'name'  => __( 'Success Green', 'controlled-chaos' ),
				'slug'  => 'oops-wp-light-blue',
				'color' => '#46b450',
			],
			[
				'name'  => __( 'Error Red', 'controlled-chaos' ),
				'slug'  => 'oops-wp-light-blue',
				'color' => '#dc3232',
			],
			[
				'name'  => __( 'Warning Yellow', 'controlled-chaos' ),
				'slug'  => 'oops-wp-light-blue',
				'color' => '#ffb900',
			]
		];

		// Apply a filter to editor arguments.
		$colors = apply_filters( 'oops_editor_colors', $color_args );

		// Add color support.
		add_theme_support( 'editor-color-palette', $colors );

		/**
		 * This theme styles the visual editor to resemble the theme style,
		 * specifically font, colors, and column width.
		 *
		 * Enqueues only if the Gutenberg plugin is active.
		 *
		 * @since  1.0.0
		 *
		 * @todo Address this when the block editor is in WordPress core.
		 * @todo Think about how to enqueue this by post type support for the block editor.
		 */
		if ( ! function_exists( 'the_gutenberg_project' ) ) {
			add_editor_style( [ 'assets/css/editor-style.css', $this::fonts_url() ] );
		}

		// Define and register starter content to showcase the theme on new sites.
		$starter_content = [
			'widgets'     => [
				// Place three core-defined widgets in the sidebar area.
				'sidebar-1' => [
					'text_business_info',
					'search',
					'text_about',
				],

				// Add the core-defined business info widget to the footer 1 area.
				'sidebar-2' => [
					'text_business_info',
				],

				// Put two core-defined widgets in the footer 2 area.
				'sidebar-3' => [
					'text_about',
					'search',
				],
			],

			// Specify the core-defined pages to create and add custom thumbnails to some of them.
			'posts'       => [
				'home',
				'about'            => [
					'thumbnail' => '{{image-sandwich}}',
				],
				'contact'          => [
					'thumbnail' => '{{image-espresso}}',
				],
				'blog'             => [
					'thumbnail' => '{{image-coffee}}',
				],
				'homepage-section' => [
					'thumbnail' => '{{image-espresso}}',
				],
			],

			// Create the custom image attachments used as post thumbnails for pages.
			'attachments' => [
				'image-espresso' => [
					'post_title' => _x( 'Espresso', 'Theme starter content', 'twentyseventeen-oops' ),
					'file'       => 'assets/images/espresso.jpg', // URL relative to the template directory.
				],
				'image-sandwich' => [
					'post_title' => _x( 'Sandwich', 'Theme starter content', 'twentyseventeen-oops' ),
					'file'       => 'assets/images/sandwich.jpg',
				],
				'image-coffee'   => [
					'post_title' => _x( 'Coffee', 'Theme starter content', 'twentyseventeen-oops' ),
					'file'       => 'assets/images/coffee.jpg',
				],
			],

			// Default to a static front page and assign the front and posts pages.
			'options'     => [
				'show_on_front'  => 'page',
				'page_on_front'  => '{{home}}',
				'page_for_posts' => '{{blog}}',
			],

			// Set the front page section theme mods to the IDs of the core-registered pages.
			'theme_mods'  => [
				'panel_1' => '{{homepage-section}}',
				'panel_2' => '{{about}}',
				'panel_3' => '{{blog}}',
				'panel_4' => '{{contact}}',
			],

			// Set up nav menus for each of the two areas registered in the theme.
			'nav_menus'   => [
				// Assign a menu to the "top" location.
				'top'    => [
					'name'  => __( 'Top Menu', 'twentyseventeen-oops' ),
					'items' => [
						'link_home', // Note that the core "home" page is actually a link in case a static front page is not used.
						'page_about',
						'page_blog',
						'page_contact',
					],
				],

				// Assign a menu to the "social" location.
				'social' => [
					'name'  => __( 'Social Links Menu', 'twentyseventeen-oops' ),
					'items' => [
						'link_yelp',
						'link_facebook',
						'link_twitter',
						'link_instagram',
						'link_email',
					],
				],
			],
		];

		/**
		 * Filters Twenty Seventeen array of starter content.
		 *
		 * @since  1.0.0
		 * @param array $starter_content Array of starter content.
		 */
		$starter_content = apply_filters( 'twentyseventeen_starter_content', $starter_content );

		add_theme_support( 'starter-content', $starter_content );

	}

	/**
	 * Set the content width in pixels, based on the theme's design and stylesheet.
	 *
	 * Priority 0 to make it available to lower priority callbacks.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 * @global int $content_width
	 */
	public function content_width() {

		$content_width = $GLOBALS['content_width'];

		// Get layout.
		$page_layout = get_theme_mod( 'page_layout' );

		// Check if layout is one column.
		if ( 'one-column' === $page_layout ) {
			if ( Oops_Templates::is_frontpage() ) {
				$content_width = 644;
			} elseif ( is_page() ) {
				$content_width = 740;
			}
		}

		// Check if is single post and there is no sidebar.
		if ( is_single() && ! is_active_sidebar( 'sidebar-1' ) ) {
			$content_width = 740;
		}

		/**
		 * Filter Twenty Seventeen content width of the theme.
		 *
		 * @access public
		 * @param int $content_width Content width in pixels.
		 */
		$GLOBALS['content_width'] = apply_filters( 'twentyseventeen_content_width', $content_width );

	}

	/**
	 * Register custom fonts.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public static function fonts_url() {

		$fonts_url = '';

		/**
		 * Translators: If there are characters in your language that are not
		 * supported by Libre Franklin, translate this to 'off'. Do not translate
		 * into your own language.
		 */
		$libre_franklin = _x( 'on', 'Libre Franklin font: on or off', 'twentyseventeen-oops' );

		if ( 'off' !== $libre_franklin ) {
			$font_families = [];

			$font_families[] = 'Libre Franklin:300,300i,400,400i,600,600i,800,800i';

			$query_args = [
				'family' => urlencode( implode( '|', $font_families ) ),
				'subset' => urlencode( 'latin,latin-ext' ),
			];

			$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
		}

		return esc_url_raw( $fonts_url );

	}

	/**
	 * Add preconnect for Google Fonts.
	 * @since  1.0.0
	 * @access public
	 * @param  array  $urls           URLs to print for resource hints.
	 * @param  string $relation_type  The relation type the URLs are printed.
	 * @return array $urls           URLs to print for resource hints.
	 * @return void
	 */
	function resource_hints( $urls, $relation_type ) {

		if ( wp_style_is( 'oops-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
			$urls[] = [
				'href' => 'https://fonts.gstatic.com',
				'crossorigin',
			];
		}

		return $urls;

	}

	/**
	 * Register widget areas.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	function widgets_init() {
		register_sidebar(
			[
				'name'          => __( 'Blog Sidebar', 'twentyseventeen-oops' ),
				'id'            => 'sidebar-1',
				'description'   => __( 'Add widgets here to appear in your sidebar on blog posts and archive pages.', 'twentyseventeen-oops' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			]
		);

		register_sidebar(
			[
				'name'          => __( 'Footer 1', 'twentyseventeen-oops' ),
				'id'            => 'sidebar-2',
				'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen-oops' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			]
		);

		register_sidebar(
			[
				'name'          => __( 'Footer 2', 'twentyseventeen-oops' ),
				'id'            => 'sidebar-3',
				'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen-oops' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			]
		);

	}

	/**
	 * Handles JavaScript detection.
	 *
	 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 */
	function javascript_detection() {

		echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";

	}

	/**
	 * Display custom color CSS.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 */
	function colors_css_wrap() {

		if ( 'custom' !== get_theme_mod( 'colorscheme' ) && ! is_customize_preview() ) {
			return;
		}

		require_once get_theme_file_path( '/includes/color-patterns.php' );
		$hue = absint( get_theme_mod( 'colorscheme_hue', 250 ) );

		$customize_preview_data_hue = '';
		if ( is_customize_preview() ) {
			$customize_preview_data_hue = 'data-hue="' . $hue . '"';
		}
	?>
		<style type="text/css" id="custom-theme-colors" <?php echo $customize_preview_data_hue; ?>>
			<?php echo Oops_Color_Patterns::custom_colors_css(); ?>
		</style>
	<?php
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	function enqueue_scripts() {

		// Add custom fonts, used in the main stylesheet.
		wp_enqueue_style( 'oops-fonts', $this::fonts_url(), [], null );

		// Theme stylesheet.
		wp_enqueue_style( 'oops-style', get_stylesheet_uri() );

		// Load the dark colorscheme.
		if ( 'dark' === get_theme_mod( 'colorscheme', 'light' ) || is_customize_preview() ) {
			wp_enqueue_style( 'oops-colors-dark', get_theme_file_uri( '/assets/css/colors-dark.css' ), [ 'oops-style' ], '1.0' );
		}

		// Load the Internet Explorer 9 specific stylesheet, to fix display issues in the Customizer.
		if ( is_customize_preview() ) {
			wp_enqueue_style( 'oops-ie9', get_theme_file_uri( '/assets/css/ie9.css' ), [ 'oops-style' ], '1.0' );
			wp_style_add_data( 'oops-ie9', 'conditional', 'IE 9' );
		}

		// Load the Internet Explorer 8 specific stylesheet.
		wp_enqueue_style( 'oops-ie8', get_theme_file_uri( '/assets/css/ie8.css' ), [ 'oops-style' ], '1.0' );
		wp_style_add_data( 'oops-ie8', 'conditional', 'lt IE 9' );

		// Load the html5 shiv.
		wp_enqueue_script( 'html5', get_theme_file_uri( '/assets/js/html5.js' ), [], '3.7.3' );
		wp_script_add_data( 'html5', 'conditional', 'lt IE 9' );

		wp_enqueue_script( 'oops-skip-link-focus-fix', get_theme_file_uri( '/assets/js/skip-link-focus-fix.js' ), [], '1.0', true );

		$twentyseventeen_l10n = [
			'quote' => Oops_Icons::get_svg( [ 'icon' => 'quote-right' ] ),
		];

		if ( has_nav_menu( 'top' ) ) {
			wp_enqueue_script( 'oops-navigation', get_theme_file_uri( '/assets/js/navigation.js' ), [ 'jquery' ], '1.0', true );
			$twentyseventeen_l10n['expand']   = __( 'Expand child menu', 'twentyseventeen-oops' );
			$twentyseventeen_l10n['collapse'] = __( 'Collapse child menu', 'twentyseventeen-oops' );
			$twentyseventeen_l10n['icon']     = Oops_Icons::get_svg(
				[
					'icon'     => 'angle-down',
					'fallback' => true,
				]
			);
		}

		wp_enqueue_script( 'oops-global', get_theme_file_uri( '/assets/js/global.js' ), [ 'jquery' ], '1.0', true );

		wp_enqueue_script( 'jquery-scrollto', get_theme_file_uri( '/assets/js/jquery.scrollTo.js' ), [ 'jquery' ], '2.1.2', true );

		wp_localize_script( 'oops-skip-link-focus-fix', 'twentyseventeenScreenReaderText', $twentyseventeen_l10n );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

	}

	/**
	 * Add a pingback url auto-discovery header for singularly identifiable articles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	function pingback_header() {

		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">' . "\n", get_bloginfo( 'pingback_url' ) );
		}

	}

	/**
	 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
	 * a 'Continue reading' link.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $link Link to single post/page.
	 * @return string 'Continue reading' link prepended with an ellipsis.
	 */
	function excerpt_more( $link ) {

		if ( is_admin() ) {
			return $link;
		}

		$link = sprintf(
			'<p class="link-more"><a href="%1$s" class="more-link">%2$s</a></p>',
			esc_url( get_permalink( get_the_ID() ) ),
			/* translators: %s: Name of current post */
			sprintf( __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'twentyseventeen-oops' ), get_the_title( get_the_ID() ) )
		);

		return ' &hellip; ' . $link;

	}

	/**
	 * Add custom image sizes attribute to enhance responsive image functionality
	 * for content images.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param string $sizes A source size value for use in a 'sizes' attribute.
	 * @param array  $size  Image size. Accepts an array of width and height
	 *                      values in pixels (in that order).
	 * @return string A source size value for use in a content image 'sizes' attribute.
	 */
	function content_image_sizes_attr( $sizes, $size ) {

		$width = $size[0];

		if ( 740 <= $width ) {
			$sizes = '(max-width: 706px) 89vw, (max-width: 767px) 82vw, 740px';
		}

		if ( is_active_sidebar( 'sidebar-1' ) || is_archive() || is_search() || is_home() || is_page() ) {
			if ( ! ( is_page() && 'one-column' === get_theme_mod( 'page_options' ) ) && 767 <= $width ) {
				$sizes = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
			}
		}

		return $sizes;

	}

	/**
	 * Filter the `sizes` value in the header image markup.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $html   The HTML image tag markup being filtered.
	 * @param  object $header The custom header object returned by 'get_custom_header()'.
	 * @param  array  $attr   Array of the attributes for the image tag.
	 * @return string The filtered header image HTML.
	 */
	function header_image_tag( $html, $header, $attr ) {

		if ( isset( $attr['sizes'] ) ) {
			$html = str_replace( $attr['sizes'], '100vw', $html );
		}

		return $html;

	}

	/**
	 * Add custom image sizes attribute to enhance responsive image functionality
	 * for post thumbnails.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array $attr       Attributes for the image markup.
	 * @param  int   $attachment Image attachment ID.
	 * @param  array $size       Registered image size or flat array of height and width dimensions.
	 * @return array The filtered attributes for the image markup.
	 */
	function post_thumbnail_sizes_attr( $attr, $attachment, $size ) {

		if ( is_archive() || is_search() || is_home() ) {
			$attr['sizes'] = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
		} else {
			$attr['sizes'] = '100vw';
		}

		return $attr;

	}

	/**
	 * Use front-page.php when Front page displays is set to a static page.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $template front-page.php.
	 * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.
	 */
	function front_page_template( $template ) {

		if ( is_home() ) {
			$template = '';
		} else {
			$template = $template;
		}

		return $template;

	}

	/**
	 * Modifies tag cloud widget arguments to display all tags in the same font size
	 * and use list format for better accessibility.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array $args Arguments for tag cloud widget.
	 * @return array The filtered arguments for tag cloud widget.
	 */
	function widget_tag_cloud_args( $args ) {

		$args['largest']  = 1;
		$args['smallest'] = 1;
		$args['unit']     = 'em';
		$args['format']   = 'list';

		return $args;

	}

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function oops_functions() {

	return Oops_Functions::instance();

}

// Run an instance of the class.
oops_functions();