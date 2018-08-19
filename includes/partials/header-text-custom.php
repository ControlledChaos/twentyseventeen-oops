<?php
/**
 * Custom header styles.
 *
 * @package     WordPress
 * @subpackage Twenty_Seventeen_Oops
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the custom header text color from the Customizer.
$header_text_color = get_header_textcolor(); ?>
<style id="oops-custom-header-styles" type="text/css">
.site-title a,
.colors-dark .site-title a,
.colors-custom .site-title a,
body.has-header-image .site-title a,
body.has-header-video .site-title a,
body.has-header-image.colors-dark .site-title a,
body.has-header-video.colors-dark .site-title a,
body.has-header-image.colors-custom .site-title a,
body.has-header-video.colors-custom .site-title a,
.site-description,
.colors-dark .site-description,
.colors-custom .site-description,
body.has-header-image .site-description,
body.has-header-video .site-description,
body.has-header-image.colors-dark .site-description,
body.has-header-video.colors-dark .site-description,
body.has-header-image.colors-custom .site-description,
body.has-header-video.colors-custom .site-description {
	color: #<?php echo esc_attr( $header_text_color ); ?>;
}
</style>