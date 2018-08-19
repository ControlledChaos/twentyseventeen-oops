<?php
/**
 * Custom header implementation.
 *
 * @package    WordPress
 * @subpackage Twenty_Seventeen_Oops
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom header implementation.
 *
 * @since  1.0.0
 * @access public
 */
final class Oops_Custom_Header {

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

		// Set up the WordPress core custom header feature.
		add_action( 'after_setup_theme', [ $this, 'custom_header_setup' ] );

		// Customize video play/pause button in the custom header.
		add_filter( 'header_video_settings', [ $this, 'video_controls' ] );

	}

	/**
	 * Set up the WordPress core custom header feature.
	 *
	 * @since  1.0.0
	 * @access public
	 * @uses   header_style()
	 */
	public function custom_header_setup() {

		/**
		 * Filter Twenty Seventeen custom-header support arguments.
		 *
		 * @since  1.0.0
		 * @param array $args {
		 *     An array of custom-header support arguments.
		 *
		 *     @type string $default-image          Default image of the header.
		 *     @type string $default_text_color     Default color of the header text.
		 *     @type int    $width                  Width in pixels of the custom header image. Default 954.
		 *     @type int    $height                 Height in pixels of the custom header image. Default 1300.
		 *     @type string $wp-head-callback       Callback function used to styles the header image and text
		 *                                          displayed on the blog.
		 *     @type string $flex-height            Flex support for height of header.
		 * }
		 */
		add_theme_support(
			'custom-header', apply_filters(
				'twentyseventeen_custom_header_args', [
					'default-image'    => get_theme_file_uri( '/assets/images/header.jpg' ),
					'width'            => 2000,
					'height'           => 1200,
					'flex-height'      => true,
					'video'            => true,
					'wp-head-callback' => [ $this, 'header_style' ],
				]
			)
		);

		register_default_headers(
			[
				'default-image' => [
					'url'           => '%s/assets/images/header.jpg',
					'thumbnail_url' => '%s/assets/images/header.jpg',
					'description'   => __( 'Default Header Image', 'twentyseventeen-oops' ),
				],
			]
		);

	}

	/**
	 * Styles the header image and text displayed on the blog.
	 *
	 * @since  1.0.0
	 * @access public
	 * @see    custom_header_setup().
	 */
	public function header_style() {

		$header_text_color = get_header_textcolor();

		/**
		 * Bail if no custom options for text are set.
		 *
		 * `get_header_textcolor()` options: `add_theme_support( 'custom-header' )` is default,
		 * hide text `(returns 'blank')` or any hex value.
		 */
		if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
			return;
		}

		// If the header text been hidden get the clip styles.
		if ( 'blank' === $header_text_color ) {
			include_once get_theme_file_path( '/includes/partials/header-text-default.php' );

		// Otherwise get the custom color styles.
		} else {
			include_once get_theme_file_path( '/includes/partials/header-text-custom.php' );
		}

	}

	/**
	 * Customize video play/pause button in the custom header.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array $settings Video settings.
	 * @return array The filtered video settings.
	 */
	public function twentyseventeen_video_controls( $settings ) {

		$settings['l10n']['play']  = '<span class="screen-reader-text">' . __( 'Play background video', 'twentyseventeen-oops' ) . '</span>' . Oops_Icons::get_svg( array( 'icon' => 'play' ) );
		$settings['l10n']['pause'] = '<span class="screen-reader-text">' . __( 'Pause background video', 'twentyseventeen-oops' ) . '</span>' . Oops_Icons::get_svg( array( 'icon' => 'pause' ) );

		return $settings;

	}

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function oops_custom_header() {

	return Oops_Custom_Header::instance();

}

// Run an instance of the class.
oops_custom_header();