<?php
/**
 * SVG icons related functions and filters.
 *
 * @package    WordPress
 * @subpackage Twenty_Seventeen_Oops
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SVG icons related functions and filters.
 *
 * @since  1.0.0
 * @access public
 */
final class Oops_Icons {

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

		// Add SVG definitions to the footer.
		add_action( 'wp_footer', [ $this, 'include_svg_icons' ], 9999 );

		// Display SVG icons in social links menu.
		add_filter( 'walker_nav_menu_start_el', [ $this, 'nav_menu_social_icons' ], 10, 4 );

		// Add dropdown icon if menu item has children.
		add_filter( 'nav_menu_item_title', [ $this, 'dropdown_icon_to_menu_link' ], 10, 4 );

	}

	/**
	 * Add SVG definitions to the footer.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function include_svg_icons() {

		// Define SVG sprite file.
		$svg_icons = get_theme_file_path( '/assets/images/svg-icons.svg' );

		// If it exists, include it.
		if ( file_exists( $svg_icons ) ) {
			require_once( $svg_icons );
		}

	}

	/**
	 * Return SVG markup.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array $args {
	 *     Parameters needed to display an SVG.
	 *
	 *     @type string $icon  Required SVG icon filename.
	 *     @type string $title Optional SVG title.
	 *     @type string $desc  Optional SVG description.
	 * }
	 * @return string SVG markup.
	 */
	public static function get_svg( $args = [] ) {

		// Make sure $args are an array.
		if ( empty( $args ) ) {
			return __( 'Please define default parameters in the form of an array.', 'twentyseventeen-oops' );
		}

		// Define an icon.
		if ( false === array_key_exists( 'icon', $args ) ) {
			return __( 'Please define an SVG icon filename.', 'twentyseventeen-oops' );
		}

		// Set defaults.
		$defaults = [
			'icon'     => '',
			'title'    => '',
			'desc'     => '',
			'fallback' => false,
		];

		// Parse args.
		$args = wp_parse_args( $args, $defaults );

		// Set aria hidden.
		$aria_hidden = ' aria-hidden="true"';

		// Set ARIA.
		$aria_labelledby = '';

		/*
		* Twenty Seventeen doesn't use the SVG title or description attributes; non-decorative icons are described with .screen-reader-text.
		*
		* However, child themes can use the title and description to add information to non-decorative SVG icons to improve accessibility.
		*
		* Example 1 with title: <?php echo Oops_Icons::get_svg( array( 'icon' => 'arrow-right', 'title' => __( 'This is the title', 'textdomain' ) ) ); ?>
		*
		* Example 2 with title and description: <?php echo Oops_Icons::get_svg( array( 'icon' => 'arrow-right', 'title' => __( 'This is the title', 'textdomain' ), 'desc' => __( 'This is the description', 'textdomain' ) ) ); ?>
		*
		* See https://www.paciellogroup.com/blog/2013/12/using-aria-enhance-svg-accessibility/.
		*/
		if ( $args['title'] ) {

			$aria_hidden     = '';
			$unique_id       = uniqid();
			$aria_labelledby = ' aria-labelledby="title-' . $unique_id . '"';

			if ( $args['desc'] ) {
				$aria_labelledby = ' aria-labelledby="title-' . $unique_id . ' desc-' . $unique_id . '"';
			}

		}

		// Begin SVG markup.
		$svg = '<svg class="icon icon-' . esc_attr( $args['icon'] ) . '"' . $aria_hidden . $aria_labelledby . ' role="img">';

		// Display the title.
		if ( $args['title'] ) {

			$svg .= '<title id="title-' . $unique_id . '">' . esc_html( $args['title'] ) . '</title>';

			// Display the desc only if the title is already set.
			if ( $args['desc'] ) {
				$svg .= '<desc id="desc-' . $unique_id . '">' . esc_html( $args['desc'] ) . '</desc>';
			}

		}

		/*
		* Display the icon.
		*
		* The whitespace around `<use>` is intentional - it is a work around to a keyboard navigation bug in Safari 10.
		*
		* See https://core.trac.wordpress.org/ticket/38387.
		*/
		$svg .= ' <use href="#icon-' . esc_html( $args['icon'] ) . '" xlink:href="#icon-' . esc_html( $args['icon'] ) . '"></use> ';

		// Add some markup to use as a fallback for browsers that do not support SVGs.
		if ( $args['fallback'] ) {
			$svg .= '<span class="svg-fallback icon-' . esc_attr( $args['icon'] ) . '"></span>';
		}

		$svg .= '</svg>';

		return $svg;

	}

	/**
	 * Display SVG icons in social links menu.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $item_output The menu item output.
	 * @param  WP_Post $item        Menu item object.
	 * @param  int     $depth       Depth of the menu.
	 * @param  array   $args        wp_nav_menu() arguments.
	 * @return string  $item_output The menu item output with social icon.
	 */
	public function nav_menu_social_icons( $item_output, $item, $depth, $args ) {

		// Get supported social icons.
		$social_icons = $this->social_links_icons();

		// Change SVG icon inside social links menu if there is supported URL.
		if ( 'social' === $args->theme_location ) {

			foreach ( $social_icons as $attr => $value ) {

				if ( false !== strpos( $item_output, $attr ) ) {
					$item_output = str_replace( $args->link_after, '</span>' . $this->get_svg( [ 'icon' => esc_attr( $value ) ] ), $item_output );
				}

			}

		}

		return $item_output;

	}

	/**
	 * Add dropdown icon if menu item has children.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $title The menu item's title.
	 * @param  WP_Post $item  The current menu item.
	 * @param  array   $args  An array of wp_nav_menu() arguments.
	 * @param  int     $depth Depth of menu item. Used for padding.
	 * @return string  $title The menu item's title with dropdown icon.
	 */
	public function dropdown_icon_to_menu_link( $title, $item, $args, $depth ) {

		if ( 'top' === $args->theme_location ) {

			foreach ( $item->classes as $value ) {

				if ( 'menu-item-has-children' === $value || 'page_item_has_children' === $value ) {
					$title = $title . $this->get_svg( [ 'icon' => 'angle-down' ] );
				}

			}

		}

		return $title;

	}

	/**
	 * Returns an array of supported social links (URL and icon name).
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array $social_links_icons
	 */
	public function social_links_icons() {

		// Supported social links icons.
		$social_links_icons = array(
			'behance.net'     => 'behance',
			'codepen.io'      => 'codepen',
			'deviantart.com'  => 'deviantart',
			'digg.com'        => 'digg',
			'docker.com'      => 'dockerhub',
			'dribbble.com'    => 'dribbble',
			'dropbox.com'     => 'dropbox',
			'facebook.com'    => 'facebook',
			'flickr.com'      => 'flickr',
			'foursquare.com'  => 'foursquare',
			'plus.google.com' => 'google-plus',
			'github.com'      => 'github',
			'instagram.com'   => 'instagram',
			'linkedin.com'    => 'linkedin',
			'mailto:'         => 'envelope-o',
			'medium.com'      => 'medium',
			'pinterest.com'   => 'pinterest-p',
			'pscp.tv'         => 'periscope',
			'getpocket.com'   => 'get-pocket',
			'reddit.com'      => 'reddit-alien',
			'skype.com'       => 'skype',
			'skype:'          => 'skype',
			'slideshare.net'  => 'slideshare',
			'snapchat.com'    => 'snapchat-ghost',
			'soundcloud.com'  => 'soundcloud',
			'spotify.com'     => 'spotify',
			'stumbleupon.com' => 'stumbleupon',
			'tumblr.com'      => 'tumblr',
			'twitch.tv'       => 'twitch',
			'twitter.com'     => 'twitter',
			'vimeo.com'       => 'vimeo',
			'vine.co'         => 'vine',
			'vk.com'          => 'vk',
			'wordpress.org'   => 'wordpress',
			'wordpress.com'   => 'wordpress',
			'yelp.com'        => 'yelp',
			'youtube.com'     => 'youtube',
		);

		/**
		 * Filter Twenty Seventeen social links icons.
		 *
		 * @since  1.0.0
		 * @param  array $social_links_icons Array of social links icons.
		 */
		return apply_filters( 'twentyseventeen_social_links_icons', $social_links_icons );

	}

}

/**
 * Put an instance of the class into a function.
 *
 * @since  1.0.0
 * @access public
 * @return object Returns an instance of the class.
 */
function oops_icons() {

	return Oops_Icons::instance();

}

// Run an instance of the class.
oops_icons();