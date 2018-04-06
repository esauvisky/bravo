<?php
/**
 *  Theme-specific stuff
 */
if ( !defined( 'ABSPATH' ) ) exit;
if ( !function_exists( 'hestia_child_parent_css' ) ):
    function hestia_child_parent_css() {
        wp_enqueue_style( 'hestia_child_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array( 'bootstrap' ) );
	if( is_rtl() ) {
		wp_enqueue_style( 'hestia_child_parent_rtl', trailingslashit( get_template_directory_uri() ) . 'style-rtl.css', array( 'bootstrap' ) );
	}

    }
endif;
add_action( 'wp_enqueue_scripts', 'hestia_child_parent_css', 10 );

/**
 *  Import options from Hestia
 *  @since 1.0.0
 */
function hestia_child_get_lite_options() {
	$hestia_mods = get_option( 'theme_mods_hestia' );
	if ( ! empty( $hestia_mods ) ) {
		foreach ( $hestia_mods as $hestia_mod_k => $hestia_mod_v ) {
			set_theme_mod( $hestia_mod_k, $hestia_mod_v );
		}
	}
}
add_action( 'after_switch_theme', 'hestia_child_get_lite_options' );

/**
 *  Removes the annoying admin bar
 */
show_admin_bar(false);
/*function remove_admin_bar() {
    // Removes the bar only for non-admins (meh..)
    // Deprecated in favor of custom menu items in add_menu_items()
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'remove_admin_bar');*/


/**
 *  Allow subscribers to see Private posts and pages
 */
$subRole = get_role( 'subscriber' );
$subRole->add_cap( 'read_private_posts' );
$subRole->add_cap( 'read_private_pages' );

/**
 *  Display different menu items for different users
 */
add_filter( 'wp_nav_menu_items', 'add_menu_items', 10, 2 );
function add_menu_items( $items, $args ) {
    if (is_user_logged_in()) {
        // Adds Search and My Favorites items to the primary menu when logged in
        if ($args->theme_location == 'primary') {
            // Maybe using get_permalink(get_page_by_title("Search")) could be more robust? I'd bet it isn't
            $items .= '<li><a href="' . get_permalink(2)  . '">Search</a></li>';
            $items .= '<li><a href="' . get_permalink(43) . '">My Favorites</a></li>';
        }

        // Adds menu items common to both primary and footer menus
        if ($args->theme_location == 'primary' || $args->theme_location == 'footer') {
            // Adds Administration item to both menus if user is admin
            if (current_user_can('manage_options')) {
                $items .= '<li><a href="' . get_admin_url() . '">Administration</a></li>';
            }
            // Adds Log out to both menus when logged in
            $items .= '<li><a href="' . wp_logout_url(home_url()) . '">Log Out</a></li>';
        }
    } elseif (!is_user_logged_in()) {
        // Adds menu items common to both primary and footer menus
        if ($args->theme_location == 'primary' || $args->theme_location == 'footer') {
            $items .= '<li><a href="' . wp_registration_url() . '">Register</a></li>';
            // Redirects user to Search page after login
            $items .= '<li><a href="' . wp_login_url(get_permalink(2)) . '">Log In</a></li>';
        }
    }
    return $items;
}
