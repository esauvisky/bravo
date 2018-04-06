<?php
/**
 * Customizer functionality for the Blog settings panel from both lite and pro versions.
 *
 * @package Hestia
 * @since Hestia 1.1.67
 */


/**
 * Add blog settings in both lite and pro versions of the theme.
 *
 * @param object $wp_customize Customize manager object.
 * @since 1.1.67
 */
function hestia_blog_settings_lite_customize_register( $wp_customize ) {

	$wp_customize->add_section(
		'hestia_blog_layout', array(
			'title'    => apply_filters( 'hestia_blog_layout_control_label', esc_html__( 'Blog Settings', 'hestia' ) ),
			'priority' => 30,
		)
	);

		$options    = array( 0 => ' -- ' . esc_html__( 'Disable Section', 'hestia' ) . ' -- ' );
		$categories = get_categories();
	if ( ! empty( $categories ) ) {
		foreach ( $categories as $category ) {
			$cat_id             = $category->term_id;
			$cat_name           = $category->name;
			$options[ $cat_id ] = $cat_name;
		}
	}

		$wp_customize->add_setting(
			'hestia_featured_posts_category', array(
				'sanitize_callback' => 'hestia_sanitize_array',
				'default'           => 0,
			)
		);

		$wp_customize->add_control(
			'hestia_featured_posts_category', array(
				'type'     => 'select',
				'section'  => 'hestia_blog_layout',
				'label'    => esc_html__( 'Featured Posts', 'hestia' ),
				'choices'  => $options,
				'priority' => 10,
			)
		);

}
add_action( 'customize_register', 'hestia_blog_settings_lite_customize_register' );
